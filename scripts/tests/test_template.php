<?php
require_once __DIR__ . '/../../website_download/include/news-helpers.php';

$post = [
    'title' => 'Round 2 Counselling Schedule Announced',
    'slug' => 'round-2-counselling-schedule',
    'date' => '2026-04-15',
    'date_modified' => '2026-04-15',
    'category' => 'Counselling',
    'tags' => ['B.Tech', 'MBA'],
    'read_time' => 5,
    'featured' => false,
    'is_urgent' => false,
    'image' => 'assets/images/news/counselling.jpg',
    'tldr' => 'IPU has announced Round 2 counselling dates for 2026-27.',
    'faq' => [
        ['q' => 'When does Round 2 start?', 'a' => 'April 22, 2026.'],
    ],
    'body_html' => '<h2>Schedule</h2><p>Runs April 22-28.</p>',
];

ob_start();
include __DIR__ . '/../../website_download/include/news-template.php';
$html = ob_get_clean();

// head / meta
TestCase::assertContains('<title>Round 2 Counselling Schedule Announced — IPU News</title>', $html, 'title tag');
TestCase::assertContains('name="description" content="IPU has announced Round 2 counselling dates for 2026-27."', $html, 'meta description from tldr');
TestCase::assertContains('rel="canonical" href="https://ipu.co.in/news/round-2-counselling-schedule.php"', $html, 'canonical');
TestCase::assertContains('property="og:title" content="Round 2 Counselling Schedule Announced"', $html, 'og title');
TestCase::assertContains('property="og:image" content="https://ipu.co.in/assets/images/news/counselling.jpg"', $html, 'og image absolute');
TestCase::assertContains('name="twitter:card" content="summary_large_image"', $html, 'twitter card');

// JSON-LD
TestCase::assertContains('"@type": "NewsArticle"', $html, 'NewsArticle JSON-LD');
TestCase::assertContains('"@type": "FAQPage"', $html, 'FAQPage JSON-LD');
TestCase::assertContains('"@type": "BreadcrumbList"', $html, 'Breadcrumb JSON-LD');

// body / structure
TestCase::assertContains('<h1>Round 2 Counselling Schedule Announced</h1>', $html, 'h1 with title');
TestCase::assertContains('class="news-tldr"', $html, 'tldr box visible');
TestCase::assertContains('IPU has announced Round 2', $html, 'tldr text shown');
TestCase::assertContains('<h2>Schedule</h2>', $html, 'body HTML rendered');
TestCase::assertContains('Home</a>', $html, 'breadcrumbs rendered');
TestCase::assertNotContains('source_url', $html, 'no source URL leak');
TestCase::assertNotContains('source_name', $html, 'no source name leak');
TestCase::assertNotContains('"author"', $html, 'no author field leak');
