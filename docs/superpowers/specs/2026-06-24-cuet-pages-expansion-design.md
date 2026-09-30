# ipu.co.in — CUET Pages Expansion (Design Spec)

**Date:** 2026-06-24
**Status:** Approved — proceed to implementation plan
**Scope owner:** Sumit
**Origin:** Add CUET subject-eligibility pages for 4 new programmes + surgical updates to 4 existing CUET pages

---

## 1. Goal

Expand ipu.co.in's CUET coverage from 4 existing programmes to 8 total, targeting "IPU [programme] admission through CUET" for SEO and lead generation. Source of truth: UG Admission Brochure 2026-27, Chapter 2 (CUET subject paper tables).

---

## 2. Hard constraints

- **SEO is additive-only on ranking pages.** Existing 4 CUET pages (B.Tech/BBA/B.Com/Law): ONLY `$last_updated` bump + caveat sentence update + `dateModified` in JSON-LD. Zero changes to H1, title, meta, canonical, URL, H2 order, or first ~200 words. (`feedback_seo_safety_ipu`)
- **New pages are purely additive.** New URLs carry no existing rank equity; they cannot harm current positions.
- **Evergreen URLs.** No year in slugs. (`feedback_evergreen_urls`)
- **One enquiry form per page.** Sidebar enquiry only; `$hero_show_form = false`. (`feedback_one_form_per_page`)
- **Pre-deploy quality check.** PHP lint + localhost curl 200 + localhost crosslink walk before any FTP push. (`feedback_pre_deploy_quality_check`, `feedback_localhost_crosslink_test`)
- **Prod deploy gated.** Implement + verify locally; pause for owner go-ahead before FTP push. (`feedback_full_deploy_recipe_no_shortcuts`)
- **Brochure caveat must appear on every CUET page.** CUET subject list is from 2025-26 methodology; IPU may revise for 2026-27 via separate notification (brochure page 29).

---

## 3. Source data

**PDF:** `/Users/Sumit/Desktop/UG 2026.pdf` (needs qpdf repair → scratchpad before reading)
**Chapter 2 (pages 30–68):** Eligibility & CUET subject tables

**CUET subject paper map (2026-27 brochure):**

| Programme | IPU Code | Primary entrance | CUET Section IA | CUET Section II | CUET Section III |
|-----------|----------|-----------------|-----------------|-----------------|------------------|
| B.Tech | 131 | JEE Main | — | Physics(322) + [Chem(306)/CS(308)] + Math(319) | — |
| BBA | 125 | IPU CET | English(101) | Business Studies(305) | General Aptitude(501) |
| B.Com H | 146 | IPU CET | English(101) | Accountancy(301) | General Aptitude(501) |
| BA-LLB/BBA-LLB | 121 | CLAT | English(101) | — | Legal Studies / General Aptitude(501) |
| BA JMC (BJMC) | 126 | IPU CET | English(101) | Mass Media(318) | General Aptitude(501) |
| BA Eco | 197 | IPU CET | English(101) | Economics(309) / Math(319) | — |
| BCA | 114 | IPU CET | English(101) | Math(319) / CS/Informatics(308) | General Aptitude(501) |
| BA English | 184 | IPU CET | English(101) | — | General Aptitude(501) |

---

## 4. File change inventory

### 4a. Surgical updates — existing pages (4 files)

For each file, make exactly three changes:

1. `$last_updated` → `'2026-06-24'`
2. Brochure caveat sentence: update to match brochure page 29 wording — *"Domain Specific Subjects/Optional Language/General Test under CUET UG as mentioned above are based on the 2025-26 methodology. IPU may revise subject requirements for 2026-27 via separate notification."*
3. `dateModified` in Article JSON-LD schema → `"2026-06-24"`

**Files:**
- `website_download/cuet-btech-admission-ipu.php`
- `website_download/cuet-bba-admission-ipu.php`
- `website_download/cuet-bcom-admission-ipu.php`
- `website_download/cuet-law-admission-ipu.php`

### 4b. New pages (4 files)

Clone `cuet-bba-admission-ipu.php` as the canonical template. Per-programme customisations:

