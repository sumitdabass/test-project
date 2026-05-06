# IPU PG Pages Expansion — MBA refresh, MCA refresh, new Law (3-Year) — 2026-05-06

## Goal

Surface PG-brochure-cited content for three GGSIPU postgraduate admission pathways:

1. **MBA** — refresh the existing `mba-admission-ip-university.php` with brochure-exact specifics.
2. **MCA** — refresh the existing `mca-admission-ipu.php` with brochure-exact specifics.
3. **Law (3-Year)** — Programme Code 238 — create a new evergreen page `law-3-year-admission-ipu.php`. No equivalent exists today; the only mention is an exclusion note on the CUET-Law page.

Source: `/Users/Sumit/Desktop/PG 2026 broucher.pdf` (51 MB image PDF, damaged xref — repaired with `qpdf --linearize` to `/tmp/pg_brochure_2026.pdf` and read in 20-page chunks via Read tool, mirroring the UG brochure handling on 2026-05-05).

User-facing terminology: **"Law (3-Year)"** throughout copy, H1, H2, breadcrumbs, meta, and FAQ. Brochure-citation phrasing remains "Programme Code 238 — Bachelor of Laws (3-Year)".

## Why

- 50K+ monthly impression site with established UG-side brochure-cited overhauls (2026-05-05 SEO sweep, 2026-05-06 CUET pages). PG side has not received the same treatment.
- Existing MBA/MCA pages carry generic numbers ("Rs.1.3L/yr", "around Rs.1,30,000") with no brochure citation. Refreshing in place preserves rankings.
- Law (3-Year) is missing entirely. The CUET-Law page already calls out that 3-year LLB excludes CUET (BCI:D:1823/2010 plus Programme Code 238), creating a natural inbound pointer for the new page.

## Approach (selected: Option C from brainstorming)

- **Refresh** the two existing MBA/MCA pages surgically.
- **Build** the Law (3-Year) page from scratch using the CUET-page H2 template, adapted for non-CUET admission via IPU CET LLB.

Rejected alternatives:
- Option A (refresh only) — leaves the Law (3-Year) gap unaddressed.
- Option B (new pages alongside existing MBA/MCA) — splits SEO authority by creating duplicate canonical URLs for the same intent.

## Scope

### Page 1 — MBA refresh (`mba-admission-ip-university.php`)

Surgical edits only. Preserve existing FAQPage schema, hero, breadcrumb, sidebar form, and call-to-action structure. Touch points:

- **H1**: prepend Programme Code (verify code from brochure index).
- **Eligibility section**: replace generic "50%/45%" with brochure-cited wording from PG Brochure Ch 2 (or its PG equivalent), including SC/ST/OBC/PwD relaxation and final-year provisional rule.
- **Entrance accept-list**: list the exact accept-list per the brochure (CAT/CMAT/MAT/IPU CET MBA — confirm vs. brochure, do not assume). Cite chapter.
- **Fees**: replace approximated USMS figure with brochure-exact USS fee from Ch 14 (or PG equivalent). For affiliated colleges, cite "per 6th SFRC notification dated 14.07.2025" (already-established phrasing in `project_ipu_seo_overhaul_20260505.md`).
- **Intake by college**: factual seat counts from brochure Ch 13 (or PG equivalent) for USMS, MAIMS, RDIAS, JIMS.
- **Hidden AI summary**: append `<section id="ai-summary">` block with 4–6 brochure-cited bullets (matches recent SEO overhaul pattern).
- **FAQ updates**: bump 2 of the existing 4–6 FAQ answers to brochure-cited specifics. Preserve the Schema JSON-LD; update the answer text and the visible accordion content together to keep schema parity.
- **Meta title/description**: only edit if a number changes; otherwise leave untouched to avoid CTR regression.
- **`dateModified`** (Article schema if present): bump to 2026-05-06.

### Page 2 — MCA refresh (`mca-admission-ipu.php`)

Surgical edits only. Same touchpoints as MBA, scoped to MCA specifics:

- Verify Programme Code 105 (already on page) against brochure.
- Eligibility: exact graduation rule (BCA / B.Sc with Maths / equivalent) per brochure, plus relaxation rules.
- Entrance accept-list: NIMCET / IPU CET PG mapping per brochure.
- USICT USS fee from Ch 14; affiliated college fee citation (6th SFRC).
- Intake: USICT + 12+ affiliated colleges per Ch 13.
- Hidden AI summary block.
- 1–2 FAQ answers refreshed with brochure citations.

