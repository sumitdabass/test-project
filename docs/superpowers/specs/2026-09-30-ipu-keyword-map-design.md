# ipu.co.in keyword map: courses and colleges (design spec)

Date: 2026-09-30. Status: DRAFT for Sumit's review. Nothing in this spec has been built or deployed.

## 1. Purpose and inputs

Map target keywords to pages for 8 courses and 11 colleges, using what the data shows, so that the quiet months (now to about Feb 2027) are spent on the right pages before the next counselling season.

Inputs:
- GSC Search export, last 16 months, saved in `seo/baselines/2026-09-30-gsc-export/search/`. `Queries.csv` is the TOP 1,000 queries only, so the long tail is understated. Discover export is empty (no Discover visibility), so it is ignored.
- Google Ads search-terms report (all time) and device report (`~/Downloads/Search terms report.csv`, `Device report.csv`). Only Search-campaign terms are used for keyword decisions ([[feedback_no_display_remarketing_decisions]]).

Entities confirmed by Sumit: USLS = USLLS (University School of Law and Legal Studies); BVP = Bharati Vidyapeeth College of Engineering; Dr Akhilesh = ADGITM (Dr Akhilesh Das Gupta Institute of Technology & Management); MBS = MBS College, Dwarka Sector 9 (formerly MBS School of Planning & Architecture; B.Arch, B.Tech CSE/AI-ML/ECE/Civil, BBA, BCA, B.Com Hons; mbscollege.org).

Correction to an earlier message: dedicated pages DO exist for USICT, USAR, USMS, USLS and ADGITM (`usict-admission.php` etc.). The gap for those colleges is weak rankings and low CTR, not a missing page. The only missing college page is MBS.

## 2. Principles

1. **SEO safety first** ([[feedback_seo_safety_ipu]]). Never change URL, title, meta description, canonical or H1 on a ranking page as part of this programme unless Sumit approves that specific page, with a stop-loss recheck against the baseline.
2. **Three action tiers**, used in every table below:
   - **A: additive.** New body section, FAQ entry, table or schema on an existing page. Title, meta, H1 and URL untouched. Default tier.
   - **B: new page.** Year-free URL ([[feedback_evergreen_urls]]), internal links in from the relevant hub via `$related_pages` (scan both `href=` and `'url' =>`; [[reference_ipu_project_location_deploy]]), sitemap entry, one enquiry form only ([[feedback_one_form_per_page]]).
   - **C: title/meta/H1 change.** Only with explicit per-page approval and a stop-loss recheck at about 2 and 4 weeks (revert any watch term that drops more than 2 positions).
3. **Only sourced facts.** Fees, cutoffs, packages and seats come from the UG Brochure 2026-27, the official IPU admissions site, or college disclosures. If a figure cannot be sourced, the section is not written (the mait/msit/usict "average package" sections depend on this).
4. **No deploy during this spec.** The Phase 2 hold, the CTA Wave 1 gate and the entanglement trap (shipping a file carries every committed edit to it) still apply to each implementation plan.

## 3. Courses

GSC figures are impressions (clicks) and average position across the matching top-1,000 queries. "Ads signal" is conversions from Search campaigns, noting that Ads conversions look inflated (section 6).