**`cuet-bjmc-admission-ipu.php`**
- Title: `IPU BJMC Admission Through CUET (UG) — Eligibility, Subjects, Colleges`
- Meta desc: `GGSIPU BA(JMC)/BJMC via CUET — Mass Media + General Aptitude papers, vacant-seat counselling after IPU CET. Free guidance: 9899991342.`
- Canonical: `https://ipu.co.in/cuet-bjmc-admission-ipu.php`
- H1: `IPU BJMC Admission Through CUET (UG): Eligibility, Subjects & College List`
- Hero image alt: `BJMC / BA(JMC) admission through CUET at GGSIPU — subject papers and eligible colleges`
- Programme code: 126 (BA(JMC))
- Duration: 4 years UG
- Primary entrance: IPU CET; CUET as vacant-seat fallback (Instruction #37) + management quota qualifier (Instruction #21)
- CUET papers: Section IA English(101) / Section II Mass Media & Mass Communication(318) / Section III General Aptitude Test(501)

**`cuet-ba-economics-admission-ipu.php`**
- Title: `IPU BA Economics Admission Through CUET (UG) — Eligibility, Subjects, Colleges`
- Meta desc: `GGSIPU BA Economics (Hons) via CUET — Economics and Maths domain papers, vacant-seat counselling after IPU CET. Free guidance: 9899991342.`
- Canonical: `https://ipu.co.in/cuet-ba-economics-admission-ipu.php`
- H1: `IPU BA Economics Admission Through CUET (UG): Eligibility, Subjects & College List`
- Hero image alt: `BA Economics admission through CUET at GGSIPU — subject papers and eligible colleges`
- Programme code: 197 (BA Economics Hons)
- Duration: 4 years UG
- Primary entrance: IPU CET; CUET as fallback
- CUET papers: Section IA English(101) / Section II Economics / Business Economics(309) OR Mathematics / Applied Mathematics(319)

**`cuet-bca-admission-ipu.php`**
- Title: `IPU BCA Admission Through CUET (UG) — Eligibility, Subjects, Colleges`
- Meta desc: `GGSIPU BCA via CUET — Maths + Computer Science/Informatics Practices papers, vacant-seat counselling after IPU CET. Free guidance: 9899991342.`
- Canonical: `https://ipu.co.in/cuet-bca-admission-ipu.php`
- H1: `IPU BCA Admission Through CUET (UG): Eligibility, Subjects & College List`
- Hero image alt: `BCA admission through CUET at GGSIPU — subject papers and eligible colleges`
- Programme code: 114 (BCA)
- Duration: 4 years UG
- Primary entrance: IPU CET; CUET as fallback
- CUET papers: Section IA English(101) / Section II Mathematics/Applied Mathematics(319) / Section II Computer Science/Informatics Practices(308) / Section III General Aptitude Test(501)

**`cuet-ba-english-admission-ipu.php`**
- Title: `IPU BA English Admission Through CUET (UG) — Eligibility, Subjects, Colleges`
- Meta desc: `GGSIPU BA English (Hons) via CUET — English Language + General Aptitude papers, vacant-seat counselling after IPU CET. Free guidance: 9899991342.`
- Canonical: `https://ipu.co.in/cuet-ba-english-admission-ipu.php`
- H1: `IPU BA English Admission Through CUET (UG): Eligibility, Subjects & College List`
- Hero image alt: `BA English Hons admission through CUET at GGSIPU — subject papers and eligible colleges`
- Programme code: 184 (BA English Hons)
- Duration: 4 years UG
- Primary entrance: IPU CET; CUET as fallback
- CUET papers: Section IA English Language(101) / Section III General Aptitude Test(501)

**H2 section structure for all 4 new pages (same order as BBA template):**
1. How CUET Works for IPU Admission
2. Eligibility Criteria (10+2 pass, 45–50% aggregate, stream-specific)
3. CUET Subject Papers for [Programme]
4. Participating Colleges (top 4–5 affiliated institutes with seat count)
5. Management Quota Admission via CUET
6. Programme Fees
7. Step-by-Step: How to Get IPU [Programme] Admission via CUET
8. Frequently Asked Questions (5 questions, brochure-cited)

**FAQ topics per page:**
- BJMC: Is IPU CET mandatory before CUET counselling? / Which colleges offer BJMC via CUET? / Can CUET replace IPU CET for BJMC? / What is Mass Media subject code in CUET? / When does CUET vacant-seat counselling open?
- BA Eco: Same structure, Economics-specific
- BCA: BCA-specific — CS/Math subject code FAQ
- BA English: English Language paper + GAT only — clarify no domain subject needed

### 4c. Hub update — `cuet-admission-ipu.php`

1. Table: add 4 new rows (BJMC / BA Eco / BCA / BA English) matching existing row pattern
2. `related_pages` array: add 4 new entries pointing to the new pages
3. Intro paragraph: update programme count from "4" to "8 programmes"

### 4d. Sitemap — `website_download/sitemap.xml`

Add 4 new `<url>` blocks, priority `0.80`, `changefreq` weekly, `lastmod` 2026-06-24:
- `https://ipu.co.in/cuet-bjmc-admission-ipu.php`
- `https://ipu.co.in/cuet-ba-economics-admission-ipu.php`
- `https://ipu.co.in/cuet-bca-admission-ipu.php`
- `https://ipu.co.in/cuet-ba-english-admission-ipu.php`

### 4e. llms.txt — `website_download/llms.txt`

Under the existing `## IPU CUET Admission Pages` section, add 4 new entries matching existing entry format.

---

## 5. Template clone rules

When cloning `cuet-bba-admission-ipu.php`:
- Change all `bba` / `BBA` / `Business Administration` → programme-specific strings
- Change programme code 125 → new code
- Change CUET paper table rows → new programme's papers
- Change `$related_pages` array → link to hub + the other 3 new pages + BBA/B.Com as contextually relevant
- `$last_updated` = `'2026-06-24'`
- `$hero_show_form` = `false` (enforced — no second form on page)
- All JSON-LD: update `@type CourseInstance`, `name`, `description`, `dateModified`
- Do NOT modify any shared include (`base-head.php`, `base-nav.php`, `base-footer.php`, etc.)
- Do NOT add any new PHP functions or helpers — use only patterns already in the template

---

## 6. Deploy plan

1. PHP lint all 8 changed/new files — zero errors required
2. `php -S localhost:8000` → curl-verify all 8 pages return 200
3. Walk hub → each new page → back to hub via related_pages links
4. Walk nav/footer CTAs on one new page (crosslink test)
5. **Owner go-ahead gate**
6. FTP push via `deploy.py` — new 4 files + 4 surgical + hub + sitemap + llms.txt (11 files)
7. Curl-verify all 9 live URLs return HTTP 200
8. Verify brochure caveat string present on each live page

---

## 7. Out of scope

- Any change to H1/title/meta/canonical/URL on existing 4 CUET pages
- New pages for B.Pharm, BMS, or any programme not listed above
- 3-yr LLB (Code 238) — no CUET pathway (explicitly excluded per brochure + existing law page)
- Any structural change to shared includes
