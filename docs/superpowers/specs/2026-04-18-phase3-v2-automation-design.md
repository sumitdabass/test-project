# Phase 3 V2.0 — Automation Layer — Design

**Status:** Approved 2026-04-18.
**Owner:** Sumit Dabas.
**Umbrella:** Phase 3 V2 (Intelligence Layer) = V2.0 Automation → V2.1 Reconciliation → V2.2 Tax. This spec covers **V2.0 only**. V2.1 and V2.2 each get their own spec + plan when V2.0 ships.
**Predecessor:** Phase 3 V1 `v1.0.0` at `me.ipu.co.in` (Slack + web capture of expenses/incomes/withdrawals).
**Goal:** Turn Phase 3 from a passive ledger into an active, habit-forming system — automated digests, threshold alerts, and a weekly AI-assisted retrospective that keeps the ledger honest without adding manual work.

---

## 1. Why V2 exists

V1 captures what Sumit Slacks to it. That's half of a real personal finance system. Three gaps V2.0 closes:

1. **No feedback loop.** If Sumit forgets to log for three days, V1 silently has three empty days — nothing tells him. A daily digest surfaces that immediately.
2. **No guardrails.** A ₹20k impulse spend slips in without friction. Threshold alerts make overspending visible in real time instead of at month-end.
3. **Slack-capture drift.** Gemini occasionally mis-categorizes. Over months this decays report quality. A weekly AI retrospective re-reads recent captures and surfaces suggested corrections in a review queue.

Explicitly out of V2.0 scope: reconciliation (V2.1 — accounts + CSV imports + matching), tax classification (V2.2 — flags + ITR export). Neither depends on V2.0 so they can proceed in parallel, but V2.0 ships first per Sumit's ordering preference (habit-forms the system before adding more surfaces).

---

## 2. Locked decisions from brainstorm (2026-04-18)

1. **V2 scope:** all three modules (automation + reconciliation + tax) — "the intelligence layer."
2. **Release ordering:** automation first, then reconciliation, then tax. Rationale: habit formation before optimization; more/better data collected via V2.0's feedback loops means V2.1/V2.2 compute on richer input.
3. **V2.0 includes:** (a) time-triggered digests, (b) threshold + anomaly alerts, (c) weekly AI categorization polish. Explicitly dropped: net-worth forecasts (low utility for single user).
4. **Threshold configuration:** Filament CRUD page (not config-file, not auto-derived). Familiar pattern, trivial to test, Sumit controls what triggers.
5. **Delivery:** digests land in a new `#sumit-finance-digest` Slack channel; threshold alerts land as Slack DMs to Sumit from the bot. Digests are browse-able reference; alerts interrupt.
6. **AI retrospective trigger:** scheduled Sunday 9pm IST + Slack DM ping with the pending-suggestion count. On-demand button rejected (sits unused); per-capture second pass rejected (doubles Gemini cost + adds capture latency for marginal benefit).

---

## 3. Architecture

### 3.1 Compute where, schedule where
- **Laravel owns computation.** New read-only endpoints return the numbers that digests, alerts, and suggestions need. Business logic lives in `App\Services\*`, tested in isolation.
- **n8n owns scheduling and delivery.** One new workflow with five Schedule Trigger nodes calls Laravel endpoints on cron, formats results, posts to Slack. This matches Phase 2's division of responsibility — Laravel = state machine, n8n = orchestrator.
- **No new Laravel scheduler / cron.** Hostinger shared-hosting cron is fragile and already serves one daily backup; adding more scheduled entries risks silent cron misconfiguration. n8n is already running and has retry-aware scheduling built in.

### 3.2 Why n8n for schedule, not Laravel's scheduler
Three reasons, ordered by importance:
- **Retry + visibility.** n8n records each execution with timing, inputs, outputs. When Sunday's digest doesn't post, the failure is visible in the executions list instead of buried in Laravel logs.
- **Cross-app precedent.** Phase 2 already uses n8n as the scheduling plane for all of Slack → Gemini → Laravel. Adding a second workflow that drives Laravel-computed reports keeps the control plane in one tool.
- **Decoupling.** Laravel crashes → Slack digests still try to fire → n8n records the 500 → Sumit sees "6 days no digest" in the executions view instead of six days of silence.

