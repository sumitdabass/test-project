# IPU PG Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refresh `mba-admission-ip-university.php` and `mca-admission-ipu.php` with PG-brochure-cited specifics, and ship a new evergreen `law-3-year-admission-ipu.php` (Programme Code 238).

**Architecture:** Vanilla PHP files under `website_download/`. No framework, no build step. Brochure data is the only authoritative source for fees, eligibility, intake, entrance accept-list — all numeric/policy claims must be brochure-cited. Cross-link, sitemap, llms.txt, blog.php updates follow the established CUET-pages pattern (see `upload_cuet_pages_2026.py`).

**Tech Stack:** PHP 8.2 (vanilla), Python 3.12 (FTP deploy), `qpdf` (PDF repair), Read tool (chunked PDF extraction). No tests; verification is `php -l`, `xmllint`, local `php -S` smoke, grep checks for citation strings, and post-deploy `curl -sI`.

**Source spec:** `docs/superpowers/specs/2026-05-06-ipu-pg-pages-design.md`.

**Terminology rule:** User-facing copy, H1, H2, breadcrumbs, meta, FAQ all use **"Law (3-Year)"** — never "LLB 3-yr" or "LLB 3-year". Brochure-citation phrasing remains "Programme Code 238 — Bachelor of Laws (3-Year)".

**Prod-or-not warning:** `/Users/Sumit/test-project` is the live ipu.co.in site. Every FTP deploy goes live immediately to `~50K monthly impression` audience. No staging.

---

### Task 1: Repair the PG brochure and build a chapter map

**Files:**
- Read: `/Users/Sumit/Desktop/PG 2026 broucher.pdf` (51 MB, damaged)
- Create: `/tmp/pg_brochure_2026.pdf` (qpdf-repaired, linearized)
- Create: `/tmp/pg_chapter_map.md` (scratch, not committed)

- [ ] **Step 1: Repair the brochure with qpdf**

```bash
qpdf --linearize "/Users/Sumit/Desktop/PG 2026 broucher.pdf" /tmp/pg_brochure_2026.pdf
pdfinfo /tmp/pg_brochure_2026.pdf | grep -E '^Pages|^Page size'
```

Expected: `qpdf` exits 0 (or 0-with-warnings), `pdfinfo` reports page count (likely ~150–250 pages based on UG brochure). Note the page count.

- [ ] **Step 2: Read the table of contents (first 6 pages)**

Use the Read tool: `Read /tmp/pg_brochure_2026.pdf pages 1-6`. Capture:
- Chapter index: which chapter covers eligibility, which covers entrance accept-list, which covers fees, which covers intake/seat distribution, which covers management quota.
- Programme Code list: confirm MBA code (likely **104** but verify), MCA = **105**, Law (3-Year) = **238**.

- [ ] **Step 3: Build the chapter map at `/tmp/pg_chapter_map.md`**

Format:
```
# PG 2026 Brochure — Chapter Map (scratch)
- Eligibility: Ch X (pages A–B)
- Entrance accept-list: Ch X (pages A–B)
- Fees (USS): Ch X (pages A–B)
- Fees (affiliated, 6th SFRC notification 14.07.2025): Ch X
- Intake/seat distribution: Ch X (pages A–B)
- Management quota: Instruction #21 + Ch 12
- Programme Codes:
  - MBA: 104 (verified)
  - MCA: 105 (verified)
  - Law (3-Year): 238 (verified)
```

This map is the source of truth for citations in Tasks 4, 6, 8.

- [ ] **Step 4: Verify Programme Codes appear at expected pages**

Use Read tool to spot-check: open the page where the Programme Code 238 row appears in the Programme Codes index. Confirm the row reads "Bachelor of Laws (3-Year)" and notes the duration as 3 years.

- [ ] **Step 5: Commit the spec reference (no chapter-map commit)**

The chapter map is scratch; not committed. No git activity for this task.

---

### Task 2: Read Law (3-Year)–relevant brochure pages

**Files:**
- Read: `/tmp/pg_brochure_2026.pdf` chapters identified in Task 1
- Create: `/tmp/pg_law3yr_data.md` (scratch, not committed)

- [ ] **Step 1: Read eligibility chapter for Programme Code 238**

Use the Read tool with the page range from `pg_chapter_map.md`. Capture verbatim:
- Minimum eligibility (graduation discipline, min %, SC/ST/OBC/PwD relaxation).
- BCI rules (Open School ineligibility — confirm BCI:D:1823/2010 still cited).
- Final-year provisional rule.

- [ ] **Step 2: Read entrance accept-list for Programme Code 238**

Capture: which entrance test is required (IPU CET LLB), test pattern, syllabus, marking, paper code if listed.

- [ ] **Step 3: Read fees chapter for USLLS + affiliated Law (3-Year) colleges**

Capture: USLLS USS annual fee figure verbatim; affiliated college fee citation phrasing (must include "6th SFRC notification dated 14.07.2025" if brochure uses that).

