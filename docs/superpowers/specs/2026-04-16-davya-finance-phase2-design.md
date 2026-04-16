# Davya Finance — Phase 2 Design

- **Date:** 2026-04-16 (originally drafted as Phase 1; renumbered to Phase 2 on 2026-04-16 after scope reorder — Sumit promoted the CRM ahead of Finance)
- **Status:** Deferred. Approved in brainstorm but not active work; waits on Phase 1 (CRM) completion.
- **Owner:** Sumit Dabas
- **Project:** "Finance Management by Sumit Dabas" — Phase 2 of 3
- **Related specs:** `2026-04-16-davya-crm-phase1-design.md` (active Phase 1)

---

## 1. Purpose

Semi-automated finance system for the Davya consultancy and Sumit's wider operations. Phase 1 captures three transaction types — **consultancy payments**, **operational expenses**, and **investments** — from Slack messages, runs them through a Gemini extraction call, applies rule-based routing, and writes to a MySQL database hosted on the existing IPU Hostinger plan.

The goal of Phase 1 is **a reliable system of record for money movement, with correct head/Davya ledger math**. Everything else — full CRM, sales pipeline, dashboards, multi-team personal finance — is deferred to later phases.

---

## 2. Scope

### In scope (Phase 1)

- Slack-triggered capture from two channels.
- Gemini 2.5 Flash extraction with strict JSON `responseSchema`.
- Routing math: head-based ledgers + Davya pool, freelancer special case.
- MySQL persistence in `ipuc_davyafin` with 7 tables.
- Daily MySQL dump to Google Drive (30-day retention).
- Deduplication via Slack message ID.
- Failed-extraction handling: retry once, then log + reply in Slack.

### Out of scope (deferred)

- Full Student CRM (Father Name, 12th %, course preferences, address, etc.) → **Phase 2**.
- Sales / lead pipeline with stages and conversion tracking → **Phase 3**.
- Sumit's personal multi-team finance (multiple team heads + sub-teams) → **Phase 4**.
- Backup AI fallback model → Phase 2 hardening.
- ₹50k human-in-loop confirmation buttons → Phase 2 hardening.
- Dashboard / reporting UI (Looker, AppSheet, Retool, custom) → separate spec post-Phase-1.
- CRM replacement for Zoho (Zoho is OUT; custom PHP admin on IPU Hostinger is the leading candidate) → separate spec.

---

## 3. Architecture

```
Slack (workspace: Sumit's existing)
  ├── #student-entries   ← team-shared. Heads + members + freelancers post payment events.
  └── #finance-log       ← Sumit only. Expenses, investments, misc income.
            │
            ▼
     n8n workflow  (Sumit's existing instance, off-Hostinger)
            │
            ├── Slack Trigger node
            ├── Gemini Chat node (gemini-2.5-flash, responseSchema)
            ├── Code node (JS) → routing logic
            └── MySQL node → INSERTs into ipuc_davyafin
            ▼
    MySQL: ipuc_davyafin                Google Drive
    ├── referrers                       ├── /Davya Finance/Proofs/
    ├── students                        └── /Davya Finance/Backups/
    ├── payments
    ├── expenses
    ├── investments
    ├── ledger_entries  (source of truth for balances)
    └── failed_extractions
```

---

## 4. Stack (locked)

