# IPU Phase 2A — SEO-Safe Quick Wins — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Ship the high-impact, low-risk fixes from the 2026-06-11 speed+SEO reanalysis that **cannot affect current rankings** — fix 2 live broken hero images, add 19 real pages to the sitemap, give the bare `course/` page additive head metadata, delete a dead 64KB CSS file, and fix 3 mobile-UX nits.

**Architecture:** Vanilla PHP 8, no framework. Shared head/nav/footer in `include/`. Existing helpers reused: `include/components/breadcrumb-schema.php` (BreadcrumbList JSON-LD from a `$breadcrumbs` array), `include/image-helper.php` (`webp_img()`). Verification is via `php -l`, `grep`, a local `php -S localhost:8000` server, and `curl` against both local and `https://ipu.co.in`.

**Tech Stack:** PHP 8.x, Bootstrap 5, vanilla `app.js`. No website unit-test framework (only `tests/test_deploy.py` for the deployer) — tasks verify with lint + grep + curl.

**Reference:** `docs/superpowers/specs/2026-06-10-ipu-site-improvement-program-design.md` (program spec); reanalysis findings in session 2026-06-11.

---

## HARD CONSTRAINTS (every task)

- **MUST NOT affect current SEO.** Owner directive 2026-06-11 + `feedback_seo_safety_ipu`. **Never** change an existing `<title>`, meta description, canonical, H1, URL, or visible body copy on any page that currently ranks. This plan is restricted to: fixing broken/dead assets, CSS/asset changes, ADDING sitemap entries, and ADDING head metadata/schema **only where none currently exists**. Anything that edits an existing ranking element is **out of scope** — see the "Owner-Gated (NOT in this plan)" appendix.
- **No prod deploy without owner go-ahead.** Task 6 is the gate. Run the full pre-deploy quality check (lint + visual + curl-verify every changed file) and capture the Search Console baseline before any SEO-touching file ships.
- **Scoped deploy only** (`deploy.py --manifest`), never `--sync`. Touches only the listed files.
- Work on branch `claude/2026-04-30-ipu-session`, no worktree. Spec + this plan are authoritative over existing file state.

---

## Files

- Modify: `website_download/BPIT.php:56`, `website_download/BVP.php:117` (Task 1)
- Modify: `website_download/course/index.php` (Task 2)
- Modify: `website_download/sitemap.xml` (Task 3)
- Delete: `website_download/assets/css/critical.min.css` (Task 4)
- Modify: `website_download/include/base-head.php` (Task 5 — additive critical-CSS override only)
- Modify: `website_download/include/base-footer.php` (Task 5 — `.go-top` sizing)

---

## Task 1: Fix the two 404 hero images (BPIT, BVP)

Both college pages reference hero JPGs that 404 on prod → blank LCP element + CLS. No BPIT/BVP-specific image exists in `assets/images/`. Repoint both to the existing generic admission hero (which has WebP + responsive twins) via the `webp_img()` helper, **keeping each page's existing `alt` text verbatim** (alt is descriptive of the college — do not change it). This is a broken→working asset fix: strictly SEO-positive, touches no ranking text element.

**Files:** Modify `website_download/BPIT.php`, `website_download/BVP.php`

- [ ] **Step 1: Confirm both images are still 404 and the replacement exists.**

Run:
```bash
for i in bpit-college.jpg bvb-engineering-college.jpg IP-University-b-tech-admission.webp; do
  echo "$i -> $(curl -s -o /dev/null -w '%{http_code}' https://ipu.co.in/assets/images/$i)"; done
```
Expected: `bpit-college.jpg -> 404`, `bvb-engineering-college.jpg -> 404`, `IP-University-b-tech-admission.webp -> 200`

- [ ] **Step 2: Ensure `image-helper.php` is included in BPIT.php.** Near the top includes (after the breadcrumb-schema include at `BPIT.php:17`), confirm/add:

```php
<?php include_once __DIR__ . '/include/image-helper.php'; ?>
```

- [ ] **Step 3: Replace the broken `<img>` in BPIT.php (line 56).** Replace:

