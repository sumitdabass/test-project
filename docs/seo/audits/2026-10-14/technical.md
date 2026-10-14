# ipu.co.in technical and on-page SEO audit, 14 October 2026

Scope: read-only. Nothing under `website_download/` was edited, no git state changed, nothing deployed.
Method: live crawl of https://ipu.co.in at about 2 requests per second, roughly 330 requests in total, plus a local copy on port 8021 for comparing the undeployed branch.
Companion files in this folder:
- `pages.csv`: 225 rows (one per URL), about 60 columns.
- `keyword_map.csv`: the top 150 queries by impressions, mapped and flagged.
- `redirects_and_rules_proposed.txt`: ready-to-test `.htaccess` snippets (not applied).

Safety classes used below: **A** = safe at any time. **B** = safe but needs a test or visual check. **C** = touches title, meta description, H1, canonical or URL of a page that exists or ranks; needs owner approval and a recheck plan.

---

## 1. Executive summary

1. The site is technically healthy. All 225 crawled URLs return 200, there are 0 redirect chains inside the sitemap, 0 invalid JSON-LD blocks (738 blocks parsed), 0 images without alt, 0 noindex pages by accident, and every page has exactly 1 canonical (self-referencing) except 1 page.
2. **16 live, indexable, internally linked pages are in neither sitemap** (the live one nor the local fixed one). They earned **1,829 clicks in 16 months** (`ipu-ba-llb-cutoff.php` 707, `cuet-bca-admission-ipu.php` 231, `bvicam-admission.php` 185 and 13 more). These are separate from the 8 pages you already know are missing. Class A.
3. **29 more pages still have two H1s even on the local fixed branch**, among them the three highest-click pages: `ipu-colleges-list.php` (8,834 clicks), `top-ipu-colleges.php` (4,480) and `ipu-counselling.php` (4,013). Live total is 59 double-H1 pages; the local branch fixes 30. Class C.
4. **29 pages that earned at least 150 clicks (23,245 clicks, about 34% of the 68,062 total) have 6 or fewer internal links.** `top-ipu-colleges.php` and `ipu-counselling.php` have 4 each, while the nav sends all link weight to `GGSIPU-counselling-for-B-Tech-admission.php`. Two cutoff pages have 0 inlinks (`ipu-ba-economics-cutoff-2025.php` earned 308 clicks). Class A, probably the best effort-to-gain ratio in this audit.
5. The brand head terms carry 1.05M of the 1.85M impressions in the top-1,000 export, at pos 8.5 to 9.8 and CTR 0.15% to 0.31%. This is a navigational ceiling set by the official ipu.ac.in. Realistic gain is small (about +1,100 clicks per 16 months if the `ipu` CTR doubled), and every lever is class C (the homepage test is already held for 1 Dec).
6. **No dedicated page serves "ipu rank list"**: 18,872 impressions at pos 5.3 with 0.72% CTR, plus "ipu ranklist" 1,716. A year-free new page (class B) is the cleanest unmet-intent gap.
7. Hygiene leaks: 28 junk `?q=/?u=/?p=` URLs return the full homepage with 200; 6 legacy `/blog-detail.php?url=` URLs return 404 (202 clicks, one 198-click URL with a live replacement); `/ipu-counselling.php/` returns 200; mixed-case slugs 404 when lower-cased. All class A, all fixable with `.htaccess` rules.
8. **Deploy risk:** the repo `.htaccess` has 12 news 301 rules, live has at least 34 (verified by response, for example `/news/ipu-physical-reporting-offline-courses.php`, which had 287 clicks, redirects). A blind `.htaccess` upload would remove 22 live redirects. Pull the live file first.
9. Schema and facts need cleaning: 17 pages carry FAQPage markup whose questions are not on the page; 5 pages have two BreadcrumbLists; three conflicting "affiliated colleges" counts (60+, 80+, 110+); the counselling fee is Rs 1,500 on one page and Rs 1,000 in another page's meta description.
10. What I could not verify: Core Web Vitals (no Lighthouse or CrUX access), the cost of the tags inside GTM, which page Google actually serves for each query (GSC export has no query-by-page data), and the live `.htaccess` content (403). See section 7.

---

## 2. Findings table (sorted by estimated impact, then effort)

Impact: H = plausibly 500 or more clicks per 16 months or a risk to ranking; M = 100 to 500; L = under 100 or hygiene. Effort: S = under 2 hours, M = half a day to a day, L = more.