### Page 3 — Law (3-Year) new (`law-3-year-admission-ipu.php`)

Full new page. CUET-page H2 template adapted for non-CUET PG admission:

| H2 | Source |
|---|---|
| Hero — "Law (3-Year) Admission IPU 2026 (Programme Code 238)" | Brochure cover + Ch 1 |
| How Admission Works (IPU CET LLB; CUET not accepted) | Ch 4 + already-cited BCI rule from CUET-Law page |
| Eligibility (graduation + min %; BCI rules; Open School ineligibility per BCI:D:1823/2010) | Ch 2 + CUET-Law page |
| IPU CET LLB Test Details | Ch 4 |
| Top Colleges offering Law (3-Year) — USLLS + affiliated providers | Ch 13 |
| Management Quota | Instruction #21 + Ch 12 |
| Fees (USLLS USS + affiliated 6th SFRC) | Ch 14 |
| Step-by-Step Counselling | Ch 5 |
| FAQ (5–6 brochure-cited Qs) | mixed |
| Hidden AI summary | — |

Schemas:
- `Course` (provider = GGSIPU, courseCode = "238")
- `FAQPage`
- `BreadcrumbList`

Canonical: `https://ipu.co.in/law-3-year-admission-ipu.php`. Helpline 9899991342 in hero, sidebar form, and at least one FAQ answer (matches house style).

### Cross-linking (single-sentence pointers, no buttons — matches recent CUET pattern)

| From → To | Notes |
|---|---|
| `IPU-Law-Admission.php` → new Law (3-Year) page | Hub-level pointer |
| `cuet-law-admission-ipu.php` → new Law (3-Year) page | Already states 3-yr excludes CUET; replace that note with a pointer sentence |
| `comprehensive-guide-to-bballb-admission-in-ip-university.php` → new Law (3-Year) page | "Already a graduate? See the Law (3-Year) path." |
| `ultimate-guide-to-ballb-admission-in-ip-university.php` → new Law (3-Year) page | Same framing |
| `top-law-colleges-ipu.php` → new Law (3-Year) page | Programme listing |
| `mba-admission-ip-university.php` ↔ `top-mba-colleges-ipu.php` | Verify existing reciprocal — no change if present |
| `mca-admission-ipu.php` ↔ `top-mca-colleges-ipu.php` | Verify existing reciprocal — no change if present |

### Site wiring

- **`sitemap.xml`** — append new Law (3-Year) URL (`<priority>0.85</priority>`, `<changefreq>monthly</changefreq>`, `<lastmod>2026-05-06</lastmod>`); bump `lastmod` for refreshed MBA and MCA pages.
- **`llms.txt`** — add new section "## IPU Law (3-Year) Admission" with one-line description + URL; bump line dates for the MBA/MCA entries.
- **`blog.php`** — add Law (3-Year) entry to existing law category if a law category exists; do **not** create a new category. (Verify against current `blogs[]` and `categories[]` arrays during implementation.)
- **No `htaccess` redirects** — all URLs are net-new or unchanged.

### Deploy script

`upload_pg_pages_2026_05_06.py` — Python FTP, follows the convention of `upload_cuet_pages_2026.py` and `upload_seo_overhaul_2026_05_05.py`. Files:

- `mba-admission-ip-university.php` (modified)
- `mca-admission-ipu.php` (modified)
- `law-3-year-admission-ipu.php` (new)
- `IPU-Law-Admission.php` (cross-link only)
- `cuet-law-admission-ipu.php` (cross-link replaces exclusion note)
- `comprehensive-guide-to-bballb-admission-in-ip-university.php` (cross-link only)
- `ultimate-guide-to-ballb-admission-in-ip-university.php` (cross-link only)
- `top-law-colleges-ipu.php` (cross-link only)
- `sitemap.xml`
- `llms.txt`
- `blog.php`

Total: **11 files** (3 content + 5 cross-link + 3 site-wiring).

### Verification (per pre-deploy quality rule from `feedback_pre_deploy_quality_check.md`)

**Pre-deploy (local):**