```php
<img fetchpriority="high" decoding="async" width="1000" height="600" src="assets/images/bpit-college.jpg" class="main-img" alt="BPIT College IP University">
```
with:
```php
<?= webp_img('assets/images/IP-University-b-tech-admission.jpg', 'BPIT College IP University', ['class' => 'main-img', 'width' => 1000, 'height' => 600, 'fetchpriority' => 'high', 'decoding' => 'async', 'lazy' => false]) ?>
```

- [ ] **Step 4: Repeat for BVP.php (line 117).** Ensure `image-helper.php` is included near the top, then replace:

```php
<img fetchpriority="high" decoding="async" width="1000" height="600" src="assets/images/bvb-engineering-college.jpg" class="main-img" alt="Bharati Vidyapeeth's College of Engineering IP University">
```
with:
```php
<?= webp_img('assets/images/IP-University-b-tech-admission.jpg', "Bharati Vidyapeeth's College of Engineering IP University", ['class' => 'main-img', 'width' => 1000, 'height' => 600, 'fetchpriority' => 'high', 'decoding' => 'async', 'lazy' => false]) ?>
```

> **NOTE — verify `webp_img()` signature before writing Step 3/4.** Open `include/image-helper.php` and confirm the argument order/option keys (`class`, `width`, `height`, `fetchpriority`, `decoding`, `lazy`). If the real signature differs, adapt the call to match it exactly — the goal is a `<picture>` with WebP source + `<img>` fallback carrying the SAME alt text, explicit width/height, and `fetchpriority="high"`, NOT lazy. If `webp_img()` cannot express `fetchpriority`, fall back to a hand-written `<picture>` block with the existing `.webp`/`.jpg` pair.

- [ ] **Step 5: Lint both files.**

Run: `php -l website_download/BPIT.php && php -l website_download/BVP.php`
Expected: `No syntax errors detected` for both.

- [ ] **Step 6: Render locally and confirm no 404 src + dimensions present.**

Run:
```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 &
sleep 1
curl -s "http://localhost:8000/BPIT.php" | grep -oE '<img[^>]*main-img[^>]*>|<source[^>]*>' | head
curl -s "http://localhost:8000/BVP.php" | grep -oE '<img[^>]*main-img[^>]*>|<source[^>]*>' | head
kill %1
```
Expected: a `<picture>`/`<source ... .webp>` + `<img ... IP-University-b-tech-admission... width="1000" height="600" ... alt="...">`. No reference to `bpit-college.jpg` / `bvb-engineering-college.jpg`.

- [ ] **Step 7: Commit.**

```bash
git add website_download/BPIT.php website_download/BVP.php
git commit -m "fix(images): repair 404 hero on BPIT/BVP — route to existing webp hero via webp_img() (alt unchanged)"
```

---

## Task 2: Add additive head metadata to the bare `course/` page

`course/index.php` ships `<title>IPU|GGSIPU</title>` and nothing else in `<head>` — no canonical, no meta description, no OG/Twitter, no schema. **Per the SEO constraint we do NOT touch the `<title>`** (changing it is a ranking-element edit — owner-gated). We only ADD a canonical, meta description, OG title/description, and a BreadcrumbList — all of which can only help or be neutral.

**Files:** Modify `website_download/course/index.php`

- [ ] **Step 1: Locate the head close.** The current head ends:

```php
	<!--====== Title ======-->
	<title>IPU|GGSIPU</title>
	

</head>
```

- [ ] **Step 2: Insert additive head tags immediately AFTER the `<title>` line and BEFORE `</head>`.** Leave the `<title>` line exactly as-is. Insert:

```php
	<link rel="canonical" href="https://ipu.co.in/course/">
	<meta name="description" content="Explore courses offered under GGSIPU (IP University) — B.Tech, BBA, BCA, B.Com, BA LLB, MBA and more. Admission process, eligibility and counselling guidance.">
	<meta name="robots" content="index, follow">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://ipu.co.in/course/">
	<meta property="og:title" content="Courses Offered Under GGSIPU (IP University) — Admission Guide">
	<meta property="og:description" content="B.Tech, BBA, BCA, B.Com, BA LLB, MBA and more under IP University. Eligibility, process and counselling guidance.">
	<meta name="twitter:card" content="summary_large_image">
```

- [ ] **Step 3: Add a BreadcrumbList just before `</body>`** (find the closing `</body>` near the footer include). Insert before it:

