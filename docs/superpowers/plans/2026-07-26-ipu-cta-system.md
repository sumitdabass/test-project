# ipu.co.in CTA System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the phone call the single loudest action on every ipu.co.in page — via an exclusive action colour, a Call+Enquire sticky bar, a persistent header call button, GA4 call-click attribution, FAQ phone cleanup, and a time-aware out-of-hours swap.

**Architecture:** All new CTA CSS/JS lives in the Phase-2-clean, globally-included `include/base-nav.php` (reaches 95 pages, cascades after `base-head.php` so it wins). No `base-head.php` edits — it carries the HELD Phase-2 commit. Verification is `php -l` + local headless-Chrome render + live `curl`, not a unit-test runner (this is a static PHP site).

**Tech Stack:** Vanilla PHP includes, inline CSS/JS, existing GA4 `gtag` (`G-9VS3CTJ8SV`) + GTM (`GTM-5GXCN7Z`), `deploy.py` file-scoped FTP.

## Global Constraints

- **Never edit `include/base-head.php`** — it carries HELD Phase-2 commit `a7627b8` (deploy after 2026-08-15). All CTA CSS/JS goes in `include/base-nav.php`.
- **No SEO-frozen changes:** never touch any page's URL, `<title>`, meta description, canonical, or H1.
- **`--action:#ff7a1a` is for CALLING only** — no heading/tag/border/icon/decoration uses it.
- **`--action-label:#0d1b6e` (navy) on `--action`; never white** (white=2.6:1 fails WCAG AA; navy=5.75:1 passes).
- **One enquiry form per page:** the "Enquire" action links to the existing form anchor; never add a second form.
- **Measure via GA4** `G-9VS3CTJ8SV` (site forms do not feed a CRM).
- **Office hours:** IST (Asia/Kolkata), Monday–Saturday, 09:00–19:00 (open = `[09:00, 19:00)`).
- **Deploy:** branch `claude/2026-04-30-ipu-session`; `python3 deploy.py --files <paths>` (FTP creds from `.env`: `FTP_HOST/FTP_USER/FTP_PASS`, source then shred). Never deploy `include/base-head.php`.
- **Verify a file is Phase-2-clean before deploying it:** `git log --oneline a7627b8 -1 -- <file>` must NOT return `a7627b8`, and `grep -n webp_img <file>` must be empty (else it carries held work).

---

## File Structure

- `include/base-nav.php` (modify) — owns the entire CTA system: `:root` tokens, header button, sticky bar, hero-button recolour override, college-block button injector, attribution listener, and (Wave 2b) the server-side office-open state. Global to 95 pages.
- `include/components/sidebar-enquiry.php` (modify, Wave 1) — add `id="enquiry-form"` anchor so the "Enquire" CTA scrolls to the form.
- ~94 page files with `$faqs` arrays (modify, Wave 2a) — strip phone numbers from `'answer' =>` strings.

Rollout waves: **Wave 1** = Tasks 1–7 (one deploy). **Wave 2a** = Task 8 (FAQ, gated). **Wave 2b** = Task 9 (out-of-hours).

---

## Task 1: CTA tokens + GA4 attribution listener

**Files:**
- Modify: `include/base-nav.php` (top `<style>` + a `<script>` near the existing menu script)

**Interfaces:**
- Produces: CSS custom properties `--action`, `--action-label`, `--ink`, `--border-strong` on `:root`; a global `click` delegate firing `gtag('event','cta_call'|'cta_enquire', {cta_src, page_path})` for any `[data-cta-src]`.

- [ ] **Step 1: Add the token + CTA `<style>` block** immediately after the opening `?>`/before `<!-- GTM noscript fallback -->` in `include/base-nav.php`:

