<?php
require_once __DIR__ . '/../build-news.php';

$tmp = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp . '/content/news', 0755, true);
mkdir($tmp . '/website_download/news', 0755, true);
mkdir($tmp . '/website_download/include', 0755, true);

copy(__DIR__ . '/fixtures/sample-post.md', $tmp . '/content/news/sample-post.md');

$written = news_build_single_post($tmp . '/content/news/sample-post.md', $tmp . '/website_download/news/');

TestCase::assertEqual($tmp . '/website_download/news/round-2-counselling-schedule.php', $written, 'returns written path');
TestCase::assertTrue(file_exists($written), 'PHP file created');

$php = file_get_contents($written);
TestCase::assertContains('$post = ', $php, 'post array assignment');
TestCase::assertContains("'slug' => 'round-2-counselling-schedule'", $php, 'slug in array');
TestCase::assertContains('news-template.php', $php, 'includes shared template');
TestCase::assertContains("'body_html'", $php, 'body_html present (pre-rendered HTML)');
TestCase::assertNotContains('## Schedule Overview', $php, 'raw markdown NOT in output');
TestCase::assertContains('<h2>Schedule Overview</h2>', $php, 'rendered HTML IS in output');

exec('rm -rf ' . escapeshellarg($tmp));
