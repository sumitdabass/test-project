# IPU Website: Form Fix, SEO/AI Improvements & New College Pages

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix duplicate form submissions (critical — site is live with ads), improve SEO/AI visibility across all pages, and add 25 high-demand college pages with official IPU seat intake data.

**Architecture:** Three independent workstreams executed sequentially — form fix first (business-critical), then SEO improvements (incremental), then college pages (content expansion). All changes are PHP files in `website_download/`. No build system; changes are deployed via FTP.

**Tech Stack:** PHP 7.4+, HTML5, Bootstrap 5, JSON-LD Schema.org, Google Apps Script (Sheets webhook)

---

## Phase 1: Fix Duplicate Form Submissions (CRITICAL)

### Task 1: Add duplicate prevention to form-handler.php

**Files:**
- Modify: `website_download/include/form-handler.php`

- [ ] **Step 1: Add phone-based session dedup + cookie dedup + cooldown timer**

Replace the entire `website_download/include/form-handler.php` with this version that adds three layers of duplicate prevention:

```php
<?php
/**
 * Form Handler — Anti-duplicate + anti-spam
 * Layers: 1) Session phone dedup  2) Cookie dedup  3) 5-min cooldown  4) Honeypot  5) Time-based bot check
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$form_error = '';
$form_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Layer 1: Honeypot check — bots fill hidden fields
    if (!empty($_POST['website'])) {
        header("Location: /thank-you.php");
        exit();
    }

    // Layer 2: Time-based bot check — reject submissions faster than 3 seconds
    $form_loaded = $_SESSION['form_loaded_at'] ?? 0;
    if ($form_loaded > 0 && (time() - $form_loaded) < 3) {
        header("Location: /thank-you.php");
        exit();
    }

    // Layer 3: Cooldown — block any submission within 5 minutes of last successful one
    $last_submit = $_SESSION['last_submit_time'] ?? 0;
    if ($last_submit > 0 && (time() - $last_submit) < 300) {
        // Already submitted recently, silently redirect to thank-you
        header("Location: /thank-you.php");
        exit();
    }

    // Sanitize inputs
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields
    if (!$name || !$phone || !$course) {
        $form_error = 'Please fill in Name, Phone, and Course.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $form_error = 'Please enter a valid 10-digit Indian phone number.';
    } elseif ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Please enter a valid email address.';
    } else {
        // Layer 4: Phone-based session dedup — same phone can't submit twice in same session
        $submitted_phones = $_SESSION['submitted_phones'] ?? [];
        if (in_array($phone, $submitted_phones)) {
            header("Location: /thank-you.php");
            exit();
        }

        // Layer 5: Cookie-based dedup — same phone can't submit from same browser within 24 hours
        $phone_hash = hash('sha256', $phone . 'ipu_salt_2026');
        $cookie_key = 'ipu_eq_' . substr($phone_hash, 0, 12);
        if (isset($_COOKIE[$cookie_key])) {
            header("Location: /thank-you.php");
            exit();
        }

        // All checks passed — process submission
        $utm_source = htmlspecialchars($_POST['utm_source'] ?? $_GET['utm_source'] ?? '', ENT_QUOTES, 'UTF-8');
        $utm_medium = htmlspecialchars($_POST['utm_medium'] ?? $_GET['utm_medium'] ?? '', ENT_QUOTES, 'UTF-8');
        $utm_campaign = htmlspecialchars($_POST['utm_campaign'] ?? $_GET['utm_campaign'] ?? '', ENT_QUOTES, 'UTF-8');
        $page_url = htmlspecialchars($_POST['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

        // Build email
        $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
        $subject = "New Enquiry: $name - $course";

        $body = "Name: $name\r\n";
        $body .= "Phone: $phone\r\n";
        $body .= "Email: $email\r\n";
        $body .= "Course: $course\r\n";
        $body .= "Source Page: $page_url\r\n";
        if ($utm_source) $body .= "UTM Source: $utm_source\r\n";
        if ($utm_medium) $body .= "UTM Medium: $utm_medium\r\n";
        if ($utm_campaign) $body .= "UTM Campaign: $utm_campaign\r\n";
        $body .= "Time: " . date('Y-m-d H:i:s') . "\r\n";

        $headers = "From: noreply@ipu.co.in\r\n";
        $headers .= "Reply-To: " . ($email ?: 'admission@ipu.co.in') . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
        $headers .= "X-Priority: 1\r\n";

        // Send email
        mail($to, $subject, $body, $headers);

        // Send to Google Sheets
        $sheets_data = json_encode([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'course' => $course,
            'source' => $utm_source,
            'medium' => $utm_medium,
            'campaign' => $utm_campaign,
            'page' => $page_url,
        ]);

        $ch = curl_init('https://script.google.com/macros/s/AKfycbz_8geQQfgTGW5FT6kVahb7KeVGh0EGyIBzKvwcISjqA0ZN7GhALp9jXqTGN0iqiQaQvw/exec');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $sheets_data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_exec($ch);
        curl_close($ch);

        // Mark as submitted — session
        $submitted_phones[] = $phone;
        $_SESSION['submitted_phones'] = $submitted_phones;
        $_SESSION['last_submit_time'] = time();

        // Mark as submitted — cookie (24 hours)
        setcookie($cookie_key, '1', time() + 86400, '/', '', true, true);

        // Store for enhanced conversions on thank-you page
        $_SESSION['enh_email'] = $email;
        $_SESSION['enh_phone'] = $phone;

        // Redirect to thank-you
        header("Location: /thank-you.php");
        exit();
    }
}
?>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l website_download/include/form-handler.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add website_download/include/form-handler.php
git commit -m "fix: add 5-layer duplicate prevention to form-handler.php"
```

