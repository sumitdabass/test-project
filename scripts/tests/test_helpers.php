<?php
require_once __DIR__ . '/../../website_download/include/news-helpers.php';

TestCase::assertEqual('ipu-round-2-counselling', news_slugify('IPU Round 2 Counselling!'), 'basic slugify');
TestCase::assertEqual('cet-result-date-confirmed-for-may-20', news_slugify('CET Result Date Confirmed for May 20'), 'spaces and caps');
TestCase::assertEqual('mba-admission-2026-27', news_slugify('MBA Admission 2026–27'), 'en-dash becomes hyphen');
TestCase::assertEqual('abc-def', news_slugify('  abc  def  '), 'trim whitespace');
TestCase::assertEqual('b-tech-colleges', news_slugify('B.Tech Colleges'), 'dots removed');
TestCase::assertEqual('', news_slugify(''), 'empty input returns empty');
TestCase::assertEqual('', news_slugify('!!! ??? ---'), 'all-punctuation input collapses to empty');
TestCase::assertEqual('already-a-slug', news_slugify('already-a-slug'), 'idempotent on existing slug');

TestCase::assertEqual(['Counselling','CET','Admissions','Results','General'], news_categories(), 'five categories');
TestCase::assertEqual('assets/images/news/counselling.jpg', news_category_image('Counselling'), 'counselling image');
TestCase::assertEqual('assets/images/news/general.jpg', news_category_image('NonExistent'), 'unknown category falls back to general');
TestCase::assertTrue(news_is_valid_category('CET'), 'CET is valid');
TestCase::assertTrue(!news_is_valid_category('Sports'), 'Sports is not valid');

$html = news_md_to_html("## Hello\n\nThis is **bold**.");
TestCase::assertContains('<h2>Hello</h2>', $html, 'h2 rendered');
TestCase::assertContains('<strong>bold</strong>', $html, 'bold rendered');

$html2 = news_md_to_html("- one\n- two\n- three");
TestCase::assertContains('<ul>', $html2, 'list rendered');
TestCase::assertContains('<li>one</li>', $html2, 'list item rendered');

[$fm, $body] = news_parse_mdfile(__DIR__ . '/fixtures/sample-post.md');
TestCase::assertEqual('Round 2 Counselling Schedule Announced', $fm['title'], 'title parsed');
TestCase::assertEqual('Counselling', $fm['category'], 'category parsed');
TestCase::assertEqual(['B.Tech', 'MBA'], $fm['tags'], 'tags parsed');
TestCase::assertEqual('April 22, 2026.', $fm['faq'][0]['a'], 'faq parsed');
TestCase::assertContains('Round 2 counselling runs', $body, 'body contains text');
TestCase::assertNotContains('"title"', $body, 'frontmatter not in body');

TestCase::assertEqual(1, news_read_time('just a few words'), '< 200 words → 1 min minimum');
$long = str_repeat('word ', 400);
TestCase::assertEqual(2, news_read_time($long), '400 words at 200 wpm → 2 min');
$longer = str_repeat('word ', 1000);
TestCase::assertEqual(5, news_read_time($longer), '1000 words → 5 min');

$tmp_state = sys_get_temp_dir() . '/news_state_' . uniqid() . '.json';
TestCase::assertTrue(!news_state_has_seen($tmp_state, 'https://example.com/a'), 'unseen URL returns false');
news_state_mark_seen($tmp_state, 'https://example.com/a');
TestCase::assertTrue(news_state_has_seen($tmp_state, 'https://example.com/a'), 'seen URL returns true');
TestCase::assertTrue(!news_state_has_seen($tmp_state, 'https://example.com/b'), 'other URL still unseen');
news_state_mark_seen($tmp_state, 'https://example.com/b');
TestCase::assertEqual(2, count(json_decode(file_get_contents($tmp_state), true)['seen']), 'two hashes stored');
unlink($tmp_state);