```html
<style>
  :root{ --action:#ff7a1a; --action-label:#0d1b6e; --ink:#0d1b6e; --border-strong:#d4d3df; }

  /* Desktop header call button */
  .nav-call-btn{display:none}
  @media(min-width:992px){
    .nav-call-btn{display:inline-flex;align-items:center;gap:7px;background:var(--action);color:var(--action-label);
      font-weight:800;font-size:14px;line-height:1;text-decoration:none;padding:11px 18px;border-radius:999px;
      white-space:nowrap;flex:0 0 auto;transition:filter .15s ease}
    .nav-call-btn:hover{filter:brightness(.92);color:var(--action-label)}
    .nav-call-btn svg{fill:var(--action-label)}
    .header-area .navbar-nav.mx-auto{margin-right:12px !important}
  }

  /* Recolour ONLY call-type primary buttons (hero / cta-strip / hero-banner) — icon is currentColor */
  .ipu-btn-primary[href^="tel:"]{background:var(--action) !important;color:var(--action-label) !important}
  .ipu-btn-primary[href^="tel:"] svg{fill:var(--action-label)}

  /* College-block call button (injected by JS on ranked pages) */
  .cb-call-btn{display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:var(--action);
    color:var(--action-label);font-weight:800;font-size:14px;text-decoration:none;padding:9px 16px;border-radius:999px}
  .cb-call-btn svg{fill:var(--action-label)}

  /* Mobile sticky bar — Call(7) + Enquire(3), mobile only */
  @media(max-width:768px){
    .mobile-call-cta{background:#fff !important;padding:10px 12px calc(10px + env(safe-area-inset-bottom)) !important;
      border-top:1px solid var(--border-strong);box-shadow:0 -2px 10px rgba(5,0,56,.08) !important;
      display:flex !important;gap:8px;align-items:stretch}
    .mobile-call-btn{flex:7 1 0;width:auto !important;background:var(--action) !important;color:var(--action-label) !important;
      border-radius:999px !important;min-height:50px;font-size:16px;font-weight:800;box-shadow:none !important;margin:0}
    .mobile-call-btn svg{fill:var(--action-label)}
    .mobile-enquire-btn{flex:3 1 0;display:flex;align-items:center;justify-content:center;min-height:50px;
      background:#fff;border:1.5px solid var(--ink);border-radius:999px;color:var(--ink);font-weight:800;
      font-size:15px;text-decoration:none;white-space:nowrap}
    body{padding-bottom:74px}
  }
  @media(min-width:769px){ .mobile-call-cta{display:none !important} }
</style>
```

- [ ] **Step 2: Add the attribution `<script>`** just before the closing of the existing menu-toggle `<script>`'s IIFE region — append a new `<script>` block after it:

```html
<script>
(function(){
  document.addEventListener('click', function(e){
    var el = e.target.closest && e.target.closest('[data-cta-src]');
    if(!el) return;
    var src = el.getAttribute('data-cta-src') || 'unknown';
    var evt = src.indexOf('enquire') > -1 ? 'cta_enquire' : 'cta_call';
    if(typeof window.gtag === 'function'){
      window.gtag('event', evt, { cta_src: src, page_path: location.pathname });
    } else if(window.dataLayer){
      window.dataLayer.push({ event: evt, cta_src: src, page_path: location.pathname });
    }
  }, true);
})();
</script>
```

- [ ] **Step 3: Lint**

Run: `php -l website_download/include/base-nav.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add website_download/include/base-nav.php
git commit -m "feat(cta): action-colour tokens + GA4 call-click attribution listener"
```

---

## Task 2: Desktop header call button

**Files:**
- Modify: `include/base-nav.php` (inside `<nav>`, after the `#mainNav` collapse div, before `</nav>`)

**Interfaces:**
- Consumes: `.nav-call-btn` CSS from Task 1.
- Produces: a persistent desktop-only orange call button, `data-cta-src="header"`.

- [ ] **Step 1: Insert the button markup** after `</div>` that closes `#mainNav` and before `</nav>`:

```html
        <!-- Desktop header call button -->
        <a class="nav-call-btn" href="tel:+919899991342" data-cta-src="header">
          <svg width="17" height="17" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
          Call now
        </a>
```

- [ ] **Step 2: Render-verify at 1280px**

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 &
sleep 1.5
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --hide-scrollbars \
  --window-size=1280,760 --screenshot=/tmp/hdr.png "http://127.0.0.1:8899/IPU-B-Tech-admission-2026.php" >/dev/null 2>&1
kill %1 2>/dev/null
```
Expected: `/tmp/hdr.png` shows an orange "Call now" pill (navy label) in the top-right, not clipped.

- [ ] **Step 3: Commit**

```bash
git add website_download/include/base-nav.php
git commit -m "feat(cta): persistent desktop header call button"
```

---

## Task 3: Mobile sticky bar — Call + Enquire

**Files:**
- Modify: `include/base-nav.php` (the existing `<!-- Mobile Sticky Call CTA -->` block)

**Interfaces:**
- Consumes: `.mobile-call-cta/.mobile-call-btn/.mobile-enquire-btn` CSS from Task 1.
- Produces: two-child sticky bar; `data-cta-src="sticky"` and `="sticky-enquire"`; Enquire links to `#enquiry-form` (created in Task 5).

