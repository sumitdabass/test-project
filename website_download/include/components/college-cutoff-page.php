<?php
/**
 * College cutoff page template.
 * Page file sets $cp = ['slug','short','institute','title','meta','h1','intro','published','related'] first.
 * Renders the full document (head through footer). Fails loudly (HTTP 500) if the institute has no data,
 * so a mistyped key can never ship a silently thin page.
 */
$cp = $cp ?? null;
if (!$cp) { http_response_code(500); exit('college-cutoff-page: $cp not set'); }

$load = function ($file) { return is_file($file) ? include $file : []; };
$data25 = $load(__DIR__ . '/../data/btech-cutoffs-2025.php');
$data26 = $load(__DIR__ . '/../data/btech-cutoffs-2026.php');
$rows25 = $data25[$cp['institute']] ?? [];
$rows26 = $data26[$cp['institute']] ?? [];
if (!$rows25 && !$rows26) {
    error_log('college-cutoff-page: no cutoff data for ' . $cp['institute']);
    http_response_code(500);
    exit('No cutoff data for this institute');
}
$rows = $rows26 ?: $rows25;              // newest available year drives the summary
$data_year = $rows26 ? '2026' : '2025';

$fmt = fn($n) => number_format((int) $n);
// Overall closing-rank span for a branch across rounds 1-3: best (lowest) min to worst (highest) max.
$span = function (array $b, string $quota) {
    $mins = $maxs = [];
    foreach (['round_1', 'round_2', 'round_3'] as $r) {
        if (isset($b[$r][$quota]['min'])) { $mins[] = $b[$r][$quota]['min']; $maxs[] = $b[$r][$quota]['max']; }
    }
    return $mins ? [min($mins), max($maxs)] : null;
};

$url = 'https://ipu.co.in/' . $cp['slug'];
// CSE may be split into shifts ("Computer Science & Engineering (Shift I)"): combine every CSE branch.
$cse_spans = function (string $quota) use ($rows, $span) {
    $all = [];
    foreach ($rows as $branch => $rounds) {
        if (strpos($branch, 'Computer Science & Engineering') === 0 && ($s = $span($rounds, $quota))) { $all[] = $s; }
    }
    return $all ? [min(array_column($all, 0)), max(array_column($all, 1))] : null;
};

$faqs = [];
if ($s = $cse_spans('delhi')) {
    $faqs[] = ['question' => "What is the {$cp['short']} cutoff for CSE?",
        'answer' => "In the {$data_year} GGSIPU counselling, CSE at {$cp['short']} closed between JEE Main rank " . $fmt($s[0]) . " and " . $fmt($s[1]) . " for Delhi-quota General candidates across rounds 1 to 3. The lower number is the tightest seat in any round; the higher number is where the last seat closed."];
}
if ($s = $cse_spans('outside')) {
    $faqs[] = ['question' => "What is the {$cp['short']} cutoff for students from outside Delhi?",
        'answer' => "For outside-Delhi candidates, CSE at {$cp['short']} closed between rank " . $fmt($s[0]) . " and " . $fmt($s[1]) . " in the {$data_year} counselling. Outside-Delhi ranks are much tighter because far fewer seats are reserved for them."];
}
$faqs[] = ['question' => "How do I read Min Rank and Max Rank in the {$cp['short']} cutoff table?",
    'answer' => "Min Rank is the best (lowest) JEE Main rank that received a seat in that round; Max Rank is the last rank that received one. If your rank is below the Max Rank for your branch and quota in an earlier round, you had a realistic chance."];
$faqs[] = ['question' => "Is the {$cp['short']} cutoff the same every year?",
    'answer' => "No. Closing ranks move with the number of candidates, seat matrix and branch demand. Use these figures to compare branches and rounds, then check the latest round notice before filling choices."];
$faqs[] = ['question' => "Does {$cp['short']} offer management-quota seats?",
    'answer' => "Some IPU-affiliated engineering colleges fill part of their intake under management or sponsored quota outside the JEE Main merit list. Read our management-quota guide for how the process works."];