---

### Task 2: Add duplicate prevention to sendemail.php

**Files:**
- Modify: `website_download/sendemail.php`

- [ ] **Step 1: Add the same dedup layers to sendemail.php**

Replace `website_download/sendemail.php` with this version:

```php
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Honeypot check
    if (!empty($_POST['website'])) {
        header("Location: /thank-you.php");
        exit();
    }

    // Time-based bot check
    $form_loaded = $_SESSION['form_loaded_at'] ?? 0;
    if ($form_loaded > 0 && (time() - $form_loaded) < 3) {
        header("Location: /thank-you.php");
        exit();
    }

    // Cooldown — block resubmission within 5 minutes
    $last_submit = $_SESSION['last_submit_time'] ?? 0;
    if ($last_submit > 0 && (time() - $last_submit) < 300) {
        header("Location: /thank-you.php");
        exit();
    }

    // Sanitize input
    $name   = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email  = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone  = htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
    $course = htmlspecialchars(trim($_POST['course'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate
    if (!$name || !$phone || !$course) {
        header("Location: /?error=fields");
        exit();
    }

    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        header("Location: /?error=phone");
        exit();
    }

    // Phone-based session dedup
    $submitted_phones = $_SESSION['submitted_phones'] ?? [];
    if (in_array($phone, $submitted_phones)) {
        header("Location: /thank-you.php");
        exit();
    }

    // Cookie-based dedup (24 hours)
    $phone_hash = hash('sha256', $phone . 'ipu_salt_2026');
    $cookie_key = 'ipu_eq_' . substr($phone_hash, 0, 12);
    if (isset($_COOKIE[$cookie_key])) {
        header("Location: /thank-you.php");
        exit();
    }

    // Capture UTM & page source
    $page_url = htmlspecialchars($_POST['page_url'] ?? $_SERVER['HTTP_REFERER'] ?? '', ENT_QUOTES, 'UTF-8');

    // Send email
    $to = "sumitdabass@gmail.com,sonamdabas222@gmail.com";
    $subject = "New Enquiry: $name - $course";

    $message = "Name: $name\r\n";
    $message .= "Phone: $phone\r\n";
    $message .= "Email: $email\r\n";
    $message .= "Course: $course\r\n";
    $message .= "Source: $page_url\r\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\r\n";

    $headers  = "From: noreply@ipu.co.in\r\n";
    $headers .= "Reply-To: " . ($email ?: 'admission@ipu.co.in') . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "X-Priority: 1\r\n";

    mail($to, $subject, $message, $headers);

    // Send to Google Sheet
    $url = "https://script.google.com/macros/s/AKfycbz_8geQQfgTGW5FT6kVahb7KeVGh0EGyIBzKvwcISjqA0ZN7GhALp9jXqTGN0iqiQaQvw/exec";
    $data = json_encode([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'course' => $course,
        'city' => 'Website',
        'message' => $page_url,
        'source' => 'ipu.co.in',
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    curl_exec($ch);
    curl_close($ch);

    // Mark as submitted — session
    $submitted_phones[] = $phone;
    $_SESSION['submitted_phones'] = $submitted_phones;
    $_SESSION['last_submit_time'] = time();

    // Mark as submitted — cookie (24 hours)
    setcookie($cookie_key, '1', time() + 86400, '/', '', true, true);

    // Store for enhanced conversions
    $_SESSION['enh_email'] = $email;
    $_SESSION['enh_phone'] = $phone;

    // Redirect to thank-you page
    header("Location: /thank-you.php");
    exit();

} else {
    header("Location: /");
    exit();
}
?>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l website_download/sendemail.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add website_download/sendemail.php
git commit -m "fix: add duplicate prevention to sendemail.php (session + cookie + cooldown)"
```

---