- [ ] **Step 4: Read intake chapter for Law (3-Year)**

Capture: list of colleges offering Programme Code 238 + seat counts each.

- [ ] **Step 5: Save the extracted data to `/tmp/pg_law3yr_data.md`**

Plain markdown with sections matching the new page H2 structure. This is the authoritative source for Task 4 — every claim on the new page must trace back here.

---

### Task 3: Read MBA + MCA brochure pages

**Files:**
- Read: `/tmp/pg_brochure_2026.pdf`
- Create: `/tmp/pg_mba_data.md`, `/tmp/pg_mca_data.md` (scratch, not committed)

- [ ] **Step 1: Read MBA-relevant brochure pages**

For Programme Code 104 (or whatever the brochure assigns), capture:
- Eligibility (50% bachelor + relaxation; final-year provisional rule).
- Entrance accept-list (CAT/CMAT/MAT/IPU CET MBA — verify exact list per brochure).
- USMS USS fee figure verbatim.
- Affiliated college fee citation ("6th SFRC notification dated 14.07.2025").
- Intake by college: USMS, MAIMS, RDIAS, JIMS at minimum.

Save to `/tmp/pg_mba_data.md`.

- [ ] **Step 2: Read MCA-relevant brochure pages**

For Programme Code 105:
- Eligibility (BCA / B.Sc with Maths / equivalent + min % + relaxation).
- Entrance accept-list (NIMCET / IPU CET PG — verify).
- USICT USS fee figure verbatim.
- Affiliated college fee citation.
- Intake: USICT + 12+ affiliated colleges.

Save to `/tmp/pg_mca_data.md`.

- [ ] **Step 3: Verify the citations**

Cross-check the figures against the brochure pages — a single typo on a fee number is a public-trust failure. Re-read the relevant lines if uncertain.

---

### Task 4: Build the new Law (3-Year) page

**Files:**
- Create: `website_download/law-3-year-admission-ipu.php`
- Reference: `website_download/cuet-law-admission-ipu.php` (template)
- Reference: `website_download/cuet-bba-admission-ipu.php` (closest CUET-page template per memory `project_ipu_cuet_pages_20260506.md`)

- [ ] **Step 1: Copy the CUET-Law page as a structural starter**

```bash
cp website_download/cuet-law-admission-ipu.php website_download/law-3-year-admission-ipu.php
```

Open the new file and adapt — do NOT leave any CUET-specific copy intact.

- [ ] **Step 2: Update head + meta**

Replace:
- `<title>` → `Law (3-Year) Admission IPU 2026 (Programme Code 238) | Eligibility, Fees, Top Colleges`
- `<meta name="description">` → fits 155 chars, mentions "Law (3-Year)", "Programme Code 238", "IPU CET LLB", "Rs. <USLLS USS fee>", "9899991342".
- `<link rel="canonical">` → `https://ipu.co.in/law-3-year-admission-ipu.php`
- Open Graph + Twitter Card tags → match new title/description/URL.
- `<meta name="robots">` → `index, follow`.

- [ ] **Step 3: Update breadcrumb + breadcrumb schema**

Replace breadcrumbs array:

```php
$breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], ['Law (3-Year) Admission', '']];
```

The breadcrumb schema include in `/include/components/breadcrumb-schema.php` consumes this array — no separate JSON-LD edit required.

- [ ] **Step 4: Replace the Course schema JSON-LD**

```php
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Law (3-Year) at IPU (Programme Code 238)",
  "description": "Bachelor of Laws (3-Year) at Guru Gobind Singh Indraprastha University. BCI-recognised; admission via IPU CET LLB. Open to graduates of any discipline meeting BCI eligibility rules.",
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University",
    "sameAs": "https://www.ipu.ac.in"
  },
  "courseCode": "238"
}
</script>
```

- [ ] **Step 5: Replace the hero block**

Adapt the existing hero include pattern:

```php
<?php
$hero_title = "Law (3-Year) Admission IPU 2026 (Programme Code 238)";
$hero_breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], ['Law (3-Year) Admission', '']];
$hero_compact = true;
include "include/components/hero.php";
?>
```

(If the CUET-Law page uses a different hero include path, match that path verbatim — do not invent a new one.)

- [ ] **Step 6: Write the H2 sections in order (Hero → How Admission Works → Eligibility → IPU CET LLB → Top Colleges → Management Quota → Fees → Step-by-Step → FAQ)**

Use `/tmp/pg_law3yr_data.md` as the citation source. Specific must-haves:

- "How Admission Works": one paragraph stating IPU CET LLB is the entry route; explicitly note CUET is **not** accepted for Programme Code 238 (carry over the BCI:D:1823/2010 cite from the CUET-Law page exclusion note).
- "Eligibility": bullet list ending with "Source: PG Brochure 2026-27, Ch \[X\]".
- "Fees": separate paragraphs for USLLS (USS) vs affiliated; affiliated paragraph ends with "Source: 6th SFRC notification dated 14.07.2025".
- Every numeric claim has a "Source: …" tail.

