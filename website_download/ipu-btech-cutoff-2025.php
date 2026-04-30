<?php session_start(); ob_start(); include_once("include/form-handler.php"); ?>
<?php include_once("include/base-head.php"); ?>
<title>IPU B.Tech Cutoff 2025 | GGSIPU Round 1, 2, 3 JEE Main Rank</title>
<meta name="description" content="IPU B.Tech cutoff 2025 – Round 1, 2 & 3 JEE Main closing ranks for CSE, IT, ECE at USICT, MAIT, MSIT, BPIT, BVP, GTBIT. General category Delhi quota cutoffs. Call 9899991342.">
<meta name="keywords" content="ipu btech cutoff, ggsipu cutoff btech, ipu jee main cutoff, ipu btech cutoff 2025, ggsipu btech closing rank">
<link rel="canonical" href="https://ipu.co.in/ipu-btech-cutoff-2025.php">

<!-- Open Graph -->
<meta property="og:title" content="IPU B.Tech Cutoff 2025 – Round 1, 2 & 3 JEE Main Closing Ranks">
<meta property="og:description" content="College-wise B.Tech cutoff analysis for GGSIPU 2025. CSE, IT, ECE round-wise closing ranks for Delhi quota.">
<meta property="og:url" content="https://ipu.co.in/ipu-btech-cutoff-2025.php">
<meta property="og:type" content="article">
<meta property="og:site_name" content="IPU Admission Guide">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="IPU B.Tech Cutoff 2025 – Round-wise JEE Main Closing Ranks">
<meta name="twitter:description" content="College-wise B.Tech cutoff analysis for GGSIPU 2025. CSE, IT, ECE round-wise closing ranks.">

<!-- Article Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "IPU B.Tech Cutoff 2025 – Round 1, 2 & 3 JEE Main Rank",
  "description": "Complete round-wise B.Tech cutoff analysis for GGSIPU 2025 including CSE, IT, ECE closing ranks for top colleges.",
  "author": {"@type": "Organization", "name": "IPU Admission Guide"},
  "publisher": {"@type": "Organization", "name": "IPU Admission Guide", "url": "https://ipu.co.in"},
  "datePublished": "2025-10-15",
  "dateModified": "2026-03-25"
}
</script>
</head>
<body>
<?php include_once("include/base-nav.php"); ?>

