# ipu.co.in CTA System — Design Spec

**Date:** 2026-07-26
**Branch:** `claude/2026-04-30-ipu-session` (established prod-deploy branch; no merge to main)
**Source inputs:** external design handoff `~/Downloads/design_handoff_ipu_redesign/` (README + 3 `.dc.html` mockups) and the audit PDF `~/Downloads/IPU website content audit.pdf`, §5 "The CTA system".

## Goal

Make the phone call the single loudest, clearest action on every page, to lift call-clicks to **9899991342**. Today the phone CTA is the same navy as everything else and appears ~9× per page, so it reads as background. One exclusive colour, fewer/better-placed CTAs, and clickable-only phone numbers make the call the most prominent element.

**Primary conversion:** `tel:+919899991342` click. **Secondary:** the existing enquiry form (email/callback).

## Guardrails (hard constraints)

- **SEO-safety** ([[feedback_seo_safety_ipu]]): no changes to URL, `<title>`, meta description, canonical, or H1 on any page. Additive / styling / body-CTA only.
- **Never edit `include/base-head.php`.** It carries the HELD Phase-2 commit `a7627b8` (security headers, held until after 2026-08-15). Editing/deploying it re-opens the entanglement incident that 500'd 12 pages on 2026-07-18 (see `seo/rechecks/2026-07-18/decision.md` and the 2026-07-26 hotfix `ab1acf5`). All new CTA CSS/JS lives in `include/base-nav.php`, which is Phase-2-clean, global (95 pages), and cascades **after** base-head so its rules win.
- **One enquiry form per page** ([[feedback_one_form_per_page]]): the "Enquire" action *links* to the page's existing enquiry-form anchor; it never introduces a second form.
- **Action colour is for calling only.** `#ff7a1a` may not colour any heading, tag, border, icon, or decoration.
- **Measure via GA4, not CRM** ([[project_ipu_crm_disconnection]]): the site's forms do not feed a CRM; call-click lift is measured through GA4 events.

## Success criteria

- Measurable increase in `cta_call` GA4 events (call-click rate) after Wave 1, week-over-week.
- No ranking regression: watch terms in `seo/baselines/2026-06-11-primary-targets.csv` do not drop >2 positions at the post-deploy recheck (esp. after the Wave 2a FAQ edit).
- No Core Web Vitals regression: the sticky bar reserves its height (no CLS).

## Token & colour system

Defined once in `include/base-nav.php` (`:root` block), reaching all 95 pages:

| Token | Value | Use |
|---|---|---|
| `--action` | `#ff7a1a` | Primary CALL CTAs only |
| `--action-label` | `#0d1b6e` (navy) | Text/icon on `--action`. **Never white** (white=2.6:1 fails AA; navy=5.75:1 passes) |
| `--ink` | `#0d1b6e` | Brand navy; secondary-button borders/text |
| `--border-strong` | `#d4d3df` | Sticky-bar top divider |

## CTA inventory — current → target

| Surface | Current | Target | `data-cta-src` |
|---|---|---|---|
| Desktop header | no call button (removed in `7c555f0`) | Orange **"Call now"** pill, top-right, persistent, ≥992px only | `header` |
| Mobile sticky bar | single amber-gradient "CALL: 9899991342", `width:100%` | White bar; **Call (orange, flex 7)** + **Enquire (outline navy, flex 3)**; reserved height | `sticky`, `sticky-enquire` |
| Hero call button | orange fill, **white label (fails AA)** | Recolour label → navy `--action-label` | `hero` |
| Sidebar counsellor card | navy card, amber (`#f59e0b`) number | Keep; align phone number to `--action` | `sidebar` |
| Ranked college-table rows | partial tel: buttons (`ipu-colleges-list.php` 5, `top-law-colleges-ipu.php` 5, `best-btech-colleges-ipu.php` 1; verify `top-btech-colleges-ipu-comparison.php`) | Every ranked row gets an orange call button, navy label | `row-<college>` |

## Component detail

### Desktop header button (Wave 1)
`.nav-call-btn`, `display:none` below 992px. Orange fill, navy label + icon, 999px radius, ~11/18px padding, 14px/800, `flex:0 0 auto`; centred menu gets a right margin so the button never clips. Note: with the current 8-item nav this is tight; the design's eventual nav-cut to 5 items (separate program, out of scope) gives it full room. Compact label "Call now" fits today.

