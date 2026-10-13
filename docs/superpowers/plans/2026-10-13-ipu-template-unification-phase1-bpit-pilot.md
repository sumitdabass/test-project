# ipu.co.in Template Unification: Foundation + BPIT Pilot Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the shared design foundation (CSS, hero, CTA strip) and prove it end to end on one college page (`BPIT.php`), plus the 8-page sitemap addition, without changing any title, meta, canonical, H1 or page copy.

**Architecture:** Phase 1 adds shared CSS and optional hero slots to `include/base-head.php`, `include/components/page-hero.php` and `include/components/cta-strip.php`, all defaulting to current behaviour except the intended orange call-button colour. `BPIT.php` is then rebuilt on the new `.ipu-body` CSS grid (one DOM copy of the sidebar) as the pilot for every later college page. Later page types (news, course/cutoff, older pages) get their own plans after the pilot is signed off.

**Tech Stack:** PHP 8.5 includes (no framework), Bootstrap 5 + inline critical CSS, pytest (existing `tests/` style with `php -S` fixture), `scripts/seo_verify.py`, `php scripts/tests/run.php`.

**Spec:** `docs/superpowers/specs/2026-10-13-ipu-template-unification-design.md` (handoff: `~/Downloads/design_handoff_ipu_template_unification/README.md`).

## Global Constraints

