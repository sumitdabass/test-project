---
project: davya-crm
phase: 2 (Finance) — v2 Assistant release
milestone: M6 (image proofs, simplified) + M7 (acceptance + tag)
date: 2026-04-20
status: design
supersedes: Drive-upload section of `2026-04-19-davya-finance-assistant.md` M6
---

# Davya M6 (Slack permalink proofs) + M7 (v1.2.0-assistant) — Design

## Why this exists

M6 as originally designed (Slack → n8n download → Drive upload → `proof_drive_url`) is blocked on an n8n Google Drive OAuth credential that Sumit has not been able to save. The M7 smoke matrix and `v1.2.0-assistant` tag are gated on M6 finishing.

Drive upload was valuable when Filament was the proof entry point (Phase 1, M4). Since Phase 2 made Slack the capture entry point, Slack already holds the uploaded file durably on the paid workspace. Re-uploading to Drive adds an OAuth dance, a credential to rotate, a second source of truth, and ~4 new n8n nodes — for no retrieval win over a Slack permalink.

**Goal:** ship `v1.2.0-assistant` this week by swapping Drive upload for Slack permalink storage.

## Non-goals

- No change to the capture extraction path (Gemini classify → Dispatch → POST).
- No change to the assistant question path.
- No change to n8n as orchestrator. (Ruled in: keep n8n.)
- No Filament chat widget in this release (deferred as post-ship D1).
- No Gemini billing enablement in this spec — that's a Sumit account action, tracked separately in memory.

## Scope

In:
1. Rename DB column `payments.proof_drive_url` → `payments.proof_url`.
2. Update `Payment` model, `StoreFinancePaymentRequest`, `FinancePaymentController`, `PaymentsRelationManager`, and all tests to use `proof_url`.
3. Replace the pre-staged `m6_1_patch.py` with a new patch script that removes the Drive node group and adds a single Slack-permalink-attach node.
4. Push updated n8n workflow to live; smoke-test a payment-with-image Slack message.
5. Run M7 8-case smoke matrix against live.
6. Tag `v1.2.0-assistant`. Update `project_davyascrm.md`.

Out:
- Drive as a proof backend. Removed entirely. The `drive` filesystem disk and `masbug/flysystem-google-drive-ext` package stay (used elsewhere or harmless if unused — leave for a separate cleanup pass).
- Existing Drive folder `1xSKfdiKcwjJ1TemkMikgS_ZFRRamClb4` retained for existing M4 Filament-uploaded proofs; no data migration.

## Architecture

### Before (as designed 2026-04-19)
```
Slack Trigger → Gemini classify → Dispatch → Route by target
  └── target=payments →
       Payment with image? (IF) →
         TRUE → Download Slack file → Upload to Drive → Attach proof_drive_url → POST /api/finance/payments
         FALSE → POST /api/finance/payments
```

### After (this design)
```
Slack Trigger → Gemini classify → Dispatch → Route by target
  └── target=payments →
       Payment with image? (IF) →
         TRUE → Attach proof_url (Code, one-liner: reads Slack file permalink) → POST /api/finance/payments
         FALSE → POST /api/finance/payments
```

Three nodes deleted: `Download Slack file` (HTTP), `Upload to Drive` (googleDrive), and the old `Attach proof_drive_url` (Code). One new Code node inserted in their place.

### The attach node

Single Code node named `Attach proof_url`. Reads from the Slack Trigger's `files` array (already in the event payload). Emits payload with `proof_url` set to the Slack file permalink (the public-within-workspace URL that Slack returns on upload).

```js
// Code: Attach proof_url
const dispatch = $('Dispatch by category').item.json;
const files = $('Slack Trigger — new message').item.json.files || [];
const firstImage = files.find(f => (f.mimetype || '').startsWith('image/'));
if (firstImage) {
  dispatch.payload.proof_url = firstImage.permalink;  // workspace-scoped permalink
}
return { json: dispatch };
```

### Laravel

**Migration** (new, timestamped 2026_04_20):
```php
Schema::table('payments', function (Blueprint $t) {
    $t->renameColumn('proof_drive_url', 'proof_url');
});
```

**StoreFinancePaymentRequest**: rule changes from `proof_drive_url` to `proof_url`. Keep `nullable|url|max:2048`.

**FinancePaymentController**: replace `proof_drive_url` with `proof_url` in the `Payment::create([...])` call.

**Payment model**: update `$fillable`.

