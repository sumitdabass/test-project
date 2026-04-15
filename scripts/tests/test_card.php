<?php
require_once __DIR__ . '/../../website_download/include/news-helpers.php';

$post = [
    'title' => 'Round 2 Counselling Schedule Announced',
    'slug' => 'round-2-counselling-schedule',
    'date' => '2026-04-15',
    'category' => 'Counselling',
    'image' => 'assets/images/news/counselling.jpg',
    'tldr' => 'Round 2 counselling dates announced.',
    'read_time' => 5,
    'is_urgent' => false,
];

ob_start();
include __DIR__ . '/../../website_download/include/news-card.php';
$html = ob_get_clean();

TestCase::assertContains('Round 2 Counselling Schedule Announced', $html, 'title in card');
TestCase::assertContains('href="/news/round-2-counselling-schedule.php"', $html, 'link to post');
TestCase::assertContains('assets/images/news/counselling.jpg', $html, 'image src');
TestCase::assertContains('Round 2 counselling dates announced.', $html, 'tldr shown');
TestCase::assertContains('5 min read', $html, 'read time shown');
TestCase::assertContains('Counselling', $html, 'category badge');
TestCase::assertContains('2026-04-15', $html, 'date shown');
TestCase::assertNotContains('news-card--urgent', $html, 'no urgent class when is_urgent false');

$post['is_urgent'] = true;
ob_start();
include __DIR__ . '/../../website_download/include/news-card.php';
$html_urgent = ob_get_clean();
TestCase::assertContains('news-card--urgent', $html_urgent, 'urgent class added');