| # | Finding and evidence | Affected URLs | Recommended fix | Class | How to verify |
|---|---|---|---|---|---|
| 1 | **Pages missing from the sitemap.** 16 live 200 pages, linked from other pages, absent from both the live and the local fixed sitemap. 1,829 clicks and about 111,000 impressions in GSC. `top-btech-colleges-ipu-comparison.php` is one of them and also has no canonical. | 16: `ipu-ba-llb-cutoff.php` (707 clicks), `cuet-bca-admission-ipu.php` (231), `bvicam-admission.php` (185), `mtech-admission-ipu.php` (132), `usct-admission.php` (102), `cuet-bjmc-admission-ipu.php` (95), `cuet-ba-economics-admission-ipu.php` (92), `top-btech-colleges-ipu-comparison.php` (72), `med-admission-ipu.php` (63), `cuet-ba-english-admission-ipu.php` (54), `dspsr-admission.php` (49), MABS PGDM page (16), `tiips-admission.php`, `sbit-admission.php`, `dist-admission.php`, `tribhuvan-admission.php`. Plus the 8 known pages (`cuet-admission-ipu`, `cuet-btech/bba/bcom/law-admission-ipu`, `economics-admission-ip-university`, `law-3-year-admission-ipu`, `mca-admission-ipu`), which the live sitemap still lacks. | Add all 24 to `sitemap.xml` (keep `top-btech-colleges-ipu-comparison.php` out until finding 12 is decided). Add the sitemap step to whatever script appends news. | A | Fetch `/sitemap.xml`, count `<loc>` (live today: 200; expect 223 plus new news). GSC > Sitemaps > Discovered URLs. |
| 2 | **Double H1 beyond the known 30.** Live: 59 pages with 2 H1s. The local branch fixes 30 (25 college-template pages in the sitemap plus `usct`, `dist`, `sbit`, `tiips`, `tribhuvan`). The other 29 keep a hero-banner H1 plus an in-content H1 ("... Complete Guide"); two of them (`med-admission-ipu.php`, `mtech-admission-ipu.php`) are not in the sitemap. | 29 still double locally, including `ipu-colleges-list.php` (8,834 clicks), `top-ipu-colleges.php` (4,480), `ipu-counselling.php` (4,013), `ipu-fees-structure.php` (1,500), `top-bcom/law/mca/bba/mba/bca-colleges-ipu.php`, `top-btech-colleges-delhi.php`, `mait-delhi-fees-courses-placements.php`, `maims-delhi-fees-courses.php`, `usict/usar/usls/usms/mait/msit-admission.php`, `bcom/bca/mca/ba-english/ba-economics/barch/llm-admission-ipu.php`, `college-admission-delhi.php`, `med/mtech-admission-ipu.php`. Full list in `pages.csv` (`h1_count` = 2). | Keep the hero H1 text unchanged and demote the in-content one to H2, wave by wave: first the 2 lowest-click pages (`med`, `mtech`) plus 5 to 8 of the smallest college pages, then the rest of the mid-click pages, the top 3 last and only after the 25-page deploy shows no drop. | **C** | `scripts/seo_verify.py` (h1 count must be 1); GSC position for the page's watch terms at 2 and 4 weeks against `seo/baselines/2026-09-30-gsc-export/`. |
| 3 | **Internal-link starvation on pages that earn clicks.** 29 pages with 150 or more clicks have 6 or fewer inlinks (23,245 clicks). Nav and footer link only `GGSIPU-counselling-for-B-Tech-admission.php` for "Counselling" and only `ipu-colleges-list.php` for "Colleges". Orphans: `ipu-ba-economics-cutoff-2025.php` (308 clicks, 0 inlinks), `ipu-mba-cutoff-2025.php` (0). | `top-ipu-colleges.php` 4 inlinks (4,480 clicks), `ipu-counselling.php` 4 (4,013), `ipu-btech-via-cuet.php` 3 (1,562), `top-bcom/law/mca/bca/bba/mba-colleges-ipu.php` 3 to 6, `mait-delhi-fees-courses-placements.php` 2 (994), `maims-delhi-fees-courses.php` 2 (606), `ipu-law-cutoff-2025.php` 1, `ipu-bjmc-cutoff-2025.php` 1, `mait-cutoff.php` 4, `msit-cutoff.php` 4, `ipu-colleges-in-dwarka.php` 3 | Add contextual links (descriptive anchors, not "read more") from the hubs: `ipu-colleges-list.php` to `top-ipu-colleges.php` and every `top-*-colleges` page; `GGSIPU-counselling-...` and the B.Tech pages to `ipu-counselling.php`; cutoff hub (`ipu-cutoff-analysis.php`) to the 5 course cutoff pages; each college admission page to its `*-cutoff.php`. Use the existing `$related_pages` mechanism. | A | Re-run the crawl; target at least 12 inlinks for each of the 29 pages. Compare average position at 4 weeks. |
| 4 | **Junk parameter URLs return 200 with the full homepage.** Probe: `/?q=1929-ford-model-a-coupe-gg-je1mMRS8`, `/?u=...tt-bzqdPkIa`, `/index.php?p=594877`, `/?search_mode=basic` all 200, 98.6 KB, canonical `/`. 28 such URLs in GSC (574 impressions, 4 clicks). Parameter value is not reflected (tested with an HTML payload: not echoed). | all `/?q=`, `/?u=`, `/index.php?p=`, `/?search_mode=`, `index.php?products/..` | `.htaccess` 301 to `/` for these parameter names only (snippet 2 in `redirects_and_rules_proposed.txt`). Leave utm, gclid, fbclid alone. | A | `curl -sI "https://ipu.co.in/?q=x"` gives 301 to `/`; `curl -sI "https://ipu.co.in/?utm_source=x"` still 200. |
| 5 | **Legacy `/blog-detail.php?url=` now 404.** 6 URLs, 202 clicks, 10,185 impressions. One URL: 198 clicks, pos 16.9. | the 6 URLs in the proposal file | 301 map in the proposal file: the 198-click URL goes to `/guide-to-bjmc-colleges-under-ip-university.php` (title "Top BJMC Colleges in IPU 2026"); the BBA, B.Tech and counselling slugs map to the matching live pages; 2 slugs with no equivalent get 410. | A | `curl -sI` each; GSC Pages report in 4 weeks shows the old URLs dropping and the target gaining. |
| 6 | **Repo `.htaccess` stale against live.** Repo `.htaccess` has 12 `^news/` rules; `website_download/htaccess` (dated 30 Sep) has 34; live responds as if it has at least the 34 (`/news/verify-heis-avoid-fake-universities.php` and `/news/ipu-physical-reporting-offline-courses.php` both 301). The live `/htaccess` is a different 328-byte file (old). Cannot read live `.htaccess` (403). | 1 file | Before any deploy that includes `.htaccess`, fetch the live file over FTP and merge; make the repo copy the source of truth afterwards. | A | `diff` live vs repo; count `^news/` rules. |
| 7 | **FAQPage schema without matching visible FAQ.** 17 pages: schema questions not found in visible text (match test on the first 40 characters, spot-checked on 3 pages). Examples: `mba-admission-ip-university.php` 0/6 visible, `btech-management-quota-ipu.php` 0/4, `ipu-admission-guide.php` 1/8, `BVP.php` 0/5, `explore-MSIT-and-MSI-janakpuri.php` 0/6, `ipu-btech-via-jee-main.php` 0/4. Marking up content that users cannot see is against Google's structured-data guidelines. | 17 (list in `pages.csv`, `faq_mismatch` = 1) | Either render the FAQ visibly (best: it also feeds long-tail) or remove the JSON-LD. Do not change existing title or meta. | A | `scripts/seo_verify.py` plus a visible-text check; Rich Results Test on 3 pages. |
| 8 | **Conflicting facts.** (a) Affiliated colleges: "60+" on 48 pages including the homepage, "80+" in the `ipu-colleges-list.php` title and body, "110+" in `top-ipu-colleges.php`. (b) Counselling fee: `ipu-counselling.php` and `ipu-fees-structure.php` say Rs 1,500 (Gen/OBC) and Rs 750 (SC/ST); the meta description of `GGSIPU-counselling-for-B-Tech-admission.php` says "Rs. 1,000 fee". | 50+ pages for (a); 1 meta description for (b) | Pick one sourced figure per fact and apply it in body text (A). The meta description fix is **C**, schedule it with the 29 Dec window or bundle it with an approved title test. | A for body, **C** for the meta | grep the crawl for each string; `pages.csv` text columns. |
| 9 | **Incomplete Open Graph.** 35 non-news pages have `og:image` (from the fallback in `base-head.php`) but no `og:title`, `og:description` or `og:url`. Includes `GGSIPU-counselling-for-B-Tech-admission.php`, `IPU-B-Tech-admission-2026.php`, `ipu-admission-guide.php`, `IPU-Law-Admission.php`, all 8 CUET pages. One og:image is a **404**: `b-tech-colleges-under-IP-university.php` points to `/assets/images/btech-ipu-colleges.jpg` (file does not exist; page has 369 clicks). | 35 pages (og), 1 page (404 image) | Emit og:title/description/url from the existing title and meta variables in `base-head.php` (does not change the title or meta tags themselves). Point the broken og:image at an existing file. | A | `pages.csv` columns `og_title`, `og_url`; `curl -I` the image. |
| 10 | **Images without dimensions.** 311 of 345 `<img>` tags (90%) have no width and height, on 99 pages; CLS risk. `fetchpriority="high"` exists on only 24 pages; 0 pages preload the hero. All images do have alt text (0 missing). | 99 pages | Add width/height (the build helper in `include/image-helper.php` knows the sizes); add `fetchpriority="high"` to the first hero image on pages where it is the LCP element. | A (dimensions), B (priority, check LCP first) | Lighthouse CLS on 5 template pages before and after. |
| 11 | **Render-blocking CSS.** One blocking stylesheet on all 225 pages: `bootstrap5.min.css` 232,803 bytes (31,137 gzip). `bundle.min.css` is deferred correctly (97,226 bytes, 13,504 gzip), fonts use the preload pattern, JS is deferred. Inline critical CSS is about 19 KB repeated in every HTML. | all 225 | Purge unused Bootstrap (or ship only grid, utilities and components actually used) and inline the above-the-fold part; visual-diff 10 templates. Cannot quantify the CWV gain without Lighthouse. | B | Lighthouse mobile before/after on `/`, `ipu-colleges-list.php`, one college page, one news page. |
| 12 | **Thin and duplicate comparison page.** `top-btech-colleges-ipu-comparison.php`: 485 words, **no canonical**, not in sitemap, 1 inlink, same H1 ("Best B.Tech Colleges under IP University...") as `b-tech-colleges-under-IP-university.php` and `best-btech-colleges-ipu.php` (3 pages, one intent). GSC: 72 clicks, 9,591 impressions, pos 5.2. | 3 pages | Decide: 301 it into `best-btech-colleges-ipu.php`, or add a canonical, or expand and differentiate. Any option touches URL or canonical. | **C** | GSC for this URL and the two siblings at 2 and 4 weeks. |
| 13 | **Cannibalisation candidates (inferred, not measured).** `ipu-counselling.php` (title "IPU Counselling 2026 – Dates, Registration, Fees, Process & Last Date") and `GGSIPU-counselling-for-B-Tech-admission.php` ("IPU Counselling 2026 \| GGSIPU B.Tech Dates, Registration & Last Date") both lead with the same phrase and both sit in the top 4 pages by clicks (4,013 and 4,846). The B.Tech date queries are also covered by `IPU-B-Tech-admission-2026.php` and `ipu-btech-via-jee-main.php`. MAIT has 4 pages, MAIMS 3. | see `keyword_map.csv` | Do not merge or retitle. Give each page one role in body copy and links: `ipu-counselling.php` = all programmes and fee/dates; `GGSIPU-...` = B.Tech steps. Add the contextual links from finding 3. | A | GSC query-by-page for "ipu counselling" (the export lacks it, pull it first). |
| 14 | **Unsourced figures on top pages.** Heuristic: 9 non-news pages state LPA/placement figures with no source cue ("source", "per the institute", brochure, SFRC, NIRF): `top-ipu-colleges.php` (30 LPA mentions, 4,480 clicks), `top-btech-colleges-delhi.php` (16), `top-bca/bcom/mca-colleges-ipu.php` (24 to 28 each), `meri`, `kasturi-ram`, `sgtbimit`, `maharaja-...PGDM`. Most other pages cite the UG Brochure or SFRC notification properly. | 9 | Add the source (college disclosure or brochure chapter) or soften to "reported by the college". | A | Re-run the same heuristic; manual read of 3 pages. |
| 15 | **Schema gaps.** 16 pages lack BreadcrumbList (14 non-news, e.g. `ipu-choice-filling-strategy.php`, `ipu-btech-cutoff-analysis.php`, 3 management-quota pages); 5 pages have two BreadcrumbList blocks (`ipu-bjmc-cutoff-2025.php`, `ipu-bcom-cutoff-2025.php`, `ipu-ba-economics-cutoff-2025.php`, `ipu-mba-cutoff-2025.php`, `ipu-cet-cutoff-2025.php`); 88 of 90 `Article` blocks have no `image`, 16 have no `datePublished`/`dateModified` (all the CUET pages, `IPU-B-Tech-admission-2026.php`, `ipu-b-tech-pillar.php`). | 16 / 5 / 88 | Add or dedupe. Dates must reflect real content changes. | A | Rich Results Test, `pages.csv` (`breadcrumb_count`). |
| 16 | **Duplicate-URL variants return 200 or 404.** `/ipu-counselling.php/` 200 (canonical OK but a second URL); `/news/index.php` 200 with canonical `/news/` (155 KB duplicate; title and H1 identical to `/news/`); `/ipu-counselling` and `/IPU-COUNSELLING.PHP` and `/ipu-b-tech-admission-2026.php` 404. Mixed-case slugs: `IPU-B-Tech-admission-2026.php`, `GGSIPU-counselling-for-B-Tech-admission.php`, `IPU-Law-Admission.php`, `BPIT.php`, `BVP.php`, `b-tech-colleges-under-IP-university.php`, `exploring-MAIT-and-MAIMS.php`, `explore-MSIT-and-MSI-janakpuri.php`. www and http variants are fine (single-hop 301). | ~10 | Snippets 3 and 4 in the proposal file; 301 `/news/index.php` to `/news/`. | A | `curl -sI` each variant. |
| 17 | **Internal links to redirecting URLs and a 2-hop chain.** 10 links to `/IPU-B-Tech-admission-2025.php` (301) from news pages; 4 links to `/bharati-vidyapeeth-engineering-college-delhi-admission-courses-placement.php` (301). `/ipu-law-admission-2026.php` goes 301 to `/IPU-Law-Admission-2026.php`, which 301s again to `/IPU-Law-Admission.php`. No broken (404) internal link found among 24 non-sitemap link targets plus all sitemap URLs. | 14 links, 1 chain | Update the 14 hrefs to final targets (the news template is the source of the first 10); collapse the chain in `.htaccess`. | A | Re-crawl: 0 internal links with a 3xx status. |
| 18 | **Sitemap hygiene.** `lastmod` looks stale: 87 of 103 pages with a `lastmod` have a server file date more than 30 days later (26 URLs share `2026-02-15`, 24 share `2026-04-06`); `IPU-B-Tech-admission-2026.php` says 2026-02-15 but was uploaded 2026-09-30. 97 news URLs have no priority, 40 non-news have priority of 0.90 or more (Google ignores priority and changefreq). The 5 newest hand-added entries use a different indent. | 1 file | Generate `lastmod` from the last real content change; drop priority and changefreq or leave them. Do not bump `lastmod` for cosmetic edits. | A | Spot-check 10 URLs. |
| 19 | **Custom 404.** 404 status is correct, but the body is the 1,251-byte server default (no navigation, no links), and it is `no-store`. 198+ clicks historically landed on dead legacy URLs. | site-wide | Custom `404.php` with nav, search links to the 6 main hubs and `noindex`; keep status 404. | A | `curl -sI` for status; view in browser. |
| 20 | **Snippet length.** Titles over 60 characters on 107 of 127 non-news pages (median 69, 53 over 70); news titles median 79 characters ("... — IPU News" suffix); descriptions over 160 on 46 non-news pages. Google rewrites some titles anyway. | 201 titles | **No change to existing pages** (SEO-safety rule). Apply the length targets (title 55 to 62, description 140 to 158) to new pages and to the news template for future posts only. | A (new pages), **C** (existing) | n/a |
| 21 | **Heading outline.** 118 pages skip a level (h2 to h4), 6 skip h1 to h3. Not a ranking factor of note. | 124 | Fix opportunistically when a page is edited anyway. | A | `pages.csv` `heading_issues`. |
| 22 | **Residual `&amp;amp;` in body text on 6 pages** after the local branch fix (live: 31 pages): `comprehensive-guide-to-bballb-admission-in-ip-university.php` (3), `hmr-admission.php` (3), `iitm-admission.php`, `cuet-btech-admission-ipu.php` (2), `cuet-bba-admission-ipu.php`, `law-3-year-admission-ipu.php` (2). Body text only, so not title, meta or H1. | 6 | Double-encoded ampersand: change to `&amp;` in source. | A | `grep -c '&amp;amp;'` on the rendered HTML. |
| 23 | **Thin hubs.** `ipu-b-tech-pillar.php` 471 words (linked from the footer of every page, 224 inlinks), `ipu-colleges-in-dwarka.php` 522, `ipu-btech-cutoff-analysis.php` 548, `economics-admission-ip-university.php` 595, `bba/btech-management-quota-ipu.php` about 586. 40 of 98 news pages are under 500 words (normal for notices). | ~14 | Expand the pillar first, using only sourced figures from the UG Brochure. | A | Word count in `pages.csv`. |
| 24 | **Server file exposure and headers.** All required security headers present (HSTS 1 year, nosniff, SAMEORIGIN, Referrer-Policy, Permissions-Policy). No CSP (not an SEO issue). `X-XSS-Protection` is obsolete. `.env`, `.git/config`, `include/` return 403. `/htaccess` (no dot) returns 200 with a 328-byte old file; the 11 KB `htaccess` in the repo must never be uploaded to the root. `llms.txt` (55 KB) is served without a charset, so the em dash shows as `â` mojibake. | n/a | Delete or block `/htaccess` on the server; add `AddDefaultCharset UTF-8` or a charset header for `.txt`. | A | `curl -sI`. |
| 25 | **robots.txt.** Valid and permissive; sitemap declared. `Crawl-delay: 0` and `Request-rate: 0` are non-standard and ignored by Google. `Disallow: /thank-you.php` plus `noindex` on that page is harmless (the page is unlinked). `Disallow` lines for two files that now 404 are redundant. | 1 file | Optional tidy only. | A | n/a |

