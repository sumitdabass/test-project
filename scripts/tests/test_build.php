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
TestCase::assertTrue($pos_other < $pos_featured, 'newest post renders first regardless of featured flag');

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

// --- orphan cleanup ---
$tmp4 = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp4 . '/content/news', 0755, true);
mkdir($tmp4 . '/website_download/news', 0755, true);
copy(__DIR__ . '/fixtures/sample-post.md', $tmp4 . '/content/news/sample-post.md');

// pretend a stale PHP is left from a previously-deleted MD
file_put_contents($tmp4 . '/website_download/news/stale-old-post.php', "<?php /* stale */ ?>");
// also create an index.php to confirm it's preserved
file_put_contents($tmp4 . '/website_download/news/index.php', "<?php /* index */ ?>");

$removed = news_cleanup_orphans($tmp4 . '/content/news', $tmp4 . '/website_download/news/');
TestCase::assertEqual(1, count($removed), 'one orphan removed');
TestCase::assertTrue(!file_exists($tmp4 . '/website_download/news/stale-old-post.php'), 'stale PHP deleted');
TestCase::assertTrue(file_exists($tmp4 . '/website_download/news/index.php'), 'index.php preserved');

exec('rm -rf ' . escapeshellarg($tmp4));

// --- news_validate_post() ---
TestCase::assertTrue(news_validate_post([
    'title' => 'GGSIPU Counselling 2026 Schedule', 'slug' => 'ggsipu-counselling-2026-schedule',
    'date' => '2026-06-10', 'category' => 'Counselling',
], str_repeat('Real body content. ', 30)) === [], 'valid post yields no errors');
TestCase::assertTrue(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '2026-06-10', 'category' => 'Counselling',
], '   ')) > 0, 'empty body rejected');
TestCase::assertTrue(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '1999-13-99', 'category' => 'Counselling',
], str_repeat('body ', 30))) > 0, 'bad date rejected');
TestCase::assertTrue(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '2026-06-10', 'category' => 'NotARealCategory',
], str_repeat('body ', 30))) > 0, 'unknown category rejected');
foreach (['Counselling', 'CET', 'Admissions', 'Results', 'General'] as $cat) {
    TestCase::assertTrue(news_validate_post([
        'title' => 'X', 'slug' => 'x', 'date' => '2026-06-10', 'category' => $cat,
    ], str_repeat('body ', 60)) === [], "category $cat (the scraper prompt's own list) is accepted");
}

// every post already published must pass validation, so the validator can never block the live pipeline
$bad = [];
foreach (glob(__DIR__ . '/../../content/news/*.md') as $p) {
    [$vfm, $vbody] = news_parse_mdfile($p);
    $errs = news_validate_post($vfm, $vbody);
    if ($errs) { $bad[basename($p)] = implode('; ', $errs); }
}
TestCase::assertTrue($bad === [], 'all published posts pass validation: ' . json_encode($bad));

// the build refuses an invalid post instead of deploying it
$tmp5 = sys_get_temp_dir() . '/news-invalid-' . uniqid();
mkdir($tmp5 . '/website_download/news', 0755, true);
file_put_contents($tmp5 . '/bad.md', "{\n  \"title\": \"Bad\",\n  \"slug\": \"bad\",\n  \"date\": \"2026-06-10\",\n  \"category\": \"Counselling\"\n}\n---\nToo short.\n");
$threw = false;
try { news_build_single_post($tmp5 . '/bad.md', $tmp5 . '/website_download/news/'); } catch (RuntimeException $e) { $threw = true; }
TestCase::assertTrue($threw, 'build throws on a post that fails validation');
TestCase::assertTrue(!file_exists($tmp5 . '/website_download/news/bad.php'), 'no page written for the invalid post');
exec('rm -rf ' . escapeshellarg($tmp5));
