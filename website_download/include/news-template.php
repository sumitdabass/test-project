<?php
/** @var array $post — must include body_html, title, slug, date, date_modified, category, tags, read_time, is_urgent, image, tldr, faq */
require_once __DIR__ . '/news-helpers.php';
require_once __DIR__ . '/news-jsonld.php';

$post_url = 'https://ipu.co.in/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
$img_abs  = 'https://ipu.co.in/' . ltrim($post['image'], '/');
$jsonld_article = news_jsonld_newsarticle($post);
$jsonld_faq     = news_jsonld_faqpage($post);
$jsonld_bc      = news_jsonld_breadcrumb($post);

if (session_status() === PHP_SESSION_NONE) session_start();
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

<style>
/* News post — blog-matching typography */
.breadcrumb-wrap {
  background: #f0f4ff;
  padding: 10px 0;
  font-size: .85rem;
  border-bottom: 1px solid #dce6ff;
}
.breadcrumb-wrap a { color: #1a4a9f; text-decoration: none; }
.breadcrumb-wrap a:hover { text-decoration: underline; }
.breadcrumb-wrap span { color: #666; }

.news-post-wrap { padding: 30px 0 40px; }
.news-post__header { margin-bottom: 18px; }
.news-post__category {
  display: inline-block;
  background: #e8effe;
  color: #1a4a9f;
  font-size: .72rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: .5px;
}
.news-post__header h1 {
  font-size: 1.9rem;
  font-weight: 800;
  color: #1a2d6b;
  line-height: 1.3;
  margin: 0 0 10px;
}
.news-post__meta { color: #888; font-size: .85rem; }
.news-post__meta time { color: #555; font-weight: 600; }

.news-urgent-banner {
  background: #fef3c7;
  border-left: 4px solid #f59e0b;
  color: #92400e;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: .88rem;
  margin-bottom: 16px;
}

.news-post__image { margin: 0 0 20px; }
.news-post__image img {
  width: 100%;
  max-height: 360px;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 2px 14px rgba(0,0,0,.08);
}

.news-tldr {
  background: linear-gradient(135deg, #f0f4ff 0%, #fff 100%);
  border-left: 4px solid #1a4a9f;
  padding: 14px 18px;
  border-radius: 8px;
  margin-bottom: 22px;
}
.news-tldr strong {
  display: block;
  color: #1a4a9f;
  font-size: .78rem;
  font-weight: 800;
  letter-spacing: .8px;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.news-tldr p { margin: 0; font-size: .95rem; color: #333; line-height: 1.55; }

.news-post__body {
  font-size: 1rem;
  line-height: 1.7;
  color: #333;
}
.news-post__body h2 {
  font-size: 1.35rem;
  color: #1a2d6b;
  font-weight: 800;
  margin: 24px 0 10px;
}
.news-post__body h3 {
  font-size: 1.1rem;
  color: #1a2d6b;
  font-weight: 700;
  margin: 20px 0 8px;
}
.news-post__body a { color: #1a4a9f; text-decoration: underline; }
.news-post__body a:hover { color: #e65c00; }
.news-post__body p { margin: 0 0 14px; }
.news-post__body ul, .news-post__body ol { margin: 0 0 14px 20px; }
.news-post__body li { margin-bottom: 6px; }

.news-faq {
  margin-top: 30px;
  background: #f8faff;
  border-radius: 12px;
  padding: 22px 24px;
  border: 1px solid #e2e8f0;
}
.news-faq h2 {
  font-size: 1.25rem;
  color: #1a2d6b;
  font-weight: 800;
  margin: 0 0 14px;
}
.news-faq details {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 10px 14px;
  margin-bottom: 10px;
}
.news-faq summary {
  font-weight: 700;
  color: #1a2d6b;
  cursor: pointer;
  font-size: .95rem;
}
.news-faq details[open] summary { margin-bottom: 8px; }
.news-faq details p { margin: 0; color: #444; font-size: .9rem; line-height: 1.55; }

.news-post__tags { margin-top: 22px; padding-top: 14px; border-top: 1px solid #eee; }
.news-tag {
  display: inline-block;
  background: #f0f4ff;
  color: #1a4a9f;
  font-size: .75rem;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  margin-right: 6px;
  margin-bottom: 6px;
}
</style>

<body>
<?php include_once __DIR__ . '/base-nav.php'; ?>

<nav class="breadcrumb-wrap" aria-label="Breadcrumb">
  <div class="container">
    <a href="/">Home</a> &rsaquo;
    <a href="/news/">News</a> &rsaquo;
    <a href="/news/?cat=<?= strtolower($post['category']) ?>"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></a> &rsaquo;
    <span><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></span>
  </div>
</nav>

<section class="news-post-wrap">
<div class="container">
  <div class="row">
    <div class="col-lg-9 col-md-12 order-2 order-lg-1">
      <article>
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
          <img src="/<?= htmlspecialchars(ltrim($post['image'], '/'), ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
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
      </article>
    </div>

    <aside class="col-lg-3 col-md-12 order-1 order-lg-2 mb-4">
      <div style="position:sticky;top:80px">
        <?php include_once __DIR__ . '/sidebar-cta.php'; ?>
        <?php include_once __DIR__ . '/news-popular-blogs.php'; ?>
      </div>
    </aside>
  </div>
</div>
</section>

<?php include_once __DIR__ . '/base-footer.php'; ?>
</body>
</html>