| Course | Existing pages (GSC position) | Target keywords | Gap | Action |
|---|---|---|---|---|
| **B.Tech** | `IPU-B-Tech-admission-2026.php` (99k impr, p6.1); `ipu-b-tech-pillar.php`; `ipu-btech-cutoff-2025.php` (p6.2); `btech-management-quota-ipu.php` (p6.2); `top-btech-colleges-delhi.php` (p5.1) | `ipu btech` (2.2k, p9.5); `ipu btech fees` (1.5k, p6.4); `ipu colleges for btech` (1.8k, p5.6); `ipu btech cutoff`; evergreen counselling terms | `ipu btech` is the striking-distance head term; fees have no dedicated section | A: fee section with anchor on the pillar and admission page, internal links to it from college pages. Dated counselling terms stay as they are until next season. |
| **BBA** | `top-bba-colleges-ipu.php`; `ipu-bba-cutoff.php` (p4.9); `comprehensive-guide-to-bba-…php` (86k impr, p6.85); `bba-management-quota-ipu.php` (p8.8) | `ipu bba fees` (1.6k, p7.1); `ggsipu bba fees` (2k, p6.1); `vips bba fees` (3.4k, p4.7); `maims bba fees` (1.4k, p6.6) | Fee queries dominate; the management-quota page is weak | A: college-by-college BBA fees table (sourced) on the guide and on `ipu-fees-structure.php`; A on the management-quota page (FAQ, "admission process"). |
| **B.Com (Hons)** | `bcom-admission-ipu.php` (p4.85); `top-bcom-colleges-ipu.php` (p5.1); `ipu-bcom-cutoff-2025.php` | `ipu bcom hons fees` (729, p4.7); `vips bcom hons fees` (826, p5.5); `ipu bcom fees` (358, p5.2) | Already ranking well; low volume | A only: fees table. No new page. |
| **MBA** | `mba-admission-ip-university.php` (p5.4); `ipu-mba-cutoff-2025.php` (p4.7); `mba-management-quota-ipu.php` (p30.7, 601 impr) | `ipu mba` (544, p8.7); `ipu mba fees` (902, p5.2); `ggsipu mba fees` (712, p6.2); `ipu mba counselling 2026` (620, p4.1); `ipu mba admission process` (Ads converter) | `ipu mba` head term weak; management-quota page effectively unranked | A: fees table and admission-process section on `mba-admission-ip-university.php`; diagnose why the management-quota page sits at p30 (canonical/indexing check) before touching it. |
| **BA LLB** | `ultimate-guide-to-ballb-admission-in-ip-university.php` (p7.0); `ipu-ba-llb-cutoff.php` (p5.0); `ballb-management-quota-ipu.php` (p7.9) | `uslls ba llb fees` (844, p6.2); `ipu ba llb fees` (473, p5); `ipu ba llb counselling 2026` (371, p8.9); `ggsipu ba llb admission 2026` | Counselling term weak | A: fees table, counselling section. |
| **BBA LLB** | `comprehensive-guide-to-bballb-admission-in-ip-university.php` (402 impr, p13) | `ggsipu bba llb fees` (266, p4.8 from another page); `ipu bba llb admission` | The dedicated page barely ranks while another page wins the query | A: fees and admission-process content on the BBA LLB guide; check which page is actually winning and whether the guide is being cannibalised or is under-indexed. |
| **Law 3-year (LLB)** | `law-3-year-admission-ipu.php` (2.99% CTR, p6.0) | `vips 3 year llb fees` (278, p4.2); `ipu llb admission`; `ipu 3 year llb fees` | Healthy | A: fees FAQ only. Protect. |
| **Law (general)** | `IPU-Law-Admission.php` (canonical); `top-law-colleges-ipu.php` (p5.6); `ipu-law-cutoff-2025.php` (2.0% CTR) | `ipu law college` (887, p5); `ipu law colleges` (182, p8.2); `ggsipu law colleges` (434, p4.4); `ipu llm admission 2026` (352, p6.2) | `ipu law colleges` weak; LLM has no dedicated page | B candidate: year-free LLM admission page (confirm none exists); A on top-law page. |

## 4. Colleges

