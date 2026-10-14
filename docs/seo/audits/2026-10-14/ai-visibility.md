# AI visibility audit: ipu.co.in

Date: 2026-10-14. Scope: crawlability, structured data, answer-readiness, trust, citation gaps and the news section, assessed for ChatGPT, Perplexity, Gemini, Claude and Google AI Overviews. Read-only: nothing under `website_download/` was changed.

Evidence base: live crawl of 137 URLs (top 40 pages by GSC clicks, plus all 97 news URLs in the sitemap, at about 2 req/s), live `robots.txt`, `llms.txt`, `sitemap.xml` and `/api/agent-data.php`, a local render of 103 sitemap pages and 136 root `.php` files on port 8022, the repo build scripts, `content/news/*.md` (95 files), the GSC export of 2026-09-30, and 25 web searches.

Safety classes used below: **A** = safe, no ranking-page text change (new files, schema, robots, sitemap, llms.txt, data endpoints). **B** = visible body-copy or markup change on existing pages, with no title, meta, H1, canonical or URL change. **C** = touches title, meta, H1, canonical, URL, redirects or deletion of indexed URLs, and needs owner approval.

## 1. Executive summary

1. **AI crawlers are not blocked.** `robots.txt` explicitly allows GPTBot, ClaudeBot, anthropic-ai, PerplexityBot, CCBot, Google-Extended, Applebot-Extended and Bytespider. The main content is server-rendered (median 1,747 words in the initial HTML for the top 40 pages). Responses took a median of 0.36 s with Brotli, and no `X-Robots-Tag` or other blocking headers were seen. Technical access is not the bottleneck.
2. **The discovery files mislead machines.** `sitemap.xml` omits 24 indexable, self-canonical pages, including all 9 `cuet-*` pages, `ipu-ba-llb-cutoff.php` (707 GSC clicks) and `mca-admission-ipu.php`. `llms.txt` omits 22 of the 38 top non-news pages, still says "Last updated: 2026-06-11", points at a URL that now 301s, and is 55 KB (about 13,700 tokens) of which about 60% is the news list.
3. **A correctness bug in the machine-readable layer.** `/api/agent-data.php` tells agents "MSIT CSE cutoff around 5,000-8,000 JEE Main rank". The site's own sourced dataset shows 6,832 is the Round 1 *opening* rank for Delhi, and the *closing* rank is 96,135. An AI quoting the API would be off by more than ten times.
4. **Unsupported credibility claims sit in the AI-facing files.** `llms.txt` says "99% admission success rate", "5000+ students guided" and "since 2015". `AI_AGENT_README.md` says "500+ students". The visible site says "5,000+ ... over the last 12 years" on one page. These conflict with each other, are unsourced, and contradict the owner's no-unsourced-numbers rule.
5. **Schema is broad and mostly valid.** There are no JSON-LD parse errors on the 137 pages crawled. FAQPage is on 39 of 40 top pages, BreadcrumbList on 37 of 40, and the homepage has an Organisation, WebSite (with SearchAction) and WebPage graph. The gaps are in the entity layer: no `@id` reuse on article publishers, Organisation named five different ways, no named author or Person entity, and only 2 `sameAs` links.
6. **Answer-readiness scores 3.5 / 5 on average** (rubric in section 5). The page set is strong on FAQ (37 of 40), tables (34 of 40) and brochure citations (30 of 40). It is weak on freshness agreement (15 of 40; 7 pages show a visible date later than `dateModified`) and on an explicit answer-first box (26 of 40 by a loose test).
7. **The news section is thin as a citation source.** All 95 posts are under 300 words (median 160). 62 of 97 live news pages defer to "the official PDF" without stating the dates or figures. 82 of 95 posts have no `source_url`, and the 13 that do are not rendered (only 1 live page contains the link). 55 posts have 2 FAQ items. 34 `.htaccess` redirects already exist for duplicate slugs, which shows the generator produces near-duplicates.
8. **Trust is honest but anonymous.** The non-affiliation disclosure is in the footer of every page and in the helpline FAQ, and that is good. But there is no About page, editorial policy, corrections policy or named person. The byline is "IPU Admission Team". `privacy-policy.php` is `noindex`. The trust bar shows "100,000+ students graduated" and "NAAC A++" with no visible attribution to GGSIPU.
9. **Brand presence in AI-style search is near zero in what I could check.** Across 25 realistic queries, `ipu.co.in` did not appear in any result set returned by the web-search tool. The dominant citations were Careers360 (21 of 25 queries), Collegedunia (15), Getmyuni, PW, Sartha and college sites. GSC shows the site ranks at positions 4 to 8 in India for many of the same queries, so the search tool is a weak proxy (section 3).
10. **Biggest opportunity: become the primary source for year-by-year data.** The repo already holds a sourced round-wise B.Tech cutoff dataset (21 institutes, 3 rounds, Delhi and Outside Delhi) and a sourced college-facts JSON with `as_of` dates. Exposing these as static year-free tables plus CSV/JSON is a class A change. Twelve proposals are in section 6, and the 30/60/90-day plan in section 7 finishes before the 15 January 2027 freeze.

## 2. Method and limits

- Page scoring is automated on the live HTML of the top 40 GSC URLs. It is a transparent heuristic, not a judgement of quality (section 5 defines it).
- I could **not** query ChatGPT, Perplexity, Gemini, Claude or Google AI Overviews directly. The only external signal is a US-only web-search tool that returns about 10 results per query, with no AI-answer layer. "Cited" below means "appeared in the result set".
- I did not test bot-specific user-agent behaviour at the CDN or WAF level beyond a few requests with browser-like and bot-like user agents. Those returned normal 200 responses, but Cloudflare-style bot management is not in use (LiteSpeed host), so I could not confirm how third-party crawlers from datacentre IP ranges are treated.
- The repo `llms.txt` (31,927 B) differs from live (55,078 B) because CI appends the news block at build time. I audited live.
- 3 FAQ-parity checks (`/ipu-helpline-contact-number.php`, `/ipu-btech-via-cuet.php`, `/b-tech-colleges-under-IP-university.php`) could not be confirmed by the automated detector because they use non-standard FAQ markup. Visible FAQ text exists on all three, but question-for-question parity was not verified.
- I did not verify any figure on the site against the GGSIPU brochure or SFRC gazette. Where I note a conflict, it is between two places on the site or between the site and its own data files.

## 3. Query to citation table (25 queries, 2026-10-14)

Method: WebSearch tool, standard mode, one call per query. "Cited" means the domain appeared in the returned result set. `ipu.co.in` present = no for all 25. The GSC column shows where the site actually ranks for the nearest query in the 2026-09-30 export, to show how weak the search tool is as a proxy.