- [ ] **Step 7: Add the FAQ block (5–6 Qs) with FAQPage JSON-LD**

Each FAQ Q–A pair must be present **both** in the visible accordion HTML AND in the JSON-LD `mainEntity` array. Drop in the JSON-LD ABOVE the visible accordion.

Suggested Qs (final wording at write time, brochure-cited answers):
1. What is Programme Code 238?
2. Who is eligible for Law (3-Year) admission at IPU?
3. Which entrance test is required?
4. Is CUET accepted for Programme Code 238?
5. What is the IPU Law (3-Year) fee structure?
6. Which colleges offer Law (3-Year) under IPU?

- [ ] **Step 8: Add the hidden AI summary block**

Just above `</body>`, before any closing `<?php include ... footer ?>`:

```php
<section id="ai-summary" style="display:none;" aria-hidden="true">
  <h2>Law (3-Year) at IPU — AI Summary</h2>
  <ul>
    <li>Programme Code 238 — Bachelor of Laws (3-Year), BCI-recognised, offered at GGSIPU.</li>
    <li>Eligibility: graduation in any discipline with brochure-mandated minimum percentage; SC/ST/OBC/PwD relaxation per brochure.</li>
    <li>Admission via IPU CET LLB. CUET is not accepted (BCI:D:1823/2010).</li>
    <li>USLLS USS fee 2026-27: Rs. <X> per year (Source: PG Brochure 2026-27 Ch \[Fees\]).</li>
    <li>Affiliated colleges: fee per 6th SFRC notification dated 14.07.2025.</li>
    <li>Helpline: 9899991342 for free admission guidance.</li>
  </ul>
</section>
```

- [ ] **Step 9: Verify every citation source line is real**

Search the file:

```bash
grep -nE 'Source:|Programme Code 238|BCI:D:1823/2010|6th SFRC' website_download/law-3-year-admission-ipu.php
```

Expected: at least one Programme Code 238 mention, at least one BCI:D:1823/2010 cite (in "How Admission Works" or related FAQ), at least one 6th SFRC mention. If any are missing, fix before moving on.

- [ ] **Step 10: PHP lint**

```bash
php -l website_download/law-3-year-admission-ipu.php
```

Expected: `No syntax errors detected`. If errors: read the line numbers, fix, re-lint until clean. Do NOT proceed with errors.

- [ ] **Step 11: Commit**

```bash
git add website_download/law-3-year-admission-ipu.php
git commit -m "feat(law-3yr): new Programme Code 238 admission page

Brochure-cited new evergreen page for IPU Law (3-Year) — IPU CET LLB
entry route, CUET excluded per BCI:D:1823/2010, 6th SFRC fee citation."
```

---

### Task 5: Surgical refresh of `mba-admission-ip-university.php`

**Files:**
- Modify: `website_download/mba-admission-ip-university.php`
- Reference: `/tmp/pg_mba_data.md`

- [ ] **Step 1: Capture the current SEO title + description as a baseline**

```bash
grep -nE '<title>|<meta name="description"' website_download/mba-admission-ip-university.php | head -4
```

Save the output. The rule from the spec: **only edit title/description if a number changes**. Default is leave-untouched to protect CTR.

- [ ] **Step 2: Update H1 to include Programme Code**

Find the H1 (likely inside the hero block or first `<h1>` tag). Prepend the brochure Programme Code:

```
MBA Admission IPU 2026 (Programme Code <NN>)
```

If the H1 is rendered through a `$hero_title` variable, edit the variable assignment.

- [ ] **Step 3: Replace the eligibility section with brochure-cited copy**

Locate the eligibility section. Replace generic "50%/45%" wording with the brochure-exact text. End the section with a `<p class="cite-source">Source: PG Brochure 2026-27, Ch [X]</p>` line.

- [ ] **Step 4: Replace the entrance accept-list**

Locate the entrance section. Replace any guesswork with the brochure-exact accept-list. Cite chapter at the end of the section.

- [ ] **Step 5: Replace USMS USS fee figure**

Search for any `Rs. 1,30,000`, `Rs. 1.3L`, `1,30,000-1,40,000` style placeholders. Replace with brochure-exact USMS figure. Add `Source: PG Brochure 2026-27, Ch [Fees]` at the end of the fee paragraph.

- [ ] **Step 6: Update affiliated college fees**

Replace any approximated affiliated college figures with brochure data, ending the paragraph with: `Source: 6th SFRC notification dated 14.07.2025`.

- [ ] **Step 7: Update intake by college section (if present)**

If the page lists intake/seat numbers, replace with brochure-exact figures from `/tmp/pg_mba_data.md`. Add the chapter cite.

- [ ] **Step 8: Update 2 FAQ answers (visible + JSON-LD parity)**

Pick the 2 most-numeric FAQ answers (likely "fees" and "eligibility"). Update the answer text in BOTH the visible accordion HTML and the FAQPage JSON-LD `acceptedAnswer.text`. Verify they match character-for-character on the substantive claim.

