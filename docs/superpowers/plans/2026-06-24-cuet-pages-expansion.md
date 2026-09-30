# CUET Pages Expansion Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add 4 new CUET admission pages (BJMC / BA Eco / BCA / BA English) + surgical updates to 4 existing CUET pages + hub update + sitemap + llms.txt.

**Architecture:** Each new PHP page is a direct clone of `cuet-bba-admission-ipu.php` with programme-specific substitutions. Existing pages get only 2 surgical edits: `$last_updated` + caveat sentence. No new includes, no new helpers, no new PHP functions.

**Tech Stack:** PHP 8.2 vanilla, shared includes at `include/`, `deploy.py` for FTP push.

## Global Constraints

- NEVER change title/meta/canonical/H1/H2/URL on existing ranking pages — only `$last_updated` + caveat on existing 4 files.
- `$hero_show_form = false` on every page — no second enquiry form.
- All new URLs are evergreen (no year in slug).
- PHP lint must be clean before deploy — `php -l filename.php` exit 0.
- Template source of truth: `website_download/cuet-bba-admission-ipu.php` (293 lines).
- All brochure citations: GGSIPU UG Admission Brochure 2026-27.
- CUET subject caveat (standard wording to use on all pages): `Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.`
- New page `$last_updated` = `'2026-06-24'`.
- Working directory for all commands: `/Users/Sumit/test-project/website_download/`

---

### Task 1: Surgical updates — 4 existing CUET pages

**Files:**
- Modify: `website_download/cuet-btech-admission-ipu.php:49` (`$last_updated`)
- Modify: `website_download/cuet-btech-admission-ipu.php:105` (caveat)
- Modify: `website_download/cuet-bba-admission-ipu.php:49` (`$last_updated`)
- Modify: `website_download/cuet-bba-admission-ipu.php:102` (caveat)
- Modify: `website_download/cuet-bcom-admission-ipu.php:49` (`$last_updated`)
- Modify: `website_download/cuet-bcom-admission-ipu.php:106` (caveat)
- Modify: `website_download/cuet-law-admission-ipu.php:49` (`$last_updated`)
- Modify: `website_download/cuet-law-admission-ipu.php:108` (caveat)

**Interfaces:**
- Produces: 4 files with freshened `$last_updated` date + standardised caveat

- [ ] **Step 1: Update $last_updated on all 4 existing pages**

In each of the 4 files, find the line:
```php
<?php $last_updated = '2026-05-06'; include 'include/components/last-updated.php'; ?>
```
Replace with:
```php
<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>
```

- [ ] **Step 2: Standardise caveat sentence on all 4 existing pages**

In each of the 4 files, find the paragraph immediately after the CUET subject papers list that contains `Per Section 1.2 of the brochure`. The exact text varies per page but each has one such `<p style="font-size:13px...">` caveat paragraph. Replace the entire `<p>` tag with this standardised version:

```html
<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>
```

Exact strings to find per file:
- `cuet-btech-admission-ipu.php`: `<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>` — **already correct on B.Tech; skip this file**.
- `cuet-bba-admission-ipu.php` line 102: `<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: paper-mapping shown above is from CUET 2025; final CUET 2026 paper structure will be notified by GGSIPU before counselling.</em></p>`
- `cuet-bcom-admission-ipu.php` line 106: `<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: paper-mapping shown above is from CUET 2025; final CUET 2026 paper structure will be notified by GGSIPU before counselling.</em></p>`
- `cuet-law-admission-ipu.php` line 108: same pattern as BBA/B.Com.

Replace all three (BBA, B.Com, Law) old caveat `<p>` with the standardised version from Step 2.

- [ ] **Step 3: PHP lint all 4 files**

```bash
cd /Users/Sumit/test-project/website_download
php -l cuet-btech-admission-ipu.php && php -l cuet-bba-admission-ipu.php && php -l cuet-bcom-admission-ipu.php && php -l cuet-law-admission-ipu.php
```
Expected: `No syntax errors detected` for all 4.

- [ ] **Step 4: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-btech-admission-ipu.php website_download/cuet-bba-admission-ipu.php website_download/cuet-bcom-admission-ipu.php website_download/cuet-law-admission-ipu.php
git commit -m "content: refresh last_updated + standardise CUET caveat on 4 existing pages"
```

---

### Task 2: New page — cuet-bjmc-admission-ipu.php

**Files:**
- Create: `website_download/cuet-bjmc-admission-ipu.php`

**Interfaces:**
- Produces: live BJMC CUET page at `https://ipu.co.in/cuet-bjmc-admission-ipu.php`

- [ ] **Step 1: Create the file with this exact content**

