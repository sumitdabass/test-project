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
