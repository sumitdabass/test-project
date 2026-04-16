# Davya CRM — Phase 1 Design

- **Date:** 2026-04-16
- **Status:** Approved via brainstorm; pending written sign-off
- **Owner:** Sumit Dabas
- **Project:** "Finance Management by Sumit Dabas" — Phase 1 of 3 (reordered 2026-04-16)
- **URL:** `davyas.ipu.co.in` (subdomain of existing IPU Hostinger plan)

---

## 1. Purpose

Internal-only CRM for the Davya consultancy, replacing Zoho. Handles the full student lifecycle: lead capture → office meeting → onboarding → IPU counselling (multiple rounds across three tracks) → admission or closure. Built with Laravel + Filament, hosted on Sumit's existing IPU Hostinger plan, shares the same MySQL database as the future Phase 2 Finance module.

Phase 1 is **manual entry via Filament** only. Slack + Gemini automation and Davya/Nikhil ledger math are Phase 2.

---

## 2. Scope

### In scope (Phase 1)

- Laravel + Filament app at `davyas.ipu.co.in`.
- Email + password auth, invite-based user creation.
- Role-based access: Admin / Head / Member / Freelancer.
- Single pipeline (`NewAdmission`) with 10 stages.
- Student records with ~30 Zoho-style fields.
- Round history tracking for 9 IPU counselling round types.
- Payments table (advance / partial / full / refund) with signed amounts.
- Optional proof-of-payment file uploads to Google Drive.
- Kanban board with live aggregates per column (deal / received / pending / count).
- Dashboard alerts: seat-fee-pending, re-entry candidates, stuck leads.
- Encrypted storage of student IPU portal passwords with on-demand "Show Password" in UI.
- Default Filament mobile responsiveness.
- Daily MySQL backup to Google Drive (shared with Phase 2 spec).

### Out of scope (deferred)

- Davya / Nikhil ledger routing math → **Phase 2** (`2026-04-16-davya-finance-phase2-design.md`).
- Expenses and investments modules → Phase 2.
- Slack trigger + Gemini auto-extraction → Phase 2.
- Sumit's personal expense ledger → Phase 3.
- Custom fields admin (user adds new columns via UI) → future.
- Advanced reports/exports beyond Filament defaults → future.
- Data migration from legacy Google Sheet → **not happening** (team starts fresh).
- Native mobile apps → not planned.
- Student-facing login → not planned; internal team only.

---

## 3. Stack

| Layer | Choice | Notes |
|---|---|---|
| Language | PHP 8.2+ | Hostinger Premium supports |
| Framework | Laravel 11+ | |
| Admin panel | Filament v3 | CRM-oriented, Kanban plugin available |
| Templating / UI | Livewire + Tailwind (Filament defaults) | |
| Auth | Laravel Auth + Filament login | Email + password |
| Permissions | `spatie/laravel-permission` | Role-based; maps to our 4-role matrix |
| Encrypted fields | Laravel `encrypted` cast | For `ipu_password` |
| Activity log | `spatie/laravel-activitylog` | Audit trail of mutations |
| File storage | Google Drive via `masbug/flysystem-google-drive-ext` | Payment proofs |
| Kanban | Filament Kanban plugin (to evaluate during scaffolding) | |
| Database | MySQL on IPU Hostinger (`ipuc_davyafin`) | Shared with Phase 2 Finance |
| Hosting | Hostinger shared, subdomain `davyas.ipu.co.in` | SSL via Let's Encrypt |
| Deploy | `git pull` + `composer install` + `php artisan migrate` on SSH | |

---

## 4. Organisational hierarchy & roles

### 4.1 Hierarchy

- **Heads (3):** Sumit, Sonam, Nikhil.
- **Members report to one head:** Nisha → Nikhil; Poonam, Neetu → Sonam.
- **Freelancers report to a head, have no team:** Kapil → Sumit.
- Sumit additionally has **Admin** role (god mode).

### 4.2 Roles

- **Admin** (Sumit): full access to everything.
- **Head** (Sonam, Nikhil, Sumit): manages own team; sees + edits own + team's students.
- **Member** (Nisha, Poonam, Neetu, future): sees + edits only students they own.
- **Freelancer** (Kapil, future): same as Member — own only, no team.