```php
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_cache_limiter('public'); session_cache_expire(30); session_start(); }
include_once("include/base-head.php");
include_once("include/form-handler.php");
?>

<!-- SEO META -->
<title>IPU BJMC Admission Through CUET (UG) — Eligibility, Subjects, Colleges</title>

<meta name="description" content="GGSIPU BA(JMC)/BJMC via CUET — Mass Media + General Aptitude papers, vacant-seat counselling after IPU CET, management quota route. Free guidance: 9899991342.">

<meta name="robots" content="index, follow">
<link rel="canonical" href="https://ipu.co.in/cuet-bjmc-admission-ipu.php" />

</head>

<body>

<?php include_once("include/base-nav.php"); ?>

<?php
$hero_h1 = 'IPU BJMC Admission Through CUET (UG): Eligibility, Subjects &amp; College List';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>

<section class="blog-wrapper pt-130 pb-130">

<div class="container">

<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="cuet-admission-ipu.php">CUET Admission</a></li>
<li class="breadcrumb-item active">BJMC via CUET</li>
</ol>
</nav>

<div class="row">

<div class="col-lg-8">
<div class="blog-details">

<img fetchpriority="high" decoding="async" width="1000" height="600" src="assets/images/IPU-BJMC-Admission.jpg"
class="main-img"
alt="BJMC / BA(JMC) admission through CUET at GGSIPU — subject papers and eligible colleges">

<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>

<!-- AI Summary -->
<section id="ai-summary" style="display:none">
<p>BA(JMC) / BJMC admission at GGSIPU (Programme Code 126 — BA Journalism &amp; Mass Communication / BA JMC) is granted on the merit of <strong>IPU CET 2026</strong>. Per Important Instruction #37 of the 2026-27 brochure, vacant BJMC seats remaining after IPU CET counselling are filled on the merit of <strong>CUET (UG) 2026</strong>. CUET-qualified candidates also become eligible for the 10% management quota seats at every unaided affiliated BJMC college (Important Instruction #21 + Chapter 12). Eligibility: Class 12 with 50% aggregate (best 4 subjects including English), pass in English. CUET subject papers required: Section IA English (Code 101), Section II Mass Media &amp; Mass Communication (Code 318), Section III General Aptitude Test (Code 501). Top colleges: USMC (USS — no management quota), VIPS-TC, JIMS, MAIMS, Bharati Vidyapeeth. Free admission counselling: 9899991342.</p>
</section>

<h2>How CUET Works for IPU BJMC 2026-27</h2>

<p>
Per Section 1.1 ("At a Glance") of the GGSIPU UG Admission Brochure 2026-27, BA(JMC) / BJMC admission at IP University (Programme Code <strong>126</strong> &mdash; BA Journalism &amp; Mass Communication) is structured as a <strong>two-tier merit list</strong>:
</p>

<ol>
<li><strong>First preference:</strong> <strong>GGSIPU CET 2026</strong> conducted by the University.</li>
<li><strong>Second pathway:</strong> <strong>CUET (UG) 2026 #</strong> &mdash; used to fill vacant seats remaining after IPU CET counselling rounds (Important Instruction #37).</li>
</ol>

<p>
A BJMC aspirant who only has a CUET score (and not IPU CET) is still admissible &mdash; through the CUET vacant-seat round and through the management quota route at IPU's affiliated BJMC colleges.
</p>

<hr>

<h2>Eligibility for BJMC via CUET (Programme Code 126)</h2>

<p>Per Chapter 2 of the brochure:</p>

<ul>
<li>Pass in <strong>Class 12 (10+2)</strong> of CBSE or equivalent with a minimum <strong>50% marks in aggregate</strong>* (best 4 subjects including English).</li>
<li>The candidate must also have passed <strong>English (core / elective / functional)</strong> as a subject in the qualifying examination.</li>
<li>Marks are <strong>not rounded off</strong> &mdash; 49.99% will not become 50% (Important Instruction #28).</li>
<li>Reserved categories (SC/ST/OBC/PwD): 5% relaxation per university policy.</li>
</ul>

<p style="font-size:13px;color:#666"><em>* "Aggregate of 50% marks in the 12th class for the purpose of eligibility will be taken as the aggregate of the best four subjects (unless otherwise specified) including English and compulsory subject(s), if any" — Chapter 2, "For Graduation Programmes" footnote.</em></p>

<hr>

<h2>CUET (UG) Subject Papers Required for IPU BJMC</h2>

<p>Per Chapter 2, Programme Code 126 (Domain Specific Subjects column), candidates appearing in CUET (UG) for IPU BJMC should attempt <strong>at least one</strong> of the following:</p>

<ul>
<li><strong>Section IA &mdash; English (Code 101)</strong> &mdash; recommended language paper since English is a compulsory eligibility subject.</li>
<li><strong>Section II &mdash; Mass Media &amp; Mass Communication (Code 318)</strong></li>
<li><strong>Section III &mdash; General Aptitude Test (Code 501)</strong></li>
</ul>

<p>
Combining all three (English + Mass Media + General Aptitude) gives the strongest profile for the IPU CUET vacant-seat round and for management-quota applications. Candidates who did not study Mass Communication in Class 12 can rely on English + General Aptitude Test.
</p>

<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>

<hr>

<h2>BJMC Programme at IPU</h2>

<p>Programme Code 126 covers <strong>BA(JMC) &mdash; Bachelor of Arts (Journalism &amp; Mass Communication)</strong>, a 4-year undergraduate programme:</p>

<ul>
<li>BA(JMC) &mdash; Journalism &amp; Mass Communication (general)</li>
<li>Specialisations vary by college: Print Media, Electronic Media, Advertising &amp; PR, Digital Media</li>
<li>USMC (University School of Mass Communication), Dwarka &mdash; the University's flagship BJMC school</li>
</ul>

<hr>

<h2>Top IPU BJMC Colleges That Accept CUET (Vacant Seat Round)</h2>

<ul>
<li><strong>USMC (University School of Mass Communication)</strong> &mdash; Dwarka campus (USS; no management quota — USS is excluded)</li>
<li><a href="vips-admission.php"><strong>VIPS-TC Pitampura</strong></a> &mdash; BA(JMC)</li>
<li>JIMS Rohini &mdash; BA(JMC)</li>
<li>JIMS Kalkaji &mdash; BA(JMC)</li>
<li>MAIMS Rohini &mdash; BA(JMC)</li>
<li>Bharati Vidyapeeth &mdash; BA(JMC)</li>
<li><a href="cpj-admission.php"><strong>CPJ College Narela</strong></a> &mdash; BA(JMC)</li>
</ul>

<p>
👉 Full list: <a href="ipu-colleges-list.php"><strong>All IPU Affiliated Colleges</strong></a>
</p>

<hr>

<h2>Management Quota Through CUET — BJMC</h2>

<p>
Per Chapter 12 of the brochure, every unaided affiliated BJMC college reserves <strong>10% of its total seats</strong> as Management Quota. A valid CUET (UG) score is one of the three accepted qualifiers:
</p>

<ul>
<li><strong>Eligibility:</strong> Class 12 with 50% aggregate (best 4 including English) <em>plus</em> a valid <strong>IPU CET / CUET</strong> score (Instruction #21 + Chapter 12 Note 2).</li>
<li><strong>Aggregate calculation for BJMC:</strong> Per Chapter 12 — <em>"for programmes like BBA, BHMCT, BCA &amp; BA(JMC) for calculating aggregate marks obtained in the qualifying examination, aggregate marks of <strong>best 4 subjects</strong> should be taken which includes English."</em></li>
<li><strong>Application:</strong> Apply directly to the college during the 18-day window advertised in 2 newspapers (Hindi + English).</li>
<li><strong>Registration fee cap:</strong> Rs. 2,500 per the Admission Regulatory Committee.</li>
<li><strong>Last date:</strong> 9 calendar days after the regular reporting date of the last GGSIPU counselling round.</li>
</ul>

<p>
👉 Detailed guide: <a href="IP-University-management-quota-admission-eligibility-criteria.php"><strong>IPU Management Quota Admission</strong></a>
</p>

<hr>

<h2>Fee Structure 2026-27 (CUET-Admitted BJMC Students)</h2>

<ul>
<li><strong>USMC (USS) BJMC:</strong> USS fee structure 2026-27 as per Chapter 14 of the brochure; check ipu.ac.in for the current fee notification (USS is not open to management quota).</li>
<li><strong>Affiliated BJMC colleges:</strong> Tuition typically per the 6th SFRC Delhi Gazette Notification dated 14.07.2025, plus university charges, exam fee, alumni and welfare contributions.</li>
<li><strong>Management quota seats</strong> follow the same regulated tuition. Capitation fee is illegal under the Delhi Professional Colleges Act, 2007.</li>
</ul>

<hr>

<h2>Step-by-Step: Get BJMC Admission via CUET</h2>

<ol>
<li><strong>Register for CUET (UG) 2026</strong> at cuet.nta.nic.in. Choose Section IA English (101), Section II Mass Media &amp; Mass Communication (318), Section III General Aptitude Test (501).</li>
<li>Appear in CUET (UG) 2026 on the scheduled date.</li>
<li><strong>Wait for GGSIPU notification</strong> announcing the CUET-based counselling round. This typically opens after IPU CET vacancies are reported.</li>
<li><strong>Register on ipu.admissions.nic.in</strong> using your CUET roll number, fill college choices in order of preference, lock preferences.</li>
<li>If allotted, pay the Part Academic Fee, complete online document verification, report to the allotted college.</li>
<li><strong>Parallel track &mdash; Management Quota:</strong> Apply directly during each college's advertised 18-day management-quota window with Class 12 marksheet, CUET scorecard, ID and photo.</li>
</ol>

<hr>

<h2>Need Free CUET-to-IPU BJMC Admission Help?</h2>

<p>
We have placed hundreds of students into IPU BJMC / BA(JMC) colleges through CUET vacant-seat and management quota counselling.
</p>

<p>
<strong>Call our team for free 1-on-1 guidance:</strong><br>
<b><?php include("include/phone.php"); ?></b><br>
<em>Mon&ndash;Sat, 9 AM &ndash; 7 PM</em>
</p>

</div>
</div>

<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>

</div>
</div>

</section>

<!-- FAQ Section (sourced from GGSIPU UG Admission Brochure 2026-27) -->
<?php
$faqs = [
  ['question' => 'Can I get BJMC admission at IPU only with a CUET score?', 'answer' => 'Yes, through two routes. <strong>(1) Vacant-seat round</strong>: per Important Instruction #37, GGSIPU runs IPU CET counselling first; whatever BJMC seats remain unfilled are then offered on CUET merit. <strong>(2) Management Quota</strong>: per Important Instruction #21 + Chapter 12, every unaided affiliated BJMC college (VIPS-TC, JIMS, MAIMS, CPJ etc.) reserves 10% seats as management quota where a valid CUET score is one of the accepted qualifiers. The candidate must still meet the Class 12 50% aggregate eligibility (best 4 including English).'],
  ['question' => 'Which CUET (UG) subject papers should I attempt for IPU BJMC?', 'answer' => 'Per Chapter 2, Programme Code 126 of the 2026-27 brochure, attempt <strong>at least one</strong> of: <strong>Section IA — English (Code 101)</strong>, <strong>Section II — Mass Media &amp; Mass Communication (Code 318)</strong>, <strong>Section III — General Aptitude Test (Code 501)</strong>. The strongest combination is all three. Candidates who did not study Mass Communication in Class 12 can use English + General Aptitude Test.'],
  ['question' => 'What is the eligibility for BJMC / BA(JMC) admission at IPU 2026-27?', 'answer' => 'Pass in Class 12 of CBSE or equivalent with a minimum aggregate of <strong>50% marks</strong> (best 4 subjects including English). Must have passed English (core/elective/functional) as a subject. Reserved category candidates get a 5% relaxation per university policy. Marks are not rounded off (Instruction #28).'],
  ['question' => 'What is the Mass Media subject code in CUET for IPU BJMC?', 'answer' => '<strong>Mass Media &amp; Mass Communication is Code 318</strong> under Section II of CUET (UG). This is the domain-specific subject paper relevant to BA(JMC) / BJMC. Combined with Section IA English (101) and Section III General Aptitude Test (501), this gives the strongest CUET profile for IPU BJMC vacant-seat and management quota counselling.'],
  ['question' => 'Which IPU BJMC colleges are most likely to have CUET vacant seats?', 'answer' => 'Vacant-seat availability changes year to year. USMC (USS) usually fills in IPU CET rounds. Tier-2 affiliated colleges historically have more seats remaining after CET rounds — watch VIPS-TC, JIMS, MAIMS, CPJ College and Bharati Vidyapeeth. Apply to multiple colleges to maximise chances.'],
  ['question' => 'Will GGSIPU notify a separate CUET counselling schedule for BJMC?', 'answer' => 'Yes. Per Section 1.2 of the brochure, CUET-based admissions are carried out after exhausting the IPU CET merit list. GGSIPU will publish a separate notification for CUET vacant-seat counselling. Watch ipu.ac.in and ipu.admissions.nic.in after the IPU CET result.']
];
include 'include/components/faq-section.php';
?>

<?php
$related_pages = [
    ['title' => 'CUET Admission Hub', 'url' => '/cuet-admission-ipu.php', 'desc' => 'Complete CUET-to-IPU guide for all 8 programmes'],
    ['title' => 'IPU BBA Admission Through CUET', 'url' => '/cuet-bba-admission-ipu.php', 'desc' => 'BBA via CUET — Business Studies subject paper'],
    ['title' => 'IPU BCA Admission Through CUET', 'url' => '/cuet-bca-admission-ipu.php', 'desc' => 'BCA via CUET — Maths + CS/Informatics papers'],
    ['title' => 'IPU Management Quota Admission', 'url' => '/IP-University-management-quota-admission-eligibility-criteria.php', 'desc' => '10% reserved seats at every unaided IPU college'],
    ['title' => 'IPU Helpline 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission guidance — Mon–Sat 9 AM–7 PM'],
    ['title' => 'All IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => '60+ IPU affiliated colleges in Delhi NCR'],
];
include 'include/components/related-pages.php';
?>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Bachelor of Arts in Journalism & Mass Communication BA(JMC) at GGSIPU — Admission Through CUET (UG)",
  "description": "4-year BA(JMC) / BJMC programme at Guru Gobind Singh Indraprastha University admitting through GGSIPU CET 2026 (primary) and CUET (UG) for vacant-seat filling and management quota qualification, per the 2026-27 admission brochure.",
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University (GGSIPU)",
    "sameAs": "https://www.ipu.ac.in/"
  },
  "educationalCredentialAwarded": "Bachelor of Arts in Journalism & Mass Communication BA(JMC)",
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "Onsite",
    "courseWorkload": "P4Y",
    "location": {"@type": "Place", "name": "Delhi NCR"}
  }
}
</script>

<?php include_once("include/base-footer.php"); ?>

<!-- Article Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"IPU BJMC Admission Through CUET (UG) — Eligibility, Subjects & College List",
"publisher":{"@type":"Organization","name":"ipu.co.in"},
"mainEntityOfPage":{"@type":"WebPage","@id":"https://ipu.co.in/cuet-bjmc-admission-ipu.php"}
}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"BreadcrumbList",
"itemListElement":[
{"@type":"ListItem","position":1,"name":"Home","item":"https://ipu.co.in/"},
{"@type":"ListItem","position":2,"name":"CUET Admission","item":"https://ipu.co.in/cuet-admission-ipu.php"},
{"@type":"ListItem","position":3,"name":"BJMC via CUET","item":"https://ipu.co.in/cuet-bjmc-admission-ipu.php"}
]
}
</script>

</body>
</html>
```

- [ ] **Step 2: PHP lint**