### 3.3 Endpoints added in V2.0

All auth'd via the existing `X-Personal-Token` header + `throttle:60,1` middleware. Responses JSON-only.

- **`GET /api/personal/reports/daily?date=YYYY-MM-DD`** — returns today's totals + top 3 categories + income total + running-month spend.
- **`GET /api/personal/reports/weekly?week_of=YYYY-MM-DD`** — returns last-7-days spend/income breakdown by category + comparison to prior week (% change per category).
- **`GET /api/personal/reports/monthly?month=YYYY-MM`** — returns prior-month full breakdown: fixed vs variable split, per-category table, net flow, category deltas vs prior month.
- **`POST /api/personal/alerts/evaluate`** — evaluates all enabled `alert_rules` against current month's data, returns any currently-breached rules that haven't already been notified (via `alert_events` dedup). Idempotent.
- **`POST /api/personal/suggestions/generate`** — kicks off the weekly retrospective job: creates a `suggestion_batches` row, queues per-entry Gemini calls, returns `{batch_id, queued_count}`. Long-running work happens in a queued job, not synchronously.
- **`GET /api/personal/suggestions?status=pending`** — returns current pending suggestion count + batch metadata, for the Slack DM ping.

### 3.4 Capture-time alert evaluation
Threshold alerts fire two ways, both necessary:
1. **At capture, via a queued job.** After `PersonalExpenseController::store()` returns the 201 to the caller, an `EvaluateAlertsAfterCapture` job is dispatched. The job runs `AlertEvaluator::evaluate($expense)` and, for each breach, posts to the n8n webhook which DMs Sumit. The dispatch is non-blocking — the HTTP response to n8n is already on its way when the job queues — so capture latency stays unaffected. Typical end-to-end from Slack post to DM: under 10 seconds.
2. **Safety-net nightly.** n8n also calls `POST /api/personal/alerts/evaluate` at 9pm IST. If any breach wasn't notified (because n8n was down, or an expense was manually added via Filament without the capture-time path), the nightly pass catches it. Dedup via `alert_events` unique index `(alert_rule_id, expense_id)`.

Rationale for both: the real-time path is the UX; the safety net is the audit guarantee. Skipping either lets edge cases slip silently, which is the failure mode V2 exists to prevent.

---

## 4. Data model — 4 new tables

All new tables added as migrations in the existing `sumit-finance` repo. No schema change to V1's `expenses` / `incomes` / `income_sources` / `expense_categories` — V1 write path stays untouched per Sumit's V2 design rule.

### 4.1 `alert_rules`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| category_id | FK → expense_categories nullable | null = "any category" |
| monthly_cap | decimal(12,2) nullable | INR; null = no monthly cap |
| single_txn_cap | decimal(12,2) nullable | INR; null = no per-txn cap |
| enabled | boolean default true | soft-disable without deleting |
| timestamps + softDeletes | | |

**Validation:** at least one of `monthly_cap` / `single_txn_cap` must be non-null (a rule with neither does nothing). Enforced in `StoreAlertRuleRequest` + a model observer for UI creates.

**Seed data (4 rules):**
- `{category: Food, monthly_cap: 15000}`
- `{category: Transport, monthly_cap: 8000}`
- `{category: Entertainment, monthly_cap: 5000}`
- `{category: null, single_txn_cap: 10000}` — any single expense > ₹10k triggers.

### 4.2 `alert_events`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| alert_rule_id | FK → alert_rules | |
| expense_id | FK → expenses nullable | null for monthly-cap breaches not triggered by a specific txn |
| fired_at | datetime | when the breach was detected |
| message | varchar(300) | human-readable, e.g. "Food over ₹15k this month — at ₹16,240" |
| dismissed_at | datetime nullable | set by Filament "mark seen" bulk action |
| timestamps | | |

**Indexes:** `(alert_rule_id, expense_id) UNIQUE` (prevents duplicate fires for the same rule+txn), `(dismissed_at)` for fast "unseen" filter.

### 4.3 `suggestion_batches`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| run_at | datetime | when the job started |
| window_start | datetime | inclusive |
| window_end | datetime | inclusive |
| status | enum('queued','running','completed','failed') | |
| total_entries | int | how many expenses+incomes were evaluated |
| total_suggestions | int | how many suggestions this batch produced |
| timestamps | | |

