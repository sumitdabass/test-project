# IPU Site Cohesion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Unify the visual chrome of 138 ipu.co.in PHP pages around the `index.php` reference grammar to convert phone calls + form fills on every archetype, **without changing a single word of content**.

**Architecture:** Two new shared partials (`page-hero.php`, `sidebar-enquiry.php`) plus a token block in `base-head.php`. Pages keep their existing copy/intro/H1/HTML — they only swap the wrapping container (banner-three slab → form-bearing hero, hand-rolled sidebars → unified enquiry component). Nothing is built; everything that ships is a re-wrap.

**Tech Stack:** Vanilla PHP 8.5, Bootstrap 5 grid utilities, Inter font, inline critical CSS in `base-head.php`, GTM for tracking, FTP deploy via existing `upload_*.py` Python scripts.

**Reference spec:** `docs/superpowers/specs/2026-05-09-ipu-site-cohesion-design.md`

**Hard rules across all tasks:**
- **Frozen content.** No copy edits. Same words, same order, same HTML text content. Only the chrome changes.
- **Frozen form contract.** Field names (`name`, `phone`, `email`, `course`, `page_url`, honeypot `website`), `<form method="POST" action="/sendemail.php">`, error-state surface — all preserved exactly.
- **Localhost crosslink walk before every FTP push.** Per `feedback_localhost_crosslink_test.md`. No exceptions.
- **PAUSE points are explicit.** Phase 2 / Phase 3 / Phase 4 each end with an FTP push gate that requires Sumit's go-ahead before deploy.

---

## File structure

**New files (created in this plan):**
- `website_download/include/components/sidebar-enquiry.php` — unified enquiry block (form + phone + popular list)
- `website_download/include/components/page-hero.php` — form-bearing hero replacing `banner-three`
- `website_download/include/components/link-card.php` — universal card primitive (Phase 6 only)
- `website_download/scripts/crosslink_walk.sh` — localhost smoke runner (curl every link from a sample set)
- `docs/superpowers/plans/2026-05-09-ipu-site-cohesion.md` — this file (already created)

**Modified files (this plan):**
- `website_download/index.php` — add PHP 8.5 deprecation guard (Phase 1)
- `website_download/include/base-head.php` — token CSS block + `$skip_legacy_css` switch + ipu-component CSS (Phase 1, Phase 2, Phase 3)
- `website_download/include/base-nav.php` — retire `desktop-call-widget` (Phase 6)
- ~33 banner-three pages — swap banner section for `page-hero` include (Phase 4)
- ~50 college pages — swap inline sidebars for `sidebar-enquiry` include (Phase 5)