Notes on things checked and found fine: HTTPS and www handling (single hop), gzip on all HTML/CSS/JS, HTML `Cache-Control: public, max-age=1800`, static assets one year immutable, CSS/JS 30 days, HTTP/3 advertised, median response header time 0.07 s (one outlier: `top-bba-colleges-ipu.php` 2.47 s on one request, retest before acting), hreflang not applicable, only third-party hosts are Google Fonts and GTM.

---

## 3. Cross-cutting detail

**Sitemap versus reality.**
- The live sitemap has 200 URLs, all return 200 and none redirect.
- It lacks the 8 pages fixed in the local sitemap (194 URLs) and the 16 pages in finding 1.
- It contains 14 news URLs that the local copy lacks, so the local news folder is stale (3 news files locally against 97 news URLs in the live sitemap). Run `git pull` before any `--sync`, as already noted in the project rules.

**Parameters, hosts and variants (live probe results).**

| Probe | Result |
|---|---|
| `http://ipu.co.in/`, `http://www…`, `https://www…` | 301 to `https://ipu.co.in/` in one hop |
| `https://www.ipu.co.in/index.php` | 301 to `https://ipu.co.in/index.php` (200, canonical `/`) |
| `/ipu-counselling.php?utm_source=x` | 200, canonical self (fine) |
| `/ipu-counselling.php/` | 200 (duplicate) |
| `/news` | 301 to `/news/` |
| `/nonexistent-page-xyz` | 404 default body |
| `/api/`, `/assets/images.zip`, `/AI_AGENT_README.md`, `/seo-report-march-2026.html` | 404 |
| `/include/base-head.php`, `/.env`, `/.git/config`, `/.htaccess`, `/cgi-bin/` | 403 |
| `/sendemail.php` | 302 to `/` |
| `/favicon.ico` | 404 (the page declares `/assets/images/favicon.ico`; browsers still request `/favicon.ico`) |

