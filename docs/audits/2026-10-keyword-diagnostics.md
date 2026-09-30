# Keyword-map diagnostics (2026-09-30)

Plan: `docs/superpowers/plans/2026-09-30-ipu-keyword-map-season-2027.md`, Task 2.

## Verified facts (curl and file checks, 2026-09-30)
- Prod 404: `mait-cutoff.php`, `msit-cutoff.php`, `mbs-college-admission.php`, `ipu-colleges-in-dwarka.php`. Prod 200: `llm-admission-ipu.php` (LLM page exists).
- `/?q=<junk>` returns 200 with `rel="canonical" href="https://ipu.co.in/"`: canonicalised, low risk. No action.
- `mba-management-quota-ipu.php`: 8,166 bytes, inbound references from only `IP-University-management-quota-admission-eligibility-criteria.php` and `btech-management-quota-ipu.php` (plus itself). Thin and poorly linked, consistent with GSC position 30.7 (601 impressions).
- `comprehensive-guide-to-bballb-admission-in-ip-university.php`: two `<meta name="description">` tags on one line (line 16); title ends with "Meta"; 14.5 KB. Template bug, consistent with GSC position ~13.

## blog-detail.php legacy URLs
- Top: the BJMC guide (`blog-detail.php?url=guide-to-bjmc-colleges-under-ip-university--top-10-institutions--admission-process--counselling-process-`, 198 clicks, about 9.9k impressions).
- Equivalent page exists: `guide-to-bjmc-colleges-under-ip-university.php`.
- `website_download/htaccess` has no `blog-detail` rule and there is no local `blog-detail.php`; prod serves it some other way (live `.htaccess` has drifted, see memory). Recorded only. A redirect is a separate task that must start from the live `.htaccess`.

## Decisions
1. MBA management-quota page: full rewrite plus inbound links in Task 9.
2. BBA LLB guide: duplicate description fixed in Task 11 (tier C, needs approval); body extended in Task 9.
3. Junk-parameter URLs: no action.
