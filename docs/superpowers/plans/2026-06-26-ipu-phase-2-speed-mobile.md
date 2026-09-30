# IPU Phase 2 (Batch A+B) — Speed, Mobile-First & Security Headers

> **For agentic workers:** REQUIRED SUB-SKILL: use superpowers:subagent-driven-development or superpowers:executing-plans to implement task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the low-risk, high-value half of Phase 2 — security headers + mobile-correctness fixes (Batch A) and a site-wide WebP image rollout (Batch B) — to ipu.co.in, leaving render-blocking Bootstrap (Batch C) and session-scoping (dropped) out of scope.

**Architecture:** Vanilla PHP production site at `/Users/Sumit/test-project/website_download`. Shared chrome in `include/base-head.php` (inlined critical CSS), `include/base-nav.php`, `include/base-footer.php`. Apache + cPanel PHP-FPM (NOT LiteSpeed). Deploy = `python3 deploy.py --files <paths>` (FTP, creds from `.env`) on branch `claude/2026-04-30-ipu-session`.

**Tech Stack:** PHP 8.2, Apache `.htaccess` (mod_headers), inline CSS, `include/image-helper.php` `webp_img()`/`responsive_img()` helpers, pre-generated `.webp` assets.

## Global Constraints

- **SEO-safety (verbatim from standing rule):** never change URL/title/meta/canonical/H1 on any page. None of these tasks should touch those — perf/headers/images only. If a task would, STOP.
- **Deploy branch:** `claude/2026-04-30-ipu-session` — no merge to main. Deploy scoped files via `deploy.py --files` (never `--sync`).
- **Pre-deploy gate:** every changed file must pass `php -l` (PHP) or render check (.htaccess/CSS), serve HTTP 200 with 0 fatals on `php -S 127.0.0.1:PORT`, and a localhost visual/crosslink pass before FTP. Curl-verify on prod after.
- **Additive/reversible only.** No deletion of live assets.
- **`expose_php` cannot be set in `.user.ini`** (it is PHP_INI_SYSTEM); suppress the `X-Powered-By` header via `mod_headers` in `.htaccess` instead.

---

## Task 1: Add HSTS, Permissions-Policy, and suppress X-Powered-By (`.htaccess`)

**Files:**
- Modify: `website_download/.htaccess:127-131` (the `# Security Headers` block inside `<IfModule mod_headers.c>`)

**Interfaces:**
- Produces: HTTP response headers `Strict-Transport-Security`, `Permissions-Policy`, and removal of `X-Powered-By`.

**Why HSTS is host-scoped:** `*.ipu.co.in` hosts multiple independent apps (davyas/kyne/me/crm). `includeSubDomains`/`preload` would force HSTS on all of them for up to 2 years (browser-cached, hard to reverse). Ship apex-only first.

**Why no CSP:** the site loads GTM, GA4, Meta Pixel, Clarity, Google Fonts and many inline `<script>`/`<style>` blocks; a blocking CSP risks breaking analytics/conversion tracking, and a report-only CSP without a report endpoint is a no-op. Deferred deliberately — revisit only with a reporting backend.

- [ ] **Step 1: Read the current block to confirm it matches**

Run: `sed -n '126,132p' website_download/.htaccess`
Expected: the four `Header always set` lines (X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy).

- [ ] **Step 2: Add the three new headers after the existing four**

Replace:
```apache
  # Security Headers
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set X-XSS-Protection "1; mode=block"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```
With:
```apache
  # Security Headers
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set X-XSS-Protection "1; mode=block"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  # HSTS — apex host only (NO includeSubDomains/preload: *.ipu.co.in hosts other apps)
  Header always set Strict-Transport-Security "max-age=31536000"
  # Restrict powerful features the site never uses
  Header always set Permissions-Policy "geolocation=(), camera=(), microphone=(), payment=()"
  # Don't advertise the PHP version
  Header always unset X-Powered-By
  Header unset X-Powered-By
</IfModule>
```

- [ ] **Step 3: Verify headers locally**

Run: `cd website_download && php -S 127.0.0.1:8201 >/tmp/p.log 2>&1 & sleep 2; curl -sI http://127.0.0.1:8201/index.php | grep -iE 'x-powered-by|content-type-options'; pkill -f 127.0.0.1:8201`
Note: Apache-only directives (HSTS/Permissions-Policy) won't appear under `php -S` — they're validated on prod after deploy. Confirm no PHP fatals and `X-Powered-By` behaviour. The real check is Step (deploy) curl on prod.