- [ ] **Step 9: Add the hidden AI summary block**

Just above `</body>`, append the same `<section id="ai-summary">` pattern from Task 4 Step 8, with MBA-specific bullets sourced from `/tmp/pg_mba_data.md`.

- [ ] **Step 10: Bump `dateModified` if Article schema is present**

```bash
grep -n '"dateModified"' website_download/mba-admission-ip-university.php
```

If matched, change the value to `"2026-05-06"`. If no match, skip.

- [ ] **Step 11: Re-check title/description**

If any change in Step 4 or 5 altered the displayed numeric value the meta-description quotes, update meta description. Otherwise leave both untouched.

- [ ] **Step 12: PHP lint**

```bash
php -l website_download/mba-admission-ip-university.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 13: Verify citation strings present**

```bash
grep -cE 'Source: PG Brochure|6th SFRC notification|Programme Code' website_download/mba-admission-ip-university.php
```

Expected: ≥ 3 matches across the file.

- [ ] **Step 14: Commit**

```bash
git add website_download/mba-admission-ip-university.php
git commit -m "seo(mba): surgical refresh with PG brochure citations

Programme Code in H1, brochure-cited eligibility/fees/intake,
6th SFRC notification cite for affiliated college fees, AI summary
block, 2 FAQs refreshed with schema parity."
```

---

### Task 6: Surgical refresh of `mca-admission-ipu.php`

**Files:**
- Modify: `website_download/mca-admission-ipu.php`
- Reference: `/tmp/pg_mca_data.md`

- [ ] **Step 1: Verify Programme Code 105 on the page**

```bash
grep -n 'Programme Code 105\|Code 105\|courseCode' website_download/mca-admission-ipu.php
```

Confirm Code 105 appears in H1 and Course schema. If missing, add to H1 (mirror MBA Step 2 pattern).

- [ ] **Step 2: Refresh the eligibility section**

Locate the eligibility section. Replace with brochure-exact wording from `/tmp/pg_mca_data.md` (BCA / B.Sc with Maths / equivalent + min % + relaxation). End with chapter cite.

- [ ] **Step 3: Refresh the entrance accept-list**

Replace with brochure-exact list (NIMCET / IPU CET PG — confirm verbatim). End with chapter cite.

- [ ] **Step 4: Replace USICT USS fee figure**

Replace any approximated figures with brochure-exact. End with chapter cite.

- [ ] **Step 5: Update affiliated college fees**

Same pattern as MBA Step 6. End with `Source: 6th SFRC notification dated 14.07.2025`.

- [ ] **Step 6: Update intake/seat section**

Replace with brochure-exact intake from `/tmp/pg_mca_data.md`.

- [ ] **Step 7: Update 1–2 FAQ answers (visible + JSON-LD parity)**

Same pattern as MBA Step 8.

- [ ] **Step 8: Add the hidden AI summary block**

Same pattern as MBA Step 9, MCA-specific bullets.

- [ ] **Step 9: Bump `dateModified` if Article schema is present**

Same pattern as MBA Step 10.

- [ ] **Step 10: PHP lint**

```bash
php -l website_download/mca-admission-ipu.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 11: Verify citation strings present**

```bash
grep -cE 'Source: PG Brochure|6th SFRC notification|Programme Code 105' website_download/mca-admission-ipu.php
```

Expected: ≥ 3 matches.

- [ ] **Step 12: Commit**

```bash
git add website_download/mca-admission-ipu.php
git commit -m "seo(mca): surgical refresh with PG brochure citations

Brochure-cited eligibility/fees/intake for Programme Code 105,
6th SFRC notification cite for affiliated college fees, AI summary
block, FAQs refreshed with schema parity."
```

---

### Task 7: Cross-link insertions (5 files)

**Files:**
- Modify: `website_download/IPU-Law-Admission.php`
- Modify: `website_download/cuet-law-admission-ipu.php` (replace exclusion note)
- Modify: `website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php`
- Modify: `website_download/ultimate-guide-to-ballb-admission-in-ip-university.php`
- Modify: `website_download/top-law-colleges-ipu.php`

Single-sentence pointer style — no buttons, no visually-separate CTAs, blends into surrounding paragraph. Per `feedback_unobtrusive-links.md`.

- [ ] **Step 1: Pointer in `IPU-Law-Admission.php`**

Find a paragraph near the top of the body that mentions law admission paths. Insert one sentence:

```php
<p>If you have already completed your graduation, see the <a href="/law-3-year-admission-ipu.php">Law (3-Year) admission page</a> for the Programme Code 238 path through IPU CET LLB.</p>
```

- [ ] **Step 2: Pointer in `cuet-law-admission-ipu.php`**

Find the existing exclusion note (per memory: "3-yr LLB (Code 238) does NOT accept CUET"). Replace with:

```php
<p>The 3-year LLB (Programme Code 238) does not accept CUET (per BCI:D:1823/2010); see the <a href="/law-3-year-admission-ipu.php">Law (3-Year) admission page</a> for the IPU CET LLB entry route.</p>
```

The BCI:D:1823/2010 cite is preserved — net loss zero.

- [ ] **Step 3: Pointer in `comprehensive-guide-to-bballb-admission-in-ip-university.php`**

Find a "who should apply" or "alternative paths" section. Insert:

```php
<p>If you are already a graduate, the <a href="/law-3-year-admission-ipu.php">Law (3-Year) admission</a> route (Programme Code 238) may be a faster path than the 5-year integrated programme.</p>
```

- [ ] **Step 4: Pointer in `ultimate-guide-to-ballb-admission-in-ip-university.php`**

Same framing as Step 3, similar placement.

- [ ] **Step 5: Pointer in `top-law-colleges-ipu.php`**

Find the intro paragraph. Insert:

```php
<p>Looking for the 3-year graduate path? See the <a href="/law-3-year-admission-ipu.php">Law (3-Year) admission</a> page for Programme Code 238 details.</p>
```

- [ ] **Step 6: PHP lint all 5 modified files**

```bash
php -l website_download/IPU-Law-Admission.php
php -l website_download/cuet-law-admission-ipu.php
php -l website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php
php -l website_download/ultimate-guide-to-ballb-admission-in-ip-university.php
php -l website_download/top-law-colleges-ipu.php
```

Expected: 5 × `No syntax errors detected`.

- [ ] **Step 7: Verify each file links to the new page**

```bash
for f in website_download/IPU-Law-Admission.php website_download/cuet-law-admission-ipu.php website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php website_download/ultimate-guide-to-ballb-admission-in-ip-university.php website_download/top-law-colleges-ipu.php; do
  echo "$f: $(grep -c law-3-year-admission-ipu "$f")"
done
```

Expected: each line ends with `: 1` (or higher).

- [ ] **Step 8: Commit**

```bash
git add website_download/IPU-Law-Admission.php website_download/cuet-law-admission-ipu.php website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php website_download/ultimate-guide-to-ballb-admission-in-ip-university.php website_download/top-law-colleges-ipu.php
git commit -m "seo(law): cross-link 5 law pages to new Law (3-Year) page

Single-sentence pointers (no buttons), per unobtrusive-links rule.
CUET-Law page replaces its exclusion note with a pointer that
preserves the BCI:D:1823/2010 citation."
```

---

### Task 8: Site wiring — sitemap, llms.txt, blog.php

**Files:**
- Modify: `website_download/sitemap.xml`
- Modify: `website_download/llms.txt`
- Modify: `website_download/blog.php`

- [ ] **Step 1: Add new entry to `sitemap.xml`**

Locate a similar evergreen entry (e.g. `cuet-admission-ipu.php`) for indentation reference. Append before `</urlset>`:

```xml
  <url>
    <loc>https://ipu.co.in/law-3-year-admission-ipu.php</loc>
    <lastmod>2026-05-06</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.85</priority>
  </url>
```

Also bump `<lastmod>` to `2026-05-06` for the existing `mba-admission-ip-university.php` and `mca-admission-ipu.php` entries.

- [ ] **Step 2: Validate the sitemap**

```bash
xmllint --noout website_download/sitemap.xml
```

Expected: silent (no output = valid). If error: fix the indentation/closing tag issue, re-run.

- [ ] **Step 3: Add new section to `llms.txt`**

Locate the existing CUET section as the indentation/format reference. Add a new section:

```
## IPU Law (3-Year) Admission

- Law (3-Year) at IPU (Programme Code 238) — graduate-entry 3-year LLB via IPU CET LLB. CUET not accepted (BCI:D:1823/2010). USLLS + affiliated colleges. https://ipu.co.in/law-3-year-admission-ipu.php
```

Bump or update the dates for the existing MBA / MCA entries to 2026-05-06.

- [ ] **Step 4: Add the new page to `blog.php` Law category**

Open `blog.php`. Locate the `$blogs` array (line 12 area, per the grep in the spec). Add a new entry, format-matched to the existing Law-category entries:

```php
["category"=>"Law","title"=>"IPU Law (3-Year) Admission 2026: Programme Code 238 Eligibility, Fees & Top Colleges","url"=>"law-3-year-admission-ipu.php","img"=>"assets/images/IPU-Law-Admission-2025.jpg","alt"=>"IPU Law (3-Year) Admission","excerpt"=>"Complete guide to Programme Code 238 — eligibility, IPU CET LLB, fees per 6th SFRC notification, and top colleges.","read_time"=>"7"],
```

(Use the existing `IPU-Law-Admission-2025.jpg` image — no new asset needed for this batch.)

- [ ] **Step 5: PHP lint blog.php**

```bash
php -l website_download/blog.php
```

Expected: `No syntax errors detected`.

- [ ] **Step 6: Verify all 3 wiring files reference the new page**