GSC still lists 33 `www.` URLs (30,380 impressions over 16 months; `https://www.ipu.co.in/` alone 24,885 impressions at pos 11.7). They are already 301s, so this should shrink by itself.

**Largest assets and weights.**
- The 15 largest assets referenced from pages (all under 213 KB; none is oversized):

| Asset | Bytes | Pages using it |
|---|---|---|
| `ipu-b-tech-admission-2025.jpg` | 212,352 | 1 |
| `exploring-MAIT-and-MAIMS.jpg` | 200,436 | 3 |
| `explore-MSIT-and-MSI-janakpuri.jpg` | 189,452 | 3 |
| `blog-ipu-mba.jpg` | 139,758 | 1 |
| `breadcrumbs.jpg` | 127,022 | 1 |
| `explore-MSIT-and-MSI-janakpuri.webp` | 108,562 | 1 |
| `IPU-BJMC-Admission.jpg` | 107,387 | 2 |
| `exploring-MAIT-and-MAIMS.webp` | 107,276 | 2 |
| `IP-University-b-tech-admission.jpg` | 100,372 | 208 (og:image fallback) |
| `ipu-bballb.jpg` | 95,933 | 3 |
| `IPU-Law-Admission-2025.jpg` | 93,207 | 4 |
| `economics-admission-2025.jpg` | 92,779 | 2 |
| `blog-1.jpg` | 92,381 | 1 |
| `vips-pitampura-courses.jpg` | 85,706 | 2 |
| `comprehensive-guide-to-bba-…-institutions.jpg` | 83,381 | 1 |

