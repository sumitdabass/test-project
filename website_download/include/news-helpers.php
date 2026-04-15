<?php
// News blog helpers — slug generation, categories, markdown rendering, frontmatter parsing, read-time, dedup state.

function news_slugify(string $title): string {
    $s = mb_strtolower($title, 'UTF-8');
    $s = preg_replace('/[\x{2013}\x{2014}]/u', '-', $s);  // en-dash, em-dash → hyphen
    $s = preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', $s);     // punctuation becomes whitespace (then collapsed)
    $s = preg_replace('/\s+/', '-', trim($s));
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}

function news_categories(): array {
    return ['Counselling', 'CET', 'Admissions', 'Results', 'General'];
}

function news_is_valid_category(string $c): bool {
    return in_array($c, news_categories(), true);
}

function news_category_image(string $c): string {
    $slug = news_is_valid_category($c) ? strtolower($c) : 'general';
    return "assets/images/news/{$slug}.jpg";
}

function news_md_to_html(string $md): string {
    static $parser = null;
    // Parsedown 1.7.4 predates PHP 8.x and emits E_DEPRECATED on nullable-param signatures.
    // Suppress only that class of notice while it loads and runs; preserve other errors.
    $prev = error_reporting();
    error_reporting($prev & ~E_DEPRECATED);
    if ($parser === null) {
        require_once __DIR__ . '/../../scripts/vendor/Parsedown.php';
        $parser = new Parsedown();
        $parser->setSafeMode(true);
    }
    $html = $parser->text($md);
    error_reporting($prev);
    return $html;
}