### Task 3: Add client-side double-submit prevention to sidebar-cta.php

**Files:**
- Modify: `website_download/include/sidebar-cta.php`

- [ ] **Step 1: Add JavaScript to disable button after first click and pass page_url**

Add just before the closing `</div>` of the enquiry form section, after the submit button's `</p>` tag, add a hidden field for page_url and a script to prevent double-click submissions:

After the honeypot `<div>`, add:
```html
<input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
```

After the closing `</form>` tag, before `</div>`, add:
```html
<script>
document.querySelector('.enquiry-form').addEventListener('submit', function(e) {
  var btn = this.querySelector('button[type="submit"]');
  if (btn.disabled) { e.preventDefault(); return false; }
  btn.disabled = true;
  btn.textContent = 'Submitting...';
  btn.style.opacity = '0.7';
});
</script>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l website_download/include/sidebar-cta.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add website_download/include/sidebar-cta.php
git commit -m "fix: add client-side double-submit prevention + page_url tracking"
```

---

## Phase 2: SEO & AI Improvements

### Task 4: Add BreadcrumbList JSON-LD component

**Files:**
- Create: `website_download/include/components/breadcrumb-schema.php`

- [ ] **Step 1: Create reusable breadcrumb schema component**

```php
<?php
/**
 * Breadcrumb Schema Component — Generates BreadcrumbList JSON-LD
 *
 * Usage:
 *   $breadcrumbs = [['Home', '/'], ['B.Tech', '/IPU-B-Tech-admission-2026.php'], ['MAIT', '']];
 *   include 'include/components/breadcrumb-schema.php';
 */
$breadcrumbs = $breadcrumbs ?? [];
if (count($breadcrumbs) < 2) return;
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
<?php foreach ($breadcrumbs as $i => $crumb): ?>
    {
      "@type": "ListItem",
      "position": <?= $i + 1 ?>,
      "name": "<?= htmlspecialchars($crumb[0], ENT_QUOTES) ?>"<?php if (!empty($crumb[1])): ?>,
      "item": "https://ipu.co.in<?= htmlspecialchars($crumb[1], ENT_QUOTES) ?>"<?php endif; ?>
    }<?= $i < count($breadcrumbs) - 1 ? ',' : '' ?>
<?php endforeach; ?>
  ]
}
</script>
```

- [ ] **Step 2: Commit**

```bash
git add website_download/include/components/breadcrumb-schema.php
git commit -m "feat: add reusable BreadcrumbList JSON-LD component"
```

---

### Task 5: Add "Last Updated" date component

**Files:**
- Create: `website_download/include/components/last-updated.php`

- [ ] **Step 1: Create last-updated component**

```php
<?php
/**
 * Last Updated Date Component — Shows visible freshness signal
 *
 * Usage:
 *   $last_updated = '2026-04-06';
 *   include 'include/components/last-updated.php';
 */
$last_updated = $last_updated ?? date('Y-m-d');
$formatted = date('j F Y', strtotime($last_updated));
?>
<p style="font-size:13px;color:#64748b;margin-bottom:20px">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="#64748b" style="vertical-align:-2px;margin-right:4px"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/></svg>
  Last Updated: <time datetime="<?= $last_updated ?>"><?= $formatted ?></time>
</p>
```

- [ ] **Step 2: Commit**

```bash
git add website_download/include/components/last-updated.php
git commit -m "feat: add last-updated date component for SEO freshness signal"
```

---

### Task 6: Add CollegeOrUniversity schema component for college pages

**Files:**
- Create: `website_download/include/components/college-schema.php`

- [ ] **Step 1: Create college schema component**

```php
<?php
/**
 * CollegeOrUniversity Schema Component
 *
 * Usage:
 *   $college = [
 *     'name' => 'Maharaja Agrasen Institute of Technology',
 *     'short_name' => 'MAIT',
 *     'url' => 'https://ipu.co.in/mait-admission.php',
 *     'address' => 'Sector-22, Rohini, Delhi-110085',
 *     'founded' => '1999',
 *     'courses' => ['B.Tech CSE', 'B.Tech IT', 'B.Tech ECE'],
 *     'total_seats' => 780,
 *     'accreditation' => 'NAAC, AICTE',
 *   ];
 *   include 'include/components/college-schema.php';
 */
$college = $college ?? [];
if (empty($college['name'])) return;
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollegeOrUniversity",
  "name": "<?= htmlspecialchars($college['name'], ENT_QUOTES) ?>",
  "alternateName": "<?= htmlspecialchars($college['short_name'] ?? '', ENT_QUOTES) ?>",
  "url": "<?= htmlspecialchars($college['url'] ?? '', ENT_QUOTES) ?>",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "<?= htmlspecialchars($college['address'] ?? '', ENT_QUOTES) ?>",
    "addressRegion": "Delhi NCR",
    "addressCountry": "IN"
  },
  <?php if (!empty($college['founded'])): ?>"foundingDate": "<?= htmlspecialchars($college['founded'], ENT_QUOTES) ?>",<?php endif; ?>
  "parentOrganization": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University",
    "alternateName": "GGSIPU"
  },
  <?php if (!empty($college['accreditation'])): ?>"hasCredential": "<?= htmlspecialchars($college['accreditation'], ENT_QUOTES) ?>",<?php endif; ?>
  <?php if (!empty($college['total_seats'])): ?>"numberOfStudents": <?= (int)$college['total_seats'] ?>,<?php endif; ?>
  "knowsAbout": [<?php echo implode(',', array_map(function($c) { return '"' . htmlspecialchars($c, ENT_QUOTES) . '"'; }, $college['courses'] ?? [])); ?>]
}
</script>
```

