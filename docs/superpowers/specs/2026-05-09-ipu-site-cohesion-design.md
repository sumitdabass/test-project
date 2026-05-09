# ipu.co.in — Site-Wide Design Cohesion (Conversion-First)

**Date:** 2026-05-09
**Author:** Sumit + Claude (brainstorm)
**Status:** Draft for review
**Pilot piloted:** v4 blog redesign mockup informed this spec but has been parked

---

## 1. Problem

ipu.co.in is **two sites stitched together**. A modern conversion-optimised front — `index.php`, college listicles, utility pages, the news archive — sits next to a legacy theme that owns the highest-intent traffic: course hubs (B.Tech, MBA, Law, BBA, B.Com, MCA, LL.M.), CUET pages, management-quota pages, single-college pages. A visitor who lands from organic search on, say, `mba-admission-ip-university.php` sees a darker `banner-three` slab with a fixed 35 px headline, no above-the-fold form, no obvious phone CTA. The same visitor on `index.php` sees a polished split hero with an inline lead-capture card. Same brand, two stories.

The cost is not aesthetic — it is **conversion**. The pages where ranking and intent are highest are the pages with the weakest call-to-action grammar.

## 2. Goal

Unify the visual language of all 138 page-level `.php` files so every page reads as one site, one brand, one conversion engine — without changing a single word of content.

**Audience:** mobile + desktop (both primary).
**Conversion targets:** phone calls to `9899991342` and form-fills via `form-handler.php`.
**Hard constraint:** **no content edits.** Headlines, body copy, FAQs, lists, intros, JSON-LD, alt-text — all stay byte-identical. Only the chrome around them changes.

## 3. Non-goals

- New pages, new content, new SEO copy.
- Replacing the form backend (`form-handler.php`, `sendemail.php`).
- Replacing GTM, GA4, conversion-tracking, or `phone_click` event wiring.
- A full design-system token library / Tailwind / build step. We stay vanilla PHP + critical-CSS-in-`base-head` + Bootstrap 5 utility classes.
- Component reuse for *every* hand-rolled card on the site. We unify the **conversion-critical** components first; cosmetic card cleanup is a follow-up.
- Migrating away from `bundle.min.css` everywhere. We deprecate it on the modern pages we touch; legacy pages keep it until they're migrated.

## 4. Locked decisions

- **Reference grammar = `index.php`.** Any new shared component must read as a sibling of the index hero, index management-quota cards, and index blog highlights.
- **Brand palette stays.** `#0d1b6e` ink, `#1a3a9c` mid-blue, `#f59e0b` amber accent, `#e65c00` primary CTA orange, `#f8faff` page bg, `#e2e8f0` rule, `#fff3e0` accent-soft, `#e8f0ff` highlight-blue. No new tokens, no Editorial-Ink cream.
- **Inter remains the only font.** No Fraunces / Albert Sans imports added in this pass. The v4 blog mockup's typography is parked; reviving Editorial Ink can happen later as a separate aesthetic layer once cohesion is in.
- **Primary CTA = solid `#e65c00`** with `0 3px 12px rgba(230,92,0,.30)` shadow. Amber `#f59e0b` is **accent only** — gradient endpoints, status dots, ticker highlights, never a full-width button fill.
- **Phone number 9899991342 is hardcoded everywhere.** Already consistent. Don't introduce a config indirection.
- **Forms post to `/sendemail.php` via `form-handler.php`.** Contract preserved exactly: same field names (`name`, `phone`, `email`, `course`, `page_url`, honeypot `website`), same POST shape, same error/success handling.
- **Mobile sticky bottom call CTA stays site-wide** (`base-nav.php` `mobile-call-cta`). Desktop call widget gets retired in favour of a unified in-flow sidebar enquiry on every sub-page.
- **No content edits.** If a redesign would require renaming or rewording anything visible — we don't do that thing.

## 5. The 7 cohesion axes

These are the structural moves, ordered by conversion leverage. Each axis is independently shippable; localhost crosslink testing happens after each.

### Axis 1 — Unified sidebar enquiry component (`include/components/sidebar-enquiry.php`)

**What.** One sidebar block matching the index hero form card visually:
- White card, 16 px radius, border `1px solid var(--rule-soft, #d0d9f0)`, shadow `0 12px 30px rgba(13,27,110,.10)`.
- Heading style copied from index form ("Get Free Admission Guidance" stays text-content-driven; component just supplies wrapping styles).
- Inputs: 12 px padding, 8 px radius, `1px solid #e2e8f0` resting, focus border `#1a3a9c` with `box-shadow 0 0 0 3px rgba(26,58,156,.14)`.
- Submit button: solid `#e65c00`, white text, 700 weight, 14 px padding, 8 px radius, the documented shadow.
- Below the form: a small navy-gradient phone block (matches the `mobile-call-cta` palette) — single tap-to-call target on desktop too.
- Optional below-block: an `accent-soft` pale-amber "popular guides" list (passed in by the host page or rendered from a default site-wide list).

