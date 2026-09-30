# ipu.co.in — Site Improvement Program (Design Spec)

**Date:** 2026-06-10
**Status:** Approved shape; pending spec review → per-phase implementation plans
**Scope owner:** Sumit
**Origin:** Full-site code + content audit (2026-06-10, six parallel audit agents)

---

## 1. Goal

Improve ipu.co.in across four axes the owner named — **website SEO, AI SEO, website speed, and a mobile-first policy** — and fix the engineering/conversion/deploy defects surfaced by the 2026-06-10 audit. The site is a real production lead-generation site (~50K/mo keyword impressions; helpline 9899991342; leads = revenue). Work is delivered as one strategic program split into **five deploy-coherent phases**, each of which gets its own implementation plan at execution time.

## 2. Hard constraints (apply to every phase)

These are non-negotiable guardrails derived from the owner's standing rules. Any task that would violate one is BLOCKED, not worked around.

- **SEO is additive-only.** Never change URL, `<title>`, meta description, canonical, or H1 on any page that currently ranks. Only add content, schema (JSON-LD), internal links, FAQ blocks, and new pages. (`feedback_seo_safety_ipu`)
- **Search Console baseline before SEO ships.** Capture a watch-term baseline before Phase 3/4 deploys; stop-loss revert if any watch term drops > 2 positions. (`feedback_seo_safety_ipu`, `project_ipu_seo_overhaul_20260505`)
- **No visual redesign.** Mobile-first means correctness + speed on the *current* design. The parked site-cohesion / Editorial-Ink redesign is explicitly out of scope. (`project_ipu_blog_redesign_20260509`, `project_ipu-co-in`)
- **All prod deploys are gated.** Implement + verify locally; pause for owner go-ahead before each FTP push. Run the full pre-deploy quality check (lint + visual + curl-verify every changed file) and the localhost crosslink walk for site-wide changes. (`feedback_pre_deploy_quality_check`, `feedback_localhost_crosslink_test`)
- **Deploy safely.** Use the consolidated deployer (built in Phase 0); for any file deletions on prod, use `--delete` with scoped excludes or a per-deploy deletion checklist — never leave orphan files. `git fetch+pull` before any `--sync` (the news GH Action auto-commits). (`feedback_rsync_deploy_stale_files`, `feedback_git_pull_before_sync`, `feedback_full_deploy_recipe_no_shortcuts`)
- **One enquiry form per page.** Never introduce a second form on a page. (`feedback_one_form_per_page`)
- **FTP password rotation is OUT of scope** — owner will handle separately. Do not rotate or touch credentials. (Scripts still get refactored to read from env in Phase 0, but no secret value is changed.)

## 3. Out of scope

- FTP/cPanel password rotation and git-history purge (owner-owned).
- Any visual redesign / re-skin / cohesion work.
- Any change to ranking-page titles/meta/canonical/H1/URLs.
- New marketing/Display/Remarketing decisions (`feedback_no_display_remarketing_decisions`).

## 4. Architecture context (verified in audit)