- [ ] **Step 1: Replace the existing sticky-bar block** with:

```html
<!-- Mobile Sticky Call CTA — Call (7) + Enquire (3) -->
<div class="mobile-call-cta" id="mobileCallCTA">
  <a href="tel:+919899991342" class="mobile-call-btn" data-cta-src="sticky">
    <svg width="18" height="18" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    Call a counsellor
  </a>
  <a href="#enquiry-form" class="mobile-enquire-btn" data-cta-src="sticky-enquire">Enquire</a>
</div>
```

- [ ] **Step 2: Render-verify at 375px**

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 &
sleep 1.5
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --hide-scrollbars \
  --force-device-scale-factor=2 --window-size=375,812 --screenshot=/tmp/sticky.png "http://127.0.0.1:8899/IPU-B-Tech-admission-2026.php" >/dev/null 2>&1
kill %1 2>/dev/null
```
Expected: `/tmp/sticky.png` shows a white bottom bar with an orange "Call a counsellor" (navy label, ~70% width) and an outline navy "Enquire" (~30%), both fully visible.

- [ ] **Step 3: Commit**

```bash
git add website_download/include/base-nav.php
git commit -m "feat(cta): mobile sticky bar Call + Enquire (7:3), reserved height"
```

---

## Task 4: Hero / primary call-button recolour

**Files:**
- No new edit — the `.ipu-btn-primary[href^="tel:"]` override in Task 1 already recolours `page-hero.php`, `hero-banner.php`, and `cta-strip.php` call buttons.

**Interfaces:**
- Consumes: Task 1 override CSS.

- [ ] **Step 1: Confirm the three hero components use `.ipu-btn-primary` on their `tel:` link**

Run: `grep -rn 'ipu-btn-primary' website_download/include/components/page-hero.php website_download/include/components/hero-banner.php website_download/include/components/cta-strip.php`
Expected: each shows `class="ipu-btn-primary ...` on an `<a href="tel:...">`.

- [ ] **Step 2: Add `data-cta-src="hero"` to each** of those three `tel:` anchors (so hero calls are attributed). Edit each file's call anchor to include `data-cta-src="hero"`.

- [ ] **Step 3: Render-verify the hero label is navy (not white)**

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 &
sleep 1.5
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --hide-scrollbars \
  --window-size=1280,760 --screenshot=/tmp/hero.png "http://127.0.0.1:8899/IPU-B-Tech-admission-2026.php" >/dev/null 2>&1
kill %1 2>/dev/null
```
Expected: `/tmp/hero.png` — the hero "Call: 9899991342" button has a **navy** label + icon on orange (no white text).

- [ ] **Step 4: Commit**

```bash
git add website_download/include/components/page-hero.php website_download/include/components/hero-banner.php website_download/include/components/cta-strip.php
git commit -m "feat(cta): navy label on hero call buttons + hero attribution tag"
```

---

## Task 5: Enquiry-form anchor

**Files:**
- Modify: `include/components/sidebar-enquiry.php` (the `.ipu-enquiry__form-wrap` div)

**Interfaces:**
- Produces: `id="enquiry-form"` target for the sticky "Enquire" link (Task 3).

- [ ] **Step 1: Verify the file is Phase-2-clean**

Run: `git log --oneline a7627b8 -1 -- website_download/include/components/sidebar-enquiry.php; grep -c webp_img website_download/include/components/sidebar-enquiry.php`
Expected: first command does NOT print `a7627b8`; grep prints `0`.

- [ ] **Step 2: Add the anchor id** — change `<div class="ipu-enquiry__form-wrap">` to:

```php
  <div class="ipu-enquiry__form-wrap" id="enquiry-form">
```

- [ ] **Step 3: Lint**

Run: `php -l website_download/include/components/sidebar-enquiry.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Verify the anchor resolves**

Run: `cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 & sleep 1.5; curl -s http://127.0.0.1:8899/IPU-B-Tech-admission-2026.php | grep -c 'id="enquiry-form"'; kill %1`
Expected: `1`

- [ ] **Step 5: Commit**

```bash
git add website_download/include/components/sidebar-enquiry.php
git commit -m "feat(cta): #enquiry-form anchor for the Enquire CTA"
```

---

## Task 6: College-block call buttons (ranked pages)

**Files:**
- Modify: `include/base-nav.php` (append to the attribution `<script>` region a small injector)

