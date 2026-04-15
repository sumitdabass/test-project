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
