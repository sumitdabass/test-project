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
