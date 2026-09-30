# Tier-C title tests, go-live 2026-12-01 (pending deploy)

## ADGIPS (approved by Sumit 2026-09-30)
Page: website_download/adgitm-admission.php (URL, canonical, H1 and hero title unchanged)

Before (revert target):
- title: `ADGITM Admission 2026 | IPU B.Tech, MBA, MCA Courses`
- meta description: `ADGITM admission 2026 under IPU. B.Tech, MBA, MCA courses, placements & fees. Call 9899991342 for free admission guidance at ADGITM Delhi.`

After:
- title: `ADGIPS Delhi (formerly ADGITM) Admission 2026 – Fees, Cutoff & Courses`
- meta description: `ADGIPS Delhi (formerly ADGITM) under IPU: B.Tech, BBA, MBA, BA LLB and BBA LLB courses, fees and cutoff. Free admission guidance at 9899991342.`

Why: the college renamed itself Dr. Akhilesh Das Gupta Institute of Professional Studies (source adgips.ac.in/about-adgips); MCA is not offered per its site.

Baseline (GSC 2026-09-30): `adgitm` 2,099 impr, p8.14, CTR 0.19%; page 21,696 impr, p7.16, CTR 0.48%; `dr akhilesh das gupta institute of technology and management` 388 impr, p5.56.
Watch: `python3 seo/scripts/watch_terms.py compare seo/baselines/2026-09-30-keyword-map-watch-terms.csv <new Queries.csv>`; recheck 2026-12-15 and 2026-12-29; decide keep or revert by 2027-01-05. Revert if `adgitm` or the long-name query worsens by more than 2 positions or goes MISSING, unless untouched control pages (`bpit`, `vips`) fell too (seasonality).


## BBA LLB guide (approved by Sumit 2026-09-30, tier C bug fix)
Page: website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php (URL, canonical, H1 unchanged)

Before (revert target):
- title: `Comprehensive Guide to BBALLB Admission in IP University (IPU) : Eligibility, Counselling, Top Colleges, and CLAT Process  Meta`
- meta description #1 (removed): `Discover the comprehensive and professional guide to BBALLB admission in IP University. Learn about eligibility criteria, counselling process, top colleges, and the exclusive use of CLAT for admission. Gain insights into the differences between BBALLB and BALLB courses and explore their future scopes. Get all the essential information for aspiring BBALLB students in IP University.`
- meta description #2 (kept): `BBA LLB IPU Admission 2026: Complete guide to BBA LL.B admission in IP University (GGSIPU). Check CLAT eligibility, counselling process, top BBA LLB colleges and fees for GGSIPU integrated law programs.`

After:
- title: `IPU BBA LLB Admission 2026 – Fees, CLAT Eligibility & Top Colleges`
- one meta description (#2)

Watch: `ggsipu bba llb fees`, `ipu bba llb admission`; recheck 29 Jan and 12 Feb. Revert if either worsens by more than 2 positions unless control pages fell too.


## USAR + homepage (approved by Sumit 2026-09-30; go-live with the 1 Dec batch)
USAR: title/meta only (URL, canonical, H1, hero unchanged). Deviation from plan: "Fees" dropped from the title and meta because usar-admission.php has no sourced fee figure (same rule as the MBS page).

USAR before (revert target):
- title: `USAR IPU Admission 2026 – Automation & Design Dual-Degree Courses`
- meta: `USAR IPU admission 2026. University School of Automation & Design — B.Tech/M.Tech Dual-Degree (AI&DS, AI&ML, IIOT, A&R) at 132 seats each. JEE Main cutoff, placements. Call 9899991342.`
USAR after:
- title: `USAR IPU Delhi 2026 – Admission, Cutoff & Placements`
- meta: `USAR IPU Delhi: dual-degree B.Tech/M.Tech in AI&DS, AI&ML, IIOT and A&R with 132 seats each. Cutoff, placements and admission process. Call 9899991342.`
Baseline: `usar` ~5.5k impr, p~9. Watch `usar`, `usar delhi`; recheck 15 Dec, 29 Dec; decide by 5 Jan.

Homepage before (revert target):
- title: `IPU Admission 2026 | IP University (GGSIPU) Counselling & Management Seat Help`
- meta: `IP University (GGSIPU) admission 2026 — counselling dates, cutoffs, fees, management seat &amp; quota for B.Tech, BBA, Law. Free helpline 9899991342.`
Homepage after:
- title: `IPU (IP University) Admission 2026 – Colleges, Cutoff, Fees & Counselling`
- meta: `IP University (GGSIPU) admission 2026: colleges list, cutoffs, fees, counselling dates and management-quota help for B.Tech, BBA, Law. Free helpline 9899991342.`
Baseline: `ipu` ~707k impr, p8-10, CTR ~0.16-0.2%. Goal is CTR (~0.25%+), not position. Watch `ipu`, `ggsipu`, `ip university`; same rechecks. Revert if a term is flagged unless control pages (`bpit`, `vips`) fell too.