**Interfaces:**
- Consumes: `.cb-call-btn` CSS from Task 1.
- Produces: a `tel:` call button appended to each `.college-block` on ranked pages, `data-cta-src="row"`.

- [ ] **Step 1: Confirm `.college-block` is the shared ranked-profile class across the ranked pages**

Run: `grep -lc 'college-block' website_download/top-law-colleges-ipu.php website_download/best-btech-colleges-ipu.php website_download/top-bba-colleges-ipu.php website_download/top-bca-colleges-ipu.php website_download/top-mba-colleges-ipu.php 2>/dev/null`
Expected: each ranked page reports a non-zero count. (Pages without `.college-block` simply get no injection — safe.)

- [ ] **Step 2: Add the injector `<script>`** in `include/base-nav.php` after the attribution script:

```html
<script>
(function(){
  var blocks = document.querySelectorAll('.college-block');
  if(!blocks.length) return;
  blocks.forEach(function(b){
    if(b.querySelector('.cb-call-btn')) return;
    var h = b.querySelector('h3');
    var name = h ? h.textContent.replace(/^\s*\d+\s*/,'').split('–')[0].trim() : 'this college';
    var a = document.createElement('a');
    a.className = 'cb-call-btn';
    a.href = 'tel:+919899991342';
    a.setAttribute('data-cta-src', 'row');
    a.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg> Ask about ' + name;
    b.appendChild(a);
  });
})();
</script>
```

- [ ] **Step 3: Render-verify on a ranked page**

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 &
sleep 1.5
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --hide-scrollbars \
  --window-size=1280,1400 --screenshot=/tmp/rows.png "http://127.0.0.1:8899/top-law-colleges-ipu.php" >/dev/null 2>&1
kill %1 2>/dev/null
```
Expected: `/tmp/rows.png` shows an orange "Ask about <college>" button (navy label) inside each ranked block.

- [ ] **Step 4: Commit**

```bash
git add website_download/include/base-nav.php
git commit -m "feat(cta): inject per-college call button on ranked pages"
```

---

## Task 7: Wave 1 verification + deploy

**Files:** none new — deploys Tasks 1–6.

- [ ] **Step 1: Full local render sweep** (mobile 375 + desktop 1280) across page types; confirm no PHP errors and correct CTA rendering:

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 &
sleep 1.5
for p in IPU-B-Tech-admission-2026.php ipu-bba-cutoff.php ipu-colleges-list.php vips-admission.php index.php top-law-colleges-ipu.php; do
  curl -s -o /dev/null -w "%{http_code} $p\n" "http://127.0.0.1:8899/$p"
done
grep -i "error\|fatal\|undefined" /tmp/s.log || echo "server log clean"
kill %1 2>/dev/null
```
Expected: all `200`; server log clean.

- [ ] **Step 2: Confirm gtag is present on LIVE prod** (attribution dependency)

Run: `curl -s https://ipu.co.in/IPU-B-Tech-admission-2026.php | grep -oE "gtag\(|G-9VS3CTJ8SV|dataLayer" | sort -u`
Expected: at least `dataLayer` and ideally `G-9VS3CTJ8SV`/`gtag(`. If only `dataLayer`, the listener's dataLayer fallback covers it — no change needed.

- [ ] **Step 3: Verify each deploy file is Phase-2-clean**

Run: `for f in include/base-nav.php include/components/sidebar-enquiry.php include/components/page-hero.php include/components/hero-banner.php include/components/cta-strip.php; do echo "== $f =="; git log --oneline a7627b8 -1 -- website_download/$f; grep -c webp_img website_download/$f; done`
Expected: no line prints `a7627b8`; every grep prints `0`.

- [ ] **Step 4: Deploy Wave 1 files**

```bash
cd /Users/Sumit/test-project
tmp=$(mktemp); grep -E '^(FTP_HOST|FTP_USER|FTP_PASS|REMOTE_ROOT)=' .env > "$tmp"; set -a; source "$tmp"; set +a; shred -u "$tmp" 2>/dev/null || rm -f "$tmp"
python3 deploy.py --files \
  website_download/include/base-nav.php \
  website_download/include/components/sidebar-enquiry.php \
  website_download/include/components/page-hero.php \
  website_download/include/components/hero-banner.php \
  website_download/include/components/cta-strip.php
```
Expected: `done: 5 uploaded, 0 delete(s) attempted`. (NEVER include `include/base-head.php`.)

- [ ] **Step 5: Live post-deploy verification**