- Vanilla PHP 8.x, no framework. Live code under `website_download/`; shared components in `website_download/include/`.
- **Live page stack** (96 pages): `base-head.php` + `base-nav.php` + `base-footer.php` — Bootstrap 5 + vanilla `app.js`, no jQuery, deferred CSS bundle, inlined preloader CSS, self-hosted woff2 fonts. This is the modern, healthy stack.
- **Legacy stack** (dead, 1 page `index-old.php` + partial `index-new.php`, both 301'd to `/`): `common-head/header/header2/footer/call-widgets.php`, jQuery 1.12.4 + ~12 plugins, ~12 orphan CSS files, `call.gif`.
- **Forms:** live forms POST to standalone `/sendemail.php` (well-built: validation, phone regex, CRLF guard, honeypot, dedup, dual delivery to email + Google Sheet). `include/form-handler.php` is a near-duplicate used genuinely only by `b-tech-colleges-under-IP-university.php` (self-posts, has `ob_start()`). `course/index.php` self-posts (`action=""`) into `form-handler.php` but is mis-wired (broken — see Phase 1).
- **Deploy:** ~36 one-off `upload_*.py` scripts (FTP via `ftplib`), creds hardcoded; `upload_news.py` is the clean env-based template. GitHub Actions: `news-scrape.yml` (daily 02:30 UTC) + `news-build-deploy.yml`.
- **Helpers that already exist and are underused:** `include/image-helper.php` (`webp_img()`/`responsive_img()` → `<picture>` + WebP + width/height + lazy), `include/helpers/phone-dedup.php` (`lead_record()`).

## 5. Phases

Each phase is a deploy-coherent batch. Each becomes its own implementation plan (`docs/superpowers/plans/`) when executed. Owner gates the deploy at the end of each.

### Phase 0 — Repo & deploy foundation *(execute first; mostly non-visible)*

Makes every later deploy safe and reproducible.

1. **Commit untracked production code.** ~110 untracked files under `website_download/` — 10 PHP (incl. 8 shared includes: `call-widgets.php`, `common-head.php`, `form-code.php`, `form-codecopy.php`, `header.php`, `header2.php`, `news-related-content.php`, `phone.php`) + 2 pages (`ipu-ba-llb-cutoff.php`, `ipu-bba-cutoff.php`) + ~100 assets. Commit so git = prod. (Note: some of these are dead and removed in step 6 — commit first so the deletion is a tracked, revertible change.)
2. **Commit the pending `.gitignore`** (currently modified-uncommitted; protects `.env`, `*.db`, credentials).
3. **Consolidate the deployer.** One env-based `deploy.py` modeled on `upload_news.py`: reads `FTP_HOST/USER/PASS` from env, `--dry-run` default, `--files`/`--manifest`, explicit confirm-gated `--delete`, pre-flight missing-file check, proper exit codes. Archive the ~36 one-offs to `deploy/archive/`. No secret values changed.
4. **Harden the GitHub workflows.** Add a shared `concurrency: { group: ipu-deploy, cancel-in-progress: false }` to both workflows so CI deploys serialize and can't race a manual push. Add an `if: failure()` notification step to both.
5. **Gate the news auto-deploy pipeline.** Add content sanity checks to `build-news.php` (non-empty body, plausible date, category allowlist, min/max length → fail build on violation) and a manual approval gate on `news-build-deploy.yml` (GitHub Environment `required_reviewers`, or scrape→PR instead of push-to-main). Add a `--sync` safety floor: refuse to delete remote news if local `content/news/` is empty/below threshold. (`feedback_n8n_nevererror_trap` analog; `feedback_full_deploy_recipe_no_shortcuts`)
6. **Remove the dead legacy stack** via an explicit deletion checklist (so the consolidated `--delete` removes them from prod, no orphans): `index-old.php`, `index-new.php`, `include/common-head.php`, `header.php`, `header2.php`, `footer.php` (if legacy), `call-widgets.php`, `form-code.php`, `form-codecopy.php`, `ajax-contact.js`, `main.js` (legacy), jQuery 1.12.4 + the plugin set, the 12 orphan CSS files (`bootstrap.min.css`, `bundle.css`, `critical.min.css`*, `style.css`, `style2.css`, `default.css`, `font-awesome.min.css`, `slick.css`, `magnific-popup.css`, `nice-select.css`, `flaticon.css`), and `call.gif`. *Note: `critical.min.css` may be repurposed in Phase 2 — verify before deleting.* Confirm zero live-page references before each removal.
7. **Delete committed `error_log` files** from the repo (`website_download/error_log`, `website_download/course/error_log`) and keep them out of the deploy set; **move the 12 stray root status `.md` docs** into `docs/` or delete the obsolete ones.

**Phase 0 acceptance:** `git status` clean under `website_download/`; one canonical deployer with dry-run default; both workflows serialized + alerting; news pipeline cannot auto-publish unvalidated content; dead stack gone from repo and prod (curl-verify 404 on removed assets); live pages unchanged (curl diff of a sample page before/after = identical rendered output).

### Phase 1 — Conversion bug fixes *(revenue-critical)*

1. **`course/index.php` form.** Rewire to the canonical pattern: replace `$error`/`$success` with `$form_error`/`$form_success` (null-guarded), drop the dead captcha label/input, add the honeypot + `form_loaded_at`, and either add `ob_start()` at top or post to `/sendemail.php` like the sidebar form. Verify lead delivery end-to-end.
2. **Silent lead loss.** In `sendemail.php`, capture the `mail()` return and the Sheets `curl` HTTP status; on failure append the lead via `lead_record()` so nothing is ever lost; optionally trim the 5s curl timeout.
3. **Broken `tel:` links.** Fix `best-btech-colleges-ipu.php:296` and `bba-management-quota-ipu.php:178` (misuse of `include/phone.php` → nested/garbled anchors). Prefer normalizing `phone.php` to emit only the number, then fix call sites.
4. **`thank-you.php` icons.** Replace Font Awesome `<i class="fa …">` with inline SVGs (as the footer already does) so the post-conversion page renders correctly.
5. **Anti-spam.** Set `$_SESSION['form_loaded_at']` inside `sidebar-enquiry.php` and on the homepage so the time-gate actually fires; add a simple per-IP rate limit using the `.private/` flat-file pattern.

**Phase 1 acceptance:** every form path delivers a test lead to both channels; failure path writes to local fallback; both call buttons dial; thank-you icons render; time-gate + rate-limit verified.

### Phase 2 — Speed & mobile-first correctness

1. **Render-blocking CSS.** Async-load `bootstrap5.min.css` (233KB) the way `bundle.min.css` is loaded, with true critical CSS inlined (repurpose/build from `critical.min.css`), or purge unused Bootstrap. Target: remove the single largest first-paint blocker.
2. **Cache suppression.** Drop `Vary: User-Agent`; scope PHP `session_start()` to form-bearing pages only so static HTML responses are cookie-free and edge-cacheable. Smooths the 0.19→0.67s TTFB variance.
3. **Images.** Roll `webp_img()` out site-wide — heroes via `<picture>` + WebP (e.g. B.Tech hero 100KB JPG → 34KB WebP, fix `fetchpriority`), below-fold images `loading="lazy"`, all with width/height for CLS.
4. **Mobile correctness.** Lift `.go-top` above the sticky `.mobile-call-cta` bar on small screens; audit tap-target sizes (≥44px) on nav/CTAs/form; confirm viewport + safe-area handling across archetypes.
5. **Security headers / hardening.** Add HSTS (`max-age=63072000; includeSubDomains; preload`), add CSP report-only first, suppress `X-Powered-By` (`expose_php=Off`) and LiteSpeed server token.

**Phase 2 acceptance:** mobile Lighthouse/PSI improvement (LCP, CLS, render-blocking) measured before/after on homepage + a course hub + counselling page; no layout regressions; HSTS/CSP present; tap targets pass; full localhost crosslink walk before deploy.

### Phase 3 — Website SEO (additive-only)

1. **Schema enrichment** where missing: `FAQPage`, `BreadcrumbList`, `Course`/`EducationalOccupationalProgram`, `EducationalOrganization` — additive JSON-LD only, no edits to existing schema on ranking pages beyond fixing invalid JSON.
2. **Internal linking + FAQ blocks** added to pages lacking them (additive anchors with varied text; no changes to existing ranking elements).
3. **Search Console baseline** captured before any deploy (reuse `seo/scripts/fetch_sc.py`); watch-terms tracked; stop-loss revert if any drops > 2 positions.

**Phase 3 acceptance:** all new JSON-LD validates (Rich Results); no ranking element changed (diff-guard); SC baseline recorded; post-deploy watch scheduled.

### Phase 4 — AI SEO

1. **Crawler access:** allow AI crawlers in `robots.txt` (GPTBot, ClaudeBot, PerplexityBot, Google-Extended, CCBot as desired); enhance `llms.txt`.
2. **Extraction-friendly content:** additive TL;DR blocks, clean Q&A, semantic tables, definition-style answers on key hub/guide pages — additive only.
3. **Authoritativeness:** `sameAs`, author/org markup, consistent NAP (name/address/phone) — additive JSON-LD.

**Phase 4 acceptance:** `robots.txt`/`llms.txt` updated; new structured content validates; no ranking element changed.

## 6. Risks & mitigations

- **SEO regression** → additive-only + SC baseline + stop-loss revert. The single biggest risk; enforced by the hard constraint.
- **Deleting a file that's actually referenced live** (Phase 0 step 6) → grep all live pages for references before each deletion; commit-before-delete so it's revertible; curl a sample page before/after to confirm identical render.
- **Cache/session change breaks a form page** (Phase 2 step 2) → keep sessions on every form-bearing page; test all form paths after.
- **CSP breaks inline GTM/scripts** (Phase 2 step 5) → ship report-only first, observe violations, then enforce.
- **Deploy mistakes** → consolidated dry-run-default deployer + gated pushes + per-phase deletion checklist.

## 7. Success criteria (program-level)

- Zero leads silently lost; all enquiry/call paths verified working on mobile + desktop.
- Measurable mobile CWV improvement (render-blocking removed, LCP image weight down, cache hit-rate up).
- git = prod; one safe deployer; news pipeline cannot auto-publish unvalidated content.
- Dead code + ~1MB legacy assets removed.
- Additive SEO + AI-SEO shipped with zero ranking-element changes and no watch-term drop > 2 positions.

## 8. Execution model

- One phase at a time, each via its own implementation plan in `docs/superpowers/plans/`.
- Subagent-driven where tasks are independent; run tasks back-to-back, pausing only on BLOCKED or at the pre-deploy gate (`feedback_plan_execution_autorun`, `feedback_subagent_driven_dev_no_dual_review`).
- Owner approves each prod deploy.
