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
        $fa = !empty($a['featured']) ? 1 : 0;
        $fb = !empty($b['featured']) ? 1 : 0;
        if ($fa !== $fb) return $fb - $fa;
        return strcmp($b['date'], $a['date']);
    });
    return $posts;
}

function news_build_index(string $content_dir, string $out_dir): string {
    $posts = news_load_all_posts($content_dir);

    $out  = "<?php include_once __DIR__ . '/../include/base-head.php'; ?>\n";
    $out .= "<title>IPU News &amp; Announcements — Latest Updates for 2026-27</title>\n";
    $out .= "<meta name=\"description\" content=\"Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.\">\n";
    $out .= "<link rel=\"canonical\" href=\"https://ipu.co.in/news/\">\n";
    $out .= "<meta name=\"robots\" content=\"index, follow\">\n";
    $out .= "<body>\n";
    $out .= "<?php include_once __DIR__ . '/../include/base-nav.php'; ?>\n";
    $out .= "<main class=\"news-index\">\n";
    $out .= "  <header class=\"news-index__header\"><h1>IPU News &amp; Announcements</h1><p>Latest updates on GGSIPU admissions, counselling, CET, and results.</p></header>\n";
    $out .= "  <nav class=\"news-categories\"><a href=\"/news/\">All</a>";
    foreach (news_categories() as $cat) {
        $out .= "<a href=\"/news/?cat=" . strtolower($cat) . "\">" . $cat . "</a>";
    }
    $out .= "</nav>\n";
    $out .= "  <div class=\"news-grid\">\n";
    foreach ($posts as $post) {
        if (empty($post['image'])) {
            $post['image'] = news_category_image($post['category'] ?? 'General');
        }
        $post_export = var_export($post, true);
        $out .= "    <?php \$post = $post_export; include __DIR__ . '/../include/news-card.php'; ?>\n";
    }
    $out .= "  </div>\n</main>\n";
    $out .= "<?php include_once __DIR__ . '/../include/base-footer.php'; ?>\n";
    $out .= "</body>\n</html>\n";

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

function news_build_all(string $content_dir, string $web_dir): array {
    $posts_written = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        $posts_written[] = news_build_single_post($md, $web_dir . '/news/');
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