- [ ] **Step 2: Commit**

```bash
git add website_download/include/components/college-schema.php
git commit -m "feat: add CollegeOrUniversity schema component for college pages"
```

---

### Task 7: Update existing college pages with new components

**Files:**
- Modify: All 21 existing college pages (mait-admission.php, msit-admission.php, etc.)

For each existing college page, add three things:
1. Breadcrumb schema (after Article schema)
2. College schema (after Course schema)
3. Last Updated date (after AI Summary)

- [ ] **Step 1: Update mait-admission.php as the reference implementation**

After the `</script>` closing the Course schema (line 46), before `</head>`, add:
```php
<?php
$breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], ['MAIT Admission', '']];
include 'include/components/breadcrumb-schema.php';
$college = [
  'name' => 'Maharaja Agrasen Institute of Technology',
  'short_name' => 'MAIT',
  'url' => 'https://ipu.co.in/mait-admission.php',
  'address' => 'Sector-22, Rohini, Delhi-110085',
  'founded' => '1999',
  'courses' => ['B.Tech CSE', 'B.Tech IT', 'B.Tech ECE', 'B.Tech EEE', 'B.Tech AIML', 'BBA', 'BJMC'],
  'total_seats' => 780,
  'accreditation' => 'NAAC, AICTE',
];
include 'include/components/college-schema.php';
?>
```

After the AI Summary section, add:
```php
<?php $last_updated = '2026-04-06'; include 'include/components/last-updated.php'; ?>
```

- [ ] **Step 2: Apply same pattern to remaining 20 college pages**

Each college page gets the same three additions with college-specific data. The `$college` array values change per college, the pattern is identical.

- [ ] **Step 3: Commit**

```bash
git add website_download/*.php
git commit -m "feat: add breadcrumb, college schema & last-updated to all existing college pages"
```

---

### Task 8: Add BreadcrumbList + Last Updated to all course pages

**Files:**
- Modify: All course pages (IPU-B-Tech-admission-2026.php, ipu-bba-admission.php, etc.)

- [ ] **Step 1: Add breadcrumb schema + last-updated to each course page**

Same pattern as college pages. Example for IPU-B-Tech-admission-2026.php:
```php
<?php
$breadcrumbs = [['Home', '/'], ['B.Tech Admission 2026', '']];
include 'include/components/breadcrumb-schema.php';
?>
```

And after the AI Summary:
```php
<?php $last_updated = '2026-04-06'; include 'include/components/last-updated.php'; ?>
```

- [ ] **Step 2: Commit**

```bash
git add website_download/*.php
git commit -m "feat: add breadcrumb schema + last-updated to all course pages"
```

---

### Task 9: Expand agent-data.php with all colleges and courses

**Files:**
- Modify: `website_download/api/agent-data.php`

- [ ] **Step 1: Expand the colleges array from 4 to include all 46 colleges (existing 21 + new 25)**

Add all colleges with their official seat intake data, courses offered, address, and key stats to the `$colleges` array in agent-data.php. Each college entry should include: name, short_name, location, courses (array), total_seats, accreditation, placement_range, website_page (URL on ipu.co.in).

- [ ] **Step 2: Expand the courses array to include all 12 courses**

Add BEd, LLM, LLB 3-year to the existing courses list (B.Tech, MBA, BBA, Law, BCA, B.Com, BJMC, BA Economics, BA English, BArch, MCA).

- [ ] **Step 3: Add a "seat_intake" action that returns official GGSIPU seat data by programme**

- [ ] **Step 4: Verify PHP syntax**

Run: `php -l website_download/api/agent-data.php`
Expected: `No syntax errors detected`

- [ ] **Step 5: Commit**

```bash
git add website_download/api/agent-data.php
git commit -m "feat: expand AI agent API with all 46 colleges and 12 courses"
```

---

### Task 10: Expand llms.txt with full college and course data