| College | Existing page (GSC position, CTR) | Target keywords | Gap | Action |
|---|---|---|---|---|
| **MAIT** | `mait-admission.php` (p6.45, 0.88%); `mait-delhi-fees-courses-placements.php` (p5.3); `exploring-MAIT-and-MAIMS.php` (52.9k impr, 0.44%, p7.3) | `mait` (10.9k, p7.5); `mait cutoff` (3.7k, p8.9); `mait campus area in acres` (1.9k, p5.8); `mait cse average package` (1.8k, p8.9); `mait direct admission` / `mait management quota` (Ads: 15 conv) | No MAIT cutoff page (verify); informational queries unanswered; conversion wording absent | B: `mait-cutoff.php` (year-free, rolling 3 years per [[project_ipu-btech-cutoff-policy]]). A: campus-area FAQ, sourced package section, management-quota section on the admission page. |
| **MSIT** | `msit-admission.php` (p7.25, 0.86%); `explore-MSIT-and-MSI-janakpuri.php` (22.7k impr, 0.38%, p8.5) | `msit cutoff` (5.8k, p7.9, 0.19% CTR); `msit janakpuri` (1.7k, p9.1); `msit cse average package` (1.6k, p9.6); `msit management quota fees` (Ads: 7 conv at about ₹1) | Cutoff is the biggest gap | B: `msit-cutoff.php`. A: management-quota fees section, package section if sourced. |
| **VIPS** | `vips-admission.php` (p6.4, 1.01%); `vips-pitampura-courses.php` | `vips` (9.1k, p7.1); `vips college fees` (4.3k, p6.6); `vips pitampura` (4k, p7.0); `vips bba fees` (3.4k, p4.7); `vips ipu management quota` (Ads: 5 conv) | Best-covered college; CTR on head terms is low | A: fees FAQ, management-quota section. C candidate only if Sumit wants a CTR test. |
| **BPIT** | `BPIT.php` (33.6k impr, p7.3, 0.81%) | `bpit` (4.6k, p8.8); `bpit fees` (1.1k, p8.9); `bpit fees btech cse` (523, p6.5) | Weak position on head and fee terms; no Ads conversions | A: fee table (sourced), cutoff section, FAQ. |
| **BVP (BVCOE)** | `BVP.php` (17.2k impr, p7.55, 0.74%) | `bvp college` (1.6k, p9.6); `is bharati vidyapeeth under ipu` (178, p3.8); `bvp college fees` | Head term at p9.6 | A: "is it under IPU" answer block near the top (already ranks p3.8 from elsewhere), fees. |
| **USICT** | `usict-admission.php` (62.6k impr, p6.7, 1.02%) | `usict` (5.5k, p8.9); `usict cse average package` (3.1k, p9.3); `usict placement 2026` (656, p5.0); `usict cutoff` (503, p8.6); `usict mca fees` (785, p3.2, 10% CTR); `usict delhi admission process` (Ads) | Head term and package/cutoff queries weak; strong Ads conversion | A: cutoff section (rolling 3 years), package section if sourced, admission-process section. Keep the MCA fees block intact (high CTR). |
| **USMS** | `usms-admission.php` (17.9k impr, p6.4, 1.28%) | `usms ipu` (3.5k, p8.0); `usms` (1.4k, p6.7); `usms mba fees` (624, p4.1); `usms bba fees` (322, p6.2) | `usms ipu` head weak | A: fees table (MBA, BBA, B.Com Hons), FAQ. |
| **USAR** | `usar-admission.php` (37.3k impr, p6.8, 1.15%) | `usar` (9.4k, p9.1, 0.51%); `usar delhi` (841, p6.1); `usar fees structure` (255, p7.6); `usar cutoff 2025` (362, p9.05); `usar placement 2026` (307, p7.5) | Biggest impressions-to-clicks gap among the colleges | A: fees, cutoff and placement sections, FAQ. C candidate (title) is the highest-value CTR test if approved. |
| **USLS / USLLS** | `usls-admission.php` (6.2k impr, p5.8, 1.7%) | `uslls ba llb fees` (844, p6.2); `uslls` and `usls` spellings; `uslls cutoff`; `uslls fees` | Page title uses "USLS (USLLS)" already; GSC queries use the USLLS spelling | A: make both spellings appear naturally in body and FAQ; fees table. |
| **ADGITM** | `adgitm-admission.php` (21.7k impr, p7.2, 0.48%) | `akhilesh das gupta institute` (948 impr, p5.0 in GSC); `adgitm`; `adgitm fees`; `adgitm cutoff`; `dr akhilesh das gupta institute of technology and management` | Low CTR on 21.7k impressions | A: full-name variants and fees/cutoff sections. C candidate (title) to include the full name. |
| **MBS College (Dwarka)** | No dedicated page. Mentioned inside the BBA guide, `ipu-bba-cutoff.php`, `barch-admission-ipu.php`. | `mbs college dwarka`; `mbs college fees`; `mbs college btech`; `mbs school of planning and architecture` (old name); `ipu colleges in dwarka` (hub) | Zero GSC data, one Ads row. A genuinely new page. | B: `mbs-college-admission.php` (college page in the `*-admission.php` pattern) and a Dwarka hub `ipu-colleges-in-dwarka.php`. Needs seats and programme codes from the UG Brochure (`~/Desktop/UG 2026.pdf` was not found; locate it) and fees from the college. |

