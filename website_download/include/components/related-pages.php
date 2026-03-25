<?php
/**
 * Related Pages Component — Internal linking for SEO
 *
 * Usage:
 *   $related_pages = [
 *     ['title' => 'B.Tech Admission 2026', 'url' => '/IPU-B-Tech-admission-2026.php', 'desc' => 'Complete guide to B.Tech admission at IPU'],
 *     ['title' => 'Top B.Tech Colleges', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare the best engineering colleges under IPU'],
 *   ];
 *   include 'include/components/related-pages.php';
 */

$related_pages = $related_pages ?? [];
if (empty($related_pages)) return;
?>

<section style="padding:40px 0;border-top:1px solid #e2e8f0">
  <div class="container">
    <h3 style="font-size:1.3rem;margin-bottom:20px">Related Guides</h3>
    <div class="row g-3">
      <?php foreach ($related_pages as $page): ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= htmlspecialchars($page['url']) ?>" style="display:block;padding:16px;background:#f8faff;border:1px solid #e2e8f0;border-radius:8px;text-decoration:none;transition:all .2s;height:100%"
           onmouseover="this.style.borderColor='#1a3a9c';this.style.background='#fff'"
           onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#f8faff'">
          <h4 style="font-size:15px;color:#0d1b6e;margin-bottom:4px"><?= htmlspecialchars($page['title']) ?></h4>
          <?php if (!empty($page['desc'])): ?>
            <p style="font-size:13px;color:#4a5568;margin:0;line-height:1.5"><?= htmlspecialchars($page['desc']) ?></p>
          <?php endif; ?>
          <span style="font-size:13px;color:#1a3a9c;font-weight:600;margin-top:8px;display:inline-block">Read More →</span>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