| Layer | Choice | Why |
|---|---|---|
| Input | Slack, 2 channels | Already connected; team-friendly; nicer interactive UI for Phase 2 confirmations |
| AI extraction | Gemini 2.5 Flash with `responseSchema` | Reuses IPU news scraper key; cheap; native strict JSON |
| Orchestration | n8n (Sumit's existing instance) | Already running for KYNE workflows |
| Database | MySQL on IPU Hostinger (`ipuc_davyafin`) | IPU site doesn't use its DB; zero incremental cost |
| File storage | Google Drive | Native Slack/n8n integration; keeps DB lean |
| **NOT used** | Supabase, Sheets-as-source, multi-agent AI, GitHub for this project | Explicitly ruled out |

---

## 5. Org hierarchy & routing rules (LOCKED)

### 5.1 Hierarchy

- **3 heads:** Sumit, Sonam, Nikhil.
- **Team members report to one head:**
  - Nikhil's team → Nisha
  - Sonam's team → Poonam, Neetu
  - Sumit's team → (open)
- **Freelancers** report to a head, cannot have their own teams. Currently: Kapil under Sumit.
- **Sahil:** referrer name visible in CRM dropdown but team affiliation TBD (open question §10).

### 5.2 Routing rules

When a payment lands for a student:

1. Look up `students.referred_by_id` → `referrers` row.
2. **If `role = 'freelancer'`** → 100% of payment goes to Davya pool. **No** head ledger credit. Manual payout to the freelancer is outside this system.
3. **Else** (`head` or `member`):
   - Resolve `head` = referrer if `role='head'`, else `referrers[head_id]`.
   - If `head.split_pct > 0`:
     - `head_share = amount × split_pct ÷ 100` → credit head's ledger.
     - `davya_share = amount − head_share` → credit Davya pool.
   - If `head.split_pct = 0`: 100% to Davya.

**Current splits:**

| Head | split_pct | Effect |
|---|---|---|
| Sumit | 0 | 100% Davya on every referral by Sumit or his team |
| Sonam | 0 | 100% Davya on every referral by Sonam or her team |
| Nikhil | 60 | 60% Nikhil ledger, 40% Davya on every referral by Nikhil or his team |

Member referrals roll up to their head's split, but the actual referrer name is preserved on the student record so Sumit (admin) can see exactly who brought each student.

### 5.3 Expenses & investments

- **Expense paid:** ledger += (`account='davya'`, `delta = -amount`).
- **Investment out** (capital deployed): ledger += (`davya`, `-amount`).
- **Investment in** (returns received): ledger += (`davya`, `+amount`).

Davya is the only account that funds expenses and investments in Phase 1.

---

## 6. Data model — 7 tables

All tables in MySQL DB `ipuc_davyafin`, accessed by user `ipuc_davyapp`.

**Conventions:**
- All `TIMESTAMP` columns stored in **IST (Asia/Kolkata)**. n8n MySQL connection sets `time_zone = '+05:30'` on connect.
- All money columns are `DECIMAL(12,2)`, INR.
- `referrers.name` uses display case (e.g., "Sumit"); `ledger_entries.account` uses lowercase normalized keys (e.g., `'sumit'`, `'nikhil'`, `'davya'`). The two are not interchangeable.

### 6.1 `referrers`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| name | VARCHAR(60) UNIQUE NOT NULL | Display name |
| role | ENUM('head','member','freelancer') NOT NULL | |
| head_id | INT NULL FK → referrers.id | NULL for heads; for member/freelancer = their head's id |
| split_pct | TINYINT NOT NULL DEFAULT 0 | Only meaningful when role='head'. 0..100. |
| active | BOOLEAN NOT NULL DEFAULT 1 | Soft-delete flag |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

**Seed data:**

| id | name | role | head_id | split_pct |
|---|---|---|---|---|
| 1 | Sumit | head | NULL | 0 |
| 2 | Sonam | head | NULL | 0 |
| 3 | Nikhil | head | NULL | 60 |
| 4 | Nisha | member | 3 | 0 |
| 5 | Poonam | member | 2 | 0 |
| 6 | Neetu | member | 2 | 0 |
| 7 | Sahil | member | NULL | 0 | (TBD which head — open question) |
| 8 | Kapil | freelancer | 1 | 0 |

Note: "Davya" is **not** a referrer row. Davya only appears as a string `'davya'` in `ledger_entries.account`.

### 6.2 `students`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| phone | VARCHAR(15) UNIQUE NOT NULL | Indian phone, digits only, normalized |
| name | VARCHAR(120) NULL | Optional in Phase 1 |
| referred_by_id | INT NOT NULL FK → referrers.id | The actual person, e.g. Nisha — preserved for visibility even when money rolls up to head |
| extra_notes | TEXT NULL | Freeform; AI can write here. **AI must not invent new columns.** |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | |

### 6.3 `payments`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| student_id | INT NOT NULL FK → students.id | |
| amount | DECIMAL(12,2) NOT NULL | INR |
| is_partial | BOOLEAN NOT NULL DEFAULT 0 | |
| received_at | TIMESTAMP NOT NULL | |
| proof_drive_url | VARCHAR(500) NULL | Google Drive file URL |
| slack_message_id | VARCHAR(50) UNIQUE NOT NULL | Dedup key |
| raw_input | TEXT | Original Slack message |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

### 6.4 `expenses`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| amount | DECIMAL(12,2) NOT NULL | |
| category | VARCHAR(60) | e.g. Marketing, Rent, Food, Office |
| description | TEXT | |
| paid_at | TIMESTAMP NOT NULL | |
| slack_message_id | VARCHAR(50) UNIQUE NOT NULL | |
| raw_input | TEXT | |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

### 6.5 `investments`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| asset_name | VARCHAR(80) NOT NULL | e.g. Tata Motors, Binance, Real Estate |
| amount | DECIMAL(12,2) NOT NULL | |
| direction | ENUM('in','out') NOT NULL | 'out' = capital deployed; 'in' = returns received |
| transacted_at | TIMESTAMP NOT NULL | |
| slack_message_id | VARCHAR(50) UNIQUE NOT NULL | |
| raw_input | TEXT | |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

### 6.6 `ledger_entries` (source of truth for balances)

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| account | VARCHAR(60) NOT NULL | 'davya', 'nikhil', or future heads. Lowercase. |
| delta_amount | DECIMAL(12,2) NOT NULL | Signed: positive = credit, negative = debit |
| source_type | ENUM('payment','expense','investment') NOT NULL | |
| source_id | INT NOT NULL | id in the source table; not enforced as FK due to polymorphic shape |
| note | VARCHAR(255) NULL | |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

**Index:** `INDEX idx_account_created (account, created_at)`.

**Balances are derived, never stored:**

```sql
-- Davya pool balance
SELECT SUM(delta_amount) AS davya_balance
FROM ledger_entries WHERE account = 'davya';

-- Nikhil ledger balance
SELECT SUM(delta_amount) AS nikhil_balance
FROM ledger_entries WHERE account = 'nikhil';
```

This is the most important design choice: **no running-balance columns anywhere.** Balances are always reconstructable from the immutable log. Audit trail is free.

### 6.7 `failed_extractions`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| slack_message_id | VARCHAR(50) NOT NULL | NOT unique — same message may fail multiple times across retries |
| slack_channel | VARCHAR(60) | |
| raw_input | TEXT | |
| error_reason | VARCHAR(255) | |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | |

---

## 7. Gemini extraction

- **Model:** `gemini-2.5-flash`.
- **Mode:** `responseSchema` (strict JSON output, no chatty replies).
- **Temperature:** 0.1 (deterministic enough for parsing).

### 7.1 Schema

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

### 7.2 System prompt (skeleton)

> You are a strict JSON extractor for Davya consultancy's finance system. You only output JSON matching the provided schema. If a field is unknown, omit it — never invent data. If the message is ambiguous (e.g., amount unclear, multiple categories possible), set `notes` to explain. Never reply conversationally.

Few-shot examples will cover: payment with referrer name and phone; partial payment; expense with category; investment "in" (return) vs "out" (deployment).

---

## 8. Slack channels

| Channel | Audience | Purpose |
|---|---|---|
| `#student-entries` | Heads + members + freelancers | Posts about new students, payments received, partial payments, proof-of-payment uploads |
| `#finance-log` | Sumit only | Expenses, investments (in/out), misc income |

Both channels are listened to by the same n8n trigger, with the channel name passed into the Code node so routing can validate (e.g., reject Investment posts that arrive on `#student-entries`).

---

## 9. Error handling

| Failure | Behaviour |
|---|---|
| Gemini returns invalid JSON | n8n retries once. If still invalid → row in `failed_extractions` + threaded reply: "❌ Couldn't parse this. Please rephrase or post details manually." |
| Required field missing (e.g., Payment without `amount`) | Same as above; reply specifies what's missing. |
| Unknown referrer name | Reply: "I don't recognize referrer X. Add to roster or correct spelling." No insert. |
| New phone number on a Payment | Auto-create `students` row with `name=null`. If `referrer_name` not provided in the message, reply asking for it. |
| Duplicate `slack_message_id` | Silently skip. No double-write. |
| MySQL connection error | n8n's built-in retry + fallback alert in `#finance-log`. |

---

## 10. Backup

n8n is off-Hostinger and does not have shell access to run `mysqldump` against the IPU DB host. Cleanest mechanism for Phase 1:

- **Trigger:** PHP cron job on IPU Hostinger, daily at 02:00 IST.
- **Action:** PHP script runs `mysqldump ipuc_davyafin | gzip > /home/<user>/private_backups/davyafin-YYYY-MM-DD.sql.gz`, then uploads the gzip to Google Drive `/Davya Finance/Backups/` via the Drive API (using a Google service account JSON credential stored in the IPU site's `.env`, never in the public webroot).
- **Retention:** Same script deletes local dumps older than 7 days and Drive dumps older than 30 days.
- **Why PHP, not n8n:** mysqldump runs on the DB host. PHP cron is the simplest reliable invocation that has shell + DB access in one place.
- **Restore drill:** Documented in implementation plan; run at Phase 1 sign-off and quarterly thereafter.

---

## 11. Security

- DB user `ipuc_davyapp` has privileges only on `ipuc_davyafin`. Not `*.*`.
- Remote MySQL whitelist restricts to n8n's outbound IP only.
- All API keys and DB password live in n8n credentials (not in workflow JSON).
- cPanel API token rotated after the recent leak (verify before go-live).
- No PII (phone numbers, payment amounts) leaves the n8n + MySQL + Drive perimeter.

---

## 12. Open questions

These do not block design approval but must be resolved before / during implementation:

1. **Sahil's head?** — referrer dropdown shows him; needs head_id assignment before he refers a student.
2. **n8n outbound IP** — pending Sumit's dashboard recovery (~5 hrs from 2026-04-16 14:30 IST).
3. **cPanel API token rotation** — Sumit acknowledged warning; not confirmed done.

---

## 13. Definition of Done (Phase 1)

- All 7 tables exist in `ipuc_davyafin` with seed referrers populated.
- n8n workflow live, listening to both Slack channels.
- 10 test messages of each type (Payment / Expense / Investment) parse correctly and produce correct `ledger_entries` rows.
- Daily backup runs and produces a valid, restorable dump.
- Sumit can run a SELECT in phpMyAdmin and see correct Davya & Nikhil balances.
- Spec reviewed and signed off by Sumit (in writing, in this file or a comment thread).

---

## 14. What this design explicitly is NOT

- Not a CRM. Phase 1 stores only `phone + name + referred_by` per student.
- Not multi-agent. One Gemini call per Slack message; deterministic code does everything else.
- Not Sheets-backed. SQL is source of truth from day 1.
- Not a Zoho replacement. That's a separate spec.
- Not a dashboard. Reporting is via raw SELECT in Phase 1; UI comes later.
