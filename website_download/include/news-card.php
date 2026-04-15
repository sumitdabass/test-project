<?php
/** @var array $post — required by caller */
$urgent_class = !empty($post['is_urgent']) ? ' news-card--urgent' : '';
$post_url = '/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
?>
<article class="news-card<?= $urgent_class ?>">
    <a href="<?= $post_url ?>" class="news-card__link">
        <img src="/<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>"
             alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
             class="news-card__image" loading="lazy">
        <div class="news-card__body">
            <span class="news-card__category"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></span>
            <h3 class="news-card__title"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></h3>
            <p class="news-card__tldr"><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p>
            <div class="news-card__meta">
                <time datetime="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>"><?= htmlspecialchars($post['date'], ENT_QUOTES) ?></time>
                <span class="news-card__read-time"><?= (int)$post['read_time'] ?> min read</span>
            </div>
        </div>
    </a>
</article>