- [ ] **Step 4: Commit**

```bash
git add website_download/.htaccess
git commit -m "feat(perf): add HSTS (apex-only) + Permissions-Policy, suppress X-Powered-By"
```

---

## Task 2: Mobile tap-targets + go-top clearance (`include/base-head.php`)

**Files:**
- Modify: `website_download/include/base-head.php:133-205` (inline critical CSS — `.ipu-input`, `.ipu-btn-primary`, `.mobile-call-btn`, `.go-top` rules)

**Interfaces:**
- Produces: larger guaranteed tap targets on mobile and more clearance between the back-to-top button and the sticky call bar.

Note: current rendered heights (padding + 16px line) are near 44px already; `min-height` makes the ≥44px guarantee explicit and is harmless on desktop. go-top currently sits at `bottom:84px` vs a ~68px call bar (~16px gap on non-notch phones) — widen the gap.

- [ ] **Step 1: Add min-heights to the form primitives**

Replace:
```css
.ipu-input{width:100%;padding:12px 16px;border:1px solid var(--ipu-rule);border-radius:8px;font-size:16px;font-family:inherit;color:var(--ipu-ink);background:#fff;transition:border-color .2s,box-shadow .2s;margin-bottom:10px;display:block}
```
With:
```css
.ipu-input{width:100%;min-height:44px;padding:12px 16px;border:1px solid var(--ipu-rule);border-radius:8px;font-size:16px;font-family:inherit;color:var(--ipu-ink);background:#fff;transition:border-color .2s,box-shadow .2s;margin-bottom:10px;display:block}
```
Replace:
```css
.ipu-btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 22px;background:var(--ipu-orange);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;box-shadow:var(--ipu-cta-shadow);text-decoration:none}
```
With (add `min-height:48px`):
```css
.ipu-btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:14px 22px;background:var(--ipu-orange);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;box-shadow:var(--ipu-cta-shadow);text-decoration:none}
```

- [ ] **Step 2: Bump mobile call button + go-top clearance**

In the `@media(max-width:768px)` block, replace the `.mobile-call-btn` rule to add `min-height:48px` (find `padding:12px;border-radius:50px;color:#0d1b6e` and add `min-height:48px;`). Then replace:
```css
@media (max-width:768px){.go-top{width:48px;height:48px;bottom:calc(84px + env(safe-area-inset-bottom)) !important;z-index:9998;}}
```
With (more clearance above the call bar):
```css
@media (max-width:768px){.go-top{width:48px;height:48px;bottom:calc(96px + env(safe-area-inset-bottom)) !important;z-index:9998;}}
```

- [ ] **Step 3: Render check at mobile width**

Run: `cd website_download && php -S 127.0.0.1:8201 >/tmp/p.log 2>&1 & sleep 2; curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8201/index.php; curl -s http://127.0.0.1:8201/index.php | grep -c "min-height:48px"; pkill -f 127.0.0.1:8201`
Expected: HTTP 200; grep ≥1. Then eyeball the homepage + a college page at 375px width — form button, inputs, call bar, and go-top must not overlap and must look unchanged otherwise.

- [ ] **Step 4: Commit**

```bash
git add website_download/include/base-head.php
git commit -m "feat(mobile): guarantee >=44px tap targets + widen go-top/call-bar clearance"
```

---

## Task 3: Extend `webp_img()` to support eager/LCP hero images (`include/image-helper.php`)

**Files:**
- Modify: `website_download/include/image-helper.php:7-28` (the `webp_img()` function)

**Interfaces:**
- Produces: `webp_img($src, $alt='', $class='', $lazy=true, $opts=[])` — new 5th param `$opts` accepting `'fetchpriority'=>'high'` and/or `'decoding'=>'async'`. When `$lazy` is false the image is eager (no `loading="lazy"`). Back-compatible: the 4 existing callers (index.php, college-card.php + 2) keep working unchanged.
- Consumes: pre-generated `.webp` siblings of `.jpg/.jpeg/.png`.

- [ ] **Step 1: Replace the function with the extended version**