```bash
php -l /Users/Sumit/test-project/website_download/cuet-bjmc-admission-ipu.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-bjmc-admission-ipu.php
git commit -m "feat: add BJMC CUET admission page (Programme Code 126)"
```

---

### Task 3: New page — cuet-ba-economics-admission-ipu.php

**Files:**
- Create: `website_download/cuet-ba-economics-admission-ipu.php`

**Interfaces:**
- Produces: live BA Economics CUET page at `https://ipu.co.in/cuet-ba-economics-admission-ipu.php`

- [ ] **Step 1: Create the file with this exact content**

```php
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_cache_limiter('public'); session_cache_expire(30); session_start(); }
include_once("include/base-head.php");
include_once("include/form-handler.php");
?>

<!-- SEO META -->
<title>IPU BA Economics Admission Through CUET (UG) — Eligibility, Subjects, Colleges</title>

<meta name="description" content="GGSIPU BA Economics (Hons) via CUET — Economics and Maths domain papers, vacant-seat counselling after IPU CET, management quota route. Free guidance: 9899991342.">

<meta name="robots" content="index, follow">
<link rel="canonical" href="https://ipu.co.in/cuet-ba-economics-admission-ipu.php" />

</head>

<body>

<?php include_once("include/base-nav.php"); ?>

<?php
$hero_h1 = 'IPU BA Economics Admission Through CUET (UG): Eligibility, Subjects &amp; College List';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>

<section class="blog-wrapper pt-130 pb-130">

<div class="container">

<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="cuet-admission-ipu.php">CUET Admission</a></li>
<li class="breadcrumb-item active">BA Economics via CUET</li>
</ol>
</nav>

<div class="row">

<div class="col-lg-8">
<div class="blog-details">

<img fetchpriority="high" decoding="async" width="1000" height="600" src="assets/images/economics-admission-2025.jpg"
class="main-img"
alt="BA Economics admission through CUET at GGSIPU — subject papers and eligible colleges">

<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>

<!-- AI Summary -->
<section id="ai-summary" style="display:none">
<p>BA Economics (Hons) admission at GGSIPU (Programme Code 197) is granted on the merit of <strong>IPU CET 2026</strong>. Per Important Instruction #37 of the 2026-27 brochure, vacant BA Economics seats remaining after IPU CET counselling are filled on the merit of <strong>CUET (UG) 2026</strong>. CUET-qualified candidates also become eligible for the 10% management quota seats at every unaided affiliated BA Economics college (Important Instruction #21 + Chapter 12). Eligibility: Class 12 with 50% aggregate (best 4 subjects including English), pass in English. CUET subject papers required: Section IA English (Code 101), Section II Economics / Business Economics (Code 309) or Mathematics / Applied Mathematics (Code 319). Free admission counselling: 9899991342.</p>
</section>

<h2>How CUET Works for IPU BA Economics 2026-27</h2>

<p>
Per Section 1.1 ("At a Glance") of the GGSIPU UG Admission Brochure 2026-27, BA Economics (Hons) admission at IP University (Programme Code <strong>197</strong>) is structured as a <strong>two-tier merit list</strong>:
</p>

<ol>
<li><strong>First preference:</strong> <strong>GGSIPU CET 2026</strong> conducted by the University.</li>
<li><strong>Second pathway:</strong> <strong>CUET (UG) 2026 #</strong> &mdash; used to fill vacant seats remaining after IPU CET counselling rounds (Important Instruction #37).</li>
</ol>

<p>
A BA Economics aspirant who only has a CUET score (and not IPU CET) is still admissible &mdash; through the CUET vacant-seat round and through the management quota route at IPU's affiliated colleges offering Economics.
</p>

<hr>

<h2>Eligibility for BA Economics via CUET (Programme Code 197)</h2>

<p>Per Chapter 2 of the brochure:</p>

<ul>
<li>Pass in <strong>Class 12 (10+2)</strong> of CBSE or equivalent with a minimum <strong>50% marks in aggregate</strong>* (best 4 subjects including English).</li>
<li>The candidate must also have passed <strong>English (core / elective / functional)</strong> as a subject in the qualifying examination.</li>
<li>Marks are <strong>not rounded off</strong> &mdash; 49.99% will not become 50% (Important Instruction #28).</li>
<li>Reserved categories (SC/ST/OBC/PwD): 5% relaxation per university policy.</li>
</ul>

<p style="font-size:13px;color:#666"><em>* "Aggregate of 50% marks in the 12th class for the purpose of eligibility will be taken as the aggregate of the best four subjects (unless otherwise specified) including English and compulsory subject(s), if any" — Chapter 2, "For Graduation Programmes" footnote.</em></p>

<hr>

<h2>CUET (UG) Subject Papers Required for IPU BA Economics</h2>

<p>Per Chapter 2, Programme Code 197 (Domain Specific Subjects column), candidates appearing in CUET (UG) for IPU BA Economics should attempt <strong>at least one</strong> of the following:</p>

<ul>
<li><strong>Section IA &mdash; English (Code 101)</strong> &mdash; recommended language paper since English is a compulsory eligibility subject.</li>
<li><strong>Section II &mdash; Economics / Business Economics (Code 309)</strong></li>
<li><strong>Section II &mdash; Mathematics / Applied Mathematics (Code 319)</strong></li>
</ul>

<p>
Combining English + Economics + Mathematics (all three) gives the strongest CUET profile for IPU BA Economics vacant-seat and management quota counselling. Candidates with Commerce background should attempt Economics (309); candidates with Science background should attempt Mathematics (319). Attempting both domain papers further strengthens the application.
</p>

<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>

<hr>

<h2>BA Economics Programme at IPU</h2>

<p>Programme Code 197 covers <strong>BA Economics (Honours)</strong>, a 4-year undergraduate programme:</p>

<ul>
<li>BA Economics (Hons) &mdash; 4-year undergraduate programme under the National Education Policy 2020 (NEP) framework</li>
<li>Offered at USSHSS (University School of Humanities &amp; Social Sciences), Dwarka, and select affiliated colleges</li>
<li>Covers Microeconomics, Macroeconomics, Indian Economy, Statistics for Economics, Development Economics, and electives</li>
</ul>

<hr>

<h2>Top IPU BA Economics Colleges That Accept CUET (Vacant Seat Round)</h2>

<ul>
<li><strong>USSHSS (University School of Humanities &amp; Social Sciences)</strong> &mdash; Dwarka campus (USS; no management quota)</li>
<li>Select affiliated colleges offering BA Economics &mdash; see <a href="ipu-colleges-list.php">full IPU college list</a> for current availability</li>
</ul>

<p>
👉 Full list: <a href="ipu-colleges-list.php"><strong>All IPU Affiliated Colleges</strong></a>
</p>

<hr>

<h2>Management Quota Through CUET — BA Economics</h2>

<p>
Per Chapter 12 of the brochure, every unaided affiliated college reserves <strong>10% of its total seats</strong> as Management Quota. A valid CUET (UG) score is one of the three accepted qualifiers:
</p>

<ul>
<li><strong>Eligibility:</strong> Class 12 with 50% aggregate (best 4 including English) <em>plus</em> a valid <strong>IPU CET / CUET</strong> score (Instruction #21 + Chapter 12 Note 2).</li>
<li><strong>Application:</strong> Apply directly to the college during the 18-day window advertised in 2 newspapers (Hindi + English).</li>
<li><strong>Registration fee cap:</strong> Rs. 2,500 per the Admission Regulatory Committee.</li>
<li><strong>Last date:</strong> 9 calendar days after the regular reporting date of the last GGSIPU counselling round.</li>
</ul>

<p>
👉 Detailed guide: <a href="IP-University-management-quota-admission-eligibility-criteria.php"><strong>IPU Management Quota Admission</strong></a>
</p>

<hr>

<h2>Fee Structure 2026-27 (CUET-Admitted BA Economics Students)</h2>

<ul>
<li><strong>USSHSS (USS) BA Economics:</strong> USS fee structure 2026-27 as per Chapter 14 of the brochure; check ipu.ac.in for the current fee notification (USS is not open to management quota).</li>
<li><strong>Affiliated colleges:</strong> Tuition per the 6th SFRC Delhi Gazette Notification dated 14.07.2025, plus university charges, exam fee, alumni and welfare contributions.</li>
<li><strong>Management quota seats</strong> follow the same regulated tuition. Capitation fee is illegal under the Delhi Professional Colleges Act, 2007.</li>
</ul>

<hr>

<h2>Step-by-Step: Get BA Economics Admission via CUET</h2>

<ol>
<li><strong>Register for CUET (UG) 2026</strong> at cuet.nta.nic.in. Choose Section IA English (101), Section II Economics / Business Economics (309) and/or Mathematics / Applied Mathematics (319).</li>
<li>Appear in CUET (UG) 2026 on the scheduled date.</li>
<li><strong>Wait for GGSIPU notification</strong> announcing the CUET-based counselling round. This typically opens after IPU CET vacancies are reported.</li>
<li><strong>Register on ipu.admissions.nic.in</strong> using your CUET roll number, fill college choices in order of preference, lock preferences.</li>
<li>If allotted, pay the Part Academic Fee, complete online document verification, report to the allotted college.</li>
<li><strong>Parallel track &mdash; Management Quota:</strong> Apply directly during each college's advertised 18-day management-quota window with Class 12 marksheet, CUET scorecard, ID and photo.</li>
</ol>

<hr>

<h2>Need Free CUET-to-IPU BA Economics Admission Help?</h2>

<p>
We have guided thousands of students into IPU undergraduate programmes through CUET vacant-seat and management quota counselling.
</p>

<p>
<strong>Call our team for free 1-on-1 guidance:</strong><br>
<b><?php include("include/phone.php"); ?></b><br>
<em>Mon&ndash;Sat, 9 AM &ndash; 7 PM</em>
</p>

</div>
</div>

<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>

</div>
</div>

</section>

<!-- FAQ Section (sourced from GGSIPU UG Admission Brochure 2026-27) -->
<?php
$faqs = [
  ['question' => 'Can I get BA Economics admission at IPU only with a CUET score?', 'answer' => 'Yes, through two routes. <strong>(1) Vacant-seat round</strong>: per Important Instruction #37, GGSIPU runs IPU CET counselling first; whatever BA Economics seats remain unfilled are then offered on CUET merit. <strong>(2) Management Quota</strong>: per Important Instruction #21 + Chapter 12, every unaided affiliated college reserves 10% seats as management quota where a valid CUET score is one of the accepted qualifiers. The candidate must still meet the Class 12 50% aggregate eligibility (best 4 including English).'],
  ['question' => 'Which CUET (UG) subject papers should I attempt for IPU BA Economics?', 'answer' => 'Per Chapter 2, Programme Code 197 of the 2026-27 brochure, attempt: <strong>Section IA — English (Code 101)</strong>, and at least one of <strong>Section II — Economics / Business Economics (Code 309)</strong> or <strong>Section II — Mathematics / Applied Mathematics (Code 319)</strong>. Attempting all three gives the strongest profile. Commerce-stream students should prioritise Economics (309); Science-stream students should prioritise Mathematics (319).'],
  ['question' => 'What is the eligibility for BA Economics admission at IPU 2026-27?', 'answer' => 'Pass in Class 12 of CBSE or equivalent with a minimum aggregate of <strong>50% marks</strong> (best 4 subjects including English). Must have passed English (core/elective/functional) as a subject. Reserved category candidates get a 5% relaxation per university policy. Marks are not rounded off (Instruction #28).'],
  ['question' => 'Do I need Mathematics in Class 12 for BA Economics at IPU?', 'answer' => 'No, Mathematics is not a mandatory Class 12 subject for BA Economics eligibility at IPU. The eligibility requires Class 12 pass with 50% aggregate (best 4 including English) — stream is not restricted. However, taking CUET Mathematics paper (Code 319) in addition to Economics (309) can strengthen your CUET profile for IPU BA Economics admission.'],
  ['question' => 'Which colleges offer BA Economics (Hons) at IPU?', 'answer' => 'USSHSS (University School of Humanities &amp; Social Sciences) at Dwarka is IPU\'s own school for BA Economics. A limited number of affiliated colleges also offer BA Economics under Programme Code 197. Check ipu.ac.in for the current year\'s participating college list.'],
  ['question' => 'Will GGSIPU notify a separate CUET counselling schedule for BA Economics?', 'answer' => 'Yes. Per Section 1.2 of the brochure, CUET-based admissions are carried out after exhausting the IPU CET merit list. GGSIPU will publish a separate notification for CUET vacant-seat counselling. Watch ipu.ac.in and ipu.admissions.nic.in after the IPU CET result.']
];
include 'include/components/faq-section.php';
?>

<?php
$related_pages = [
    ['title' => 'CUET Admission Hub', 'url' => '/cuet-admission-ipu.php', 'desc' => 'Complete CUET-to-IPU guide for all 8 programmes'],
    ['title' => 'IPU B.Com Admission Through CUET', 'url' => '/cuet-bcom-admission-ipu.php', 'desc' => 'B.Com Hons via CUET — Accountancy subject paper'],
    ['title' => 'IPU BA English Admission Through CUET', 'url' => '/cuet-ba-english-admission-ipu.php', 'desc' => 'BA English Hons via CUET — English Language paper'],
    ['title' => 'IPU Management Quota Admission', 'url' => '/IP-University-management-quota-admission-eligibility-criteria.php', 'desc' => '10% reserved seats at every unaided IPU college'],
    ['title' => 'IPU Helpline 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission guidance — Mon–Sat 9 AM–7 PM'],
    ['title' => 'All IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => '60+ IPU affiliated colleges in Delhi NCR'],
];
include 'include/components/related-pages.php';
?>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Bachelor of Arts in Economics (Hons) at GGSIPU — Admission Through CUET (UG)",
  "description": "4-year BA Economics (Hons) programme at Guru Gobind Singh Indraprastha University admitting through GGSIPU CET 2026 (primary) and CUET (UG) for vacant-seat filling and management quota qualification, per the 2026-27 admission brochure.",
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University (GGSIPU)",
    "sameAs": "https://www.ipu.ac.in/"
  },
  "educationalCredentialAwarded": "Bachelor of Arts in Economics (Honours)",
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "Onsite",
    "courseWorkload": "P4Y",
    "location": {"@type": "Place", "name": "Delhi NCR"}
  }
}
</script>

<?php include_once("include/base-footer.php"); ?>

<!-- Article Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"IPU BA Economics Admission Through CUET (UG) — Eligibility, Subjects & College List",
"publisher":{"@type":"Organization","name":"ipu.co.in"},
"mainEntityOfPage":{"@type":"WebPage","@id":"https://ipu.co.in/cuet-ba-economics-admission-ipu.php"}
}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"BreadcrumbList",
"itemListElement":[
{"@type":"ListItem","position":1,"name":"Home","item":"https://ipu.co.in/"},
{"@type":"ListItem","position":2,"name":"CUET Admission","item":"https://ipu.co.in/cuet-admission-ipu.php"},
{"@type":"ListItem","position":3,"name":"BA Economics via CUET","item":"https://ipu.co.in/cuet-ba-economics-admission-ipu.php"}
]
}
</script>

</body>
</html>
```