### 4.3 Permissions matrix

| Role | View own | View team | View all | Edit own | Edit team | Edit all | Create | Delete |
|---|---|---|---|---|---|---|---|---|
| Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Head | ✓ | ✓ | ✗ | ✓ | ✓ | ✗ | ✓ | own+team |
| Member | ✓ | ✗ | ✗ | ✓ | ✗ | ✗ | ✓ | ✗ |
| Freelancer | ✓ | ✗ | ✗ | ✓ | ✗ | ✗ | ✓ | ✗ |

### 4.4 Ownership rules

- Every student has **one owner** (`owner_id`). Single-owner model; transfers handle collaboration.
- **Visibility** flows upward: a Head sees every student whose `owner_id` is in their team (themselves + members + freelancers under them).
- **Re-assigning ownership:** only Admin or Head can transfer. Members cannot.
- **Referrer** (the person who originally brought the lead — `referrer_id`) is **separate from owner**. Preserves attribution even if the student is transferred later.

---

## 5. Pipeline & stages

Single pipeline: `NewAdmission`.

### 5.1 Stages (10)

| # | Stage | Purpose | Required field to move in |
|---|---|---|---|
| 1 | **Lead Captured** | New lead, phone + name + owner + referrer recorded | phone, name, owner_id, lead_source |
| 2 | **Meeting Scheduled** | Office meeting booked | meeting_date, meeting_location |
| 3 | **Meeting Done** | Explained Davya's offering; student responded | student_response (Ready / Not Interested / Needs Time) |
| 4 | **Onboarded** | Student said Ready; picked a plan; advance paid | plan (Online/Offline/All), deal_amount, at least one `advance` payment row |
| 5 | **University Registration** | Checking or doing IPU registration | is_ipu_registered, ipu_user_id |
| 6 | **Counselling In Progress** | Participating in rounds | a `round_history` row (any round) |
| 7 | **Seat Allotted** | Final allotment, ready to pay fees | final_college, final_course |
| 8 | **Full Payment Received** | Fee fully cleared | `SUM(payments.amount) >= deal_amount` |
| 9 | **Admission Confirmed** | Enrolled at college | admission_date |
| 10 | **Closed** | Dead lead (any reason) | close_reason (dropdown) |

### 5.2 Free movement (with a few hard rails)

Stage transitions are **mostly unrestricted** — a student can move from any stage to any stage, matching Sumit's request for flexibility. Required-fields listed in §5.1 are **soft nudges** (yellow warning if missing, not hard blocks).

The short list of **hard blocks** (cannot proceed until resolved) lives in §5.3: closing without a reason, ownership transfer by a non-permitted role, and re-entry without a note. Everything else is soft.

### 5.3 Business rule validators (soft warnings)

Surface yellow/red banners when user changes stage; user can always proceed anyway.

- **Moving to next round while prior round `seat_fee_paid = false`** → yellow warning.
- **Entering Sliding Round with no prior `Allotted` outcome in any `round_history` row** → red warning.
- **Moving to Stage 10 (Closed) without selecting `close_reason`** → blocks until set (hard validation).
- **Transferring ownership by a Member** → blocked (hard validation).
- **Re-entry after kickout** (moving from Closed back to a round stage) → requires `re_entry_reason` note (hard validation, text box).

---

## 6. Data model

All tables live in MySQL DB `ipuc_davyafin`, same DB as future Phase 2 Finance.

**Conventions:**
- All `TIMESTAMP` columns in **IST** (Laravel `app.timezone = 'Asia/Kolkata'`).
- Money in `DECIMAL(12,2)`, INR only.

### 6.1 `users` (Laravel auth + Spatie roles)

Standard Laravel users table. Fields: id, name, email (unique), email_verified_at, password (bcrypt hash), remember_token, team_head_id (FK → users.id, NULL for heads/admin), is_freelancer (BOOL), timestamps. Roles assigned via Spatie.

Seed users: Sumit (admin+head), Sonam (head), Nikhil (head), Nisha (member, head=Nikhil), Poonam (member, head=Sonam), Neetu (member, head=Sonam), Kapil (freelancer, head=Sumit). **7 seed users total.**

