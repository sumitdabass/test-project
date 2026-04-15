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

function news_read_time(string $body_md): int {
    $words = str_word_count(strip_tags($body_md));
    return max(1, (int) ceil($words / 200));
}

function news_state_load(string $path): array {
    if (!file_exists($path)) return ['seen' => []];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) && isset($data['seen']) ? $data : ['seen' => []];
}

function news_state_has_seen(string $path, string $url): bool {
    $state = news_state_load($path);
    return in_array(hash('sha256', $url), $state['seen'], true);
}

function news_state_mark_seen(string $path, string $url): void {
    $state = news_state_load($path);
    $hash = hash('sha256', $url);
    if (!in_array($hash, $state['seen'], true)) {
        $state['seen'][] = $hash;
    }
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT));
}

function news_parse_mdfile(string $path): array {
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Cannot read MD file: $path");
    }
    $parts = preg_split('/^---\s*$/m', $raw, 2);
    if (count($parts) !== 2) {
        throw new RuntimeException("MD file missing '---' separator: $path");
    }
    $fm = json_decode(trim($parts[0]), true);
    if (!is_array($fm)) {
        throw new RuntimeException("Invalid JSON frontmatter in $path: " . json_last_error_msg());
    }
    $body = ltrim($parts[1]);
    return [$fm, $body];
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