- [ ] **Step 2: PHP lint**

```bash
php -l /Users/Sumit/test-project/website_download/cuet-ba-economics-admission-ipu.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-ba-economics-admission-ipu.php
git commit -m "feat: add BA Economics CUET admission page (Programme Code 197)"
```

---

### Task 4: New page — cuet-bca-admission-ipu.php

**Files:**
- Create: `website_download/cuet-bca-admission-ipu.php`

**Interfaces:**
- Produces: live BCA CUET page at `https://ipu.co.in/cuet-bca-admission-ipu.php`

- [ ] **Step 1: Create the file with this exact content**

```php
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_cache_limiter('public'); session_cache_expire(30); session_start(); }
include_once("include/base-head.php");
include_once("include/form-handler.php");
?>

<!-- SEO META -->
<title>IPU BCA Admission Through CUET (UG) — Eligibility, Subjects, Colleges</title>

<meta name="description" content="GGSIPU BCA via CUET — Maths + Computer Science/Informatics Practices papers, vacant-seat counselling after IPU CET, management quota route. Free guidance: 9899991342.">

<meta name="robots" content="index, follow">
<link rel="canonical" href="https://ipu.co.in/cuet-bca-admission-ipu.php" />

</head>

<body>

<?php include_once("include/base-nav.php"); ?>

<?php
$hero_h1 = 'IPU BCA Admission Through CUET (UG): Eligibility, Subjects &amp; College List';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>

<section class="blog-wrapper pt-130 pb-130">

<div class="container">

<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="cuet-admission-ipu.php">CUET Admission</a></li>
<li class="breadcrumb-item active">BCA via CUET</li>
</ol>
</nav>

<div class="row">

<div class="col-lg-8">
<div class="blog-details">

<img fetchpriority="high" decoding="async" width="470" height="343" src="assets/images/bca.jpg"
class="main-img"
alt="BCA admission through CUET at GGSIPU — subject papers and eligible colleges">

<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>

<!-- AI Summary -->
<section id="ai-summary" style="display:none">
<p>BCA admission at GGSIPU (Programme Code 114) is granted on the merit of <strong>IPU CET 2026</strong>. Per Important Instruction #37 of the 2026-27 brochure, vacant BCA seats remaining after IPU CET counselling are filled on the merit of <strong>CUET (UG) 2026</strong>. CUET-qualified candidates also become eligible for the 10% management quota seats at every unaided affiliated BCA college (Important Instruction #21 + Chapter 12). Eligibility: Class 12 with 50% aggregate (best 4 subjects including English), pass in English. CUET subject papers required: Section IA English (Code 101), Section II Mathematics / Applied Mathematics (Code 319), Section II Computer Science / Informatics Practices (Code 308), Section III General Aptitude Test (Code 501). Top colleges: BVICAM, USICT (no management quota), JIMS, Fairfield, CPJ. Free admission counselling: 9899991342.</p>
</section>

<h2>How CUET Works for IPU BCA 2026-27</h2>

<p>
Per Section 1.1 ("At a Glance") of the GGSIPU UG Admission Brochure 2026-27, BCA admission at IP University (Programme Code <strong>114</strong>) is structured as a <strong>two-tier merit list</strong>:
</p>

<ol>
<li><strong>First preference:</strong> <strong>GGSIPU CET 2026</strong> conducted by the University.</li>
<li><strong>Second pathway:</strong> <strong>CUET (UG) 2026 #</strong> &mdash; used to fill vacant seats remaining after IPU CET counselling rounds (Important Instruction #37).</li>
</ol>

<p>
A BCA aspirant who only has a CUET score (and not IPU CET) is still admissible &mdash; through the CUET vacant-seat round and through the management quota route at IPU's affiliated BCA colleges.
</p>

<hr>

<h2>Eligibility for BCA via CUET (Programme Code 114)</h2>

<p>Per Chapter 2 of the brochure:</p>

<ul>
<li>Pass in <strong>Class 12 (10+2)</strong> of CBSE or equivalent with a minimum <strong>50% marks in aggregate</strong>* (best 4 subjects including English).</li>
<li>The candidate must also have passed <strong>English (core / elective / functional)</strong> as a subject in the qualifying examination.</li>
<li>Marks are <strong>not rounded off</strong> &mdash; 49.99% will not become 50% (Important Instruction #28).</li>
<li>Reserved categories (SC/ST/OBC/PwD): 5% relaxation per university policy.</li>
</ul>

<p style="font-size:13px;color:#666"><em>* "Aggregate of 50% marks in the 12th class for the purpose of eligibility will be taken as the aggregate of the best four subjects (unless otherwise specified) including English and compulsory subject(s), if any" — Chapter 2, "For Graduation Programmes" footnote.</em></p>

<hr>

<h2>CUET (UG) Subject Papers Required for IPU BCA</h2>

<p>Per Chapter 2, Programme Code 114 (Domain Specific Subjects column), candidates appearing in CUET (UG) for IPU BCA should attempt <strong>at least one</strong> of the following:</p>

<ul>
<li><strong>Section IA &mdash; English (Code 101)</strong> &mdash; recommended language paper since English is a compulsory eligibility subject.</li>
<li><strong>Section II &mdash; Mathematics / Applied Mathematics (Code 319)</strong></li>
<li><strong>Section II &mdash; Computer Science / Informatics Practices (Code 308)</strong></li>
<li><strong>Section III &mdash; General Aptitude Test (Code 501)</strong></li>
</ul>

<p>
The strongest CUET combination for IPU BCA is all four papers: English + Mathematics + Computer Science/Informatics Practices + General Aptitude Test. Candidates from a non-Mathematics Class 12 background can attempt Computer Science/Informatics Practices (308) + English (101) + General Aptitude (501).
</p>

<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>

<hr>

<h2>BCA Programme at IPU</h2>

<p>Programme Code 114 covers <strong>Bachelor of Computer Applications (BCA)</strong>, a 4-year undergraduate programme under NEP 2020:</p>

<ul>
<li>BCA (General) &mdash; 4-year programme with exit options at Year 2 / Year 3 per NEP</li>
<li>Covers: Programming (C/C++/Java/Python), DBMS, Web Development, Data Structures, Computer Networks, Software Engineering</li>
<li>Gateway to MCA at IPU and other universities</li>
</ul>

<hr>

<h2>Top IPU BCA Colleges That Accept CUET (Vacant Seat Round)</h2>

<ul>
<li><a href="bvicam-admission.php"><strong>BVICAM (Bharati Vidyapeeth's Institute of Computer Applications &amp; Management)</strong></a> &mdash; BCA</li>
<li><strong>USICT (University School of Information, Communication &amp; Technology)</strong> &mdash; Dwarka (USS; no management quota)</li>
<li>JIMS Rohini &mdash; BCA</li>
<li>JIMS Kalkaji &mdash; BCA</li>
<li><a href="fairfield-admission.php"><strong>Fairfield Institute</strong></a> &mdash; BCA</li>
<li><a href="cpj-admission.php"><strong>CPJ College Narela</strong></a> &mdash; BCA</li>
<li><a href="ideal-admission.php"><strong>Ideal Institute</strong></a> &mdash; BCA</li>
<li><a href="don-bosco-admission.php">Don Bosco Institute</a> &mdash; BCA</li>
</ul>

<p>
👉 Full list: <a href="ipu-colleges-list.php"><strong>All IPU Affiliated Colleges</strong></a>
</p>

<hr>

<h2>Management Quota Through CUET — BCA</h2>

<p>
Per Chapter 12 of the brochure, every unaided affiliated BCA college reserves <strong>10% of its total seats</strong> as Management Quota. A valid CUET (UG) score is one of the three accepted qualifiers:
</p>

<ul>
<li><strong>Eligibility:</strong> Class 12 with 50% aggregate (best 4 including English) <em>plus</em> a valid <strong>IPU CET / CUET</strong> score (Instruction #21 + Chapter 12 Note 2).</li>
<li><strong>Aggregate calculation for BCA:</strong> Per Chapter 12 — <em>"for programmes like BBA, BHMCT, BCA &amp; BA(JMC) for calculating aggregate marks obtained in the qualifying examination, aggregate marks of <strong>best 4 subjects</strong> should be taken which includes English."</em></li>
<li><strong>Application:</strong> Apply directly to the college during the 18-day window advertised in 2 newspapers (Hindi + English).</li>
<li><strong>Registration fee cap:</strong> Rs. 2,500 per the Admission Regulatory Committee.</li>
<li><strong>Last date:</strong> 9 calendar days after the regular reporting date of the last GGSIPU counselling round.</li>
</ul>

<p>
👉 Detailed guide: <a href="IP-University-management-quota-admission-eligibility-criteria.php"><strong>IPU Management Quota Admission</strong></a>
</p>

<hr>

<h2>Fee Structure 2026-27 (CUET-Admitted BCA Students)</h2>

<ul>
<li><strong>USICT (USS) BCA:</strong> USS fee structure 2026-27 as per Chapter 14 of the brochure; check ipu.ac.in for the current fee notification (USS is not open to management quota).</li>
<li><strong>Affiliated BCA colleges:</strong> Tuition typically Rs. 1,15,000-1,40,000 per year per the 6th SFRC Delhi Gazette Notification dated 14.07.2025, plus university charges, exam fee, alumni and welfare contributions.</li>
<li><strong>Management quota seats</strong> follow the same regulated tuition. Capitation fee is illegal under the Delhi Professional Colleges Act, 2007.</li>
</ul>

<hr>

<h2>Step-by-Step: Get BCA Admission via CUET</h2>

<ol>
<li><strong>Register for CUET (UG) 2026</strong> at cuet.nta.nic.in. Choose Section IA English (101), Section II Mathematics/Applied Mathematics (319), Section II Computer Science/Informatics Practices (308), Section III General Aptitude Test (501).</li>
<li>Appear in CUET (UG) 2026 on the scheduled date.</li>
<li><strong>Wait for GGSIPU notification</strong> announcing the CUET-based counselling round. This typically opens after IPU CET vacancies are reported.</li>
<li><strong>Register on ipu.admissions.nic.in</strong> using your CUET roll number, fill college choices in order of preference, lock preferences.</li>
<li>If allotted, pay the Part Academic Fee, complete online document verification, report to the allotted college.</li>
<li><strong>Parallel track &mdash; Management Quota:</strong> Apply directly during each college's advertised 18-day management-quota window with Class 12 marksheet, CUET scorecard, ID and photo.</li>
</ol>

<hr>

<h2>Need Free CUET-to-IPU BCA Admission Help?</h2>

<p>
We have placed hundreds of students into IPU BCA colleges through CUET vacant-seat and management quota counselling.
</p>

<p>
<strong>Call our team for free 1-on-1 guidance:</strong><br>
<b><?php include("include/phone.php"); ?></b><br>
<em>Mon&ndash;Sat, 9 AM &ndash; 7 PM</em>
</p>

</div>
</div>

<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>

</div>
</div>

</section>

<!-- FAQ Section (sourced from GGSIPU UG Admission Brochure 2026-27) -->
<?php
$faqs = [
  ['question' => 'Can I get BCA admission at IPU only with a CUET score?', 'answer' => 'Yes, through two routes. <strong>(1) Vacant-seat round</strong>: per Important Instruction #37, GGSIPU runs IPU CET counselling first; whatever BCA seats remain unfilled are then offered on CUET merit. <strong>(2) Management Quota</strong>: per Important Instruction #21 + Chapter 12, every unaided affiliated BCA college (BVICAM, JIMS, Fairfield, CPJ, Ideal etc.) reserves 10% seats as management quota where a valid CUET score is one of the accepted qualifiers. The candidate must still meet the Class 12 50% aggregate eligibility (best 4 including English).'],
  ['question' => 'Which CUET (UG) subject papers should I attempt for IPU BCA?', 'answer' => 'Per Chapter 2, Programme Code 114 of the 2026-27 brochure, attempt: <strong>Section IA — English (Code 101)</strong>, <strong>Section II — Mathematics / Applied Mathematics (Code 319)</strong>, <strong>Section II — Computer Science / Informatics Practices (Code 308)</strong>, <strong>Section III — General Aptitude Test (Code 501)</strong>. The strongest profile uses all four papers. Candidates without Maths in Class 12 can use CS/Informatics (308) + English (101) + General Aptitude (501).'],
  ['question' => 'What is the eligibility for BCA admission at IPU 2026-27?', 'answer' => 'Pass in Class 12 of CBSE or equivalent with a minimum aggregate of <strong>50% marks</strong> (best 4 subjects including English). Must have passed English (core/elective/functional) as a subject. Reserved category candidates get a 5% relaxation per university policy. Marks are not rounded off (Instruction #28).'],
  ['question' => 'Do I need Mathematics in Class 12 for BCA at IPU?', 'answer' => 'No, Mathematics is not a mandatory Class 12 subject for BCA eligibility at IPU. The eligibility requires Class 12 pass with 50% aggregate (best 4 including English) — stream is not restricted. However, attempting both Mathematics (Code 319) and Computer Science/Informatics Practices (Code 308) in CUET (UG) strengthens your CUET profile for IPU BCA admission.'],
  ['question' => 'Which IPU BCA colleges are most likely to have CUET vacant seats?', 'answer' => 'Vacant-seat availability changes year to year. USICT (USS) usually fills in IPU CET rounds. Tier-2 affiliated colleges historically have more seats remaining after CET rounds — watch BVICAM, Fairfield, CPJ College, Ideal Institute and Don Bosco. Apply to multiple colleges to maximise chances.'],
  ['question' => 'What is the Computer Science subject code in CUET for IPU BCA?', 'answer' => '<strong>Computer Science / Informatics Practices is Code 308</strong> under Section II of CUET (UG). Mathematics / Applied Mathematics is Code 319. Both are relevant domain papers for IPU BCA (Programme Code 114). Combining CS/Informatics (308) + Maths (319) + English (101) + General Aptitude (501) gives the strongest CUET profile for BCA vacant-seat and management quota admission at IPU.']
];
include 'include/components/faq-section.php';
?>

<?php
$related_pages = [
    ['title' => 'CUET Admission Hub', 'url' => '/cuet-admission-ipu.php', 'desc' => 'Complete CUET-to-IPU guide for all 8 programmes'],
    ['title' => 'IPU BBA Admission Through CUET', 'url' => '/cuet-bba-admission-ipu.php', 'desc' => 'BBA via CUET — Business Studies subject paper'],
    ['title' => 'IPU BJMC Admission Through CUET', 'url' => '/cuet-bjmc-admission-ipu.php', 'desc' => 'BA(JMC)/BJMC via CUET — Mass Media subject paper'],
    ['title' => 'IPU Management Quota Admission', 'url' => '/IP-University-management-quota-admission-eligibility-criteria.php', 'desc' => '10% reserved seats at every unaided IPU college'],
    ['title' => 'IPU Helpline 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission guidance — Mon–Sat 9 AM–7 PM'],
    ['title' => 'All IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => '60+ IPU affiliated colleges in Delhi NCR'],
];
include 'include/components/related-pages.php';
?>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Bachelor of Computer Applications (BCA) at GGSIPU — Admission Through CUET (UG)",
  "description": "4-year BCA programme at Guru Gobind Singh Indraprastha University admitting through GGSIPU CET 2026 (primary) and CUET (UG) for vacant-seat filling and management quota qualification, per the 2026-27 admission brochure.",
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University (GGSIPU)",
    "sameAs": "https://www.ipu.ac.in/"
  },
  "educationalCredentialAwarded": "Bachelor of Computer Applications (BCA)",
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "Onsite",
    "courseWorkload": "P4Y",
    "location": {"@type": "Place", "name": "Delhi NCR"}
  },
  "offers": {
    "@type": "Offer",
    "category": "Tuition",
    "priceCurrency": "INR",
    "priceSpecification": {"@type": "PriceSpecification", "price": "121000", "priceCurrency": "INR", "description": "Affiliated BCA college tuition per annum 2026-27 (approx., per 6th SFRC)"}
  }
}
</script>

<?php include_once("include/base-footer.php"); ?>

<!-- Article Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"IPU BCA Admission Through CUET (UG) — Eligibility, Subjects & College List",
"publisher":{"@type":"Organization","name":"ipu.co.in"},
"mainEntityOfPage":{"@type":"WebPage","@id":"https://ipu.co.in/cuet-bca-admission-ipu.php"}
}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"BreadcrumbList",
"itemListElement":[
{"@type":"ListItem","position":1,"name":"Home","item":"https://ipu.co.in/"},
{"@type":"ListItem","position":2,"name":"CUET Admission","item":"https://ipu.co.in/cuet-admission-ipu.php"},
{"@type":"ListItem","position":3,"name":"BCA via CUET","item":"https://ipu.co.in/cuet-bca-admission-ipu.php"}
]
}
</script>

</body>
</html>
```

