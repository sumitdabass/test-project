# IPU Phase 1 — Conversion Bug Fixes — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Fix the conversion/lead-capture defects from the 2026-06-10 audit so no lead is silently lost and every call/enquiry path works on the live site.

**Architecture:** Vanilla PHP. Live enquiry forms POST to `/sendemail.php` (6-layer anti-dup, dual delivery: `mail()` + Google Apps Script). Canonical form component: `include/components/sidebar-enquiry.php`. Helpers: `include/helpers/phone-dedup.php` (has `lead_record(phone, source)` — logs a phone HASH only, not recoverable lead data).

**Tech Stack:** PHP 8.x, no framework. Verification via `php -l`, `php -S localhost:8000` + `curl` POSTs.

**Reference spec:** `docs/superpowers/specs/2026-06-10-ipu-site-improvement-program-design.md` §5 Phase 1.

**Constraints:** No ranking-element changes (additive/behavioral only). No prod deploy without owner go-ahead (Task 6 is the gate). Do NOT change `include/phone.php` — 21 other call sites depend on it emitting a full `<a>`; fix the 2 misusing sites instead. Work on branch `claude/2026-04-30-ipu-session`, no worktree. Spec is authoritative.

---

## Files

- Modify: `website_download/course/index.php` (Task 1)
- Modify: `website_download/sendemail.php` (Task 2, 5)
- Create: `website_download/include/helpers/lead-fallback.php` (Task 2)
- Modify: `website_download/best-btech-colleges-ipu.php`, `website_download/bba-management-quota-ipu.php` (Task 3)
- Modify: `website_download/thank-you.php` (Task 4)
- Modify: `website_download/include/components/sidebar-enquiry.php`, `website_download/index.php`, `website_download/assets/js/app.js` (Task 5)

---

## Task 1: Fix the broken `course/index.php` enquiry form

The form self-posts (`action=""`) into `form-handler.php` but the body references `$error`/`$success`/`$_SESSION['captcha']` (never set) → undefined-variable warnings, a garbled required captcha, and a failed redirect. Rewire it to the canonical `/sendemail.php` contract.

**Files:** Modify `website_download/course/index.php`

- [ ] **Step 1: Remove the vestigial form-handler include.** Delete these lines (around lines 8-12):

```php
<?php 

    include_once(__DIR__ . "/../include/form-handler.php");

?>
```

- [ ] **Step 2: Replace the `<form>` block.** Find the block starting `<form method="POST" action="">` (around line 74) through its closing `</form>`. Replace the ENTIRE form with:

```php
<form method="POST" action="/sendemail.php" class="enquiry-form" novalidate>
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
    <input type="hidden" name="form_loaded_at" value="">
    <h3 class="title">Enquire now</h3>
    <div class="input-box mt-10">
        <input type="text" name="name" required placeholder="Your Name" autocomplete="name" />
    </div>
    <div class="input-box mt-10">
        <input type="email" name="email" placeholder="Your Email (optional)" autocomplete="email" />
    </div>
    <div class="input-box mt-10">
        <input type="tel" name="phone" required placeholder="Phone Number" inputmode="tel" autocomplete="tel" pattern="[6-9][0-9]{9}" maxlength="10" />
    </div>
    <div class="input-box mt-10">
        <input type="text" name="course" required placeholder="Enter Course" />
    </div>
    <div class="input-box mt-10">
        <button type="submit" name="submit">Submit Now</button>
    </div>
</form>
```

(Note: `email` is now optional to match `sendemail.php` validation; the honeypot + `page_url` + `form_loaded_at` hidden fields match the canonical contract; captcha and `$error`/`$success` blocks are gone.)

- [ ] **Step 3: Lint.**

Run: `php -l website_download/course/index.php`
Expected: `No syntax errors detected`.

- [ ] **Step 4: Smoke-serve and confirm no undefined-variable warnings.**

```bash
cd /Users/Sumit/test-project/website_download && php -S localhost:8000 >/tmp/srv.log 2>&1 &
sleep 1
curl -s -o /dev/null -w "course hub → %{http_code}\n" "http://localhost:8000/course/"
grep -iE "undefined|captcha|warning" /tmp/srv.log | head
kill %1 2>/dev/null
```
Expected: `200`; no "Undefined variable $error/$success" or captcha warnings in the log.

- [ ] **Step 5: Commit.**