- The 42 unique images referenced total 2.77 MB.
- WebP versions exist for nearly all of them. The five news category JPGs (39 to 50 KB each, used on 96 news pages) have no WebP.
- The repo carries about 10 MB of unused web-font files (`fa-*` Font Awesome; `bundle.min.css` references only Flaticon) and a 4.2 MB `assets/images.zip`. Neither is served (the zip 404s), so there is no live cost.

**Page weight.**
- Median HTML is 57 KB (gzip 12 to 15 KB).
- Heaviest: `ipu-helpline-contact-number.php` 180 KB, of which about 131 KB is whitespace runs (compresses to 14.8 KB gzip, harmless), `/news/` 155 KB (every news item on one page, no pagination), `ipu-bba-cutoff.php` 125 KB, homepage 98 KB.

**Third-party cost.**
- Only GTM (`GTM-5GXCN7Z`, async) and Google Fonts load from other hosts.
- The GTM container carries GA4, Google Ads, Meta Pixel and Clarity (per the code comment), so real third-party cost sits inside GTM and was not measured.

**Cache headers.**
- HTML `public, max-age=1800` plus `Last-Modified`, `Vary: Accept-Encoding,User-Agent`. The `User-Agent` in `Vary` splits caches by device and may be needless.

---

## 4. Keyword and page mapping (from `keyword_map.csv`)