- [ ] **Step 2: PHP lint**

```bash
php -l /Users/Sumit/test-project/website_download/cuet-bca-admission-ipu.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-bca-admission-ipu.php
git commit -m "feat: add BCA CUET admission page (Programme Code 114)"
```

---

### Task 5: New page — cuet-ba-english-admission-ipu.php

**Files:**
- Create: `website_download/cuet-ba-english-admission-ipu.php`

**Interfaces:**
- Produces: live BA English CUET page at `https://ipu.co.in/cuet-ba-english-admission-ipu.php`

- [ ] **Step 1: Create the file with this exact content**

```php
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_cache_limiter('public'); session_cache_expire(30); session_start(); }
include_once("include/base-head.php");
include_once("include/form-handler.php");
?>

<!-- SEO META -->
<title>IPU BA English Admission Through CUET (UG) — Eligibility, Subjects, Colleges</title>

<meta name="description" content="GGSIPU BA English (Hons) via CUET — English Language + General Aptitude papers only, vacant-seat counselling after IPU CET, management quota route. Free guidance: 9899991342.">

<meta name="robots" content="index, follow">
<link rel="canonical" href="https://ipu.co.in/cuet-ba-english-admission-ipu.php" />

</head>

<body>

<?php include_once("include/base-nav.php"); ?>

<?php
$hero_h1 = 'IPU BA English Admission Through CUET (UG): Eligibility, Subjects &amp; College List';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>

<section class="blog-wrapper pt-130 pb-130">

<div class="container">

<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="cuet-admission-ipu.php">CUET Admission</a></li>
<li class="breadcrumb-item active">BA English via CUET</li>
</ol>
</nav>

<div class="row">

<div class="col-lg-8">
<div class="blog-details">

<img fetchpriority="high" decoding="async" width="470" height="343" src="assets/images/BA-ENGLISH.jpg"
class="main-img"
alt="BA English Hons admission through CUET at GGSIPU — subject papers and eligible colleges">

<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>

<!-- AI Summary -->
<section id="ai-summary" style="display:none">
<p>BA English (Hons) admission at GGSIPU (Programme Code 184) is granted on the merit of <strong>IPU CET 2026</strong>. Per Important Instruction #37 of the 2026-27 brochure, vacant BA English seats remaining after IPU CET counselling are filled on the merit of <strong>CUET (UG) 2026</strong>. CUET-qualified candidates also become eligible for the 10% management quota seats at every unaided affiliated BA English college (Important Instruction #21 + Chapter 12). Eligibility: Class 12 with 50% aggregate (best 4 subjects including English), pass in English. CUET subject papers: Section IA English Language (Code 101) + Section III General Aptitude Test (Code 501) — no domain-specific subject required. Free admission counselling: 9899991342.</p>
</section>

<h2>How CUET Works for IPU BA English 2026-27</h2>

<p>
Per Section 1.1 ("At a Glance") of the GGSIPU UG Admission Brochure 2026-27, BA English (Hons) admission at IP University (Programme Code <strong>184</strong>) is structured as a <strong>two-tier merit list</strong>:
</p>

<ol>
<li><strong>First preference:</strong> <strong>GGSIPU CET 2026</strong> conducted by the University.</li>
<li><strong>Second pathway:</strong> <strong>CUET (UG) 2026 #</strong> &mdash; used to fill vacant seats remaining after IPU CET counselling rounds (Important Instruction #37).</li>
</ol>

<p>
A BA English aspirant who only has a CUET score (and not IPU CET) is still admissible &mdash; through the CUET vacant-seat round and through the management quota route at IPU's affiliated colleges offering BA English.
</p>

<hr>

<h2>Eligibility for BA English via CUET (Programme Code 184)</h2>

<p>Per Chapter 2 of the brochure:</p>

<ul>
<li>Pass in <strong>Class 12 (10+2)</strong> of CBSE or equivalent with a minimum <strong>50% marks in aggregate</strong>* (best 4 subjects including English).</li>
<li>The candidate must also have passed <strong>English (core / elective / functional)</strong> as a subject in the qualifying examination.</li>
<li>Marks are <strong>not rounded off</strong> &mdash; 49.99% will not become 50% (Important Instruction #28).</li>
<li>Reserved categories (SC/ST/OBC/PwD): 5% relaxation per university policy.</li>
</ul>

<p style="font-size:13px;color:#666"><em>* "Aggregate of 50% marks in the 12th class for the purpose of eligibility will be taken as the aggregate of the best four subjects (unless otherwise specified) including English and compulsory subject(s), if any" — Chapter 2, "For Graduation Programmes" footnote.</em></p>

<hr>

<h2>CUET (UG) Subject Papers Required for IPU BA English</h2>

<p>Per Chapter 2, Programme Code 184 (Domain Specific Subjects column), candidates appearing in CUET (UG) for IPU BA English should attempt:</p>

<ul>
<li><strong>Section IA &mdash; English Language (Code 101)</strong> &mdash; the language paper, directly aligned with the English Honours programme.</li>
<li><strong>Section III &mdash; General Aptitude Test (Code 501)</strong></li>
</ul>

<p>
<strong>No domain-specific subject (Section II) is required for BA English via CUET at IPU.</strong> The combination of English Language (101) + General Aptitude Test (501) is sufficient per the brochure's subject mapping for Programme Code 184. This is one of the simplest CUET paper combinations among all IPU UG programmes.
</p>

<p style="font-size:13px;color:#666"><em>Per Section 1.2 of the brochure: <em>"during the Academic Session 2026-27, the methodology for conduct of CUET may be as per the notification to be issued by the University in due course of time"</em>. Final paper-mapping will be confirmed by GGSIPU on ipu.ac.in before counselling.</em></p>

<hr>

<h2>BA English Programme at IPU</h2>

<p>Programme Code 184 covers <strong>BA English (Honours)</strong>, a 4-year undergraduate programme under NEP 2020:</p>

<ul>
<li>BA English (Hons) &mdash; 4-year programme with exit options at Year 2 / Year 3 per NEP</li>
<li>Covers: English Literature (Prose, Poetry, Drama), Language &amp; Linguistics, Creative Writing, Literary Theory &amp; Criticism, Communication Skills</li>
<li>Offered at USSHSS (University School of Humanities &amp; Social Sciences), Dwarka, and select affiliated colleges</li>
</ul>

<hr>

<h2>Top IPU BA English Colleges That Accept CUET (Vacant Seat Round)</h2>

<ul>
<li><strong>USSHSS (University School of Humanities &amp; Social Sciences)</strong> &mdash; Dwarka campus (USS; no management quota)</li>
<li>Select affiliated colleges offering BA English &mdash; see <a href="ipu-colleges-list.php">full IPU college list</a> for current availability</li>
</ul>

<p>
👉 Full list: <a href="ipu-colleges-list.php"><strong>All IPU Affiliated Colleges</strong></a>
</p>

<hr>

<h2>Management Quota Through CUET — BA English</h2>

<p>
Per Chapter 12 of the brochure, every unaided affiliated college reserves <strong>10% of its total seats</strong> as Management Quota. A valid CUET (UG) score is one of the three accepted qualifiers:
</p>

<ul>
<li><strong>Eligibility:</strong> Class 12 with 50% aggregate (best 4 including English) <em>plus</em> a valid <strong>IPU CET / CUET</strong> score (Instruction #21 + Chapter 12 Note 2).</li>
<li><strong>Application:</strong> Apply directly to the college during the 18-day window advertised in 2 newspapers (Hindi + English).</li>
<li><strong>Registration fee cap:</strong> Rs. 2,500 per the Admission Regulatory Committee.</li>
<li><strong>Last date:</strong> 9 calendar days after the regular reporting date of the last GGSIPU counselling round.</li>
</ul>

<p>
👉 Detailed guide: <a href="IP-University-management-quota-admission-eligibility-criteria.php"><strong>IPU Management Quota Admission</strong></a>
</p>

<hr>

<h2>Fee Structure 2026-27 (CUET-Admitted BA English Students)</h2>

<ul>
<li><strong>USSHSS (USS) BA English:</strong> USS fee structure 2026-27 as per Chapter 14 of the brochure; check ipu.ac.in for the current fee notification (USS is not open to management quota).</li>
<li><strong>Affiliated colleges:</strong> Tuition per the 6th SFRC Delhi Gazette Notification dated 14.07.2025, plus university charges, exam fee, alumni and welfare contributions.</li>
<li><strong>Management quota seats</strong> follow the same regulated tuition. Capitation fee is illegal under the Delhi Professional Colleges Act, 2007.</li>
</ul>

<hr>

<h2>Step-by-Step: Get BA English Admission via CUET</h2>

<ol>
<li><strong>Register for CUET (UG) 2026</strong> at cuet.nta.nic.in. Choose Section IA English Language (101) and Section III General Aptitude Test (501).</li>
<li>Appear in CUET (UG) 2026 on the scheduled date.</li>
<li><strong>Wait for GGSIPU notification</strong> announcing the CUET-based counselling round. This typically opens after IPU CET vacancies are reported.</li>
<li><strong>Register on ipu.admissions.nic.in</strong> using your CUET roll number, fill college choices in order of preference, lock preferences.</li>
<li>If allotted, pay the Part Academic Fee, complete online document verification, report to the allotted college.</li>
<li><strong>Parallel track &mdash; Management Quota:</strong> Apply directly during each college's advertised 18-day management-quota window with Class 12 marksheet, CUET scorecard, ID and photo.</li>
</ol>

<hr>

<h2>Need Free CUET-to-IPU BA English Admission Help?</h2>

<p>
We have guided thousands of students into IPU undergraduate programmes through CUET vacant-seat and management quota counselling.
</p>

<p>
<strong>Call our team for free 1-on-1 guidance:</strong><br>
<b><?php include("include/phone.php"); ?></b><br>
<em>Mon&ndash;Sat, 9 AM &ndash; 7 PM</em>
</p>

</div>
</div>

<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>

</div>
</div>

</section>

<!-- FAQ Section (sourced from GGSIPU UG Admission Brochure 2026-27) -->
<?php
$faqs = [
  ['question' => 'Can I get BA English admission at IPU only with a CUET score?', 'answer' => 'Yes, through two routes. <strong>(1) Vacant-seat round</strong>: per Important Instruction #37, GGSIPU runs IPU CET counselling first; whatever BA English seats remain unfilled are then offered on CUET merit. <strong>(2) Management Quota</strong>: per Important Instruction #21 + Chapter 12, every unaided affiliated college reserves 10% seats as management quota where a valid CUET score is one of the accepted qualifiers. The candidate must still meet the Class 12 50% aggregate eligibility (best 4 including English).'],
  ['question' => 'Which CUET (UG) subject papers should I attempt for IPU BA English?', 'answer' => 'Per Chapter 2, Programme Code 184 of the 2026-27 brochure: <strong>Section IA — English Language (Code 101)</strong> and <strong>Section III — General Aptitude Test (Code 501)</strong>. <strong>No Section II domain subject is required</strong> — this is one of the simplest CUET paper combinations among all IPU programmes. Both English Language and General Aptitude Test together form the complete CUET subject requirement for BA English (Hons) at IPU.'],
  ['question' => 'What is the eligibility for BA English admission at IPU 2026-27?', 'answer' => 'Pass in Class 12 of CBSE or equivalent with a minimum aggregate of <strong>50% marks</strong> (best 4 subjects including English). Must have passed English (core/elective/functional) as a subject. Reserved category candidates get a 5% relaxation per university policy. Marks are not rounded off (Instruction #28).'],
  ['question' => 'Is there any domain-specific CUET paper required for BA English at IPU?', 'answer' => 'No. Per Chapter 2, Programme Code 184, the CUET subject requirement for IPU BA English is <strong>only Section IA English Language (Code 101) + Section III General Aptitude Test (Code 501)</strong>. No Section II domain subject (like Literature in English, History etc.) is listed in the brochure for this programme. This differs from most other IPU humanities programmes and makes BA English one of the simplest CUET combinations at IPU.'],
  ['question' => 'Which colleges offer BA English (Hons) at IPU?', 'answer' => 'USSHSS (University School of Humanities &amp; Social Sciences) at Dwarka is IPU\'s own school for BA English. A limited number of affiliated colleges also offer BA English under Programme Code 184. Check ipu.ac.in for the current year\'s participating college list.'],
  ['question' => 'Will GGSIPU notify a separate CUET counselling schedule for BA English?', 'answer' => 'Yes. Per Section 1.2 of the brochure, CUET-based admissions are carried out after exhausting the IPU CET merit list. GGSIPU will publish a separate notification for CUET vacant-seat counselling. Watch ipu.ac.in and ipu.admissions.nic.in after the IPU CET result.']
];
include 'include/components/faq-section.php';
?>

<?php
$related_pages = [
    ['title' => 'CUET Admission Hub', 'url' => '/cuet-admission-ipu.php', 'desc' => 'Complete CUET-to-IPU guide for all 8 programmes'],
    ['title' => 'IPU BA Economics Admission Through CUET', 'url' => '/cuet-ba-economics-admission-ipu.php', 'desc' => 'BA Economics Hons via CUET — Economics + Maths papers'],
    ['title' => 'IPU B.Com Admission Through CUET', 'url' => '/cuet-bcom-admission-ipu.php', 'desc' => 'B.Com Hons via CUET — Accountancy subject paper'],
    ['title' => 'IPU Management Quota Admission', 'url' => '/IP-University-management-quota-admission-eligibility-criteria.php', 'desc' => '10% reserved seats at every unaided IPU college'],
    ['title' => 'IPU Helpline 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission guidance — Mon–Sat 9 AM–7 PM'],
    ['title' => 'All IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => '60+ IPU affiliated colleges in Delhi NCR'],
];
include 'include/components/related-pages.php';
?>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Bachelor of Arts in English (Honours) at GGSIPU — Admission Through CUET (UG)",
  "description": "4-year BA English (Hons) programme at Guru Gobind Singh Indraprastha University admitting through GGSIPU CET 2026 (primary) and CUET (UG) for vacant-seat filling and management quota qualification, per the 2026-27 admission brochure.",
  "provider": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University (GGSIPU)",
    "sameAs": "https://www.ipu.ac.in/"
  },
  "educationalCredentialAwarded": "Bachelor of Arts in English (Honours)",
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "Onsite",
    "courseWorkload": "P4Y",
    "location": {"@type": "Place", "name": "Delhi NCR"}
  }
}
</script>

<?php include_once("include/base-footer.php"); ?>

<!-- Article Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"Article",
"headline":"IPU BA English Admission Through CUET (UG) — Eligibility, Subjects & College List",
"publisher":{"@type":"Organization","name":"ipu.co.in"},
"mainEntityOfPage":{"@type":"WebPage","@id":"https://ipu.co.in/cuet-ba-english-admission-ipu.php"}
}
</script>

<!-- Breadcrumb Schema -->
<script type="application/ld+json">
{
"@context":"https://schema.org",
"@type":"BreadcrumbList",
"itemListElement":[
{"@type":"ListItem","position":1,"name":"Home","item":"https://ipu.co.in/"},
{"@type":"ListItem","position":2,"name":"CUET Admission","item":"https://ipu.co.in/cuet-admission-ipu.php"},
{"@type":"ListItem","position":3,"name":"BA English via CUET","item":"https://ipu.co.in/cuet-ba-english-admission-ipu.php"}
]
}
</script>

</body>
</html>
```