```bash
git add website_download/course/index.php
git commit -m "fix(course): rewire broken /course/ enquiry form to /sendemail.php contract

Was self-posting to form-handler with undefined \$error/\$success and a
garbled required captcha + failed redirect. Now uses the canonical
honeypot + page_url + sendemail.php path like sidebar-enquiry.php."
```

---

## Task 2: Stop silent lead loss in `sendemail.php`

`mail()` (line ~100) and the Google-Sheet `curl` (line ~123) both ignore their results. If both fail, the user sees the success page and the lead vanishes. Capture both outcomes; on ANY delivery failure, persist the FULL lead to a recoverable file.

**Files:** Create `website_download/include/helpers/lead-fallback.php`; modify `website_download/sendemail.php`

- [ ] **Step 1: Create the fallback writer.** Create `website_download/include/helpers/lead-fallback.php`:

```php
<?php
/**
 * lead-fallback.php — last-resort recoverable store for leads whose
 * primary delivery (email and/or Google Sheet) failed. Writes the FULL
 * lead as one JSON line so nothing is lost. Best-effort, never throws.
 */
if (!function_exists('lead_fallback_save')) {
    function lead_fallback_save(array $lead, string $reason): void {
        $dir = __DIR__ . '/../.private';
        if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
        $file = $dir . '/leads-fallback.jsonl';
        $lead['_reason'] = $reason;
        $lead['_at']     = date('c');
        $line = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        $fh = @fopen($file, 'a');
        if ($fh === false) { @error_log("lead_fallback_save: cannot open $file"); return; }
        if (flock($fh, LOCK_EX)) { fwrite($fh, $line); fflush($fh); flock($fh, LOCK_UN); }
        @fclose($fh);
    }
}
```

- [ ] **Step 2: Require the helper.** In `website_download/sendemail.php`, add after the existing `require_once` (line 12):

```php
require_once __DIR__ . '/include/helpers/lead-fallback.php';
```

- [ ] **Step 3: Capture `mail()` result.** Replace the `mail($to, $subject, $message, $headers);` line (~line 100) with:

```php
    $mail_ok = mail($to, $subject, $message, $headers);
```

- [ ] **Step 4: Capture the Sheet curl result + persist on failure.** Replace the curl execution block (the `curl_exec($ch); curl_close($ch);` lines, ~123-124) with:

```php
    $sheet_resp = curl_exec($ch);
    $sheet_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $sheet_err  = curl_errno($ch);
    curl_close($ch);
    $sheet_ok = ($sheet_err === 0 && $sheet_code >= 200 && $sheet_code < 400);

    // If EITHER delivery channel failed, persist the full lead so it is recoverable.
    if (!$mail_ok || !$sheet_ok) {
        lead_fallback_save([
            'name' => $name, 'phone' => $phone, 'email' => $email,
            'course' => $course, 'source' => $page_url,
            'mail_ok' => (bool)$mail_ok, 'sheet_ok' => $sheet_ok, 'sheet_code' => $sheet_code,
        ], 'delivery_failure');
    }
```

- [ ] **Step 5: Lint.**

Run: `php -l website_download/sendemail.php && php -l website_download/include/helpers/lead-fallback.php`
Expected: both `No syntax errors detected`.

- [ ] **Step 6: Unit-test the fallback writer.** Create a throwaway check (do not commit it):

```bash
cd /Users/Sumit/test-project/website_download
php -r 'require "include/helpers/lead-fallback.php";
  lead_fallback_save(["name"=>"Test","phone"=>"9876543210","email"=>"","course"=>"B.Tech","source"=>"/x"], "unit");
  $f = __DIR__."/include/.private/leads-fallback.jsonl";
  $last = trim(array_slice(file($f), -1)[0]);
  $row = json_decode($last, true);
  echo ($row["name"]==="Test" && $row["_reason"]==="unit" && isset($row["_at"])) ? "FALLBACK OK\n" : "FALLBACK FAIL\n";'
# clean the test line so it doesn't pollute real data
php -r '$f=__DIR__."/website_download/include/.private/leads-fallback.jsonl"; if(is_file($f)){$l=array_filter(file($f),fn($x)=>!str_contains($x,"\"_reason\":\"unit\"")); file_put_contents($f,implode("",$l));}'
```
Expected: `FALLBACK OK`.

- [ ] **Step 7: Commit.**

```bash
git add website_download/sendemail.php website_download/include/helpers/lead-fallback.php
git commit -m "fix(leads): never lose a lead — capture mail()/Sheet failures and persist full lead to recoverable fallback file"
```

---

## Task 3: Fix the two broken `tel:` click-to-call links