**Files:**
- Modify: `website_download/llms.txt` (or `website_download/.well-known/llms.txt`)

- [ ] **Step 1: Add all 46 colleges to the entity relationship section**

Under the colleges section, add each college with: name, abbreviation, location, courses offered, total seats, page URL.

- [ ] **Step 2: Add all course URLs and descriptions**

- [ ] **Step 3: Update statistics (number of colleges, pages, students served)**

- [ ] **Step 4: Commit**

```bash
git add website_download/llms.txt
git commit -m "feat: expand llms.txt with all colleges and courses for AI models"
```

---

## Phase 3: Add 25 New College Pages

### Task 11: Create college page template file

**Files:**
- Create: `website_download/include/templates/college-page-template.php`

- [ ] **Step 1: Extract the MAIT page pattern into a data-driven template**

Create a template that accepts a `$college_data` array and renders the full page. This allows creating new college pages with just a data array + template include. The template handles: base-head, meta tags, OG tags, schema markup, AI summary, all content sections, sidebar, FAQ, related pages, footer.

```php
<?php
/**
 * College Page Template
 *
 * Required $college_data keys:
 *   slug, name, short_name, title, meta_desc, canonical,
 *   og_title, og_desc, address, founded, accreditation,
 *   about_text, admission_text, courses (array of arrays),
 *   fees (array of arrays), cutoffs (array of arrays),
 *   placements (array), campus_life (array), faqs (array),
 *   related_pages (array), last_updated, total_seats
 */
$d = $college_data;
session_start(); ob_start(); include_once("include/form-handler.php");
include_once("include/base-head.php");
?>
<title><?= htmlspecialchars($d['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($d['meta_desc']) ?>">
<link rel="canonical" href="https://ipu.co.in/<?= htmlspecialchars($d['slug']) ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= htmlspecialchars($d['og_title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($d['og_desc']) ?>">
<meta property="og:url" content="https://ipu.co.in/<?= htmlspecialchars($d['slug']) ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="IPU Admission Guide">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($d['og_title']) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($d['og_desc']) ?>">

<!-- Article Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?= htmlspecialchars($d['og_title'], ENT_QUOTES) ?>",
  "description": "<?= htmlspecialchars($d['og_desc'], ENT_QUOTES) ?>",
  "author": {"@type": "Organization", "name": "IPU Admission Guide"},
  "publisher": {"@type": "Organization", "name": "IPU Admission Guide", "url": "https://ipu.co.in"},
  "datePublished": "<?= $d['last_updated'] ?>",
  "dateModified": "<?= $d['last_updated'] ?>"
}
</script>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Programmes at <?= htmlspecialchars($d['short_name'], ENT_QUOTES) ?>",
  "description": "Courses offered at <?= htmlspecialchars($d['name'], ENT_QUOTES) ?> under GGSIPU Delhi",
  "provider": {
    "@type": "EducationalOrganization",
    "name": "<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>",
    "parentOrganization": {"@type": "CollegeOrUniversity", "name": "Guru Gobind Singh Indraprastha University"}
  }
}
</script>

<?php
$breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], [$d['short_name'] . ' Admission', '']];
include 'include/components/breadcrumb-schema.php';

$college = [
  'name' => $d['name'], 'short_name' => $d['short_name'],
  'url' => 'https://ipu.co.in/' . $d['slug'],
  'address' => $d['address'], 'founded' => $d['founded'] ?? '',
  'courses' => array_column($d['courses'], 'name'),
  'total_seats' => $d['total_seats'] ?? 0,
  'accreditation' => $d['accreditation'] ?? '',
];
include 'include/components/college-schema.php';
?>
</head>
<body>
<?php include_once("include/base-nav.php"); ?>

<!-- Hero -->
<?php
$hero_title = $d['short_name'] . " Admission 2026 – Courses, Cutoff & Placements";
$hero_breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], [$d['short_name'] . ' Admission', '']];
$hero_compact = true;
include 'include/components/hero-banner.php';
?>

<!-- Content -->
<section style="padding:50px 0">
<div class="container">
<div class="row">
<div class="col-lg-8">

  <!-- AI Summary -->
  <section id="ai-summary" style="background:#f0f7ff;border-left:4px solid #1a3a9c;padding:20px 24px;border-radius:0 8px 8px 0;margin-bottom:32px">
    <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
    <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($d['ai_summary']) ?></p>
  </section>

  <?php $last_updated = $d['last_updated']; include 'include/components/last-updated.php'; ?>

  <h1><?= htmlspecialchars($d['short_name']) ?> IPU Admission 2026 – Complete Guide</h1>

  <?= $d['about_text'] ?>

  <h2>Courses Offered at <?= htmlspecialchars($d['short_name']) ?></h2>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <thead>
      <tr style="background:#0d1b6e;color:#fff">
        <th style="padding:10px 14px;text-align:left">Programme</th>
        <th style="padding:10px 14px;text-align:left">Duration</th>
        <th style="padding:10px 14px;text-align:left">Seats</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($d['courses'] as $i => $c): ?>
      <tr style="border-bottom:1px solid #e2e8f0;<?= $i % 2 ? 'background:#f8faff' : '' ?>">
        <td style="padding:10px 14px"><?= htmlspecialchars($c['name']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($c['duration']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($c['seats']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Admission Process 2026</h2>
  <?= $d['admission_text'] ?>

  <?php if (!empty($d['cutoffs'])): ?>
  <h2><?= htmlspecialchars($d['short_name']) ?> Cutoff Trends</h2>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <thead>
      <tr style="background:#0d1b6e;color:#fff">
        <th style="padding:10px 14px;text-align:left">Branch</th>
        <th style="padding:10px 14px;text-align:left">Round 1 Cutoff</th>
        <th style="padding:10px 14px;text-align:left">Last Round Cutoff</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($d['cutoffs'] as $i => $c): ?>
      <tr style="border-bottom:1px solid #e2e8f0;<?= $i % 2 ? 'background:#f8faff' : '' ?>">
        <td style="padding:10px 14px"><?= htmlspecialchars($c['branch']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($c['round1']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($c['last_round']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <h2>Placements at <?= htmlspecialchars($d['short_name']) ?></h2>
  <ul>
    <?php foreach ($d['placements'] as $p): ?>
    <li><strong><?= htmlspecialchars($p['label']) ?>:</strong> <?= htmlspecialchars($p['value']) ?></li>
    <?php endforeach; ?>
  </ul>

  <h2>Campus Life at <?= htmlspecialchars($d['short_name']) ?></h2>
  <ul>
    <?php foreach ($d['campus_life'] as $item): ?>
    <li><?= htmlspecialchars($item) ?></li>
    <?php endforeach; ?>
  </ul>

  <p>For personalised admission guidance to <?= htmlspecialchars($d['short_name']) ?>, call <a href="tel:+919899991342">9899991342</a> today.</p>

  <!-- Fee & Seat Intake -->
  <?php if (!empty($d['fees'])): ?>
  <div style="margin:30px 0;padding:24px;background:#f8faff;border-radius:12px;border:1px solid #e2e8f0">
    <h3 style="color:#0d1b6e;margin-bottom:16px">Fee Structure & Seat Intake (2025-26)</h3>
    <p style="font-size:13px;color:#64748b;margin-bottom:12px">As per GGSIPU Official Notification</p>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="background:#0d1b6e;color:#fff">
          <th style="padding:10px;text-align:left;border-radius:6px 0 0 0">Course</th>
          <th style="padding:10px;text-align:center">Annual Fee</th>
          <th style="padding:10px;text-align:center;border-radius:0 6px 0 0">Seats</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($d['fees'] as $i => $f): ?>
        <tr style="border-bottom:1px solid #e2e8f0;<?= $i % 2 ? 'background:#f0f4ff' : '' ?>">
          <td style="padding:10px"><?= htmlspecialchars($f['course']) ?></td>
          <td style="padding:10px;text-align:center"><?= htmlspecialchars($f['fee']) ?></td>
          <td style="padding:10px;text-align:center"><?= htmlspecialchars($f['seats']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="font-size:12px;color:#94a3b8;margin:12px 0 0">Source: GGSIPU Official Notification. Total seats at <?= htmlspecialchars($d['short_name']) ?>: ~<?= $d['total_seats'] ?>.</p>
  </div>
  <?php endif; ?>

</div>
<div class="col-lg-4">
  <?php include 'include/sidebar-cta.php'; ?>
</div>
</div>
</div>
</section>

<!-- CTA Strip -->
<?php $cta_heading = "Need Help with " . $d['short_name'] . " Admission?"; $cta_subtext = "Get free expert counselling on cutoffs, choice filling and management quota"; include 'include/components/cta-strip.php'; ?>

<!-- FAQ Section -->
<?php $faqs = $d['faqs']; include 'include/components/faq-section.php'; ?>

<!-- Related Pages -->
<?php $related_pages = $d['related_pages']; include 'include/components/related-pages.php'; ?>

<?php include_once("include/base-footer.php"); ?>
</body>
</html>
```