<!-- Hero -->
<?php
$hero_title = "IPU B.Tech Cutoff 2025 – Round 1, 2 & 3 (JEE Main Rank)";
$hero_subtitle = "College-wise closing ranks for CSE, IT & ECE – General Category, Delhi Quota";
$hero_breadcrumbs = [['Home', '/'], ['B.Tech Admission', '/IPU-B-Tech-admission-2026.php'], ['B.Tech Cutoff 2025', '']];
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
    <p style="margin:0;color:#4a5568;font-size:15px">IPU B.Tech cutoff is based on JEE Main All India Rank (AIR). Based on official 2025 GGSIPU counselling data, USICT Dwarka (Dual Degree CSE) remains the most competitive with CSE closing around rank 38,638 in Round 1 (Delhi quota). MAIT Rohini Shift-I CSE closes around 70,889 in R1, MSIT Janakpuri around 96,135. Cutoffs relax ~30% from Round 1 to Round 3 as students vacate seats. Delhi (Home State) quota cutoffs are significantly more relaxed than Outside Delhi. The tables below are populated directly from the official GGSIPU cut-off list. For personalised rank analysis, call <a href="tel:+919899991342" style="color:#1a3a9c;font-weight:600">9899991342</a>.</p>
  </section>

  <p>IP University (GGSIPU) B.Tech admission is conducted through centralized counselling based on <strong>JEE Main All India Rank</strong>. The cutoff is the closing rank at which the last seat is allotted in each round of counselling. Understanding these cutoffs is essential for effective choice filling and college selection strategy.</p>

  <p style="background:#fff8e1;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:0 8px 8px 0;font-size:14px;color:#4a5568;margin:20px 0"><strong>Note:</strong> Cutoffs shown below are for <strong>General Category (OPNO), Home State (Delhi) quota</strong>. Outside Delhi cutoffs are typically 40–60% lower (better ranks required). Category-wise cutoffs (SC/ST/OBC/EWS) are more relaxed.</p>

  <?php
  // ── Data-driven cutoff tables — sourced from official GGSIPU 2025 counselling ──
  $cutoffs_data = include 'include/data/btech-cutoffs-2025.php';

  $cutoff_colleges = [
    'University School of Information & Communication Technology' => ['short' => 'USICT Dwarka',          'page' => '/usict-admission.php'],
    'University School of Automation & Robotics'                  => ['short' => 'USAR Dwarka',           'page' => '/usar-admission.php'],
    'University School of Chemical Technology'                    => ['short' => 'USCT Dwarka',           'page' => '/usct-admission.php'],
    'Maharaja Agrasen Institute of Technology'                    => ['short' => 'MAIT Rohini',           'page' => '/mait-admission.php'],
    'Maharaja Surajmal Institute Technology'                      => ['short' => 'MSIT Janakpuri',        'page' => '/msit-admission.php'],
    'Bharati Vidyapeeths College of Engineering'                  => ['short' => 'BVCOE Paschim Vihar',   'page' => '/BVP.php'],
    'Bhagwan Parshuram Institute of Technology'                   => ['short' => 'BPIT Rohini',           'page' => '/BPIT.php'],
    'Vivekananda Institute of Professional Studies'               => ['short' => 'VIPS Pitampura',        'page' => '/vips-pitampura-courses.php'],
    'Guru Teg Bahadur Institute of Technology'                    => ['short' => 'GTBIT Rajouri Garden',  'page' => '/gtbit-admission.php'],
    'Guru Tegh Bahadur 4th Centenary Engineering College'         => ['short' => 'GTB-4CEC',              'page' => '/gtb4cec-admission.php'],
    'Dr. Akhilesh Das Gupta Institute of Professional Studies'    => ['short' => 'ADGITM Shastri Park',   'page' => '/adgitm-admission.php'],
    'HMR Institute of Technology & Management'                    => ['short' => 'HMR Hamidpur',          'page' => '/hmr-admission.php'],
    'JIMS Engineering Management Technical Campus'                => ['short' => 'JEMTEC Greater Noida',  'page' => '/jemtec-admission.php'],
    'Delhi Technical Campus'                                      => ['short' => 'DTC Greater Noida',     'page' => '/dtc-admission.php'],
    'Greater Noida Institute of Technology'                       => ['short' => 'GNIT Greater Noida',    'page' => '/gnit-admission.php'],
    'Fairfield Institute of Management & Technology'              => ['short' => 'FIMT Kapashera',        'page' => '/fairfield-admission.php'],
    'Echelon Institute of Technology'                             => ['short' => 'Echelon Faridabad',     'page' => '/echelon-admission.php'],
  ];

  // Branch aliases — one branch can have several names across colleges (Shift I, DD, etc.)
  $branch_aliases = [
    'CSE' => ['B. Tech./M. Tech.(DD) (CSE)', 'Computer Science & Engineering (Shift I)', 'Computer Science & Engineering'],
    'IT'  => ['B. Tech./M. Tech.(DD) (IT)',  'Information Technology (Shift I)',         'Information Technology'],
    'ECE' => ['B. Tech./M. Tech.(DD) (ECE)', 'Electronics & Communication Engineering (Shift I)', 'Electronics & Communication Engineering'],
  ];

  $btc_get = function ($cutoffs, $institute, $aliases) {
    if (!isset($cutoffs[$institute])) return null;
    foreach ($aliases as $alias) {
      if (isset($cutoffs[$institute][$alias])) return [$alias, $cutoffs[$institute][$alias]];
    }
    return null;
  };
  $btc_fmt = function ($cell) {
    if (!$cell || !isset($cell['min']) || !isset($cell['max'])) return '<span style="color:#94a3b8">—</span>';
    return number_format((int) $cell['min']) . ' &ndash; ' . number_format((int) $cell['max']);
  };
  $btc_render_table = function ($branch_key, $branch_label, $intro_html) use ($cutoffs_data, $cutoff_colleges, $branch_aliases, $btc_get, $btc_fmt) {
    echo '<h2>B.Tech ' . htmlspecialchars($branch_label) . ' Cutoff 2025 — Round-wise (Delhi Quota)</h2>';
    echo $intro_html;
    echo '<div style="overflow-x:auto;-webkit-overflow-scrolling:touch;margin:20px 0;border:1px solid #e2e8f0;border-radius:8px">';
    echo '<table style="width:100%;border-collapse:collapse;min-width:680px;font-size:13.5px">';
    echo '<thead><tr style="background:#0d1b6e;color:#fff">';
    echo '<th style="padding:10px 12px;text-align:left">College</th>';
    echo '<th style="padding:10px 12px;text-align:left;font-size:12px;font-weight:500;color:#cbd5e1">Branch</th>';
    echo '<th style="padding:10px 12px;text-align:center">Round 1</th>';
    echo '<th style="padding:10px 12px;text-align:center">Round 2</th>';
    echo '<th style="padding:10px 12px;text-align:center">Round 3</th>';
    echo '</tr></thead><tbody>';
    $i = 0;
    foreach ($cutoff_colleges as $institute => $info) {
      $hit = $btc_get($cutoffs_data, $institute, $branch_aliases[$branch_key]);
      if (!$hit) continue;
      [$matched_branch, $rounds] = $hit;
      $bg = $i++ % 2 === 0 ? '#fff' : '#f8faff';
      echo '<tr style="background:' . $bg . ';border-top:1px solid #e2e8f0">';
      echo '<td style="padding:10px 12px;white-space:nowrap"><a href="' . htmlspecialchars($info['page']) . '" style="color:#1a3a9c;font-weight:600">' . htmlspecialchars($info['short']) . '</a></td>';
      echo '<td style="padding:10px 12px;color:#64748b;font-size:12px">' . htmlspecialchars($matched_branch) . '</td>';
      echo '<td style="padding:10px 12px;text-align:center;white-space:nowrap">' . $btc_fmt($rounds['round_1']['delhi'] ?? null) . '</td>';
      echo '<td style="padding:10px 12px;text-align:center;white-space:nowrap">' . $btc_fmt($rounds['round_2']['delhi'] ?? null) . '</td>';
      echo '<td style="padding:10px 12px;text-align:center;white-space:nowrap">' . $btc_fmt($rounds['round_3']['delhi'] ?? null) . '</td>';
      echo '</tr>';
    }
    echo '</tbody></table></div>';
    echo '<p style="font-size:12px;color:#94a3b8;margin:8px 0 28px">Values are JEE Main All India Rank ranges (Min &ndash; Max) at which seats closed in each round, Delhi Region quota. "&mdash;" means the college had no allotment in that round for this branch.</p>';
  };

  $btc_render_table('CSE', 'CSE (Computer Science & Engineering)',
    '<p>Computer Science & Engineering is the most sought-after branch across IPU colleges. Round 1 ranks are tightest; Rounds 2 and 3 relax as students vacate seats to upgrade or join other universities.</p>');

  $btc_render_table('IT', 'IT (Information Technology)',
    '<p>IT cutoffs typically run 10&ndash;20% higher (i.e., easier ranks accepted) than CSE at the same college. A solid backup if CSE is out of reach for your rank.</p>');

  $btc_render_table('ECE', 'ECE (Electronics & Communication Engineering)',
    '<p>ECE is the third-most competitive core branch. Strong fit for VLSI, embedded systems, and telecom-track careers.</p>');
  ?>

  <!-- ===== Detailed round-wise cutoffs by college ===== -->
  <h2>Detailed Round-wise Cutoffs by College</h2>
  <p>The summary tables above show the primary CSE / IT / ECE branch at each college. For the full branch list (including AI/ML, DS, Cyber Security, Mechanical, Civil, etc.) with Delhi <em>and</em> Outside Delhi cutoffs side-by-side, open the per-college page:</p>
  <div class="row g-3" style="margin:18px 0">
    <?php foreach ($cutoff_colleges as $institute => $info): ?>
    <div class="col-md-6 col-lg-4">
      <a href="<?= htmlspecialchars($info['page']) ?>" style="display:block;padding:14px 16px;background:#f8faff;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;color:inherit;height:100%"
         onmouseover="this.style.borderColor='#1a3a9c';this.style.background='#fff'"
         onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#f8faff'">
        <strong style="color:#0d1b6e;font-size:14px"><?= htmlspecialchars($info['short']) ?></strong>
        <span style="display:block;color:#64748b;font-size:12px;margin-top:4px"><?= htmlspecialchars(count($cutoffs_data[$institute] ?? [])) ?> branches &bull; R1/R2/R3 Delhi + Outside &rarr;</span>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ===== How to Use Cutoff Data ===== -->
  <h2>How to Use Cutoff Data for 2026 Admission</h2>
  <p>Cutoff data from 2025 serves as the best reference point for 2026 counselling strategy. Here is how to use it effectively:</p>
  <ul>
    <li><strong>Identify your range:</strong> Check which colleges had closing ranks near your JEE Main rank. These are your realistic targets.</li>
    <li><strong>Plan choice filling:</strong> List 3–4 aspirational choices (slightly better than your rank), 3–4 realistic choices, and 2–3 safe/backup choices.</li>
    <li><strong>Track round-wise movement:</strong> If your rank is between Round 1 and Round 3 closing rank for a college, there is a strong chance of allotment in later rounds.</li>
    <li><strong>Consider branch flexibility:</strong> If CSE cutoff is too competitive, IT and CSAI branches at the same college often have slightly relaxed cutoffs.</li>
    <li><strong>Delhi vs Outside Delhi:</strong> Delhi quota students get significantly better cutoffs. Outside Delhi students should target colleges where closing ranks are higher.</li>
  </ul>
  <p>For personalized rank analysis and choice filling strategy, call <a href="tel:+919899991342" style="color:#1a3a9c;font-weight:600">9899991342</a> to speak with our expert team.</p>

  <!-- ===== Factors Affecting Cutoff ===== -->
  <h2>Factors Affecting IPU B.Tech Cutoff</h2>
  <p>Several factors determine the final cutoff rank for each college and branch:</p>
  <ul>
    <li><strong>Category:</strong> General (OPNO), SC, ST, OBC, EWS, Defence, PWD categories have separate cutoffs. Reserved category cutoffs are typically more relaxed.</li>
    <li><strong>Quota:</strong> Delhi (Home State) quota has approximately 85% seats. Outside Delhi quota has fewer seats with more competitive cutoffs.</li>
    <li><strong>Branch:</strong> CSE has the lowest (most competitive) cutoff, followed by IT, CSAI/CSAIML, ECE, EE, ME, and CE.</li>
    <li><strong>Counselling Round:</strong> Round 1 cutoffs are tightest. Round 2 and Round 3 cutoffs relax as students vacate seats.</li>
    <li><strong>Total applicants:</strong> Year-on-year variation in JEE Main registrations and IPU applications affects cutoff trends.</li>
    <li><strong>New seat additions:</strong> When AICTE approves intake increase at a college, cutoffs for that college may relax.</li>
  </ul>

  <!-- ===== Important Notes ===== -->
  <h2>Important Notes</h2>
  <div style="background:#fff8e1;border:1px solid #f59e0b;border-radius:8px;padding:20px;margin:20px 0">
    <ul style="margin:0;padding-left:18px">
      <li style="margin-bottom:10px">These are <strong>approximate figures</strong> based on official GGSIPU data. For exact cutoffs, refer to the official portal.</li>
      <li style="margin-bottom:10px">Official cutoff data is published at: <a href="https://ipu.admissions.nic.in/create-cut-off-2025-2026/" target="_blank" rel="noopener" style="color:#1a3a9c;font-weight:600">ipu.admissions.nic.in/create-cut-off-2025-2026/</a></li>
      <li style="margin-bottom:10px">Cutoff data is approximate and based on official GGSIPU records. Always verify from the official portal.</li>
      <li style="margin-bottom:10px">Cutoffs may vary slightly year-on-year depending on number of applicants, seat changes, and JEE Main difficulty.</li>
      <li style="margin-bottom:0">For personalized rank analysis and college prediction, call <a href="tel:+919899991342" style="color:#1a3a9c;font-weight:600">9899991342</a>.</li>
    </ul>
  </div>

