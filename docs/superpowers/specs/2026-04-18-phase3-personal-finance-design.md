# Phase 3 — Sumit Personal Finance — Design

**Status:** Approved 2026-04-18.
**Owner:** Sumit Dabas.
**Umbrella:** "Finance Management by Sumit Dabas" — Phase 3 of 3.
**Predecessors:** Phase 1 davyascrm (live at `davyas.ipu.co.in`, tag `v1.1.2-finance-api`), Phase 2 Davya Finance (live, n8n workflow `yO0nzgy8KvdneITL`).
**Goal:** Single-user personal finance ledger that consolidates all of Sumit's expenses and income — including explicit withdrawals from the Davya business pool — into one auditable system with Slack-first capture and a Filament web UI.

---

## 1. Locked decisions from brainstorm (2026-04-18)

1. **Starting state:** greenfield + consolidation. Today Sumit tracks nothing systematically; finances are scattered across UPI apps, Google Sheets, and memory. Phase 3 replaces all of that.
2. **Scope of ledger:** expenses + income + their sources. Investments, assets, liabilities, and net-worth tracking are explicitly out of v1 (see YAGNI cuts).
3. **Capture surface:** both Slack-based ingestion (primary, for point-of-purchase capture on mobile) and a Filament web UI (for edits, reports, and bulk entry).
4. **Architectural placement:** separate Laravel app on a new subdomain — **not** a module inside davyascrm. Rationale: clean separation of personal vs business data, independent release cadence, possibility of separate reuse later.
5. **Davya → personal feed-in:** **explicit withdrawal events**. Sumit posts a Slack message saying he withdrew from Davya; the workflow writes a matched pair — one expense on the Davya side (debiting the business pool) and one income on the personal side. No auto-sweep, no shared-balance illusion. Accounting stays clean for future CA / tax work.

Single-user is also locked (memory: Phase 4 was previously multi-user personal finance, now absorbed into Phase 3 since Sumit is the only intended user).

---

## 2. Architecture

### 2.1 Stack
- **Language/framework:** PHP 8.4+ (matches Phase 1 prod), Laravel 11.
- **Admin panel:** Filament v3.3+.
- **Database:** MySQL 8 on Hostinger shared.
- **Queue / cache:** file/database drivers to match Phase 1's posture (no Redis needed for single-user scale).
- **Frontend:** Filament's Livewire + Tailwind, no separate SPA.
- **Testing:** PHPUnit with the same `tests/Feature` + `tests/Unit` split Phase 1/2 use; TDD discipline per commit (fail-first, then implement).

Deliberate choice to mirror the davyascrm stack: no new framework to learn, deploy muscle memory already exists, and code patterns (StoreRequest → Controller → Model → Policy → Filament Resource) are directly reusable.

### 2.2 Hosting & deployment
- **URL:** `me.ipu.co.in` — new subdomain on Sumit's existing Hostinger shared plan.
- **Docroot:** `/home/ipuc/sumit-finance/public` (new directory on the same server that hosts davyascrm; separate subdomain docroot configured via cPanel/hPanel).
- **PHP path:** server CLI default is 8.2; use `/opt/alt/php84/usr/bin/php` for artisan/composer (same constraint Phase 1/2 hit).
- **Deploy method:** SSH + `git pull` + migrate, orchestrated by `scripts/deploy.sh` modeled on Phase 2's `scripts/deploy-m10.sh`.
- **CI:** none in v1. Deploy is one command from laptop via SSH.
- **Backups:** daily MySQL dump to Google Drive via existing cron pattern from Phase 1.

### 2.3 Repository & code location
- **New repo:** `github.com/sumitdabass/sumit-finance` (private).
- **App root on laptop:** `/Users/Sumit/sumit-finance/`.
- **Plan + spec docs:** live under `docs/superpowers/` inside the IPU/test-project repo for cross-project history, same as Phase 1/2.

