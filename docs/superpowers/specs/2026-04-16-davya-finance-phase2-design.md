# Davya Finance — Phase 2 Design

- **Date:** 2026-04-16 (original draft); **revised 2026-04-17** after Phase 1 (davyascrm) shipped and its schema was observable.
- **Status:** Ready for implementation. Revision aligns spec with Phase 1 reality — no more duplicate tables, no separate `referrers` roster, Laravel-owned routing math.
- **Owner:** Sumit Dabas
- **Project:** "Finance Management by Sumit Dabas" — Phase 2 of 3
- **Related:** `2026-04-16-davya-crm-phase1-design.md`, `2026-04-17-davya-lead-capture-design.md`

---

## 0. What changed in the 2026-04-17 revision

The original draft predated Phase 1 shipping. Once Phase 1 was live, five design decisions were reopened and locked to match reality:

| Original spec said | Revised |
|---|---|
| Separate `referrers` table (7 rows) | **Reuse `users`** — add one column `split_pct`. Same 7 people, single source of truth. |
| Minimal new `students` table (phone/name/referred_by) | **Reuse Phase 1 `students`** — already has phone-unique + richer CRM columns. Phase 2 writes only to a subset. |
| Minimal new `payments` table | **Reuse Phase 1 `payments`** — add `slack_message_id` (unique, nullable) + `raw_input` columns. |
| Routing logic in n8n Code node (JavaScript) | **Routing in Laravel** — 3 `/api/finance/*` endpoints + `LedgerRoutingService`. Matches the Lead Capture pattern already proven in Phase 1. |
| DB `ipuc_davyafin` | **DB `ipuc_ipuc_davyapp`** (reality on Hostinger; cPanel doubled the prefix when the subdomain was created). |

Phase 2 still creates 4 new tables: `expenses`, `investments`, `ledger_entries`, `failed_extractions`. Backup reuses Phase 1's existing `backup:database` command unchanged.

---

## 1. Purpose

Semi-automated system of record for Davya money movement. Captures three transaction categories — **Payments**, **Expenses**, **Investments** — from two Slack channels, runs them through Gemini 2.5 Flash for JSON extraction, and persists them into the same MySQL DB as the Phase 1 CRM. Balances (Davya pool, per-head ledgers) are always derivable from the immutable `ledger_entries` log — never stored as running totals.

Phase 2 is a "system of record" goal. Dashboards, reporting UIs, multi-team personal finance, and human-in-the-loop confirmation for large amounts are deferred to later phases.

---

## 2. Scope

### In scope

- Slack ingestion from 2 channels via n8n.
- Gemini 2.5 Flash extraction with strict `responseSchema`.
- 3 Laravel API endpoints under `X-Finance-Token` auth.
- `LedgerRoutingService` implementing head/Davya split math + freelancer rule.
- 4 new tables; 2 existing tables extended (`users`, `payments`).
- Idempotency via `slack_message_id` unique constraint.
- Failed-extraction logging with Slack reply feedback.

### Out of scope

- Dashboard / balance reporting UI (raw `SELECT` or Filament widget in a later phase).
- ₹50k human-in-loop confirmation (Phase 2.1).
- Backup AI fallback model if Gemini is down (Phase 2.1).
- Sumit's personal finance system (Phase 3).
- Full CRM replacements not already covered in Phase 1.

---

## 3. Architecture