Limit: `Queries.csv` is the top 1,000 queries only (15,722 clicks, 1.85M impressions, about 23% of clicks), and the export has no query-by-page data. "Page that ranks" is therefore inferred from titles and intent, not measured. Pull GSC Performance filtered by query, grouped by page, for the 10 queries below before any C-class decision. Figures are 16-month aggregates that blend the season with the quiet months.

**Striking distance (pos 8 to 20, 500 or more impressions): 46 queries, 1.26M impressions, 2,587 clicks.** The actionable ones, excluding brand head terms:

| Query (impr, pos, CTR) | Target page | Concrete change | Class |
|---|---|---|---|
| bvicam (4,923, 8.1, 0.10%) | `bvicam-admission.php` | Add to sitemap; 4 inlinks to 12+; add an "Is BVICAM under IPU, fees, programme codes" block. CTR test later. | A, then C |
| gtb4cec (4,753, 8.2, 0.21%), gnit ipu, tips dwarka, adgitm, ideal institute | each college page | Add the full institution name and the "fees, cutoff, courses" answer block near the top; link from the 3 matching `top-*` pages. These pages also have the double-H1 issue. | A (body), C (H1) |
| bpit (4,607, 8.8), bpit fees (1,071, 8.9), bvp college (1,577, 9.6) | `BPIT.php`, `BVP.php` | Sourced fee table (UG Brochure/SFRC) and "is it under IPU" answer; FAQ schema on both pages is not visible (finding 7). | A |
| usict (5,524, 8.9), usict cse average package (3,148, 9.3) | `usict-admission.php` | Package section only if sourced (spec rule); cutoff block linking to a rolling three-year view. | A |
| usar (9,384, 9.1, 0.51%) | `usar-admission.php` | The 1 Dec title test is already held; leave the page alone until the 15 Dec recheck. | C (held) |
| mait cutoff (3,741, 8.9), mait cutoff 2026 (1,160, 9.6) | `mait-cutoff.php` (live 30 Sep) | The page post-dates the data; link it from `mait-admission.php`, `mait-delhi-fees-courses-placements.php`, `ipu-btech-cutoff-2025.php` and the cutoff hub. Recheck at 4 weeks. | A |
| msit cutoff (5,784, 7.9, 0.19%), msit janakpuri (1,739, 9.1) | `msit-cutoff.php` / `msit-admission.php` | Same as MAIT. | A |
| ipu cutoff (1,425, 8.2), ipu cutoff 2026 (1,762, 9.1) | `ipu-cutoff-analysis.php` | Title says "IPU Cutoff 2025" while queries say 2026. Body: add a "2026 round status" block with only released data. Year in title is class C; wait until 2026 data exists. | A, then C |
| ipu cuet (1,644, 8.0) | `ipu-btech-via-cuet.php` / `cuet-admission-ipu.php` | Link the CUET hub (`cuet-admission-ipu.php`, in local sitemap only) from `ipu-btech-via-cuet.php`; two pages share the query. | A |
| ipu btech (2,203, 9.5) | `ipu-b-tech-pillar.php` | Pillar is 471 words; expand with the sourced fee, seat and cutoff summary the spec (30 Sep) already calls for. | A |
| ipu counselling (83,395, 8.4), ggsipu counselling (46,170, 8.4), ip university counselling (1,200, 13.7) | `ipu-counselling.php` vs `GGSIPU-counselling-...` | See finding 13. | A |
| ipu admission (14,052, 9.7), ipu admissions, ggsipu admission (2,759, 11.6) | `ipu-admission-guide.php` (676 words) or homepage | The guide is not among the top 45 pages by clicks, so Google probably serves the homepage. Expand the guide; link to it from the hero copy. | A |