One row per weekly run. Keeps history for auditing "did the retrospective actually run last Sunday?"

### 4.4 `suggestions`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| batch_id | FK → suggestion_batches | |
| expense_id | FK → expenses nullable | |
| income_id | FK → incomes nullable | |
| field | varchar(40) | the field being suggested: `category_id`, `is_fixed`, `source_id`, `payment_mode` |
| current_value | varchar(120) | human-readable "Transport" |
| suggested_value | varchar(120) | human-readable "Food" |
| reason | varchar(500) nullable | Gemini's explanation, e.g. "raw input mentions 'swiggy' which is typically Food, not Transport" |
| status | enum('pending','accepted','rejected') default pending | |
| reviewed_at | datetime nullable | |
| timestamps | | |

**Invariant:** exactly one of `expense_id` / `income_id` is non-null per row. Enforced by a model observer and a feature test.

---

## 5. Services + jobs

### 5.1 `App\Services\AlertEvaluator`
Pure service; no Laravel dependencies beyond models.
- `evaluate(Expense $e): Collection<AlertEventPayload>` — evaluates capture-time (per-txn + monthly caps after this insert). Returns the payload(s) to fire.
- `evaluateAll(): Collection<AlertEventPayload>` — evaluates monthly caps only against current totals, for the safety-net path.
- Writes `alert_events` rows with `fired_at = now()`. Returns the payloads for the caller to turn into Slack DMs.

### 5.2 `App\Services\DigestService`
- `daily(CarbonInterface $date): array` — shape matches the `/reports/daily` response.
- `weekly(CarbonInterface $weekOf): array`
- `monthly(CarbonInterface $month): array`

Pure read; no writes. Unit-testable against seeded data.

### 5.3 `App\Jobs\GenerateSuggestions`
Queueable job (runs on the default queue — Laravel 11 on shared hosting defaults to `database` driver, sufficient for weekly single-run).
1. Create `suggestion_batches` row with `status=running`.
2. Fetch all expenses + incomes in window.
3. For each entry, call Gemini with the critique prompt (see §6.2), parse response.
4. For any suggestion with `suggested_value != current_value`, insert `suggestions` row.
5. Update batch `status=completed`, `total_*` counts.
6. On Gemini error, mark batch `failed` and log; don't block next week's run.

### 5.4 `App\Services\SuggestionApplier`
- `accept(Suggestion $s): void` — applies the suggested value to the target model, marks suggestion `status=accepted`, `reviewed_at=now()`.
- `reject(Suggestion $s): void` — marks suggestion `status=rejected`, `reviewed_at=now()`, does not modify target.
Called by Filament row actions; also covered by feature tests on the resource.

---

## 6. n8n workflow

New workflow `Sumit Finance — Digests + Alerts + Retrospective`. Committed as `docs/n8n-personal-automation-workflow.json` in the `sumit-finance` repo. Structure:

### 6.1 Nodes
Five Schedule Trigger entry points, each branching into its own path:
1. **Daily 9pm IST** → HTTP GET `/api/personal/reports/daily` → Format node (compose Slack blocks) → Slack Post to `#sumit-finance-digest`.
2. **Weekly Sun 9pm IST** → `/api/personal/reports/weekly` → Slack Post to `#sumit-finance-digest`.
3. **Monthly 1st 9am IST** → `/api/personal/reports/monthly` → Slack Post to `#sumit-finance-digest`.
4. **Alert safety-net daily 9pm IST** → HTTP POST `/api/personal/alerts/evaluate` → IF (any breaches returned?) → for each breach, Slack DM to Sumit's user id.
5. **Suggestions Sun 9pm IST** → HTTP POST `/api/personal/suggestions/generate` → wait 2 min → HTTP GET `/api/personal/suggestions?status=pending` → IF count > 0 → Slack DM to Sumit with review link.

Plus a **capture-time alert webhook**: Laravel's `PersonalExpenseController` posts to an n8n webhook (`/webhook/<id>/personal-alert`) with the breach payload; the workflow receives it and posts a Slack DM. This is separate from the five scheduled branches.