```
Slack workspace (Sumit's existing)
  ├── #student-entries    team-shared. Payments only.
  └── #finance-log        Sumit only. Expenses + Investments.
            │
            ▼
n8n workflow "Davya Finance — Slack → CRM"   (sibling to Davyas Lead Capture)
   Slack Trigger (both channels)
     → Gemini Chat (gemini-2.5-flash, responseSchema, temperature 0.1)
     → Code node "Dispatch by category"
         • validates channel ↔ category (Investment on #student-entries → failed_extractions)
         • picks endpoint URL + payload shape
     → HTTP Request → POST https://davyas.ipu.co.in/api/finance/{payments|expenses|investments}
     → IF statusCode == 201 → Slack reaction ✅
        ELIF statusCode == 409 → silent skip (idempotent replay)
        ELSE → write failed_extractions row + threaded reply in Slack
            │
            ▼
Laravel (davya-crm repo, same app as Phase 1)
   routes/api.php   → 3 routes under middleware VerifyFinanceToken
   FormRequest     → validate per endpoint
   Controller      → call LedgerRoutingService inside DB::transaction
   Writes:           1 source row (payment / expense / investment) + N ledger_entries rows
            │
            ▼
MySQL: ipuc_ipuc_davyapp  (localhost; Laravel-only; no remote access)
   users             ← roster, EXTENDED with split_pct
   students          ← Phase 1 table, reused
   payments          ← Phase 1 table + slack_message_id (UNIQUE) + raw_input
   expenses          ← NEW
   investments       ← NEW
   ledger_entries    ← NEW (source of truth for balances)
   failed_extractions← NEW
            │
            ▼
Backup
   Phase 1's existing `php artisan backup:database` (daily at 02:00 IST via cron)
   mysqldump → gzip → upload to Drive /Davya CRM/Backups/
   Retention 7d local / 30d Drive
   Phase 2 new tables included automatically (whole-DB dump).
```

**Properties:**

- No remote-MySQL whitelist needed. n8n only speaks HTTPS to Laravel.
- No duplicate roster table. `users` is the one place to add/remove/reassign people.
- No running-balance columns. `SELECT SUM(delta_amount) FROM ledger_entries WHERE account='davya'`.
- One workflow, one controller per category, one service, one transaction boundary.

---

## 4. Stack (locked)