```php
<?php $breadcrumbs = [['Home', '/'], ['Courses', '']]; include __DIR__ . '/../include/components/breadcrumb-schema.php'; ?>
```

- [ ] **Step 4: Lint.**

Run: `php -l website_download/course/index.php`
Expected: `No syntax errors detected`.

- [ ] **Step 5: Verify additive tags present AND title unchanged.**

Run:
```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 &
sleep 1
echo "--- title (must still be IPU|GGSIPU) ---"; curl -s "http://localhost:8000/course/" | grep -oiE '<title>[^<]*</title>'
echo "--- canonical + description + breadcrumb ---"; curl -s "http://localhost:8000/course/" | grep -ciE 'rel="canonical"|name="description"|BreadcrumbList'
kill %1
```
Expected: title still `<title>IPU|GGSIPU</title>` (unchanged), and the second grep count = 3.

- [ ] **Step 6: Validate the new JSON-LD parses.**

Run:
```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s "http://localhost:8000/course/" | python3 -c "import sys,re,json; [json.loads(b) for b in re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S)]; print('JSON-LD OK')"
kill %1
```
Expected: `JSON-LD OK` (no exception).

- [ ] **Step 7: Commit.**

```bash
git add website_download/course/index.php
git commit -m "feat(seo): additive head metadata on /course/ (canonical+meta+OG+BreadcrumbList) — title untouched"
```

---

## Task 3: Add the 19 missing real pages to sitemap.xml

19 genuine content pages (the entire `cuet-*` cluster, both big cutoff pages, `mca/mtech/med`, `vips`, etc.) are absent from `sitemap.xml`, so they rely on internal links alone for discovery. Adding `<loc>` entries is purely additive — it cannot demote any currently-ranked page.

**Files:** Modify `website_download/sitemap.xml`

- [ ] **Step 1: Curl-verify each candidate returns 200 (not a redirect) before adding.**

Run:
```bash
for p in bvicam-admission cuet-admission-ipu cuet-bba-admission-ipu cuet-bcom-admission-ipu \
  cuet-btech-admission-ipu cuet-law-admission-ipu dspsr-admission economics-admission-ip-university \
  ipu-ba-llb-cutoff ipu-bba-cutoff law-3-year-admission-ipu \
  maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi mca-admission-ipu \
  med-admission-ipu mtech-admission-ipu top-btech-colleges-ipu-comparison vips-admission \
  dist-admission privacy-policy; do
  echo "$p -> $(curl -s -o /dev/null -w '%{http_code}' https://ipu.co.in/$p.php)"; done
```
Expected: each prints `200`. **Drop any that return 301/302/404 from the add-list.** (The known low-value typo stubs `sbit/tiips/tribhuvan/usct-admission.php` are intentionally excluded.)

- [ ] **Step 2: Append the verified URLs inside `<urlset>`, before `</urlset>`.** Use today's date as `lastmod`. Use `priority` 0.70 for content pages, 0.30 for `privacy-policy`. Pattern (one block per verified page):

```xml
<!-- ================= ADDED 2026-06-11 (discovery gap) ================= -->
<url>
<loc>https://ipu.co.in/cuet-admission-ipu.php</loc>
<lastmod>2026-06-11</lastmod>
<priority>0.70</priority>
</url>
<url>
<loc>https://ipu.co.in/ipu-bba-cutoff.php</loc>
<lastmod>2026-06-11</lastmod>
<priority>0.70</priority>
</url>
<!-- ...one <url> block per remaining verified page... -->
<url>
<loc>https://ipu.co.in/privacy-policy.php</loc>
<lastmod>2026-06-11</lastmod>
<priority>0.30</priority>
</url>
```

- [ ] **Step 3: Validate the XML is well-formed and the count rose by the number added.**

Run:
```bash
python3 -c "import xml.dom.minidom,sys; xml.dom.minidom.parse('website_download/sitemap.xml'); print('XML OK')"
grep -c '<loc>' website_download/sitemap.xml
```
Expected: `XML OK`; loc count = 139 + (number of pages that passed Step 1).

- [ ] **Step 4: Commit.**

```bash
git add website_download/sitemap.xml
git commit -m "feat(seo): add ~19 missing content pages to sitemap.xml (additive discovery fix)"
```

---

## Task 4: Delete the dead `critical.min.css`

