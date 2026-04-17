# Click-to-call tracking — implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Install a site-wide delegated click listener that pushes a `phone_click` event to the GTM `dataLayer` whenever a `tel:` link is tapped, then route it through GTM to GA4 so per-page phone-click-intent becomes measurable.

**Architecture:** 6 lines of vanilla JS inside `<script>` in `website_download/include/base-head.php` (which every page includes). GTM container `GTM-5GXCN7Z` (already live) forwards the custom event to GA4 as an event. GA4 `phone_click` gets marked as a Key Event so it surfaces in Conversions reports. Deploy is FTP-upload of one PHP file to cPanel. Google Ads conversion import is deferred to a separate plan (P0.4).

**Tech Stack:** PHP include, vanilla JS, Google Tag Manager (browser UI), Google Analytics 4 (browser UI), cPanel FTP (Python/ftplib).

**Spec:** `docs/superpowers/specs/2026-04-17-click-to-call-tracking.md`

---

## File Structure

| Path | Change | Responsibility |
|---|---|---|
| `website_download/include/base-head.php` | Modify | Hosts the inline `<script>` delegated click listener. Included in every page's `<head>`, so one file change covers all 117 pages. |

No other local files touched. All other changes are in browser UIs (GTM + GA4).

---

## Task 1: Capture pre-deploy baseline (the "failing test")

**Files:** none

- [ ] **Step 1: Open GA4 Realtime and note current state**

Open https://analytics.google.com → the IPU property → Reports → **Realtime**. Scroll to "Event count by event name". In the search / filter, type `phone_click`.

Expected: no row named `phone_click`. This is the "failing test" baseline — no phone-click events exist today.

Write down somewhere visible (sticky note, scratch file): `pre-deploy baseline — phone_click events: 0`.

- [ ] **Step 2: Note any running GTM Preview session**

If someone (you or someone else) has a GTM Preview session open for the container, end it. We want the live-mode baseline to be clean.

---

## Task 2: Add the click listener to `base-head.php`

**Files:**
- Modify: `website_download/include/base-head.php` (insert before the final `</head>` on line 145)

- [ ] **Step 1: Read the target file to confirm the insertion point**

```bash
tail -5 website_download/include/base-head.php
```

Expected last line: `</head>`.

- [ ] **Step 2: Insert the `<script>` block**

Insert this block between the `<!-- All tracking managed by GTM… -->` comment line and the final `</head>`:

```html

<!-- phone_click custom event for tel: links (GTM → GA4 conversion) -->
<script>
document.addEventListener('click', function(e) {
  var a = e.target.closest('a[href^="tel:"]');
  if (!a) return;
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: 'phone_click' });
});
</script>
```

Exact placement: new lines go immediately before `</head>`, after the existing comment/tracking block.

- [ ] **Step 3: Lint the modified PHP file**

```bash
php -l website_download/include/base-head.php
```

Expected: `No syntax errors detected in website_download/include/base-head.php`

- [ ] **Step 4: Verify the diff is exactly what we want**

```bash
git diff website_download/include/base-head.php
```

Expected: 7–8 added lines (comment + `<script>` block + closing `</script>`), 0 removed lines.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/base-head.php
git commit -m "feat(tracking): phone_click dataLayer event for tel: links

Delegated global click listener in base-head.php pushes a phone_click
custom event to GTM whenever a tel: anchor is clicked. Browser behavior
unchanged (no preventDefault). GTM will route this to GA4 as a Key Event
and later to Google Ads as a Conversion.