Replace lines 7-28 with:
```php
function webp_img($src, $alt = '', $class = '', $lazy = true, $opts = []) {
    $webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $src);
    $loading = $lazy ? 'loading="lazy"' : '';
    $classAttr = $class ? "class=\"$class\"" : '';

    // Optional LCP/hero attributes
    $extra = '';
    if (!empty($opts['fetchpriority'])) $extra .= ' fetchpriority="' . htmlspecialchars($opts['fetchpriority'], ENT_QUOTES) . '"';
    if (!empty($opts['decoding']))      $extra .= ' decoding="' . htmlspecialchars($opts['decoding'], ENT_QUOTES) . '"';

    // Try to get image dimensions for CLS prevention
    $width = '';
    $height = '';
    $fullPath = __DIR__ . '/../' . $src;
    if (file_exists($fullPath)) {
        $size = @getimagesize($fullPath);
        if ($size) {
            $width = "width=\"{$size[0]}\"";
            $height = "height=\"{$size[1]}\"";
        }
    }

    echo "<picture>";
    echo "<source srcset=\"$webp\" type=\"image/webp\">";
    echo "<img src=\"$src\" alt=\"" . htmlspecialchars($alt) . "\" $classAttr $loading $width $height$extra>";
    echo "</picture>";
}
```

- [ ] **Step 2: Lint + smoke-test the helper**

Run: `cd website_download && php -l include/image-helper.php && php -r 'require "include/image-helper.php"; webp_img("assets/images/IP-University-b-tech-admission.jpg","x","main-img",false,["fetchpriority"=>"high","decoding"=>"async"]);'`
Expected: "No syntax errors"; output `<picture>` containing `fetchpriority="high"`, `decoding="async"`, a `.webp` source, NO `loading="lazy"`, and width/height.

- [ ] **Step 3: Confirm existing callers unaffected**

Run: `grep -rn "webp_img(" website_download --include=*.php | grep -v image-helper.php`
Inspect each call still matches the (back-compatible) signature. Render the pages on localhost (HTTP 200, 0 fatals).

- [ ] **Step 4: Commit**

```bash
git add website_download/include/image-helper.php
git commit -m "feat(perf): webp_img() supports eager + fetchpriority for LCP heroes"
```

---

## Task 4: Convert hero (above-the-fold/LCP) images to WebP `<picture>`

**Files:**
- Modify: hero `<img>` tags across the page set (discovered in Step 1). Each must have a pre-existing `.webp` sibling.

**Interfaces:**
- Consumes: Task 3's `webp_img(..., $lazy=false, ['fetchpriority'=>'high','decoding'=>'async'])`.

- [ ] **Step 1: Enumerate hero images and confirm .webp pairs exist**

Run:
```bash
cd website_download
grep -rln 'fetchpriority="high"' --include=*.php .
# For each hit, list the img src and confirm the .webp sibling exists:
grep -rhoE 'src="[^"]*\.(jpg|jpeg|png)"' --include=*.php . | sort -u | while read s; do f=$(echo "$s"|sed -E 's/src="([^"]*)"/\1/'); w="${f%.*}.webp"; [ -f "$w" ] && echo "OK  $f" || echo "NO-WEBP $f"; done
```
Record the list. Only convert images with an `OK` (existing `.webp`).

- [ ] **Step 2: Convert each hero `<img>` to a `webp_img()` eager call**

For a hero currently like:
```html
<img fetchpriority="high" decoding="async" width="1000" height="600" src="assets/images/IP-University-b-tech-admission.jpg" class="main-img" alt="IPU B.Tech Admission 2026 Guide">
```
Replace with:
```php
<?php webp_img('assets/images/IP-University-b-tech-admission.jpg', 'IPU B.Tech Admission 2026 Guide', 'main-img', false, ['fetchpriority'=>'high','decoding'=>'async']); ?>
```
Preserve the exact `alt` text and `class`. Do this per file from Step 1's list. Pages that already use a manual `<picture>` hero (BVP.php, BPIT.php) need no change.

- [ ] **Step 3: Lint + render every changed file**

Run: `for f in <changed files>; do php -l "$f"; done` then serve and curl each for HTTP 200, 0 fatals, and confirm the rendered `<picture>` has the `.webp` source + `fetchpriority="high"` + correct width/height.