### Mobile sticky bar (Wave 1)
Fixed bottom, `background:#fff`, 1px `--border-strong` top, `0 -2px 10px rgba(5,0,56,.08)` shadow. Flex row, gap 8px:
- Call: `flex:7 1 0`, `width:auto` (override base-head `width:100%`), orange fill, navy label, 999px, min-height 50px, 16px/800.
- Enquire: `flex:3 1 0`, white, 1.5px `--ink` border, navy text, 999px, min-height 50px, 15px/800 → links to on-page enquiry-form anchor.
- `body{padding-bottom:74px}` on mobile to reserve height (no CLS, never covers last element).
- Hidden ≥769px.
**One-per-viewport decision (approved, option a):** the hero orange button and the sticky orange bar may both be visible on mobile — matches the handoff's own mobile home; no scroll-reveal listener. Visual-pass check: never place two orange call buttons directly adjacent/stacked.

### Hero button recolour (Wave 1)
The existing in-hero call button keeps its orange fill but its label/icon change to navy `--action-label` (fixes the pre-existing white-on-orange AA failure).

### College-row call buttons (Wave 1)
On the 4 ranked-table pages, every college row gets a call button styled to `--action` + navy label + `data-cta-src="row-<college-slug>"`. Additive; recolour existing tel: buttons to match. Applied per page (not a shared partial today).

### FAQ phone removal (Wave 2a)
~94 pages carry `9899991342` inside `$faqs` `'answer' =>` strings. That single array renders both the visible FAQ **and** the `FAQPage` JSON-LD, so one edit fixes both (confirm coupling per template at build time). Per occurrence: drop the trailing "Call 9899991342…" sentence, or rephrase to a non-phone soft CTA ("our counsellors can guide you"). Never leave a dangling fragment. This is the one item that edits indexable body + schema content across many pages → its own deploy wave + stop-loss recheck.

### Out-of-hours swap (Wave 2b)
Server-side PHP, timezone **Asia/Kolkata (IST)**. Office open = Monday–Saturday, 09:00–18:59.
- **Open:** Call = primary orange; Enquire = secondary outline. (default sticky-bar state)
- **Closed:** Enquire takes the primary slot with a **navy** primary fill (NOT orange — preserves "orange = calling only", approved); Call demotes to a muted secondary labelled "Opens 9 AM".
Rendered server-side at load (no flash; SEO sees a definite state). Not client-side — a visitor's local clock ≠ IST office hours.

### Attribution (Wave 1, wired first)
A delegated `click` listener (in `base-nav.php`) on any `[data-cta-src]` element fires:
```js
gtag('event', 'cta_call', { cta_src: <placement>, page_path: location.pathname });
```
Enquiry clicks fire `cta_enquire`. Uses the existing GA4 `G-9VS3CTJ8SV` already loaded site-wide — no GTM-console changes. **Build-time check:** confirm `gtag` is defined on prod's (older, non-Phase-2) `base-head.php`; if absent, fall back to `dataLayer.push({event:'cta_call', …})` (GTM `GTM-5GXCN7Z` is present in base-nav). Ships first in Wave 1 so measurement starts on day one. (`?src=` query params are NOT used — they cannot ride a `tel:` call to the phone system.)

## Rollout & verification

Rationale for split (not baseline-first): counselling season is at peak demand now, the visual changes are zero-SEO-risk, and there is no historical tagged baseline to protect — so the lift ships immediately while broad/nuanced items are gated.

- **Wave 1 (single deploy):** tokens + header button + sticky bar + hero recolour + college-row buttons + attribution.
  - Verify: `php -l` on every changed file; local render sweep (php -S) at 375px + 1280px across `IPU-B-Tech-admission-2026.php` (hub), a cutoff page, `ipu-colleges-list.php`, a single-college page, `index.php` — confirm sticky bar renders mobile-only, header button desktop-only, no layout shift, no white-on-orange.
  - Deploy via `deploy.py --files`: `include/base-nav.php` + the 4 college-table pages. **No `base-head.php`.**
  - Post-deploy: curl the changed page types for 200; visual pass; fire a live GA4 test event and confirm receipt.
- **Wave 2a (FAQ removal):** edit ~94 pages, `php -l` + local FAQ render, deploy, then stop-loss recheck vs baselines → revert any watch term dropping >2 positions.
- **Wave 2b (out-of-hours):** implement + test both open and closed states by overriding IST time locally; deploy `base-nav.php`; verify both states.

**Files touched (total):** `include/base-nav.php` (global), 4 college-table pages, ~94 FAQ pages (Wave 2a). Zero `base-head.php` edits across all waves.

## Out of scope (belongs to the broader redesign program)

Course hubs (6), college pages (10), canonical-URL 301 consolidation, nav-cut to 5 items, emoji→line-icons, retiring the blog into Courses/Updates. Those are stop-loss-gated, spec'd separately.