- [ ] **Step 2: PHP lint**

```bash
php -l /Users/Sumit/test-project/website_download/cuet-ba-english-admission-ipu.php
```
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-ba-english-admission-ipu.php
git commit -m "feat: add BA English CUET admission page (Programme Code 184)"
```

---

### Task 6: Hub page update — cuet-admission-ipu.php

**Files:**
- Modify: `website_download/cuet-admission-ipu.php:53` (`$last_updated`)
- Modify: `website_download/cuet-admission-ipu.php:63` (intro paragraph "4 major courses" text)
- Modify: `website_download/cuet-admission-ipu.php:111–115` (table tbody: add 4 new rows)
- Modify: `website_download/cuet-admission-ipu.php:229–238` (`$related_pages` array: add 4 entries)

**Interfaces:**
- Produces: hub page listing all 8 CUET programmes with links

- [ ] **Step 1: Update $last_updated on hub**

Find and replace (line 53):
```php
<?php $last_updated = '2026-05-06'; include 'include/components/last-updated.php'; ?>
```
Replace with:
```php
<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>
```

- [ ] **Step 2: Update intro paragraph**

Find (line 67, in the opening H2 section):
```html
and links to course-specific CUET guides for B.Tech, BBA, B.Com and Law.
```
Replace with:
```html
and links to course-specific CUET guides for B.Tech, BBA, B.Com, Law, BJMC, BA Economics, BCA and BA English.
```

- [ ] **Step 3: Add 4 new rows to the programme table**

Find the closing `</tbody>` tag that follows the 4 existing table rows (the last row is the Law row ending with `</tr>`). The existing last row is:
```html
<tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">Integrated BA-LLB / BBA-LLB</td><td style="padding:10px 14px">121</td><td style="padding:10px 14px">CLAT UG 2026</td><td style="padding:10px 14px"><a href="cuet-law-admission-ipu.php"><strong>Law via CUET &rarr;</strong></a></td></tr>
```

After that row (before `</tbody>`), insert:
```html
<tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">BA(JMC) / BJMC</td><td style="padding:10px 14px">126</td><td style="padding:10px 14px">IPU CET</td><td style="padding:10px 14px"><a href="cuet-bjmc-admission-ipu.php"><strong>BJMC via CUET &rarr;</strong></a></td></tr>
<tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">BA Economics (Hons)</td><td style="padding:10px 14px">197</td><td style="padding:10px 14px">IPU CET</td><td style="padding:10px 14px"><a href="cuet-ba-economics-admission-ipu.php"><strong>BA Economics via CUET &rarr;</strong></a></td></tr>
<tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">BCA</td><td style="padding:10px 14px">114</td><td style="padding:10px 14px">IPU CET</td><td style="padding:10px 14px"><a href="cuet-bca-admission-ipu.php"><strong>BCA via CUET &rarr;</strong></a></td></tr>
<tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">BA English (Hons)</td><td style="padding:10px 14px">184</td><td style="padding:10px 14px">IPU CET</td><td style="padding:10px 14px"><a href="cuet-ba-english-admission-ipu.php"><strong>BA English via CUET &rarr;</strong></a></td></tr>
```

- [ ] **Step 4: Add 4 new entries to $related_pages**

The existing `$related_pages` array ends with:
```php
    ['title' => 'All IPU Colleges List 2026', 'url' => '/ipu-colleges-list.php', 'desc' => 'Complete list of 60+ IPU affiliated colleges in Delhi NCR'],