- `website_download/index.php` is never modified.
- Do not change page copy, CTA labels, phone numbers, URLs, `<title>`, meta description, canonical, robots or JSON-LD content, except: BPIT `$breadcrumbs` labels/URLs, BPIT FAQPage JSON-LD aligned to the visible FAQ, BPIT og/twitter tags, `tel:9899991342` to `tel:+919899991342`.
- Exactly one `<h1>` and exactly one `id="enquiry-form"` per page. H2/H3 text and order unchanged.
- Form contract unchanged: `POST /sendemail.php`, fields `name, phone, email, course, page_url, website, form_loaded_at`.
- `components/trust-bar.php` is NOT changed (owner rule: the trust strip stays the `index.php` version verbatim, so the handoff's trust-bar size tweak is dropped).
- Hold list: USAR, homepage and ADGIPS pages are not touched (titles in test until 5 Jan 2027).
- Tokens only: ink `#0d1b6e`, ink-2 `#1a3a9c`, ink-3 `#2a5ac8`, call action `#ff7a1a` with label `#0d1b6e`, form submit `#e65c00`, amber `#f59e0b`, tint `#f8faff`, highlight `#e8f0ff`, rules `#e2e8f0` / `#d0d9f0` / `#eef2f7`. Radii 8 / 12 / 16 / 999px.
- Nothing is deployed or pushed by this plan. Pushing to `main` publishes `sitemap.xml` immediately via the news workflow, so push only on the owner's go.
- Commit messages end with `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`. Never chain `git commit` after `;`; use `&&` so a red test blocks it.
- macOS: `sed -i ''`. In zsh use arrays for file lists.

## Review Focus

- Pages that include `page-hero.php` with `$hero_show_form = true` already carry the form inside the hero (`id="enquiry-form"` comes from `sidebar-enquiry.php`). The new Enquire button must therefore default OFF so no existing page gets a dead `#enquiry-form` anchor or a second form.
- `base-nav.php` overrides the `.mobile-call-cta` look with `!important`; deleting the `base-head.php` rules must keep the layout rules (position, z-index, body bottom padding) or the sticky bar loses its positioning on all 97 pages.
- BPIT's visible FAQ (3 questions) differs from its FAQPage JSON-LD (different third question and answer wording); the visible copy is the source of truth.
- The sitemap in the repo is behind live (news CI updates live); editing a stale copy and pushing would delete live news URLs from the sitemap.
- `privacy-policy.php` is `noindex`; it must stay out of the sitemap.

---

## File Structure

| File | Responsibility | Change |
|---|---|---|
| `tests/test_template_foundation.py` | Foundation CSS, hero and CTA strip tests (component render via `php -r`) | Create |
| `tests/test_bpit_pilot.py` | BPIT structure, SEO-invariant and FAQ/schema tests (via `php -S`) | Create |
| `tests/test_sitemap_additions.py` | Sitemap contains the 8 pages, not privacy-policy, valid XML, no duplicates | Create |
| `tests/fixtures/bpit_baseline.json` | Pre-change BPIT title, meta, canonical, H2/H3 list, internal links | Create |
| `website_download/include/base-head.php` | box-sizing, grid CSS, hero/CTA/FAQ/table classes, remove legacy gradient, static dot | Modify |
| `website_download/include/components/page-hero.php` | Enquire button, meta slot, orange call button | Modify |
| `website_download/include/components/cta-strip.php` | Orange pill call button, card variant | Modify |
| `website_download/BPIT.php` | Pilot page rebuilt on the body grid | Modify |
| `website_download/sitemap.xml` | 8 new `<url>` entries | Modify |

---

### Task 1: Sync the branch and create the test harness

**Files:**
- Create: `tests/test_template_foundation.py`

**Interfaces:**
- Produces: `render(component, **vars) -> str` (renders an include component with PHP locals set) and `ROOT`/`WEB` constants, copied by later test files.

- [ ] **Step 1: Fast-forward the local branch to main**

Run:
```bash
cd /Users/Sumit/test-project && git fetch origin && git status --short && git merge --ff-only origin/main && git log --oneline -1
```
Expected: clean status, fast-forward succeeds (branch is 8 news commits behind), HEAD equals `origin/main`. If the merge is not a fast-forward, stop and report.

- [ ] **Step 2: Record the green baseline**

Run:
```bash
cd /Users/Sumit/test-project && php scripts/tests/run.php | tail -3 && python3 -m pytest tests -q 2>&1 | tail -3
```
Expected: PHP suite passes, pytest passes. Write the counts in the commit message of Task 2 for later comparison. If anything is red, stop and report; do not proceed on a red baseline.

- [ ] **Step 3: Create the harness with a first failing test**

Create `tests/test_template_foundation.py`:
```python
import json, re, subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
HEAD = (WEB / "include" / "base-head.php").read_text(encoding="utf-8")


def render(component: str, **local_vars) -> str:
    """Render include/components/<component>.php with PHP locals set."""
    assigns = "".join(f"${k}=json_decode({json.dumps(json.dumps(v))},true);" for k, v in local_vars.items())
    code = f"{assigns} include {json.dumps(str(WEB / 'include' / 'components' / (component + '.php')))};"
    return subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB).stdout


def test_box_sizing_covers_pseudo_elements():
    assert "*,*::before,*::after{box-sizing:border-box}" in HEAD.replace(" ", "")
```

- [ ] **Step 4: Run it and verify it fails**

Run: `python3 -m pytest tests/test_template_foundation.py -v`
Expected: FAIL (`assert ... in HEAD` false), proving the harness runs.

- [ ] **Step 5: Commit the harness only after Task 2 makes it pass**

Do not commit yet; Task 2 commits harness and implementation together.

---

### Task 2: Foundation CSS in `base-head.php`

**Files:**
- Modify: `website_download/include/base-head.php:23` (reset), `:154` (pulse dot), `:179` (after page-hero block), `:193-198` (mobile call CTA block)
- Test: `tests/test_template_foundation.py`

**Interfaces:**
- Produces CSS classes used by later tasks: `.ipu-body`, `.ipu-body__intro`, `.ipu-body__aside`, `.ipu-body__rest`, `.ipu-chips`, `.ipu-linkrow`, `.ipu-cards`, `.ipu-card`, `.ipu-steps`, `.ipu-tints`, `.ipu-tint`, `.ipu-tablewrap`, `.ipu-faq`, `.ipu-page-hero__meta`, `.ipu-page-hero__enquire`, `.ipu-cta-card`, `.ipu-cta-call`.

- [ ] **Step 1: Add the failing tests**

Append to `tests/test_template_foundation.py`:
```python
def test_body_grid_css_present():
    css = HEAD.replace(" ", "")
    assert ".ipu-body{" in css
    assert 'grid-template-areas:"intro""aside""rest"' in css
    assert 'grid-template-areas:"introaside""restaside"' in css
    assert ".ipu-body__aside{position:sticky;top:84px}" in css


def test_pulse_animation_removed_from_online_dot():
    assert "ipuPulse" not in HEAD


def test_legacy_mobile_call_gradients_removed_but_layout_kept():
    # base-nav.php owns the sticky bar's look; base-head must keep only layout.
    assert "linear-gradient(135deg,#f59e0b 0%,#FFD700 100%)" not in HEAD
    assert ".mobile-call-cta{position:fixed" in HEAD
    assert "body{padding-bottom:calc(68px" in HEAD


def test_hero_call_button_is_action_orange():
    assert ".ipu-page-hero a.ipu-page-hero__call{background:#ff7a1a;color:#0d1b6e" in HEAD.replace(" ", "")
```

- [ ] **Step 2: Run to verify they fail**

Run: `python3 -m pytest tests/test_template_foundation.py -v`
Expected: 5 FAIL.

- [ ] **Step 3: Edit the reset (line 23)**

Replace `*{margin:0;padding:0;box-sizing:border-box}` with:
```css
*{margin:0;padding:0}*,*::before,*::after{box-sizing:border-box}
```

- [ ] **Step 4: Remove the pulse animation (lines ~152-153)**

Replace the `.ipu-enquiry__phone-badge::before{...animation:ipuPulse 1.6s ease-in-out infinite;display:inline-block}` rule and delete the following `@keyframes ipuPulse{...}` line, leaving a static dot:
```css
.ipu-enquiry__phone-badge::before{content:"";width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.25);display:inline-block}
```

- [ ] **Step 5: Add the shared layout CSS after the page-hero block (after line 179, before the "Inner-page Banner" comment)**

Insert:
```css
/* ===== unified hero extras ===== */
.ipu-page-hero a.ipu-page-hero__call{background:#ff7a1a;color:#0d1b6e;font-weight:800;font-size:16px;min-height:48px;border-radius:8px;box-shadow:var(--ipu-cta-shadow)}
.ipu-page-hero a.ipu-page-hero__call:hover{background:#ff8a35;color:#0d1b6e}
.ipu-page-hero__call svg{fill:#0d1b6e}
.ipu-page-hero__actions{display:flex;flex-wrap:wrap;gap:12px;align-items:center}
.ipu-page-hero a.ipu-page-hero__enquire{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:13px 24px;border:1.5px solid rgba(255,255,255,.5);border-radius:8px;background:transparent;color:#fff;font-weight:700;font-size:15px;text-decoration:none}
.ipu-page-hero a.ipu-page-hero__enquire:hover{border-color:#fff}
.ipu-page-hero__meta{font-size:14px;color:rgba(255,255,255,.75);margin:0 0 18px}

/* ===== unified body grid (single DOM copy of the sidebar) ===== */
.ipu-body{max-width:1200px;margin:0 auto;padding:24px 16px 32px;display:grid;grid-template-columns:minmax(0,1fr);grid-template-areas:"intro" "aside" "rest";gap:28px;align-items:start}
.ipu-body__intro{grid-area:intro;min-width:0}.ipu-body__aside{grid-area:aside;min-width:0}.ipu-body__rest{grid-area:rest;min-width:0}
@media(min-width:992px){.ipu-body{padding:40px 24px 56px;grid-template-columns:minmax(0,1fr) 360px;grid-template-areas:"intro aside" "rest aside";gap:28px 40px}
.ipu-body__aside{position:sticky;top:84px}}
.ipu-body h2{font-size:22px;line-height:1.25;font-weight:800;color:var(--ipu-ink);margin:0 0 12px}
@media(min-width:992px){.ipu-body h2{font-size:26px}}
.ipu-body__hero-img{display:block;width:100%;height:auto;aspect-ratio:5/3;object-fit:cover;border-radius:16px;margin-bottom:20px}
.ipu-body section{margin-bottom:28px}

/* ===== unified content blocks ===== */
.ipu-chips{list-style:none;padding:0;margin:0 0 16px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px}
.ipu-chips li{background:var(--ipu-bg);border:1px solid var(--ipu-rule);border-radius:8px;padding:10px 12px;font-size:15px;display:flex;align-items:center;gap:10px}
.ipu-chips li::before{content:"";flex:0 0 6px;height:6px;border-radius:50%;background:var(--ipu-orange)}
.ipu-linkrow{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:48px;padding:14px 16px;background:var(--ipu-highlight);border-radius:12px;font-weight:700;color:var(--ipu-ink);text-decoration:none}
.ipu-linkrow::after{content:"\2192";color:var(--ipu-orange)}
.ipu-cards{display:grid;gap:16px}
.ipu-card{border:1px solid var(--ipu-rule);border-radius:12px;padding:18px;background:#fff}
.ipu-card h3{font-size:17px;font-weight:700;margin:0 0 10px;color:var(--ipu-ink)}
.ipu-card ul{padding-left:18px;margin:0 0 12px}
.ipu-card__foot{border-top:1px solid #eef2f7;padding-top:12px;margin:0;font-size:15px}
.ipu-steps{list-style:none;padding:0;margin:0 0 14px;counter-reset:ipustep}
.ipu-steps li{counter-increment:ipustep;display:flex;gap:12px;align-items:flex-start;padding:8px 0}
.ipu-steps li::before{content:counter(ipustep);flex:0 0 28px;height:28px;border-radius:50%;background:var(--ipu-highlight);color:var(--ipu-ink-2);font-weight:800;display:flex;align-items:center;justify-content:center;font-size:14px}
.ipu-tints{display:grid;grid-template-columns:1fr;gap:16px}
@media(min-width:768px){.ipu-tints{grid-template-columns:1fr 1fr}}
.ipu-tint{background:var(--ipu-bg);border-radius:12px;padding:18px}
.ipu-tint ul{padding-left:18px;margin:0}
.ipu-tablewrap{overflow-x:auto;border:1px solid var(--ipu-rule);border-radius:12px;font-variant-numeric:tabular-nums}
.ipu-tablewrap td:first-child,.ipu-tablewrap th:first-child{position:sticky;left:0;background:#fff}
.ipu-faq details{border:1px solid var(--ipu-rule);border-radius:12px;margin-bottom:10px;background:#fff}
.ipu-faq summary{list-style:none;cursor:pointer;min-height:52px;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.ipu-faq summary::-webkit-details-marker{display:none}
.ipu-faq summary h3{margin:0;font-size:16px;font-weight:700;color:var(--ipu-ink)}
.ipu-faq summary::after{content:"+";color:var(--ipu-orange);font-size:22px;font-weight:700}
.ipu-faq details[open] summary::after{content:"\2212"}
.ipu-faq details p{padding:0 16px 16px;margin:0}
.ipu-cta-card{background:linear-gradient(135deg,var(--ipu-ink) 0%,var(--ipu-ink-2) 100%);border-radius:16px;padding:24px;color:#fff;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between}
.ipu-cta-card h2,.ipu-cta-card h3,.ipu-cta-card p{color:#fff;margin:0}
.ipu-cta-card p{color:rgba(255,255,255,.8);font-size:15px}
a.ipu-cta-call{display:inline-flex;align-items:center;gap:10px;min-height:48px;padding:14px 28px;border-radius:999px;background:#ff7a1a;color:#0d1b6e;font-weight:800;font-size:16px;text-decoration:none;box-shadow:var(--ipu-cta-shadow)}
a.ipu-cta-call:hover{background:#ff8a35;color:#0d1b6e}
```

- [ ] **Step 6: Strip the legacy colours from the mobile call CTA (the `/* Mobile Call CTA */` block)**

`base-nav.php:31-42` already sets the sticky bar's background, button colour and layout with `!important`. Keep position, z-index, safe-area padding and the body bottom padding; remove only the gradient/colour/shadow declarations. The block becomes:
```css
/* Mobile Call CTA — layout only; base-nav.php owns the look */
@media(max-width:768px){
  .mobile-call-cta{position:fixed;bottom:0;left:0;right:0;padding:12px 16px calc(12px + env(safe-area-inset-bottom));z-index:9999}
  .mobile-call-btn{display:flex;align-items:center;justify-content:center;gap:8px;min-height:48px;border:none;padding:12px;border-radius:50px;font-weight:700;font-size:16px;text-decoration:none;width:100%}
  body{padding-bottom:calc(68px + env(safe-area-inset-bottom))}
}
@media(min-width:769px){.mobile-call-cta{display:none}}
```
Preserve any trailing declarations on the original `.mobile-call-btn` line that are not colours (read the full line first; the earlier view truncated it).

- [ ] **Step 7: Run tests**

Run: `python3 -m pytest tests/test_template_foundation.py -v`
Expected: 5 PASS.

- [ ] **Step 8: Check the sticky bar visually is unchanged**

Run: `cd /Users/Sumit/test-project/website_download && php -S 127.0.0.1:8000 >/dev/null 2>&1 &` then open `http://127.0.0.1:8000/BPIT.php` in headless Chrome at 390px (force the viewport via CDP, see memory `reference_headless_chrome_viewport_crop`). Expected: the bottom bar shows the white bar with orange "Call a counsellor" (7) and "Enquire" (3), identical to production `https://ipu.co.in/BPIT.php` at 390px. If the bar lost its fixed position or the body padding, restore the missing layout declaration. Stop the server afterwards.

- [ ] **Step 9: Run both suites and commit**

Run:
```bash
cd /Users/Sumit/test-project && php scripts/tests/run.php | tail -2 && python3 -m pytest tests -q 2>&1 | tail -2 && git add tests/test_template_foundation.py website_download/include/base-head.php && git commit -m "feat(ui): shared body grid, content-block CSS, static online dot, layout-only legacy call bar

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: suites green (counts match Task 1 Step 2 plus the new tests), commit created.

---

### Task 3: Hero upgrade (Enquire button, meta slot, orange call button)

**Files:**
- Modify: `website_download/include/components/page-hero.php`
- Test: `tests/test_template_foundation.py`

**Interfaces:**
- Consumes: CSS classes from Task 2 (`.ipu-page-hero__actions`, `__enquire`, `__meta`).
- Produces new optional locals: `$hero_show_enquire` (bool, default `false`), `$hero_meta` (string, default null; HTML allowed). Existing locals unchanged.

- [ ] **Step 1: Write the failing tests**

Append to `tests/test_template_foundation.py`:
```python
HERO = dict(hero_h1="Test H1", hero_show_form=False, show_trust_bar=False,
            hero_breadcrumbs=[["Home", "/"], ["Colleges", "/ipu-colleges-list.php"], ["BPIT College", ""]])


def test_hero_default_has_no_enquire_button_and_one_h1():
    html = render("page-hero", **HERO)
    assert html.count("<h1") == 1
    assert "#enquiry-form" not in html
    assert 'href="tel:+919899991342"' in html
    assert 'data-cta-src="hero"' in html


def test_hero_enquire_button_opt_in():
    html = render("page-hero", hero_show_enquire=True, **HERO)
    assert 'href="#enquiry-form"' in html and "ipu-page-hero__enquire" in html
    assert html.count('href="#enquiry-form"') == 1


def test_hero_meta_slot_renders_when_set():
    html = render("page-hero", hero_meta="Last Updated: 6 April 2026", **HERO)
    assert '<p class="ipu-page-hero__meta">Last Updated: 6 April 2026</p>' in html
    assert "ipu-page-hero__meta" not in render("page-hero", **HERO)


def test_hero_breadcrumb_unchanged_markup():
    html = render("page-hero", **HERO)
    assert '<a href="/ipu-colleges-list.php">Colleges</a>' in html
    assert '<span aria-current="page">BPIT College</span>' in html
```

- [ ] **Step 2: Run to verify failures**

Run: `python3 -m pytest tests/test_template_foundation.py -v -k hero`
Expected: `enquire_button_opt_in` and `meta_slot` FAIL; the other two PASS (they pin current behaviour).

- [ ] **Step 3: Add the locals (after `$hero_slot_html = ...`)**

```php
$hero_show_enquire = $hero_show_enquire ?? false;  // opt-in: only pages whose body carries #enquiry-form
$hero_meta         = $hero_meta         ?? null;   // meta line under the H1 (HTML allowed)
```
Also add both to the header comment's locals list.

- [ ] **Step 4: Render the meta line and the two buttons**

Replace the existing `<a href="tel:+919899991342" class="ipu-btn-primary ipu-page-hero__call" data-cta-src="hero">...</a>` block with:
```php
          <?php if ($hero_meta): ?>
            <p class="ipu-page-hero__meta"><?= $hero_meta ?></p>
          <?php endif; ?>

          <div class="ipu-page-hero__actions">
            <a href="tel:+919899991342" class="ipu-btn-primary ipu-page-hero__call" data-cta-src="hero">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
              Call: 9899991342
            </a>
            <?php if ($hero_show_enquire): ?>
              <a href="#enquiry-form" class="ipu-page-hero__enquire" data-cta-src="hero-enquire">Enquire</a>
            <?php endif; ?>
          </div>
```
Keep the phone SVG path exactly as in the file (copy it, do not retype).

- [ ] **Step 5: Run tests, suites, commit**

Run:
```bash
cd /Users/Sumit/test-project && python3 -m pytest tests/test_template_foundation.py -v && php scripts/tests/run.php | tail -2 && python3 -m pytest tests -q 2>&1 | tail -2 && git add tests/test_template_foundation.py website_download/include/components/page-hero.php && git commit -m "feat(ui): hero meta slot, opt-in Enquire button, action-orange call button

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: all green.

- [ ] **Step 6: Regression-check pages that already use the hero**

Run (server from Task 2 Step 8 pattern, port 8000):
```bash
cd /Users/Sumit/test-project && for p in $(grep -l 'components/page-hero' website_download/*.php | xargs -n1 basename); do echo -n "$p: "; python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /$p | tail -1; done
```
Expected: no page reports a new problem versus the same loop run on `git stash`-free `HEAD~2`. Report any difference.

---

### Task 4: CTA strip button

**Files:**
- Modify: `website_download/include/components/cta-strip.php`
- Test: `tests/test_template_foundation.py`

**Interfaces:**
- Consumes: `.ipu-cta-call`, `.ipu-cta-card` from Task 2.
- Produces: optional local `$cta_card` (bool, default false). Default output keeps the full-width section; `true` renders the rounded card used inside the body grid.

- [ ] **Step 1: Write the failing tests**

```python
def test_cta_strip_button_is_orange_pill_not_gold_gradient():
    html = render("cta-strip")
    assert "FFD700" not in html and "f59e0b,#FFD700" not in html
    assert 'class="ipu-cta-call"' in html
    assert 'href="tel:+919899991342"' in html and 'data-cta-src="hero"' in html
    assert "Call: 9899991342" in html


def test_cta_strip_card_variant():
    html = render("cta-strip", cta_card=True, cta_heading="Need Admission Guidance?", cta_subtext="x")
    assert 'class="ipu-cta-card"' in html and "Need Admission Guidance?" in html
    assert "<section" not in html
```

- [ ] **Step 2: Run, expect FAIL**

Run: `python3 -m pytest tests/test_template_foundation.py -v -k cta_strip`

- [ ] **Step 3: Rewrite the component body (keep the docblock and the two `??` defaults; add `$cta_card = $cta_card ?? false;`)**

```php
<?php
$cta_heading = $cta_heading ?? 'Need Admission Guidance?';
$cta_subtext = $cta_subtext ?? 'Talk to our expert counsellors for free guidance on IPU admissions';
$cta_card    = $cta_card    ?? false;   // true = rounded card for use inside .ipu-body__rest
$cta_call = function () { ?>
  <a href="tel:+919899991342" class="ipu-cta-call" data-cta-src="hero">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="#0d1b6e" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    Call: 9899991342
  </a>
<?php };
if ($cta_card): ?>
<div class="ipu-cta-card">
  <div>
    <h3><?= htmlspecialchars($cta_heading) ?></h3>
    <p><?= htmlspecialchars($cta_subtext) ?></p>
  </div>
  <?php $cta_call(); ?>
</div>
<?php else: ?>
<section style="background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 100%);padding:40px 0;margin:40px 0">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
        <h3 style="color:#fff;margin-bottom:8px;font-size:clamp(1.2rem,2vw,1.6rem)"><?= htmlspecialchars($cta_heading) ?></h3>
        <p style="color:rgba(255,255,255,.75);margin:0;font-size:15px"><?= htmlspecialchars($cta_subtext) ?></p>
      </div>
      <div class="col-lg-4 col-md-5 text-md-end"><?php $cta_call(); ?></div>
    </div>
  </div>
</section>
<?php endif; ?>
```
Use the phone SVG path copied from the existing file.

- [ ] **Step 4: Run tests and suites, commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests/test_template_foundation.py -v && php scripts/tests/run.php | tail -2 && python3 -m pytest tests -q 2>&1 | tail -2 && git add tests/test_template_foundation.py website_download/include/components/cta-strip.php && git commit -m "feat(ui): CTA strip uses the site-wide orange pill call button, adds card variant

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: green. The 46 pages using `cta-strip.php` get the new button colour; the heading/subtext/phone number/`data-cta-src` are unchanged.

---

### Task 5: BPIT pilot

**Files:**
- Create: `tests/fixtures/bpit_baseline.json`, `tests/test_bpit_pilot.py`
- Modify: `website_download/BPIT.php`

**Interfaces:**
- Consumes: classes from Task 2; hero locals `$hero_breadcrumbs`, `$last_updated`, `$hero_show_enquire` from Task 3; `$cta_card` from Task 4.
- Produces: the reference markup for all later college pages (a later plan converts `include/templates/college-page-template.php` from it).

- [ ] **Step 1: Capture the pre-change baseline from the live page**

Run:
```bash
cd /Users/Sumit/test-project && python3 - <<'E'
import re, json, urllib.request
h = urllib.request.urlopen("https://ipu.co.in/BPIT.php").read().decode("utf-8","replace")
body = h.split("</head>")[1]
g = lambda p: (re.search(p, h, re.S) or [None, None])[1]
base = {
 "title": g(r"<title>(.*?)</title>"),
 "description": g(r'<meta name="description" content="(.*?)"'),
 "canonical": g(r'<link rel="canonical" href="(.*?)"'),
 "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
 "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
}
json.dump(base, open("tests/fixtures/bpit_baseline.json", "w"), indent=1, ensure_ascii=False)
print(base["title"], len(base["headings"]), len(base["links"]))
E
```
Expected: prints the BPIT title, a heading count and a link count. Open the JSON and confirm the title starts `BPIT Rohini 2026`. Headings from the sidebar/footer/nav are in the baseline too; the test (Step 2) therefore compares the new page against this list in the same way (same extraction), not against BPIT-only copy.

- [ ] **Step 2: Write the failing pilot tests**

Create `tests/test_bpit_pilot.py` (reuse the `server` and `_get` helpers from `tests/test_cutoff_pages.py` verbatim, copied into this file, plus `import json, re` and `from seo_verify import check_html`):
```python
BASE = json.load(open(ROOT / "tests" / "fixtures" / "bpit_baseline.json"))


@pytest.fixture(scope="module")
def page(server):
    status, html = _get(server + "/BPIT.php")
    assert status == 200
    return html


def _g(pat, html):
    m = re.search(pat, html, re.S)
    return m.group(1) if m else None


def test_seo_invariants_unchanged(page):
    assert _g(r"<title>(.*?)</title>", page) == BASE["title"]
    assert _g(r'<meta name="description" content="(.*?)"', page) == BASE["description"]
    assert _g(r'<link rel="canonical" href="(.*?)"', page) == BASE["canonical"]


def test_structure_one_h1_one_form_no_legacy(page):
    assert len(re.findall(r"<h1[\s>]", page)) == 1
    assert page.count('id="enquiry-form"') == 1
    assert "breadcrumb-item" not in page
    assert "pt-130" not in page and "blog-wrapper" not in page
    assert "👉" not in page
    assert check_html(page) == []


def test_headings_same_text_and_order_apart_from_faq_and_cta(page):
    body = page.split("</head>")[1]
    got = [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)]
    expected = [h for h in BASE["headings"] if h != "Need Admission Guidance?"]
    got = [h for h in got if h != "Need Admission Guidance?"]
    # Order may move only for the cutoff table block (handoff: moved above FAQ); compare as multisets plus
    # relative order of the content H2s.
    assert sorted(got) == sorted(expected)
    core = ["About BPIT College (IP University)", "Courses Offered at BPIT", "Admission Process (IP University)",
            "Placement Overview", "Campus & Location Advantage", "Frequently Asked Questions (BPIT IP University)",
            "Compare with Other Top IPU Colleges"]
    idx = [got.index(h) for h in core]
    assert idx[:5] == sorted(idx[:5])


def test_every_baseline_link_still_present(page):
    body = page.split("</head>")[1]
    now = set(re.findall(r'href="([^"#][^"]*)"', body))
    missing = [l for l in BASE["links"] if l not in now]
    assert missing == []


def test_hero_breadcrumb_and_schema_match(page):
    crumbs = _g(r'<nav class="ipu-page-hero__crumbs".*?</nav>', page) or ""
    assert "Home" in crumbs and "Colleges" in crumbs and "BPIT College" in crumbs
    ld = re.findall(r'<script type="application/ld\+json">(.*?)</script>', page, re.S)
    bl = [json.loads(x) for x in ld if '"BreadcrumbList"' in x][0]
    names = [i["name"] for i in bl["itemListElement"]]
    assert names == ["Home", "Colleges", "BPIT College"]


def test_faq_visible_equals_schema(page):
    visible = re.findall(r"<details[^>]*>\s*<summary[^>]*>\s*<h3[^>]*>(.*?)</h3>.*?</summary>\s*<p>(.*?)</p>", page, re.S)
    assert len(visible) == 3
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', page, re.S) if '"FAQPage"' in x][0]
    schema = [(q["name"], q["acceptedAnswer"]["text"]) for q in ld["mainEntity"]]
    norm = lambda s: re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", s)).strip().replace("&amp;", "&")
    assert [(norm(a), norm(b)) for a, b in visible] == [(norm(a), norm(b)) for a, b in schema]


def test_dom_order_form_after_about_before_courses(page):
    a = page.index("About BPIT College (IP University)")
    f = page.index('id="enquiry-form"')
    c = page.index("Courses Offered at BPIT")
    assert a < f < c


def test_social_tags_present_and_phone_links_normalised(page):
    for tag in ("og:title", "og:description", "og:url", "og:type", "twitter:title", "twitter:description"):
        assert tag in page
    assert 'href="tel:9899991342"' not in page
    assert 'href="#enquiry-form"' in page  # hero Enquire button
```

- [ ] **Step 3: Run, expect failures**

Run: `python3 -m pytest tests/test_bpit_pilot.py -v`
Expected: `structure`, `hero_breadcrumb`, `faq_visible_equals_schema`, `dom_order`, `social_tags` FAIL; `seo_invariants`, `links` PASS.

- [ ] **Step 4: Head block edits in `BPIT.php`**

After the canonical line, add (values copied from the existing title/description; do not change them):
```php
<meta property="og:title" content="BPIT Rohini 2026 | B.Tech Admission, Courses &amp; Placements – IPU">
<meta property="og:description" content="BPIT (Bhagwan Parshuram Institute of Technology) Rohini – B.Tech CSE, IT, ECE, BBA, MBA. Cutoff, fees &amp; placements. Call 9899991342 for free IPU admission help.">
<meta property="og:url" content="https://ipu.co.in/BPIT.php">
<meta property="og:type" content="website">
<meta name="twitter:title" content="BPIT Rohini 2026 | B.Tech Admission, Courses &amp; Placements – IPU">
<meta name="twitter:description" content="BPIT (Bhagwan Parshuram Institute of Technology) Rohini – B.Tech CSE, IT, ECE, BBA, MBA. Cutoff, fees &amp; placements. Call 9899991342 for free IPU admission help.">
```
Change `$breadcrumbs` to:
```php
$breadcrumbs = [['Home', '/'], ['Colleges', '/ipu-colleges-list.php'], ['BPIT College', '']];
```
(first check `ipu-colleges-list.php` exists and returns 200 on production; if not, stop and ask).

- [ ] **Step 5: Replace the hero block**

```php
<?php
$hero_h1 = 'BPIT College (Bhagwan Parshuram Institute of Technology) – Complete Guide';
$hero_show_form = false;
$hero_show_enquire = true;
$hero_breadcrumbs = [['Home', '/'], ['Colleges', '/ipu-colleges-list.php'], ['BPIT College', '']];
$last_updated = '2026-04-06';
include __DIR__ . '/include/components/page-hero.php';
?>
```
(The hero already renders "Last Updated … By IPU Admission Team" in dark theme with `<time datetime>`; the body's `last-updated.php` include is deleted in Step 6.)

- [ ] **Step 6: Rebuild the body on the grid**

Replace everything from `<section class="blog-wrapper pt-130 pb-130">` through the closing `</section>` with the structure below. All heading, paragraph, list and link text is moved verbatim from the current file; only wrappers and the three FAQ and two tint blocks change shape. Keep the original `<picture>...</picture>` element's `srcset`, `src`, `alt`, `width`, `height` and `fetchpriority` attributes exactly as they are and add `class="ipu-body__hero-img"` in place of `class="main-img"`.

```php
<div class="ipu-body">

  <div class="ipu-body__intro blog-details">
    <!-- original <picture>…</picture> with class="ipu-body__hero-img" -->
    <h2>About BPIT College (IP University)</h2>
    <p><!-- original About paragraph, verbatim --></p>
    <ul class="ipu-chips">
      <li>Affiliated to GGSIPU</li>
      <li>Located in Rohini Delhi</li>
      <li>Industry-focused curriculum</li>
      <li>Good placement ecosystem</li>
      <li>Strong academic environment</li>
    </ul>
    <a class="ipu-linkrow" href="ipu-admission-guide.php"><strong>Complete IP University Admission Guide</strong></a>
  </div>

  <div class="ipu-body__aside">
    <?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
  </div>

  <div class="ipu-body__rest blog-details">

    <section>
      <h2>Courses Offered at BPIT</h2>
      <div class="ipu-cards">
        <div class="ipu-card">
          <h3>B.Tech Courses</h3>
          <ul>
            <li>Computer Science Engineering</li>
            <li>Information Technology</li>
            <li>Electronics &amp; Communication Engineering</li>
            <li>Computer Science – Data Science</li>
            <li>Electrical Engineering</li>
          </ul>
          <p class="ipu-card__foot">Read B.Tech admission guide: <a href="IPU-B-Tech-admission-2026.php">IPU B.Tech Admission Complete Guide</a></p>
        </div>
        <div class="ipu-card">
          <h3>BBA Program</h3>
          <ul>
            <li>Business management fundamentals</li>
            <li>Industry-focused curriculum</li>
            <li>Management skill development</li>
          </ul>
          <p class="ipu-card__foot">Read BBA admission guide: <a href="comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php">BBA Colleges under IP University</a></p>
        </div>
        <div class="ipu-card">
          <h3>MBA Program</h3>
          <ul>
            <li>Marketing, Finance, HR specializations</li>
            <li>Corporate exposure and internships</li>
          </ul>
          <p class="ipu-card__foot">Read MBA admission guide: <a href="mba-admission-ip-university.php">MBA Admission in IP University</a></p>
        </div>
      </div>
    </section>

    <section>
      <h2>Admission Process (IP University)</h2>
      <h3>B.Tech Admission Criteria</h3>
      <ol class="ipu-steps">
        <li>Primary preference: JEE Main</li>
        <li>Secondary pathway: CUET (if applicable)</li>
        <li>Minimum eligibility: 55% marks in Class 12 PCM</li>
      </ol>
      <p>Counselling process: <a href="GGSIPU-counselling-for-B-Tech-admission.php">IPU Counselling Guide</a></p>
    </section>

    <?php $cta_card = true; $cta_heading = 'Need Admission Guidance?'; include __DIR__ . '/include/components/cta-strip.php'; ?>

    <div class="ipu-tints">
      <section class="ipu-tint">
        <h2>Placement Overview</h2>
        <ul>
          <li>Strong placement opportunities for CSE &amp; IT</li>
          <li>Roles in software development &amp; analytics</li>
          <li>Internship programs and industry exposure</li>
        </ul>
      </section>
      <section class="ipu-tint">
        <h2>Campus &amp; Location Advantage</h2>
        <ul>
          <li>Rohini Delhi prime location</li>
          <li>Metro connectivity</li>
          <li>Corporate exposure</li>
          <li>Modern campus facilities</li>
        </ul>
      </section>
    </div>

    <!-- Fee table: original block moved verbatim; wrap its <table> in <div class="ipu-tablewrap">…</div> -->

    <?php
    $facts_key = 'bpit'; include __DIR__ . '/include/components/college-facts-block.php';
    $cutoff_institute = 'Bhagwan Parshuram Institute of Technology';
    include __DIR__ . '/include/components/btech-cutoff-rounds-table.php';
    ?>

    <section class="ipu-faq">
      <h2>Frequently Asked Questions (BPIT IP University)</h2>
      <details><summary><h3>Is BPIT good for B.Tech?</h3></summary><p>Yes, BPIT is known for strong technical programs, especially Computer Science and IT branches.</p></details>
      <details><summary><h3>Which entrance exam is required for BPIT admission?</h3></summary><p>B.Tech admission is primarily through JEE Main, while BBA uses CET/CUET and MBA uses CAT/CMAT/CET.</p></details>
      <details><summary><h3>Is BPIT better than other IPU colleges?</h3></summary><p>BPIT is a good option depending on branch preference, placements and location advantage.</p></details>
    </section>

    <section>
      <h2>Compare with Other Top IPU Colleges</h2>
      <ul>
        <li><a href="exploring-MAIT-and-MAIMS.php">MAIT &amp; MAIMS Guide</a></li>
        <li><a href="explore-MSIT-and-MSI-janakpuri.php">MSIT College Review</a></li>
        <li><a href="vips-admission.php">VIPS Pitampura Review</a></li>
      </ul>
    </section>

    <?php $cta_card = true; $cta_heading = 'Need Admission Guidance?'; include __DIR__ . '/include/components/cta-strip.php'; ?>
    <p><b><?php include("include/phone.php"); ?></b></p>
  </div>

</div>
```
Notes for the implementer:
- Before deleting the old section, check what `include/components/college-facts-block.php` and `btech-cutoff-rounds-table.php` output (they were previously rendered AFTER the `</section>`, outside the container, in the `related_pages` block). If either renders a full-width `<section>` with its own container, keep it AFTER `.ipu-body` instead of inside `rest` and note this in the commit message; do not restyle those components in this plan.
- The second `cta-strip` + `phone.php` is the handoff's "restyle the trailing guidance block as a CTA strip" decision (owner-confirmed keep). If `phone.php` output duplicates the strip's number, drop only the `<p><b>…phone.php…</b></p>` line and say so; the phone number remains in the strip.
- Keep the original `$related_pages` array and `include 'include/components/related-pages.php'` after `.ipu-body`, then `base-footer.php`.
- Replace the remaining inline `👉` with nothing (the link text is unchanged; the `ipu-linkrow` and `ipu-card__foot` carry the visual cue).

- [ ] **Step 7: Align the FAQPage JSON-LD to the visible FAQ**

Replace the three `Question` objects in the trailing `<script type="application/ld+json">` with:
```json
{"@type":"Question","name":"Is BPIT good for B.Tech?","acceptedAnswer":{"@type":"Answer","text":"Yes, BPIT is known for strong technical programs, especially Computer Science and IT branches."}},
{"@type":"Question","name":"Which entrance exam is required for BPIT admission?","acceptedAnswer":{"@type":"Answer","text":"B.Tech admission is primarily through JEE Main, while BBA uses CET/CUET and MBA uses CAT/CMAT/CET."}},
{"@type":"Question","name":"Is BPIT better than other IPU colleges?","acceptedAnswer":{"@type":"Answer","text":"BPIT is a good option depending on branch preference, placements and location advantage."}}
```

- [ ] **Step 8: Run the pilot tests until green**

Run: `python3 -m pytest tests/test_bpit_pilot.py -v`
Expected: all PASS. If `test_headings_...` fails only because the CTA heading changed from H2 to H3 or the facts block adds headings, adjust the test's exclusions (not the page copy) and document why in the commit message.

- [ ] **Step 9: Visual check against the handoff**

Start `php -S 127.0.0.1:8000 -t website_download`. Capture `/BPIT.php` at 390px and 1280px with headless Chrome (force the viewport via CDP). Compare to `IPU Page Templates.dc.html` College view. Checklist: hero breadcrumb is Home / Colleges / BPIT College; orange Call and outlined Enquire buttons; the form follows the About section on mobile; the form is a sticky right column on desktop; tables scroll sideways inside their wrapper; no horizontal page scroll; sticky bottom bar unchanged. Fix class or CSS issues in `base-head.php` (not inline styles in BPIT). Re-run Step 8 after any change.

- [ ] **Step 10: Crosslink walk and SEO verify**

Run:
```bash
cd /Users/Sumit/test-project && python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /BPIT.php && grep -c 'tel:9899991342' website_download/BPIT.php
```
Expected: no problems; count 0. Then click through every nav, footer, sidebar and CTA link on localhost (memory rule `feedback_localhost_crosslink_test`), including links rewritten by JS (`grep -n "setAttribute\|\.href=" website_download/include/*.php`).

- [ ] **Step 11: Full suites and commit**

```bash
cd /Users/Sumit/test-project && php scripts/tests/run.php | tail -2 && python3 -m pytest tests -q 2>&1 | tail -2 && git add tests/test_bpit_pilot.py tests/fixtures/bpit_baseline.json website_download/BPIT.php && git commit -m "feat(ui): BPIT pilot on the unified body grid (single breadcrumb, details FAQ, schema aligned)

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 6: Sitemap additions

**Files:**
- Create: `tests/test_sitemap_additions.py`
- Modify: `website_download/sitemap.xml`

**Interfaces:**
- Produces: 8 new `<url>` entries; nothing else changes.

- [ ] **Step 1: Re-sync against live before editing**

Run:
```bash
cd /Users/Sumit/test-project && git fetch origin && git merge --ff-only origin/main; curl -s https://ipu.co.in/sitemap.xml > /tmp/live_sitemap.xml && diff <(grep -oE '<loc>[^<]+' /tmp/live_sitemap.xml | sort) <(grep -oE '<loc>[^<]+' website_download/sitemap.xml | sort) | head
```
Expected: no `<` lines (live has nothing the repo lacks). If live has URLs the repo lacks, copy live over `website_download/sitemap.xml` first (`cp /tmp/live_sitemap.xml website_download/sitemap.xml`) and commit that alone as `chore: sync sitemap with live`; never edit a stale copy.

- [ ] **Step 2: Write the failing test**

```python
import re
from pathlib import Path
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
SM = ROOT / "website_download" / "sitemap.xml"
NEW = ["economics-admission-ip-university", "cuet-admission-ipu", "cuet-btech-admission-ipu",
       "cuet-bba-admission-ipu", "cuet-bcom-admission-ipu", "cuet-law-admission-ipu",
       "law-3-year-admission-ipu", "mca-admission-ipu"]


def locs():
    ns = {"s": "http://www.sitemaps.org/schemas/sitemap/0.9"}
    return [e.text for e in ET.parse(SM).getroot().findall("s:url/s:loc", ns)]


def test_new_pages_listed_once():
    L = locs()
    for slug in NEW:
        assert L.count(f"https://ipu.co.in/{slug}.php") == 1, slug


def test_noindex_privacy_policy_excluded():
    assert not any("privacy-policy" in u for u in locs())


def test_valid_and_unique():
    L = locs()
    assert len(L) == len(set(L))
    assert all(u.startswith("https://ipu.co.in/") for u in L)
```

- [ ] **Step 3: Run, expect FAIL**

Run: `python3 -m pytest tests/test_sitemap_additions.py -v` (first test fails).

- [ ] **Step 4: Look at a peer entry and each page's last change date**

Run:
```bash
cd /Users/Sumit/test-project/website_download && grep -B1 -A3 'mba-admission-ip-university' sitemap.xml | head -8; for s in economics-admission-ip-university cuet-admission-ipu cuet-btech-admission-ipu cuet-bba-admission-ipu cuet-bcom-admission-ipu cuet-law-admission-ipu law-3-year-admission-ipu mca-admission-ipu; do echo "$s $(git log -1 --format=%cs -- $s.php)"; done
```
Use the peer's `<priority>` for all eight (0.80 if the peer is not found), and each page's date as `<lastmod>`.

- [ ] **Step 4b: Insert the entries**

Insert before `</urlset>` (or after the course-pages group if one exists), one block per page in this form, with the date from Step 4:
```xml
<url>
<loc>https://ipu.co.in/cuet-admission-ipu.php</loc>
<lastmod>YYYY-MM-DD</lastmod>
<priority>0.80</priority>
</url>
```
(Write all 8; no entry for `privacy-policy.php`.)

- [ ] **Step 5: Run tests and commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && git add tests/test_sitemap_additions.py website_download/sitemap.xml && git commit -m "feat(seo): add 8 live indexable pages to sitemap (privacy-policy stays out, noindex)

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: green. Also check `llms.txt` for the same 8 pages: `grep -c 'cuet-admission-ipu' website_download/llms.txt`; if they are absent, report to the owner (do not edit; it is a separate decision).

---

### Task 7: Final verification and owner hand-off (no deploy)

**Files:** none modified.

- [ ] **Step 1: Branch-wide verification**

Run:
```bash
cd /Users/Sumit/test-project && php scripts/tests/run.php | tail -2 && python3 -m pytest tests -q 2>&1 | tail -3 && git log --oneline origin/main..HEAD && git diff --stat origin/main..HEAD
```
Expected: suites green; the diff touches only the files listed in File Structure plus the spec/plan docs; `index.php` and `trust-bar.php` are absent from the diff.

- [ ] **Step 2: Regression sweep over pages affected by shared includes**

Run the localhost server and:
```bash
cd /Users/Sumit/test-project && for p in $(grep -lE 'components/(page-hero|cta-strip)' website_download/*.php | xargs -n1 basename); do echo -n "$p: "; python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /$p | tail -1; done
```
Expected: every page the same as before. Spot-check three of them visually at 390px and 1280px (hero buttons, CTA strip button, sticky bar).

- [ ] **Step 3: Hand off for owner review**

Report to the owner: the commits, test counts, what visibly changes sitewide (hero call button and CTA strip button turn orange; online dot is static), what changed only on BPIT, and the deploy plan: file-scoped rsync from a clean worktree (back up live first, `cmp` against git HEAD~N for drift, curl 200 plus fatal-error grep), `.htaccess` and live `sitemap.xml` untouched except the 8 entries. Do not push or deploy until the owner says go. After sign-off, write the next plans: college template + remaining 29 college pages, news article template, course/cutoff pages, older pages.