**Files:** Modify `website_download/best-btech-colleges-ipu.php` (line ~296), `website_download/bba-management-quota-ipu.php` (line ~178)

- [ ] **Step 1: Fix `best-btech-colleges-ipu.php`.** The current line renders `tel:<a href=…>…</a>` (dead button). Replace:

```php
            <a href="tel:<?php echo trim(file_get_contents('include/phone.php')); ?>" class="btn-cta">&#128222; Call Now</a>
```

with:

```php
            <a href="tel:+919899991342" class="btn-cta">&#128222; Call Now</a>
```

- [ ] **Step 2: Fix `bba-management-quota-ipu.php`.** The current markup nests an anchor inside an anchor. Replace:

```php
<a href="tel:9899991342"><?php include("include/phone.php"); ?></a>
```

with:

```php
<a href="tel:+919899991342">+91-9899991342</a>
```

- [ ] **Step 3: Lint + verify no nested/garbled tel anchors remain.**

```bash
cd /Users/Sumit/test-project/website_download
php -l best-btech-colleges-ipu.php && php -l bba-management-quota-ipu.php
grep -nE 'tel:<\?php|tel:"?>\s*<a|<a[^>]*>\s*<\?php include\("include/phone.php"\)' best-btech-colleges-ipu.php bba-management-quota-ipu.php
```
Expected: both lint clean; the grep prints NOTHING.

- [ ] **Step 4: Commit.**

```bash
git add website_download/best-btech-colleges-ipu.php website_download/bba-management-quota-ipu.php
git commit -m "fix(tel): repair two dead click-to-call buttons (phone.php emits a full anchor; hardcode tel: at these 2 call sites)"
```

---

## Task 4: Replace the tofu Font Awesome icons on `thank-you.php`

`thank-you.php` uses `<i class="fa …">` but loads no Font Awesome CSS → blank boxes. Replace each with an inline SVG (same approach as `base-footer.php`).

**Files:** Modify `website_download/thank-you.php`

- [ ] **Step 1: Replace each `<i class="fa …"></i>` with its inline SVG.** Apply these exact substitutions (the `<i>` tag → the SVG). All SVGs use `width="1em" height="1em" fill="currentColor"` so they inherit the surrounding text size/color:

`fa-phone` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:-0.125em"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
```
`fa-whatsapp` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:-0.125em"><path d="M12 2a10 10 0 00-8.6 15l-1.4 5 5.1-1.3A10 10 0 1012 2zm5.8 14.2c-.2.7-1.4 1.3-2 1.4-.5.1-1.2.1-1.9-.1-.4-.1-1-.3-1.7-.6-3-1.3-4.9-4.3-5-4.5-.2-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .6l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.5 2 2.4 1.4 1.2 2.5 1.6 2.8 1.7.3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.4.1.2.1.7-.1 1.3z"/></svg>
```
`fa-clock-o` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="vertical-align:-0.125em"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
```
`fa-users` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="vertical-align:-0.125em"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
```
`fa-star` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:-0.125em"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
```
`fa-check-circle` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="vertical-align:-0.125em"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg>
```
`fa-university` →
```html
<svg width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="vertical-align:-0.125em"><path d="M12 2L2 7v2h20V7L12 2zM4 11v7H3v2h18v-2h-1v-7h-2v7h-3v-7h-2v7h-2v-7H9v7H6v-7H4z"/></svg>
```

Apply to all occurrences (lines ~347, 351, 353, 449, 450, 451, 452, 453). Use the surrounding context to map each `fa-*` class to the SVG above.

- [ ] **Step 2: Verify no Font Awesome `<i>` tags remain + lint.**

```bash
cd /Users/Sumit/test-project/website_download
php -l thank-you.php
grep -nE '<i class="fa' thank-you.php
```
Expected: lint clean; grep prints NOTHING.

- [ ] **Step 3: Smoke-serve and eyeball.**

```bash
cd /Users/Sumit/test-project/website_download && php -S localhost:8000 >/tmp/srv.log 2>&1 &
sleep 1
curl -s "http://localhost:8000/thank-you.php?src=submit" | grep -c "<svg"
kill %1 2>/dev/null
```
Expected: count ≥ 8 (the new SVGs render in the HTML).

- [ ] **Step 4: Commit.**

```bash
git add website_download/thank-you.php
git commit -m "fix(thank-you): replace tofu Font Awesome <i> icons with inline SVGs (no FA CSS is loaded on this page)"
```

---

## Task 5: Arm the anti-spam time-gate (cache-safe) + per-IP rate limit

