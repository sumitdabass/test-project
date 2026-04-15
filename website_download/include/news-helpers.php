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