### 6.2 `students`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| phone | VARCHAR(15) UNIQUE NOT NULL | Normalized digits only |
| name | VARCHAR(120) NOT NULL | |
| father_name | VARCHAR(120) NULL | |
| phone_2 | VARCHAR(15) NULL | |
| owner_id | INT NOT NULL FK → users.id | Primary handler |
| referrer_id | INT NOT NULL FK → users.id | Person who brought the lead (may differ from owner) |
| stage | ENUM (10 stages above) NOT NULL default 'Lead Captured' | |
| lead_source | VARCHAR(60) NOT NULL | Dropdown: all head/member/freelancer names + 'Other' |
| student_response | ENUM('Ready','Not Interested','Needs Time') NULL | Set at Meeting Done |
| exam_appeared | VARCHAR(40) NULL | IPU CET / CUET / JEE / Other |
| twelfth_marks | VARCHAR(20) NULL | Text — may be %, CGPA, or raw marks |
| category | ENUM('Delhi','Outside') NULL | |
| course | VARCHAR(80) NULL | |
| preference_r1 | VARCHAR(120) NULL | Top college choice |
| preference_r2 | VARCHAR(120) NULL | |
| preference_r3 | VARCHAR(120) NULL | |
| deal_amount | DECIMAL(12,2) NULL | Required from Stage 4 onward; drives Kanban aggregates |
| plan | ENUM('Online','Offline','All') NULL | Service package chosen |
| is_ipu_registered | BOOLEAN NULL | |
| ipu_user_id | VARCHAR(60) NULL | Student's IPU portal username |
| ipu_password | TEXT NULL | **Encrypted** (Laravel `encrypted` cast). Never plaintext at rest. |
| current_round | VARCHAR(40) NULL | Pointer to latest round in `round_history` |
| seat_fee_due | BOOLEAN DEFAULT 0 | Derived flag for UI badges |
| final_college | VARCHAR(120) NULL | Set at Seat Allotted |
| final_course | VARCHAR(120) NULL | |
| admission_date | DATE NULL | |
| meeting_date | DATETIME NULL | |
| meeting_location | VARCHAR(120) NULL | |
| address_sent | BOOLEAN NULL | |
| office_visit | BOOLEAN NULL | |
| close_reason | ENUM('Not Interested','Backed Out — Forfeit','Backed Out — Partial Refund','Completed','Other') NULL | Required at Stage 10 |
| refund_amount | DECIMAL(12,2) NULL | When close_reason = 'Backed Out — Partial Refund' |
| re_entry_reason | TEXT NULL | Required if moving from Closed back to a round stage |
| description | TEXT NULL | Freeform notes |
| extra_notes | TEXT NULL | Secondary freeform |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 6.3 `round_history`

Row per round per student. Captures every round the student has entered, outcome, and seat-allotment fee status.

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| student_id | INT NOT NULL FK → students.id | |
| round_name | ENUM(`Online_R1`, `Online_R2`, `Online_R3`, `Online_Sliding`, `Online_Reporting`, `S2_R1`, `S2_R3`, `Offline_R1`, `Offline_R2`) NOT NULL | 9 values |
| allotted_college | VARCHAR(120) NULL | |
| allotted_course | VARCHAR(120) NULL | |
| seat_fee_amount | DECIMAL(12,2) NULL | |
| seat_fee_paid | BOOLEAN DEFAULT 0 | |
| fee_paid_at | TIMESTAMP NULL | |
| outcome | ENUM('Not Allotted','Allotted — Fee Pending','Allotted — Fee Paid','Kicked Out — Fee Unpaid','Allotted — Frozen (Final)') NOT NULL | |
| notes | TEXT NULL | |
| created_at | TIMESTAMP | |

**Index:** `(student_id, created_at)`.

### 6.4 `payments`

| Column | Type | Notes |
|---|---|---|
| id | INT PK AUTO_INCREMENT | |
| student_id | INT NOT NULL FK → students.id | |
| type | ENUM('advance','partial','full','refund') NOT NULL | |
| amount | DECIMAL(12,2) NOT NULL | **Signed**: advance/partial/full positive, refund negative |
| mode | ENUM('cash','upi','bank_transfer','card','cheque','other') NULL | |
| reference_number | VARCHAR(80) NULL | UTR / transaction ID |
| received_at | TIMESTAMP NOT NULL | |
| proof_drive_url | VARCHAR(500) NULL | **Optional**. Google Drive file URL |
| notes | TEXT NULL | |
| recorded_by_user_id | INT NOT NULL FK → users.id | Who entered this payment |
| created_at | TIMESTAMP | |

