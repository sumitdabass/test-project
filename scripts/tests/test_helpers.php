<?php
require_once __DIR__ . '/../../website_download/include/news-helpers.php';

TestCase::assertEqual('ipu-round-2-counselling', news_slugify('IPU Round 2 Counselling!'), 'basic slugify');
TestCase::assertEqual('cet-result-date-confirmed-for-may-20', news_slugify('CET Result Date Confirmed for May 20'), 'spaces and caps');
TestCase::assertEqual('mba-admission-2026-27', news_slugify('MBA Admission 2026–27'), 'en-dash becomes hyphen');
TestCase::assertEqual('abc-def', news_slugify('  abc  def  '), 'trim whitespace');
TestCase::assertEqual('b-tech-colleges', news_slugify('B.Tech Colleges'), 'dots removed');