**Queries with no good page.**
- **"ipu rank list" (18,872 impressions, pos 5.3, 0.72%) and "ipu ranklist" (1,716, pos 6.6)**: no page targets this. Only news posts mention merit lists. Build a year-free `/ipu-rank-list.php` (class B). Rough size: at pos 5 a matching page would typically earn 4% to 6% CTR, about 800 to 1,100 clicks per 16 months against 139 today. This estimate is an assumption, not a measurement.
- **Average package queries** (usict, mait, msit, maharaja agrasen): 8,400 impressions, no sourced content. The keyword-map spec already says not to write these sections without a source, so this stays blocked until disclosures are found.
- **Fee queries for BPIT and BVP**: the pages exist, but the fee tables are not worded for the query. A.

**CTR outliers (3,000 or more impressions, CTR below 40% of a typical figure for the position): 40 queries, 1.38M impressions, 3,399 clicks.**
- Mostly brand head terms: `ipu` (706,831 impressions, 0.16%), `ggsipu` (0.19%), `ip university` (0.29%), `ipu university` (0.31%), `ipu delhi` (0.21%).
- Also non-brand: `ipu college` (20,037, pos 7.2, 0.61%), `maims` (19,130, 0.15%), `ipu admission` (0.21%), `msit cutoff` (0.19%), `bvicam` (0.10%), `ipuadmission` (0.09%).
- The brand head terms are navigational. The official ipu.ac.in and its sitelinks absorb most clicks, so I would not expect more than a doubling of CTR even with a perfect snippet. Doubling `ipu` from 0.16% to 0.32% is worth about 1,100 clicks per 16 months.
- Snippet proposals (all **class C**, not to be run before the 1 Dec homepage test reads out on 15 Dec, and then one page at a time):

| Page | Proposed direction |
|---|---|
| Homepage | Lead the title with the entity: "IP University (GGSIPU) Admission 2026 \| Counselling, Colleges, Fees" and move the helpline number out of the first 60 characters. Already in the held 1 Dec test. |
| `ipu-colleges-list.php` | Keep the title; test a description that opens with the number of colleges once finding 8(a) is settled. |
| `bvicam-admission.php` | Title starts "BVICAM Paschim Vihar Admission 2026"; test adding "Is BVICAM under IPU?" to the description. |
| `msit-cutoff.php`, `mait-cutoff.php` | Wait 4 weeks for the new pages' own data before touching. |

Expected CTR figures come from a generic position-CTR curve and are only a screening aid. CTRs here are averaged over 16 months.

---

## 5. Content quality signals

- **Staleness:** every page mentions 2026. No title or H1 carries 2023 to 2025 except pages whose URL is deliberately dated (`ipu-btech-cutoff-2025.php`, `ipu-*-cutoff-2025.php`, `economics-...`). 72 pages mention 2025 in the body, nearly all legitimately (SFRC notification of 14.07.2025, 2025 round cutoffs, "CAT 2025 → CMAT 2026"). Older years (2020 to 2024) occur on about 19 non-news pages, almost always as dated sources or placement history ("2023: 820 job offers").
- **Cutoff pages still say "2025" in title and URL:** `ipu-btech-cutoff-2025.php` (1,097 clicks), `ipu-cutoff-analysis.php` (837, title "IPU Cutoff 2025"), `ipu-law-cutoff-2025.php`, `ipu-bcom-cutoff-2025.php`, `ipu-ba-economics-cutoff-2025.php`, `ipu-bjmc-cutoff-2025.php`, `ipu-mba-cutoff-2025.php`, `ipu-cet-cutoff-2025.php`. Per the rolling three-year cutoff policy, do not rename. Any title or URL change is class C.
- **Conflicting numbers:** see finding 8. The helpline number is consistent (9899991342 appears 890 times and is the only phone number on the site).
- **Thin pages:** finding 23. Median non-news page is 885 words; news median 513.
- **Duplicated blocks:** the refund-policy paragraph ("less Rs. 1,000 processing charges ... 15 days before commencement") is repeated almost verbatim on 9 programme pages; the 60+ colleges strip is on 48 pages. Repetition on this scale is probably tolerated, but the refund paragraph should be shortened or linked to `ipu-fees-structure.php` when those pages are next edited.
- **Anchor text:** 7,881 of 8,296 internal links are root-relative; 215 card links have an empty-text image anchor plus a "read more →" anchor; 4 say "learn more →". Contextual anchors on key targets are good ("IPU counselling guide", "IPU fee structure"). The weak spot is nav-only inlinks (225 of 269 inlinks to `ipu-colleges-list.php` and 418 of 480 to `GGSIPU-...` are the single-word nav labels "Colleges", "Counselling"). 397 links are relative without a leading slash, which resolve correctly today but would break on the trailing-slash variant (finding 16).
- **News:** 98 pages, valid NewsArticle with datePublished and dateModified on all 96 that carry the block, plus FAQPage visible on each. Dedup via 301 is working. One watch item: `news/ipu-centralized-online-counselling-enrolment.php` (350 clicks, 57,681 impressions, pos 8.3, CTR 0.61%) competes with the counselling guides and has 2 inlinks.