## 5. Ordering

1. **Cutoff pages (B):** `mait-cutoff.php`, `msit-cutoff.php`. Largest unanswered query volume (9.5k impressions combined).
2. **MBS page and Dwarka hub (B).** Needs sourced data first.
3. **Fees sections (A)** across BBA, B.Com, MBA, BA LLB, BPIT, USMS, USAR. Fee queries are the most common pattern in GSC.
4. **College sections (A)** on USICT, USAR, BPIT, BVP, ADGITM: cutoff, sourced package, admission process, FAQ.
5. **CTR tests (C), approval needed:** USAR, ADGITM, and the `ipu` homepage ([[project_ipu_improvement_program]] flagged it). One page at a time, each with its own stop-loss.
6. **Diagnostics before any edit:** the `mba-management-quota-ipu.php` p30 ranking; why the BBA LLB guide sits at p13; whether an LLM page already exists.

## 6. Google Ads notes (Search network only)

- **Negative-keyword candidates** (zero conversions, portal-seekers): `ipu cet 2024`, `ipu ac nic`, `ipu ac in ipu admissions nic in`, `guru gobind singh indraprastha university counselling form`, `ip university counselling form`, `ggsipu form`, `indraprastha university registration 2026`, `ipu university registration 2026`, `maharaja agrasen institute`. About ₹24k of roughly ₹3.25 lakh Search spend (about 7%) had no conversions.
- **Weak Search campaigns:** `Lead Search 2026 for MBA PDGM LAW` (₹308 per conversion, 2.5k impressions), `Phone Call` (₹37), `Phone Call - Btech` (₹30). `Admission Helpline` carries about ₹4.98 lakh of the ₹10.4 lakh total and 96% of its spend is on mobile.
- **Converting wording not present in organic:** "direct admission", "management quota", "admission process". Fold this wording into the A-tier college sections where the page can genuinely answer it.
- **Data caveat:** Ads conversions look inflated (several campaigns show conversion rates of 39-107%, more conversions than clicks in one case), so they likely count call taps or page views. Do not treat cost per conversion as cost per lead. ipu.co.in has no CRM feed ([[project_ipu_crm_disconnection]]), so real lead counts need another source.
- Many converting terms carry old years (`2023`, `2024`). Year-free wording is safer for any new page.

## 7. Open items before an implementation plan

1. Locate the UG Brochure 2026-27 PDF (memory says `~/Desktop/UG 2026.pdf`, but the file is not there).
2. Confirm with `curl` that no `mait-cutoff`, `msit-cutoff`, LLM admission or MBS page already exists at any URL.
3. Sumit decides which C-tier title tests to approve, if any.
4. Junk-parameter URLs (`?q=`, `?u=`, `index.php?p=`) and legacy `blog-detail.php?url=` pages from the GSC export still need a live `curl` check; that is a separate hygiene task, not part of this keyword map.
5. Stop-loss baseline for any C-tier edit: `seo/baselines/2026-09-30-gsc-export/`.