```

After that line (before the closing `];`), add:
```php
    ['title' => 'IPU BJMC Admission Through CUET', 'url' => '/cuet-bjmc-admission-ipu.php', 'desc' => 'BA(JMC)/BJMC via CUET — Mass Media + General Aptitude papers'],
    ['title' => 'IPU BA Economics Admission Through CUET', 'url' => '/cuet-ba-economics-admission-ipu.php', 'desc' => 'BA Economics Hons via CUET — Economics and Maths domain papers'],
    ['title' => 'IPU BCA Admission Through CUET', 'url' => '/cuet-bca-admission-ipu.php', 'desc' => 'BCA via CUET — Maths + CS/Informatics papers'],
    ['title' => 'IPU BA English Admission Through CUET', 'url' => '/cuet-ba-english-admission-ipu.php', 'desc' => 'BA English Hons via CUET — English Language + General Aptitude only'],
```

- [ ] **Step 5: PHP lint**

```bash
php -l /Users/Sumit/test-project/website_download/cuet-admission-ipu.php
```
Expected: `No syntax errors detected`

- [ ] **Step 6: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/cuet-admission-ipu.php
git commit -m "content: update CUET hub page — add 4 new programme rows + related_pages"
```

---

### Task 7: Sitemap + llms.txt update

**Files:**
- Modify: `website_download/sitemap.xml:763` (after last cuet entry)
- Modify: `website_download/llms.txt` (add CUET section before existing BBA/B.Com entries)