The Layer-2 time-gate needs `$_SESSION['form_loaded_at']`, set only on `index.php`. Most form pages don't start a session, and `sidebar-enquiry.php` is included after output begins (so `session_start()` there would warn). Use a **cache-safe, session-free** approach: a hidden `form_loaded_at` field whose value is set by JS at page load, read by `sendemail.php`. Also add a simple per-IP rate limit.

**Files:** Modify `website_download/include/components/sidebar-enquiry.php`, `website_download/index.php`, `website_download/assets/js/app.js`, `website_download/sendemail.php`

- [ ] **Step 1: Add the hidden field to `sidebar-enquiry.php`.** Inside the `<form class="ipu-enquiry__form enquiry-form" …>` (after the `page_url` hidden input, ~line 55), add:

```php
      <input type="hidden" name="form_loaded_at" value="">
```

- [ ] **Step 2: Add the hidden field to the `index.php` hero form.** After its `page_url` hidden input (~line 170), add:

```php
            <input type="hidden" name="form_loaded_at" value="">
```

- [ ] **Step 3: Set the timestamp via JS at load (cache-safe).** Append to `website_download/assets/js/app.js`:

```javascript
// Stamp enquiry forms with browser load time (seconds) so the server-side
// 3s time-gate works even on edge-cached HTML. Fail-open if JS is off.
document.querySelectorAll('form.enquiry-form input[name="form_loaded_at"]').forEach(function (el) {
  el.value = Math.floor(Date.now() / 1000);
});
```

- [ ] **Step 4: Read the field in `sendemail.php` (fallback to session) + add per-IP rate limit.** In `sendemail.php`, change the Layer-2 line:

```php
    $form_loaded = $_SESSION['form_loaded_at'] ?? 0;
```
to:

```php
    $form_loaded = (int)($_POST['form_loaded_at'] ?? 0);
    if ($form_loaded <= 0) { $form_loaded = $_SESSION['form_loaded_at'] ?? 0; }
```

Then add a per-IP rate limit immediately AFTER the Layer-3 cooldown block (after line ~36, before "Sanitize input"):

```php
    // ── Layer 3b: per-IP rate limit — max 5 submissions / 10 min ─────────────
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($ip !== '') {
        $rl_dir = __DIR__ . '/include/.private';
        if (!is_dir($rl_dir)) { @mkdir($rl_dir, 0700, true); }
        $rl_file = $rl_dir . '/rate-' . hash('sha256', $ip) . '.txt';
        $now = time();
        $hits = is_file($rl_file) ? array_filter(array_map('intval', explode(',', (string)@file_get_contents($rl_file))), fn($t) => $t > $now - 600) : [];
        if (count($hits) >= 5) {
            header("Location: /thank-you.php");
            exit();
        }
        $hits[] = $now;
        @file_put_contents($rl_file, implode(',', $hits), LOCK_EX);
    }
```

- [ ] **Step 5: Lint everything touched.**

```bash
cd /Users/Sumit/test-project/website_download
php -l sendemail.php && php -l index.php && php -l include/components/sidebar-enquiry.php
node --check assets/js/app.js 2>/dev/null || echo "(node not available — JS check skipped; verify syntax visually)"
```
Expected: all PHP `No syntax errors`; JS check passes (or skipped note).