64KB file in the repo, returns 404 live, referenced nowhere (verified zero refs in session). Non-SEO cleanup.

**Files:** Delete `website_download/assets/css/critical.min.css`

- [ ] **Step 1: Re-confirm zero references.**

Run: `grep -rn "critical.min.css" website_download/ ; echo "exit=$?"`
Expected: no matches (`exit=1`). If ANY match appears, STOP — do not delete.

- [ ] **Step 2: Delete and commit.**

```bash
git rm website_download/assets/css/critical.min.css
git commit -m "chore(assets): remove dead critical.min.css (64KB, 404 live, zero refs)"
```

> The file is already 404 on prod, so no prod deletion is needed — this is repo hygiene only. Do NOT add it to the deploy manifest.

---

## Task 5: Mobile UX nits — `.go-top` overlap/size + icon `font-display`

The `.go-top` button (`bottom:20px; left:20px; z-index:998`, 40×40px) sits under the full-width `.mobile-call-cta` bar (`bottom:0; z-index:9999`) on phones and is below the 44px tap minimum; icon `@font-face` blocks lack `font-display`. CSS-only, non-SEO.

**Files:** Modify `website_download/include/base-footer.php` (`.go-top` inline style) and `website_download/include/base-head.php` (append to the inline critical-CSS `<style>` block — do NOT edit minified `bundle.min.css`).

- [ ] **Step 1: Find the `.go-top` markup/style in base-footer.php.**

Run: `grep -n 'go-top' website_download/include/base-footer.php`

- [ ] **Step 2: In the inline critical CSS in `base-head.php`, append a mobile override** (inside the existing `<style>...</style>` block, near the `.mobile-call-cta` rules around lines 196–202):

```css
@media (max-width: 768px){
  .go-top{ width:48px; height:48px; bottom:calc(84px + env(safe-area-inset-bottom)) !important; z-index:9998; }
}
.go-top{ width:48px; height:48px; }
/* icon fonts: avoid FOIT */
@font-face{ font-display:swap; }
```

> The bare `@font-face{font-display:swap}` above is a no-op placeholder — REMOVE it. Instead, if the Flaticon/FontAwesome `@font-face` rules are reachable in an editable (non-minified) CSS source, add `font-display:swap;` there. If they live ONLY in minified `bundle.min.css`, skip the font-display change in this plan and note it for the Phase 2 CSS work (editing minified output by hand is error-prone). Keep only the `.go-top` rules.

- [ ] **Step 3: Lint base-head.php and base-footer.php.**

Run: `php -l website_download/include/base-head.php && php -l website_download/include/base-footer.php`
Expected: `No syntax errors detected` for both.

- [ ] **Step 4: Verify the override renders on a page that has the mobile CTA.**

Run:
```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s "http://localhost:8000/IPU-B-Tech-admission-2026.php" | grep -c 'go-top'
kill %1
```
Expected: ≥1 (markup present; visual overlap check happens in the Task 6 quality pass).

- [ ] **Step 5: Commit.**

```bash
git add website_download/include/base-head.php website_download/include/base-footer.php
git commit -m "fix(mobile): lift .go-top above call-bar + 48px tap target (CSS-only, non-SEO)"
```

---

## Task 6: Pre-deploy gate — SC baseline, quality check, gated deploy

**Files:** none (process task). **This task BLOCKS on owner go-ahead before the FTP push.**

- [ ] **Step 1: Capture a Search Console watch-term baseline** (the SEO-touching changes are sitemap + course-head; additive only, but baseline anyway per `feedback_seo_safety_ipu`).

Run: `seo/.venv/bin/python seo/scripts/fetch_sc.py || echo "GSC API not yet configured — use xlsx fallback per seo/README.md"`
Then snapshot watch terms to `seo/baselines/2026-06-11-phase2a-baseline.csv`. Record current positions for the course/cutoff/cuet watch terms.

