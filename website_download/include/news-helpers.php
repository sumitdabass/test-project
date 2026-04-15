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