- [ ] **Step 6: Smoke-test the gate locally.** Confirm honeypot + rate limit redirect correctly and a normal post is accepted (mail/curl will fail locally — that's fine, the fallback file catches it):

```bash
cd /Users/Sumit/test-project/website_download && php -S localhost:8000 >/tmp/srv.log 2>&1 &
sleep 1
# honeypot filled → redirect to /thank-you.php (no src=submit)
curl -s -o /dev/null -w "honeypot → %{http_code} %{redirect_url}\n" -d "website=bot&name=x&phone=9876543210&course=B.Tech" "http://localhost:8000/sendemail.php"
# missing fields → /?error=fields
curl -s -o /dev/null -w "missing → %{http_code} %{redirect_url}\n" -d "name=&phone=&course=" "http://localhost:8000/sendemail.php"
# bad phone → /?error=phone
curl -s -o /dev/null -w "badphone → %{http_code} %{redirect_url}\n" -d "name=A&phone=12345&course=B.Tech" "http://localhost:8000/sendemail.php"
kill %1 2>/dev/null
```
Expected: honeypot → 302 to `/thank-you.php`; missing → 302 to `/?error=fields`; badphone → 302 to `/?error=phone`.

- [ ] **Step 7: Add `.private/` ignore safety (if not already).** Confirm rate-limit files won't be committed:

```bash
cd /Users/Sumit/test-project
git check-ignore website_download/include/.private/rate-test.txt || grep -q ".private" .gitignore && echo ".private ignored" || echo "WARN: add website_download/include/.private/ to .gitignore"
```
Expected: `.private ignored` (it already is per Phase 0 / pre-existing `.gitignore`).

- [ ] **Step 8: Commit.**

```bash
git add website_download/sendemail.php website_download/index.php website_download/include/components/sidebar-enquiry.php website_download/assets/js/app.js
git commit -m "feat(antispam): cache-safe JS-stamped time-gate + per-IP rate limit on enquiry submissions"
```

---

## Task 6: Phase-1 deploy gate (OWNER GO-AHEAD REQUIRED)

**Only prod-touching step. STOP for owner go-ahead.**

- [ ] **Step 1: Full local verification.** Lint all changed PHP; `php -S localhost:8000` and crosslink-walk the changed pages (`/course/`, `/best-btech-colleges-ipu.php`, `/bba-management-quota-ipu.php`, `/thank-you.php?src=submit`, `/`); confirm no warnings in the server log. Per `feedback_localhost_crosslink_test` + `feedback_pre_deploy_quality_check`.

- [ ] **Step 2: Build the deploy manifest** of changed files:

```
website_download/course/index.php
website_download/sendemail.php
website_download/include/helpers/lead-fallback.php
website_download/best-btech-colleges-ipu.php
website_download/bba-management-quota-ipu.php
website_download/thank-you.php
website_download/index.php
website_download/include/components/sidebar-enquiry.php
website_download/assets/js/app.js
```

- [ ] **Step 3: Deploy (gated).** Dry-run then push via the Phase-0 deployer (creds from env, never hardcoded):

```bash
cd /Users/Sumit/test-project
python3 deploy.py --files <the 9 paths above> --dry-run
( set -a; . ./.env; set +a; python3 deploy.py --files <the 9 paths above> )
```

- [ ] **Step 4: Prod curl-verify.** Live pages 200; thank-you renders SVGs not tofu; a real test submission (own phone) lands in email + Sheet; confirm the two call buttons have correct `href="tel:+919899991342"`:

```bash
for u in /course/ /best-btech-colleges-ipu.php /bba-management-quota-ipu.php "/thank-you.php?src=submit" /; do echo -n "$u → "; curl -s -o /dev/null -w "%{http_code}\n" "https://ipu.co.in$u"; done
curl -s "https://ipu.co.in/thank-you.php?src=submit" | grep -c "<svg"
curl -s https://ipu.co.in/best-btech-colleges-ipu.php | grep -o 'href="tel:+919899991342"' | head -1
```
Expected: all 200; SVG count ≥ 8; the tel href found.

- [ ] **Step 5: Update memory** — Phase 1 shipped; note the lead-fallback file location (`include/.private/leads-fallback.jsonl`) for the owner to monitor.

---

## Self-Review

**Spec coverage (§5 Phase 1, items 1–5):**
1. `course/index.php` form → Task 1 ✓
2. Silent lead loss → Task 2 ✓ (new `lead_fallback_save`, full-lead JSONL)
3. Broken `tel:` links → Task 3 ✓ (2 sites; `phone.php` left untouched, protecting 21 callers)
4. `thank-you.php` icons → Task 4 ✓ (inline SVG, 7 icon types)
5. Anti-spam time-gate + IP rate limit → Task 5 ✓ (cache-safe JS stamp avoids the session/output-order trap; per-IP throttle)

**Placeholder scan:** Task 6 uses "<the 9 paths above>" — the 9 paths are explicitly listed in Task 6 Step 2; the implementer substitutes them. Not a content gap.

**Consistency:** `form_loaded_at` hidden field (Tasks 1, 5) + JS stamp (Task 5 Step 3) + server read (Task 5 Step 4) all use the same field name and seconds unit. `lead_fallback_save(array, string)` defined in Task 2 Step 1, called in Step 4 with that signature. `.private/` dir reused for both fallback (Task 2) and rate-limit (Task 5) — consistent path `include/.private/`.

**Note:** `course/index.php` gains a `form_loaded_at` hidden field (Task 1) but Task 5's JS targets `form.enquiry-form input[name="form_loaded_at"]` and Task 1's form has `class="enquiry-form"` — so the course form is stamped too. Consistent by design.