```bash
grep -c law-3-year-admission-ipu website_download/sitemap.xml website_download/llms.txt website_download/blog.php
```

Expected: each line ends with `:1` or higher.

- [ ] **Step 7: Commit**

```bash
git add website_download/sitemap.xml website_download/llms.txt website_download/blog.php
git commit -m "seo(wiring): add Law (3-Year) to sitemap + llms.txt + blog index

New entry priority 0.85 in sitemap; new section in llms.txt;
Law-category blog tile linking to law-3-year-admission-ipu.php.
MBA + MCA lastmod bumped to 2026-05-06."
```

---

### Task 9: Pre-deploy local smoke test

**Files:**
- Read-only: every file modified or created in Tasks 4–8

- [ ] **Step 1: Start the local PHP dev server**

```bash
php -S localhost:8765 -t website_download &
sleep 2
```

- [ ] **Step 2: Smoke-load the new page**

```bash
curl -sI http://localhost:8765/law-3-year-admission-ipu.php | head -1
```

Expected: `HTTP/1.1 200 OK`.

- [ ] **Step 3: Smoke-load the refreshed pages**

```bash
curl -sI http://localhost:8765/mba-admission-ip-university.php | head -1
curl -sI http://localhost:8765/mca-admission-ipu.php | head -1
```

Expected: both `HTTP/1.1 200 OK`.

- [ ] **Step 4: Smoke-load the 5 cross-linked pages**

```bash
for p in IPU-Law-Admission.php cuet-law-admission-ipu.php comprehensive-guide-to-bballb-admission-in-ip-university.php ultimate-guide-to-ballb-admission-in-ip-university.php top-law-colleges-ipu.php; do
  echo "$p: $(curl -sI http://localhost:8765/$p | head -1)"
done
```

Expected: each line shows `200 OK`.

- [ ] **Step 5: Body-content check on the new page**

```bash
curl -s http://localhost:8765/law-3-year-admission-ipu.php | grep -cE 'Programme Code 238|BCI:D:1823/2010|6th SFRC'
```

Expected: ≥ 3 matches (Programme Code present, BCI cite present, SFRC cite present).

- [ ] **Step 6: JSON-LD validity check on the new page**

```bash
curl -s http://localhost:8765/law-3-year-admission-ipu.php | python3 -c '
import sys, re, json
html = sys.stdin.read()
blocks = re.findall(r"<script type=\"application/ld\+json\">(.*?)</script>", html, re.DOTALL)
print(f"Found {len(blocks)} JSON-LD blocks")
for i, b in enumerate(blocks):
    try:
        obj = json.loads(b.strip())
        print(f"  Block {i}: type={obj.get(\"@type\")} OK")
    except Exception as e:
        print(f"  Block {i}: INVALID — {e}")
        sys.exit(1)
'
```

Expected: 3 blocks (Course, FAQPage, BreadcrumbList) — all "OK", no "INVALID".

- [ ] **Step 7: Stop the dev server**

```bash
kill %1 2>/dev/null || true
```

- [ ] **Step 8: Visual smoke (manual)**

Open `http://localhost:8765/law-3-year-admission-ipu.php` in a browser. Confirm:
- Hero renders with title "Law (3-Year) Admission IPU 2026 (Programme Code 238)".
- Breadcrumb shows Home > Admissions > Law (3-Year) Admission.
- FAQ accordion expands/collapses.
- Sidebar form is present (with a `phone` field and submit button).
- No PHP warnings/notices anywhere on the page.

If any visual regression: stop, do not proceed to deploy. Fix and rerun this task.

---

### Task 10: Build and run the FTP deploy script

**Files:**
- Create: `upload_pg_pages_2026_05_06.py`

- [ ] **Step 1: Write the deploy script**

Create `upload_pg_pages_2026_05_06.py` matching the convention of `upload_cuet_pages_2026.py` (FTP creds inline, FILES_TO_UPLOAD dict, abort on failure):