- [ ] **Step 2: Verify PHP syntax**

Run: `php -l website_download/include/templates/college-page-template.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add website_download/include/templates/college-page-template.php
git commit -m "feat: create data-driven college page template"
```

---

### Task 12-36: Create 25 new college pages

Each college page is a single PHP file that sets `$college_data` and includes the template.

**College list with filenames:**

| # | Task | File | College |
|---|------|------|---------|
| 12 | Task 12 | `dtc-admission.php` | Delhi Technical Campus |
| 13 | Task 13 | `jemtec-admission.php` | JIMS Engineering Management Technical Campus |
| 14 | Task 14 | `echelon-admission.php` | Echelon Institute of Technology |
| 15 | Task 15 | `dme-admission.php` | Delhi Metropolitan Education |
| 16 | Task 16 | `cpj-admission.php` | Chandarprabhu Jain College |
| 17 | Task 17 | `fairfield-admission.php` | Fairfield Institute |
| 18 | Task 18 | `rdias-admission.php` | Rukmini Devi Institute |
| 19 | Task 19 | `gibs-admission.php` | Gitarattan International Business School |
| 20 | Task 20 | `jims-kalkaji-admission.php` | JIMS Kalkaji |
| 21 | Task 21 | `jims-vasant-kunj-admission.php` | JIMS Vasant Kunj |
| 22 | Task 22 | `ideal-admission.php` | Ideal Institute |
| 23 | Task 23 | `kcc-admission.php` | KCC Institute |
| 24 | Task 24 | `tecnia-admission.php` | Tecnia Institute |
| 25 | Task 25 | `dias-admission.php` | Delhi Institute of Advanced Studies (already exists — update with template) |
| 26 | Task 26 | `ndim-admission.php` | New Delhi Institute of Management |
| 27 | Task 27 | `tips-admission.php` | Trinity Institute of Professional Studies |
| 28 | Task 28 | `meri-admission.php` | MERI Janakpuri |
| 29 | Task 29 | `kasturi-ram-admission.php` | Kasturi Ram College |
| 30 | Task 30 | `lingayas-admission.php` | Lingayas Institute |
| 31 | Task 31 | `don-bosco-admission.php` | Don Bosco Institute |
| 32 | Task 32 | `gtb4cec-admission.php` | Guru Tegh Bahadur 4th Centenary Engineering College |
| 33 | Task 33 | `bcips-admission.php` | Banarsidas Chandiwala Institute |
| 34 | Task 34 | `sgtbimit-admission.php` | Sri Guru Tegh Bahadur Institute |
| 35 | Task 35 | `sirifort-admission.php` | Sirifort Institute |
| 36 | Task 36 | `gnit-admission.php` | Greater Noida Institute of Technology |