### 2.4 Database
- **New DB:** `ipuc_sumit_finance` (on Hostinger MySQL).
- **New user:** `ipuc_sumit_finance` with `ALL PRIVILEGES` on its own DB only.
- **Not shared** with `ipuc_ipuc_davyapp`. Cross-DB joins are disallowed; cross-app data flows only over HTTP (Phase 2 capture endpoint for the Davya side of a withdrawal).

### 2.5 Authentication
- Single user seeded at scaffold: `sumit@davya.local` with a forced-password-reset on first login (Phase 1 pattern).
- TOTP 2FA available from day one (copied from davyascrm).
- Session timeout: 2h idle / 7d max (matches Phase 1).
- Account lockout: 5 bad attempts / 15 min per email (matches Phase 1).
- Force-HTTPS, HSTS, full security headers set from scaffold.

### 2.6 External integrations
- **Slack:** one new channel `#sumit-finance` in the existing workspace. Reuse the existing `Davya Finance Bot` app — add the new channel subscription to its Event Subscriptions scope. No new Slack app, no new token rotation.
- **n8n:** clone the Phase 2 `Davya Finance — Slack → CRM` workflow, rename to `Sumit Personal Finance — Slack → me.ipu.co.in`, retarget the Dispatch code node's POSTs to `me.ipu.co.in/api/personal/*`. Reuse same Gemini API key.
- **Gemini 2.5 Flash:** new prompt (different category set: Payment/Expense/Investment → Expense/Income/Withdrawal) but same `responseSchema` pattern.

### 2.7 Secrets / tokens
- `PERSONAL_CAPTURE_TOKEN` — 32-char hex, generated on prod only, stored in prod `.env`, never committed. Used as `X-Personal-Token` header on all capture endpoints.
- The existing `FINANCE_CAPTURE_TOKEN` (Phase 2) is reused by the withdrawal flow's second HTTP node; no new token on the davyascrm side.

---

## 3. Data model

Four tables total. Deliberately no `ledger_entries` table — with one account ("Sumit"), running balance is trivially computable from `sum(incomes.amount) - sum(expenses.amount)`, and the signed-delta ledger pattern from Phase 2 buys nothing at this scale.

### 3.1 `expense_categories`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(60) UNIQUE | display name |
| slug | varchar(60) UNIQUE | URL-safe key used by Gemini |
| sort_order | smallint | for UI ordering |
| timestamps | | |

**Seed data:** Food, Transport, Utilities, Rent, Subscriptions, Entertainment, Health, Personal Care, Shopping, Education, Travel, Gifts, Other.

### 3.2 `income_sources`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| name | varchar(60) UNIQUE | display name |
| slug | varchar(60) UNIQUE | Gemini-friendly key |
| sort_order | smallint | |
| timestamps | | |

**Seed data:** Davya withdrawal, Salary, Rent received, Interest, Freelance, Gift, Other.

Extending either list is a DB seeder change, not a schema change.

### 3.3 `expenses`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| amount | decimal(12,2) | INR, always positive |
| category_id | FK → expense_categories | constrained, restrict on delete |
| description | varchar(500) nullable | what Gemini extracted / what Sumit typed |
| spent_at | datetime | when the expense was incurred |
| payment_mode | enum('upi','card','cash','bank_transfer','other') nullable | |
| slack_message_id | varchar(50) UNIQUE nullable | `E.<slack_ts>`; null when entered via web form |
| raw_input | text nullable | original Slack message text for audit |
| timestamps | | |

**Indexes:** `(spent_at)`, `(category_id, spent_at)`.

### 3.4 `incomes`

| column | type | notes |
|---|---|---|
| id | bigint unsigned PK | |
| amount | decimal(12,2) | INR, always positive |
| source_id | FK → income_sources | |
| description | varchar(500) nullable | |
| received_at | datetime | |
| slack_message_id | varchar(50) UNIQUE nullable | `I.<slack_ts>`; null on web-form entry |
| raw_input | text nullable | |
| davya_reference | json nullable | when `source = "Davya withdrawal"`: stores the Phase 2 side's `expense_id` + `ledger_entry_ids` for forensic cross-link |
| timestamps | | |