Plan: docs/superpowers/plans/2026-04-17-click-to-call-tracking.md
Spec: docs/superpowers/specs/2026-04-17-click-to-call-tracking.md"
```

Do not push yet — local smoke test first.

---

## Task 3: Local smoke test

**Files:** none (runtime check only)

- [ ] **Step 1: Start the PHP built-in server**

```bash
php -S 127.0.0.1:8000 -t website_download
```

Expected output: `PHP … Development Server (http://127.0.0.1:8000) started`.

- [ ] **Step 2: In a second terminal tab, curl the homepage to confirm the listener is in the served HTML**

```bash
curl -s http://127.0.0.1:8000/ | grep -c "phone_click"
```

Expected: `2` (once in the HTML comment, once in the dataLayer push).

- [ ] **Step 3: Open the homepage in a real browser**

Open http://127.0.0.1:8000/ in Chrome/Safari. Press F12 to open DevTools. Go to the **Console** tab.

- [ ] **Step 4: Confirm `window.dataLayer` exists**

In the console, type:

```js
window.dataLayer
```

Expected: an array (possibly already with GTM init events pushed to it).

- [ ] **Step 5: Simulate a tel: click and verify the event fires**

In the console (to avoid actually opening the dialer on desktop):

```js
document.querySelector('a[href^="tel:"]').click();
window.dataLayer[window.dataLayer.length - 1];
```

Expected: the last element is `{event: 'phone_click'}`.

- [ ] **Step 6: Stop the PHP server**

In the terminal running `php -S`: press Ctrl-C.

If any step failed, fix the code in `base-head.php`, re-run from step 1. Don't proceed until the local smoke test passes.

---

## Task 4: Deploy the change to cPanel via FTP

**Files:** uploads `website_download/include/base-head.php` → `/public_html/include/base-head.php`

- [ ] **Step 1: Confirm FTP credentials in `.env`**

```bash
grep -c "^FTP_HOST=\|^FTP_USER=\|^FTP_PASS=" .env
```

Expected: `3`.

- [ ] **Step 2: Upload the file**

```bash
set -a && . ./.env && set +a && python3 -c "
import ftplib, os
host = os.environ['FTP_HOST']; user = os.environ['FTP_USER']; pw = os.environ['FTP_PASS']
local_path  = 'website_download/include/base-head.php'
remote_path = '/public_html/include/base-head.php'
print(f'Uploading {os.path.getsize(local_path)} bytes → {host}:{remote_path}')
ftp = ftplib.FTP(host, timeout=60); ftp.login(user, pw); ftp.set_pasv(True)
with open(local_path, 'rb') as f:
    ftp.storbinary(f'STOR {remote_path}', f)
ftp.quit()
print('✓ uploaded')
"
```

Expected last line: `✓ uploaded`.

- [ ] **Step 3: Verify the live file contains the new code**

```bash
curl -s https://ipu.co.in/ | grep -c "phone_click"
```

Expected: `2`.

If `0`, the upload failed, CDN/cPanel is caching, or the file path was wrong. Do not proceed. Debug upload.

---

## Task 5: Post-deploy live smoke test

**Files:** none (runtime check)

- [ ] **Step 1: Open the live site in an incognito/private window**

Open https://ipu.co.in/ in a fresh incognito window to bypass any local cache. Open DevTools → Console.

- [ ] **Step 2: Verify `dataLayer` is ready**

In the console:

```js
Array.isArray(window.dataLayer)
```

Expected: `true`.

- [ ] **Step 3: Click a `tel:` link programmatically and check the push**

```js
document.querySelector('a[href^="tel:"]').click();
window.dataLayer.filter(x => x.event === 'phone_click').length
```

Expected: `1` (or higher if you clicked multiple times).

- [ ] **Step 4: Check console for errors**

Scroll through Console. Expected: no red errors related to `phone_click`, `dataLayer`, or `base-head`.

If any step fails, rollback immediately (Task 11) and investigate.

---

## Task 6: Create the GTM trigger

**Files:** none (GTM UI only)

- [ ] **Step 1: Open the GTM workspace**

Open https://tagmanager.google.com → pick the container with ID `GTM-5GXCN7Z`. Confirm you're in a **Workspace** (not published view). Top-right should show "Submit" and "Preview" buttons.

- [ ] **Step 2: Create the trigger**

Left nav → **Triggers** → click **New** (top-right).

- Name (top-left): `CE — phone_click`
- Click **Trigger Configuration** → choose **Custom Event**
- Event name: `phone_click`
- This trigger fires on: **All Custom Events**
- Click **Save**.

- [ ] **Step 3: Verify the trigger was saved**

Back in the Triggers list, expected: a row named `CE — phone_click`, Type `Custom Event`.

---

## Task 7: Create the GTM tag (GA4 event)

**Files:** none (GTM UI only)

- [ ] **Step 1: Confirm the existing GA4 configuration tag**

In Tags, find the existing GA4 Configuration tag (or GA4 Config tag). Note its **Measurement ID** — starts with `G-`. You'll reference it in the next step if using the Event tag's "Config Tag" dropdown.

- [ ] **Step 2: Create the new GA4 Event tag**

Left nav → **Tags** → click **New**.

- Name: `GA4 — phone_click`
- **Tag Configuration** → choose **Google Analytics: GA4 Event**
- **Configuration Tag**: pick the existing GA4 config tag from the dropdown (whatever it's named, e.g., "GA4 Config")
- **Event Name**: `phone_click` (lowercase, with underscore, exact)
- Leave "Event Parameters" empty — GA4 auto-attaches `page_path`, `page_referrer`, `session_source`, etc.
- **Triggering** → click to add → pick **CE — phone_click** (the one you made in Task 6)
- Click **Save**.

- [ ] **Step 3: Verify the tag was saved**

Back in the Tags list, expected: a row named `GA4 — phone_click`, Type `Google Analytics: GA4 Event`, Firing triggers shows `CE — phone_click`.

---

## Task 8: GTM Preview mode — end-to-end test before publishing

**Files:** none

- [ ] **Step 1: Start GTM Preview**

Top-right of GTM → click **Preview**. In the Tag Assistant tab that opens, enter `https://ipu.co.in/` → click **Connect**.

- [ ] **Step 2: A new browser tab opens to ipu.co.in with a debug overlay**

In that tab, scroll to any `CALL: 9899991342` link (should be in the header or hero) → tap/click it. On desktop Chrome, this may open a "Choose an app" dialog; cancel it — the dataLayer push happens regardless.

- [ ] **Step 3: Check the Tag Assistant debugger**

Return to the Tag Assistant tab. In the left-side event timeline, find a new event row labeled `phone_click` (or `Custom Event`).

Click it. Expected:
- Under **Tags Fired**: `GA4 — phone_click` should be listed.
- Under **Triggers**: `CE — phone_click` should show as fired.

If the tag didn't fire, inspect:
- Trigger configuration (Task 6) — event name is exactly `phone_click`?
- Tag configuration (Task 7) — trigger linked correctly?
- Live site has the JS (Task 4 step 3)?

- [ ] **Step 4: Leave Preview running for the GA4 Realtime check in the next task**

---

## Task 9: Publish the GTM container

**Files:** none

- [ ] **Step 1: Submit the workspace**

In the GTM workspace, top-right → click **Submit**.

- [ ] **Step 2: Fill in the version details**

- Version name: `Add phone_click event (audit P0.3)`
- Version description: `Trigger CE — phone_click + tag GA4 — phone_click. Plan: 2026-04-17-click-to-call-tracking.md`
- Click **Publish**.

- [ ] **Step 3: Confirm the version went live**

After publishing, GTM shows a green "Published successfully" banner. Note the new version number (e.g., v23). The container is now live on ipu.co.in — the new tag fires for all visitors, not just Preview sessions.

---

## Task 10: GA4 Realtime verification

**Files:** none

- [ ] **Step 1: Open GA4 Realtime**

Open https://analytics.google.com → IPU property → Reports → **Realtime**.

- [ ] **Step 2: Trigger a live event**

In a separate browser tab (not Preview mode this time — this is the real published container), open https://ipu.co.in/ in an incognito window. Click any `CALL: 9899991342` link. Cancel the dialer prompt.

- [ ] **Step 3: Verify the event appears in Realtime within 30 seconds**

Back in the GA4 Realtime tab. Scroll to "**Event count by Event name**". Expected: a row named `phone_click` with count ≥ 1.

If it doesn't appear after 60 seconds:
- Re-verify the JS is live (Task 4 step 3).
- Re-verify the container version published (Task 9 step 3).
- Check that you're looking at the correct GA4 property.

- [ ] **Step 4: Trigger two more clicks from different pages**

Open https://ipu.co.in/IPU-B-Tech-admission-2026.php and https://ipu.co.in/ipu-admission-guide.php in incognito windows. Click `tel:` links on each.

Expected: the Realtime `phone_click` count increments by 2. This confirms the listener works on non-homepage pages (proves the site-wide deployment).

---

## Task 11: Mark `phone_click` as a Key Event in GA4

**Files:** none

- [ ] **Step 1: Open GA4 Admin → Events**

Open https://analytics.google.com → IPU property → **Admin** (gear icon, bottom-left) → **Events** (under the property column).

- [ ] **Step 2: Find `phone_click` in the events table**

Expected: a row named `phone_click` appears (it may take up to 24 hours after the first event fires; often 5–30 minutes).

If not yet visible: wait and retry — come back in 1 hour.

- [ ] **Step 3: Toggle "Mark as key event"**

On the `phone_click` row, flip the **Mark as key event** toggle to ON.

- [ ] **Step 4: Verify in Conversions report**

Reports → Engagement → **Conversions**. Expected: `phone_click` appears in the list (may be 24h delayed in this report; Realtime is immediate).

---

## Task 12: Push the code commit to origin

**Files:** none (git)

- [ ] **Step 1: Confirm local state**

```bash
git status -sb
```

Expected: ahead of origin/main by 1 commit (the one made in Task 2).

- [ ] **Step 2: Push**

```bash
git push origin main
```

Expected: successful push, remote HEAD now matches local.

---

## Task 13: Close out audit tracker

**Files:**
- Modify: `docs/superpowers/specs/2026-04-17-ipu-website-audit.md`

- [ ] **Step 1: Open the audit spec and find the P0.3 row**

The row is in "§6 Prioritized roadmap → P0 — Do this week". It looks like:

```
| P0.3 | Wire click-to-call GA4 event (§1.1) | H | 30m | None |
```

- [ ] **Step 2: Append a completion note to the Executive summary section**

Under **Executive summary**, add a small status line (under the existing paragraphs, before the "Three biggest findings" subheading):

```markdown
> **Progress log**
> - 2026-04-17 — P0.3 Click-to-call GA4 event shipped. `phone_click` event live on all pages via GTM. Next: measure for 2 weeks, then prioritize per-page CTA work.
```

- [ ] **Step 3: Commit**

```bash
git add docs/superpowers/specs/2026-04-17-ipu-website-audit.md
git commit -m "docs(audit): mark P0.3 click-to-call tracking complete"
git push origin main
```

---

## Rollback procedure (if anything breaks in production)

If after deploy you see console errors on ipu.co.in, broken UX, or a drop in Ads performance (within first 24h):

```bash
# 1. Revert the code change locally
git revert HEAD --no-edit

# 2. Re-upload base-head.php to cPanel
set -a && . ./.env && set +a && python3 -c "
import ftplib, os
ftp = ftplib.FTP(os.environ['FTP_HOST'], timeout=60)
ftp.login(os.environ['FTP_USER'], os.environ['FTP_PASS']); ftp.set_pasv(True)
with open('website_download/include/base-head.php', 'rb') as f:
    ftp.storbinary('STOR /public_html/include/base-head.php', f)
ftp.quit(); print('rolled back')
"

# 3. Push
git push origin main
```

GTM tags can stay live — they're harmless with no matching dataLayer events firing.

Events already collected in GA4 remain (nothing to delete).