| Layer | Choice | Why |
|---|---|---|
| Input | Slack, 2 channels | Already part of Sumit's team workflow. |
| AI extraction | Gemini 2.5 Flash with `responseSchema` | Strict JSON, cheap, reuses existing API key. Prompt + schema live in n8n. |
| Orchestration | n8n (Sumit's existing Hostinger instance) | Same instance as KYNE + Davyas Lead Capture. |
| API | Laravel 11 in `davya-crm` repo | Reuses Phase 1 auth middleware pattern, FormRequest validation, PHPUnit. |
| Database | MySQL on IPU Hostinger (`ipuc_ipuc_davyapp`) | Shared with Phase 1. Zero incremental cost. |
| File storage | Google Drive (via Phase 1's existing disk binding) | Only used for payment proofs; expenses/investments stay text-only in Phase 2. |
| **NOT used** | Direct n8n → MySQL, separate `referrers` table, Sheets, Supabase, multi-agent AI | Explicitly ruled out. |

---

## 5. Org hierarchy & routing rules

### 5.1 Hierarchy (mirror of Phase 1)

Source of truth: `users` table, with:
- `team_head_id` (NULL for heads, FK-to-users for members and freelancers)
- `is_freelancer` (boolean; only true for Kapil today)
- Spatie role (`admin`, `head`, `member`, `freelancer`)
- `split_pct` TINYINT UNSIGNED DEFAULT 0 — **new in Phase 2**; only meaningful when role=head.

Current roster (already seeded in Phase 1):

| Name | Role | team_head_id | is_freelancer | split_pct (post-Phase 2 seed) |
|---|---|---|---|---|
| Sumit | admin + head | NULL | false | 0 |
| Sonam | head | NULL | false | 0 |
| Nikhil | head | NULL | false | **60** |
| Nisha | member | Nikhil | false | 0 |
| Poonam | member | Sonam | false | 0 |
| Neetu | member | Sonam | false | 0 |
| Kapil | freelancer | Sumit | **true** | 0 |

Phase 2's migration seeds only Nikhil's `split_pct = 60`. Others stay at the default 0.

### 5.2 Routing rules

For each Payment processed by `LedgerRoutingService::routePayment`:

```
referrer = $student->referrer            // User row; student's referrer_id already resolved in Phase 1 CRM or at student creation

IF referrer->is_freelancer == true:
  ledger_writes = [ ('davya', +amount, 'freelancer referral') ]

ELSE:
  head = referrer->teamHead ?? referrer   // climb to head if member; else head = self
  split = head->split_pct                 // 0..100

  IF split == 0:
    ledger_writes = [ ('davya', +amount, "head $head->name has 0% split") ]
  ELSE:
    head_share  = round(amount * split / 100, 2)
    davya_share = amount - head_share
    ledger_writes = [
      ( strtolower(head->name), +head_share, "head share $split%" ),
      ( 'davya',                +davya_share, "davya share" ),
    ]
```

For Expense: `ledger_writes = [ ('davya', -amount, 'expense: '.$category) ]`.
For Investment: `ledger_writes = [ ('davya', sign * amount, "investment $direction: $asset_name") ]` where `sign = +1 if direction=='in' else -1`.

**Account name convention:**
- `ledger_entries.account` is ALWAYS lowercase.
- For heads, it's `strtolower($user->name)`: `'sumit'`, `'sonam'`, `'nikhil'`.
- `'davya'` is not a user — it's the fixed pool account name.

### 5.3 Worked examples

| Input | Referrer chain | ledger_entries writes |
|---|---|---|
| Payment ₹50,000, student.referrer=Nisha | Nisha (member) → Nikhil (head, 60%) | `('nikhil', +30000)`, `('davya', +20000)` |
| Payment ₹50,000, student.referrer=Poonam | Poonam (member) → Sonam (head, 0%) | `('davya', +50000)` |
| Payment ₹30,000, student.referrer=Kapil | Kapil (freelancer) | `('davya', +30000)` |
| Payment ₹50,000, student.referrer=Nikhil (direct) | Nikhil (head, 60%) | `('nikhil', +30000)`, `('davya', +20000)` |
| Expense ₹5,000 (category: Marketing) | — | `('davya', -5000)` |
| Investment out ₹100,000 (Tata Motors) | — | `('davya', -100000)` |
| Investment in ₹120,000 (Tata Motors returns) | — | `('davya', +120000)` |

### 5.4 Balance queries

Balances are derived, never stored:

```sql
-- Davya pool
SELECT SUM(delta_amount) FROM ledger_entries WHERE account = 'davya';

-- Nikhil's ledger
SELECT SUM(delta_amount) FROM ledger_entries WHERE account = 'nikhil';
```

The `INDEX idx_account_created (account, created_at)` keeps these fast.

---

## 6. Data model

### 6.1 Existing tables — migrations add columns only

```sql
-- users: add split_pct
ALTER TABLE users
  ADD COLUMN split_pct TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER team_head_id;

-- payments: add Slack dedup + raw_input, relax recorded_by_user_id
ALTER TABLE payments
  ADD COLUMN slack_message_id VARCHAR(50) NULL AFTER recorded_by_user_id,
  ADD COLUMN raw_input        TEXT        NULL AFTER slack_message_id,
  ADD UNIQUE KEY payments_slack_message_id_unique (slack_message_id),
  MODIFY COLUMN recorded_by_user_id BIGINT UNSIGNED NULL;   -- was NOT NULL in Phase 1; Slack-originated rows have NULL
```

`slack_message_id` is nullable because Phase 1 CRM-created payments (hand-entered via Filament) don't have one. The UNIQUE constraint still holds because MySQL allows multiple NULL values in a UNIQUE column.

`recorded_by_user_id` becoming nullable is the deliberate choice documented in §8.4 — Slack-originated payments don't have a CRM user attached, and seeding a synthetic "Slack Bot" user was rejected as roster pollution.

### 6.2 `expenses` (new)

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| amount | DECIMAL(12,2) NOT NULL | Always positive; sign is applied by ledger. |
| category | VARCHAR(60) NULL | E.g. "Marketing", "Rent", "Food". |
| description | TEXT NULL | |
| paid_at | TIMESTAMP NOT NULL | IST. |
| slack_message_id | VARCHAR(50) NOT NULL UNIQUE | Dedup key. |
| raw_input | TEXT NULL | Original Slack message for audit. |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | |

### 6.3 `investments` (new)

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| asset_name | VARCHAR(80) NOT NULL | E.g. "Tata Motors", "Real Estate #12". |
| amount | DECIMAL(12,2) NOT NULL | Always positive; sign applied by ledger. |
| direction | ENUM('in','out') NOT NULL | `out` = capital deployed; `in` = returns received. |
| transacted_at | TIMESTAMP NOT NULL | IST. |
| slack_message_id | VARCHAR(50) NOT NULL UNIQUE | |
| raw_input | TEXT NULL | |
| created_at / updated_at | timestamps | |

### 6.4 `ledger_entries` (new — source of truth for balances)

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| account | VARCHAR(60) NOT NULL | Lowercase. `'davya'` or `strtolower(user.name)`. |
| delta_amount | DECIMAL(12,2) NOT NULL | Signed: positive = credit, negative = debit. |
| source_type | ENUM('payment','expense','investment') NOT NULL | |
| source_id | INT NOT NULL | id in the source table. Polymorphic, intentionally not FK. |
| note | VARCHAR(255) NULL | Human-readable; e.g. "head share 60%". |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

Indexes:
- `INDEX idx_account_created (account, created_at)` — for balance queries.
- `INDEX idx_source (source_type, source_id)` — for reconstructing which ledger rows came from which source row.

### 6.5 `failed_extractions` (new)

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| slack_message_id | VARCHAR(50) NOT NULL | NOT unique — same message may fail multiple times across retries. |
| slack_channel | VARCHAR(60) NULL | e.g. `'#student-entries'`. |
| raw_input | TEXT NULL | |
| error_reason | VARCHAR(255) NULL | |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

---

## 7. Gemini extraction

### 7.1 Schema (unchanged from original spec)

```json
{
  "type": "object",
  "properties": {
    "category":             { "type": "string", "enum": ["Payment", "Expense", "Investment"] },
    "amount":               { "type": "number" },
    "student_phone":        { "type": "string" },
    "student_name":         { "type": "string" },
    "referrer_name":        { "type": "string" },
    "asset_name":           { "type": "string" },
    "investment_direction": { "type": "string", "enum": ["in", "out"] },
    "expense_category":     { "type": "string" },
    "is_partial":           { "type": "boolean" },
    "notes":                { "type": "string" }
  },
  "required": ["category", "amount"]
}
```

### 7.2 System prompt (skeleton — final prompt lives in n8n)

> You are a strict JSON extractor for Davya consultancy's finance system. You only output JSON matching the provided schema. If a field is unknown, omit it — never invent data. If the message is ambiguous (e.g., amount unclear, multiple categories possible), set `notes` to explain. Never reply conversationally.

Few-shot examples included in the prompt:

- Payment: `"got 50k from priya 9999911111, ref nisha"` → `{category:"Payment", amount:50000, student_phone:"9999911111", student_name:"priya", referrer_name:"nisha"}`
- Partial payment: `"priya paid 20k more, pending 30k"` → `{category:"Payment", amount:20000, student_name:"priya", is_partial:true}`
- Expense: `"paid 5k for fb ads"` → `{category:"Expense", amount:5000, expense_category:"Marketing", notes:"fb ads"}`
- Investment out: `"bought 100k tata motors"` → `{category:"Investment", amount:100000, asset_name:"Tata Motors", investment_direction:"out"}`
- Investment in: `"tata motors paid 120k"` → `{category:"Investment", amount:120000, asset_name:"Tata Motors", investment_direction:"in"}`

### 7.3 Temperature

`0.1` — deterministic enough for this parsing job without being rigidly brittle.

---

## 8. API endpoints (Laravel)

All under the `api` middleware group; all require `X-Finance-Token` header matching env `FINANCE_CAPTURE_TOKEN`. Return JSON only.

### 8.1 `POST /api/finance/payments`

Request body (JSON):

| Field | Type | Required | Notes |
|---|---|---|---|
| student_phone | string | yes | 10 digits, digits-only normalization same as Lead Capture |
| amount | number | yes | Positive, ≤ 10 million |
| student_name | string | no | Only used when phone is new |
| referrer_name | string | no | Only used when phone is new; must match a `users.name` case-insensitive |
| is_partial | boolean | no | Default false; maps to `payments.type = 'partial'` / `'full'` |
| received_at | ISO8601 datetime | no | Default: server `now()` |
| slack_message_id | string | yes | Used for idempotency |
| raw_input | string | no | Original Slack text; stored on `payments.raw_input` |

**Processing:**

1. Validate + normalize phone.
2. Find Student by phone. If found, use their stored `referrer_id` and `owner_id` — **ignore request's `referrer_name`**. If not found, `referrer_name` is required → resolve user by name (case-insensitive); if unknown → 422. Then create Student with name + referrer + owner via the same ownership derivation used by Lead Capture.
3. Check for existing `payments.slack_message_id` → if duplicate, return 409 with `existing_id`.
4. Inside `DB::transaction`:
   - Create Payment row (`type` derived from `is_partial`; `recorded_by_user_id = null` on Slack-originated rows — or a dedicated system user; see 8.4).
   - Call `LedgerRoutingService::routePayment($payment)` → returns array of ledger deltas.
   - Insert N `ledger_entries` rows.
5. Return 201 with `{id, ledger_entries: N}`.

### 8.2 `POST /api/finance/expenses`

| Field | Type | Required | Notes |
|---|---|---|---|
| amount | number | yes | Positive |
| category | string | no | Max 60 chars |
| description | string | no | |
| paid_at | ISO8601 datetime | no | Default: `now()` |
| slack_message_id | string | yes | |
| raw_input | string | no | |

Writes 1 expense row + 1 ledger_entries row `('davya', -amount, ...)`.

### 8.3 `POST /api/finance/investments`

| Field | Type | Required | Notes |
|---|---|---|---|
| asset_name | string | yes | Max 80 chars |
| amount | number | yes | Positive |
| direction | enum('in','out') | yes | |
| transacted_at | ISO8601 datetime | no | Default: `now()` |
| slack_message_id | string | yes | |
| raw_input | string | no | |

Writes 1 investment row + 1 ledger_entries row `('davya', ±amount, ...)`.

### 8.4 `POST /api/finance/failed` (internal, used by n8n failure arm)

Writes a `failed_extractions` row. Same `X-Finance-Token` auth. Keeps n8n from needing any DB access.

| Field | Type | Required | Notes |
|---|---|---|---|
| slack_message_id | string | yes | Not unique across failures |
| slack_channel | string | no | e.g. `'#student-entries'` |
| raw_input | string | no | Original Slack text |
| error_reason | string | yes | Human-readable reason from n8n or Laravel |

Returns 201 on write. No ledger writes.

### 8.5 `recorded_by_user_id` for Slack-originated Payments

Phase 1's `payments.recorded_by_user_id` is NOT NULL. Phase 2 Slack flow doesn't know which user posted the message (we'd need Slack user → CRM user mapping, out of scope). Decision:

- The migration makes `recorded_by_user_id` **nullable** (changed from Phase 1's NOT NULL). Slack-originated rows have `NULL`, hand-entered CRM rows keep the Filament-logged user id.
- The Filament UI remains unchanged; the default-on-create behavior keeps it populated for all manual paths.

Alternative (rejected): dedicated "Slack Bot" user seeded into `users`. Rejected because it pollutes the roster and the referrer/owner dropdowns.

### 8.6 Response codes

| Code | Meaning | Body |
|---|---|---|
| 201 Created | Success | `{"id": <source_row_id>, "ledger_entries": <count>}` |
| 401 Unauthorized | Missing/invalid token | `{"error": "unauthorized"}` |
| 409 Conflict | Duplicate `slack_message_id` | `{"error": "duplicate_slack_message", "existing_id": <id>}` |
| 422 Unprocessable | Validation failure | `{"message": "The given data was invalid.", "errors": {...}}` |
| 500 Internal | Unexpected | generic Laravel response |

Rate limit: default 60 req/min (same as `/api/leads`). n8n naturally stays well under this.

---

## 9. n8n workflow

**Name:** "Davya Finance — Slack → CRM"
**Location:** Same n8n instance as KYNE + Davyas Lead Capture (`n8n.srv1117424.hstgr.cloud`).

### 9.1 Nodes

| # | Node | Purpose |
|---|---|---|
| 1 | Slack Trigger | Subscribe to `message.channels` on both channel IDs |
| 2 | Gemini Chat | gemini-2.5-flash, responseSchema=§7.1, system prompt=§7.2, temperature 0.1 |
| 3 | Code "Dispatch by category" | Validates channel↔category (Investment on #student-entries → fail). Selects endpoint URL + payload shape. |
| 4 | HTTP Request | POST to selected URL, header `X-Finance-Token` |
| 5 | IF | statusCode == 201 |
| 6a | Slack "Add Reaction ✅" | On success, react on the original message |
| 6b | IF | statusCode == 409 (idempotent replay — silent skip) |
| 6c | Slack "Reply in Thread" | On other failures, reply with error summary |
| 7 | HTTP Request (failure arm) | POST to internal endpoint (or direct DB — see 9.3) to write `failed_extractions` row |

### 9.2 Channel ↔ category validation (Code node §3)

```js
const category = $json.category;
const channel  = $('Slack Trigger').item.json.channel;

if (channel === process.env.STUDENT_ENTRIES_CHANNEL_ID && category !== 'Payment') {
  return { __failed: true, reason: `Only Payments allowed in #student-entries; got ${category}` };
}
if (channel === process.env.FINANCE_LOG_CHANNEL_ID && !['Expense','Investment'].includes(category)) {
  return { __failed: true, reason: `Only Expenses/Investments allowed in #finance-log; got ${category}` };
}
// else: build endpoint URL + payload
```

### 9.3 Writing `failed_extractions`

Via `POST /api/finance/failed` (defined in §8.4). Alternative of direct MySQL writes from n8n was rejected — it would reintroduce the remote-MySQL whitelist we got to delete.

### 9.4 Credentials in n8n

- Slack OAuth2 (new — see §10 pre-flight).
- Gemini API key (reuse existing KYNE credential, or a new one if key rotation is desired).
- HTTP Request credential: simple "header auth" with `X-Finance-Token` value.

### 9.5 Workflow JSON committed to `davya-crm` repo

At `docs/n8n-finance-workflow.json`. Not activated by the repo; imported into n8n via Public API (same pattern as Davyas Lead Capture).

---

## 10. Slack pre-flight (blocker — before Task 1)

Sumit must complete these before implementation starts (they need a browser + a human account):

1. Slack workspace → Apps → Build → **create app "Davya Finance Bot"**.
2. OAuth & Permissions → Bot Token Scopes: `channels:history`, `channels:read`, `chat:write`, `reactions:write`.
3. Event Subscriptions → enable → subscribe to `message.channels`.
4. Install app to workspace → copy **Bot User OAuth Token** (`xoxb-…`).
5. Create channels **`#student-entries`** and **`#finance-log`** (or reuse existing). Invite the bot into both: `/invite @davya-finance-bot`.
6. Record the 2 channel IDs (begin with `C`).
7. Generate `FINANCE_CAPTURE_TOKEN`: `openssl rand -hex 16`. Store in `davya-crm/.env` on prod.

Output: bot token + 2 channel IDs + finance token. All three are needed to import + activate the n8n workflow.

---

## 11. Error handling

| Failure | Behaviour |
|---|---|
| Gemini returns invalid JSON | n8n retries the Gemini node once. Still invalid → write failed_extractions + threaded Slack reply: "❌ Couldn't parse. Please rephrase or post details manually." |
| Required field missing (e.g. Payment without amount) | Same — specific reason in reply. |
| Unknown referrer for new student phone | Laravel returns 422; n8n reply: "I don't recognize referrer *X*. Add to roster or correct spelling." |
| New phone on a Payment without `referrer_name` | 422; reply: "New phone but no referrer — please repost with '... ref <name>'". |
| Duplicate `slack_message_id` | Laravel returns 409; n8n treats as success; no reply, no reaction, idempotent replay is safe. |
| Channel ↔ category mismatch (spec 9.2) | n8n fails fast, writes failed_extractions, Slack reply names the expected channel. |
| Laravel 5xx | n8n retries HTTP 3x with backoff; after max retries, threaded reply: "⚠️ DB write failing — Sumit, check the app." |

---

## 12. Security

- `FINANCE_CAPTURE_TOKEN` is separate from `LEAD_CAPTURE_TOKEN` — one leak doesn't compromise both.
- DB user `ipuc_ipuc_davyapp` privileges already scoped to its own DB (Phase 1 state).
- No remote-MySQL whitelist required (n8n → HTTPS → Laravel only).
- Gemini + Slack credentials live in n8n credentials, not in workflow JSON.
- Slack bot's scope is limited to `chat:write` + `reactions:write`; it cannot read DMs or other channels.
- `raw_input` may contain phone numbers — kept inside MySQL, never exported.

---

## 13. Testing strategy

TDD, per-task. Gemini is never called from tests; tests hit the Laravel API with Gemini-shaped JSON directly.

**Unit tests** — `tests/Unit/LedgerRoutingServiceTest.php` covering every branch of §5.2 math.

**Feature tests** — one file per endpoint:
- `tests/Feature/PaymentCaptureTest.php`
- `tests/Feature/ExpenseCaptureTest.php`
- `tests/Feature/InvestmentCaptureTest.php`

Each covers: happy 201, 401 missing/wrong token, 422 missing required fields, 409 duplicate `slack_message_id`, atomicity under forced failure mid-transaction.

**Integration smoke** — `tests/Feature/BalanceReconstructionTest.php`: after running a realistic mixed sequence (10 payments, 5 expenses, 3 investments), `SUM(delta_amount)` per account matches the expected values.

**Coverage target:** 100% of `LedgerRoutingService` (small, deterministic). Controllers: happy path + every documented failure. Target ~30–40 new tests; Phase 1 has 91 / 278 assertions — Phase 2 brings total to ~125 / ~400.

**Not tested:** the Slack Events API, the Gemini call, the n8n workflow itself. Those are operational concerns, verified by the spec §14 acceptance smoke test.

---

## 14. Definition of Done

Phase 2 ships when:

- [ ] All 4 new tables exist in prod; 2 existing tables have new columns.
- [ ] All new tests green locally + in CI.
- [ ] 3 API endpoints live behind `X-Finance-Token`.
- [ ] n8n workflow imported + activated; Slack bot replying in threads; reactions firing on success.
- [ ] 10 test messages of each type (Payment / Expense / Investment) land correctly in the right tables and produce correct `ledger_entries`.
- [ ] `SELECT SUM(delta_amount) FROM ledger_entries WHERE account='davya'` and `…WHERE account='nikhil'` return correct running balances for the test inputs.
- [ ] `docs/FINANCE_API.md` + `docs/n8n-finance-workflow.json` committed.
- [ ] Daily backup includes the 4 new tables (verified by a restore drill).
- [ ] `v2.0.0` tag cut on davya-crm repo.

---

## 15. What this design explicitly is NOT

- Not a dashboard. Balances are queryable via raw SQL in Phase 2.
- Not multi-agent. One Gemini call per Slack message; deterministic PHP does everything else.
- Not a generic expense tracker. Constrained to Davya + heads; Sumit's personal finance is Phase 3.
- Not a replacement for the Filament CRM's manual payment entry. The Filament path stays; Slack is an alternate ingress.