</div>
<div class="col-lg-4">
  <?php include 'include/sidebar-cta.php'; ?>
</div>
</div>
</div>
</section>

<!-- CTA Strip -->
<?php $cta_heading = "Need Help with B.Tech Cutoff Analysis?"; $cta_subtext = "Call for free personalized rank analysis and college prediction based on your JEE Main score"; include 'include/components/cta-strip.php'; ?>

<!-- FAQ Section -->
<?php
$faqs = [
  ['question' => 'What is the minimum JEE Main rank required for IPU B.Tech CSE?', 'answer' => 'For top colleges like USICT, you need a JEE Main rank under 35,000 (Delhi quota, General). For colleges like MAIT and MSIT, ranks up to 85,000–90,000 can secure CSE seats. For exact rank analysis, call <a href="tel:+919899991342">9899991342</a>.'],
  ['question' => 'Do IPU B.Tech cutoffs change between Round 1 and Round 3?', 'answer' => 'Yes, cutoffs relax by approximately 20–30% from Round 1 to Round 3. This means if your rank is slightly above the Round 1 closing rank, you still have a good chance in later rounds. Students who do not report or upgrade in earlier rounds free up seats.'],
  ['question' => 'What is the difference between Delhi and Outside Delhi cutoff?', 'answer' => 'Delhi (Home State) quota has approximately 85% of total seats, so cutoffs are more relaxed. Outside Delhi students compete for fewer seats and typically need 40–60% better ranks than Delhi quota to secure the same college and branch.'],
  ['question' => 'Where can I check official IPU B.Tech cutoff data?', 'answer' => 'Official cutoff data is published on the GGSIPU admission portal at <a href="https://ipu.admissions.nic.in/create-cut-off-2025-2026/" target="_blank" rel="noopener">ipu.admissions.nic.in</a>. You can filter by college, branch, category, and round.'],
  ['question' => 'Can I get B.Tech CSE at IPU with a JEE Main rank of 150,000?', 'answer' => 'With a rank around 150,000 (Delhi quota, General category), you can target CSE at colleges like GTBIT and ADGITM in Round 1. In Round 3, seats at BPIT and BVP may also become available. For IT and ECE, more options open up at this rank range. Call <a href="tel:+919899991342">9899991342</a> for a personalized college list.']
];
include 'include/components/faq-section.php';
?>

<!-- Related Pages -->
<?php
$related_pages = [
  ['title' => 'IPU B.Tech Admission 2026', 'url' => '/IPU-B-Tech-admission-2026.php', 'desc' => 'Complete guide to B.Tech eligibility, JEE Main process & counselling at IPU'],
  ['title' => 'IPU Management Quota B.Tech', 'url' => '/btech-management-quota-ipu.php', 'desc' => 'Direct admission to B.Tech without JEE Main cutoff – process & fees'],
  ['title' => 'Best B.Tech Colleges in IPU', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare top engineering colleges under GGSIPU – placements, fees & ranking'],
  ['title' => 'IPU Cutoff Analysis Hub', 'url' => '/ipu-cutoff-analysis.php', 'desc' => 'Course-wise cutoff analysis for B.Tech, BBA, Law and more'],
  ['title' => 'IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => 'Complete list of 60+ colleges affiliated to GGSIPU Delhi'],
  ['title' => 'IPU Counselling Guide', 'url' => '/GGSIPU-counselling-for-B-Tech-admission.php', 'desc' => 'Step-by-step counselling process, choice filling and seat allotment']
];
include 'include/components/related-pages.php';
?>

<?php include_once("include/base-footer.php"); ?>
</body>
</html>