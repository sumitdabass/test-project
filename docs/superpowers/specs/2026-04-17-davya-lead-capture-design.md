# Davya CRM — Lead Capture (Google Form → n8n → CRM)

- **Date:** 2026-04-17
- **Status:** Scoped extension to davyascrm Phase 1 (spec §2 originally said "manual entry only"; this adds one automated lead-capture path).
- **Owner:** Sumit Dabas
- **URL:** `https://davyas.ipu.co.in`

---

## 1. Purpose

Give Davya a zero-cost, zero-login way to capture new leads without anyone opening the CRM. Inbound leads flow: **Google Form → Google Sheet → n8n (poll) → CRM `POST /api/leads` → Student row at stage `Lead Captured`**. Davya keeps doing the rest of the pipeline manually in Filament.

---

## 2. Scope

### In scope
- New Google Form with 10 fields.
- n8n workflow (Google Sheets Trigger → HTTP Request → Slack-on-error).
- CRM endpoint `POST /api/leads` with token header auth.
- CRM-side owner/referrer derivation from a referrer name string.
- API contract doc (`davya-crm/docs/LEAD_CAPTURE_API.md`) for n8n wiring.

### Out of scope (deferred)
- WhatsApp Business API → later; swap the n8n trigger node when ready.
- Public web form embedded on davyas.ipu.co.in → later.
- LLM parsing of unstructured text → not needed while the form is structured.
- Bidirectional sync (CRM → Sheet) → not needed; CRM is the source of truth after ingest.
- Duplicate detection on phone → caught by DB uniqueness; no de-dup merge UI in v1.

---

## 3. Architecture

```
Google Form ──(auto)──▶ Google Sheet "Form Responses 1"
                                │
                                │ 1-minute polling
                                ▼
                        n8n Sheets Trigger
                                │
                                ▼
                        n8n HTTP Request
                     (POST, X-Lead-Token header)
                                │
                                ▼
              CRM: POST /api/leads  (this spec)
                                │
                                ├──▶ token middleware
                                ├──▶ StoreLeadRequest validation
                                ├──▶ LeadController@store
                                │     ├─ referrer lookup by name
                                │     ├─ derive owner_id from head/self
                                │     └─ Student::create(stage='Lead Captured', ...)
                                ▼
                        201 { id, stage, owner }
```

Latency budget: ≤60 s form-submit to CRM row (dominated by n8n poll interval). Acceptable for Davya's lead cadence (~1–5 leads/day).

---

## 4. Google Form — field schema

All fields below land in the linked Sheet in the order listed.

| # | Question label | Required | Type | Maps to Student column |
|---|---|---|---|---|
| 1 | Phone number | ✓ | Short answer (regex: 10 digits) | `phone` |
| 2 | Full name | ✓ | Short answer | `name` |
| 3 | Father's name |  | Short answer | `father_name` |
| 4 | Alternate phone |  | Short answer | `phone_2` |
| 5 | Exam appeared |  | Dropdown: IPU CET, CUET, JEE, Other | `exam_appeared` |
| 6 | 12th marks (% or CGPA) |  | Short answer | `twelfth_marks` |
| 7 | Category |  | Dropdown: Delhi, Outside | `category` |
| 8 | Course interested in |  | Short answer | `course` |
| 9 | Who referred you? | ✓ | Dropdown (see §5) | → owner+referrer derivation |
| 10 | Anything else we should know? |  | Paragraph | `description` |

Form submit button label: "Submit". Confirmation: "Thanks! We'll call you shortly."

---

## 5. Referrer dropdown (8 options)

| Label | CRM lookup | Result |
|---|---|---|
| Sumit | user `sumit@davya.local` | referrer=Sumit, owner=Sumit (head) |
| Sonam | user `sonam@davya.local` | referrer=Sonam, owner=Sonam (head) |
| Nikhil | user `nikhil@davya.local` | referrer=Nikhil, owner=Nikhil (head) |
| Nisha | user `nisha@davya.local` | referrer=Nisha, owner=Nikhil (her head) |
| Poonam | user `poonam@davya.local` | referrer=Poonam, owner=Sonam (her head) |
| Neetu | user `neetu@davya.local` | referrer=Neetu, owner=Sonam (her head) |
| Kapil | user `kapil@davya.local` | referrer=Kapil, owner=Sumit (his head) |
| Walk-in / Self | — | referrer=null, owner=Sumit (admin default) |

Any string not matching the first 7 or literally `Walk-in / Self` → fall through to `referrer=null, owner=Sumit` (safe default, prevents silent data loss).

---

## 6. CRM endpoint — `POST /api/leads`

### 6.1 Request

```http
POST /api/leads HTTP/1.1
Host: davyas.ipu.co.in
Content-Type: application/json
X-Lead-Token: <32-char hex secret from .env LEAD_CAPTURE_TOKEN>

{
  "phone": "9999911111",
  "name": "Ankit Sharma",
  "father_name": "Mr. Sharma",
  "phone_2": null,
  "exam_appeared": "IPU CET",
  "twelfth_marks": "85%",
  "category": "Delhi",
  "course": "BCA",
  "referrer_name": "Nisha",
  "description": "Called from google form"
}
```

### 6.2 Validation (`StoreLeadRequest`)