$related_pages = $cp['related'];
$breadcrumbs = [['Home', '/'], ['B.Tech Admission', '/IPU-B-Tech-admission-2026.php'], [$cp['short'] . ' Cutoff', '']];
$ld_article = json_encode([
    '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $cp['h1'],
    'description' => $cp['meta'],
    'author' => ['@type' => 'Organization', 'name' => 'IPU Admission Guide'],
    'publisher' => ['@type' => 'Organization', 'name' => 'IPU Admission Guide', 'url' => 'https://ipu.co.in'],
    'datePublished' => $cp['published'], 'dateModified' => $cp['published'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include_once __DIR__ . '/../base-head.php';
?>
<title><?= htmlspecialchars($cp['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($cp['meta']) ?>">
<link rel="canonical" href="<?= $url ?>">
<meta property="og:title" content="<?= htmlspecialchars($cp['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($cp['meta']) ?>">
<meta property="og:url" content="<?= $url ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="IPU Admission Guide">
<script type="application/ld+json"><?= $ld_article ?></script>
<?php include __DIR__ . '/breadcrumb-schema.php'; ?>
</head>
<body>
<?php include_once __DIR__ . '/../base-nav.php'; ?>
<?php
$hero_title = $cp['h1'];
$hero_breadcrumbs = $breadcrumbs;
$hero_compact = true;
include __DIR__ . '/hero-banner.php';
?>
<section style="padding:50px 0">
<div class="container"><div class="row"><div class="col-lg-8">

  <section id="ai-summary" style="background:#f0f7ff;border-left:4px solid #1a3a9c;padding:20px 24px;border-radius:0 8px 8px 0;margin-bottom:32px">
    <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
    <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($cp['intro']) ?></p>
  </section>

  <p style="background:#fff8e1;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:0 8px 8px 0;font-size:14px;color:#4a5568">
    <strong>Note:</strong> figures are General category, Delhi (home-state) and Outside-Delhi quota closing ranks from GGSIPU counselling rounds 1 to 3. Category-wise cutoffs are more relaxed.
  </p>

  <h2 style="font-size:1.5rem;color:#0d1b6e">Branch-wise closing ranks, <?= $data_year ?> (all rounds)</h2>
  <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:8px">
    <table style="width:100%;min-width:560px;border-collapse:collapse;font-size:14px">
      <thead><tr style="background:#0d1b6e;color:#fff"><th style="padding:10px;text-align:left">Branch</th><th style="padding:10px;text-align:center">Delhi quota</th><th style="padding:10px;text-align:center">Outside Delhi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $branch => $rounds): $d = $span($rounds, 'delhi'); $o = $span($rounds, 'outside'); ?>
        <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px"><?= htmlspecialchars($branch) ?></td>
          <td style="padding:10px;text-align:center"><?= $d ? $fmt($d[0]) . ' – ' . $fmt($d[1]) : '—' ?></td>
          <td style="padding:10px;text-align:center"><?= $o ? $fmt($o[0]) . ' – ' . $fmt($o[1]) : '—' ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="font-size:13px;color:#64748b;margin-top:8px">Source: GGSIPU <?= $data_year ?> B.Tech counselling, rounds 1, 2 and 3. Read the ranks as Min – Max JEE Main rank.</p>

  <p>Planning your choice list? Call <a href="tel:+919899991342"><strong>9899991342</strong></a> for free rank guidance.</p>

</div>
<div class="col-lg-4"><?php include __DIR__ . '/sidebar-enquiry.php'; ?></div>
</div></div>
</section>

<?php
// Round-by-round detail table (2025 data component); renders nothing if the institute key is absent.
$cutoff_institute = $cp['institute'];
include __DIR__ . '/btech-cutoff-rounds-table.php';
$cta_heading = "Need Help with {$cp['short']} Cutoff Analysis?";
$cta_subtext = "Get free rank analysis and a realistic choice-filling plan";
include __DIR__ . '/cta-strip.php';
include __DIR__ . '/faq-section.php';
include __DIR__ . '/related-pages.php';
include_once __DIR__ . '/../base-footer.php';
?>
</body>
</html>