| # | Query | Domains returned | ipu.co.in present | Nearest GSC evidence (India) |
|---|---|---|:-:|---|
| 1 | IPU B.Tech admission 2026 cutoff | Careers360 (all 9 results) | No | `ipu btech counselling 2026 date` pos 4.36, 125 clicks |
| 2 | BPIT fees | Collegedunia, Careers360, Getmyuni, Targetadmission, SelectYourUniversity | No | Not in top queries |
| 3 | IPU management quota admission | Careers360 Q&A, learn.engineering.vips.edu | No | Page `/IP-University-management-quota...` pos 5.14, 473 clicks |
| 4 | GGSIPU counselling schedule 2026 | Collegedunia, Careers360, Sartha, maims.ac.in | No | `ipu counselling 2026` pos 5.08 |
| 5 | IPU CET result 2026 | Careers360, Collegedunia, KCCITM, SelectYourUniversity | No | Not in top queries |
| 6 | IPU colleges list | KCCITM, Careers360, LLDIMS, Toprankers, Collegesearch, successcds | No | `ipu college list` pos 3.43, 264 clicks; page `/ipu-colleges-list.php` is the top page (8,834 clicks) |
| 7 | GGSIPU helpline number | Collegedunia (2020), Careers360, collegebatch, indiacustomercare, Wikipedia, maims.ac.in | No | `ggsipu helpline number` pos 4.05, 381 clicks |
| 8 | MAIT vs MSIT which is better IPU | Careers360 Q&A, careerplanb | No | Not in top queries |
| 9 | IPU BBA cutoff CUET 2025 college wise | Careers360, Vedantu, HitBullseye, PW | No | Page `/ipu-bba-cutoff.php` pos 4.9, 1,178 clicks |
| 10 | IPU BA LLB admission 2026 CLAT cutoff VIPS | Collegedunia, Careers360 | No | Page `/ipu-ba-llb-cutoff.php` pos 5.02, 707 clicks |
| 11 | USICT MCA fees | Collegedunia, Getmyuni, Findmycollege, Careers360 | No | `usict mca fees` pos 3.17, 80 clicks (site ranks, tool does not show it) |
| 12 | IPU B.Tech admission through CUET | Careers360, PW, Collegedunia, vips.edu | No | Page `/ipu-btech-via-cuet.php` pos 6.54, 1,562 clicks |
| 13 | ipu.co.in IPU admission guide (brand) | Timesofcollege, Careers360, PW, Collegedunia, Vedantu | No | Brand not retrievable by this tool |
| 14 | IPU counselling registration 2026 B.Tech | Careers360, Timesofcollege | No | `ipu counselling registration 2026` pos 1.87 |
| 15 | IPU fee structure 2026 B.Tech BBA | Pagalguy, Getmyuni, Careers360 | No | Page `/ipu-fees-structure.php` pos 5.9, 1,500 clicks |
| 16 | top IPU colleges 2026 ranking | Collegedunia, LLDIMS, Careers360, careerplanb | No | `top ipu colleges` pos 4.41 |
| 17 | is ipu.co.in official GGSIPU website | Careers360, Aglasem, Amar Ujala, Pagalguy | No | n/a (no source says ipu.co.in is official; ipu.ac.in is the official domain) |
| 18 | MSIT vs MSI vs MAIT vs MAIMS difference | GWU (US master's degrees, wrong entity), Careers360 | No | Disambiguation gap on the open web |
| 19 | IPU MCA admission 2026 NIMCET programme code 105 | Maarula, Buddy4Study, Collegedekho, Findmycollege, Collegebatch, Collegedunia | No | Page `/mca-admission-ipu.php` pos 4.89, 630 clicks |
| 20 | IPU B.Com Hons CUET cutoff | PW, Collegedunia, Careers360 | No | Page `/ipu-bcom-cutoff-2025.php` exists |
| 21 | VIPS vs VIPS-TC Pitampura admission | Wikipedia, engineering.vips.edu, learn.engineering.vips.edu | No | Page `/vips-admission.php` pos 6.4, 1,498 clicks |
| 22 | GGSIPU spot round counselling 2026 vacant seats | Collegedunia, MAIT, MAIMS, Notification24 | No | Several news URLs rank, e.g. `tentative-vacant-seats-btech-spot-round-two` 98 clicks |
| 23 | IPU round 1 seat allotment B.Tech closing rank MAIT CSE | Collegedunia, careerplanb | No | The site holds sourced round-wise data on `/ipu-btech-cutoff-2025.php` (1,097 clicks) |
| 24 | GGSIPU admission fake agents touts direct admission warning | Collegedunia, Mooknayak, mait.ac.in | No | Own news post `ggsipu-admissions-warning-touts.php` has 12 clicks |
| 25 | IPU B.Tech cutoff 2025 round wise JEE Main rank | Collegedunia, Careers360, Pagalguy, Vedantu | No | See row 23 |

Tally: Careers360 in 21 of 25 result sets, Collegedunia in 15, Getmyuni 4, PW 4, Pagalguy 4, college-owned sites (MAIT, MAIMS, VIPS) 6. No result set quoted a primary source for round-wise cutoffs; every cutoff answer came from an aggregator or "expected" estimate. Search results also showed these aggregators disagreeing with each other on counselling dates, BBA fees and B.Tech fees. That disagreement is the opening for a dated, sourced primary-data site.

What the table does and does not tell us: the tool is US-only, returns about 10 links, and has no AI-answer layer, so absence here is a weak signal. GSC shows 68,062 clicks and 5.8 million impressions over the export window, 98% from India, and positions between 3 and 8 for the queries above. The honest reading is "ranks on Google India, invisible to this tool, unknown in AI engines".

## 4. Findings (sorted by impact, then effort)

Effort: S = under half a day, M = 1 to 3 days, L = a week or more.

### Impact: high

**F1. `/api/agent-data.php` states a misleading B.Tech cutoff (Effort S, class A)**
- Evidence: the FAQ entry "What is the cutoff for MSIT B.Tech 2026?" answers "CSE typically ... around 5,000-8,000 in JEE Main rank". `data/btech-cutoff-2025/cutoffs.json` shows MSIT CSE (Shift I) Round 1 Delhi `min` 6,832 and `max` 96,135 (the dataset's `min` and `max` are the opening and closing rank), so the quoted range is an opening rank presented as "the cutoff". The same entry says "top 2,50,000 ranks" is needed for B.Tech, which is not tied to a source in the file. The endpoint is public, CORS-open (`access-control-allow-origin: *`) and cached for 30 minutes, so it is the file most likely to be ingested verbatim by an agent.
- Affected URLs: `https://ipu.co.in/api/agent-data.php?action=all` and `?action=overview`.
- Fix: replace the answer with a sourced sentence that gives opening and closing rank separately, names the year, round and category, and links to the cutoff page. Add `source`, `as_of` and `disclaimer` fields to every FAQ object. Remove or source the "250,000" figure.
- Verification: `curl` the endpoint, diff each numeric answer against `include/data/btech-cutoffs-2025.php`, and add a PHP test that fails if an API answer contains a number not in a sourced dataset.

**F2. Unsourced and mutually inconsistent credibility numbers in AI-facing files (Effort S, class A for files, class B for the sitewide trust bar)**
- Evidence: `llms.txt` lines 29 and 31 say "99% admission success rate" and "5000+ students guided successfully"; `llms.txt` says "10+ years" and "Since 2015"; `AI_AGENT_README.md` says "Students Guided: 500+"; `ipu-ba-llb-cutoff.php` says "helped 5,000+ students ... over the last 12 years". The trust bar (`include/components/trust-bar.php`) shows "100,000+ Students Graduated", "NAAC A++ Accredited" and "Est. 1998" with no visible statement that these describe GGSIPU rather than ipu.co.in. College counts also vary: 60+ (homepage, llms.txt, trust bar), 80+ (`ipu-colleges-list.php` title), 110+ (`top-ipu-colleges.php` body); an aggregator in the search results quoted 118 affiliated colleges. Placement ranges such as "5-15 LPA" and "10-25 LPA" in `top-ipu-colleges.php`, "Placement: 92%+" in `b-tech-colleges-under-IP-university.php` and "widely recognised as the #1 IPU-affiliated engineering college" in `mait-delhi-fees-courses-placements.php` carry no source or year.
- Why it matters: models cross-check claims across sources. A "99% success rate" next to a non-affiliation disclaimer reads as a marketing claim and lowers trust in the whole domain. Conflicting counts make the site look unreliable for the numbers it should own.
- Fix: delete the "99%", "5000+", "500+", "since 2015" and "serving ... annually" lines from `llms.txt` and `AI_AGENT_README.md`. Replace with factual, checkable statements ("independent guidance site, not affiliated with GGSIPU, free initial guidance, helpline Mon-Sat 09:00-19:00"). Pick one college count, source it to the brochure's affiliated-institutions list with an as-of date, and use it everywhere. Label the trust bar "About GGSIPU (source: ipu.ac.in)" or remove it. Source placement figures to the college's disclosure with year, or drop them.
- Verification: `grep -rE "99%|5000\+|5,000\+|500\+ students|Since 2015"` returns nothing in live files; one `grep` for college counts returns a single value.

**F3. `sitemap.xml` omits 24 indexable pages and has stale `lastmod` (Effort S, class A)**
- Evidence: live sitemap has 200 URLs (103 non-news, 97 news). The 24 omitted, self-canonical, indexable pages are: `cuet-admission-ipu.php` and the 8 `cuet-*-admission-ipu.php` pages, `ipu-ba-llb-cutoff.php`, `mca-admission-ipu.php`, `law-3-year-admission-ipu.php`, `mtech-admission-ipu.php`, `med-admission-ipu.php`, `economics-admission-ip-university.php`, `top-btech-colleges-ipu-comparison.php`, `maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php`, `usct-admission.php`, `tribhuvan-admission.php`, `tiips-admission.php`, `sbit-admission.php`, `dist-admission.php`, `dspsr-admission.php`, `bvicam-admission.php`. Eight more pages are correctly omitted because they canonicalise elsewhere. `lastmod` is stale: `ipu-bba-cutoff.php` is `2026-03-25` in the sitemap but its JSON-LD `dateModified` is `2026-05-15`; `GGSIPU-counselling-for-B-Tech-admission.php` (4,846 clicks) is `2026-02-15`. 26 URLs share `2026-03-24` and 26 share `2026-02-15`, which suggests bulk-set dates.
- Fix: generate the sitemap from the file list minus a short exclusion list (canonical-elsewhere, `noindex`, thank-you), with `lastmod` taken from each page's `$last_updated` or file mtime. Keep the news block as is.
- Verification: `diff` the sitemap against `ls *.php` minus the exclusion list (should be empty); spot-check three `lastmod` values against the page.

**F4. `llms.txt` is large, stale and not in the llms.txt format (Effort M, class A)**
- Evidence: live file is 55,078 B (about 13,700 tokens, 6,894 words), 653 lines, 174 URLs. Header says "Last updated: 2026-06-11" but the file changed on 2026-10-13. It does not follow the llmstxt.org shape (H1 title, blockquote summary, H2 sections of `- [title](url): note` links); it starts with a comment banner. 22 of the 38 top non-news pages by clicks are absent (for example `ipu-counselling.php`, `top-ipu-colleges.php`, `ipu-fees-structure.php`, `ipu-bba-cutoff.php`, `vips-admission.php`, `mca-admission-ipu.php`). It lists `/ipu-bba-cutoff-2025.php`, which now 301s to `/ipu-bba-cutoff.php` (an `.htaccess` rule), and it describes "46 colleges with dedicated pages" and per-college seat totals (for example USICT "Seats: 600", MAIT "720") with no source, while `usict-admission.php` says USICT does not offer standalone four-year B.Tech programmes and lists its own seat table. A "CITATION PREFERENCE" block asks models to cite "IPU Admission Guide (ipu.co.in)" and include the helpline number; this reads as promotional instruction and is better removed. The news block is regenerated by `scripts/build-news.php` (`news_update_llms_txt`), which strips and re-appends the `## IPU News` section idempotently. That part works, but the 95 news items make up most of the file.
- Fix: keep the existing file as `llms-full.txt` (class A, new URL) and make `llms.txt` a short index (about 80 lines): H1, blockquote with the independence statement, then sections for Guides, Cutoffs and fees, Counselling, Colleges (link to the colleges list, not 46 summaries), Data endpoints, and "Latest news (10 most recent)". Update `news_update_llms_txt` to write only the latest 10 into `llms.txt` and all into `llms-full.txt`. Drop seat totals unless sourced.
- Verification: file under 15 KB, every URL returns 200 (script), top-40 GSC pages all present, header date equals build date.

**F5. News posts defer to "the official PDF" and drop their own primary source (Effort M, class B)**
- Evidence: of 97 live news pages, 62 contain phrases such as "detailed in the official PDF", "check the official portal" or "download and read". Median body is 160 words and the longest is 281. 82 of 95 markdown files have no `source_url`; the 13 that do (all on `cdnbbsr.s3waas.gov.in`, GGSIPU's document host) are not output by the template, and only 1 of 97 live pages contains that link. `include/news-template.php` has no `source_url` handling. Example: `/news/ipu-centralized-online-counselling-enrolment.php` (350 clicks, 57,681 impressions) says the schedule is in the PDF and does not state it.
- Fix: render a "Source: GGSIPU notification dated DD Month YYYY (PDF)" link when `source_url` is present, and add `source_url` as a required field in the news build for new posts. For future posts, require the extracted key facts (dates, codes, fees) in the body or a small table.
- Verification: build and grep the output for `s3waas` count equal to the number of posts with `source_url`.

### Impact: medium

**F6. Freshness signals disagree or are missing on the highest-traffic pages (Effort S to M, class B)**
- Evidence: of the top 40 pages, 12 have no visible "Last Updated" (including `/` , `/GGSIPU-counselling-for-B-Tech-admission.php` with 4,846 clicks, `/ipu-colleges-list.php` with 8,834 clicks, `/ipu-helpline-contact-number.php`, `/ipu-btech-cutoff-2025.php`), 13 have no `dateModified` in JSON-LD, and 7 show a visible date later than `dateModified` (`usict-admission.php` visible 2026-04-06 vs JSON-LD 2026-03-24; same pattern on `mait-admission.php`, `msit-admission.php`, `usar-admission.php`, `bca-admission-ipu.php`, `bcom-admission-ipu.php`, `ba-economics-admission-ipu.php`). Only 15 of 40 have both and they agree. Nearly all top pages say "2026" in titles but show 2026-03 to 2026-06 dates, which is more than four months old on a page that is meant to track a rolling cycle.
- Fix: drive both the visible `last-updated.php` component and JSON-LD `dateModified` from one variable (`$last_updated`) so they cannot diverge. Add the component to the 12 pages that lack it. Only bump the date when facts change.
- Verification: script that extracts both dates for all pages and fails if they differ or either is missing.

**F7. Entity layer: five organisation names, no author entity, thin `sameAs` (Effort S, class A)**
- Evidence: the organisation appears as "IPU Admission Guide" (88 JSON-LD blocks), "IPU Consultancy", "IPU.co.in" (news publisher), "IPU Admission Team" (byline), "IPU Admission 2026 Guide" (`AI_AGENT_README.md` and the API) and "IP University Admission Guide 2026" (WebSite name, with a year in the name). Articles use `author: Organization "IPU Admission Guide"`; NewsArticle has no `author`. Publishers are inline objects, not references to `https://ipu.co.in/#organization`. `sameAs` on the homepage lists only Facebook and Instagram. `LocalBusiness` on the helpline page has a city but no street address, which is fine for a phone-only service but should not claim a physical location. Positives: the homepage graph is clean (`EducationalOrganization` with `@id`, `WebSite` with `SearchAction`, `WebPage` with `about` pointing at GGSIPU on ipu.ac.in), and the organisation description reads "Third-party education consultancy".
- Fix: one canonical name ("IPU Admission Guide"), reuse `{"@id": "https://ipu.co.in/#organization"}` as publisher everywhere (Article and NewsArticle), add `author` to NewsArticle, remove the year from the WebSite name, add more verifiable `sameAs` (a Google Business Profile, LinkedIn or YouTube if they exist) and do not add any that the owner cannot control. Consider a `Person` entity only if a real named reviewer agrees to be listed (see F9).
- Verification: Rich Results Test on the homepage, one college page and one news page; script counts distinct organisation names (target: 1).

**F8. Risky or promotional wording that an AI or regulator could penalise (Effort S to M, class B, some C)**
- Evidence: "direct admission" appears in 9 files, including the meta description of `/IP-University-management-quota-admission-eligibility-criteria.php` ("direct admission to B.Tech, BBA, Law & MBA") and in `llms.txt` ("Direct admission outside regular counselling based on merit and college discretion"). The site's own news post `ggsipu-admissions-warning-touts.php` reports a GGSIPU advisory against agents "promising direct admission"; a web search for that warning also returned a 2026 press report of a Rs 1.69 crore management-quota fraud. "Call 9899991342 for free step-by-step guidance" repeats in FAQ answers and the Q&A on the homepage. The helpline page has the typo "Expert experts" and the phrase "Management Quota Admission - B.Tech, BBA, Law, MBA direct seats". Ranking pages (`top-ipu-colleges.php`) say colleges are ranked on "placements, student strength, programme diversity, infrastructure and reputation" without a method, weights or sources. No `aggregateRating`, `Review` or `reviewBody` markup exists, which is correct and should stay that way. Credit where due: USICT, MAIT and MSIT pages carry "treat 2025 numbers as guidance, not a guarantee", and the helpline FAQ says "Is this the official GGSIPU helpline? No."
- Neutral wording to use: replace "direct admission" with "management-quota seats" and add "Management-quota seats are filled through the process notified by the university and the college; the UG Brochure 2026-27 (Chapter 12) states the share for unaided affiliated colleges; no one can guarantee a seat". Replace "ranking" with "comparison" or publish the method (inputs, weights, as-of date). Replace "expert guidance" with "counselling by the ipu.co.in team" unless named counsellors are shown. Use "free initial guidance" rather than "free counselling, no charges" if later steps are paid.
- Class: meta description on the management-quota page is **class C** (do not change without owner approval). Body-copy and FAQ edits are class B. `llms.txt` and API wording are class A.
- Verification: `grep -ri "direct admission"` on live HTML returns only occurrences inside a quoted warning or an explained definition.

**F9. Trust and E-E-A-T pages are missing and the privacy page is `noindex` (Effort M, class A for new pages, B for footer links)**
- Evidence: no About, Editorial policy, Corrections, Methodology, Terms or Contact page exists (`ls website_download` shows only `ipu-helpline-contact-number.php` and `privacy-policy.php`). `privacy-policy.php` carries `noindex, nofollow`. The footer links to about a dozen guide pages and the privacy policy, plus an admin login on `davyas.ipu.co.in`. Bylines read "By IPU Admission Team" with no names, qualifications or review dates, and `rel="author"` points at a span rather than a URL. Non-affiliation is stated in the footer (`include/base-footer.php`, lines 13 and 88), the helpline FAQ and the privacy policy, which is good and should be kept.
- Fix: create `/about.php` (who runs the site, since when, independence statement, how the helpline is staffed, what is free), `/editorial-policy.php` (sources used, GGSIPU brochure and SFRC gazette precedence, how often pages are reviewed, how errors are corrected, contact for corrections), and `/methodology.php` for the comparison pages. Name a responsible person only with that person's consent. Remove `noindex` from the privacy policy only with owner approval (class C), since it is a legal page and is excluded from ranking intentionally.
- Verification: pages return 200, appear in sitemap and `llms.txt`, and are linked once from the footer.

**F10. College-name disambiguation is absent on the pages that need it (Effort S, class B)**
- Evidence: none of `msit-admission.php`, `msi-admission.php`, `mait-admission.php`, `maims-admission.php`, `vips-admission.php`, `usar-admission.php` contains a "not to be confused with" line (grep for "not to be confused", "do not confuse", "not the same" found nothing). The open-web search for "MSIT vs MSI vs MAIT vs MAIMS" returned George Washington University master's degrees. `vips-admission.php` is titled "VIPS-TC Pitampura", while `llms.txt` describes "VIPS" as a law and management college and the search results describe VIPS-TC's engineering school as separate; I did not verify which programmes belong to which institute.
- Fix: add a short "Do not confuse" box at the top of each such page with full names, campus and programme families (sourced to the brochure's affiliated institutions list), and add `alternateName` and `disambiguatingDescription` to `college-schema.php`.
- Verification: each college page contains the box; `college-schema.php` output includes `alternateName`.

### Impact: lower or longer-term

**F11. Page weight and markup bloat on one top page (Effort S, class A/B)**
- Evidence: `/ipu-helpline-contact-number.php` is 180 KB of HTML for 720 words (median top page is 74 KB), because its source has hundreds of leading spaces per line in the JSON-LD block (the `LocalBusiness` block is indented progressively deeper on every line). Brotli hides most of the transfer cost (14 KB sent for `/BPIT.php`), but extraction tools that read raw HTML or count tokens pay for it. `/news/` is 154 KB and `ipu-bba-cutoff.php` is 124 KB. Response time is not an issue (median 0.36 s, max 0.48 s on the top 40; news max 2.86 s on one request).
- Fix: re-indent the JSON-LD on the helpline page and move large inline CSS to a cached file where it is repeated (the homepage embeds a full stylesheet inline). No visible change.
- Verification: HTML size drops under 80 KB for the helpline page.

**F12. `robots.txt` is permissive but has noise (Effort S, class A)**
- Evidence: the live file (1,483 B, last modified 2026-05-15) allows every named AI crawler. It also has `Crawl-delay: 0` and `Request-rate: 0` attached to the last group (Diffbot): `Request-rate` is non-standard and `Crawl-delay` is ignored by Google; neither is needed. `/AI_AGENT_README.md` is disallowed while `llms.txt` and the API still describe it, which is inconsistent. `Perplexity-User` and `ChatGPT-User` are allowed, which is right for user-initiated fetches. There is no `Disallow` for the `/api/` path, so the JSON endpoint is crawlable (intended).
- Fix: remove the two non-standard lines, keep the allow-list, and decide whether `AI_AGENT_README.md` should be public. If it is kept public, remove the `Disallow`, correct its numbers (F2) and link it from `llms.txt`.
- Verification: `curl -s https://ipu.co.in/robots.txt` and a robots parser test for each named agent.

**F13. Duplicate and legacy pages dilute entity signals (Effort M, class C)**
- Evidence: 8 files canonicalise to another page (`IPU-B-Tech-admission-2025.php`, `IPU-Law-Admission-2025.php`, `IPU-Law-Admission-2026.php`, `economics-admission-2025.php`, `ipu-bba-admission.php`, `law-admission-ip-university.php`, `vips-pitampura-courses.php`, `bharati-vidyapeeth-engineering-college-delhi-admission-courses-placement.php`). Live, some also 301. Top-40 pages with overlapping intent exist (`top-ipu-colleges.php`, `ipu-colleges-list.php`, `b-tech-colleges-under-IP-university.php`, `top-btech-colleges-delhi.php`, `best-btech-colleges-ipu.php`). Three of these are in the top 40 with different numbers for the same facts (for example USICT B.Tech seats).
- Fix: do not merge or redirect now. Add a "Data source" block that all of these pull from one dataset (see section 6). Any consolidation is class C and belongs after the season.
- Verification: a script lists every figure per college across pages and flags differences.

### News section assessment (point 6 of the brief)

Is the news section helping or diluting? Net: **helping on freshness and long-tail clicks, diluting on citation quality**.

- Helping: 115 news URLs appear in GSC with 3,223 clicks and 233,934 impressions (4.7% of the 68,062 total clicks), and 105 of them have at least one click. `/news/` itself has 392 clicks. Posts are published about one per day from 2026-04-15 to 2026-10-11 (95 posts, 95 distinct dates, latest 3 days ago), which is a credible cadence. Content is sourced from real GGSIPU notices and uses `NewsArticle` + `FAQPage` + `BreadcrumbList` with no parse errors.
- Diluting: all 95 posts are under 300 words; 55 have 2 FAQ items and 40 have 3; categories are skewed (62 Counselling, 26 Admissions, 4 Results, 2 CET, 1 General); 14 use the same "Key Details" headings and 10 the same "Next Steps for Applicants" heading, and 67 end with the same link to the IPU admission guide, which reads templated. There are at least 17 near-duplicate title pairs by token overlap, for example "GGSIPU Withdraws Offline Spot Round Counselling Schedule" against "GGSIPU Withdraws Spot Round 3 Offline Counselling Schedule" (similarity 0.83), and refund-order posts that differ only by course. 34 `.htaccess` redirects already collapse duplicate slugs into a handful of canonical posts (for example 6 slugs into `ipu-new-medical-paramedical-applications-2026.php`, 5 into `ipu-withdraws-msc-slp-applications.php`, 4 into `ggsipu-admissions-warning-touts.php`), which shows the generator creates duplicates and the cleanup is manual.
- Schema quality: `NewsArticle` has headline, description, image, dates, publisher logo, `articleSection`; it lacks `author`, `isBasedOn` or `citation` (the GGSIPU notice URL), `inLanguage`, and time-of-day on dates (`datePublished` is date only). The BreadcrumbList third item points at `/news/?cat=counselling`, a query URL.
- Recommendations (none urgent; all class C for any URL change, so owner approval first):
  1. Prevention first (class A, build script): block publishing a post whose title token overlap with an existing post is 0.6 or more, or flag it for merge into a single "round tracker" post. Require `source_url` and at least one concrete fact (date, code, amount) in the body.
  2. Add `isBasedOn` (source PDF) and `author` (Organisation reference) to `news-jsonld.php` (class A).
  3. Merge by topic into evergreen trackers rather than deleting: "Spot round 2026-27: schedules and vacant seats", "Refund and withdrawal orders", "Offline counselling schedules by programme". Keep every news URL alive; when a post is merged, 301 it to the tracker only if it has zero clicks and zero impressions in the last 90 days (10 news URLs had zero clicks in the export). Do not delete anything with clicks.
  4. Add `noindex,follow` only to posts older than 12 months that have no clicks, and only after owner approval (class C). Not before the 2027 season.
  5. Re-point the third breadcrumb to a clean category URL, or drop the category level (class B).

## 5. Rubric and scores for the top 40 pages

Pages are the 40 highest-clicking URLs in `Pages.csv` (GSC export 2026-09-30), fetched live on 2026-10-14. That set includes `/news/` and one news post. Scoring is automatic from the HTML, so treat it as a screening tool.

**Rubric (0 to 5, one point per criterion, minus 1 for an unsourced claim, floor 0):**

| Code | Criterion | Test applied |
|---|---|---|
| A | Answer-first | A TL;DR, "at a glance", "quick answer", "key takeaway" or "Summary" block appears in the first 1,800 characters of body text |
| B | Question structure | At least 5 FAQ questions in `FAQPage` JSON-LD, and every question's text is present in the visible HTML |
| C | Data table | At least one `<table>` containing units or data tokens (Rs, rank, seats, %, LPA, CUET, score) |
| D | Primary sourcing | At least 2 mentions of brochure, SFRC, gazette, notification, `ipu.admissions.nic.in` or `ipu.ac.in` in body text, including one of brochure, SFRC, gazette or notification |
| E | Freshness agreement | Visible "Last Updated" date and JSON-LD `dateModified` both present and equal |
| Risk | Unsourced claim | Placement range, placement percentage or "#1" claim with no placement-data source on the page (-1) |

**Results.** Mean 3.5 / 5. Score distribution: 5 points = 9 pages, 4 = 12, 3 = 12, 2 = 4, 1 = 3. Criterion pass counts out of 40: A 26, B 37, C 34, D 30, E 15, Risk 2. Freshness (E) is the weakest criterion by a wide margin, then answer-first (A). The full machine-readable table is in `ai-visibility-rubric-top40.csv` next to this file.

### 15 lowest-scoring pages (ties broken by clicks, highest first)

| Rank | Page | GSC clicks | Score | Gaps |
|---|---|---:|:-:|---|
| 1 | `/` | 2,338 | 1 | no answer-first box; no data table; weak primary-source cites; freshness mismatch/missing |
| 2 | `/IP-University-management-quota-admission-eligibility-criteria.php` | 473 | 1 | no answer-first box; no data table; weak primary-source cites; freshness mismatch/missing |
| 3 | `/news/` | 392 | 1 | no answer-first box; no FAQ; no data table; freshness mismatch/missing |
| 4 | `/ipu-helpline-contact-number.php` | 3,418 | 2 | no answer-first box; weak primary-source cites; freshness mismatch/missing |
| 5 | `/ipu-btech-via-cuet.php` | 1,562 | 2 | no answer-first box; FAQ not verifiably visible=schema; freshness mismatch/missing |
| 6 | `/b-tech-colleges-under-IP-university.php` | 369 | 2 | no answer-first box; weak primary-source cites; freshness mismatch/missing |
| 7 | `/news/ipu-centralized-online-counselling-enrolment.php` | 350 | 2 | FAQ not verifiably visible=schema; no data table; freshness mismatch/missing |
| 8 | `/GGSIPU-counselling-for-B-Tech-admission.php` | 4,846 | 3 | no answer-first box; freshness mismatch/missing |
| 9 | `/vips-admission.php` | 1,498 | 3 | no answer-first box; freshness mismatch/missing |
| 10 | `/ipu-btech-cutoff-2025.php` | 1,097 | 3 | weak primary-source cites; freshness mismatch/missing |
| 11 | `/top-btech-colleges-delhi.php` | 1,079 | 3 | weak primary-source cites; freshness mismatch/missing |
| 12 | `/IPU-B-Tech-admission-2026.php` | 1,059 | 3 | no answer-first box; freshness mismatch/missing |
| 13 | `/IPU-Law-Admission.php` | 1,045 | 3 | no answer-first box; freshness mismatch/missing |
| 14 | `/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` | 1,003 | 3 | no answer-first box; freshness mismatch/missing |
| 15 | `/ipu-cutoff-analysis.php` | 837 | 3 | weak primary-source cites; freshness mismatch/missing |

### All 40 pages, ordered by clicks

| # | Page | GSC clicks | A | B | C | D | E | Risk | Score | Main gaps |
|---|---|---:|:-:|:-:|:-:|:-:|:-:|:-:|:-:|---|
| 1 | `/ipu-colleges-list.php` | 8,834 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 2 | `/GGSIPU-counselling-for-B-Tech-admission.php` | 4,846 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 3 | `/top-ipu-colleges.php` | 4,480 | 1 | 1 | 1 | 0 | 1 | 0 | **4** | weak primary-source cites |
| 4 | `/ipu-counselling.php` | 4,013 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 5 | `/ipu-helpline-contact-number.php` | 3,418 | 0 | 1 | 1 | 0 | 0 | 0 | **2** | no answer-first box; weak primary-source cites; freshness mismatch/missing |
| 6 | `/` | 2,338 | 0 | 1 | 0 | 0 | 0 | 0 | **1** | no answer-first box; no data table; weak primary-source cites; freshness mismatch/missing |
| 7 | `/ipu-btech-via-cuet.php` | 1,562 | 0 | 0 | 1 | 1 | 0 | 0 | **2** | no answer-first box; FAQ not verifiably visible=schema; freshness mismatch/missing |
| 8 | `/ipu-fees-structure.php` | 1,500 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 9 | `/vips-admission.php` | 1,498 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 10 | `/top-bcom-colleges-ipu.php` | 1,461 | 1 | 1 | 1 | 0 | 1 | 0 | **4** | weak primary-source cites |
| 11 | `/top-law-colleges-ipu.php` | 1,281 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 12 | `/top-mca-colleges-ipu.php` | 1,222 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 13 | `/ipu-bba-cutoff.php` | 1,178 | 0 | 1 | 1 | 1 | 1 | 0 | **4** | no answer-first box |
| 14 | `/ipu-btech-cutoff-2025.php` | 1,097 | 1 | 1 | 1 | 0 | 0 | 0 | **3** | weak primary-source cites; freshness mismatch/missing |
| 15 | `/top-btech-colleges-delhi.php` | 1,079 | 1 | 1 | 1 | 0 | 0 | 0 | **3** | weak primary-source cites; freshness mismatch/missing |
| 16 | `/IPU-B-Tech-admission-2026.php` | 1,059 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 17 | `/IPU-Law-Admission.php` | 1,045 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 18 | `/comprehensive-guide-to-bba-colleges-under-ip...` | 1,003 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 19 | `/mait-delhi-fees-courses-placements.php` | 994 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 20 | `/top-bba-colleges-ipu.php` | 849 | 1 | 1 | 1 | 0 | 1 | 0 | **4** | weak primary-source cites |
| 21 | `/ipu-cutoff-analysis.php` | 837 | 1 | 1 | 1 | 0 | 0 | 0 | **3** | weak primary-source cites; freshness mismatch/missing |
| 22 | `/top-mba-colleges-ipu.php` | 821 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 23 | `/top-bca-colleges-ipu.php` | 732 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 24 | `/ipu-ba-llb-cutoff.php` | 707 | 0 | 1 | 1 | 1 | 1 | 0 | **4** | no answer-first box |
| 25 | `/guide-to-bjmc-colleges-under-ip-university.php` | 645 | 0 | 1 | 1 | 1 | 0 | 0 | **3** | no answer-first box; freshness mismatch/missing |
| 26 | `/usict-admission.php` | 637 | 1 | 1 | 1 | 1 | 0 | -1 | **3** | freshness mismatch/missing; unsourced placement/rank claim |
| 27 | `/bcom-admission-ipu.php` | 634 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 28 | `/mca-admission-ipu.php` | 630 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 29 | `/maims-delhi-fees-courses.php` | 606 | 1 | 1 | 1 | 1 | 1 | 0 | **5** | - |
| 30 | `/mait-admission.php` | 584 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 31 | `/bca-admission-ipu.php` | 566 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 32 | `/IP-University-management-quota-admission-eli...` | 473 | 0 | 1 | 0 | 0 | 0 | 0 | **1** | no answer-first box; no data table; weak primary-source cites; freshness mismatch/missing |
| 33 | `/usar-admission.php` | 430 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 34 | `/msit-admission.php` | 420 | 1 | 1 | 1 | 1 | 0 | 0 | **4** | freshness mismatch/missing |
| 35 | `/news/` | 392 | 0 | 0 | 0 | 1 | 0 | 0 | **1** | no answer-first box; no FAQ; no data table; freshness mismatch/missing |
| 36 | `/ba-economics-admission-ipu.php` | 385 | 1 | 1 | 1 | 1 | 0 | -1 | **3** | freshness mismatch/missing; unsourced placement/rank claim |
| 37 | `/b-tech-colleges-under-IP-university.php` | 369 | 0 | 1 | 1 | 0 | 0 | 0 | **2** | no answer-first box; weak primary-source cites; freshness mismatch/missing |
| 38 | `/news/ipu-centralized-online-counselling-enro...` | 350 | 1 | 0 | 0 | 1 | 0 | 0 | **2** | FAQ not verifiably visible=schema; no data table; freshness mismatch/missing |
| 39 | `/cuet-law-admission-ipu.php` | 348 | 1 | 1 | 0 | 1 | 0 | 0 | **3** | no data table; freshness mismatch/missing |
| 40 | `/law-3-year-admission-ipu.php` | 339 | 1 | 1 | 0 | 1 | 1 | 0 | **4** | no data table |

Reading the table: the three highest-click pages that score 4 or less are `/ipu-colleges-list.php` (8,834 clicks, score 4), `/GGSIPU-counselling-for-B-Tech-admission.php` (4,846, score 3) and `/` (2,338, score 1). Each is a short fix (visible date plus `dateModified`, an answer-first box, one primary-source line). The nine pages at 5 points (`/ipu-counselling.php`, `/ipu-fees-structure.php`, `/top-law-colleges-ipu.php`, `/top-mca-colleges-ipu.php`, `/top-mba-colleges-ipu.php`, `/top-bca-colleges-ipu.php`, `/mca-admission-ipu.php`, `/maims-delhi-fees-courses.php`, `/mait-delhi-fees-courses-placements.php`) are the template to copy: TL;DR box, question headings, tables with units, brochure citations and a matching visible and machine date. For clause-level sourcing, `/ipu-btech-via-cuet.php` is the best example on the site ("Per UG Brochure 2026-27 §1.2 (page 29) and Important Instruction #37"), even though it scores 2 on other criteria. `/mait-delhi-fees-courses-placements.php` scores 5 despite the unsourced "#1" sentence because the page cites some placement data, so the Risk flag is conservative and should not be read as a clean bill of health.

## 6. Twelve proposed evergreen hub and data pages

All are new, year-free URLs (class A), so no existing title, meta, H1, canonical or URL changes. Each page should show an "as of" date and a dated source line, publish the same table as CSV and JSON, and use the same figure everywhere it appears. Figures must come from the GGSIPU brochure, SFRC gazette or the official round-wise notices; where only a college's disclosure exists, say so. The existing B.Tech cutoff dataset has Delhi and Outside Delhi dimensions only, which matches the university's own publication (no category split); keep that rule so the site never invents a split the source does not have.

| # | Proposed page (year-free) | What it is | Target queries | Source documents | Effort | Class |
|---|---|---|---|---|---|---|
| 1 | `/data/btech-cutoffs.php` + CSV/JSON | Cutoff explorer as static tables: institute, branch, round, region, opening and closing rank, one section per cycle. Seeded from `include/data/btech-cutoffs-2025.php` (21 institutes, 3 rounds) | "MAIT CSE closing rank round 1", "IPU B.Tech cutoff round wise", "MSIT cutoff" | GGSIPU round-wise cutoff PDFs on `ipu.admissions.nic.in` | M | A |
| 2 | `/data/btech-seat-matrix.php` | Seats per institute, branch and quota, plus round-wise vacant-seat movement | "MAIT CSE seats", "IPU B.Tech seat matrix" | UG Brochure seat matrix; official seat-matrix and vacant-seat notices | M | A |
| 3 | `/ipu-counselling-calendar.php` | One table of counselling events per programme and round across cycles, each row linked to the notice | "GGSIPU counselling schedule", "IPU spot round date", "IPU counselling calendar" | Brochure schedule; notice PDFs already referenced in news `source_url` | M | A |
| 4 | `/data/fees-by-college.php` + CSV | Tuition and other fees by college and programme, with year, category (Delhi or Outside Delhi where relevant) and a source per row | "BPIT fees", "USICT MCA fees", "IPU fee structure" | SFRC gazette; UG and PG brochures; college mandatory disclosures | L | A |
| 5 | `/data/programme-codes.php` | Programme code, eligible national test, IPU CET and CUET paper codes, priority order (national test, then IPU CET, then CUET), minimum marks | "IPU programme code 131", "IPU MCA code 105", "IPU CUET subject codes" | UG and PG Brochure 2026-27, chapter on programme codes and Important Instructions | S | A |
| 6 | `/data/affiliated-colleges.php` + JSON-LD `ItemList` | The one canonical list of affiliated institutes: full name, short name, aliases, campus, location, programmes, approved intake | "IPU colleges list", "GGSIPU affiliated colleges", "MSIT vs MSI vs MAIT" | Brochure affiliated-institutions list; approved-intake disclosures (pattern already in `college-facts-2026.json`) | M | A |
| 7 | `/data/management-quota.php` | Share, eligibility and process by programme, with a no-guarantee statement and "report fraud" link | "IPU management quota", "IPU management seat fee" | UG Brochure 2026-27 Chapter 12; GGSIPU anti-tout advisory | S | A |
| 8 | `/data/cuet-ug-cutoffs.php` | Round-wise CUET score or rank cutoffs for BBA, BCA, B.Com (Hons), BJMC and BA courses by college and region | "IPU BBA cutoff CUET", "IPU B.Com cutoff" | Official round-wise cutoff lists | L | A |
| 9 | `/data/law-cutoffs.php` | CLAT rank cutoffs for BA LLB and BBA LLB by college and round | "VIPS BA LLB cutoff", "IPU BA LLB cutoff" | Official law round-wise cutoff lists | M | A |
| 10 | `/data/pg-cutoffs.php` | MBA (CAT or CMAT), MCA (NIMCET or IPU CET) and M.Tech seats and cutoffs | "USMS MBA cutoff", "IPU MCA cutoff" | PG Brochure 2026-27; official PG cutoff lists | M | A |
| 11 | `/notifications/` | Dated index of GGSIPU notices (date, programme, type, official PDF link), generated from news `source_url` fields; the news posts become the explainers | "GGSIPU notice", "IPU vacant seat list", "IPU refund order" | Official notice PDFs (`cdnbbsr.s3waas.gov.in` and `ipu.ac.in`) | M | A |
| 12 | `/data/ipu-terms-and-institutes.php` | Glossary for IPU, GGSIPU, IP University, IPU CET, CUET, programme codes, and a disambiguation table for MSIT, MSI, MAIT, MAIMS, VIPS, VIPS-TC, USAR, USICT | "GGSIPU full form", "MAIT full form", "difference between MSIT and MSI" | Brochure; each institute's own site for full names | S | A |

Linking these from existing ranking pages is a body-copy edit (class B, additive, no change to title, meta, H1, canonical or URL). Do that only after the hub exists and with owner approval per page. Page 1 overlaps the topic of `/ipu-btech-cutoff-2025.php` (1,097 clicks); keep the existing page as the narrative and make the hub the data source, cross-linked, so the two do not compete.

Data endpoints: serve each table as `https://ipu.co.in/data/<name>.csv` and `.json` with `Content-Type`, a `Last-Modified` header, a header row with units, and a `source` and `as_of` column. Do not add them to `robots.txt` Disallow.

## 7. 30/60/90-day plan (quiet season, content freeze on 15 January 2027)

Admissions traffic is lowest between October and January, so this is the window to change the machine-facing layer and build data pages. Everything below lands by 12 January 2027 so that nothing is in flight on the freeze date. Existing commitments from the keyword plan stay unchanged: the 1 December title deployments (USAR, homepage, ADGIPS) and their rechecks on 15 December, 29 December and 5 January. Do not stack any class B edits on those three URLs in the same weeks, so the title effect stays measurable.

**Days 1 to 30 (14 October to 13 November): class A fixes and baseline**
- F1: correct `/api/agent-data.php` and add the numeric-answer test.
- F2: remove unsourced numbers from `llms.txt` and `AI_AGENT_README.md`; agree one college count.
- F3: generate `sitemap.xml` from the file list (24 pages added, `lastmod` from page dates).
- F4: split into short `llms.txt` and `llms-full.txt`; update `build-news.php` to write the latest 10 items in the short file; fix the 301 entry; set a build date in the header.
- F7: unify organisation name, publisher `@id` references and NewsArticle `author` in JSON-LD (class A).
- F12 and F11: clean `robots.txt` noise; re-indent helpline JSON-LD.
- F5, build part: make `source_url` required for new news posts and render it (template change, class B, additive).
- Baseline: export GSC again and record manual prompts (owner or Sumit, 20 prompts in ChatGPT, Perplexity and Gemini) to know the starting position. I could not run these myself.
- Owner decisions needed: approve F5 template change; decide whether `AI_AGENT_README.md` stays disallowed.

**Days 31 to 60 (14 November to 13 December): trust, freshness and first data pages**
- F9: publish `/about.php`, `/editorial-policy.php`, `/methodology.php` and a visible corrections log; add one footer link each, in the same quiet style as existing footer links.
- F6: drive visible date and `dateModified` from one variable on all top-40 pages; add the date component to the 12 pages that lack it.
- F10: "Do not confuse" boxes on the six college pages (body copy, class B, after approval).
- F8: body and FAQ wording changes per the neutral-wording rules (class B). The management-quota meta description stays untouched unless the owner approves (class C).
- Data pages 5, 7, 11, 12 (small, mostly text) and 6 (affiliated colleges directory).
- Data pages 1 and 2 (B.Tech cutoffs and seat matrix) published with CSV and JSON.

**Days 61 to 90 (14 December to 12 January): remaining data pages and verification**
- Data pages 3, 4, 8, 9, 10 as sources allow; ship what is sourced and mark the rest "in preparation" only if the owner agrees (no placeholder tables).
- News: present the prune and merge proposal (section 4, news) to the owner. Build the overlap guard for new posts now; execute any merge or redirect after the season (class C).
- Re-run this audit script, rubric and `grep` checks; target mean rubric score 4.2 or higher, 0 unsourced-number hits in AI-facing files, sitemap and `llms.txt` coverage of the top 40 at 100%, one organisation name in JSON-LD.
- 8 January: merge freeze for all changes. 12 January: last deploy. 15 January: content freeze.

**Measure** (monthly, from the GSC export): clicks and impressions for `/data/*`, referrals from `chatgpt.com`, `perplexity.ai`, `gemini.google.com` and `copilot.microsoft.com` in server logs or GA4 (not available to me), and branded query volume for "ipu admission guide".

## 8. What I could not verify

- Whether any AI engine currently cites or ingests ipu.co.in. The search tool is US-only, shows no AI-answer layer, and showed no ipu.co.in result for any of the 25 queries even where GSC shows positions 3 to 6, so it under-reports.
- Bot-specific serving behaviour from third-party crawler IP ranges (only 3 user-agent variants tested, all returned normal pages).
- Accuracy of any college fee, seat, cutoff or placement figure against the GGSIPU brochure or SFRC gazette. I flagged only internal contradictions and missing sources.
- Whether the visible FAQ on three pages matches the schema question for question (non-standard markup).
- Live `.htaccess` content: I read the repo copy (`website_download/htaccess`, 34 news rules); live redirects for `IPU-Law-Admission-2026.php` and `ipu-bba-cutoff-2025.php` confirmed by `curl`, others not tested.
- GA4, server logs and AI-referrer traffic.
- Rich Results Test and Schema Markup Validator output (not run; parse validity and type coverage were checked locally).

## 9. Files

- This report: `/Users/Sumit/test-project/docs/seo/audits/2026-10-14/ai-visibility.md`
- Rubric data: `/Users/Sumit/test-project/docs/seo/audits/2026-10-14/ai-visibility-rubric-top40.csv`
