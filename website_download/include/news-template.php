<?php
/** @var array $post — must include body_html, title, slug, date, date_modified, category, tags, read_time, is_urgent, image, tldr, faq */
require_once __DIR__ . '/news-helpers.php';
require_once __DIR__ . '/news-jsonld.php';

$post_url = 'https://ipu.co.in/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
$img_abs = 'https://ipu.co.in/' . ltrim($post['image'], '/');
$jsonld_article = news_jsonld_newsarticle($post);
$jsonld_faq = news_jsonld_faqpage($post);
$jsonld_bc = news_jsonld_breadcrumb($post);

include_once __DIR__ . '/base-head.php';
?>

<title><?= htmlspecialchars($post['title'], ENT_QUOTES) ?> — IPU News</title>
<meta name="description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<link rel="canonical" href="<?= $post_url ?>">
<meta name="robots" content="index, follow, max-image-preview:large">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
<meta property="og:description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<meta property="og:url" content="<?= $post_url ?>">
<meta property="og:image" content="<?= htmlspecialchars($img_abs, ENT_QUOTES) ?>">
<meta property="og:site_name" content="IPU.co.in">
<meta property="article:published_time" content="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>">
<meta property="article:modified_time" content="<?= htmlspecialchars($post['date_modified'], ENT_QUOTES) ?>">
<meta property="article:section" content="<?= htmlspecialchars($post['category'], ENT_QUOTES) ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($img_abs, ENT_QUOTES) ?>">

<script type="application/ld+json"><?= $jsonld_article ?></script>
<?php if ($jsonld_faq): ?>
<script type="application/ld+json"><?= $jsonld_faq ?></script>
<?php endif; ?>
<script type="application/ld+json"><?= $jsonld_bc ?></script>

<body>
<?php include_once __DIR__ . '/base-nav.php'; ?>

<main class="news-post">
    <nav class="news-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a> /
        <a href="/news/">News</a> /
        <a href="/news/?cat=<?= strtolower($post['category']) ?>"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></a> /
        <span><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></span>
    </nav>

    <?php if (!empty($post['is_urgent'])): ?>
    <div class="news-urgent-banner">⚠ Time-sensitive: please check dates directly with official sources.</div>
    <?php endif; ?>

    <header class="news-post__header">
        <span class="news-post__category"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></span>
        <h1><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></h1>
        <div class="news-post__meta">
            <time datetime="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>">Published <?= htmlspecialchars($post['date'], ENT_QUOTES) ?></time>
            <?php if (!empty($post['date_modified']) && $post['date_modified'] !== $post['date']): ?>
                <span> · Updated <?= htmlspecialchars($post['date_modified'], ENT_QUOTES) ?></span>
            <?php endif; ?>
            <span> · <?= (int)$post['read_time'] ?> min read</span>
        </div>
    </header>

    <figure class="news-post__image">
        <img src="/<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
    </figure>

    <aside class="news-tldr">
        <strong>TL;DR</strong>
        <p><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p>
    </aside>

    <div class="news-post__body">
        <?= $post['body_html'] ?>
    </div>

    <?php if (!empty($post['faq'])): ?>
    <section class="news-faq">
        <h2>Frequently Asked Questions</h2>
        <?php foreach ($post['faq'] as $pair): ?>
        <details>
            <summary><?= htmlspecialchars($pair['q'], ENT_QUOTES) ?></summary>
            <p><?= htmlspecialchars($pair['a'], ENT_QUOTES) ?></p>
        </details>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($post['tags'])): ?>
    <footer class="news-post__tags">
        <?php foreach ($post['tags'] as $tag): ?>
            <span class="news-tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
        <?php endforeach; ?>
    </footer>
    <?php endif; ?>
</main>

<?php include_once __DIR__ . '/base-footer.php'; ?>
</body>
</html>