**Indexes:** `(received_at)`, `(source_id, received_at)`.

`davya_reference` is intentionally JSON rather than a column-per-field — we want the cross-link to be auditable but not to drive any joins or constraints (Phase 3 cannot see Phase 2's `expenses` table).

---

## 4. API

All endpoints under `/api/personal/`. Auth = `X-Personal-Token` header checked by `VerifyPersonalToken` middleware (copy of Phase 2's `VerifyFinanceToken`). Rate-limit `throttle:60,1`. Responses follow the Phase 2 contract exactly: 201 on create, 401 on auth fail, 409 `{"error":"duplicate_slack_message","existing_id":X}` on slack_message_id collision, 422 on validation error.

### 4.1 `POST /api/personal/expenses`

Request:
```json
{
  "amount": 450,
  "category": "Transport",         // either name or slug
  "description": "fuel at HP",
  "spent_at": "2026-04-18T18:30:00+05:30",  // optional; defaults to now()
  "payment_mode": "upi",           // optional
  "slack_message_id": "E.1776532286.987379",
  "raw_input": "spent 450 on fuel hp upi"
}
```

Response 201:
```json
{ "id": 42 }
```

### 4.2 `POST /api/personal/incomes`

Request:
```json
{
  "amount": 50000,
  "source": "Davya withdrawal",
  "description": "apr owner drawing",
  "received_at": "2026-04-30T20:00:00+05:30",
  "slack_message_id": "I.1776532286.987379",
  "raw_input": "withdrew 50000 from davya, apr salary",
  "davya_reference": {              // optional; populated by n8n after Phase 2 side commits
    "phase2_expense_id": 17,
    "phase2_slack_message_id": "W.1776532286.987379"
  }
}
```

Response 201 with `{id}`. The `davya_reference` is not required at creation — n8n can PATCH it after the Phase 2 write succeeds, but v1 keeps it simple: n8n runs the Phase 2 call first, then includes the resulting id in the Phase 3 call.

### 4.3 `POST /api/personal/failed`

Mirrors Phase 2's failed-extractions endpoint. Stores `{slack_message_id, raw_input, error_reason}` for post-hoc inspection when Gemini extraction fails or channel-category routing mismatches. No ledger write, no side-effect.

### 4.4 Concurrency + idempotency

Same pattern as Phase 2's M12-fixed controllers: pre-check `slack_message_id` uniqueness, wrap the DB insert in `try/catch QueryException` for 23000 SQLSTATE, re-query on race and return the contract's 409. Regression test per endpoint using the `DB::listen`-outside-savepoint trick (see Phase 2 PaymentCaptureTest for the pattern).

---

## 5. Davya → personal withdrawal flow

This is the single novel data flow in Phase 3 — every other capture is a single endpoint hit. It needs its own description because it writes to two different apps over two different HTTP calls and must stay idempotent under retries.

### 5.1 Happy path

1. Sumit posts in `#sumit-finance`:
   ```
   withdrew 50000 from davya, apr salary
   ```
2. n8n Slack trigger fires for the new message.
3. Gemini extracts:
   ```json
   { "category": "Withdrawal", "amount": 50000, "description": "apr salary" }
   ```
4. n8n's Dispatch Code node sees `category = "Withdrawal"`. Two POSTs in sequence:
    - **Step A:** `POST https://davyas.ipu.co.in/api/finance/expenses` with headers `X-Finance-Token: $FIN`, body:
       ```json
       {
         "amount": 50000,
         "category": "Owner drawing",
         "description": "Davya withdrawal by Sumit — apr salary",
         "paid_at": "<now>",
         "slack_message_id": "W.<slack_ts>",
         "raw_input": "<original slack text>"
       }
       ```
       Phase 2 routes this through `LedgerRoutingService::routeExpense`, which writes `davya: -50000` to `ledger_entries`. Phase 2 returns `{id, ledger_entries: 1}`.
    - **Step B:** `POST https://me.ipu.co.in/api/personal/incomes` with headers `X-Personal-Token: $PER`, body:
       ```json
       {
         "amount": 50000,
         "source": "Davya withdrawal",
         "description": "apr salary",
         "received_at": "<now>",
         "slack_message_id": "I.<slack_ts>",
         "raw_input": "<original slack text>",
         "davya_reference": {
           "phase2_expense_id": <from step A response>,
           "phase2_slack_message_id": "W.<slack_ts>"
         }
       }
       ```
5. On both 201s, n8n reacts ✅ on the source Slack message (existing pattern from Phase 2 finance workflow).

### 5.2 Slack-ts prefixing

Each side uses its own prefix on `slack_message_id` so the unique indexes don't collide across what's conceptually "the same event":
- Phase 2 expense row: `W.<ts>`
- Phase 3 income row: `I.<ts>`

This matches Phase 2's existing convention (`E.`/`I.` for expense/investment) and makes Slack-message provenance obvious when reading either DB.

### 5.3 Failure modes

- **Step A 422 / 500:** n8n routes to the failure branch (post to `#sumit-finance` thread + POST to `me.ipu.co.in/api/personal/failed`). No partial state — Step B never runs.
- **Step A 201 but Step B fails:** this is the dangerous case — Davya is debited but personal income not recorded. n8n captures via IF-201 scoped to both responses; on any step-B failure it POSTs a warning into the thread with the Phase 2 expense id so Sumit can manually reconcile or retry.
- **Retry on same Slack message:** both endpoints are idempotent on their respective `slack_message_id`s — the 409 path returns the existing row id, n8n can safely re-run.

### 5.4 Why n8n orchestrates, not Laravel

Considered: Phase 3's `/api/personal/incomes` endpoint could call davyascrm's capture endpoint internally before writing the income row, making it one atomic HTTP call from n8n. Rejected because:
- Cross-app HTTP from inside Laravel means Phase 3's response time depends on Phase 2's health.
- A network blip between Phase 3 and Phase 2 would require a second app's retry queue.
- n8n is already a retry-aware orchestrator for workflows — that's literally its job.

Keeping the dual-write in n8n makes each Laravel app responsible for exactly its own writes and nothing else.

---

## 6. Slack + n8n + Gemini setup

### 6.1 Slack channel + bot
- New channel: `#sumit-finance` (private, just Sumit + the bot).
- Invite existing `Davya Finance Bot` into the channel.
- Update the Slack app's Event Subscriptions to include the new channel's events (no new scope grants needed — `channels:history` and `groups:history` already granted).

### 6.2 n8n workflow
- Clone `yO0nzgy8KvdneITL` (Phase 2 finance workflow) to a new workflow.
- Slack Trigger node's `channelId` filter → new channel id.
- Gemini prompt edited for personal-finance examples (category set = Expense / Income / Withdrawal). Preserves the Phase 2-M12 fixes: strip `<...|...>` Slack markdown, enforce lowercase enum values, scope 201-IF to `target != 'failed'`, drop bot messages at Dispatch entry, referrer regex fallback removed (N/A for personal), add Withdrawal branch that dual-POSTs per §5.
- New workflow committed to `sumit-finance/docs/n8n-personal-finance-workflow.json` as the source of truth.

### 6.3 Gemini prompt skeleton

Following Phase 2's format — `systemInstruction` with examples and `responseSchema` to force JSON-only output. Key examples:
```
"got 450 on fuel hdfc card" → {category: "Expense", amount: 450, expense_category: "Transport", notes: "fuel hdfc card", payment_mode: "card"}
"rent 25000 to amit" → {category: "Expense", amount: 25000, expense_category: "Rent", notes: "to amit"}
"interest from fd 1200" → {category: "Income", amount: 1200, income_source: "Interest"}
"withdrew 50000 from davya, apr salary" → {category: "Withdrawal", amount: 50000, notes: "apr salary"}
```

---

## 7. Web UI (Filament)

Single Filament admin panel at `/admin`. One user. Single theme emerald (match davyascrm brand).

### 7.1 Pages

**Dashboard (`/admin`):**
- "This month — spending by category" — bar chart widget, bars clickable to drill into Expense list filtered.
- "This month — income by source" — bar chart.
- "Running balance" — line chart, 6-month rolling.
- "Top 5 spends this month" — table widget.

**Resources:**
- `ExpenseResource` at `/admin/expenses` — list (date, category badge, amount INR, description, payment mode, raw input on expand), filters by month and category, "Add expense" button for manual entry, "Export CSV" bulk action.
- `IncomeResource` at `/admin/incomes` — list (date, source badge, amount INR, description, raw input), filters by month and source, "Add income" button.
- Both resources have full CRUD; web-form entries have `slack_message_id = null` and get saved that way. (The DB unique index is nullable — multiple nulls are fine.)

**Admin-only pages:**
- `/admin/categories` — edit/add expense categories (for when the Gemini prompt needs a new one).
- `/admin/sources` — edit/add income sources.

### 7.2 Charts library
Filament widgets use Chart.js; no extra dependency. If a more polished chart is desired later, swap to `filament/widgets` add-on — not blocking v1.

---

## 8. Testing strategy

Direct parallels to Phase 1 + 2. No new patterns invented.

### 8.1 Feature tests (tests/Feature)
- `ExpenseCaptureTest` — 401/409/422/201 coverage + race simulation via `DB::listen` + category-lookup-by-name-or-slug + `slack_message_id` dedup.
- `IncomeCaptureTest` — same coverage matrix, plus `davya_reference` JSON persistence when `source = "Davya withdrawal"`.
- `WithdrawalIncomeTest` — Phase-3-only test of the Withdrawal-shaped POST (`source = "Davya withdrawal"` + populated `davya_reference` JSON). Asserts the row persists the JSON verbatim, and a retry with the same `slack_message_id` returns 409 + the original id. The cross-app double-write with Phase 2 is covered in acceptance (§8.3), not in this unit of tests — the Laravel test suite cannot reach `davyas.ipu.co.in`.
- `FailedCaptureTest` — parallel to Phase 2's.
- Filament resource tests — Livewire feature tests for `ExpenseResource::create()` and `IncomeResource::create()` manual entry (slack_message_id null).

### 8.2 Unit tests (tests/Unit)
- `ExpensePolicyTest`, `IncomePolicyTest` — only Sumit can CRUD.
- `PersonalBalanceServiceTest` — the single math helper that returns `sum(incomes) - sum(expenses)` with optional date-range filters.
- `CategoryResolverTest` — accepts name or slug, case-insensitively; 404 on unknown category.

### 8.3 Acceptance
Same approach as Phase 2 M12: post a real Slack message in each of {Expense, Income, Withdrawal}, watch n8n executions, assert DB rows. Capture in `docs/ACCEPTANCE.md`.

---

## 9. Security posture

Copied wholesale from Phase 1:
- HTTPS forced, HSTS set, CSP locked down.
- Single-session enforcement (logs out other devices on new login).
- Force password reset on first login.
- TOTP 2FA + 10 recovery codes.
- Auth audit log.
- `X-Personal-Token` header lives in prod `.env` at mode 600; never committed.
- No-index / no-archive meta + `X-Robots-Tag` header on all pages.
- Root (`/`) redirects to `/admin`.

No new security design — Phase 1's is already solid and has been live for a week.

---

## 10. YAGNI cuts (explicit)

These are **not** in v1. Each line is a conscious deferral, not an oversight.

| Feature | Why deferred |
|---|---|
| Bank/UPI statement CSV imports | Each bank has a different format; custom parser per bank is fragile and labor-heavy. If Slack capture proves insufficient for volume, revisit as v2. |
| Investment tracking | Phase 2 already has an `investments` table for Davya's holdings. Personal investments could reuse it via HTTP API in v2 — don't duplicate the schema prematurely. |
| Budgets & alerts | Need ≥3 months of real data before budget values are meaningful. v2+. |
| Recurring-transaction auto-creation (rent, subs) | One-time templating can wait; meanwhile Slack capture is fast enough. |
| Multi-currency | INR-only. Non-INR transactions entered in INR-equivalent at time of entry. |
| Mobile app | Filament's responsive layout covers phone usage. Native / PWA wrapper is a v2+ polish item. |
| Family / Sonam access | Single-user locked. Any future sharing requires a fresh brainstorm. |
| Bookkeeper / CA export view | v2 if/when Sumit actually has a CA asking for this. CSV export from `/admin/expenses` and `/admin/incomes` covers manual handoff meanwhile. |
| Receipt image uploads | Drive integration exists (Phase 1 M4) — add in v2 if Sumit starts wanting visual audit trails. |

---

## 11. Milestones (estimate ~40–50 hours total)

Ordered by dependency; each ends with a tag + prod deploy, mirroring Phase 1/2 vertical-slice discipline.

1. **M1 — Scaffold + auth + empty `me.ipu.co.in`.** ~6 hrs. Laravel 11 + Filament install, DB + user migration, auth pages, force-password-reset, TOTP, security headers, deploy script. Tag `v0-scaffold`.
2. **M2 — Expenses.** ~6 hrs. `expense_categories` + `expenses` migrations, `Expense` model + factory + Policy, `ExpenseResource` (list/create/edit), manual-add round-trip test, `/api/personal/expenses` endpoint + `StorePersonalExpenseRequest` + 10-ish feature tests (incl. race). Tag `v1-expenses`.
3. **M3 — Incomes.** ~6 hrs. Parallel to M2 for incomes + sources. Tag `v2-incomes`.
4. **M4 — Dashboard widgets.** ~4 hrs. 4 widgets described in §7.1 + the 1 service (`PersonalBalanceService`). Tag `v3-dashboard`.
5. **M5 — Slack + n8n + Gemini capture for Expense + Income (no Withdrawal yet).** ~8 hrs. Clone workflow, edit Gemini prompt, wire to `/api/personal/expenses` and `/incomes`. Acceptance: Slack → DB end-to-end for both. Tag `v4-slack-capture`.
6. **M6 — Davya withdrawal dual-write.** ~6 hrs. Add Withdrawal branch to n8n workflow (dual-POST per §5). Add `davya_reference` persistence + test. Acceptance: Slack → Phase 2 Davya expense + Phase 3 income + ✅ reaction. Tag `v5-withdrawal`.
7. **M7 — Reports + CSV export + acceptance matrix.** ~3 hrs. CSV export bulk action. Tag `v1.0.0`.

Pre-M1 preflight (Sumit actions, not code):
- Create subdomain `me.ipu.co.in` in Hostinger.
- Create DB `ipuc_sumit_finance` + user with privileges limited to that DB.
- Create private GitHub repo `sumit-finance`.
- Create Slack channel `#sumit-finance` and invite `Davya Finance Bot`.
- Update the Slack app's Event Subscriptions to include the new channel (no new scopes).
- Generate `PERSONAL_CAPTURE_TOKEN` (32 hex chars) and note it for M5.

---

## 12. Non-obvious design rules (carried from Phase 1/2)

- **Fixed generous schema + `extra_notes` / `description` freeform** — AI does not invent new columns. Adding a category is a seeder change, not a migration.
- **Proof/receipt files go to Drive, not DB** (when the feature lands in v2). DB stores the Drive URL only.
- **Daily MySQL dump to Drive** with 30-day retention — built in M1 alongside the deploy script.
- **Phone number is not a personal-finance concept** — no uniqueness constraint on anything user-facing. Slack `ts` does the dedup work.
- **Decimal precision:** `decimal(12,2)` — max ≈ 999M INR, more than enough.
- **Currency is INR only.** Not stored as a column. If multi-currency ever happens, that's a schema change and a fresh brainstorm.

---

## 13. Open items at design time

None blocking. M1 can start as soon as the preflight list in §11 is done.