**Derivations:**
- `student.total_received = SUM(payments.amount)` (signed, so refunds subtract).
- `student.pending = student.deal_amount - student.total_received`.

### 6.5 `activity_log` (Spatie)

Audit trail of all mutations. Built-in from `spatie/laravel-activitylog`. Captures who changed what, when, old vs new value. Used for accountability and debugging.

### 6.6 `referrers` table — **not needed in Phase 1**

Users table already carries role + head_id + is_freelancer. Phase 2 will derive the same info for its routing logic directly from users. No separate `referrers` table.

---

## 7. Filament resources

### 7.1 StudentResource (main CRUD)

- **Form** — grouped into sections matching Zoho layout:
  1. Identity (phone, name, father name, phone 2)
  2. Source & Owner (owner, lead source, referrer)
  3. Stage & Response (stage dropdown, student response if applicable)
  4. Academic (exam, 12th marks, category)
  5. Preferences (course, R1/R2/R3)
  6. Deal (deal amount, plan, advance payment shortcut)
  7. Counselling (is_ipu_registered, ipu_user_id, ipu_password with Show Password button, current round)
  8. Final (final college/course, admission date)
  9. Logistics (meeting date/location, address_sent, office_visit)
  10. Closure (close_reason, refund_amount, re_entry_reason)
  11. Notes (description, extra_notes)
- **List view** — columns: phone, name, owner, stage, deal, received, pending, last updated. Filters: owner, stage, lead_source, plan.
- **Kanban view** — separate page; see §8.
- **Relation managers:** `round_history` (below student), `payments` (below student).

### 7.2 UserResource (Admin only)

Manage team: create, assign role, set head, deactivate. Nothing fancy.

### 7.3 Dashboard

Admin + Head see widgets:
- **Seat Fee Pending** — list of students with `round_history.seat_fee_paid = false` and `outcome = 'Allotted — Fee Pending'`, sorted by days pending.
- **Re-entry Candidates** — students whose latest `round_history` row has `outcome = 'Kicked Out — Fee Unpaid'`.
- **Stuck Leads** — students not updated in 14+ days, grouped by owner.
- **Pipeline Summary** — count + total deal per stage.

---

## 8. Kanban board

Custom page or Filament plugin. One column per stage.

### 8.1 Column header format

```
┌──────────────────────────────────────┐
│ Counselling In Progress              │
│ Deal: ₹12,50,000 · Rcvd: ₹4,00,000   │
│ Pending: ₹8,50,000 · 7 Students      │
└──────────────────────────────────────┘
```

- **Deal** = `SUM(deal_amount) WHERE stage = X AND visible_to_current_user`
- **Rcvd** = `SUM(payments.amount WHERE student IN column)`
- **Pending** = Deal − Rcvd
- **Count** = student count in column

All recomputed live on stage changes and payment additions.

### 8.2 Card layout

Per student card shows: name, phone, owner avatar, deal amount, pending amount, current round (if Stage 6), days in current stage.

### 8.3 Drag-drop

Admin/Head/owner can drag cards between columns. Triggers stage change + business-rule warnings (§5.3).

---

## 9. Security

- **Laravel defaults:** CSRF on all forms, bcrypt password hashing, HTTPS-only cookies, session expiry.
- **Student `ipu_password`:** AES-encrypted via Laravel `encrypted` cast using `APP_KEY`. Plaintext never stored in DB or backups. Filament form field renders as masked with a "👁 Show Password" toggle that decrypts on demand client-side (via Livewire round-trip). Only authorized viewers (admin / owner / head-of-owner) can reveal.
- **DB user `ipuc_davyapp`** — privileges scoped to `ipuc_davyafin` only, never `*.*`.
- **Activity log** captures all mutations including who viewed a student's password (record the "Show Password" action).
- **Sessions** — 2-hour idle timeout, absolute 7-day max. Team members logging in daily fine.

