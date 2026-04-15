<?php
require_once __DIR__ . '/../../website_download/include/news-jsonld.php';

$post = [
    'title' => 'Round 2 Counselling Schedule Announced',
    'slug' => 'round-2-counselling-schedule',
    'date' => '2026-04-15',
    'date_modified' => '2026-04-15',
    'tldr' => 'Round 2 counselling dates announced.',
    'category' => 'Counselling',
    'image' => 'assets/images/news/counselling.jpg',
];

$jsonld = news_jsonld_newsarticle($post);
$decoded = json_decode($jsonld, true);

TestCase::assertEqual('NewsArticle', $decoded['@type'], 'type is NewsArticle');
TestCase::assertEqual('Round 2 Counselling Schedule Announced', $decoded['headline'], 'headline set');
TestCase::assertEqual('2026-04-15', $decoded['datePublished'], 'datePublished set');
TestCase::assertEqual('2026-04-15', $decoded['dateModified'], 'dateModified set');
TestCase::assertEqual('https://ipu.co.in/news/round-2-counselling-schedule.php', $decoded['mainEntityOfPage']['@id'], 'canonical url set');
TestCase::assertEqual('https://ipu.co.in/assets/images/news/counselling.jpg', $decoded['image'], 'absolute image url');
TestCase::assertEqual('Organization', $decoded['publisher']['@type'], 'publisher is Organization');
TestCase::assertEqual('IPU.co.in', $decoded['publisher']['name'], 'publisher name');

$post_with_faq = $post + ['faq' => [
    ['q' => 'When does Round 2 start?', 'a' => 'April 22, 2026.'],
    ['q' => 'Who can apply?', 'a' => 'All 10+2 qualified candidates.'],
]];

$faq_jsonld = news_jsonld_faqpage($post_with_faq);
TestCase::assertTrue($faq_jsonld !== '', 'non-empty when faq present');
$decoded_faq = json_decode($faq_jsonld, true);
TestCase::assertEqual('FAQPage', $decoded_faq['@type'], 'type FAQPage');
TestCase::assertEqual(2, count($decoded_faq['mainEntity']), 'two FAQs');
TestCase::assertEqual('Question', $decoded_faq['mainEntity'][0]['@type'], 'first is Question');
TestCase::assertEqual('When does Round 2 start?', $decoded_faq['mainEntity'][0]['name'], 'question text');
TestCase::assertEqual('April 22, 2026.', $decoded_faq['mainEntity'][0]['acceptedAnswer']['text'], 'answer text');

$no_faq = news_jsonld_faqpage($post);
TestCase::assertEqual('', $no_faq, 'empty string when no faq');

$bc = news_jsonld_breadcrumb($post);
$decoded_bc = json_decode($bc, true);
TestCase::assertEqual('BreadcrumbList', $decoded_bc['@type'], 'type BreadcrumbList');
TestCase::assertEqual(4, count($decoded_bc['itemListElement']), 'four crumbs: Home, News, Counselling, Post');
TestCase::assertEqual('Home', $decoded_bc['itemListElement'][0]['name'], 'first is Home');
TestCase::assertEqual('News', $decoded_bc['itemListElement'][1]['name'], 'second is News');
TestCase::assertEqual('Counselling', $decoded_bc['itemListElement'][2]['name'], 'third is category');
TestCase::assertEqual('Round 2 Counselling Schedule Announced', $decoded_bc['itemListElement'][3]['name'], 'fourth is post title');
