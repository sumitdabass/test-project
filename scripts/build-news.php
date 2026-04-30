<?php
require_once __DIR__ . '/../website_download/include/news-helpers.php';

function news_build_single_post(string $md_path, string $out_dir): string {
    [$fm, $body_md] = news_parse_mdfile($md_path);

    $fm['read_time'] = news_read_time($body_md);
    $fm['body_html'] = news_md_to_html($body_md);

    if (empty($fm['image'])) {
        $fm['image'] = news_category_image($fm['category'] ?? 'General');
    }

    $slug = $fm['slug'] ?? news_slugify($fm['title']);
    $out_file = rtrim($out_dir, '/') . '/' . $slug . '.php';

    $exported = var_export($fm, true);
    $content = "<?php\n\$post = $exported;\ninclude __DIR__ . '/../include/news-template.php';\n";

    if (!is_dir($out_dir)) mkdir($out_dir, 0755, true);
    file_put_contents($out_file, $content);

    return $out_file;
}

function news_load_all_posts(string $content_dir): array {
    $posts = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        [$fm, $body] = news_parse_mdfile($md);
        $fm['read_time'] = news_read_time($body);
        $posts[] = $fm;
    }
    usort($posts, function ($a, $b) {
        // Newest first by date; slug as deterministic tiebreaker on same-day posts.
        $cmp = strcmp($b['date'], $a['date']);
        return $cmp !== 0 ? $cmp : strcmp($a['slug'], $b['slug']);
    });
    return $posts;
}