### 6.2 Gemini critique prompt (suggestions)
V2.0 scope is **category corrections only** (not `is_fixed`, not `payment_mode`, not `source_id`). Those other fields are either rare to mis-assign (payment_mode) or decisions the user makes consciously at capture (is_fixed). Limiting scope keeps the suggestions queue tight — expanding later is a V2.1 or V2.2 decision. Feed Gemini one entry at a time plus its current category. Prompt shape:

```
You are auditing a personal-finance categorization. Given the original
text and the current categorization, decide if the categorization is
correct. If yes, return {"ok": true}. If a different category fits
better, return {"ok": false, "suggested_category": "...", "reason": "..."}.
Only change if you're confident. Never invent new categories — stick to
the provided list.

Original text: "<raw_input>"
Current category: "<category.name>"
Valid categories: [list of 13 expense_categories.name]
```

Responses fed back into `GenerateSuggestions` job which creates rows only when `ok == false`.

### 6.3 Webhook-registration gotcha (carried from Phase 2)
Every time the workflow is PUT via n8n API, follow with deactivate → activate to force webhook registration (see memory `reference_kyne_n8n.md`). Otherwise capture-time alert webhook returns 404 despite the workflow showing `active=true`.

---

## 7. Filament UI additions

Three new resources + one dashboard card. All policy-scoped to Sumit (single-user pattern from V1).

### 7.1 `AlertRuleResource` at `/admin/alert-rules`
Full CRUD. Form: category select (nullable — "any"), monthly_cap number field, single_txn_cap number field, enabled toggle. Validation ensures at least one cap is set. Table columns: category badge, monthly cap INR, single-txn cap INR, enabled icon, created_at. Seeded with four defaults (§4.1).

### 7.2 `AlertEventResource` at `/admin/alerts`
Read-only. List of fired alerts. Columns: fired_at, rule (category + cap), expense link, message, dismissed icon. Bulk action: "Mark seen" (sets `dismissed_at`). Default filter: `dismissed_at IS NULL`.

### 7.3 `SuggestionResource` at `/admin/suggestions`
Default filter `status=pending`. Columns: target entry date, current value, suggested value, reason. Row actions: Accept (calls `SuggestionApplier::accept`), Reject (calls `SuggestionApplier::reject`). Bulk-accept bulk action for the "all suggestions look right" case.

### 7.4 Dashboard widget: "Inbox"
StatsOverview widget showing two numbers: unseen alerts count (`alert_events where dismissed_at is null`) and pending suggestions count (`suggestions where status = pending`). Each links to its resource page.

---

## 8. Testing strategy

Mirrors Phase 3 V1 patterns. Target: ≥25 new tests in V2.0.

### 8.1 Unit tests (`tests/Unit/`)
- `AlertEvaluatorTest` — ≥6 cases: per-txn cap, monthly cap, rule disabled, category-specific rule, "any category" rule, dedup via unique index.
- `DigestServiceTest` — ≥4 cases per shape (daily / weekly / monthly) including boundary conditions (empty month, single-entry week).
- `SuggestionApplierTest` — accept persists, reject doesn't mutate target, status transitions are one-way.

### 8.2 Feature tests (`tests/Feature/`)
- `AlertRuleResourceTest` — Filament CRUD + validation (at least one cap required).
- `AlertEventResourceTest` — mark-seen bulk action updates `dismissed_at`.
- `SuggestionResourceTest` — accept / reject row actions + bulk accept.
- `ReportsApiTest` — one test per `/reports/*` endpoint, covering token auth + shape.
- `AlertsEvaluateApiTest` — POST returns breaches, idempotent re-call returns empty (dedup via `alert_events`).
- `GenerateSuggestionsJobTest` — dispatches Gemini calls, persists suggestions only when `ok=false`, marks batch complete.

### 8.3 Acceptance (in `docs/ACCEPTANCE.md`)
Seven scenarios, scripted:
1. Post a ₹12k expense → Slack DM fires within 10s.
2. Cross a monthly cap via a small expense → Slack DM fires.
3. Don't post anything for 3 days → daily digest shows zero; no alert fires (zero isn't a breach).
4. Sunday night rolls → suggestions batch runs → Slack DM ping → click → queue visible.
5. Accept a suggestion → target expense's category updates.
6. Add a rule via Filament → post a matching expense → alert fires.
7. Mark an alert event as seen → disappears from default filter view.

