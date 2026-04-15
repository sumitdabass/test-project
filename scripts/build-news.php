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