function news_build_index(string $content_dir, string $out_dir): string {
    $posts = news_load_all_posts($content_dir);

    // Post cards grid (with mid-CTA strip after the 6th card)
    $grid = '';
    foreach ($posts as $i => $post) {
        if (empty($post['image'])) {
            $post['image'] = news_category_image($post['category'] ?? 'General');
        }
        $category_attr = htmlspecialchars($post['category'] ?? 'General', ENT_QUOTES);
        $title_attr = htmlspecialchars(strtolower($post['title'] ?? ''), ENT_QUOTES);
        $post_export = var_export($post, true);
        $grid .= "        <div class=\"col-lg-4 col-md-6 mb-4 blog-item\" data-category=\"$category_attr\" data-title=\"$title_attr\">\n";
        $grid .= "          <?php \$post = $post_export; include __DIR__ . '/../include/news-card.php'; ?>\n";
        $grid .= "        </div>\n";
        if ($i === 5) {
            $grid .= <<<'CTA'
        <div class="col-12 mid-cta-strip-col">
          <div class="mid-cta-strip">
            <div class="cta-text">
              <h3>&#128222; Confused About IPU Admission 2026?</h3>
              <p>Talk to our expert right now — Free guidance, no charges, instant answers.</p>
            </div>
            <a href="tel:9899991342" class="cta-btn">📞 Call Free: 9899991342</a>
          </div>
        </div>

CTA;
        }
    }

    // Category filter buttons: "All" + news_categories()
    $cat_buttons = '';
    foreach (array_merge(['All'], news_categories()) as $cat) {
        $active = $cat === 'All' ? ' active' : '';
        $cat_esc = htmlspecialchars($cat, ENT_QUOTES);
        $cat_buttons .= "        <button class=\"btn-cat$active\" data-cat=\"$cat_esc\">$cat_esc</button>\n";
    }

    $top = <<<'TOP'
<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../include/base-head.php';
include_once __DIR__ . '/../include/form-handler.php';
?>
<title>IPU News &amp; Announcements — Latest Updates for 2026-27</title>
<meta name="description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<link rel="canonical" href="https://ipu.co.in/news/">
<meta name="robots" content="index, follow">
<meta property="og:title" content="IPU News &amp; Announcements — Latest Updates for 2026-27">
<meta property="og:description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<meta property="og:url" content="https://ipu.co.in/news/">
<meta property="og:type" content="website">
<style>
/* ============ NEWS PAGE — MATCHES BLOG STYLING ============ */
.news-hero { position: relative; padding: 120px 0 60px; text-align: center; color: #fff; overflow: hidden; -webkit-clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%); clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%); }
.news-hero::before { content: ''; position: absolute; inset: 0; background-color: #0b2c5d; opacity: 0.85; z-index: 0; }
.news-hero::after { content: ''; position: absolute; inset: 0; background-image: url(/assets/images/banner-bg-2.jpg); background-size: cover; background-position: center; z-index: -1; }
.news-hero .container { position: relative; z-index: 1; }
.news-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; color: #fff; }
.news-hero p { font-size: 1.05rem; opacity: .88; color: #fff; }
@media (max-width: 767px) { .news-hero { padding: 90px 0 50px; -webkit-clip-path: none; clip-path: none; } }
.breadcrumb-wrap { background: #f0f4ff; padding: 10px 0; font-size: .85rem; border-bottom: 1px solid #dce6ff; }
.breadcrumb-wrap a { color: #1a4a9f; text-decoration: none; }
.breadcrumb-wrap a:hover { text-decoration: underline; }
.breadcrumb-wrap span { color: #666; }
.cat-filter { padding: 22px 0 10px; }
.cat-filter .btn-cat { display: inline-block; padding: 7px 18px; margin: 4px 4px; border-radius: 25px; border: 2px solid #1a4a9f; color: #1a4a9f; background: #fff; font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .2s; text-transform: uppercase; letter-spacing: .4px; }
.cat-filter .btn-cat:hover, .cat-filter .btn-cat.active { background: #1a4a9f; color: #fff; }
.blog-card-wrap { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.08); overflow: hidden; transition: transform .2s, box-shadow .2s; height: 100%; display: flex; flex-direction: column; }
.blog-card-wrap:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(26,74,159,.18); }
.blog-card-wrap img { width: 100%; height: 185px; object-fit: cover; }
.blog-card-body { padding: 16px 18px 18px; flex: 1; display: flex; flex-direction: column; }
.blog-cat-tag { display: inline-block; background: #e8effe; color: #1a4a9f; font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; }
.blog-card-title { font-size: .97rem; font-weight: 700; color: #1a2d6b; line-height: 1.4; margin-bottom: 8px; text-decoration: none; display: block; }
.blog-card-title:hover { color: #e65c00; }
.blog-excerpt { font-size: .82rem; color: #555; line-height: 1.5; flex: 1; margin-bottom: 12px; }
.blog-meta { display: flex; justify-content: space-between; align-items: center; font-size: .78rem; color: #888; border-top: 1px solid #f0f0f0; padding-top: 10px; margin-top: auto; }
.blog-read-more { color: #1a4a9f; font-weight: 700; font-size: .82rem; text-decoration: none; }
.blog-read-more:hover { color: #e65c00; }
.urgent-badge { display: inline-block; background: #dc2626; color: #fff; font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; margin-left: 6px; text-transform: uppercase; letter-spacing: .4px; }
.mid-cta-strip { background: linear-gradient(90deg, #e65c00 0%, #f5820a 100%); border-radius: 12px; padding: 22px 28px; margin: 10px 0 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.mid-cta-strip .cta-text { color: #fff; }
.mid-cta-strip .cta-text h3 { font-size: 1.15rem; font-weight: 800; margin: 0 0 4px; }
.mid-cta-strip .cta-text p { font-size: .88rem; margin: 0; opacity: .9; }
.mid-cta-strip .cta-btn { background: #fff; color: #e65c00; font-weight: 800; font-size: 1rem; padding: 11px 24px; border-radius: 30px; text-decoration: none; white-space: nowrap; transition: background .2s; }
.mid-cta-strip .cta-btn:hover { background: #fff3e8; }
.blog-search-wrap { position: relative; margin-bottom: 8px; }
.blog-search-wrap input { width: 100%; padding: 10px 40px 10px 15px; border-radius: 25px; border: 2px solid #dde3f5; font-size: .9rem; }
.blog-search-wrap input:focus { border-color: #1a4a9f; outline: none; }
.blog-search-wrap .search-icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #999; }
.blog-item.hidden { display: none !important; }
.no-results-msg { display: none; text-align: center; color: #888; padding: 30px; font-size: .95rem; width: 100%; }
</style>
<body>

<?php include_once __DIR__ . '/../include/base-nav.php'; ?>

<section class="news-hero">
  <div class="container">
    <h1>IPU News &amp; Announcements</h1>
    <p>Latest updates on GGSIPU admissions, counselling, CET &amp; results</p>
  </div>
</section>

<nav class="breadcrumb-wrap" aria-label="breadcrumb">
  <div class="container">
    <a href="/">Home</a> &rsaquo; <span>News</span>
  </div>
</nav>

<section class="py-4">
<div class="container">

  <div class="row mb-2">
    <div class="col-lg-9 col-md-12">
      <div class="blog-search-wrap">
        <input type="text" id="newsSearch" placeholder="Search news… e.g. CET, counselling, results" autocomplete="off">
        <span class="search-icon">&#128269;</span>
      </div>
    </div>
  </div>

  <div class="cat-filter mb-3">

TOP;

    $grid_open = <<<'MID'
  </div>

  <div class="row">
    <div class="col-lg-9 col-md-12 order-2 order-lg-1">
      <div class="row" id="newsGrid">

MID;

    $bottom = <<<'BOTTOM'
        <div class="no-results-msg" id="noResults">No news found. Try a different search or category.</div>
      </div>
    </div>

    <div class="col-lg-3 col-md-12 order-1 order-lg-2 mb-4">
      <div style="position:sticky;top:80px">
        <?php include_once __DIR__ . '/../include/sidebar-cta.php'; ?>
        <?php include_once __DIR__ . '/../include/news-popular-blogs.php'; ?>
      </div>
    </div>
  </div>

</div>
</section>

<?php include_once __DIR__ . '/../include/base-footer.php'; ?>

<script>
const filterBtns = document.querySelectorAll('.btn-cat');
const newsItems = document.querySelectorAll('.blog-item');
const noResults = document.getElementById('noResults');
const searchInput = document.getElementById('newsSearch');

let activeCategory = 'All';
let searchQuery = '';

function applyFilters() {
  let visibleCount = 0;
  newsItems.forEach(item => {
    const cat = item.dataset.category;
    const title = item.dataset.title || '';
    const matchesCat = activeCategory === 'All' || cat === activeCategory;
    const matchesSearch = searchQuery === '' || title.includes(searchQuery.toLowerCase());
    if (matchesCat && matchesSearch) {
      item.classList.remove('hidden');
      visibleCount++;
    } else {
      item.classList.add('hidden');
    }
  });
  noResults.style.display = visibleCount === 0 ? 'block' : 'none';
  const midCTA = document.querySelector('.mid-cta-strip-col');
  if (midCTA) midCTA.style.display = (activeCategory === 'All' && searchQuery === '') ? '' : 'none';
}

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeCategory = btn.dataset.cat;
    applyFilters();
  });
});

searchInput.addEventListener('input', () => {
  searchQuery = searchInput.value.trim();
  applyFilters();
});
</script>
</body>
</html>

BOTTOM;

    $out = $top . $cat_buttons . $grid_open . $grid . $bottom;

    if (!is_dir($out_dir)) mkdir($out_dir, 0755, true);
    $out_file = rtrim($out_dir, '/') . '/index.php';
    file_put_contents($out_file, $out);
    return $out_file;
}

function news_update_sitemap(string $content_dir, string $sitemap_path): void {
    $posts = news_load_all_posts($content_dir);
    $xml = file_exists($sitemap_path) ? file_get_contents($sitemap_path) : '';
    if ($xml === '') {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n</urlset>\n";
    }

    // strip any previous news entries (idempotent) — matches both /news/<slug>.php and the bare /news/ index
    $xml = preg_replace('#\s*<url>\s*<loc>https://ipu\.co\.in/news/[^<]*</loc>.*?</url>#s', '', $xml);

    $new_entries = '';
    foreach ($posts as $p) {
        $loc = 'https://ipu.co.in/news/' . $p['slug'] . '.php';
        $lastmod = htmlspecialchars($p['date_modified'] ?? $p['date'], ENT_QUOTES);
        $new_entries .= "  <url>\n    <loc>" . htmlspecialchars($loc, ENT_QUOTES) . "</loc>\n    <lastmod>$lastmod</lastmod>\n    <changefreq>weekly</changefreq>\n  </url>\n";
    }
    // also include /news/ index
    $new_entries .= "  <url>\n    <loc>https://ipu.co.in/news/</loc>\n    <lastmod>" . date('Y-m-d') . "</lastmod>\n    <changefreq>daily</changefreq>\n  </url>\n";

    $xml = str_replace('</urlset>', $new_entries . '</urlset>', $xml);
    file_put_contents($sitemap_path, $xml);
}

function news_update_llms_txt(string $content_dir, string $llms_path): void {
    $posts = news_load_all_posts($content_dir);
    $existing = file_exists($llms_path) ? file_get_contents($llms_path) : "# IPU.co.in\n";

    // strip previous News section (idempotent)
    $existing = preg_replace('/\n## IPU News.*?(?=\n## |\z)/s', '', $existing);

    $section = "\n## IPU News\n\nLatest IPU-related news and announcements:\n\n";
    foreach ($posts as $p) {
        $url = 'https://ipu.co.in/news/' . $p['slug'] . '.php';
        $section .= "- [" . $p['title'] . "]($url) — " . $p['tldr'] . "\n";
    }

    file_put_contents($llms_path, rtrim($existing, "\n") . "\n" . $section);
}

function news_cleanup_orphans(string $content_dir, string $out_dir): array {
    // Build the set of slugs that SHOULD exist from the MD source files.
    $expected = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        [$fm, ] = news_parse_mdfile($md);
        $slug = $fm['slug'] ?? news_slugify($fm['title'] ?? basename($md, '.md'));
        $expected[$slug] = true;
    }
    // Walk generated *.php files in out_dir. Any that aren't in $expected and
    // aren't index.php are orphans from a now-deleted MD — remove them.
    $removed = [];
    foreach (glob(rtrim($out_dir, '/') . '/*.php') as $php) {
        $name = basename($php, '.php');
        if ($name === 'index') continue;
        if (!isset($expected[$name])) {
            unlink($php);
            $removed[] = $php;
        }
    }
    return $removed;
}

function news_build_all(string $content_dir, string $web_dir): array {
    $posts_written = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        $posts_written[] = news_build_single_post($md, $web_dir . '/news/');
    }
    $removed = news_cleanup_orphans($content_dir, $web_dir . '/news/');
    foreach ($removed as $r) {
        echo "  removed orphan: $r\n";
    }
    news_build_index($content_dir, $web_dir . '/news/');
    news_update_sitemap($content_dir, $web_dir . '/sitemap.xml');
    news_update_llms_txt($content_dir, $web_dir . '/llms.txt');
    return $posts_written;
}

// CLI entry
if (php_sapi_name() === 'cli' && realpath($argv[0]) === __FILE__) {
    $repo_root = dirname(__DIR__);
    $written = news_build_all($repo_root . '/content/news', $repo_root . '/website_download');
    echo "Built " . count($written) . " posts.\n";
    foreach ($written as $w) echo "  $w\n";
}