```python
#!/usr/bin/env python3
"""
PG Pages Deploy - 2026-05-06

- New: law-3-year-admission-ipu.php (Programme Code 238)
- Refreshed: mba-admission-ip-university.php, mca-admission-ipu.php
- Cross-link: IPU-Law-Admission, cuet-law-admission, comprehensive-guide-to-bballb,
  ultimate-guide-to-ballb, top-law-colleges
- Site wiring: sitemap.xml, llms.txt, blog.php
"""
import ftplib, os, sys, time

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = {
    "": [
        "law-3-year-admission-ipu.php",
        "mba-admission-ip-university.php",
        "mca-admission-ipu.php",
        "IPU-Law-Admission.php",
        "cuet-law-admission-ipu.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
        "top-law-colleges-ipu.php",
        "sitemap.xml",
        "llms.txt",
        "blog.php",
    ],
}


def main():
    started = time.time()
    print(f"[*] Connecting to {FTP_HOST} as {FTP_USER} ...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"[+] Connected. CWD = {ftp.pwd()}")

    total = ok = 0
    for subdir, files in FILES_TO_UPLOAD.items():
        target = FTP_REMOTE_PATH + ("/" + subdir if subdir else "")
        ftp.cwd(target)
        for fname in files:
            total += 1
            local = os.path.join(LOCAL_BASE, subdir, fname) if subdir else os.path.join(LOCAL_BASE, fname)
            if not os.path.exists(local):
                print(f"[!] MISSING: {local}"); continue
            size = os.path.getsize(local)
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR {fname}", f)
            print(f"[+] {fname} ({size:,} bytes)")
            ok += 1

    ftp.quit()
    print("")
    print(f"[=] {ok}/{total} files uploaded in {time.time()-started:.1f}s")
    if ok != total:
        sys.exit(1)


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Confirm the deploy is intentional (per "carefully consider blast radius")**

Prompt the user before running — this is a live-prod deploy. Do not run unattended.

- [ ] **Step 3: Run the deploy**

```bash
python3 upload_pg_pages_2026_05_06.py
```

Expected: `[=] 11/11 files uploaded in <T>s`. If `ok != total`, the script exits 1 — investigate the missing/failed file before retry. Do NOT proceed to Task 11 if any file failed to upload.

- [ ] **Step 4: Commit the deploy script**

```bash
git add upload_pg_pages_2026_05_06.py
git commit -m "deploy(pg-pages): FTP script for 2026-05-06 PG pages batch

11 files: 1 new (Law 3-Year) + 2 refreshed (MBA/MCA) + 5 cross-linked
+ 3 site-wiring (sitemap/llms/blog). Aborts on any STOR failure."
```

---

### Task 11: Post-deploy verification

**Files:**
- Read-only: live ipu.co.in URLs

- [ ] **Step 1: HTTP 200 on the new page**

```bash
curl -sI https://ipu.co.in/law-3-year-admission-ipu.php | head -1
```

Expected: `HTTP/2 200`. If 404 — Hostinger PHP-FPM OPcache caveat applies (per `reference_hostinger_fpm_opcache.md`). Trigger a PHP version toggle via cPanel MultiPHP Manager to clear FPM OPcache, then retry. If still 404, check the FTP STOR log from Task 10.

- [ ] **Step 2: HTTP 200 on the refreshed pages**

```bash
curl -sI https://ipu.co.in/mba-admission-ip-university.php | head -1
curl -sI https://ipu.co.in/mca-admission-ipu.php | head -1
```

Expected: both `HTTP/2 200`.

- [ ] **Step 3: HTTP 200 on the 5 cross-linked pages**

```bash
for p in IPU-Law-Admission.php cuet-law-admission-ipu.php comprehensive-guide-to-bballb-admission-in-ip-university.php ultimate-guide-to-ballb-admission-in-ip-university.php top-law-colleges-ipu.php; do
  echo "$p: $(curl -sI https://ipu.co.in/$p | head -1)"
done
```

Expected: each `HTTP/2 200`.

- [ ] **Step 4: Citation strings live on the new page**

```bash
curl -s https://ipu.co.in/law-3-year-admission-ipu.php | grep -cE 'Programme Code 238|BCI:D:1823/2010|6th SFRC notification'
```

Expected: ≥ 3.

- [ ] **Step 5: Cross-links live**

```bash
for p in IPU-Law-Admission.php cuet-law-admission-ipu.php comprehensive-guide-to-bballb-admission-in-ip-university.php ultimate-guide-to-ballb-admission-in-ip-university.php top-law-colleges-ipu.php; do
  echo "$p: $(curl -s https://ipu.co.in/$p | grep -c law-3-year-admission-ipu)"
done
```

Expected: each line ends with `: 1` or higher.

- [ ] **Step 6: Sitemap entry live**

```bash
curl -s https://ipu.co.in/sitemap.xml | grep -c law-3-year-admission-ipu
```

Expected: `1`.

- [ ] **Step 7: llms.txt entry live**

```bash
curl -s https://ipu.co.in/llms.txt | grep -c law-3-year-admission-ipu
```

Expected: `1`.

- [ ] **Step 8: blog.php Law category includes the new page**

```bash
curl -s https://ipu.co.in/blog.php | grep -c law-3-year-admission-ipu
```

Expected: ≥ 1 (the blog tile renders the URL).

- [ ] **Step 9: JSON-LD validity on the new page (live)**

```bash
curl -s https://ipu.co.in/law-3-year-admission-ipu.php | python3 -c '
import sys, re, json
html = sys.stdin.read()
blocks = re.findall(r"<script type=\"application/ld\+json\">(.*?)</script>", html, re.DOTALL)
print(f"Found {len(blocks)} JSON-LD blocks")
for i, b in enumerate(blocks):
    try:
        obj = json.loads(b.strip())
        print(f"  Block {i}: type={obj.get(\"@type\")} OK")
    except Exception as e:
        print(f"  Block {i}: INVALID — {e}")
        sys.exit(1)