- `phone` — required, string, exactly 10 digits after stripping non-digits.
- `name` — required, string, max 120.
- `father_name` — nullable, string, max 120.
- `phone_2` — nullable, string, 10 digits after stripping.
- `exam_appeared` — nullable, in `[IPU CET, CUET, JEE, Other]`.
- `twelfth_marks` — nullable, string, max 20.
- `category` — nullable, in `[Delhi, Outside]`.
- `course` — nullable, string, max 80.
- `referrer_name` — required, string, max 60.
- `description` — nullable, string, max 2000.

Phone is normalized to digits-only before save (form may submit `+91 99999 11111`).

### 6.3 Responses

**201 Created**
```json
{
  "id": 42,
  "stage": "Lead Captured",
  "owner": "Nikhil",
  "referrer": "Nisha"
}
```

**401 Unauthorized** — missing/invalid `X-Lead-Token`. Body: `{"error":"unauthorized"}`.

**422 Unprocessable Entity** — validation failure. Body: standard Laravel `{"message":"…","errors":{"field":["…"]}}`.

**409 Conflict** — phone already exists. Body: `{"error":"duplicate_phone","existing_id":12}`. n8n's Slack-on-error node fires on 409 so someone reviews.

**500** — unexpected. Generic body; detail in `storage/logs/laravel.log`.

### 6.4 Token auth

- Single shared secret stored in `.env` as `LEAD_CAPTURE_TOKEN`.
- 32-char hex generated via `bin2hex(random_bytes(16))`.
- Compared via `hash_equals()` (constant-time) in a dedicated middleware `VerifyLeadToken`.
- Token rotation = edit `.env`, `php artisan config:cache`, update n8n credential. No DB change.
- Rate limit: `throttle:60,1` (60 req/min per IP) — fine for Davya's volume, stops runaway loops.

---

## 7. Owner/referrer derivation (CRM-side)

Implemented in `LeadController@store`:

```
referrer = User::whereRaw('LOWER(name) = ?', [strtolower($referrer_name)])->first()
if ($referrer === null || $referrer_name === 'Walk-in / Self'):
    owner_id    = User::role('admin')->firstOrFail()->id   // = Sumit
    referrer_id = null
else:
    referrer_id = $referrer->id
    owner_id    = $referrer->team_head_id ?? $referrer->id   // member → head; head → self
```

Match is case-insensitive on `name`. Match ignores email/role to avoid brittle coupling.

---

## 8. Security

- HTTPS-only (already enforced by LiteSpeed + AutoSSL on `davyas.ipu.co.in`).
- CSRF exempt for `/api/leads` (it's a stateless server-to-server call with its own token).
- `VerifyLeadToken` middleware runs before FormRequest.
- No session, no cookies set (stateless).
- IP throttle `60/min` prevents log flooding if token leaks until we rotate.
- Request body is logged with `Log::info()` (minus the token header) for auditability. Student create events also land in `activity_log` via Spatie activitylog.
- `X-Lead-Token` never echoed in responses.

---

## 9. Testing (Pest/PHPUnit)

Feature tests in `tests/Feature/LeadCaptureTest.php`:

1. `valid token + valid payload creates Student at Lead Captured with derived owner/referrer`
2. `member referrer → owner = team_head`
3. `head referrer → owner = self`
4. `Walk-in / Self option → referrer_id null, owner = Sumit`
5. `unknown referrer name → referrer_id null, owner = Sumit`
6. `missing token → 401`
7. `wrong token → 401`
8. `missing phone → 422`
9. `missing name → 422`
10. `invalid category → 422`
11. `phone normalized to digits before save (+91 99999 11111 → 9999911111)`
12. `duplicate phone → 409 with existing_id`

Seed UsersSeeder + RolesSeeder in each test via `RefreshDatabase` so all 7 team members + roles exist.

---

## 10. Deploy

- New file `.env.example`: `LEAD_CAPTURE_TOKEN=` (empty in example).
- Prod `.env`: generate `LEAD_CAPTURE_TOKEN=$(openssl rand -hex 16)` during deploy; write to `.env`; `config:cache`.
- Token shared with Sumit after deploy (copy-paste into n8n credential when he sets up the workflow).
- Existing deploy recipe in `DEPLOY.md` covers the rest — this adds one migration-less commit.

---

## 11. Done definition (CRM-side of this spec)

- All 12 feature tests green locally.
- Full existing suite still green (no regressions on the 31 prior tests).
- Deployed to `davyas.ipu.co.in`; smoke test: `curl -H "X-Lead-Token: …" …/api/leads` returns 201 and Student row appears in `/admin/students`.
- `docs/LEAD_CAPTURE_API.md` exists in davya-crm repo documenting the endpoint for Sumit's n8n wiring later.
- Spec committed to IPU repo; memory updated with token location + API path.

## 12. Done definition (full — when Sumit sets up Form + n8n later)

- Google Form with 10 fields live; edit link owned by Sumit.
- n8n workflow imported, active on `n8n.srv1117424.hstgr.cloud`, credential holds `LEAD_CAPTURE_TOKEN`.
- End-to-end test: Sumit submits form → Student appears in `/admin/students` within 60s at stage `Lead Captured` with correct owner/referrer.

---

## 13. What this spec is NOT

- Not a generic lead-scoring or enrichment system. No Clearbit/etc.
- Not a CRM-to-Sheet writeback. CRM is the source of truth after ingest.
- Not a replacement for manual entry. Filament "New Student" button stays exactly as-is for walk-ins, WhatsApp, phone calls.
- Not an auth system for Davya's team. This endpoint is a single-purpose token; it cannot create users, read students, or do anything besides create one Student per call.