- [ ] **Each college page follows this pattern:**

```php
<?php
$college_data = [
  'slug' => 'dtc-admission.php',
  'name' => 'Delhi Technical Campus',
  'short_name' => 'DTC',
  'title' => 'DTC Greater Noida Admission 2026 | Courses, Cutoff, Fees & Placements – IPU',
  'meta_desc' => 'Delhi Technical Campus (DTC) Greater Noida – B.Tech CSE, AIML, BBA. IPU counselling cutoff, fees & placements. Call 9899991342 for guidance.',
  'og_title' => 'DTC Admission 2026 – IPU Courses, Cutoff & Placements',
  'og_desc' => 'Complete guide to DTC admission under IPU. B.Tech courses, cutoffs, placements and campus.',
  'canonical' => 'https://ipu.co.in/dtc-admission.php',
  'address' => '28/1, Knowledge Park-III, Greater Noida, U.P.',
  'founded' => '2007',
  'accreditation' => 'AICTE',
  'total_seats' => 1020,
  'last_updated' => '2026-04-06',
  'ai_summary' => 'Delhi Technical Campus (DTC) is an IPU-affiliated engineering college in Greater Noida offering B.Tech in CSE, AIML, AI&DS and more with a total intake of 1020+ seats. Admission through JEE Main via GGSIPU counselling.',
  'about_text' => '<h2>About DTC – IPU Affiliated College in Greater Noida</h2>
    <p>Delhi Technical Campus (DTC) is located in Knowledge Park-III, Greater Noida. Affiliated to GGSIPU and approved by AICTE, DTC offers B.Tech programmes in multiple specializations with one of the largest seat intakes among IPU colleges. For admission guidance, call <a href="tel:+919899991342">9899991342</a>.</p>',
  'courses' => [
    ['name' => 'B.Tech Computer Science & Engineering', 'duration' => '4 Years', 'seats' => '480'],
    ['name' => 'B.Tech AI & Machine Learning', 'duration' => '4 Years', 'seats' => '60'],
    ['name' => 'B.Tech AI & Data Science', 'duration' => '4 Years', 'seats' => '60'],
    ['name' => 'B.Tech Computer Science & Technology', 'duration' => '4 Years', 'seats' => '120'],
    // ... more courses from official data
  ],
  'admission_text' => '<p>Admission to DTC follows the GGSIPU counselling process based on <strong>JEE Main scores</strong>.</p>
    <ol>
      <li><strong>Clear JEE Main 2026</strong></li>
      <li><strong>Register on IPU Portal</strong> at ipu.ac.in</li>
      <li><strong>Choice Filling</strong> – Select DTC courses</li>
      <li><strong>Seat Allotment</strong> – Based on rank and preferences</li>
      <li><strong>Report to DTC</strong> – Document verification and fee payment</li>
    </ol>',
  'cutoffs' => [
    ['branch' => 'CSE', 'round1' => '75-80', 'last_round' => '65-72'],
    // ... more cutoff data
  ],
  'placements' => [
    ['label' => 'Average Package', 'value' => '4-8 LPA'],
    ['label' => 'Top Recruiters', 'value' => 'TCS, Infosys, Wipro, HCL, Accenture'],
    ['label' => 'Placement Rate', 'value' => '70-80%'],
  ],
  'campus_life' => [
    'Modern computer labs', 'Library with digital resources', 'Sports facilities',
    'Technical and cultural clubs', 'Cafeteria on campus',
  ],
  'fees' => [
    ['course' => 'B.Tech CSE', 'fee' => 'Rs. 1,41,750', 'seats' => '480'],
    // ... more fee data
  ],
  'faqs' => [
    ['question' => 'What is the cutoff for DTC CSE?', 'answer' => 'DTC CSE cutoff ranges between 65-80 percentile for General category. Call 9899991342 for current predictions.'],
    ['question' => 'How are placements at DTC?', 'answer' => 'DTC placements average 4-8 LPA with companies like TCS, Infosys, and Wipro recruiting regularly.'],
    ['question' => 'Is DTC a good college under IPU?', 'answer' => 'DTC has one of the largest B.Tech CSE intakes (480 seats) among IPU colleges, making it accessible for a wider range of JEE percentiles.'],
  ],
  'related_pages' => [
    ['title' => 'JEMTEC Admission', 'url' => '/jemtec-admission.php', 'desc' => 'JIMS Engineering Management Technical Campus guide'],
    ['title' => 'Top B.Tech Colleges IPU', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare engineering colleges under IPU'],
    ['title' => 'IPU B.Tech Admission 2026', 'url' => '/IPU-B-Tech-admission-2026.php', 'desc' => 'Complete B.Tech admission guide'],
  ],
];
include 'include/templates/college-page-template.php';
```