```bash
for p in IPU-B-Tech-admission-2026.php ipu-colleges-list.php top-law-colleges-ipu.php index.php; do
  curl -s -o /dev/null -w "%{http_code} $p\n" "https://ipu.co.in/$p"
done
curl -s https://ipu.co.in/IPU-B-Tech-admission-2026.php | grep -oE 'nav-call-btn|mobile-enquire-btn|data-cta-src="[a-z-]+"' | sort | uniq -c
```
Expected: all `200`; `nav-call-btn`, `mobile-enquire-btn`, and `data-cta-src` tags present.

- [ ] **Step 6: Manual GA4 smoke** — open `https://ipu.co.in/` on a phone, tap the sticky "Call a counsellor", and confirm a `cta_call` event with `cta_src=sticky` appears in GA4 Realtime. (Document result in the deploy log.)

- [ ] **Step 7: Log the deploy**

```bash
# Append a Wave-1 deploy note to seo/rechecks/<today>/decision.md (create dir if needed), then:
git add -A && git commit -m "docs(cta): log Wave 1 CTA deploy"
```

---

## Task 8: FAQ phone removal (Wave 2a — gated)

**Files:**
- Modify: ~94 page files containing `9899991342` inside `$faqs` `'answer' =>` strings.

**Interfaces:**
- The `$faqs` array feeds both the visible FAQ and the `FAQPage` JSON-LD, so one edit fixes both. Confirm coupling per template in Step 1.

- [ ] **Step 1: Confirm `$faqs` single-sources the schema** on a sample page

Run: `grep -n 'FAQPage\|foreach.*faqs\|\$faqs' website_download/IPU-B-Tech-admission-2026.php | head`
Expected: the `$faqs` array is iterated both into visible HTML and into a `FAQPage` script. If a page hardcodes FAQ schema separately, note it for manual handling.

- [ ] **Step 2: List all affected answer lines** into a worklist

Run: `grep -rln "9899991342" website_download/*.php | xargs grep -l "'answer' =>" > /tmp/faq_pages.txt; wc -l /tmp/faq_pages.txt`
Expected: ~94 files.

- [ ] **Step 3: For each page, edit every `'answer' => '...'` string** to remove the phone number, using one of two patterns (choose per sentence to keep readable prose — never leave a dangling fragment):
  - Drop a trailing call sentence entirely: remove `Call <a href="tel:+919899991342"...>9899991342</a> for ...` up to and including its sentence-ending period.
  - Rephrase an inline mention: replace `call <a href="tel:...">9899991342</a>` with `our counsellors can guide you` (or similar), keeping the surrounding sentence grammatical.

  Do NOT touch phone numbers outside `'answer' =>` strings (hero, sidebar, body CTAs stay). Work one file at a time; after each file:

  Run: `php -l website_download/<file>`
  Expected: `No syntax errors detected`

- [ ] **Step 4: Verify no phone numbers remain in any answer string**

Run: `for f in $(cat /tmp/faq_pages.txt); do awk "/'answer' =>/ && /9899991342/{print FILENAME\": \"NR}" "$f"; done`
Expected: no output.

- [ ] **Step 5: Local FAQ render check** on 3 sample pages — confirm answers read cleanly and `FAQPage` JSON-LD is still valid:

Run: `cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 & sleep 1.5; for p in IPU-B-Tech-admission-2026.php ipu-counselling.php ipu-bba-cutoff.php; do curl -s "http://127.0.0.1:8899/$p" | grep -c '"@type": *"Question"'; done; kill %1`
Expected: each prints its Question count (non-zero, unchanged from before).

- [ ] **Step 6: Commit + deploy**

```bash
git add -A && git commit -m "feat(cta): remove phone numbers from FAQ answer text (94 pages)"
# then deploy.py --manifest with /tmp/faq_pages.txt paths (verify each Phase-2-clean first, as in Task 7 Step 3)
```

- [ ] **Step 7: Stop-loss recheck (GATE)** — after the next GSC data window, compare watch terms in `seo/baselines/2026-06-11-primary-targets.csv`. Record verdict in `seo/rechecks/<date>/decision.md`. **Revert the FAQ edit on any page whose watch term drops >2 positions.**

---

## Task 9: Out-of-hours CTA swap (Wave 2b)

**Files:**
- Modify: `include/base-nav.php` (compute office-open in PHP; vary the sticky-bar markup)

**Interfaces:**
- Consumes: sticky-bar classes (Task 1/3).
- Produces: `$ipu_office_open` bool (IST); a closed-state sticky bar where Enquire is the navy primary and Call is a muted secondary.

