<?php
/**
 * Related content widget for news posts.
 * Renders two blocks at the end of a news article:
 *   1. "More IPU News" — 3 most-recently-modified other news posts
 *   2. "Related Admission Guides" — course hub links matched by tags/category
 *
 * Usage: $current_slug = $post['slug']; $tags = $post['tags']; $category = $post['category']; include 'include/news-related-content.php';
 */

$current_slug = $current_slug ?? ($post['slug'] ?? '');
$tags         = $tags ?? ($post['tags'] ?? []);
$category     = $category ?? ($post['category'] ?? '');

// 1. Find 3 most-recently-modified other news posts
$news_dir = __DIR__ . '/../news/';
$files = glob($news_dir . '*.php') ?: [];
$candidates = [];
foreach ($files as $f) {
    $basename = basename($f, '.php');
    if ($basename === 'index' || $basename === $current_slug) continue;
    $content = file_get_contents($f);
    $title = '';
    $tldr  = '';
    if (preg_match("/'title'\\s*=>\\s*'([^']+)'/", $content, $tm)) $title = $tm[1];
    if (preg_match("/'tldr'\\s*=>\\s*'([^']+)'/", $content, $tldrm)) $tldr = $tldrm[1];
    if (!$title) continue;
    $candidates[] = [
        'slug'  => $basename,
        'title' => $title,
        'tldr'  => $tldr,
        'mtime' => filemtime($f),
    ];
}
usort($candidates, fn($a, $b) => $b['mtime'] - $a['mtime']);
$related_news = array_slice($candidates, 0, 3);

// 2. Match tags/category to course hubs
$tag_text = strtolower(implode(' ', array_merge((array)$tags, [$category])));
$course_hubs = [
    'btech'    => ['url' => '/IPU-B-Tech-admission-2026.php', 'title' => 'IPU B.Tech Admission 2026 — Fees, Seats, Top Colleges',          'match' => ['b.tech', 'btech', 'jee main', 'engineering', 'cse', 'usict', 'usar']],
    'bba'      => ['url' => '/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php', 'title' => 'IPU BBA Admission — Top Colleges Guide', 'match' => ['bba', 'business administration', 'usms']],
    'bcom'     => ['url' => '/bcom-admission-ipu.php',         'title' => 'IPU B.Com (Hons.) Admission — Fees & Colleges',                     'match' => ['b.com', 'bcom', 'commerce']],
    'bca'      => ['url' => '/bca-admission-ipu.php',          'title' => 'IPU BCA Admission — Fees & Colleges',                                'match' => ['bca', 'computer applications']],
    'bjmc'     => ['url' => '/guide-to-bjmc-colleges-under-ip-university.php', 'title' => 'IPU BJMC Admission — Colleges & Fees',              'match' => ['bjmc', 'journalism', 'mass communication', 'usmc']],
    'law'      => ['url' => '/IPU-Law-Admission.php',          'title' => 'IPU Integrated Law (BA-LLB / BBA-LLB) Admission',                    'match' => ['law', 'llb', 'usl&ls', 'usls', 'clat']],
    'cet'      => ['url' => '/ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php', 'title' => 'IPU CET Exam — Admit Card, Schedule, Pattern', 'match' => ['cet', 'common entrance test']],
    'mq'       => ['url' => '/IP-University-management-quota-admission-eligibility-criteria.php', 'title' => 'IPU Management Quota Admission Hub', 'match' => ['management quota', 'mq']],
    'counsel'  => ['url' => '/GGSIPU-counselling-for-B-Tech-admission.php', 'title' => 'IPU Counselling Process Step-by-Step',                   'match' => ['counselling', 'counseling', 'choice filling']],
];
$matched_hubs = [];
foreach ($course_hubs as $hub) {
    foreach ($hub['match'] as $kw) {
        if (str_contains($tag_text, $kw)) { $matched_hubs[] = $hub; break; }
    }
}
$matched_hubs = array_slice($matched_hubs, 0, 4);
?>

<?php if (!empty($related_news)): ?>
<section class="news-related" style="margin-top:30px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px 24px">
  <h2 style="font-size:1.2rem;color:#1a2d6b;font-weight:800;margin:0 0 14px">More IPU News</h2>
  <div style="display:grid;gap:12px">
    <?php foreach ($related_news as $r): ?>
    <a href="/news/<?= htmlspecialchars($r['slug'], ENT_QUOTES) ?>.php" style="display:block;padding:12px 14px;border:1px solid #eee;border-radius:8px;color:#1a2d6b;text-decoration:none;transition:border-color .2s">
      <div style="font-weight:700;font-size:.95rem;margin-bottom:4px"><?= htmlspecialchars($r['title'], ENT_QUOTES) ?></div>
      <?php if ($r['tldr']): ?>
      <div style="font-size:.85rem;color:#555;line-height:1.5"><?= htmlspecialchars(mb_strimwidth($r['tldr'], 0, 140, '…'), ENT_QUOTES) ?></div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($matched_hubs)): ?>
<section class="news-related-hubs" style="margin-top:18px;background:#f0f4ff;border-left:4px solid #1a4a9f;border-radius:0 8px 8px 0;padding:18px 22px">
  <h2 style="font-size:1.1rem;color:#1a2d6b;font-weight:800;margin:0 0 12px">Related Admission Guides</h2>
  <ul style="list-style:none;padding:0;margin:0">
    <?php foreach ($matched_hubs as $h): ?>
    <li style="margin-bottom:8px"><a href="<?= htmlspecialchars($h['url'], ENT_QUOTES) ?>" style="font-size:.95rem;color:#1a3a9c;font-weight:600;text-decoration:none">→ <?= htmlspecialchars($h['title'], ENT_QUOTES) ?></a></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>
