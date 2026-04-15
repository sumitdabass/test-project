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

// --- Task 15: build_index ---
$tmp2 = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp2 . '/content/news', 0755, true);
mkdir($tmp2 . '/website_download/news', 0755, true);
copy(__DIR__ . '/fixtures/sample-post.md', $tmp2 . '/content/news/sample-post.md');
copy(__DIR__ . '/fixtures/sample-post-2.md', $tmp2 . '/content/news/sample-post-2.md');

$index_path = news_build_index($tmp2 . '/content/news', $tmp2 . '/website_download/news/');
TestCase::assertEqual($tmp2 . '/website_download/news/index.php', $index_path, 'index path returned');

$idx = file_get_contents($index_path);
TestCase::assertContains('Round 2 Counselling Schedule Announced', $idx, 'post 1 in index');
TestCase::assertContains('CET Result Date Confirmed for May 20', $idx, 'post 2 in index');
TestCase::assertContains('2026-04-15', $idx, 'newer date present');
TestCase::assertContains('2026-04-14', $idx, 'older date present');

$pos_featured = strpos($idx, 'CET Result Date Confirmed');
$pos_other = strpos($idx, 'Round 2 Counselling Schedule');
TestCase::assertTrue($pos_featured < $pos_other, 'featured post renders before non-featured');

exec('rm -rf ' . escapeshellarg($tmp2));

// --- Task 16: sitemap + llms.txt ---
$tmp3 = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp3 . '/content/news', 0755, true);
mkdir($tmp3 . '/website_download/news', 0755, true);
copy(__DIR__ . '/fixtures/sample-post.md', $tmp3 . '/content/news/sample-post.md');

file_put_contents($tmp3 . '/website_download/sitemap.xml',
    '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
    '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" .
    '  <url><loc>https://ipu.co.in/</loc></url>' . "\n" .
    '</urlset>' . "\n");
file_put_contents($tmp3 . '/website_download/llms.txt', "# IPU.co.in — LLM guide\n\nHome: https://ipu.co.in/\n");

news_update_sitemap($tmp3 . '/content/news', $tmp3 . '/website_download/sitemap.xml');
$sm = file_get_contents($tmp3 . '/website_download/sitemap.xml');
TestCase::assertContains('<loc>https://ipu.co.in/news/round-2-counselling-schedule.php</loc>', $sm, 'post URL in sitemap');
TestCase::assertContains('<lastmod>2026-04-15</lastmod>', $sm, 'lastmod set');
TestCase::assertContains('<loc>https://ipu.co.in/</loc>', $sm, 'existing entries preserved');

news_update_llms_txt($tmp3 . '/content/news', $tmp3 . '/website_download/llms.txt');
$llm = file_get_contents($tmp3 . '/website_download/llms.txt');
TestCase::assertContains('## IPU News', $llm, 'news section header');
TestCase::assertContains('Round 2 Counselling Schedule Announced', $llm, 'post title listed');
TestCase::assertContains('https://ipu.co.in/news/round-2-counselling-schedule.php', $llm, 'post URL listed');

exec('rm -rf ' . escapeshellarg($tmp3));