**Interfaces:**
- Produces: sitemap with 4 new `<url>` blocks; llms.txt with 4 new CUET page entries

- [ ] **Step 1: Add 4 new URL blocks to sitemap.xml**

Find the closing `</url>` of the last CUET entry (line 763):
```xml
<url>
<loc>https://ipu.co.in/cuet-law-admission-ipu.php</loc>
<lastmod>2026-06-11</lastmod>
<priority>0.70</priority>
</url>
```

After that block (before the next `<url>` block for `dspsr-admission.php`), insert:
```xml

<url>
<loc>https://ipu.co.in/cuet-bjmc-admission-ipu.php</loc>
<lastmod>2026-06-24</lastmod>
<priority>0.70</priority>
</url>

<url>
<loc>https://ipu.co.in/cuet-ba-economics-admission-ipu.php</loc>
<lastmod>2026-06-24</lastmod>
<priority>0.70</priority>
</url>

<url>
<loc>https://ipu.co.in/cuet-bca-admission-ipu.php</loc>
<lastmod>2026-06-24</lastmod>
<priority>0.70</priority>
</url>

<url>
<loc>https://ipu.co.in/cuet-ba-english-admission-ipu.php</loc>
<lastmod>2026-06-24</lastmod>
<priority>0.70</priority>
</url>
```

- [ ] **Step 2: Validate sitemap XML**

```bash
php -r "simplexml_load_file('/Users/Sumit/test-project/website_download/sitemap.xml') or die('XML invalid');"
```
Expected: no output (valid XML).

- [ ] **Step 3: Add 4 new entries to llms.txt**

Find the line in llms.txt:
```
## BBA ADMISSION
```

Before that line, insert the following 4 new sections:

```
## IPU BJMC ADMISSION THROUGH CUET
URL: https://ipu.co.in/cuet-bjmc-admission-ipu.php
Summary: BJMC / BA(JMC) admission at IP University through CUET (UG). Programme Code 126. CUET papers: English (101), Mass Media & Mass Communication (318), General Aptitude (501). IPU CET is primary; CUET fills vacant seats. Management quota route available.

## IPU BA ECONOMICS ADMISSION THROUGH CUET
URL: https://ipu.co.in/cuet-ba-economics-admission-ipu.php
Summary: BA Economics (Hons) admission at IP University through CUET (UG). Programme Code 197. CUET papers: English (101), Economics/Business Economics (309), Mathematics/Applied Mathematics (319). IPU CET is primary; CUET fills vacant seats.

## IPU BCA ADMISSION THROUGH CUET
URL: https://ipu.co.in/cuet-bca-admission-ipu.php
Summary: BCA admission at IP University through CUET (UG). Programme Code 114. CUET papers: English (101), Mathematics/Applied Mathematics (319), Computer Science/Informatics Practices (308), General Aptitude (501). IPU CET is primary; CUET fills vacant seats. Management quota route available.

## IPU BA ENGLISH ADMISSION THROUGH CUET
URL: https://ipu.co.in/cuet-ba-english-admission-ipu.php
Summary: BA English (Hons) admission at IP University through CUET (UG). Programme Code 184. CUET papers: English Language (101) + General Aptitude (501) only — no domain subject required. IPU CET is primary; CUET fills vacant seats.

```

- [ ] **Step 4: Commit**

```bash
cd /Users/Sumit/test-project
git add website_download/sitemap.xml website_download/llms.txt
git commit -m "seo: add 4 new CUET pages to sitemap + llms.txt"
```

---

### Task 8: Pre-deploy quality check

**Files:** Read-only verification — no changes

- [ ] **Step 1: PHP lint all 9 changed/new files**

```bash
cd /Users/Sumit/test-project/website_download
for f in cuet-btech-admission-ipu.php cuet-bba-admission-ipu.php cuet-bcom-admission-ipu.php cuet-law-admission-ipu.php cuet-bjmc-admission-ipu.php cuet-ba-economics-admission-ipu.php cuet-bca-admission-ipu.php cuet-ba-english-admission-ipu.php cuet-admission-ipu.php; do php -l $f; done
```
Expected: `No syntax errors detected` for all 9 files.

- [ ] **Step 2: Start localhost server**

```bash
cd /Users/Sumit/test-project/website_download && php -S localhost:8001 &
```

- [ ] **Step 3: Curl-verify all 9 pages return HTTP 200**

```bash
for path in cuet-btech-admission-ipu.php cuet-bba-admission-ipu.php cuet-bcom-admission-ipu.php cuet-law-admission-ipu.php cuet-bjmc-admission-ipu.php cuet-ba-economics-admission-ipu.php cuet-bca-admission-ipu.php cuet-ba-english-admission-ipu.php cuet-admission-ipu.php; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8001/$path")
  echo "$code $path"
done
```
Expected: `200` for all 9.

- [ ] **Step 4: Verify brochure caveat appears on each new page**

```bash
for path in cuet-bjmc-admission-ipu.php cuet-ba-economics-admission-ipu.php cuet-bca-admission-ipu.php cuet-ba-english-admission-ipu.php; do
  result=$(curl -s "http://localhost:8001/$path" | grep -c "Academic Session 2026-27")
  echo "$result $path"
done
```
Expected: `1` or more for each file (caveat present).

- [ ] **Step 5: Verify hub table has 8 rows**

```bash
curl -s "http://localhost:8001/cuet-admission-ipu.php" | grep -c "via CUET &rarr;"
```
Expected: `8`

- [ ] **Step 6: Walk crosslinks on one new page**

```bash
curl -s "http://localhost:8001/cuet-bjmc-admission-ipu.php" | grep -o 'href="[^"]*"' | grep -v "^#\|javascript\|mailto\|tel" | head -20
```
Visually verify that key links (`cuet-admission-ipu.php`, `vips-admission.php`, `ipu-colleges-list.php`, `IP-University-management-quota-admission-eligibility-criteria.php`) appear and are not malformed.

- [ ] **Step 7: Kill localhost server**

```bash
pkill -f "php -S localhost:8001"
```

---

### Task 9: Deploy — owner go-ahead gate

> **STOP HERE.** Do not proceed past this task until the owner (Sumit) explicitly approves the deploy. Show him the localhost verification results from Task 8 and ask for go-ahead.

**Files:** Deploy 11 files to production via FTP

- [ ] **Step 1: Confirm owner go-ahead**

Present the Task 8 verification output to the owner and wait for explicit approval before proceeding.

- [ ] **Step 2: Git pull to pick up any remote news commits**

```bash
cd /Users/Sumit/test-project && git fetch && git pull
```

- [ ] **Step 3: Dry-run deploy for the 11 files**

```bash
cd /Users/Sumit/test-project && python deploy.py --dry-run \
  website_download/cuet-btech-admission-ipu.php \
  website_download/cuet-bba-admission-ipu.php \
  website_download/cuet-bcom-admission-ipu.php \
  website_download/cuet-law-admission-ipu.php \
  website_download/cuet-bjmc-admission-ipu.php \
  website_download/cuet-ba-economics-admission-ipu.php \
  website_download/cuet-bca-admission-ipu.php \
  website_download/cuet-ba-english-admission-ipu.php \
  website_download/cuet-admission-ipu.php \
  website_download/sitemap.xml \
  website_download/llms.txt
```
Review dry-run output — confirm all 11 files listed, no errors.

- [ ] **Step 4: Live deploy**

```bash
cd /Users/Sumit/test-project && python deploy.py \
  website_download/cuet-btech-admission-ipu.php \
  website_download/cuet-bba-admission-ipu.php \
  website_download/cuet-bcom-admission-ipu.php \
  website_download/cuet-law-admission-ipu.php \
  website_download/cuet-bjmc-admission-ipu.php \
  website_download/cuet-ba-economics-admission-ipu.php \
  website_download/cuet-bca-admission-ipu.php \
  website_download/cuet-ba-english-admission-ipu.php \
  website_download/cuet-admission-ipu.php \
  website_download/sitemap.xml \
  website_download/llms.txt
```

- [ ] **Step 5: Curl-verify all 9 pages live (HTTP 200)**

```bash
for path in cuet-btech-admission-ipu.php cuet-bba-admission-ipu.php cuet-bcom-admission-ipu.php cuet-law-admission-ipu.php cuet-bjmc-admission-ipu.php cuet-ba-economics-admission-ipu.php cuet-bca-admission-ipu.php cuet-ba-english-admission-ipu.php cuet-admission-ipu.php; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "https://ipu.co.in/$path")
  echo "$code $path"
done
```
Expected: `200` for all 9.

- [ ] **Step 6: Verify brochure caveat string on live new pages**

```bash
for path in cuet-bjmc-admission-ipu.php cuet-ba-economics-admission-ipu.php cuet-bca-admission-ipu.php cuet-ba-english-admission-ipu.php; do
  result=$(curl -s "https://ipu.co.in/$path" | grep -c "Academic Session 2026-27")
  echo "$result $path"
done
```
Expected: `1` for each.