---

## 10. Deployment

### 10.1 DNS & subdomain

- cPanel → Subdomains → create `davyas` on `ipu.co.in`.
- Document root: `/home/<user>/davyas_public/public` (Laravel's `public/` folder).
- AutoSSL (Let's Encrypt) kicks in within minutes.

### 10.2 Codebase

- Private GitHub repo (new, separate from IPU repo).
- Local dev: Laravel Valet or `php artisan serve`.
- Deploy: SSH into Hostinger → `git pull` → `composer install --no-dev` → `php artisan migrate --force` → `php artisan config:cache` → `php artisan view:cache`.

### 10.3 `.env` (not committed)

```
APP_NAME="Davya CRM"
APP_ENV=production
APP_KEY=<generated>
APP_URL=https://davyas.ipu.co.in
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=ipuc_davyafin
DB_USERNAME=ipuc_davyapp
DB_PASSWORD=<from password manager>
GOOGLE_DRIVE_CLIENT_ID=...
GOOGLE_DRIVE_CLIENT_SECRET=...
GOOGLE_DRIVE_REFRESH_TOKEN=...
GOOGLE_DRIVE_FOLDER=<Davya CRM / Payment Proofs folder id>
```

---

## 11. Error handling

- Laravel default 404 / 500 pages, customized with Davya branding later.
- Filament form validation with inline error messages.
- Soft-warning banners for business-rule violations (non-blocking).
- Hard-block errors for: missing phone/name at Lead Captured, Member trying to transfer, moving to Closed without close_reason.
- All exceptions logged to `storage/logs/laravel.log`; rotated daily.

---

## 12. Testing

- **PHPUnit tests:**
  - Policy tests per role (Admin sees all, Head sees team, Member sees own).
  - Business-rule validator tests (warnings fire correctly).
  - Payment arithmetic (signed sums, refunds subtract).
  - `ipu_password` encrypt/decrypt round-trip.
- **Filament resource tests:**
  - Student create → list → edit flow.
  - Kanban aggregate correctness.
  - Show Password action is logged.
- CI not required for Phase 1 (solo dev); add if team grows.

---

## 13. Backup

Shared mechanism with Phase 2 spec. Daily PHP cron on IPU Hostinger → `mysqldump ipuc_davyafin | gzip > …` → upload to Google Drive `/Davya / Backups/`. 30-day retention. Single script covers CRM + Phase 2 Finance since same DB.

---

## 14. Done definition (Phase 1)

- `davyas.ipu.co.in` loads Filament login.
- Admin (Sumit) can create users, assign roles, assign head_id.
- Each of the 8 seed users can log in; Heads see only their team; Admin sees all.
- Students can be created, edited, moved between stages via form + Kanban.
- Round history relation manager works; soft warnings fire correctly.
- Payments relation manager works; signed totals correct; proof upload optional.
- Kanban board shows all 10 columns with live aggregates.
- Dashboard widgets (seat fee pending, re-entry candidates, stuck leads) populate correctly.
- `ipu_password` stored encrypted; Show Password reveals it; action is logged.
- Mobile view (iPhone + Android) works for list + form + payment entry (Kanban scroll accepted).
- Daily backup runs and produces a restorable dump.
- Sumit signs off on spec + acceptance test.

---

## 15. Open items (resolve during implementation)

1. **Filament Kanban plugin** — evaluate available packages during scaffolding; fall back to custom Livewire page if none fit.
2. **Google Drive credentials setup** — need service account JSON or OAuth refresh token for Hostinger → Drive upload.
3. **GitHub repo name** — suggest `davya-crm`. Private.

---

## 16. What this design explicitly is NOT

- **Not a Davya finance system.** That's Phase 2 (`2026-04-16-davya-finance-phase2-design.md`). Phase 1 records payments, doesn't do ledger math.
- **Not a Zoho clone in full.** YAGNI cuts: no custom fields admin UI, no workflow automation, no reports builder, no inline email integration.
- **Not n8n / Slack-driven.** Manual Filament entry only. Automation is Phase 2.
- **Not public.** Internal team only. No student-facing portal.
- **Not mobile-first.** Desktop-primary; default Filament responsive for mobile.
