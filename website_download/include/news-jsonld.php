<?php
// News blog JSON-LD schema builders — NewsArticle, FAQPage, BreadcrumbList.

const NEWS_SITE_ORIGIN = 'https://ipu.co.in';

function news_jsonld_newsarticle(array $post): string {
    $url = NEWS_SITE_ORIGIN . '/news/' . $post['slug'] . '.php';
    $img = NEWS_SITE_ORIGIN . '/' . ltrim($post['image'], '/');

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
        'headline' => $post['title'],
        'description' => $post['tldr'],
        'image' => $img,
        'datePublished' => $post['date'],
        'dateModified' => $post['date_modified'] ?? $post['date'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'IPU.co.in',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => NEWS_SITE_ORIGIN . '/assets/images/logo.png',
            ],
        ],
        'articleSection' => $post['category'],
    ];

    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function news_jsonld_faqpage(array $post): string {
    if (empty($post['faq']) || !is_array($post['faq'])) {
        return '';
    }
    $entities = [];
    foreach ($post['faq'] as $pair) {
        if (empty($pair['q']) || empty($pair['a'])) continue;
        $entities[] = [
            '@type' => 'Question',
            'name' => $pair['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair['a']],
        ];
    }
    if (empty($entities)) return '';
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ];
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
