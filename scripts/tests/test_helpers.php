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
