<?php
/**
 * College Page Template — Data-driven admission page
 *
 * Usage:
 *   $college_data = [
 *     // Required keys
 *     'slug'         => 'mait-admission',
 *     'name'         => 'Maharaja Agrasen Institute of Technology',
 *     'short_name'   => 'MAIT',
 *     'title'        => 'MAIT Delhi Admission 2026 | Courses, Cutoff, Fees & Placements – IPU',
 *     'meta_desc'    => 'MAIT Rohini – B.Tech CSE, IT, ECE. JEE Main cutoff & placements 7-15 LPA.',
 *     'og_title'     => 'MAIT Admission 2026 – IPU Courses, Cutoff & Placements',
 *     'og_desc'      => 'Complete guide to MAIT admission under IPU.',
 *     'address'      => 'Sector 22, Rohini, Delhi – 110086',
 *     'about_text'   => '<p>Established in 1999...</p>',          // raw HTML
 *     'admission_text' => '<ol><li>...</li></ol>',                // raw HTML
 *     'courses'      => [['name'=>'B.Tech CSE','duration'=>'4 Years','seats'=>180], ...],
 *     'placements'   => [['label'=>'Average Package','value'=>'7-15 LPA'], ...],
 *     'campus_life'  => ['Well-equipped labs', 'Sports grounds', ...],
 *     'faqs'         => [['question'=>'...','answer'=>'...'], ...],
 *     'related_pages'=> [['title'=>'...','url'=>'...','desc'=>'...'], ...],
 *     'last_updated' => '2026-04-06',
 *     'ai_summary'   => 'One-paragraph AI summary for LLMs...',
 *     'total_seats'  => 780,
 *
 *     // Optional keys
 *     'founded'      => '1999',
 *     'accreditation'=> 'NAAC A, AICTE',
 *     'cutoffs'      => [['branch'=>'CSE','round1'=>'88-91','last_round'=>'83-86'], ...],
 *     'fees'         => [['course'=>'B.Tech CSE','fee'=>'Rs. 1,55,700','seats'=>360], ...],
 *   ];
 *   include 'include/templates/college-page-template.php';
 *
 * Template path: include/templates/college-page-template.php
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────
session_start(); ob_start(); include_once("include/form-handler.php");

// ── Convenience aliases ───────────────────────────────────────────────────────
$cd          = $college_data;                           // short alias
$slug        = $cd['slug']        ?? '';
$name        = $cd['name']        ?? '';
$short_name  = $cd['short_name']  ?? $name;
$page_url    = 'https://ipu.co.in/' . $slug . '.php';
$last_updated = $cd['last_updated'] ?? date('Y-m-d');

// ── Head ─────────────────────────────────────────────────────────────────────
include_once("include/base-head.php");
?>
<title><?= htmlspecialchars($cd['title'] ?? $name . ' Admission 2026 – IPU') ?></title>
<meta name="description" content="<?= htmlspecialchars($cd['meta_desc'] ?? '') ?>">
<link rel="canonical" href="<?= htmlspecialchars($page_url) ?>">

<!-- Open Graph -->
<meta property="og:title"     content="<?= htmlspecialchars($cd['og_title'] ?? $cd['title'] ?? $name) ?>">
<meta property="og:description" content="<?= htmlspecialchars($cd['og_desc'] ?? $cd['meta_desc'] ?? '') ?>">
<meta property="og:url"       content="<?= htmlspecialchars($page_url) ?>">
<meta property="og:type"      content="article">
<meta property="og:site_name" content="IPU Admission Guide">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= htmlspecialchars($cd['og_title'] ?? $cd['title'] ?? $name) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($cd['og_desc'] ?? $cd['meta_desc'] ?? '') ?>">

<!-- Article Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?= htmlspecialchars($cd['og_title'] ?? $name . ' Admission 2026', ENT_QUOTES) ?>",
  "description": "<?= htmlspecialchars($cd['meta_desc'] ?? '', ENT_QUOTES) ?>",
  "author":    {"@type": "Organization", "name": "IPU Admission Guide"},
  "publisher": {"@type": "Organization", "name": "IPU Admission Guide", "url": "https://ipu.co.in"},
  "datePublished": "<?= htmlspecialchars($last_updated, ENT_QUOTES) ?>",
  "dateModified":  "<?= htmlspecialchars($last_updated, ENT_QUOTES) ?>"
}
</script>

<!-- Course Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "<?= htmlspecialchars(isset($cd['courses'][0]) ? $cd['courses'][0]['name'] . ' at ' . $short_name : 'Courses at ' . $short_name, ENT_QUOTES) ?>",
  "description": "<?= htmlspecialchars('Programmes at ' . $name . ' under GGSIPU Delhi', ENT_QUOTES) ?>",
  "provider": {
    "@type": "EducationalOrganization",
    "name": "<?= htmlspecialchars($name, ENT_QUOTES) ?>",
    "parentOrganization": {"@type": "CollegeOrUniversity", "name": "Guru Gobind Singh Indraprastha University"}
  }
}
</script>

<?php
// ── Breadcrumb Schema Component ───────────────────────────────────────────────
$breadcrumbs = [
    ['Home',       '/'],
    ['Admissions', '/ipu-admission-guide.php'],
    [$short_name . ' Admission', ''],
];
include 'include/components/breadcrumb-schema.php';
unset($breadcrumbs);

// ── College Schema Component ──────────────────────────────────────────────────
$college = [
    'name'        => $name,
    'short_name'  => $short_name,
    'url'         => $page_url,
    'address'     => $cd['address'] ?? '',
    'total_seats' => $cd['total_seats'] ?? null,
];
if (!empty($cd['founded']))      $college['founded']      = $cd['founded'];
if (!empty($cd['accreditation'])) $college['accreditation'] = $cd['accreditation'];
if (!empty($cd['courses'])) {
    $college['courses'] = array_column($cd['courses'], 'name');
}
include 'include/components/college-schema.php';
unset($college);
?>
</head>
<body>
<?php include_once("include/base-nav.php"); ?>

<!-- Hero -->
<?php
$hero_title = htmlspecialchars($short_name . ' Admission 2026 – Courses, Cutoff &amp; Placements');
$hero_breadcrumbs = [
    ['Home',       '/'],
    ['Admissions', '/ipu-admission-guide.php'],
    [$short_name . ' Admission', ''],
];
$hero_compact = true;
include 'include/components/hero-banner.php';
unset($hero_title, $hero_breadcrumbs, $hero_compact);
?>

<!-- Main Content -->
<section style="padding:50px 0">
<div class="container">
<div class="row">

<!-- ── 8-column content column ─────────────────────────────────────────── -->
<div class="col-lg-8">

  <!-- AI Summary (hidden visually; exposed to crawlers & LLMs) -->
  <section id="ai-summary" aria-hidden="true"
           style="background:#f0f7ff;border-left:4px solid #1a3a9c;padding:20px 24px;border-radius:0 8px 8px 0;margin-bottom:32px">
    <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
    <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($cd['ai_summary'] ?? '') ?></p>
  </section>

  <!-- Last Updated -->
  <?php
  $last_updated = $cd['last_updated'] ?? date('Y-m-d');
  include 'include/components/last-updated.php';
  unset($last_updated);
  ?>

  <!-- H1 -->
  <h1><?= htmlspecialchars($short_name) ?> IPU Admission 2026 – Complete Guide</h1>

  <!-- About -->
  <?= $cd['about_text'] ?? '' ?>

  <!-- Courses Table -->
  <?php if (!empty($cd['courses'])): ?>
  <h2>Courses Offered at <?= htmlspecialchars($short_name) ?></h2>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <thead>
      <tr style="background:#0d1b6e;color:#fff">
        <th style="padding:10px 14px;text-align:left">Programme</th>
        <th style="padding:10px 14px;text-align:left">Duration</th>
        <th style="padding:10px 14px;text-align:left">Seats (Approx)</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cd['courses'] as $i => $course): ?>
      <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f8faff' : '' ?>">
        <td style="padding:10px 14px"><?= htmlspecialchars($course['name']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($course['duration']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars((string)($course['seats'] ?? '')) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- Admission Process -->
  <?php if (!empty($cd['admission_text'])): ?>
  <h2><?= htmlspecialchars($short_name) ?> Admission Process 2026</h2>
  <?= $cd['admission_text'] ?>
  <?php endif; ?>

  <!-- Cutoffs Table (optional) -->
  <?php if (!empty($cd['cutoffs'])): ?>
  <h2><?= htmlspecialchars($short_name) ?> Cutoff Trends</h2>
  <table style="width:100%;border-collapse:collapse;margin:16px 0">
    <thead>
      <tr style="background:#0d1b6e;color:#fff">
        <th style="padding:10px 14px;text-align:left">Branch</th>
        <th style="padding:10px 14px;text-align:left">Round 1 Cutoff (Percentile)</th>
        <th style="padding:10px 14px;text-align:left">Last Round Cutoff</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cd['cutoffs'] as $i => $cutoff): ?>
      <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f8faff' : '' ?>">
        <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['branch']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['round1']) ?></td>
        <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['last_round']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <!-- Placements -->
  <?php if (!empty($cd['placements'])): ?>
  <h2>Placements at <?= htmlspecialchars($short_name) ?></h2>
  <p><?= htmlspecialchars($short_name) ?> has a strong placement record among IPU-affiliated colleges. Key highlights:</p>
  <ul>
    <?php foreach ($cd['placements'] as $item): ?>
    <li><strong><?= htmlspecialchars($item['label']) ?>:</strong> <?= htmlspecialchars($item['value']) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <!-- Campus Life -->
  <?php if (!empty($cd['campus_life'])): ?>
  <h2>Campus Life at <?= htmlspecialchars($short_name) ?></h2>
  <ul>
    <?php foreach ($cd['campus_life'] as $item): ?>
    <li><?= htmlspecialchars($item) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

  <!-- CTA paragraph -->
  <p><?= htmlspecialchars($short_name) ?> is a top choice for students targeting admission under IPU. For personalised admission guidance, call <a href="tel:+919899991342">9899991342</a> today.</p>

  <!-- Fee & Seat Intake (optional) -->
  <?php if (!empty($cd['fees'])): ?>
  <div style="margin:30px 0;padding:24px;background:#f8faff;border-radius:12px;border:1px solid #e2e8f0">
    <h3 style="color:#0d1b6e;margin-bottom:16px">Fee Structure &amp; Seat Intake (2025-26)</h3>
    <p style="font-size:13px;color:#64748b;margin-bottom:12px">As per 6th SFRC, Delhi Gazette Notification dated 14.07.2025</p>
    <table style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="background:#0d1b6e;color:#fff">
          <th style="padding:10px;text-align:left;border-radius:6px 0 0 0">Course</th>
          <th style="padding:10px;text-align:center">Annual Fee</th>
          <th style="padding:10px;text-align:center;border-radius:0 6px 0 0">Seats</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cd['fees'] as $i => $row): ?>
        <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f0f4ff' : '' ?>">
          <td style="padding:10px"><?= htmlspecialchars($row['course']) ?></td>
          <td style="padding:10px;text-align:center"><?= htmlspecialchars($row['fee']) ?></td>
          <td style="padding:10px;text-align:center"><?= htmlspecialchars((string)($row['seats'] ?? '')) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p style="font-size:12px;color:#94a3b8;margin:12px 0 0">Source: GGSIPU Official Notification. Additional charges: University fee Rs. 20,000/yr + Exam fee Rs. 3,000/yr + Innovation fee Rs. 500/yr. Alumni contribution Rs. 2,000 (one-time). Total seats at <?= htmlspecialchars($short_name) ?>: ~<?= (int)($cd['total_seats'] ?? 0) ?>.</p>
  </div>
  <?php endif; ?>

</div><!-- /col-lg-8 -->

<!-- ── 4-column sidebar ─────────────────────────────────────────────────── -->
<div class="col-lg-4">
  <?php include 'include/sidebar-cta.php'; ?>
</div>

</div><!-- /row -->
</div><!-- /container -->
</section>

<!-- CTA Strip -->
<?php
$cta_heading = 'Need Help with ' . $short_name . ' Admission?';
$cta_subtext  = 'Get free expert counselling on cutoffs, choice filling and management quota';
include 'include/components/cta-strip.php';
unset($cta_heading, $cta_subtext);
?>

<!-- FAQ Section -->
<?php
$faqs = $cd['faqs'] ?? [];
include 'include/components/faq-section.php';
unset($faqs);
?>

<!-- Related Pages -->
<?php
$related_pages = $cd['related_pages'] ?? [];
include 'include/components/related-pages.php';
unset($related_pages);
?>

<?php include_once("include/base-footer.php"); ?>
</body>
</html>