**PaymentsRelationManager** (Filament):
- Form field `proof_drive_url` (FileUpload on `'drive'` disk) → `proof_url` (TextInput, nullable URL, placeholder `https://...`).
- Filament no longer uploads files; edits accept an already-hosted URL (Slack permalink, Drive link, whatever). Slack is the primary entry path; Filament edit is the manual fallback.
- Table column renamed `proof_drive_url` → `proof_url`; the "Open proof" row action still renders the value as an external link (Slack or Drive — the action doesn't care which host).

### Tests

Test changes:
- All `tests/Feature/Finance/*` references to `proof_drive_url` → `proof_url` (mechanical rename across field names, DB assertions, and payload builders).
- `PaymentCaptureTest::test_accepts_optional_proof_url` — renamed from the Drive variant; asserts a `https://davyas.slack.com/...` URL round-trips through the POST endpoint into the DB.
- `PaymentsRelationManagerTest` — the existing FileUpload/Drive-disk test is **removed** (flow no longer exists). A new test `test_proof_url_text_field_persists` is added asserting the TextInput saves a URL string. Net test count: unchanged (one removed, one added).

**Expected suite after changes:** 170 tests green. No regressions in non-Payment tests.

## Data flow — payment with image

1. Sumit drops `got 1500 from smoketest 9991110099 ref Nisha` with a screenshot in `#student-entries`.
2. Slack Trigger fires with `files: [{ mimetype: "image/png", permalink: "https://davyas.slack.com/files/U.../F.../screenshot.png", ... }]`.
3. Gemini classifies → `target=payments`, extracts fields.
4. Dispatch builds `payload = { student_phone, amount, referrer_name, category, slack_message_id }`.
5. `Route by target` fires `main[1]` (payments).
6. `Payment with image?` IF: `files[0].mimetype startsWith "image/"` → TRUE.
7. `Attach proof_url` Code node merges `proof_url: <permalink>` into payload.
8. `POST /api/finance/payments` with the augmented payload.
9. Laravel: `StoreFinancePaymentRequest` validates `proof_url` as optional URL → `FinancePaymentController` creates Payment row with `proof_url` set → `201`.
10. Confirm-capture Slack node threads reply: `✅ Payment ₹1500 · smoketest · ref Nisha · 📎 proof attached`.

**Failure modes:**
- No image attached → `Attach proof_url` is skipped by the IF node; payload has no `proof_url`; `nullable` validation passes; row stored without proof. Confirmation reply omits `📎 proof attached`.
- Image but Slack Trigger event doesn't include `files` (shouldn't happen with `files:read` scope; scope is already granted as of 2026-04-19) → IF fires FALSE, treated as no-image. No crash.
- Malformed Slack permalink → validation rejects with 422 → existing `:warning:` fail reply fires.

## Open data question: existing M4 Filament proofs

Existing Filament-uploaded proofs currently live in Drive and their URLs are stored in `proof_drive_url`. After the column rename, those URLs are still valid (Drive links don't break from the rename). The column just holds a heterogeneous set of URLs: Drive for pre-M6 rows, Slack for post-M6 rows. That's fine — they're both external URLs rendered by the same Filament link action.

No data migration needed. Existing Filament upload flow in `PaymentsRelationManager` is **removed** in this release (Slack is the entry point now); if Sumit still wants Filament upload later, it comes back as a separate ticket with a decision on Drive-vs-S3.

## Rollout

1. Branch `m6-slack-permalink` off `main` on davya-crm.
2. Laravel changes (migration + rename) land as a single commit with tests.
3. n8n patch script (new file `docs/m6_slack_permalink_patch.py`) replaces `docs/m6_1_patch.py` (delete old script — never ran).
4. Local run of patch script against the committed-baseline workflow JSON produces the new JSON; diff-check; commit.
5. Live n8n backup → PUT new workflow → deactivate/reactivate (reuse Task 1.4 push script from the assistant plan).
6. Smoke test (Task 6.5 below).
7. M7 8-case matrix.
8. Merge branch → tag `v1.2.0-assistant` on main.
9. Memory update in `project_davyascrm.md`.

## Tasks (the plan will expand these)

- **M6.1** — Laravel column rename + model/request/controller/Filament updates + test rename. One commit.
- **M6.2** — Deploy Laravel to prod (cPanel Terminal: `git pull`, `php artisan migrate`, `config:clear`). SSH is broken from this laptop; cPanel Terminal is the documented fallback.
- **M6.3** — n8n patch script: remove `Download Slack file`, `Upload to Drive`, old `Attach proof_drive_url`. Add new `Attach proof_url`. Rewire `Payment with image?` TRUE branch to new node → POST.
- **M6.4** — Push patched workflow to live n8n; reactivate.
- **M6.5** — Smoke test: Sumit drops `got 1500 from smoketest 9991110099 ref Nisha` with screenshot in `#student-entries`. Verify Payment row has `proof_url` starting with `https://davyas.slack.com/`.
- **M7.1** — 8-case smoke matrix (unchanged from the original plan).
- **M7.2** — Tag `v1.2.0-assistant`, push tag, update `project_davyascrm.md`.

## Risks and mitigations

- **Slack permalink expires or becomes inaccessible.** Permalinks don't expire, but a revoked user's files can become orphaned. Acceptable risk for internal ops tooling — if it happens, the proof row still shows the URL; clicking 404s. Not worth engineering around.
- **n8n patch removes nodes that were never merged to live.** The staged `m6_1_patch.py` was never applied to live. Drive nodes don't exist on live yet. The new patch only needs to add the single Attach node + IF rewire. Simpler than anticipated — patch script is ~40 lines, not 100.
- **Existing `proof_drive_url` column data.** Column rename preserves data. Old Drive URLs continue to render correctly.
- **Smoke matrix Gemini quota.** 8 calls + 1 smoke = 9 calls against a 20/day free tier — fine for one run. If Sumit re-tests, hits quota. Document this in the M7 task; Sumit can enable billing before re-testing.

## Success criteria

1. A payment message with a screenshot in `#student-entries` produces a Payment row with `proof_url` populated with a Slack permalink, and the threaded confirmation ends with `📎 proof attached`.
2. All 8 M7 matrix cases pass.
3. Tag `v1.2.0-assistant` exists on origin.
4. Full Laravel suite green locally and on prod (170 tests, 0 regressions).
5. `project_davyascrm.md` reflects the new post-ship state.