---

## 6. 30/60/90-day plan (14 October 2026 to 12 January 2027)

Constraints: quiet season until about February 2027; content freeze 15 January 2027; held title tests on 1 December (USAR, homepage, ADGIPS) with rechecks on 15 December, 29 December and 5 January. Rule: no other C change on those three pages, and last C deploy no later than 8 January so there is a week to revert before the freeze. Before any deploy: `git pull`, merge the live `.htaccess`, run `scripts/seo_verify.py`, and curl-check every changed file.

**Days 1 to 30 (14 October to 13 November): class A only, no ranking risk**
1. Reconcile `.htaccess` with the live file (finding 6), then add snippets 1 to 4 (junk parameters, blog-detail 301s, trailing slash, case variants) and collapse the Law chain.
2. Deploy the already-prepared local branch (30 H1 fixes, 8-page sitemap fix) separately from everything else so its recheck is clean. Record the baseline date.
3. Sitemap: add the 24 missing pages (finding 1), fix `lastmod`, and add the sitemap step to the news script.
4. Internal-link batch 1 (finding 3), the 29 starved pages. Start with `ipu-counselling.php`, `top-ipu-colleges.php`, `ipu-btech-via-cuet.php` and the orphans.
5. Fix the og:image 404, add the missing OG tags on 35 pages, fix the 6 `&amp;amp;` pages, update the 14 links to redirecting URLs.
6. FAQ schema: make the 17 mismatches visible or remove them. Custom 404 page.
7. Pull GSC query-by-page for the 10 cannibalisation-watch queries.

**Days 31 to 60 (14 November to 13 December): class A and B, first C wave**
1. Build `/ipu-rank-list.php` (class B): year-free URL, one enquiry form, sourced from the CET merit-list notices, linked from the nav-adjacent hubs and the news template, added to the sitemap. Deploy by 25 November.
2. Core Web Vitals baseline (Lighthouse mobile on 5 templates), then image width/height and fetchpriority (finding 10). Trial the Bootstrap purge on staging with a visual diff (finding 11); deploy only if the diff is clean.
3. Settle the three conflicting facts (colleges count, counselling fee) and fix body text.
4. Expand `ipu-b-tech-pillar.php` and `ipu-admission-guide.php` with sourced content.
5. **C wave 1 (about 20 November, owner approval needed):** demote the second H1 on `med-admission-ipu.php`, `mtech-admission-ipu.php` and the smallest college and `top-*` pages not in the held tests (about 8 pages in total, chosen by lowest clicks from `pages.csv` plus `Pages.csv`). Recheck 4 and 18 December.
6. 1 December title tests go ahead as planned; do not touch those three pages otherwise.

**Days 61 to 90 (14 December to 12 January): decisions and the last C changes**
1. Read the 15 December recheck. If the USAR, homepage and ADGIPS tests are neutral or positive, approve the next single snippet test; if not, stop.
2. **C wave 2 (not before 18 December):** H1 cleanup on the mid-click pages (`top-bcom`, `top-law`, `top-mca`, `top-bba`, `top-mba`, `top-bca`, `mait-delhi-fees`, `maims-delhi-fees`, `bcom/bca/mca-admission`). The top three (`ipu-colleges-list.php`, `top-ipu-colleges.php`, `ipu-counselling.php`) only if waves 1 and 2 show no loss, and in any case not after 8 January.
3. Decide finding 12 (comparison page: 301, canonical or expand). The meta description fix for the Rs 1,000 fee text can ship with the 29 December window or wait.
4. Final crawl on 8 January: expect 0 double H1, 0 sitemap gaps, 0 internal 3xx links, 0 FAQ mismatches. Save as the pre-season baseline.
5. After 15 January: freeze content; only fix incidents.

---

## 7. What I could not verify

- **Core Web Vitals, LCP and real-user speed:** no Lighthouse, PageSpeed or CrUX access. Findings 10 and 11 describe likely causes, not measured gains.
- **GTM contents:** I can see the container ID and the code comment, not the tags it fires or their cost.
- **Which page Google ranks for each query:** no query-by-page data. Mapping and cannibalisation are inferences from titles and intent.
- **Indexing state:** whether the 16 missing pages and the junk URLs are indexed (no GSC coverage report or `site:` access); GSC Security Issues were not checked, so the spam-looking `?q=` and `?u=` URLs are assumed to come from external links, not a compromise (the pages reflect no input and the files are unchanged).
- **Live `.htaccess`:** returns 403, so its contents are inferred from redirect behaviour (34 news rules, canonical host, HTTPS).
- **Local branch details:** I measured only the effect (30 H1 fixes, 194-URL sitemap); I did not read the branch history.
- **Full link graph for news:** inlink counts come from the 225 crawled pages; the 5 `news/?cat=` listing pages were not crawled.
- **Response-time outlier:** `top-bba-colleges-ipu.php` at 2.47 s was a single sample.
- **The rewrite snippets** were not executed (the local PHP server ignores `.htaccess`).
- Requests used: about 330 of the 400 allowed, at a maximum of 2 per second.