'
```

Expected: 3 blocks, all OK.

- [ ] **Step 10: Stop on any failure**

If any verification step fails, do NOT proceed to Task 12. Rerun the relevant remediation (FPM toggle for 404, redeploy specific file if STOR was incomplete, etc.). Re-run all of Task 11 from Step 1 after remediation.

---

### Task 12: Memory update

**Files:**
- Create: `/Users/Sumit/.claude/projects/-Users-Sumit/memory/project_ipu_pg_pages_20260506.md`
- Modify: `/Users/Sumit/.claude/projects/-Users-Sumit/memory/MEMORY.md` (add index pointer)

- [ ] **Step 1: Write the project memory**

```markdown
---
name: ipu.co.in PG admission pages shipped 2026-05-06
description: MBA refresh + MCA refresh + new Law (3-Year) page (Programme Code 238) — 11 files deployed live, brochure-cited from PG 2026-27
type: project
---
**Shipped to ipu.co.in 2026-05-06** (11 files, deploy script `upload_pg_pages_2026_05_06.py`):

**Why:** User requested PG-brochure-cited pages for MBA, MCA, and Law (3-Year). MBA + MCA existing pages refreshed surgically (preserve rankings); Law (3-Year) (Programme Code 238) was missing, built from CUET-page template.

**1 new page:**
- `law-3-year-admission-ipu.php` — Programme Code 238 — Bachelor of Laws (3-Year), IPU CET LLB entry route, CUET excluded per BCI:D:1823/2010

**2 refreshed pages:**
- `mba-admission-ip-university.php` — Programme Code <NN>, brochure-cited eligibility/fees/intake, 6th SFRC affiliated cite, AI summary block, FAQ schema parity preserved
- `mca-admission-ipu.php` — Programme Code 105, same refresh pattern

**5 cross-link edits:** IPU-Law-Admission, cuet-law-admission-ipu, comprehensive-guide-to-bballb, ultimate-guide-to-ballb, top-law-colleges. Single-sentence pointers per unobtrusive-links rule.

**3 site-wiring updates:** sitemap.xml (priority 0.85 + lastmod bumps), llms.txt (new "## IPU Law (3-Year) Admission" section), blog.php (Law-category tile).

**Terminology:** "Law (3-Year)" everywhere user-facing; "Bachelor of Laws (3-Year)" + "Programme Code 238" for brochure citations.

**Brochure handling:** PG 2026-27 brochure damaged → qpdf --linearize → /tmp/pg_brochure_2026.pdf → Read in 20-page chunks. Chapter map noted at /tmp/pg_chapter_map.md (scratch).

**Verification:** PHP lint clean on all 8 modified PHP files, xmllint clean on sitemap, local php -S smoke 200 on all 8 pages, JSON-LD parse OK on new page, FTP push 11/11, post-deploy curl HTTP 200 on all 8 + citation strings live + cross-links live + sitemap/llms/blog entries live.

**How to apply:** When adding more PG-brochure pages (M.Tech / MBA-IB / MJMC / etc.), clone `law-3-year-admission-ipu.php` H2 structure. PG brochure chapter map (filled in at deploy time): see `project_ipu_pg_pages_20260506.md` body.
```

- [ ] **Step 2: Add a pointer line to MEMORY.md**

Open `/Users/Sumit/.claude/projects/-Users-Sumit/memory/MEMORY.md`. Find the existing "ipu CUET pages shipped 2026-05-06" line under the Projects section. Insert directly below:

```
- [ipu PG pages shipped 2026-05-06](project_ipu_pg_pages_20260506.md) — MBA refresh + MCA refresh + new Law (3-Year) page (Code 238); brochure-cited; deploy script `upload_pg_pages_2026_05_06.py`
```

- [ ] **Step 3: No git commit needed**

The memory directory is outside the test-project repo. No commit step.

---

## Self-Review Notes

- **Spec coverage:** All 8 sections of the spec map to tasks (Page 1 → Task 5; Page 2 → Task 6; Page 3 → Task 4; Cross-linking → Task 7; Site wiring → Task 8; Deploy script → Task 10; Verification → Tasks 9 + 11; Memory → Task 12). Brochure handling note → Task 1. Out-of-scope items confirmed not in plan.
- **Placeholder scan:** "<NN>" used twice for the MBA Programme Code (verified at Task 1 Step 2 — surfaced by name not by guess). "<X>" used in template citation strings — these are intentional placeholders to be filled with brochure values at write time, with explicit instruction to source them from `/tmp/pg_*_data.md`.
- **Type/name consistency:** The new page filename `law-3-year-admission-ipu.php` is identical across all tasks (Task 4 creates, Tasks 7/8/10 reference). Programme Code 238 cited identically. "6th SFRC notification dated 14.07.2025" phrasing verbatim across all tasks.
- **Risks acknowledged in plan body:** Hostinger FPM OPcache 404 → Task 11 Step 1 has the remediation. Live-prod deploy → Task 10 Step 2 prompts user before running.
