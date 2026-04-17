<?php
/**
 * Popular Blogs widget — used on /news/ listing and inside news-template.php only.
 * Hardcoded list of top blog post URLs to drive CTR from the news section to blog content.
 */
$popular_blogs = [
    ['url' => '/mba-admission-ip-university.php',                        'title' => 'MBA Admission in IP University 2026–27'],
    ['url' => '/IPU-B-Tech-admission-2026.php',                          'title' => 'B.Tech Admission 2026 — Eligibility & Colleges'],
    ['url' => '/IPU-Law-Admission-2026.php',                             'title' => 'IPU Law Admission 2026 Guide'],
    ['url' => '/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php', 'title' => 'Top BBA Colleges under IP University'],
    ['url' => '/guide-to-bjmc-colleges-under-ip-university.php',         'title' => 'Guide to BJMC Colleges under IPU'],
];
?>
<div style="margin-top:20px;background:#f8faff;border-radius:12px;padding:20px;border:1px solid #e2e8f0">
  <h4 style="font-size:15px;color:#0d1b6e;margin-bottom:12px;font-weight:700">Popular Blogs</h4>
  <ul style="list-style:none;padding:0;margin:0">
    <?php foreach ($popular_blogs as $i => $b): ?>
    <li style="<?= $i === count($popular_blogs) - 1 ? 'margin-bottom:0' : 'margin-bottom:10px' ?>">
      <a href="<?= htmlspecialchars($b['url'], ENT_QUOTES) ?>" style="font-size:13.5px;color:#1a3a9c;text-decoration:none;line-height:1.4;display:block">
        → <?= htmlspecialchars($b['title'], ENT_QUOTES) ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</div>