- [ ] **Step 1: Compute office-open at the top of `include/base-nav.php`** (after the opening `<?php` comment):

```php
<?php
$__ist = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$__dow = (int)$__ist->format('N');   // 1=Mon .. 7=Sun
$__hr  = (int)$__ist->format('G');   // 0..23
$ipu_office_open = ($__dow >= 1 && $__dow <= 6 && $__hr >= 9 && $__hr < 19);
?>
```

- [ ] **Step 2: Make the sticky bar conditional** — replace the Task 3 sticky block with:

```php
<!-- Mobile Sticky Call CTA — office-hours aware -->
<div class="mobile-call-cta" id="mobileCallCTA">
<?php if ($ipu_office_open): ?>
  <a href="tel:+919899991342" class="mobile-call-btn" data-cta-src="sticky">
    <svg width="18" height="18" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    Call a counsellor
  </a>
  <a href="#enquiry-form" class="mobile-enquire-btn" data-cta-src="sticky-enquire">Enquire</a>
<?php else: ?>
  <a href="#enquiry-form" class="mobile-enquire-btn mobile-enquire-primary" data-cta-src="sticky-enquire">Send an enquiry</a>
  <a href="tel:+919899991342" class="mobile-call-btn mobile-call-muted" data-cta-src="sticky">Opens 9 AM</a>
<?php endif; ?>
</div>
```

- [ ] **Step 3: Add the closed-state CSS** to the Task 1 `@media(max-width:768px)` block:

```css
    .mobile-enquire-primary{flex:7 1 0;background:var(--ink) !important;color:#fff !important;border:none}
    .mobile-call-muted{flex:3 1 0;background:#eef2fb !important;color:var(--ink) !important;font-size:13px;opacity:.85}
    .mobile-call-muted svg{display:none}
```

- [ ] **Step 4: Test BOTH states** by temporarily overriding the clock — set `$ipu_office_open = true;` then `false;` and render:

```bash
cd website_download && php -S 127.0.0.1:8899 >/tmp/s.log 2>&1 & sleep 1.5
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --force-device-scale-factor=2 --window-size=375,812 --screenshot=/tmp/oh.png "http://127.0.0.1:8899/index.php" >/dev/null 2>&1
kill %1 2>/dev/null
```
Expected — open: orange Call + outline Enquire. Closed: navy "Send an enquiry" primary + muted "Opens 9 AM". Restore the real `$ipu_office_open` computation before committing.

- [ ] **Step 5: Lint + commit + deploy**

```bash
php -l website_download/include/base-nav.php   # No syntax errors detected
git add website_download/include/base-nav.php
git commit -m "feat(cta): out-of-hours sticky-bar swap (IST office hours)"
# deploy.py --files website_download/include/base-nav.php  (Phase-2-clean check first)
```

- [ ] **Step 6: Live verify** both states resolve (curl during and outside office hours, or inspect the rendered branch):

Run: `curl -s https://ipu.co.in/index.php | grep -oE 'mobile-enquire-primary|Call a counsellor|Opens 9 AM'`
Expected: shows the branch matching current IST time.

---

## Self-Review

**Spec coverage:** tokens (Task 1) · header button (Task 2) · sticky Call+Enquire (Task 3) · hero recolour + AA fix (Task 4) · enquiry anchor (Task 5) · college-row buttons (Task 6) · attribution GA4 (Task 1, wired first; verified Task 7) · Wave-1 deploy/verify (Task 7) · FAQ removal + stop-loss gate (Task 8) · out-of-hours IST swap (Task 9). All spec sections mapped.

**Guardrails honored:** no task edits `base-head.php`; no URL/title/meta/canonical/H1 change; Enquire links to existing form (no new form); `--action` used only on call CTAs; navy label everywhere on orange; deploy steps include the Phase-2-clean check.

**Type/name consistency:** `--action`/`--action-label`/`--ink`/`--border-strong`, `.nav-call-btn`, `.mobile-call-btn`, `.mobile-enquire-btn`, `.cb-call-btn`, `#enquiry-form`, `data-cta-src` values (`header`/`sticky`/`sticky-enquire`/`hero`/`row`), `$ipu_office_open` — all defined in Task 1/3/5 and reused consistently downstream.

**Deviation from spec (noted):** spec §college-row said "every ranked row gets a button"; the pages are static `.college-block` profiles, so Task 6 injects the button DRY via JS keyed on that class (1 code location) rather than 40 static edits — same user-visible outcome, progressive-enhancement caveat documented.
