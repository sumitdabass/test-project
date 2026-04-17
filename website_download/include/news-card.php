<?php
/** @var array $post — required by caller */
$post_url = '/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
$image    = !empty($post['image']) ? $post['image'] : 'assets/images/news/general.jpg';
$is_urgent = !empty($post['is_urgent']);
?>
<div class="blog-card-wrap">
  <a href="<?= $post_url ?>">
    <img src="/<?= htmlspecialchars(ltrim($image, '/'), ENT_QUOTES) ?>"
         alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
         loading="lazy">
  </a>
  <div class="blog-card-body">
    <span class="blog-cat-tag">
      <?= htmlspecialchars($post['category'], ENT_QUOTES) ?>
      <?php if ($is_urgent): ?><span class="urgent-badge">Urgent</span><?php endif; ?>
    </span>
    <a href="<?= $post_url ?>" class="blog-card-title"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></a>
    <p class="blog-excerpt"><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p>
    <div class="blog-meta">
      <span>&#9200; <?= (int)$post['read_time'] ?> min read</span>
      <a href="<?= $post_url ?>" class="blog-read-more">Read More &rarr;</a>
    </div>
  </div>
</div>