- [ ] **After creating each batch of 5 college pages, verify syntax and commit:**

```bash
php -l website_download/dtc-admission.php
# ... repeat for each file
git add website_download/*-admission.php
git commit -m "feat: add college pages for DTC, JEMTEC, Echelon, DME, CPJ"
```

---

### Task 37: Update sitemap.xml with all new college pages

**Files:**
- Modify: `website_download/sitemap.xml`

- [ ] **Step 1: Add all 25 new college page URLs to sitemap**

Add each new college page under the "College Authority" section with priority 0.85, changefreq monthly.

- [ ] **Step 2: Update the total page count in any header comments**

- [ ] **Step 3: Commit**

```bash
git add website_download/sitemap.xml
git commit -m "feat: add 25 new college pages to sitemap.xml"
```

---

### Task 38: Update navigation and college list page

**Files:**
- Modify: `website_download/ipu-colleges-list.php`
- Modify: `website_download/include/base-nav.php` (if colleges dropdown exists)

- [ ] **Step 1: Add all 25 new colleges to the colleges list page**

- [ ] **Step 2: Commit**

```bash
git add website_download/ipu-colleges-list.php website_download/include/base-nav.php
git commit -m "feat: add new colleges to college list page and navigation"
```

---

### Task 39: Update sidebar-cta.php course dropdown with new courses

**Files:**
- Modify: `website_download/include/sidebar-cta.php`

- [ ] **Step 1: Add missing course options to the dropdown**

Add these options to the `<select name="course">` dropdown:
```html
<option value="B.Ed">B.Ed</option>
<option value="LLB">LLB (3 Year)</option>
<option value="LLM">LLM</option>
<option value="B.Arch">B.Arch</option>
```

- [ ] **Step 2: Commit**

```bash
git add website_download/include/sidebar-cta.php
git commit -m "feat: add B.Ed, LLB, LLM, B.Arch to course dropdown"
```

---

### Task 40: Final cross-linking — add Related Pages to all new college pages

**Files:**
- All 25 new college pages

- [ ] **Step 1: Ensure each college page links to 3 related colleges/courses**

Each page's `related_pages` array should include:
- One nearby/similar college (same city or course category)
- One course pillar page (e.g., B.Tech admission, BBA admission)
- One comparison/list page (e.g., top colleges, cutoff analysis)

- [ ] **Step 2: Commit**

```bash
git add website_download/*.php
git commit -m "feat: add internal cross-linking across all new college pages"
```
