<?php
/** @var array $post — must include body_html, title, slug, date, date_modified, category, tags, read_time, is_urgent, image, tldr, faq */
require_once __DIR__ . '/news-helpers.php';
require_once __DIR__ . '/news-jsonld.php';
$news_layout = getenv('NEWS_LAYOUT') ?: 'v2';   // 'v1' (legacy) is kept as the rollback path / test baseline

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
<meta property="og:site_name" content="<?= $news_layout === 'v2' ? 'IPU Admission Guide' : 'IPU.co.in' ?>">
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

<?php if ($news_layout !== 'v2'): ?><style>
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
<?php else: ?><style>
.news-post__body{font-size:1rem;line-height:1.7;color:var(--ipu-text,#1a1a2e)}
.news-post__body h2{margin:24px 0 10px}.news-post__body h3{font-size:18px;font-weight:700;margin:20px 0 8px;color:var(--ipu-ink)}
.news-post__body a{color:#1a3a9c;text-decoration:underline}.news-post__body p{margin:0 0 14px}
.news-post__body ul,.news-post__body ol{margin:0 0 14px 20px}.news-post__body li{margin-bottom:6px}
.news-post__image{margin:0 0 20px}.news-post__image img{display:block;width:100%;height:auto;aspect-ratio:5/3;object-fit:cover;border-radius:16px}
.news-tldr{background:var(--ipu-bg);border:1px solid var(--ipu-rule-soft);border-radius:12px;padding:16px 18px;margin:0 0 22px}
.news-tldr strong{display:block;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--ipu-ink-2);margin-bottom:6px}
.news-tldr p{margin:0;font-size:16px}
.news-urgent{display:flex;gap:10px;align-items:flex-start;background:#fef3c7;border:1px solid #fde68a;color:#92400e;padding:12px 14px;border-radius:12px;margin:0 0 16px;font-size:15px}
.news-urgent svg{flex:0 0 18px;margin-top:2px}
.news-tags{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 24px}
.news-tag{background:var(--ipu-bg);color:var(--ipu-ink-2);font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px}
.news-popular{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
.news-popular a{display:flex;align-items:center;justify-content:space-between;gap:8px;min-height:48px;padding:12px 14px;border:1px solid var(--ipu-rule);border-radius:12px;background:#fff;color:var(--ipu-ink);font-weight:600;text-decoration:none}
.news-popular a::after{content:"\2192";color:var(--ipu-orange)}
</style>
<body>
<?php include_once __DIR__ . '/base-nav.php'; ?>
<?php
[$news_intro_html, $news_rest_html] = news_split_body($post['body_html']);
$hero_pill = $post['category'];
$hero_h1 = htmlspecialchars($post['title'], ENT_QUOTES);
$hero_meta = '<time datetime="' . htmlspecialchars($post['date'], ENT_QUOTES) . '">Published ' . htmlspecialchars(news_human_date($post['date']), ENT_QUOTES) . '</time>'
    . (!empty($post['date_modified']) && $post['date_modified'] !== $post['date'] ? ' · Updated ' . htmlspecialchars(news_human_date($post['date_modified']), ENT_QUOTES) : '')
    . ' · By IPU Admission Team · ' . (int)$post['read_time'] . ' min read';
$hero_breadcrumbs = [['Home', '/'], ['News', '/news/'], [$post['category'], '/news/?cat=' . strtolower($post['category'])], [$post['title'], '']];
$hero_show_form = false;
$hero_show_enquire = true;
$show_trust_bar = false;
include __DIR__ . '/components/page-hero.php';
unset($hero_pill, $hero_h1, $hero_meta, $hero_breadcrumbs, $hero_show_enquire, $show_trust_bar);
?>
<div class="ipu-body">
  <article class="ipu-body__intro">
    <?php if (!empty($post['is_urgent']) && news_is_fresh($post['date_modified'] ?: $post['date'])): ?>
    <div class="news-urgent" role="note">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
      <span>Time-sensitive: please check dates directly with official sources.</span>
    </div>
    <?php endif; ?>
    <figure class="news-post__image">
      <img src="/<?= htmlspecialchars(ltrim($post['image'], '/'), ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>" width="1000" height="600" fetchpriority="high" decoding="async">
    </figure>
    <aside class="news-tldr"><strong>TL;DR</strong><p><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p></aside>
    <div class="news-post__body"><?= $news_intro_html ?></div>
  </article>

  <div class="ipu-body__aside"><?php include __DIR__ . '/components/sidebar-enquiry.php'; ?></div>

  <div class="ipu-body__rest">
    <?php if ($news_rest_html !== ''): ?><div class="news-post__body"><?= $news_rest_html ?></div><?php endif; ?>
    <?php $cta_card = true; $cta_heading = 'Confused About IPU Admission 2026?'; $cta_subtext = 'Talk to our expert right now — Free guidance, no charges, instant answers.'; include __DIR__ . '/components/cta-strip.php'; ?>
    <?php
    $faqs = array_map(fn($p) => ['question' => $p['q'], 'answer' => htmlspecialchars($p['a'], ENT_QUOTES)], $post['faq'] ?? []);
    $faq_schema = false;   // the head already carries the FAQPage JSON-LD for this post
    include __DIR__ . '/components/faq-details.php';
    unset($faqs, $faq_schema);
    ?>
    <?php if (!empty($post['tags'])): ?>
    <div class="news-tags"><?php foreach ($post['tags'] as $tag): ?><span class="news-tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span><?php endforeach; ?></div>
    <?php endif; ?>
    <?php $popular_grid = true; include __DIR__ . '/news-popular-blogs.php'; $popular_grid = false; ?>
  </div>
</div>
<?php include_once __DIR__ . '/base-footer.php'; ?>
</body>
</html>
<?php endif; ?>