**Where it goes.** Every Grammar B page (course hubs / CUET / mgmt-quota = ~33 files), every single-college page that already has a sidebar (~50+ files), every news article via `news-template.php`. Replaces inline `<div class="col-lg-4">` form blocks and existing `bigin-sidebar-form.php` / `sidebar-cta.php` invocations.

**Why this is Axis 1.** It is the single biggest visual + conversion unifier — 85 pages currently hand-roll this same job 85 different ways. One file fixes all of them.

### Axis 2 — Replace `banner-three` with a form-bearing hero (`include/components/page-hero.php`)

**What.** A page hero modelled on `index.php`'s split hero:
- Left column: existing page H1 + intro paragraph + breadcrumbs + tag chips (whatever the page already has — copy unchanged, just rewrapped from `<section class="banner-area banner-three">…</section>` to `<section class="page-hero">…</section>`).
- Right column on desktop (≥ 992 px): the same sidebar-enquiry from Axis 1, but rendered inline as part of the hero.
- Mobile: stacks; sidebar-enquiry collapses below the H1, mobile sticky bottom call CTA carries the load above-the-fold.
- Background: same gradient as index hero (`linear-gradient(135deg, #0d1b6e 0%, #1a3a9c 60%, #2a5ac8 100%)`) — kills the dead `#0b2c5d` slab.
- Typography: H1 uses `clamp(2rem, 5vw, 3rem)` — stops the 35 px crush on mobile.

**Where it goes.** All ~33 banner-three pages. Each page swaps its `<section class="banner-area banner-three">…</section>` block (and the matching `breadcrumb-area` if present) for one `<?php include 'include/components/page-hero.php'; ?>` with whatever locals it already uses.

**Why.** Above-the-fold on the highest-intent pages currently has zero conversion affordance. This axis turns dead real-estate into a phone + form double-tap zone.

### Axis 3 — Token-driven CSS in `base-head.php`

**What.** Move the inline-style chaos in `index.php` and the duplicated `<style>` blocks in `blog.php`, `news/index.php`, `news-template.php` into CSS custom properties declared once in `base-head.php`'s critical CSS:

```css
:root {
  --ipu-ink: #0d1b6e;
  --ipu-ink-2: #1a3a9c;
  --ipu-amber: #f59e0b;
  --ipu-orange: #e65c00;
  --ipu-orange-hover: #cc5200;
  --ipu-bg: #f8faff;
  --ipu-paper: #fff;
  --ipu-rule: #e2e8f0;
  --ipu-rule-soft: #d0d9f0;
  --ipu-highlight: #e8f0ff;
  --ipu-accent-soft: #fff3e0;
  --ipu-shadow-sm: 0 2px 8px rgba(13,27,110,.06);
  --ipu-shadow-md: 0 8px 24px rgba(13,27,110,.10);
  --ipu-shadow-lg: 0 20px 60px rgba(13,27,110,.18);
  --ipu-cta-shadow: 0 3px 12px rgba(230,92,0,.30);
  --ipu-radius: 12px;
  --ipu-radius-lg: 16px;
}
```

Plus a small set of utility classes (`.ipu-card`, `.ipu-btn-primary`, `.ipu-input`, `.ipu-card-hover`) that wrap the patterns currently inlined on every page. **Pages can keep their inline styles**, but new components and sidebars use the variables.

**Why.** This is the lever that makes Axes 1 and 2 cheap to maintain. Without it we hardcode `#e65c00` 30 more times.

### Axis 4 — Resolve mobile vs. desktop CTA overlap

**What.**
- Keep `mobile-call-cta` (sticky bottom call button on mobile) — it's the mobile conversion winner. Verify it never overlaps inputs on the form pages (Axis 1 sidebar + Axis 2 hero) by adding `scroll-margin-bottom: 80px` to the form anchor.
- Retire `desktop-call-widget` (the floating right-side panel). Its job is taken over by the in-flow sidebar enquiry from Axis 1, which is always present on desktop.
- Single source-of-truth: mobile = bottom bar, desktop = sidebar enquiry. No floating widget fighting the form for attention.

**Why.** Two desktop CTAs in the same right-side zone weaken each other. One-channel-per-surface concentrates click intent.

### Axis 5 — Universal card grammar