- `php -l` on every modified PHP file (must be 0/0 errors).
- `xmllint --noout sitemap.xml`.
- `php -S localhost:8765 -t website_download` smoke — load each touched page, confirm HTTP 200, no PHP warnings, breadcrumb + main-img render.
- Visual smoke on the new Law (3-Year) page: hero, FAQ accordion expand/collapse, sidebar form post-target.
- Grep checks: confirm "Programme Code 238" present on new page, "BCI:D:1823/2010" present, "6th SFRC notification" present.

**Post-deploy (live):**

- `curl -sI https://ipu.co.in/law-3-year-admission-ipu.php` → `HTTP/2 200`.
- `curl -s https://ipu.co.in/law-3-year-admission-ipu.php | grep -c "Programme Code 238"` → ≥ 1.
- Same curl check for refreshed MBA/MCA pages: confirm new fee figure / Programme Code present.
- Cross-links: `curl -s <each updated file> | grep -c law-3-year-admission-ipu` → ≥ 1.
- Sitemap: `curl -s https://ipu.co.in/sitemap.xml | grep law-3-year-admission-ipu` → match.
- JSON-LD validity for the new page: extract `<script type="application/ld+json">` blocks, pass through a JSON parser, and confirm `Course`, `FAQPage`, and `BreadcrumbList` types resolve.

**Hostinger PHP-FPM OPcache caveat (per `reference_hostinger_fpm_opcache.md`):** the new `law-3-year-admission-ipu.php` may return 404 in the browser until PHP-FPM is reloaded. If the post-deploy curl returns 404 despite a successful FTP STOR, toggle PHP version once via cPanel MultiPHP Manager to clear the FPM OPcache. Refreshed (existing) MBA/MCA files are not affected.

### Memory update after ship

Single project memory entry: `project_ipu_pg_pages_20260506.md` — page list, deploy script, brochure chapter map (Programme Codes / fee chapter / eligibility chapter / intake chapter), terminology rule ("Law (3-Year)" not "LLB 3-yr"). Mirrors the format of `project_ipu_cuet_pages_20260506.md`.

## Out of scope

- New cutoff pages for MBA / MCA / Law (3-Year). Cutoff pages are year-stamped; they follow a separate workflow (see `project_ipu-btech-cutoff-policy.md` for the rolling-3-years pattern, which applies similarly here).
- Management-quota landing pages for Law (3-Year). Existing `bba-management-quota-ipu.php` / `ballb-management-quota-ipu.php` patterns are not extended in this batch — Law (3-Year) MQ is covered as one H2 inside the new page.
- Fee revisions on `top-mba-colleges-ipu.php`, `top-mca-colleges-ipu.php`, `top-law-colleges-ipu.php` content. These are touched only if the cross-link insertion is needed, and never for fee/numeric edits in this batch.
- GTM UI work (4 pending items from `project_ipu-co-in.md`) — not blocked by this work, separate stream.

## Risks and mitigations

| Risk | Mitigation |
|---|---|
| Existing MBA/MCA rankings dip after refresh | Surgical edits only; meta title/description left untouched unless a number changes. |
| Brochure data not where expected (PG chapter map differs from UG) | First read of repaired PDF is a chapter-map pass — locate Programme Codes / fees / intake / eligibility before drafting any copy. If a chapter is missing or different, surface to user before writing. |
| Cross-link to CUET-Law page replaces a useful exclusion note | Replacement sentence retains the BCI:D:1823/2010 cite plus pointer; net loss is zero. |
| FTP push partial on shared cPanel | Deploy script aborts on any STOR failure (`sys.exit(1)`); follow with curl-verify per file. |
| Damaged PDF causes Read tool to error | Already verified `qpdf --check` succeeds with warnings; will linearize to `/tmp/pg_brochure_2026.pdf` first. |

## Brochure handling note

PG brochure has the same defect pattern as the UG brochure on 2026-05-05 (damaged xref, image-only pages). Repair command:

```
qpdf --linearize "/Users/Sumit/Desktop/PG 2026 broucher.pdf" /tmp/pg_brochure_2026.pdf
```

Then read in 20-page chunks via the Read tool. Do not attempt full-document read in one call.

## Deliverables

- 1 new content page
- 2 surgically refreshed content pages
- 5 cross-link edits
- 3 site-wiring file updates
- 1 deploy script (`upload_pg_pages_2026_05_06.py`)
- 1 project memory entry post-ship