---

## 9. YAGNI cuts — explicit

| Feature | Why not in V2.0 |
|---|---|
| Email digest delivery | Slack is already the primary interface; adding email requires mailer config + template work for marginal benefit |
| Push notifications (mobile) | No mobile app; Slack's own push notifications cover this on phone |
| Per-day or per-week rule caps | Monthly + single-txn covers >90% of real cases; weekly caps are noise |
| Budget roll-over logic | Complicates monthly cap math (was "budget — last month's unspent"); not asked for |
| Auto-derived thresholds (trailing 3-month avg) | Considered and rejected — noisy during first 3 months; not worth the UX complexity |
| AI confidence scores on suggestions | Gemini doesn't expose them reliably; binary ok/suggested works |
| SMS alerts | Cost + carrier reliability in India is worse than Slack |
| Suggestion ML training | Gemini's zero-shot is sufficient for this volume |
| Investment / net-worth forecasts | V3 territory; spec §2 scope |

---

## 10. Milestones (~20–25 hours)

Each ends with green test suite + prod deploy + tag. Same vertical-slice discipline as Phase 3 V1.

1. **M1 — `alert_rules` + Filament CRUD + seed.** ~3 hrs. Migration, model, `AlertRuleResource`, seeder, 4 tests. Tag `v2.0-m1-rules`.
2. **M2 — `AlertEvaluator` service + `alert_events` + capture-time integration.** ~5 hrs. Migration, model, service, wire into `PersonalExpenseController`, 8 tests, n8n webhook stub (points nowhere yet — M5 wires it). Tag `v2.0-m2-evaluator`.
3. **M3 — `suggestion_batches` + `suggestions` + `SuggestionResource` + `SuggestionApplier`.** ~5 hrs. Migrations, models, Filament resource with accept/reject actions, tests. `GenerateSuggestions` job stub (no Gemini yet — M5 wires it). Tag `v2.0-m3-suggestions`.
4. **M4 — Report endpoints + `DigestService`.** ~4 hrs. Three `/reports/*` routes + tests, `DigestService` unit-tested. Tag `v2.0-m4-reports`.
5. **M5 — n8n workflow + Gemini critique + acceptance.** ~6 hrs. Author `docs/n8n-personal-automation-workflow.json`, import + activate, wire capture-time webhook URL into Laravel config. Run the 7-scenario acceptance matrix. Tag `v2.0.0-automation`.

Total: 5 milestones, ~23 hours — tight enough that losing a weekend or two to IPU work shouldn't slip the release more than a couple weeks.

---

## 11. Pre-flight (Sumit actions before M1)

- Create Slack channel `#sumit-finance-digest` (private, just you + bot).
- Invite `Davya Finance Bot` into it: `/invite @Davya Finance Bot`.
- Confirm V1 (`me.ipu.co.in`) has been collecting real data for ≥30 days before M5 acceptance — the Sunday retrospective has no signal on a 2-day-old dataset.
- No new tokens, keys, or env vars needed — V2.0 reuses `PERSONAL_CAPTURE_TOKEN`, the existing Gemini API key in n8n, and the existing Slack bot.

---

## 12. Design rules carried from V1 + Phase 2

- V2 must **not** change how V1 data is written. `expenses` and `incomes` columns stay unchanged; only additions go to new tables.
- **Fixed generous schema + `reason` / `description` freeform.** AI does not invent new columns. Adding a new rule type means a seeder change, not a migration.
- **n8n imported workflows need deactivate+activate** after PUT to register webhooks.
- **Slack bot messages** must be filtered at ingestion (the capture-time path already filters via the Phase 2 bot-loop fix; digest posts that land in `#sumit-finance-digest` have zero risk since that channel's events aren't subscribed to by the capture workflow).
- **Decimal casts:** always `decimal:2`. Returning numeric from Eloquent yields strings; assert as strings in tests.

---

## 13. Open items at design time

None blocking. M1 can start as soon as Phase 3 V1 ships and the pre-flight is done.