**What.** Index's card pattern (rounded 12 px, `1px solid #e2e8f0`, hover-lift `translateY(-3px)` with `box-shadow var(--ipu-shadow-md)`, orange `→` read-more) becomes the universal card via two shared partials:
- `include/components/link-card.php` — for blog highlights, news cards, related guides, course hub tiles.
- `include/components/college-card.php` (already exists) — slightly enhanced to match.

Pages keep their existing card content; only the wrapper styles unify.

**Why.** "Tappable thing" should look the same on every page. Currently each archetype invents its own hover animation, shadow, and read-more arrow.

### Axis 6 — Drop `bundle.min.css` from migrated pages

**What.** Pages that use the new `page-hero` (Axis 2) and unified sidebar (Axis 1) no longer reference `bundle.min.css`. We do this by adding a `$skip_legacy_css = true` local before the `base-head.php` include, and `base-head.php` skips the deferred legacy stylesheet when that flag is set.

Legacy pages (single-college pages we haven't migrated yet) still load `bundle.min.css` to preserve their visual state. No big-bang removal.

**Why.** Migrated pages can't have legacy theme rules silently shadowing the new chrome; also, dropping ~95 KB on ~50+ high-traffic pages improves LCP and Core Web Vitals, which feeds back into the same conversion funnel via better organic ranks.

### Axis 7 — Phone visibility audit on hero

**What.** Confirm `9899991342` is visible without scrolling on every device on every Grammar B → migrated page:
- Desktop: in the hero sidebar phone block + in the nav (already there) ✓.
- Mobile: in the mobile sticky bottom bar (already there) + as a visible tel link in the hero copy (preserve existing one if the page has it; do not add a new one — content is frozen).

This is a **verification axis** more than a build axis. We don't add new copy. We just make sure the existing tel anchors render before the fold on every viewport.

**Why.** A frozen-content rule means we can't write a new "Call 9899991342" line, but we can guarantee the ones that exist are not pushed below the fold by the chrome.

## 6. Architecture

```
website_download/
├── include/
│   ├── base-head.php                    # critical CSS + token vars (Axis 3)
│   ├── base-nav.php                     # unchanged (mobile-call-cta lives here)
│   ├── base-footer.php                  # unchanged
│   └── components/
│       ├── page-hero.php           NEW  # Axis 2
│       ├── sidebar-enquiry.php     NEW  # Axis 1
│       ├── link-card.php           NEW  # Axis 5
│       ├── college-card.php             # Axis 5 (small unify pass)
│       ├── cta-strip.php                # unchanged
│       ├── faq-section.php              # unchanged
│       ├── trust-bar.php                # unchanged
│       ├── breadcrumb-schema.php        # unchanged
│       └── hero-banner.php              # superseded by page-hero on migrated pages
└── (138 page-level .php files — content untouched, only chrome rewrapped)
```

`base-head.php` gains:
- the token block (Axis 3),
- a new optional `$skip_legacy_css` switch (Axis 6),
- nothing else.

`page-hero.php` accepts (all optional) `$hero_kicker`, `$hero_h1`, `$hero_intro`, `$hero_chips`, `$hero_breadcrumbs`, `$hero_show_form` (default `true`). When the host page just wants to provide raw markup (because copy/HTML is already in-page), the include accepts a `$hero_slot_html` string and renders it into the left column instead.

`sidebar-enquiry.php` accepts (all optional) `$enquiry_heading`, `$enquiry_subheading`, `$enquiry_show_phone_block` (default `true`), `$enquiry_show_popular` (default `true`), `$enquiry_popular` (an array of `[label, url]` tuples; falls back to a site-wide default list).

## 7. Localhost test plan (the gate before any FTP)

After every axis lands, the change is verified locally before any deploy:

1. `cd /Users/Sumit/test-project && php -S localhost:8000 -t website_download/` (PHP 8.5; verify `error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED)` is in `index.php` first — required because PHP 8.5 PDO deprecations break HTTP per the existing memory).
2. **Crosslink walk** — open `http://localhost:8000/` and click through:
   - Every nav link in `base-nav.php` (Home / Admissions / Mgmt Quota / Counselling / Colleges / Helpline / News / Blog).
   - Every footer column link in `base-footer.php`.
   - One page from each archetype: course hub (`IPU-B-Tech-admission-2026.php`), CUET (`cuet-btech-admission-ipu.php`), mgmt-quota (`btech-management-quota-ipu.php`), college (`BPIT.php`, `vips-admission.php`), college listicle (`b-tech-colleges-under-IP-university.php`), blog (`blog.php`), news index (`news/`), news article (`news/welcome-news-launched.php`), utility (`ipu-helpline-contact-number.php`).
   - From each archetype: click sidebar enquiry submit (verify form POSTs to `/sendemail.php`, no 404), click breadcrumb links, click 1–2 in-content cross-references, click "Read more" cards.
3. **Mobile-emulator pass** (Chrome DevTools, 375 × 812 + 414 × 896): confirm no horizontal scroll, mobile bottom bar visible and tappable, phone link renders before fold, sidebar form stacks below H1.
4. **PHP lint pass**: `php -l` on every changed file.
5. **Visual regression spot-check**: pre-migration screenshot vs. post-migration screenshot of one page per archetype (at desktop 1366 + mobile 375), confirm content text is byte-identical (diff -u of HTML body excluding chrome).
6. **Curl verify** every changed file returns 200 with expected content fragment (existing memory `feedback_pre_deploy_quality_check.md`).

Only after all six pass do we proceed to FTP per `upload_*.py` script convention.

## 8. Phasing

The 7 axes do not ship as one monolith. Rough phases (concrete sequencing lives in the implementation plan):

- **Phase 1 — Foundations.** Axis 3 (tokens in `base-head`). No visible change. Pure groundwork.
- **Phase 2 — Sidebar component.** Axis 1 (`sidebar-enquiry.php`). First adopters: 2–3 single-college pages we know well (e.g., `BPIT.php`, `vips-admission.php`, `MAIT`). Verify the form POST contract is unchanged on prod. Localhost crosslink walk.
- **Phase 3 — Page hero on a single course hub.** Axis 2 + Axis 7 on `IPU-B-Tech-admission-2026.php` only — this is our pilot. We do not propagate until Sumit signs off on the visual.
- **Phase 4 — Course hub propagation.** The remaining ~32 banner-three pages get the same migration. Sample of each (CUET, mgmt-quota, course hub) reviewed.
- **Phase 5 — College pages.** Single-college pages adopt `sidebar-enquiry.php` (and optionally `page-hero.php` if they already had a hero pattern). One archetype at a time.
- **Phase 6 — Card grammar (Axis 5) + bundle drop (Axis 6) + desktop widget retirement (Axis 4).** These are quick once the foundations are in.

Each phase: localhost crosslink walk → spot-check → commit → FTP push (with the existing `upload_*.py` pattern) → curl-verify on prod → next phase.

## 9. Out of scope (followups for later)

- Editorial-Ink typography layer (Fraunces / Albert Sans) — the v4 mockup. Park.
- Blog.php redesign as a magazine archive (the original brainstorm). Once cohesion is in, blog.php can be a **decorative** layer on top of unified chrome.
- News article redesign beyond the sidebar swap.
- A11y skip-link, heading-level audit, focus-ring pass — needed but separate.
- Search/filter UX upgrade on `blog.php` — separate.
- Component migration of every hand-rolled card on every page (cosmetic). We touch only conversion-critical surfaces in this pass.

## 10. Risks

- **PHP 8.5 deprecation breakage on local `php -S`.** Mitigation: confirm `error_reporting` patch in `index.php` before phase 1; if missing, add it (not a content change).
- **Form POST contract drift.** Mitigation: the new sidebar component renders the *same field names + same form action* as the existing forms. Diffed against `index.php` form before merge.
- **GTM/`phone_click` tracking regression.** Mitigation: `tel:` links remain `<a href="tel:+919899991342">` everywhere; the GTM listener is on document and matches all of them. No script change.
- **`bundle.min.css` removal regressing legacy pages.** Mitigation: opt-in via `$skip_legacy_css` per page. Legacy pages keep loading it.
- **Sticky-rail height across viewports** (the open question from the v4 mockup). Mitigation: page-hero owns the form on desktop ≥ 992 px; below that the form stacks naturally — no `position: sticky` until cohesion is verified, then sticky added as a follow-up if data shows it lifts conversion.
- **Cross-link rot.** Mitigation: localhost crosslink walk is a hard gate per phase per `feedback_localhost_crosslink_test.md`.

## 11. Success criteria

- Every Grammar B page passes the eye-test as a sibling of `index.php` (same gradient, same card rhythm, same CTA color, same form chrome).
- Every page renders the same form contract; one `form-handler.php` flow.
- Mobile: phone visible above fold on every migrated page; sticky bottom bar present and never occluded by other CTAs.
- Desktop: phone + form visible above fold on every migrated page; floating widget no longer fights the sidebar for attention.
- LCP improves on migrated pages (`bundle.min.css` dropped on those).
- **Zero diff in visible page text** between pre- and post-migration HTML.
- All localhost crosslink walks pass before any FTP push.
- No regression in form submissions, GA4 events, or `phone_click` events post-deploy.