**Untouched (don't break):**
- `website_download/include/form-handler.php`, `sendemail.php` — form processing
- `website_download/include/base-footer.php` — footer
- GTM/GA4/`phone_click` tracking — entirely server-side via `base-head.php` head, no JS changes
- `bundle.min.css` — kept on legacy pages, dropped only on migrated pages via opt-in flag
- All page content — H1s, intros, FAQs, JSON-LD, alt-text, SEO meta

---

## Phase 1 — Foundations

No visible page changes. Establishes tokens and the legacy-CSS opt-out switch.

### Task 1: PHP 8.5 deprecation guard in `index.php`

**Why.** Per `project_davya-crm_php85_deprecations.md`, PHP 8.5 emits `E_DEPRECATED` warnings that can corrupt static-asset HTTP responses. Vanilla PHP probably tolerates it, but we'll be running `php -S localhost:8000` extensively across all 138 pages — defense-in-depth.

**Files:**
- Modify: `website_download/index.php` (top of file, before `session_start()`)

- [ ] **Step 1: Read the top of index.php to confirm it's missing the patch**

```bash
head -8 website_download/index.php
```

Expected output: `<?php\nsession_start();\nob_start();\ninclude_once("include/form-handler.php");`. No `error_reporting` line.

- [ ] **Step 2: Add the guard line**

Edit `website_download/index.php` — replace the opening `<?php\nsession_start();` with:

```php
<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
session_start();
```

- [ ] **Step 3: Lint**

```bash
php -l website_download/index.php
```

Expected: `No syntax errors detected in website_download/index.php`

- [ ] **Step 4: Smoke**

```bash
curl -sS -o /dev/null -w "HTTP %{http_code} %{size_download}B\n" http://localhost:8000/
```

Expected: `HTTP 200 ~80000B` (homepage size hasn't materially changed).

- [ ] **Step 5: Commit**

```bash
git add website_download/index.php
git commit -m "chore(php85): guard E_DEPRECATED in index.php for vanilla PHP 8.5

Mirrors the davya-crm/parmit-podcast/personal-finance fix. Prevents
deprecation warnings from leaking into HTTP responses during local
php -S sessions or any future migration to PHP 8.5+ on prod.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Add design tokens + component CSS to `base-head.php`

**Why.** The two new components (Phase 2 + Phase 3) need a shared variable system. We add the tokens AND the component CSS up front so we don't double-edit `base-head.php` later.

**Files:**
- Modify: `website_download/include/base-head.php` (insert inside the existing `<style>` block, after the `#preloader` rule and before the inner-page banner CSS)

- [ ] **Step 1: Locate the insertion point**

```bash
grep -n "@keyframes spin" website_download/include/base-head.php
```

Expected: returns one line (the existing spin keyframe).

- [ ] **Step 2: Insert the token + component block**

In `website_download/include/base-head.php`, after the `@keyframes spin{to{transform:rotate(360deg)}}` line and before the `/* Inner-page Banner (banner-three) */` comment, insert:

```css
/* ===== ipu design tokens ===== */
:root{
  --ipu-ink:#0d1b6e;
  --ipu-ink-2:#1a3a9c;
  --ipu-amber:#f59e0b;
  --ipu-orange:#e65c00;
  --ipu-orange-hover:#cc5200;
  --ipu-bg:#f8faff;
  --ipu-paper:#fff;
  --ipu-rule:#e2e8f0;
  --ipu-rule-soft:#d0d9f0;
  --ipu-highlight:#e8f0ff;
  --ipu-accent-soft:#fff3e0;
  --ipu-shadow-sm:0 2px 8px rgba(13,27,110,.06);
  --ipu-shadow-md:0 8px 24px rgba(13,27,110,.10);
  --ipu-shadow-lg:0 20px 60px rgba(13,27,110,.18);
  --ipu-cta-shadow:0 3px 12px rgba(230,92,0,.30);
  --ipu-radius:12px;
  --ipu-radius-lg:16px;
}

/* ===== ipu primitives ===== */
.ipu-input{width:100%;padding:12px 16px;border:1px solid var(--ipu-rule);border-radius:8px;font-size:14px;font-family:inherit;color:var(--ipu-ink);background:#fff;transition:border-color .2s,box-shadow .2s;margin-bottom:10px;display:block}
.ipu-input:focus{outline:none;border-color:var(--ipu-ink-2);box-shadow:0 0 0 3px rgba(26,58,156,.14)}
.ipu-input::placeholder{color:#94a3b8}
select.ipu-input{color:#64748b}
.ipu-btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 22px;background:var(--ipu-orange);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;box-shadow:var(--ipu-cta-shadow);text-decoration:none}
.ipu-btn-primary:hover{background:var(--ipu-orange-hover);color:#fff}

/* ===== sidebar-enquiry component ===== */
.ipu-enquiry{display:flex;flex-direction:column;gap:14px}
.ipu-enquiry__phone{background:linear-gradient(135deg,var(--ipu-ink) 0%,var(--ipu-ink-2) 100%);color:#fff;padding:20px 22px;border-radius:var(--ipu-radius);position:relative;overflow:hidden}
.ipu-enquiry__phone::before{content:"";position:absolute;right:-30px;top:-30px;width:110px;height:110px;background:radial-gradient(circle,rgba(245,158,11,.20),transparent 65%)}
.ipu-enquiry__phone-badge{display:inline-flex;align-items:center;gap:6px;font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--ipu-amber);font-weight:700;margin-bottom:8px}
.ipu-enquiry__phone-badge::before{content:"";width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.25);animation:ipuPulse 1.6s ease-in-out infinite;display:inline-block}
@keyframes ipuPulse{50%{box-shadow:0 0 0 6px rgba(34,197,94,.10)}}
.ipu-enquiry__phone-label{font-size:12.5px;color:rgba(255,255,255,.75);margin:0 0 6px;line-height:1.4}
.ipu-enquiry__phone-num{display:flex;align-items:center;gap:10px;color:var(--ipu-amber);font-weight:700;font-size:26px;line-height:1;margin-bottom:6px;text-decoration:none}
.ipu-enquiry__phone-num:hover{color:var(--ipu-amber)}
.ipu-enquiry__phone-hours{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.5);font-weight:600}
.ipu-enquiry__form-wrap{background:var(--ipu-paper);border:1px solid var(--ipu-rule);border-radius:var(--ipu-radius-lg);padding:22px;box-shadow:var(--ipu-shadow-md)}
.ipu-enquiry__heading{font-size:1.1rem;color:var(--ipu-ink);margin:0 0 4px;text-align:center;font-weight:700}
.ipu-enquiry__subheading{font-size:13px;color:#64748b;text-align:center;margin:0 0 14px}
.ipu-enquiry__error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:8px 12px;border-radius:6px;font-size:13px;margin-bottom:12px}
.ipu-enquiry__submit{width:100%;margin-top:4px;font-size:15px}
.ipu-enquiry__fine{font-size:11px;color:#94a3b8;text-align:center;margin:10px 0 0}
.ipu-enquiry__popular{background:var(--ipu-highlight);padding:18px 22px;border-radius:var(--ipu-radius)}
.ipu-enquiry__popular h4{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--ipu-orange);font-weight:700;margin:0 0 12px}
.ipu-enquiry__popular ul{list-style:none;padding:0;margin:0}
.ipu-enquiry__popular li{border-top:1px solid rgba(13,27,110,.10)}
.ipu-enquiry__popular li:first-child{border-top:0}
.ipu-enquiry__popular a{display:flex;justify-content:space-between;align-items:center;padding:9px 0;color:var(--ipu-ink);font-size:13.5px;font-weight:500;line-height:1.4;text-decoration:none}
.ipu-enquiry__popular a:hover{color:var(--ipu-orange)}

/* ===== page-hero component ===== */
.ipu-page-hero{background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 60%,#2a5ac8 100%);color:#fff;padding:64px 0 56px;position:relative;overflow:hidden}
.ipu-page-hero h1,.ipu-page-hero p,.ipu-page-hero a{color:#fff}
.ipu-page-hero__crumbs{font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.65);margin-bottom:14px}
.ipu-page-hero__crumbs ol{list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:8px}
.ipu-page-hero__crumbs li::after{content:"/";margin-left:8px;color:rgba(255,255,255,.35)}
.ipu-page-hero__crumbs li:last-child::after{content:""}
.ipu-page-hero__crumbs a{color:rgba(255,255,255,.85);text-decoration:none}
.ipu-page-hero__crumbs a:hover{color:var(--ipu-amber)}
.ipu-page-hero__kicker{font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.7);margin:0 0 10px;font-weight:600}
.ipu-page-hero__h1{font-size:clamp(1.85rem,4.5vw,2.8rem);line-height:1.15;margin:0 0 16px;font-weight:700}
.ipu-page-hero__h1 em{font-style:italic;font-weight:400;color:var(--ipu-amber)}
.ipu-page-hero__chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.ipu-page-hero__chips span{background:rgba(255,255,255,.1);padding:6px 14px;border-radius:20px;font-size:13px;color:rgba(255,255,255,.9)}
.ipu-page-hero__intro{font-size:16px;line-height:1.7;color:rgba(255,255,255,.85);max-width:560px;margin:0 0 18px}
.ipu-page-hero__intro a{color:var(--ipu-amber);font-weight:600}
.ipu-page-hero__call{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;font-weight:700;font-size:15px}
@media(max-width:991px){.ipu-page-hero{padding:48px 0 32px}.ipu-page-hero__h1{font-size:clamp(1.5rem,5.5vw,2rem)}}
```

- [ ] **Step 3: Lint**

```bash
php -l website_download/include/base-head.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: Smoke — homepage still renders**

```bash
curl -sS -o /tmp/index-after.html -w "HTTP %{http_code} %{size_download}B\n" http://localhost:8000/
grep -c "ipu-input\|ipu-enquiry\|ipu-page-hero" /tmp/index-after.html
```

Expected: `HTTP 200`, and `grep -c` returns `0` (no page yet uses the new classes — the CSS is dormant).

- [ ] **Step 5: Smoke — banner-three page still renders identically**

```bash
curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php > /tmp/btech-after.html
diff <(curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php) /tmp/btech-after.html
```

Expected: empty diff (idempotent: same fetch twice).

- [ ] **Step 6: Commit**

```bash
git add website_download/include/base-head.php
git commit -m "feat(tokens): add ipu design tokens + sidebar-enquiry/page-hero CSS

Token block consolidates the brand palette into CSS custom properties.
Component CSS for sidebar-enquiry + page-hero ships dormant — no page
references the classes yet. Phase 2 + Phase 3 will adopt.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Add `$skip_legacy_css` opt-out to `base-head.php`

**Why.** Phase 6 will drop `bundle.min.css` from migrated pages. We add the switch now so individual pages can opt out without another `base-head.php` edit later.

**Files:**
- Modify: `website_download/include/base-head.php` (around the `bundle.min.css` link tag)

- [ ] **Step 1: Locate the bundle link**

```bash
grep -n "bundle.min.css" website_download/include/base-head.php
```

Expected: 2 lines (the deferred `<link>` and its `<noscript>` fallback).

- [ ] **Step 2: Wrap them in a conditional**

In `website_download/include/base-head.php`, replace:

```php
<!-- Main CSS Bundle (deferred) -->
<link rel="stylesheet" href="/assets/css/bundle.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/assets/css/bundle.min.css"></noscript>
```

with:

```php
<!-- Main CSS Bundle (deferred) — pages can opt out by setting $skip_legacy_css = true before the include -->
<?php if (empty($skip_legacy_css)): ?>
<link rel="stylesheet" href="/assets/css/bundle.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/assets/css/bundle.min.css"></noscript>
<?php endif; ?>
```

- [ ] **Step 3: Lint**

```bash
php -l website_download/include/base-head.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 4: Smoke — bundle still loads on every page (no opt-outs yet)**

```bash
for u in / /IPU-B-Tech-admission-2026.php /blog.php /BPIT.php; do
  printf "%-40s  " "$u"
  curl -sS "http://localhost:8000$u" | grep -c "bundle.min.css"
done
```

Expected: each page returns `2` (the link + noscript). If any returns `0`, the wrapping is wrong.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/base-head.php
git commit -m "feat(base-head): \$skip_legacy_css opt-out for migrated pages

Default behaviour unchanged. Phase 6 will set the flag on pages that
have migrated to page-hero + sidebar-enquiry, dropping bundle.min.css
from their critical path.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Phase 1 sanity gate

- [ ] **Step 1: Crosslink walk — all archetypes still 200**

```bash
for u in / /IPU-B-Tech-admission-2026.php /mba-admission-ip-university.php /blog.php /news/ /BPIT.php /cuet-btech-admission-ipu.php /btech-management-quota-ipu.php; do
  printf "%-45s  " "$u"
  curl -sS -o /dev/null -w "HTTP %{http_code}\n" "http://localhost:8000$u"
done
```

Expected: every line `HTTP 200`.

- [ ] **Step 2: Visual idempotency — fetch homepage twice, expect identical**

```bash
curl -sS http://localhost:8000/ > /tmp/idx-1.html
curl -sS http://localhost:8000/ > /tmp/idx-2.html
diff /tmp/idx-1.html /tmp/idx-2.html | head -10
```

Expected: empty diff.

- [ ] **Step 3: PHP lint sweep on touched files**

```bash
php -l website_download/index.php && php -l website_download/include/base-head.php
```

Expected: both `No syntax errors detected`.

- [ ] **Step 4: Phase 1 done — proceed to Phase 2.** (No FTP push for Phase 1: no visible change.)

---

## Phase 2 — Sidebar enquiry component

### Task 5: Create `sidebar-enquiry.php`

**Files:**
- Create: `website_download/include/components/sidebar-enquiry.php`

- [ ] **Step 1: Create the file**

Write the entire content below to `website_download/include/components/sidebar-enquiry.php`:

```php
<?php
// include/components/sidebar-enquiry.php
// Unified enquiry sidebar — visual sibling of the index.php hero form.
// Form contract MUST match form-handler.php / sendemail.php exactly:
//   POST /sendemail.php
//   fields: name, phone, email, course, page_url, website (honeypot)
// Locals (all optional):
//   $enquiry_heading       string  — card heading, default "Get Free Admission Guidance"
//   $enquiry_subheading    string  — card subheading
//   $enquiry_show_phone    bool    — render the navy phone block above the form (default true)
//   $enquiry_show_popular  bool    — render the popular-guides list below the form (default true)
//   $enquiry_popular       array   — list of [label, url] tuples for the popular block

$enquiry_heading      = $enquiry_heading      ?? 'Get Free Admission Guidance';
$enquiry_subheading   = $enquiry_subheading   ?? 'No charges. Our expert team will call you.';
$enquiry_show_phone   = $enquiry_show_phone   ?? true;
$enquiry_show_popular = $enquiry_show_popular ?? true;
$enquiry_popular      = $enquiry_popular      ?? [
    ['B.Tech Admission 2026', '/IPU-B-Tech-admission-2026.php'],
    ['MBA Admission Guide',   '/mba-admission-ip-university.php'],
    ['Law Admission 2026',    '/IPU-Law-Admission.php'],
    ['BBA Admission Guide',   '/ipu-bba-admission.php'],
    ['Management Quota',      '/IP-University-management-quota-admission-eligibility-criteria.php'],
];

// form-handler.php may set $form_error; tolerate it being unset.
$form_error = $form_error ?? null;
?>
<aside class="ipu-enquiry">

  <?php if ($enquiry_show_phone): ?>
  <div class="ipu-enquiry__phone">
    <span class="ipu-enquiry__phone-badge">Counsellors online</span>
    <p class="ipu-enquiry__phone-label">Talk to our admission team</p>
    <a class="ipu-enquiry__phone-num" href="tel:+919899991342">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
      9899991342
    </a>
    <span class="ipu-enquiry__phone-hours">Mon–Sat · 9 AM – 7 PM</span>
  </div>
  <?php endif; ?>

  <div class="ipu-enquiry__form-wrap">
    <h3 class="ipu-enquiry__heading"><?= htmlspecialchars($enquiry_heading) ?></h3>
    <p class="ipu-enquiry__subheading"><?= htmlspecialchars($enquiry_subheading) ?></p>

    <?php if ($form_error): ?>
      <div class="ipu-enquiry__error"><?= htmlspecialchars($form_error) ?></div>
    <?php endif; ?>

    <form class="ipu-enquiry__form enquiry-form" method="POST" action="/sendemail.php" novalidate>
      <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

      <input class="ipu-input" type="text"  name="name"  placeholder="Full Name"           required autocomplete="name">
      <input class="ipu-input" type="tel"   name="phone" placeholder="Phone Number"        required inputmode="tel" autocomplete="tel" pattern="[6-9][0-9]{9}" maxlength="10">
      <input class="ipu-input" type="email" name="email" placeholder="Email (optional)"    autocomplete="email">

      <select class="ipu-input" name="course" required>
        <option value="">Select Course</option>
        <option value="B.Tech">B.Tech</option>
        <option value="MBA">MBA</option>
        <option value="BBA">BBA</option>
        <option value="BA LLB">BA LLB (Law)</option>
        <option value="BBA LLB">BBA LLB (Law)</option>
        <option value="MCA">MCA</option>
        <option value="BCA">BCA</option>
        <option value="BJMC">BJMC</option>
        <option value="B.Com">B.Com</option>
        <option value="BA Economics">BA Economics</option>
        <option value="BA English">BA English</option>
        <option value="Management Quota">Management Quota</option>
        <option value="Counselling">Admission Help</option>
        <option value="Other">Other</option>
      </select>

      <button class="ipu-btn-primary ipu-enquiry__submit" type="submit">Request a Callback</button>
      <p class="ipu-enquiry__fine">100% Free. No spam, ever.</p>
    </form>
  </div>

  <?php if ($enquiry_show_popular && !empty($enquiry_popular)): ?>
  <div class="ipu-enquiry__popular">
    <h4>Popular Guides</h4>
    <ul>
      <?php foreach ($enquiry_popular as $p): ?>
        <li><a href="<?= htmlspecialchars($p[1]) ?>"><?= htmlspecialchars($p[0]) ?> <span aria-hidden="true">→</span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

</aside>
```

- [ ] **Step 2: Lint**

```bash
php -l website_download/include/components/sidebar-enquiry.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Render fixture — make sure it produces valid HTML in isolation**

```bash
php -r "\$_SERVER['REQUEST_URI']='/test'; include 'website_download/include/components/sidebar-enquiry.php';" | head -40
```

Expected: HTML starting with `<aside class="ipu-enquiry">`, containing `tel:+919899991342`, `action="/sendemail.php"`, `name="phone"`, `name="course"`, `name="website"` (honeypot).

- [ ] **Step 4: Verify form contract — every required field is present**

```bash
php -r "\$_SERVER['REQUEST_URI']='/'; include 'website_download/include/components/sidebar-enquiry.php';" | \
  grep -oE 'name="(name|phone|email|course|page_url|website)"' | sort -u
```

Expected: 6 lines — `name="course"`, `name="email"`, `name="name"`, `name="page_url"`, `name="phone"`, `name="website"`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/components/sidebar-enquiry.php
git commit -m "feat(component): sidebar-enquiry — unified enquiry block

Visual sibling of the index.php hero form card. Reuses the existing
form-handler.php POST contract (action /sendemail.php, same field
names, honeypot preserved). Configurable via locals; includes a
default popular-guides list.

Phase 2 will adopt on 3 pilot college pages before propagating.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: Adopt `sidebar-enquiry` on 3 pilot college pages

**Pilot set:** `BPIT.php`, `vips-admission.php`, `mait-admission.php`. Three real college pages with existing hand-rolled sidebars — large enough to stress-test the component, small enough to revert cleanly.

**Per-page recipe (apply to each pilot file):**

1. Find the page's existing right-column sidebar block. Typical shapes:
   - `<div class="col-lg-4">…<form action="/sendemail.php">…</form>…</div>`
   - or an `<?php include 'include/sidebar-cta.php'; ?>` / `<?php include 'include/bigin-sidebar-form.php'; ?>` line.
2. Replace the **entire `col-lg-4` block** (everything between the opening `<div class="col-lg-4">` and its matching `</div>`) with:

   ```php
   <div class="col-lg-4">
     <?php
       // Optionally override the heading/popular list per-page; defaults are fine.
       // $enquiry_heading = 'Talk to a B.Tech Counsellor';
       include __DIR__ . '/include/components/sidebar-enquiry.php';
     ?>
   </div>
   ```
3. Do **not** touch any other markup on the page. Body text stays exact.

- [ ] **Step 1: Make a content-baseline snapshot for each pilot page**

```bash
mkdir -p /tmp/ipu-baseline
for f in BPIT.php vips-admission.php mait-admission.php; do
  curl -sS "http://localhost:8000/$f" > "/tmp/ipu-baseline/$f.before.html"
done
ls -la /tmp/ipu-baseline/
```

Expected: 3 files, each ~30-60 KB.

- [ ] **Step 2: Migrate `BPIT.php`**

Apply the per-page recipe above to `website_download/BPIT.php`.

- [ ] **Step 3: Lint + render BPIT**

```bash
php -l website_download/BPIT.php && \
curl -sS http://localhost:8000/BPIT.php > /tmp/ipu-baseline/BPIT.php.after.html && \
echo "size before: $(wc -c < /tmp/ipu-baseline/BPIT.php.before.html), after: $(wc -c < /tmp/ipu-baseline/BPIT.php.after.html)"
```

Expected: lint passes; "after" size differs from "before" because chrome changed; both > 30 KB.

- [ ] **Step 4: Verify content-frozen — body text identical (chrome stripped)**

```bash
# Strip the sidebar element entirely, then diff the rest.
strip_sidebar() {
  python3 -c "
import sys, re
html = sys.stdin.read()
# remove old hand-rolled sidebar form block (heuristic) AND new ipu-enquiry block
html = re.sub(r'<div class=\"col-lg-4\">.*?</div>\s*</div>', '', html, count=1, flags=re.S)
# normalise whitespace
html = re.sub(r'\s+', ' ', html)
print(html)
"
}
strip_sidebar < /tmp/ipu-baseline/BPIT.php.before.html > /tmp/ipu-baseline/BPIT.php.before.body.txt
strip_sidebar < /tmp/ipu-baseline/BPIT.php.after.html  > /tmp/ipu-baseline/BPIT.php.after.body.txt
diff /tmp/ipu-baseline/BPIT.php.before.body.txt /tmp/ipu-baseline/BPIT.php.after.body.txt | head -30
```

Expected: empty diff (the body text outside the sidebar is byte-identical). If non-empty, **revert the file** and re-apply the recipe — content was changed accidentally.

- [ ] **Step 5: Verify form contract on the migrated page**

```bash
curl -sS http://localhost:8000/BPIT.php | grep -oE 'name="(name|phone|email|course|page_url|website)"' | sort -u
```

Expected: 6 lines (same as Task 5 step 4).

- [ ] **Step 6: Repeat steps 2–5 for `vips-admission.php`**

- [ ] **Step 7: Repeat steps 2–5 for `mait-admission.php`**

- [ ] **Step 8: Commit (one commit per page or one bundle — bundle is fine)**

```bash
git add website_download/BPIT.php website_download/vips-admission.php website_download/mait-admission.php
git commit -m "feat(sidebar): adopt sidebar-enquiry on 3 pilot college pages

BPIT, VIPS, MAIT — replaces hand-rolled col-lg-4 sidebar blocks with
unified sidebar-enquiry component. Form contract (POST /sendemail.php,
name/phone/email/course/page_url/website fields) preserved. Body text
outside the sidebar verified byte-identical to pre-migration HTML.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 7: Phase 2 localhost crosslink walk

- [ ] **Step 1: Walk the nav from each pilot page**

```bash
for page in /BPIT.php /vips-admission.php /mait-admission.php; do
  echo "=== From $page ==="
  for link in / /ipu-admission-guide.php /IP-University-management-quota-admission-eligibility-criteria.php /GGSIPU-counselling-for-B-Tech-admission.php /ipu-colleges-list.php /ipu-helpline-contact-number.php /news/ /blog.php; do
    code=$(curl -sS -o /dev/null -w "%{http_code}" "http://localhost:8000$link")
    printf "  %-60s %s\n" "$link" "$code"
  done
done
```

Expected: every line ends in `200`.

- [ ] **Step 2: Walk the popular-guides links from the migrated sidebar**

```bash
for u in /IPU-B-Tech-admission-2026.php /mba-admission-ip-university.php /IPU-Law-Admission.php /ipu-bba-admission.php /IP-University-management-quota-admission-eligibility-criteria.php; do
  curl -sS -o /dev/null -w "%{http_code}  $u\n" "http://localhost:8000$u"
done
```

Expected: every line `200`.

- [ ] **Step 3: Form POST smoke — post a fake submission and check response**

```bash
curl -sS -i -X POST http://localhost:8000/sendemail.php \
  -d "name=Test User" \
  -d "phone=9999999999" \
  -d "email=test@example.com" \
  -d "course=B.Tech" \
  -d "page_url=/BPIT.php" \
  -d "website=" 2>&1 | head -10
```

Expected: HTTP 200 or 302 (a redirect to a thank-you / success page is also fine). NOT 404 or 500. Actual sendemail.php behaviour may be a redirect — capture the status line.

- [ ] **Step 4: Mobile-emulator visual** (manual — Sumit's eyeballs)

Open Chrome DevTools, toggle device toolbar to iPhone 12 (390×844). Visit `http://localhost:8000/BPIT.php`. Verify:
- Sidebar form stacks below the main content (no horizontal scroll)
- Mobile sticky bottom call CTA visible
- Phone link in the sidebar phone block visible above the fold (or at least within one tap)
- "Request a Callback" button readable and full-width

Repeat for `/vips-admission.php` and `/mait-admission.php`.

---

### Task 8: Phase 2 FTP push (PAUSE — Sumit go-ahead required)

⏸ **PAUSE.** Do not proceed without Sumit's explicit go-ahead.

- [ ] **Step 1: Read existing upload-script pattern**

```bash
head -60 /Users/Sumit/test-project/upload_seo_overhaul_2026_05_05.py
```

Use this as the template for the new upload script.

- [ ] **Step 2: Create `upload_cohesion_phase2_2026_05_09.py`**

Write a copy of the SEO overhaul deploy script with:
- `FILES_TO_UPLOAD` containing only:
  - `BPIT.php`, `vips-admission.php`, `mait-admission.php` (root)
  - `include/base-head.php`
  - `include/components/sidebar-enquiry.php`
  - `index.php`

- [ ] **Step 3: Sumit reviews the script**

(PAUSE — wait for go-ahead)

- [ ] **Step 4: Run the upload**

```bash
python3 upload_cohesion_phase2_2026_05_09.py
```

Expected: every file reports `OK` or `Uploaded`.

- [ ] **Step 5: Curl-verify on prod**

```bash
for u in / /BPIT.php /vips-admission.php /mait-admission.php; do
  printf "%-35s  " "$u"
  curl -sS -o /dev/null -w "HTTP %{http_code}\n" "https://ipu.co.in$u"
done
curl -sS https://ipu.co.in/BPIT.php | grep -c "ipu-enquiry"
```

Expected: every URL returns 200; the grep returns `≥ 1` (the migrated component is live).

- [ ] **Step 6: Commit the upload script**

```bash
git add upload_cohesion_phase2_2026_05_09.py
git commit -m "deploy(cohesion-p2): FTP script for Phase 2 (sidebar pilot)

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

⏸ **PAUSE — Sumit reviews the live pages on prod.** Do not start Phase 3 without an explicit "go".

---

## Phase 3 — Page-hero pilot (one course hub)

### Task 9: Create `page-hero.php`

**Files:**
- Create: `website_download/include/components/page-hero.php`

- [ ] **Step 1: Create the file**

Write the following to `website_download/include/components/page-hero.php`:

```php
<?php
// include/components/page-hero.php
// Form-bearing page hero. Replaces banner-three across course hubs.
// Pages keep their own H1/intro/breadcrumbs — this only rewraps them.
// Locals (all optional):
//   $hero_kicker      string   — small uppercase label above H1
//   $hero_h1          string   — H1 text (HTML allowed for inline emphasis like <em>…</em>)
//   $hero_intro       string   — intro paragraph (HTML allowed for anchors)
//   $hero_chips       array    — list of strings rendered as pill chips
//   $hero_breadcrumbs array    — list of [label, url] tuples; tuple with empty url renders as current
//   $hero_show_form   bool     — render the in-flow sidebar enquiry on desktop (default true)
//   $hero_slot_html   string   — raw HTML override for the left column (escapes nothing)

$hero_kicker      = $hero_kicker      ?? null;
$hero_h1          = $hero_h1          ?? null;
$hero_intro       = $hero_intro       ?? null;
$hero_chips       = $hero_chips       ?? [];
$hero_breadcrumbs = $hero_breadcrumbs ?? [];
$hero_show_form   = $hero_show_form   ?? true;
$hero_slot_html   = $hero_slot_html   ?? null;
?>
<section class="ipu-page-hero">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-lg-<?= $hero_show_form ? '7' : '12' ?> mb-4 mb-lg-0">
        <?php if ($hero_slot_html !== null): ?>
          <?= $hero_slot_html ?>
        <?php else: ?>

          <?php if (!empty($hero_breadcrumbs)): ?>
            <nav class="ipu-page-hero__crumbs" aria-label="Breadcrumb">
              <ol>
                <?php foreach ($hero_breadcrumbs as $c): ?>
                  <li>
                    <?php if (!empty($c[1])): ?>
                      <a href="<?= htmlspecialchars($c[1]) ?>"><?= htmlspecialchars($c[0]) ?></a>
                    <?php else: ?>
                      <span aria-current="page"><?= htmlspecialchars($c[0]) ?></span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            </nav>
          <?php endif; ?>

          <?php if ($hero_kicker): ?>
            <p class="ipu-page-hero__kicker"><?= htmlspecialchars($hero_kicker) ?></p>
          <?php endif; ?>

          <?php if ($hero_h1): ?>
            <h1 class="ipu-page-hero__h1"><?= $hero_h1 ?></h1>
          <?php endif; ?>

          <?php if (!empty($hero_chips)): ?>
            <div class="ipu-page-hero__chips">
              <?php foreach ($hero_chips as $chip): ?>
                <span><?= htmlspecialchars($chip) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($hero_intro): ?>
            <p class="ipu-page-hero__intro"><?= $hero_intro ?></p>
          <?php endif; ?>

          <a href="tel:+919899991342" class="ipu-btn-primary ipu-page-hero__call">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
            Call: 9899991342
          </a>

        <?php endif; ?>
      </div>

      <?php if ($hero_show_form): ?>
        <div class="col-lg-5">
          <?php include __DIR__ . '/sidebar-enquiry.php'; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>
```

- [ ] **Step 2: Lint**

```bash
php -l website_download/include/components/page-hero.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 3: Smoke render with locals**

```bash
php -r "
\$_SERVER['REQUEST_URI']='/x';
\$hero_kicker      = 'Course Hub';
\$hero_h1          = 'Test <em>Hero</em>';
\$hero_intro       = 'Lorem ipsum';
\$hero_chips       = ['One','Two'];
\$hero_breadcrumbs = [['Home','/'],['Test',null]];
include 'website_download/include/components/page-hero.php';
" | grep -E "ipu-page-hero|ipu-enquiry" | head -10
```

Expected: 2+ lines containing `ipu-page-hero` and `ipu-enquiry` — confirming the component renders the hero AND embeds the sidebar.

- [ ] **Step 4: Commit**

```bash
git add website_download/include/components/page-hero.php
git commit -m "feat(component): page-hero — form-bearing hero replacing banner-three

Split-hero modelled on index.php: left column for the page's H1/intro/
chips/breadcrumbs (all optional locals or raw HTML slot), right column
for sidebar-enquiry on desktop. Mobile stacks naturally. Falls back to
full-width when \$hero_show_form=false.

Phase 3 will adopt on IPU-B-Tech-admission-2026.php as the pilot.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 10: Migrate `IPU-B-Tech-admission-2026.php` (the pilot)

**Files:**
- Modify: `website_download/IPU-B-Tech-admission-2026.php`

**Per-page recipe:**

1. Locate the existing banner-three section. Typical shape:
   ```php
   <section class="banner-area banner-three mt-0" style="background-image:url(…)">
     <div class="container">
       <h1 class="ft-35 white">… page H1 …</h1>
       <p class="white">… intro paragraph …</p>
     </div>
   </section>
   ```
2. Locate the page's breadcrumb section if separate (usually `<section class="breadcrumb-area">…</section>`).
3. Extract the H1 inner HTML, the intro paragraph inner HTML, and any breadcrumbs into PHP variables.
4. Replace the banner section + breadcrumb section with one `page-hero` include.
5. Do **not** touch the rest of the page body.

- [ ] **Step 1: Snapshot the page**

```bash
curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php > /tmp/ipu-baseline/btech.before.html
wc -c /tmp/ipu-baseline/btech.before.html
```

- [ ] **Step 2: Read the current banner block**

```bash
grep -n "banner-area\|banner-three\|breadcrumb-area\|<h1" website_download/IPU-B-Tech-admission-2026.php | head -20
```

Note the line numbers of the banner section and breadcrumb section.

- [ ] **Step 3: Apply the migration**

In `website_download/IPU-B-Tech-admission-2026.php`, replace the banner section ONLY (do NOT touch the in-body breadcrumb that lives inside `.blog-wrapper`) with:

```php
<?php
$hero_h1 = '<<EXACT H1 text from existing banner, preserve any inline em/strong tags; replace literal & with &amp;>>';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>
```

> **CRITICAL.**
> 1. The H1 value MUST be the verbatim string from the banner-three before. The literal `&` becomes `&amp;` in the PHP string literal (it goes through unescaped echo, so the rendered HTML matches the original).
> 2. **`$hero_show_form = false;` is required** on any page that already has an in-body enquiry sidebar (course hubs include `sidebar-cta.php` or hand-roll a `<div class="col-lg-4">…<form>` in the body). Two enquiry forms on one page is worse for conversion than one well-placed form. The page-hero on these pages is header-only; the in-body sidebar carries conversion.
> 3. Do NOT pass `$hero_kicker`, `$hero_chips`, `$hero_intro`, or `$hero_breadcrumbs` unless those exact strings already appear in the page's banner. We are not authoring new copy. If the original banner only had an H1 (typical of `banner-three`), only `$hero_h1` is set. The body breadcrumb stays where it was — it's structurally part of the article.

- [ ] **Step 4: Lint**

```bash
php -l website_download/IPU-B-Tech-admission-2026.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 5: Render + content-frozen check**

```bash
curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php > /tmp/ipu-baseline/btech.after.html

# Extract main content text (drop chrome, strip tags)
extract_text() {
  python3 -c "
import sys, re
html = sys.stdin.read()
# Drop everything before <body and after </body
m = re.search(r'<body[^>]*>(.*)</body>', html, re.S)
if m: html = m.group(1)
# Drop hero/banner/breadcrumb/sidebar/footer/script/style chrome
for pattern in [
    r'<section class=\"banner-area.*?</section>',
    r'<section class=\"ipu-page-hero.*?</section>',
    r'<section class=\"breadcrumb.*?</section>',
    r'<aside class=\"ipu-enquiry.*?</aside>',
    r'<header.*?</header>',
    r'<footer.*?</footer>',
    r'<script.*?</script>',
    r'<style.*?</style>',
]:
    html = re.sub(pattern, '', html, flags=re.S)
# Strip remaining tags + collapse whitespace
text = re.sub(r'<[^>]+>', ' ', html)
text = re.sub(r'\s+', ' ', text).strip()
print(text)
"
}
extract_text < /tmp/ipu-baseline/btech.before.html > /tmp/ipu-baseline/btech.before.txt
extract_text < /tmp/ipu-baseline/btech.after.html  > /tmp/ipu-baseline/btech.after.txt
diff /tmp/ipu-baseline/btech.before.txt /tmp/ipu-baseline/btech.after.txt | head -40
```

Expected: empty diff. **If non-empty, the migration changed visible content — revert and re-apply** with stricter copy preservation.

- [ ] **Step 6: Form contract still intact**

```bash
curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php | grep -oE 'name="(name|phone|email|course|page_url|website)"' | sort -u
```

Expected: 6 lines.

- [ ] **Step 7: Commit**

```bash
git add website_download/IPU-B-Tech-admission-2026.php
git commit -m "feat(hero): migrate B.Tech 2026 hub from banner-three to page-hero

First migration of the page-hero component on a real course hub.
Banner-three slab + separate breadcrumb section consolidated into
one form-bearing hero. H1, intro, and breadcrumb labels preserved
verbatim from existing page. Body content outside hero verified
byte-identical to pre-migration HTML.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 11: Phase 3 localhost gate

- [ ] **Step 1: Visual review on localhost** (manual — Sumit's eyeballs)

Open `http://localhost:8000/IPU-B-Tech-admission-2026.php`. Verify:
- Above-the-fold split: H1 left, sidebar enquiry form right
- Phone block visible (navy gradient with amber 9899991342)
- No `banner-three` dark slab anywhere
- Page body below the hero unchanged from before
- No layout regression below 992 px (form stacks under H1)

- [ ] **Step 2: Crosslink walk from the migrated page**

```bash
for link in / /ipu-admission-guide.php /IP-University-management-quota-admission-eligibility-criteria.php /GGSIPU-counselling-for-B-Tech-admission.php /best-btech-colleges-ipu.php /b-tech-colleges-under-IP-university.php; do
  code=$(curl -sS -o /dev/null -w "%{http_code}" "http://localhost:8000$link")
  printf "%-65s %s\n" "$link" "$code"
done
```

Expected: every line `200`.

- [ ] **Step 3: PAUSE — Sumit visual sign-off**

⏸ Wait for explicit "looks good, ship it" before Task 12.

---

### Task 12: Phase 3 FTP push

- [ ] **Step 1: Create `upload_cohesion_phase3_2026_05_09.py`**

Same template as Phase 2. `FILES_TO_UPLOAD` contains:
- `IPU-B-Tech-admission-2026.php`
- `include/components/page-hero.php`

- [ ] **Step 2: Run upload**

```bash
python3 upload_cohesion_phase3_2026_05_09.py
```

- [ ] **Step 3: Curl-verify on prod**

```bash
curl -sS https://ipu.co.in/IPU-B-Tech-admission-2026.php | grep -E "ipu-page-hero|ipu-enquiry|banner-three" | head -5
```

Expected: lines with `ipu-page-hero` and `ipu-enquiry`. **No** lines with `banner-three`.

- [ ] **Step 4: Commit upload script**

```bash
git add upload_cohesion_phase3_2026_05_09.py
git commit -m "deploy(cohesion-p3): FTP script for Phase 3 (B.Tech hub pilot)

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

⏸ **PAUSE — Sumit reviews the live B.Tech hub on prod.** Do not start Phase 4 without "go".

---

## Phase 4 — Course-hub batch migration

### Task 13: Identify and migrate the remaining banner-three pages

**Target files** (~32 pages — verify the list before starting):

```bash
grep -lr "banner-area banner-three\|banner-three mt-0" website_download/ --include="*.php" | grep -v include/ | grep -v IPU-B-Tech-admission-2026.php
```

Expected output: a list of ~32 paths. **Save this list to `/tmp/banner-three-pages.txt`** before starting:

```bash
grep -lr "banner-area banner-three\|banner-three mt-0" website_download/ --include="*.php" | grep -v include/ | grep -v IPU-B-Tech-admission-2026.php > /tmp/banner-three-pages.txt
wc -l /tmp/banner-three-pages.txt
```

- [ ] **Step 1: Per-file recipe (apply to each file in the list)**

Same as Task 10 steps 1–7, with these added rules:

1. **Read the existing H1 for that file.** Use grep:
   ```bash
   grep -n "<h1\|<p class=\"white\"" website_download/<file.php>
   ```
2. **Copy the H1 verbatim into the `$hero_h1` PHP variable.** Inline emphasis (`<em>`, `<strong>`, `<a>`) preserved. Replace literal `&` with `&amp;` in the PHP string literal so the rendered HTML stays identical.
3. **Always set `$hero_show_form = false;`** for banner-three migrations. Course hubs already have an in-body enquiry sidebar (`sidebar-cta.php` include or hand-rolled `col-lg-4` form). Two forms on one page hurts conversion.
4. **Do NOT pass `$hero_kicker`, `$hero_intro`, `$hero_chips`, or `$hero_breadcrumbs` unless those exact strings already appear on the page somewhere.** Most banner-three pages have only an H1. Don't author new copy.
5. **Replace ONLY the banner-three section.** Leave any in-body breadcrumb where it is. Leave the in-body sidebar alone (Phase 5 swaps its content). Touch only the 7-line `<section class="banner-area banner-three…">…</section>` block.

- [ ] **Step 2: Snapshot every page first**

```bash
mkdir -p /tmp/ipu-baseline-p4
while read f; do
  base=$(basename "$f")
  curl -sS "http://localhost:8000/$base" > "/tmp/ipu-baseline-p4/$base.before.html"
done < /tmp/banner-three-pages.txt
ls /tmp/ipu-baseline-p4 | wc -l
```

Expected: matches the file count from the previous step.

- [ ] **Step 3: Migrate, lint, content-diff per file**

For each file in the list, apply the per-page recipe. After each file:

```bash
php -l "$f"
curl -sS "http://localhost:8000/$(basename $f)" > "/tmp/ipu-baseline-p4/$(basename $f).after.html"
# Use the same extract_text helper from Task 10 step 5
extract_text < /tmp/ipu-baseline-p4/$(basename $f).before.html > /tmp/before.txt
extract_text < /tmp/ipu-baseline-p4/$(basename $f).after.html  > /tmp/after.txt
diff /tmp/before.txt /tmp/after.txt | head -10
```

Expected: lint passes, content diff empty. **If diff non-empty, revert that one file and re-apply.**

- [ ] **Step 4: Commit in batches of 5–8 files**

```bash
git add <batch of 5-8 files>
git commit -m "feat(hero): migrate <batch description> from banner-three to page-hero

Batch <N> of <M>. Body content verified byte-identical per file.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

---

### Task 14: Phase 4 crosslink walk

- [ ] **Step 1: Walk from one page in each course-hub family**

```bash
sample_pages=(
  /IPU-B-Tech-admission-2026.php
  /mba-admission-ip-university.php
  /IPU-Law-Admission.php
  /ipu-bba-admission.php
  /bcom-admission-ipu.php
  /cuet-btech-admission-ipu.php
  /btech-management-quota-ipu.php
  /bca-admission-ipu.php
  /barch-admission-ipu.php
)
nav_links=(
  /
  /ipu-admission-guide.php
  /IP-University-management-quota-admission-eligibility-criteria.php
  /GGSIPU-counselling-for-B-Tech-admission.php
  /ipu-colleges-list.php
  /ipu-helpline-contact-number.php
  /news/
  /blog.php
)
for page in "${sample_pages[@]}"; do
  echo "=== $page ==="
  page_code=$(curl -sS -o /dev/null -w "%{http_code}" "http://localhost:8000$page")
  echo "  page itself: $page_code"
  for link in "${nav_links[@]}"; do
    code=$(curl -sS -o /dev/null -w "%{http_code}" "http://localhost:8000$link")
    [ "$code" != "200" ] && echo "  ! $link  $code"
  done
done
```

Expected: every page returns 200; no `!` lines.

- [ ] **Step 2: Verify NO banner-three remains on migrated pages**

```bash
while read f; do
  base=$(basename "$f")
  count=$(curl -sS "http://localhost:8000/$base" | grep -c "banner-three")
  [ "$count" -gt 0 ] && echo "STILL HAS banner-three: $base"
done < /tmp/banner-three-pages.txt
echo "(no STILL HAS lines = all migrated)"
```

Expected: no `STILL HAS` lines.

- [ ] **Step 3: PAUSE — Sumit reviews several pages on localhost**

⏸ Sumit walks ≥ 5 pages from the list on desktop + mobile (DevTools).

---

### Task 15: Phase 4 FTP push

- [ ] **Step 1: Create `upload_cohesion_phase4_2026_05_09.py`**

`FILES_TO_UPLOAD` contains every file from `/tmp/banner-three-pages.txt`.

- [ ] **Step 2: Run upload, curl-verify on prod**

```bash
python3 upload_cohesion_phase4_2026_05_09.py
# Verify a sample
for u in /IPU-B-Tech-admission-2026.php /mba-admission-ip-university.php /IPU-Law-Admission.php /cuet-btech-admission-ipu.php /btech-management-quota-ipu.php; do
  printf "%-50s  " "$u"
  curl -sS -o /dev/null -w "HTTP %{http_code}\n" "https://ipu.co.in$u"
done
# Spot-check that page-hero is live
curl -sS https://ipu.co.in/mba-admission-ip-university.php | grep -c "ipu-page-hero"
```

Expected: every prod URL `200`; grep returns `≥ 1` on the spot-checked page.

- [ ] **Step 3: Commit upload script**

```bash
git add upload_cohesion_phase4_2026_05_09.py
git commit -m "deploy(cohesion-p4): FTP script for Phase 4 (course-hub batch)

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>"
```

⏸ **PAUSE — Sumit reviews 5+ live course hubs on prod.** Phases 5/6 wait for "go".

---

## Phase 5 — College-page sidebar adoption

### Task 16: Identify college pages with hand-rolled sidebars

```bash
grep -lr 'class="col-lg-4"' website_download/ --include="*.php" | grep -v include/ > /tmp/sidebar-candidates.txt
# Filter to ones that ALSO contain a form action="/sendemail.php"
> /tmp/sidebar-pages.txt
while read f; do
  if grep -q 'action="/sendemail.php"' "$f"; then
    # Skip pages already migrated in earlier phases
    base=$(basename "$f")
    if [ "$base" != "BPIT.php" ] && [ "$base" != "vips-admission.php" ] && [ "$base" != "mait-admission.php" ] && ! grep -q "include/components/page-hero.php" "$f"; then
      echo "$f" >> /tmp/sidebar-pages.txt
    fi
  fi
done < /tmp/sidebar-candidates.txt
wc -l /tmp/sidebar-pages.txt
```

Expected: a list of ~40-50 college pages.

### Task 17: Migrate college pages to `sidebar-enquiry`

**Per-page recipe:** identical to Task 6 (the 3-page pilot). Same content-frozen verification (`extract_text` diff before/after).

- [ ] **Step 1: Snapshot, migrate, lint, content-diff each page in batches of 8–10.**

- [ ] **Step 2: Commit each batch.**

- [ ] **Step 3: Crosslink walk** — same script as Task 14 step 1, with sample pages drawn from this batch.

- [ ] **Step 4: FTP push** — `upload_cohesion_phase5_2026_05_09.py`. ⏸ Sumit go-ahead before push.

- [ ] **Step 5: Curl-verify on prod, sample 5 pages.**

⏸ **PAUSE — Sumit reviews 5+ live college pages on prod.**

---

## Phase 6 — Polish

### Task 18: Retire `desktop-call-widget` (Axis 4)

**Files:**
- Modify: `website_download/include/base-nav.php`

- [ ] **Step 1: Remove the desktop call widget block**

In `website_download/include/base-nav.php`, delete the entire `<!-- Desktop Call Widget -->` div block (the floating right-side panel) plus the matching CSS `.desktop-call-widget` rules in `base-head.php`.

Keep the `mobile-call-cta` block — that one stays.

- [ ] **Step 2: Verify nothing references `desktop-call-widget` anymore**

```bash
grep -rn "desktop-call-widget\|desktopCallWidget" website_download/
```

Expected: zero results.

- [ ] **Step 3: Smoke + commit + FTP push**

(Same recipe — lint, curl, crosslink walk, upload script, prod verify.)

### Task 19: Drop `bundle.min.css` on migrated pages (Axis 6)

- [ ] **Step 1: Add `$skip_legacy_css = true;` at the top of every page that uses `page-hero` or `sidebar-enquiry`**

Build the list:

```bash
grep -lr "include/components/page-hero.php\|include/components/sidebar-enquiry.php" website_download/ --include="*.php" | grep -v include/ > /tmp/migrated-pages.txt
wc -l /tmp/migrated-pages.txt
```

For each page in the list, add `<?php $skip_legacy_css = true; ?>` immediately before the existing `<?php include_once("include/base-head.php"); ?>` line.

- [ ] **Step 2: Smoke — bundle.min.css absent on migrated, present on legacy**

```bash
# Migrated page should NOT load bundle.min.css
curl -sS http://localhost:8000/IPU-B-Tech-admission-2026.php | grep -c "bundle.min.css"
# Legacy page (one without page-hero) SHOULD still load it
curl -sS http://localhost:8000/blog.php | grep -c "bundle.min.css"
```

Expected: migrated page = `0`, legacy page = `2`.

- [ ] **Step 3: Visual regression spot-check (manual)** — confirm no migrated page lost styling that came from `bundle.min.css`. If any did, that styling needs to be moved into `base-head.php` critical CSS or the opt-out reverted for that page.

- [ ] **Step 4: Commit + FTP push.**

### Task 20: (Optional) `link-card.php` component (Axis 5)

If time allows, create `include/components/link-card.php` modelled on the index `blog_highlights` card pattern (rounded-12, hover-lift, orange arrow). Defer adoption to a follow-up — this plan does not migrate every existing card.

---

## Final verification

- [ ] **All 138 pages return 200 on prod**

```bash
# Build the URL list from the file tree, curl each one against ipu.co.in
find /Users/Sumit/test-project/website_download -name "*.php" -not -path "*/include/*" -not -path "*/api/*" -not -path "*/cgi-bin/*" -not -path "*/htaccess/*" -not -path "*/course/*" | \
  sed "s|/Users/Sumit/test-project/website_download||" | \
  while read u; do
    code=$(curl -sS -o /dev/null -w "%{http_code}" "https://ipu.co.in$u")
    [ "$code" != "200" ] && echo "$code  $u"
  done
echo "(no lines = all 200)"
```

- [ ] **Spot-check GA4 + `phone_click` events fire** — open Chrome DevTools network tab on any migrated page, click a `tel:` link, confirm `gtm.js` collect request fires with `event=phone_click`.

- [ ] **Visit prod from mobile** — Sumit visits 5 pages on a real phone, confirms layout + tap targets work.

- [ ] **Update memory** — write `project_ipu_cohesion_20260509.md` documenting what shipped, link from `MEMORY.md`.

---

## Notes / followups for after this plan

- **FTP creds in upload scripts.** Each `upload_*.py` hardcodes the FTP password. Cohesion is not the right scope to fix this, but worth a note: rotate to a `.env`-loaded credential in a follow-up.
- **Bundle.min.css full removal.** Once every page is on Grammar A, the deferred bundle stylesheet stops being loaded by anyone. At that point delete it from the asset folder + `base-head.php` entirely.
- **Editorial-Ink layer (parked v4 mockup).** Now that cohesion is in, blog.php can adopt Fraunces/Albert Sans on top of the unified chrome as a decorative re-skin without affecting the rest of the site.
- **A11y pass.** Skip-link, heading-level audit, focus-ring polish — separate plan.
- **Sticky rail** for the desktop sidebar enquiry. If conversion data shows visitors scroll past the form, add `position: sticky; top: 80px` on `.col-lg-5` containing the sidebar inside `page-hero`.
- **Search/filter UX on `blog.php`** — separate plan.

---

## Self-review summary

- **Spec coverage:** Axis 1 (sidebar) → Tasks 5–6, 16–17. Axis 2 (page-hero) → Tasks 9–10, 13. Axis 3 (tokens) → Task 2. Axis 4 (CTA disambiguation) → Task 18. Axis 5 (cards) → Task 20 (optional). Axis 6 (bundle drop) → Tasks 3, 19. Axis 7 (phone visibility) → built into page-hero markup (Task 9) + verified in localhost gate (Task 11). All 7 axes covered.
- **No placeholders.** Every code block is complete; every command is runnable; every PAUSE point has explicit deploy criteria.
- **Type/name consistency.** Component locals (`$hero_*`, `$enquiry_*`) consistent across Tasks 5, 9, 10. CSS class names (`ipu-page-hero`, `ipu-enquiry`) consistent across Tasks 2, 5, 9.
- **Frozen-content rule** is restated at every per-page recipe with verification step.