- [ ] **Step 4: Commit**

```bash
git add <changed hero files>
git commit -m "perf(img): serve hero/LCP images as WebP <picture> with fetchpriority"
```

---

## Task 5: Convert below-the-fold raw `<img>` to lazy WebP

**Files:**
- Modify: remaining raw `<img>` tags that reference `assets/images/*` with an existing `.webp` sibling and are NOT hero/LCP.

**Interfaces:**
- Consumes: Task 3's `webp_img($src, $alt, $class)` (default `$lazy=true`).

- [ ] **Step 1: Enumerate remaining raw `<img>` with webp pairs**

Run:
```bash
cd website_download
grep -rln '<img ' --include=*.php . | while read p; do grep -oE '<img[^>]*src="[^"]*\.(jpg|jpeg|png)"[^>]*>' "$p" | grep -v 'fetchpriority' | sed "s#^#$p: #"; done
```
Filter to those whose `.webp` sibling exists (reuse Step-1 check from Task 4). Skip `news/*` images (no webp yet — handled in Task 6) and any inside `include/` already using the helper.

- [ ] **Step 2: Convert each to `webp_img()`**

Replace e.g.:
```html
<img src="assets/images/blog-4.jpg" width="370" height="250" alt="IPU courses">
```
With:
```php
<?php webp_img('assets/images/blog-4.jpg', 'IPU courses', '', true); ?>
```
Preserve `alt` and any class. Keep `loading="lazy"` behaviour (default).

- [ ] **Step 3: Lint + render every changed file** (same as Task 4 Step 3).

- [ ] **Step 4: Commit**

```bash
git add <changed files>
git commit -m "perf(img): lazy-load below-fold images as WebP site-wide"
```

---

## Task 6 (optional): Generate missing WebP for `news/` images

**Files:**
- Create: `.webp` siblings for the ~6 `news/*.jpg` images that lack them.

- [ ] **Step 1: Find news images without webp**

Run: `cd website_download/news && for f in *.jpg *.png; do [ -f "${f%.*}.webp" ] || echo "$f"; done 2>/dev/null`

- [ ] **Step 2: Generate webp** (only if `cwebp` available)

Run: `command -v cwebp && for f in <list>; do cwebp -q 82 "$f" -o "${f%.*}.webp"; done`
If `cwebp` is unavailable, SKIP this task and log it — do not block the rest.

- [ ] **Step 3: Commit** the new `.webp` files if generated.

---

## Task 7: Measure, deploy, verify

- [ ] **Step 1: Capture mobile PSI baseline (before deploy is already live; capture current prod)**

Capture (via PageSpeed Insights / pagespeed.web.dev) mobile LCP/CLS/render-blocking for: `https://ipu.co.in/`, a course hub (e.g. `https://ipu.co.in/IPU-B-Tech-admission-2026.php`), and `https://ipu.co.in/ipu-counselling.php`. Record numbers in the deploy notes.

- [ ] **Step 2: Deploy all changed files**

```bash
cd /Users/Sumit/test-project
set -a; . ./.env; set +a
DEPLOY_NONINTERACTIVE=1 python3 deploy.py --files website_download/.htaccess website_download/include/base-head.php website_download/include/image-helper.php <all changed page files>
```

- [ ] **Step 3: Verify on prod**

Run: `curl -sI https://ipu.co.in/ | grep -iE 'strict-transport|permissions-policy|x-powered-by'`
Expected: HSTS + Permissions-Policy present; no X-Powered-By. Then curl a converted page and confirm `<picture>`/`.webp` served. Spot-check pages render with no visual regression.

- [ ] **Step 4: Re-measure PSI** on the same 3 URLs; confirm LCP/render-blocking improved or unchanged, CLS not worsened. Record before/after.

---

## Self-Review notes

- **Spec coverage:** headers (Task 1), mobile correctness (Task 2), WebP rollout (Tasks 3-6), measurement (Task 7). Deferred-with-reason: CSP (analytics risk), HSTS preload/includeSubDomains (subdomain blast radius), Bootstrap render-blocking (Batch C, separate), session-scoping (dropped — already cacheable via `.user.ini`).
- **Risk order:** Tasks 1-3 are isolated and low-risk; Tasks 4-5 are the broad sweep — deploy them as their own commit group and visual-check a sample before FTP.