- [ ] **Step 2: Build the deploy manifest** (Task 4's deletion is repo-only — excluded):

```bash
cat > /tmp/phase2a-manifest.txt <<'EOF'
website_download/BPIT.php
website_download/BVP.php
website_download/course/index.php
website_download/sitemap.xml
website_download/include/base-head.php
website_download/include/base-footer.php
EOF
```

- [ ] **Step 3: Full pre-deploy quality check** — lint every changed PHP file + localhost crosslink walk:

```bash
for f in $(grep '\.php$' /tmp/phase2a-manifest.txt); do php -l "$f" | grep -q "No syntax" && echo "ok $f" || echo "FAIL $f"; done
```
Expected: all `ok`. Then load BPIT, BVP, course/ on `php -S localhost:8000` and eyeball: hero renders, nav/footer intact, mobile go-top clears the call bar.

- [ ] **Step 4: Dry-run the deploy.**

Run: `python3 deploy.py --manifest /tmp/phase2a-manifest.txt --dry-run`
Expected: plan lists exactly the 6 files mapped to `/public_html/`, no deletions.

- [ ] **Step 5: 🚦 PAUSE — request owner go-ahead.** Present the dry-run plan. Do NOT push without explicit approval.

- [ ] **Step 6: On approval, real push + verify live.**

```bash
set -a; source .env; set +a
python3 deploy.py --manifest /tmp/phase2a-manifest.txt
```
Then verify live (cache-busted):
```bash
for u in BPIT.php BVP.php; do echo "$u hero: $(curl -s https://ipu.co.in/$u | grep -oE 'src="[^"]*IP-University[^"]*"' | head -1)"; done
echo "course canonical: $(curl -s https://ipu.co.in/course/ | grep -c 'rel="canonical"')  title: $(curl -s https://ipu.co.in/course/ | grep -oiE '<title>[^<]*</title>')"
echo "sitemap locs: $(curl -s https://ipu.co.in/sitemap.xml | grep -c '<loc>')"
for i in bpit-college.jpg bvb-engineering-college.jpg; do echo "$i now: $(curl -s -o /dev/null -w '%{http_code}' https://ipu.co.in/assets/images/$i)"; done
```
Expected: heroes point to `IP-University-...`; course canonical=1 and title STILL `IPU|GGSIPU`; sitemap loc count risen; old broken hero filenames now irrelevant (pages no longer reference them).

- [ ] **Step 7: Resubmit sitemap in Search Console** (owner action) and schedule a watch-term recheck in 7 days; stop-loss revert if any watch term drops > 2 positions.

---

## Self-review notes
- Every task touches only assets, sitemap additions, or head metadata where none existed. No existing `<title>`/meta/canonical/H1/URL/body-copy on a ranking page is modified → satisfies "must not affect current SEO."
- Task 1 changes an `<img src>` + keeps `alt` verbatim (broken→working asset; SEO-positive).
- Task 2 explicitly preserves the `<title>` and only adds tags.
- `webp_img()` signature is verified at execution (Task 1 NOTE) rather than assumed.

---

## Owner-Gated (NOT in this plan — touch ranking elements, need owner decision)

These were surfaced by the reanalysis but are **excluded** because they edit elements that could affect current rankings. Bring to owner separately:

1. **`course/` `<title>` rewrite** — current `IPU|GGSIPU` is junk, but swapping it is a ranking-element change. Verify in Search Console that `/course/` ranks for nothing meaningful, then change with baseline+stop-loss.
2. **Duplicate `BreadcrumbList` on ~29 template college pages** (`hero-banner.php` + `breadcrumb-schema.php` both emit one). Removing one is low-risk/positive but edits structured data on ranking pages — gate it.
3. **`blog.php` duplicate `<!DOCTYPE>/<html>/<head>`** — invalid DOM; mechanical fix keeps title/meta verbatim but touches a ranking page; gate it.
4. **Near-duplicate BBA titles** (`comprehensive-guide…top-10` vs `top-bba-colleges-ipu`) cannibalizing — owner picks the primary; canonical/title change.

## Follow-on plans (separate, after 2A ships)
- **Phase 2 (speed-proper, SEO-neutral):** S1 edge-cache unlock (drop `Vary: User-Agent` + gate `session_start()` to POSTs), S4 defer Bootstrap CSS, S3 site-wide `webp_img()` rollout (47 raw `<img>` across 28 files), preloader on `DOMContentLoaded`.
- **Phase 3 (additive SEO):** O3 pillar inbound links + hub→spoke linking, O4 comparison tables, O5 missing schema (CollegeOrUniversity/Breadcrumb/FAQ), O7 llms.txt refresh + Org sameAs enrichment.
