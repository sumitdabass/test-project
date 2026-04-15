# Automated News & Announcements Blog — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a fully-automated News & Announcements section at `ipu.co.in/news/` — daily scrape of official IPU sources + Google News RSS, Claude rewrite, static PHP pages, Slack DM digest with edit/unpublish/suggest-edit controls.

**Architecture:** JSON-frontmatter MD files are the source of truth in `content/news/`. A PHP CLI build script renders each MD into a thin-wrapper `.php` file under `website_download/news/` plus updates `index.php`, `sitemap.xml`, and `llms.txt`. A new IPU-dedicated n8n instance runs the daily scrape-rewrite-build-deploy pipeline and a suite of webhook-driven edit/unpublish/suggest-edit workflows. All edits flow through git for rollback.

**Tech Stack:** PHP 7.4+ (static site), Parsedown (vendored single-file markdown parser), Python 3 (FTP), n8n (orchestration), Claude Sonnet 4.6 via Anthropic API, Slack Block Kit, GitHub (source of truth + rollback).

**Reference design spec:** `docs/superpowers/specs/2026-04-15-automated-news-blog-design.md`

**Phase milestones:**
- **Phase 1 (Tasks 1–16):** Local PHP template + build script. End state: can hand-author an MD file and run `php scripts/build-news.php` to produce live-ready `.php` pages, index, sitemap, llms.txt entries — all passing automated tests.
- **Phase 2 (Tasks 17–22):** n8n workflows for scrape/rewrite, unpublish, rebuild-single, suggest-edit, weekly summary.
- **Phase 3 (Tasks 23–25):** FTP deployment, end-to-end smoke test against `/news-staging/` on cPanel, cutover to prod.

---

## Phase 1: Local PHP Template + Build Script

### Task 1: Test harness setup

**Files:**
- Create: `scripts/tests/TestCase.php`
- Create: `scripts/tests/run.php`

- [ ] **Step 1: Write the harness**

Create `scripts/tests/TestCase.php`:

```php
<?php
class TestCase {
    public static int $passed = 0;
    public static int $failed = 0;
    public static array $failures = [];

    public static function assertEqual($expected, $actual, string $msg = ''): void {
        if ($expected === $actual) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected: " . var_export($expected, true) . "\n  Actual:   " . var_export($actual, true);
        }
    }

    public static function assertContains(string $needle, string $haystack, string $msg = ''): void {
        if (strpos($haystack, $needle) !== false) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected to find: $needle\n  In: " . substr($haystack, 0, 200);
        }
    }

    public static function assertNotContains(string $needle, string $haystack, string $msg = ''): void {
        if (strpos($haystack, $needle) === false) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected NOT to find: $needle";
        }
    }

    public static function assertTrue($actual, string $msg = ''): void {
        self::assertEqual(true, (bool)$actual, $msg);
    }

    public static function report(): int {
        echo "\n" . self::$passed . " passed, " . self::$failed . " failed\n";
        foreach (self::$failures as $f) { echo "\n$f\n"; }
        return self::$failed > 0 ? 1 : 0;
    }
}
```

Create `scripts/tests/run.php`:

```php
<?php
require __DIR__ . '/TestCase.php';
$files = glob(__DIR__ . '/test_*.php');
foreach ($files as $f) {
    echo "Running " . basename($f) . "...\n";
    require $f;
}
exit(TestCase::report());
```

- [ ] **Step 2: Run harness to verify it works with zero tests**

Run: `php scripts/tests/run.php`
Expected: `0 passed, 0 failed` and exit code 0.

- [ ] **Step 3: Commit**

```bash
git add scripts/tests/TestCase.php scripts/tests/run.php
git commit -m "chore: add minimal PHP test harness for news blog module"
```

---

### Task 2: Slug utility (TDD)

**Files:**
- Create: `website_download/include/news-helpers.php`
- Create: `scripts/tests/test_helpers.php`

- [ ] **Step 1: Write failing test**

Create `scripts/tests/test_helpers.php`:

```php
<?php
require_once __DIR__ . '/../../website_download/include/news-helpers.php';

TestCase::assertEqual('ipu-round-2-counselling', news_slugify('IPU Round 2 Counselling!'), 'basic slugify');
TestCase::assertEqual('cet-result-date-confirmed-for-may-20', news_slugify('CET Result Date Confirmed for May 20'), 'spaces and caps');
TestCase::assertEqual('mba-admission-2026-27', news_slugify('MBA Admission 2026–27'), 'en-dash becomes hyphen');
TestCase::assertEqual('abc-def', news_slugify('  abc  def  '), 'trim whitespace');
TestCase::assertEqual('b-tech-colleges', news_slugify('B.Tech Colleges'), 'dots removed');
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php scripts/tests/run.php`
Expected: Fatal error because `news-helpers.php` doesn't exist (or `news_slugify` undefined).

- [ ] **Step 3: Implement minimal slugify**

Create `website_download/include/news-helpers.php`:

```php
<?php

function news_slugify(string $title): string {
    $s = mb_strtolower($title, 'UTF-8');
    $s = preg_replace('/[\x{2013}\x{2014}]/u', '-', $s);  // en-dash, em-dash → hyphen
    $s = preg_replace('/[^\p{L}\p{N}\s-]/u', ' ', $s);     // punctuation becomes whitespace (then collapsed)
    $s = preg_replace('/\s+/', '-', trim($s));
    $s = preg_replace('/-+/', '-', $s);
    return trim($s, '-');
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php scripts/tests/run.php`
Expected: `5 passed, 0 failed`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php
git commit -m "feat(news): add news_slugify helper with tests"
```

---

### Task 3: Category map + image resolver (TDD)

**Files:**
- Modify: `website_download/include/news-helpers.php`
- Modify: `scripts/tests/test_helpers.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_helpers.php`:

```php
TestCase::assertEqual(['Counselling','CET','Admissions','Results','General'], news_categories(), 'five categories');
TestCase::assertEqual('assets/images/news/counselling.jpg', news_category_image('Counselling'), 'counselling image');
TestCase::assertEqual('assets/images/news/general.jpg', news_category_image('NonExistent'), 'unknown category falls back to general');
TestCase::assertTrue(news_is_valid_category('CET'), 'CET is valid');
TestCase::assertTrue(!news_is_valid_category('Sports'), 'Sports is not valid');
```

- [ ] **Step 2: Run and confirm failure**

Run: `php scripts/tests/run.php`
Expected: Fatal — `news_categories` undefined.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-helpers.php`:

```php
function news_categories(): array {
    return ['Counselling', 'CET', 'Admissions', 'Results', 'General'];
}

function news_is_valid_category(string $c): bool {
    return in_array($c, news_categories(), true);
}

function news_category_image(string $c): string {
    $slug = news_is_valid_category($c) ? strtolower($c) : 'general';
    return "assets/images/news/{$slug}.jpg";
}
```

- [ ] **Step 4: Run tests, expect all pass**

Run: `php scripts/tests/run.php`
Expected: `10 passed, 0 failed`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php
git commit -m "feat(news): add category helpers (list, validator, image resolver)"
```

---

### Task 4: Vendor Parsedown

**Files:**
- Create: `scripts/vendor/Parsedown.php`
- Create: `scripts/vendor/LICENSE-parsedown.txt`

- [ ] **Step 1: Download Parsedown 1.7.4 (stable, single-file)**

```bash
curl -L -o scripts/vendor/Parsedown.php https://raw.githubusercontent.com/erusev/parsedown/1.7.4/Parsedown.php
```

- [ ] **Step 2: Verify integrity**

Run: `head -5 scripts/vendor/Parsedown.php`
Expected: file starts with `<?php` and `# Parsedown` comment mentioning Emanuil Rusev.

Run: `php -l scripts/vendor/Parsedown.php`
Expected: `No syntax errors detected`.

- [ ] **Step 3: Add license notice**

Create `scripts/vendor/LICENSE-parsedown.txt`:

```
Parsedown by Emanuil Rusev — https://parsedown.org
Licensed under MIT: https://github.com/erusev/parsedown/blob/master/LICENSE.txt
Vendored version: 1.7.4
```

- [ ] **Step 4: Commit**

```bash
git add scripts/vendor/Parsedown.php scripts/vendor/LICENSE-parsedown.txt
git commit -m "chore: vendor Parsedown 1.7.4 for news blog markdown parsing"
```

---

### Task 5: Markdown rendering wrapper (TDD)

**Files:**
- Modify: `website_download/include/news-helpers.php`
- Modify: `scripts/tests/test_helpers.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_helpers.php`:

```php
$html = news_md_to_html("## Hello\n\nThis is **bold**.");
TestCase::assertContains('<h2>Hello</h2>', $html, 'h2 rendered');
TestCase::assertContains('<strong>bold</strong>', $html, 'bold rendered');

$html2 = news_md_to_html("- one\n- two\n- three");
TestCase::assertContains('<ul>', $html2, 'list rendered');
TestCase::assertContains('<li>one</li>', $html2, 'list item rendered');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`
Expected: Fatal — `news_md_to_html` undefined.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-helpers.php`:

```php
function news_md_to_html(string $md): string {
    static $parser = null;
    if ($parser === null) {
        require_once __DIR__ . '/../../scripts/vendor/Parsedown.php';
        $parser = new Parsedown();
        $parser->setSafeMode(true);  // escape raw HTML in case AI output has it
    }
    return $parser->text($md);
}
```

- [ ] **Step 4: Run tests, expect pass**

Run: `php scripts/tests/run.php`
Expected: `14 passed, 0 failed`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php
git commit -m "feat(news): add news_md_to_html wrapper around Parsedown"
```

---

### Task 6: JSON-frontmatter parser (TDD)

**Files:**
- Modify: `website_download/include/news-helpers.php`
- Modify: `scripts/tests/test_helpers.php`
- Create: `scripts/tests/fixtures/sample-post.md`

- [ ] **Step 1: Create fixture**

Create `scripts/tests/fixtures/sample-post.md`:

```
{
  "title": "Round 2 Counselling Schedule Announced",
  "slug": "round-2-counselling-schedule",
  "date": "2026-04-15",
  "date_modified": "2026-04-15",
  "category": "Counselling",
  "tags": ["B.Tech", "MBA"],
  "featured": false,
  "is_urgent": false,
  "image": "assets/images/news/counselling.jpg",
  "tldr": "IPU has announced Round 2 counselling dates for 2026-27.",
  "faq": [
    {"q": "When does Round 2 start?", "a": "April 22, 2026."}
  ]
}
---
## Schedule Overview

Round 2 counselling runs from **April 22 to April 28**, 2026.

- Day 1: B.Tech
- Day 2: MBA
- Day 3: Law
```

- [ ] **Step 2: Append failing tests**

Add to `scripts/tests/test_helpers.php`:

```php
[$fm, $body] = news_parse_mdfile(__DIR__ . '/fixtures/sample-post.md');
TestCase::assertEqual('Round 2 Counselling Schedule Announced', $fm['title'], 'title parsed');
TestCase::assertEqual('Counselling', $fm['category'], 'category parsed');
TestCase::assertEqual(['B.Tech', 'MBA'], $fm['tags'], 'tags parsed');
TestCase::assertEqual('April 22, 2026.', $fm['faq'][0]['a'], 'faq parsed');
TestCase::assertContains('Round 2 counselling runs', $body, 'body contains text');
TestCase::assertNotContains('"title"', $body, 'frontmatter not in body');
```

- [ ] **Step 3: Run, expect failure**

Run: `php scripts/tests/run.php`
Expected: Fatal — `news_parse_mdfile` undefined.

- [ ] **Step 4: Implement parser**

Append to `website_download/include/news-helpers.php`:

```php
function news_parse_mdfile(string $path): array {
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Cannot read MD file: $path");
    }
    $parts = preg_split('/^---\s*$/m', $raw, 2);
    if (count($parts) !== 2) {
        throw new RuntimeException("MD file missing '---' separator: $path");
    }
    $fm = json_decode(trim($parts[0]), true);
    if (!is_array($fm)) {
        throw new RuntimeException("Invalid JSON frontmatter in $path: " . json_last_error_msg());
    }
    $body = ltrim($parts[1]);
    return [$fm, $body];
}
```

- [ ] **Step 5: Run tests, expect pass**

Run: `php scripts/tests/run.php`
Expected: `20 passed, 0 failed`.

- [ ] **Step 6: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php scripts/tests/fixtures/sample-post.md
git commit -m "feat(news): add JSON-frontmatter MD parser with fixture test"
```

---

### Task 7: Read-time calculator (TDD)

**Files:**
- Modify: `website_download/include/news-helpers.php`
- Modify: `scripts/tests/test_helpers.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_helpers.php`:

```php
TestCase::assertEqual(1, news_read_time('just a few words'), '< 200 words → 1 min minimum');
$long = str_repeat('word ', 400);  // 400 words
TestCase::assertEqual(2, news_read_time($long), '400 words at 200 wpm → 2 min');
$longer = str_repeat('word ', 1000);
TestCase::assertEqual(5, news_read_time($longer), '1000 words → 5 min');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-helpers.php`:

```php
function news_read_time(string $body_md): int {
    $words = str_word_count(strip_tags($body_md));
    return max(1, (int) ceil($words / 200));
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php
git commit -m "feat(news): add read-time calculator (200 wpm floor)"
```

---

### Task 8: JSON-LD builder — NewsArticle (TDD)

**Files:**
- Create: `website_download/include/news-jsonld.php`
- Create: `scripts/tests/test_jsonld.php`

- [ ] **Step 1: Write failing test**

Create `scripts/tests/test_jsonld.php`:

```php
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
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Create `website_download/include/news-jsonld.php`:

```php
<?php

const NEWS_SITE_ORIGIN = 'https://ipu.co.in';

function news_jsonld_newsarticle(array $post): string {
    $url = NEWS_SITE_ORIGIN . '/news/' . $post['slug'] . '.php';
    $img = NEWS_SITE_ORIGIN . '/' . ltrim($post['image'], '/');

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
        'headline' => $post['title'],
        'description' => $post['tldr'],
        'image' => $img,
        'datePublished' => $post['date'],
        'dateModified' => $post['date_modified'] ?? $post['date'],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'IPU.co.in',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => NEWS_SITE_ORIGIN . '/assets/images/logo.png',
            ],
        ],
        'articleSection' => $post['category'],
    ];

    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-jsonld.php scripts/tests/test_jsonld.php
git commit -m "feat(news): add NewsArticle JSON-LD builder"
```

---

### Task 9: JSON-LD builder — FAQPage (TDD)

**Files:**
- Modify: `website_download/include/news-jsonld.php`
- Modify: `scripts/tests/test_jsonld.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_jsonld.php`:

```php
$post_with_faq = $post + ['faq' => [
    ['q' => 'When does Round 2 start?', 'a' => 'April 22, 2026.'],
    ['q' => 'Who can apply?', 'a' => 'All 10+2 qualified candidates.'],
]];

$faq_jsonld = news_jsonld_faqpage($post_with_faq);
TestCase::assertEqual('', $faq_jsonld !== '' ? '' : $faq_jsonld, 'non-empty when faq present');
$decoded_faq = json_decode($faq_jsonld, true);
TestCase::assertEqual('FAQPage', $decoded_faq['@type'], 'type FAQPage');
TestCase::assertEqual(2, count($decoded_faq['mainEntity']), 'two FAQs');
TestCase::assertEqual('Question', $decoded_faq['mainEntity'][0]['@type'], 'first is Question');
TestCase::assertEqual('When does Round 2 start?', $decoded_faq['mainEntity'][0]['name'], 'question text');
TestCase::assertEqual('April 22, 2026.', $decoded_faq['mainEntity'][0]['acceptedAnswer']['text'], 'answer text');

$no_faq = news_jsonld_faqpage($post);
TestCase::assertEqual('', $no_faq, 'empty string when no faq');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-jsonld.php`:

```php
function news_jsonld_faqpage(array $post): string {
    if (empty($post['faq']) || !is_array($post['faq'])) {
        return '';
    }
    $entities = [];
    foreach ($post['faq'] as $pair) {
        if (empty($pair['q']) || empty($pair['a'])) continue;
        $entities[] = [
            '@type' => 'Question',
            'name' => $pair['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $pair['a']],
        ];
    }
    if (empty($entities)) return '';
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $entities,
    ];
    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-jsonld.php scripts/tests/test_jsonld.php
git commit -m "feat(news): add FAQPage JSON-LD builder (empty string when no faq)"
```

---

### Task 10: JSON-LD builder — BreadcrumbList (TDD)

**Files:**
- Modify: `website_download/include/news-jsonld.php`
- Modify: `scripts/tests/test_jsonld.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_jsonld.php`:

```php
$bc = news_jsonld_breadcrumb($post);
$decoded_bc = json_decode($bc, true);
TestCase::assertEqual('BreadcrumbList', $decoded_bc['@type'], 'type BreadcrumbList');
TestCase::assertEqual(4, count($decoded_bc['itemListElement']), 'four crumbs: Home, News, Counselling, Post');
TestCase::assertEqual('Home', $decoded_bc['itemListElement'][0]['name'], 'first is Home');
TestCase::assertEqual('News', $decoded_bc['itemListElement'][1]['name'], 'second is News');
TestCase::assertEqual('Counselling', $decoded_bc['itemListElement'][2]['name'], 'third is category');
TestCase::assertEqual('Round 2 Counselling Schedule Announced', $decoded_bc['itemListElement'][3]['name'], 'fourth is post title');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-jsonld.php`:

```php
function news_jsonld_breadcrumb(array $post): string {
    $crumbs = [
        ['Home', NEWS_SITE_ORIGIN . '/'],
        ['News', NEWS_SITE_ORIGIN . '/news/'],
        [$post['category'], NEWS_SITE_ORIGIN . '/news/?cat=' . strtolower($post['category'])],
        [$post['title'], NEWS_SITE_ORIGIN . '/news/' . $post['slug'] . '.php'],
    ];
    $list = [];
    foreach ($crumbs as $i => [$name, $url]) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $name,
            'item' => $url,
        ];
    }
    return json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-jsonld.php scripts/tests/test_jsonld.php
git commit -m "feat(news): add BreadcrumbList JSON-LD builder"
```

---

### Task 11: News card partial (TDD)

**Files:**
- Create: `website_download/include/news-card.php`
- Create: `scripts/tests/test_card.php`

- [ ] **Step 1: Write failing test**

Create `scripts/tests/test_card.php`:

```php
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
TestCase::assertNotContains('urgent', $html, 'no urgent badge when is_urgent false');

// urgent case
$post['is_urgent'] = true;
ob_start();
include __DIR__ . '/../../website_download/include/news-card.php';
$html_urgent = ob_get_clean();
TestCase::assertContains('news-card--urgent', $html_urgent, 'urgent class added');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Create `website_download/include/news-card.php`:

```php
<?php
/** @var array $post — required by caller */
$urgent_class = !empty($post['is_urgent']) ? ' news-card--urgent' : '';
$post_url = '/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
?>
<article class="news-card<?= $urgent_class ?>">
    <a href="<?= $post_url ?>" class="news-card__link">
        <img src="/<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>"
             alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
             class="news-card__image" loading="lazy">
        <div class="news-card__body">
            <span class="news-card__category"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></span>
            <h3 class="news-card__title"><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></h3>
            <p class="news-card__tldr"><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p>
            <div class="news-card__meta">
                <time datetime="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>"><?= htmlspecialchars($post['date'], ENT_QUOTES) ?></time>
                <span class="news-card__read-time"><?= (int)$post['read_time'] ?> min read</span>
            </div>
        </div>
    </a>
</article>
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-card.php scripts/tests/test_card.php
git commit -m "feat(news): add news card partial with urgent styling"
```

---

### Task 12: News template — full page render (TDD)

**Files:**
- Create: `website_download/include/news-template.php`
- Create: `scripts/tests/test_template.php`

This task is larger. Template is tested via multiple assertions on rendered HTML, not snapshot diff — that's lower-maintenance.

- [ ] **Step 1: Write failing test**

Create `scripts/tests/test_template.php`:

```php
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

// head/meta
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

// body/structure
TestCase::assertContains('<h1>Round 2 Counselling Schedule Announced</h1>', $html, 'h1 with title');
TestCase::assertContains('class="news-tldr"', $html, 'tldr box visible');
TestCase::assertContains('IPU has announced Round 2', $html, 'tldr text shown');
TestCase::assertContains('<h2>Schedule</h2>', $html, 'body HTML rendered');
TestCase::assertContains('Home</a>', $html, 'breadcrumbs rendered');
TestCase::assertNotContains('source_url', $html, 'no source URL leak');
TestCase::assertNotContains('source_name', $html, 'no source name leak');
TestCase::assertNotContains('author', $html, 'no author leak');
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement template**

Create `website_download/include/news-template.php`:

```php
<?php
/** @var array $post — must include body_html, title, slug, date, date_modified, category, tags, read_time, is_urgent, image, tldr, faq */
require_once __DIR__ . '/news-helpers.php';
require_once __DIR__ . '/news-jsonld.php';

$post_url = 'https://ipu.co.in/news/' . htmlspecialchars($post['slug'], ENT_QUOTES) . '.php';
$img_abs = 'https://ipu.co.in/' . ltrim($post['image'], '/');
$jsonld_article = news_jsonld_newsarticle($post);
$jsonld_faq = news_jsonld_faqpage($post);
$jsonld_bc = news_jsonld_breadcrumb($post);

include_once __DIR__ . '/base-head.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($post['title'], ENT_QUOTES) ?> — IPU News</title>
<meta name="description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<link rel="canonical" href="<?= $post_url ?>">
<meta name="robots" content="index, follow, max-image-preview:large">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
<meta property="og:description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<meta property="og:url" content="<?= $post_url ?>">
<meta property="og:image" content="<?= htmlspecialchars($img_abs, ENT_QUOTES) ?>">
<meta property="og:site_name" content="IPU.co.in">
<meta property="article:published_time" content="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>">
<meta property="article:modified_time" content="<?= htmlspecialchars($post['date_modified'], ENT_QUOTES) ?>">
<meta property="article:section" content="<?= htmlspecialchars($post['category'], ENT_QUOTES) ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($img_abs, ENT_QUOTES) ?>">

<script type="application/ld+json"><?= $jsonld_article ?></script>
<?php if ($jsonld_faq): ?>
<script type="application/ld+json"><?= $jsonld_faq ?></script>
<?php endif; ?>
<script type="application/ld+json"><?= $jsonld_bc ?></script>

<link rel="stylesheet" href="/assets/css/bundle.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="news-post">
    <nav class="news-breadcrumbs" aria-label="Breadcrumb">
        <a href="/">Home</a> /
        <a href="/news/">News</a> /
        <a href="/news/?cat=<?= strtolower($post['category']) ?>"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></a> /
        <span><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></span>
    </nav>

    <?php if (!empty($post['is_urgent'])): ?>
    <div class="news-urgent-banner">⚠ Time-sensitive: please check dates directly with official sources.</div>
    <?php endif; ?>

    <header class="news-post__header">
        <span class="news-post__category"><?= htmlspecialchars($post['category'], ENT_QUOTES) ?></span>
        <h1><?= htmlspecialchars($post['title'], ENT_QUOTES) ?></h1>
        <div class="news-post__meta">
            <time datetime="<?= htmlspecialchars($post['date'], ENT_QUOTES) ?>">Published <?= htmlspecialchars($post['date'], ENT_QUOTES) ?></time>
            <?php if (!empty($post['date_modified']) && $post['date_modified'] !== $post['date']): ?>
                <span> · Updated <?= htmlspecialchars($post['date_modified'], ENT_QUOTES) ?></span>
            <?php endif; ?>
            <span> · <?= (int)$post['read_time'] ?> min read</span>
        </div>
    </header>

    <figure class="news-post__image">
        <img src="/<?= htmlspecialchars($post['image'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>">
    </figure>

    <aside class="news-tldr">
        <strong>TL;DR</strong>
        <p><?= htmlspecialchars($post['tldr'], ENT_QUOTES) ?></p>
    </aside>

    <div class="news-post__body">
        <?= $post['body_html'] ?>
    </div>

    <?php if (!empty($post['faq'])): ?>
    <section class="news-faq">
        <h2>Frequently Asked Questions</h2>
        <?php foreach ($post['faq'] as $pair): ?>
        <details>
            <summary><?= htmlspecialchars($pair['q'], ENT_QUOTES) ?></summary>
            <p><?= htmlspecialchars($pair['a'], ENT_QUOTES) ?></p>
        </details>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($post['tags'])): ?>
    <footer class="news-post__tags">
        <?php foreach ($post['tags'] as $tag): ?>
            <span class="news-tag"><?= htmlspecialchars($tag, ENT_QUOTES) ?></span>
        <?php endforeach; ?>
    </footer>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
```

- [ ] **Step 4: Stub required includes for test isolation**

The test doesn't want to pull in real navbar/footer. Add at top of `scripts/tests/test_template.php` BEFORE the include block:

```php
// stub includes not relevant to template-under-test
if (!file_exists(__DIR__ . '/../../website_download/include/base-head.php')) {
    file_put_contents(__DIR__ . '/../../website_download/include/base-head.php', '<?php // stub for tests' . PHP_EOL);
}
```

(Only needed if those files don't yet exist. In this repo they do — verify with `ls website_download/include/base-head.php website_download/include/components/navbar.php website_download/include/components/footer.php`. If any missing, adjust test to skip the problematic include.)

- [ ] **Step 5: Run, expect pass**

Run: `php scripts/tests/run.php`.

Expected: all assertions pass. If navbar/footer includes break test, temporarily comment them out in `news-template.php` for the test run.

- [ ] **Step 6: Commit**

```bash
git add website_download/include/news-template.php scripts/tests/test_template.php
git commit -m "feat(news): add full post page template with SEO + JSON-LD + FAQ"
```

---

### Task 13: Dedup state manager (TDD)

**Files:**
- Modify: `website_download/include/news-helpers.php`
- Modify: `scripts/tests/test_helpers.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_helpers.php`:

```php
$tmp_state = sys_get_temp_dir() . '/news_state_' . uniqid() . '.json';

TestCase::assertTrue(!news_state_has_seen($tmp_state, 'https://example.com/a'), 'unseen URL returns false');
news_state_mark_seen($tmp_state, 'https://example.com/a');
TestCase::assertTrue(news_state_has_seen($tmp_state, 'https://example.com/a'), 'seen URL returns true');
TestCase::assertTrue(!news_state_has_seen($tmp_state, 'https://example.com/b'), 'other URL still unseen');
news_state_mark_seen($tmp_state, 'https://example.com/b');
TestCase::assertEqual(2, count(json_decode(file_get_contents($tmp_state), true)['seen']), 'two hashes stored');

unlink($tmp_state);
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Append to `website_download/include/news-helpers.php`:

```php
function news_state_load(string $path): array {
    if (!file_exists($path)) return ['seen' => []];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) && isset($data['seen']) ? $data : ['seen' => []];
}

function news_state_has_seen(string $path, string $url): bool {
    $state = news_state_load($path);
    return in_array(hash('sha256', $url), $state['seen'], true);
}

function news_state_mark_seen(string $path, string $url): void {
    $state = news_state_load($path);
    $hash = hash('sha256', $url);
    if (!in_array($hash, $state['seen'], true)) {
        $state['seen'][] = $hash;
    }
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT));
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add website_download/include/news-helpers.php scripts/tests/test_helpers.php
git commit -m "feat(news): add dedup state manager (SHA-256 hashes of seen source URLs)"
```

---

### Task 14: Build script — render single post (TDD)

**Files:**
- Create: `scripts/build-news.php`
- Create: `scripts/tests/test_build.php`

- [ ] **Step 1: Write failing test**

Create `scripts/tests/test_build.php`:

```php
<?php
require_once __DIR__ . '/../build-news.php';

$tmp = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp . '/content/news', 0755, true);
mkdir($tmp . '/website_download/news', 0755, true);
mkdir($tmp . '/website_download/include', 0755, true);

// use our fixture as the source MD
copy(__DIR__ . '/fixtures/sample-post.md', $tmp . '/content/news/sample-post.md');

$written = news_build_single_post($tmp . '/content/news/sample-post.md', $tmp . '/website_download/news/');

TestCase::assertEqual($tmp . '/website_download/news/round-2-counselling-schedule.php', $written, 'returns written path');
TestCase::assertTrue(file_exists($written), 'PHP file created');

$php = file_get_contents($written);
TestCase::assertContains('$post = ', $php, 'post array assignment');
TestCase::assertContains("'slug' => 'round-2-counselling-schedule'", $php, 'slug in array');
TestCase::assertContains("news-template.php", $php, 'includes shared template');
TestCase::assertContains("'body_html'", $php, 'body_html present (pre-rendered HTML)');
TestCase::assertNotContains('## Schedule Overview', $php, 'raw markdown NOT in output');
TestCase::assertContains('<h2>Schedule Overview</h2>', $php, 'rendered HTML IS in output');

// cleanup
exec("rm -rf " . escapeshellarg($tmp));
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement**

Create `scripts/build-news.php`:

```php
<?php
require_once __DIR__ . '/../website_download/include/news-helpers.php';

function news_build_single_post(string $md_path, string $out_dir): string {
    [$fm, $body_md] = news_parse_mdfile($md_path);

    // enforce derived fields
    $fm['read_time'] = news_read_time($body_md);
    $fm['body_html'] = news_md_to_html($body_md);

    // default image from category if missing
    if (empty($fm['image'])) {
        $fm['image'] = news_category_image($fm['category'] ?? 'General');
    }

    $slug = $fm['slug'] ?? news_slugify($fm['title']);
    $out_file = rtrim($out_dir, '/') . '/' . $slug . '.php';

    $exported = var_export($fm, true);
    $content = "<?php\n\$post = $exported;\ninclude __DIR__ . '/../include/news-template.php';\n";

    if (!is_dir($out_dir)) mkdir($out_dir, 0755, true);
    file_put_contents($out_file, $content);

    return $out_file;
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: Commit**

```bash
git add scripts/build-news.php scripts/tests/test_build.php
git commit -m "feat(news): add build script — render single MD to thin-wrapper PHP"
```

---

### Task 15: Build script — render index page (TDD)

**Files:**
- Modify: `scripts/build-news.php`
- Create: `scripts/tests/fixtures/sample-post-2.md`
- Modify: `scripts/tests/test_build.php`

- [ ] **Step 1: Add second fixture**

Create `scripts/tests/fixtures/sample-post-2.md`:

```
{
  "title": "CET Result Date Confirmed for May 20",
  "slug": "cet-result-date-confirmed",
  "date": "2026-04-14",
  "date_modified": "2026-04-14",
  "category": "CET",
  "tags": ["CET", "Results"],
  "featured": true,
  "is_urgent": false,
  "image": "assets/images/news/cet.jpg",
  "tldr": "IPU CET 2026 results will be declared on May 20, 2026.",
  "faq": []
}
---
## Result Details

Results will be published on the official portal at 10 AM IST.
```

- [ ] **Step 2: Append failing tests**

Add to `scripts/tests/test_build.php`:

```php
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

// featured post should render first (post 2 has featured:true)
$pos_featured = strpos($idx, 'CET Result Date Confirmed');
$pos_other = strpos($idx, 'Round 2 Counselling Schedule');
TestCase::assertTrue($pos_featured < $pos_other, 'featured post renders before non-featured');

exec("rm -rf " . escapeshellarg($tmp2));
```

- [ ] **Step 3: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 4: Implement index builder**

Append to `scripts/build-news.php`:

```php
function news_load_all_posts(string $content_dir): array {
    $posts = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        [$fm, $body] = news_parse_mdfile($md);
        $fm['read_time'] = news_read_time($body);
        $posts[] = $fm;
    }
    // sort: featured first, then by date desc
    usort($posts, function ($a, $b) {
        $fa = !empty($a['featured']) ? 1 : 0;
        $fb = !empty($b['featured']) ? 1 : 0;
        if ($fa !== $fb) return $fb - $fa;
        return strcmp($b['date'], $a['date']);
    });
    return $posts;
}

function news_build_index(string $content_dir, string $out_dir): string {
    $posts = news_load_all_posts($content_dir);
    ob_start();
    ?>
<?php echo '<?php'; ?>

include_once __DIR__ . '/../include/base-head.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IPU News & Announcements — Latest Updates for 2026-27</title>
<meta name="description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<link rel="canonical" href="https://ipu.co.in/news/">
<meta name="robots" content="index, follow">
<link rel="stylesheet" href="/assets/css/bundle.css">
</head>
<body>
<?php echo '<?php'; ?> include __DIR__ . '/../include/components/navbar.php'; ?>

<main class="news-index">
  <header class="news-index__header">
    <h1>IPU News & Announcements</h1>
    <p>Latest updates on GGSIPU admissions, counselling, CET, and results.</p>
  </header>

  <nav class="news-categories">
    <a href="/news/">All</a>
    <?php foreach (news_categories() as $cat): ?>
    <a href="/news/?cat=<?php echo '<?= ' ?>strtolower('<?= $cat ?>') ?>"><?= $cat ?></a>
    <?php endforeach; ?>
  </nav>

  <div class="news-grid">
    <?php foreach ($posts as $post):
        if (empty($post['image'])) {
            $post['image'] = news_category_image($post['category'] ?? 'General');
        }
        // embed each post as an inline array literal for the card
        $post_export = var_export($post, true);
    ?>
    <?php echo '<?php'; ?> $post = <?= $post_export ?>; ?>
    <?php echo '<?php'; ?> include __DIR__ . '/../include/news-card.php'; ?>
    <?php endforeach; ?>
  </div>
</main>

<?php echo '<?php'; ?> include __DIR__ . '/../include/components/footer.php'; ?>
</body>
</html>
    <?php
    $content = ob_get_clean();
    $out_file = rtrim($out_dir, '/') . '/index.php';
    file_put_contents($out_file, $content);
    return $out_file;
}
```

**Note:** the index-builder uses PHP's ability to emit PHP-inside-HTML-inside-PHP, which is tricky. If tests flake on this, switch to a simpler string-concatenation approach:

```php
$out = "<?php include_once __DIR__ . '/../include/base-head.php'; ?>\n<!DOCTYPE html>...";
foreach ($posts as $post) {
    $out .= "<?php \$post = " . var_export($post, true) . "; include __DIR__ . '/../include/news-card.php'; ?>\n";
}
```

Prefer the string approach if output is fragile.

- [ ] **Step 5: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 6: Commit**

```bash
git add scripts/build-news.php scripts/tests/test_build.php scripts/tests/fixtures/sample-post-2.md
git commit -m "feat(news): add index page builder (featured-first sort)"
```

---

### Task 16: Build script — sitemap + llms.txt + main CLI (TDD)

**Files:**
- Modify: `scripts/build-news.php`
- Modify: `scripts/tests/test_build.php`

- [ ] **Step 1: Append failing tests**

Add to `scripts/tests/test_build.php`:

```php
$tmp3 = sys_get_temp_dir() . '/news_build_' . uniqid();
mkdir($tmp3 . '/content/news', 0755, true);
mkdir($tmp3 . '/website_download/news', 0755, true);
copy(__DIR__ . '/fixtures/sample-post.md', $tmp3 . '/content/news/sample-post.md');

// seed existing sitemap
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

exec("rm -rf " . escapeshellarg($tmp3));
```

- [ ] **Step 2: Run, expect failure**

Run: `php scripts/tests/run.php`.

- [ ] **Step 3: Implement sitemap + llms.txt updaters and main entry point**

Append to `scripts/build-news.php`:

```php
function news_update_sitemap(string $content_dir, string $sitemap_path): void {
    $posts = news_load_all_posts($content_dir);
    $xml = file_exists($sitemap_path) ? file_get_contents($sitemap_path) : '';
    if ($xml === '') {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n</urlset>\n";
    }

    // strip any previous news entries (idempotent)
    $xml = preg_replace('#\s*<url>\s*<loc>https://ipu\.co\.in/news/[^<]+</loc>.*?</url>#s', '', $xml);

    $new_entries = '';
    foreach ($posts as $p) {
        $loc = 'https://ipu.co.in/news/' . $p['slug'] . '.php';
        $lastmod = htmlspecialchars($p['date_modified'] ?? $p['date'], ENT_QUOTES);
        $new_entries .= "  <url>\n    <loc>" . htmlspecialchars($loc, ENT_QUOTES) . "</loc>\n    <lastmod>$lastmod</lastmod>\n    <changefreq>weekly</changefreq>\n  </url>\n";
    }
    // also include /news/ index
    $new_entries .= "  <url>\n    <loc>https://ipu.co.in/news/</loc>\n    <lastmod>" . date('Y-m-d') . "</lastmod>\n    <changefreq>daily</changefreq>\n  </url>\n";

    $xml = str_replace('</urlset>', $new_entries . '</urlset>', $xml);
    file_put_contents($sitemap_path, $xml);
}

function news_update_llms_txt(string $content_dir, string $llms_path): void {
    $posts = news_load_all_posts($content_dir);
    $existing = file_exists($llms_path) ? file_get_contents($llms_path) : "# IPU.co.in\n";

    // strip previous News section (idempotent)
    $existing = preg_replace('/\n## IPU News.*?(?=\n## |\z)/s', '', $existing);

    $section = "\n## IPU News\n\nLatest IPU-related news and announcements:\n\n";
    foreach ($posts as $p) {
        $url = 'https://ipu.co.in/news/' . $p['slug'] . '.php';
        $section .= "- [" . $p['title'] . "]($url) — " . $p['tldr'] . "\n";
    }

    file_put_contents($llms_path, rtrim($existing, "\n") . "\n" . $section);
}

function news_build_all(string $content_dir, string $web_dir): array {
    $posts_written = [];
    foreach (glob(rtrim($content_dir, '/') . '/*.md') as $md) {
        $posts_written[] = news_build_single_post($md, $web_dir . '/news/');
    }
    news_build_index($content_dir, $web_dir . '/news/');
    news_update_sitemap($content_dir, $web_dir . '/sitemap.xml');
    news_update_llms_txt($content_dir, $web_dir . '/llms.txt');
    return $posts_written;
}

// CLI entry
if (php_sapi_name() === 'cli' && realpath($argv[0]) === __FILE__) {
    $repo_root = dirname(__DIR__);
    $written = news_build_all($repo_root . '/content/news', $repo_root . '/website_download');
    echo "Built " . count($written) . " posts.\n";
    foreach ($written as $w) echo "  $w\n";
}
```

- [ ] **Step 4: Run, expect pass**

Run: `php scripts/tests/run.php`.

- [ ] **Step 5: End-to-end local run**

```bash
mkdir -p content/news
cp scripts/tests/fixtures/sample-post.md content/news/
cp scripts/tests/fixtures/sample-post-2.md content/news/
php scripts/build-news.php
```

Expected output: `Built 2 posts.` and the files exist:
- `website_download/news/round-2-counselling-schedule.php`
- `website_download/news/cet-result-date-confirmed.php`
- `website_download/news/index.php`
- `website_download/sitemap.xml` (contains both URLs)
- `website_download/llms.txt` (contains `## IPU News` section)

Spot-check by opening the generated `.php` files in a browser via `php -S localhost:8000 -t website_download/`:

```bash
php -S localhost:8000 -t website_download/ &
open http://localhost:8000/news/round-2-counselling-schedule.php
open http://localhost:8000/news/
```

Verify: page renders, has tldr box, has FAQ accordion, JSON-LD is present in head (`view-source:` and search for `"@type": "NewsArticle"`). Kill the server: `kill %1`.

- [ ] **Step 6: Create placeholder category images**

```bash
mkdir -p website_download/assets/images/news
for cat in counselling cet admissions results general urgent-banner; do
  # 1x1 placeholder JPG; replace with real images later
  printf '\xff\xd8\xff\xe0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xff\xdb\x00C\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\x09\x09\x08\x0a\x0c\x14\x0d\x0c\x0b\x0b\x0c\x19\x12\x13\x0f\x14\x1d\x1a\x1f\x1e\x1d\x1a\x1c\x1c $.' "\''"',#\x1c\x1c(7),01444\x1f\x27\x39=82<.342\xff\xc0\x00\x0b\x08\x00\x01\x00\x01\x01\x01\x11\x00\xff\xc4\x00\x1f\x00\x00\x01\x05\x01\x01\x01\x01\x01\x01\x00\x00\x00\x00\x00\x00\x00\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\xff\xc4\x00\xb5\x10\x00\x02\x01\x03\x03\x02\x04\x03\x05\x05\x04\x04\x00\x00\x01}\x01\x02\x03\x00\x04\x11\x05\x12!1A\x06\x13Qa\x07"q\x142\x81\x91\xa1\x08#B\xb1\xc1\x15R\xd1\xf0$3br\x82\x09\x0a\x16\x17\x18\x19\x1a%&' "'" "()*456789:CDEFGHIJSTUVWXYZcdefghijstuvwxyz\x83\x84\x85\x86\x87\x88\x89\x8a\x92\x93\x94\x95\x96\x97\x98\x99\x9a\xa2\xa3\xa4\xa5\xa6\xa7\xa8\xa9\xaa\xb2\xb3\xb4\xb5\xb6\xb7\xb8\xb9\xba\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xd2\xd3\xd4\xd5\xd6\xd7\xd8\xd9\xda\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8\xe9\xea\xf1\xf2\xf3\xf4\xf5\xf6\xf7\xf8\xf9\xfa\xff\xda\x00\x08\x01\x01\x00\x00?\x00\xfb\xd3\xff\xd9" > "website_download/assets/images/news/${cat}.jpg"
done
```

**Note:** these are 1x1 black JPEGs as placeholders. Replace with branded images before launch — image paths are stable so only the file content changes.

- [ ] **Step 7: Commit everything**

```bash
git add scripts/build-news.php scripts/tests/test_build.php content/news/ website_download/news/ website_download/sitemap.xml website_download/llms.txt website_download/assets/images/news/
git commit -m "feat(news): add sitemap + llms.txt updaters and main build CLI"
```

**Phase 1 complete.** You can now hand-author MD files in `content/news/` and run `php scripts/build-news.php` to generate the live site files.

---

## Phase 2: n8n Automation Workflows

n8n workflows are configured visually, not via TDD. For each workflow below, the task documents:
- Node-by-node configuration
- Required credentials
- Dry-run test steps
- Expected outputs

All workflow JSON exports are committed to `n8n/workflows/` for reproducibility — if you need to rebuild the n8n instance, they can be re-imported.

### Task 17: Connect to the shared IPU n8n instance + set up deployment tooling

**Revision on 2026-04-15:** the original "provision a new dedicated instance" direction was revised. The user supplied an API key for the existing shared n8n host at `https://n8n.srv1117424.hstgr.cloud` — the same server that runs KYNE, Casa Mazamiro, and ~58 other tenants' workflows. Rather than spin up a separate instance, IPU workflows live on this shared host, all prefixed with `IPU — ` so they're visually isolated and programmatically guarded.

**Files:**
- Create: `deployment/sync_workflow.py` — multi-workflow pull/push/diff/activate CLI (adapted from KYNE's pattern)
- Create: `deployment/README.md`
- Create: `deployment/workflows/.gitkeep`
- Modify: `.gitignore` — ignore `deployment/backups/`
- Modify: project-root `.env` — append `N8N_BASE_URL` + `N8N_API_KEY`

- [ ] **Step 1: Store creds**

Append to `/Users/Sumit/test-project/.env`:

```
N8N_BASE_URL=https://n8n.srv1117424.hstgr.cloud
N8N_API_KEY=<JWT from n8n Settings → API>
```

`.env` is already gitignored. The JWT expires 2026-07-13; regenerate in the n8n UI before then.

- [ ] **Step 2: Verify connectivity**

```bash
curl -sS -H "X-N8N-API-KEY: $N8N_API_KEY" "$N8N_BASE_URL/api/v1/workflows?limit=1"
```

Expect HTTP 200 and a JSON payload with a workflow in `data[0]`.

- [ ] **Step 3: Build `deployment/sync_workflow.py`**

Multi-workflow CLI: `list | pull <id> | push <file> | diff <file> | activate <file> | deactivate <file> | reload <file>`. Enforces `IPU — ` name prefix on every write operation — refuses to touch other tenants' workflows.

(See committed `deployment/sync_workflow.py` for full source.)

- [ ] **Step 4: Smoke-test**

```bash
./deployment/sync_workflow.py list
```

Expect to see all ~58 workflows on the server; IPU ones (none yet at this stage) would have an `[IPU ]` marker.

- [ ] **Step 5: Document in `deployment/README.md`**

Contents:
- Multi-tenant warning: NEVER touch non-IPU workflows
- Naming rule: every IPU workflow name starts with `IPU — ` (em-dash, not hyphen)
- Credential location: `../.env`
- Commands reference
- Webhook-reload gotcha (n8n caches compiled webhook handlers in-memory; always `reload` after pushing changes to webhook-node params)

- [ ] **Step 6: Credentials to create inside n8n's credential store (NOT in `.env`)**

In the n8n UI, under Credentials:
- Anthropic API key (for Claude rewrite nodes in Task 18)
- GitHub PAT — repo scope on the ipu.co.in repo (for MD commits and webhook listeners)
- Slack bot token — scopes `chat:write`, `im:write`, `commands` (for Task 18+21 digest and modals)
- FTP — cPanel host/user/pass (for deploys in Task 18+19+20)

Each credential gets a stable name; workflows reference it by name, so changing the underlying secret later doesn't require workflow edits.

- [ ] **Step 7: Commit**

```bash
mkdir -p deployment/workflows deployment/backups
touch deployment/workflows/.gitkeep
git add deployment/sync_workflow.py deployment/README.md deployment/workflows/.gitkeep .gitignore
git commit -m "feat(news): add n8n deployment tooling — multi-workflow sync CLI + IPU-only guardrails"
```

---

**Note on Tasks 18–22:** the original plan referenced `n8n/workflows/` and `n8n/prompts/` — since the revision, workflow JSON files live at `deployment/workflows/<name>.json` and Claude system prompts at `deployment/prompts/<name>.md`. Keep that path substitution in mind while executing 18–22.

### Task 18: n8n workflow — daily scraper + rewriter + builder + deployer

**Files:**
- Create: `n8n/workflows/daily-scraper.json` (export after creation in UI)
- Create: `n8n/prompts/news-rewrite-system.md` (the Claude system prompt)

- [ ] **Step 1: Draft Claude system prompt**

Create `n8n/prompts/news-rewrite-system.md`:

```markdown
You are a content editor for ipu.co.in, a guidance site for prospective students of GGSIPU (Guru Gobind Singh Indraprastha University, Delhi).

Your job: given a raw news item from an official IPU source (notification text, RSS item, or announcement), produce a JSON object matching our news post schema.

## Rules

1. **Factual-only.** Use ONLY facts present in the source. Do not infer dates, names, or numbers. If the source is ambiguous, say "refer to the official notification" rather than guess.
2. **Never paraphrase editorial phrasing.** Rewrite structure and voice entirely — never copy phrasing from news sites.
3. **Target audience:** prospective IPU students and parents. Direct, informative, no marketing fluff.
4. **Semantic structure:** body uses `## H2` for major sections, `### H3` for sub-sections. No `# H1` (the template adds it).
5. **Internal links:** include 2–4 markdown links to relevant existing ipu.co.in pages. Allowed URL patterns (use only these):
   - `/mba-admission-ip-university.php`
   - `/ipu-cet-cutoff-2025.php`
   - `/GGSIPU-counselling-for-B-Tech-admission.php`
   - `/ipu-admission-guide.php`
   - `/IPU-B-Tech-admission-2025.php`
   - `/mca-admission-ipu.php`
   - `/bba-admission-ipu.php`
   - (If none are clearly relevant, include zero rather than a forced link.)
6. **Category:** pick one of `Counselling`, `CET`, `Admissions`, `Results`, `General`.
7. **is_urgent:** true only when the item announces a deadline within 7 days or a same-day-actionable event.
8. **tldr:** one sentence, ≤ 160 characters.
9. **FAQ:** 2–4 Q/A pairs derived from the article. Each answer ≤ 2 sentences.
10. **Relevance gate:** if the input is NOT about IPU/GGSIPU admissions/exams/counselling, return exactly `{"skip": true, "reason": "..."}`.

## Output format

Return ONLY JSON (no surrounding prose, no code fences):

{
  "title": "...",
  "slug": "...",
  "date": "YYYY-MM-DD (today's date)",
  "date_modified": "YYYY-MM-DD (same as date)",
  "category": "...",
  "tags": ["...", "..."],
  "featured": false,
  "is_urgent": false,
  "tldr": "...",
  "faq": [{"q": "...", "a": "..."}, ...],
  "body_md": "## H2\n\nMarkdown body..."
}
```

- [ ] **Step 2: Build workflow in n8n UI**

Nodes (left to right):

1. **Schedule Trigger** — cron `30 2 * * *` (02:30 UTC = 08:00 IST daily)
2. **HTTP Request: ipu.ac.in notifications** — GET `https://www.ipu.ac.in/notifications`, extract via HTML parser node (or regex node) to list of `{title, url, date}`
3. **HTTP Request: ipuadmissions.nic.in** — same pattern
4. **RSS Read: Google News "IPU admission"** — `https://news.google.com/rss/search?q=IPU+admission&hl=en-IN&gl=IN&ceid=IN:en`
5. **RSS Read: Google News "GGSIPU counselling"** — similar
6. **Merge** — combine all sources into one list; normalize each item to `{source_url, raw_text, date}`
7. **Code: Dedup filter** — read `content/news/_state.json` from GitHub (HTTP GET raw file), filter out items whose URL SHA-256 is already in `seen`. Output filtered list.
8. **Split in Batches** — process each remaining item one-by-one
9. **Anthropic: Claude rewrite** — use system prompt from `n8n/prompts/news-rewrite-system.md`; user message = the raw source text. Model: `claude-sonnet-4-6`. Max tokens: 4000.
10. **Code: Parse JSON + skip-gate** — if output has `skip: true`, end this branch. Else pass `{frontmatter, body_md}` forward.
11. **Code: Build MD file contents** — produce `{path: "content/news/<slug>.md", content: "<json>\n---\n<body_md>"}`
12. **GitHub: Create/update file** — commit to `main` branch with message `feat(news): auto-publish <slug>`
13. **Execute Workflow: rebuild-single** — call the rebuild-single workflow (Task 19) with slug param. This regenerates the `.php` and FTPs it.
14. **Collect outputs** — aggregate all published slugs
15. **Slack: Send DM digest** — format per spec Section 10, post to user's DM channel with inline buttons (each button sends a payload the respective webhook workflows can process)
16. **Code: Update _state.json** — append SHA-256 hashes of processed URLs
17. **GitHub: Commit _state.json** — message: `chore(news): update dedup state`

- [ ] **Step 3: Test in n8n's manual-run mode**

- Trigger the workflow manually in n8n UI.
- Watch node-by-node execution; inspect outputs at each step.
- Fix any source-parsing issues (HTML selectors are brittle — likely the first failure point).
- Verify Claude returns valid JSON for 3+ test items.

- [ ] **Step 4: Export workflow JSON**

In n8n UI: Workflow menu → Download. Save to `n8n/workflows/daily-scraper.json`.

- [ ] **Step 5: Commit**

```bash
git add n8n/workflows/daily-scraper.json n8n/prompts/news-rewrite-system.md
git commit -m "feat(news): add n8n daily scraper workflow + Claude rewrite prompt"
```

---

### Task 19: n8n workflow — rebuild-single (triggered after MD commit)

Invoked by: (a) the daily scraper after committing a new MD, (b) a GitHub webhook on pushes that touch `content/news/`, (c) the suggest-edit workflow (Task 21).

**Files:**
- Create: `n8n/workflows/rebuild-single.json`

- [ ] **Step 1: Build workflow**

Nodes:

1. **Webhook Trigger** — POST `/webhook/rebuild-single` with body `{slug: "..."}`
2. **GitHub: Pull latest** — clone/pull the repo on n8n's local filesystem (or use GitHub API to fetch the single MD file)
3. **Execute Command** — run `php scripts/build-news.php --slug=<slug>` (requires minor CLI arg support; add in Task 16 follow-up if needed — or just run full build which is fast)
4. **FTP: Upload** — PUT the new `.php` file + updated `index.php` + `sitemap.xml` + `llms.txt` to cPanel
5. **Respond to Webhook** — `{ok: true, slug: "..."}`

Note: if running n8n without a local repo checkout (SaaS), adjust: use GitHub API to read MD, run build via a serverless function or local helper, then FTP. Simpler if n8n is self-hosted alongside a repo checkout.

- [ ] **Step 2: Test**

Call the webhook manually with a known slug:

```bash
curl -X POST https://ipu-n8n/webhook/rebuild-single -d '{"slug":"round-2-counselling-schedule"}'
```

Verify: the file on cPanel is updated within ~30 seconds.

- [ ] **Step 3: Export + commit**

```bash
git add n8n/workflows/rebuild-single.json
git commit -m "feat(news): add n8n rebuild-single webhook workflow"
```

---

### Task 20: n8n workflow — unpublish-by-slug

**Files:**
- Create: `n8n/workflows/unpublish-by-slug.json`

- [ ] **Step 1: Build workflow**

Nodes:

1. **Webhook Trigger** — POST `/webhook/unpublish-by-slug` with `{slug: "...", reason: "optional"}`
2. **GitHub: Delete file** — remove `content/news/<slug>.md`
3. **Execute Command** — run `php scripts/build-news.php` to regenerate index, sitemap, llms.txt without the removed post
4. **FTP: Delete** — remove `/public_html/news/<slug>.php` on cPanel
5. **FTP: Upload** — updated `index.php`, `sitemap.xml`, `llms.txt`
6. **Slack: DM confirmation** — `✅ Unpublished: <slug>`

- [ ] **Step 2: Test**

Create a test post, call the webhook, verify:
- MD file gone from repo
- PHP file gone from cPanel
- Sitemap no longer contains the URL
- Slack confirmation received

- [ ] **Step 3: Export + commit**

```bash
git add n8n/workflows/unpublish-by-slug.json
git commit -m "feat(news): add n8n unpublish-by-slug webhook workflow"
```

---

### Task 21: n8n workflow — suggest-edit (Slack modal → Claude → rebuild)

**Files:**
- Create: `n8n/workflows/suggest-edit.json`
- Create: `n8n/prompts/suggest-edit-system.md`

- [ ] **Step 1: Draft Claude system prompt**

Create `n8n/prompts/suggest-edit-system.md`:

```markdown
You are editing a published news post on ipu.co.in.

Inputs:
- CURRENT_MD: the current MD file contents (JSON frontmatter + markdown body)
- USER_INSTRUCTION: a plain-English change request

Your job:
1. Apply the change as precisely as described. Do not rewrite unchanged sections.
2. If USER_INSTRUCTION is ambiguous, pick the most conservative interpretation and note it in a `clarification_needed` field.
3. Always update `date_modified` to today's date.
4. Preserve slug, date (original), category (unless instruction explicitly changes them).
5. Output ONLY the full updated MD file content — JSON frontmatter, `---`, markdown body.

Never add explanatory prose outside the MD file itself.
```

- [ ] **Step 2: Build workflow**

Nodes:

1. **Slack Trigger: interactive message / modal submission** — fires when user submits "Suggest edit" modal
2. **Code: Extract** — from payload get `{slug, instruction}`
3. **GitHub: Get file** — read `content/news/<slug>.md`
4. **Anthropic: Claude** — system prompt above; user message = `CURRENT_MD:\n<contents>\n\nUSER_INSTRUCTION: <instruction>`
5. **Code: Diff** — produce a unified diff between old and new MD
6. **GitHub: Update file** — commit new MD with message `edit(news): <slug> — per Slack suggestion`
7. **Execute Workflow: rebuild-single** — for this slug
8. **Slack: Reply in thread** — `✅ Updated. Diff:\n\`\`\`<diff>\`\`\``

- [ ] **Step 3: Test**

In Slack, use the "Suggest edit" button on a recent post → type "Change the counselling start date to April 25" → verify:
- A commit lands on `main` with updated MD
- The live page reflects the change within ~30s
- A thread reply shows the diff

- [ ] **Step 4: Export + commit**

```bash
git add n8n/workflows/suggest-edit.json n8n/prompts/suggest-edit-system.md
git commit -m "feat(news): add n8n suggest-edit workflow (Slack modal → Claude → rebuild)"
```

---

### Task 22: n8n workflow — weekly summary

**Files:**
- Create: `n8n/workflows/weekly-summary.json`

- [ ] **Step 1: Build workflow**

Nodes:

1. **Schedule Trigger** — `0 4 * * 0` (Sunday 04:00 UTC)
2. **GitHub: Get commits** — since last Sunday, filter to `content/news/*.md` paths
3. **Code: Aggregate** — count new posts, category breakdown, detect `edit(news):` and `chore(news):` commits, count unpublished
4. **Slack: DM summary** — format: `📊 Weekly IPU news summary — <date range>\n<stats>`

- [ ] **Step 2: Test**

Trigger manually. Verify Slack DM arrives with correct counts.

- [ ] **Step 3: Export + commit**

```bash
git add n8n/workflows/weekly-summary.json
git commit -m "feat(news): add n8n weekly summary workflow"
```

---

## Phase 3: Deploy + End-to-End Smoke

### Task 23: Python FTP fallback script for news

**Files:**
- Create: `upload_news.py`

- [ ] **Step 1: Implement**

Create `upload_news.py`:

```python
#!/usr/bin/env python3
"""Manual FTP upload for the news module. Mirror of what n8n does automatically.

Usage:
    python3 upload_news.py         # upload everything under website_download/news/ + sitemap + llms.txt
    python3 upload_news.py <slug>  # upload a single post + index + sitemap + llms.txt
"""
import ftplib
import os
import sys
from pathlib import Path

FTP_HOST = os.environ["IPU_FTP_HOST"]
FTP_USER = os.environ["IPU_FTP_USER"]
FTP_PASS = os.environ["IPU_FTP_PASS"]
REMOTE_ROOT = "/public_html"

HERE = Path(__file__).parent
WEB = HERE / "website_download"


def upload_file(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)
    print(f"  ✓ {remote}")


def ensure_remote_dir(ftp: ftplib.FTP, path: str) -> None:
    parts = [p for p in path.split("/") if p]
    cwd = ""
    for p in parts:
        cwd += "/" + p
        try:
            ftp.cwd(cwd)
        except ftplib.error_perm:
            ftp.mkd(cwd)


def main() -> None:
    slug = sys.argv[1] if len(sys.argv) > 1 else None

    ftp = ftplib.FTP(FTP_HOST, FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    print(f"Connected to {FTP_HOST}")

    ensure_remote_dir(ftp, f"{REMOTE_ROOT}/news")
    ensure_remote_dir(ftp, f"{REMOTE_ROOT}/assets/images/news")

    if slug:
        files = [
            (WEB / f"news/{slug}.php", f"{REMOTE_ROOT}/news/{slug}.php"),
            (WEB / "news/index.php", f"{REMOTE_ROOT}/news/index.php"),
            (WEB / "sitemap.xml", f"{REMOTE_ROOT}/sitemap.xml"),
            (WEB / "llms.txt", f"{REMOTE_ROOT}/llms.txt"),
        ]
    else:
        files = []
        for php in (WEB / "news").glob("*.php"):
            files.append((php, f"{REMOTE_ROOT}/news/{php.name}"))
        for img in (WEB / "assets/images/news").glob("*.jpg"):
            files.append((img, f"{REMOTE_ROOT}/assets/images/news/{img.name}"))
        files.append((WEB / "sitemap.xml", f"{REMOTE_ROOT}/sitemap.xml"))
        files.append((WEB / "llms.txt", f"{REMOTE_ROOT}/llms.txt"))
        files.append((WEB / "include/news-template.php", f"{REMOTE_ROOT}/include/news-template.php"))
        files.append((WEB / "include/news-card.php", f"{REMOTE_ROOT}/include/news-card.php"))
        files.append((WEB / "include/news-jsonld.php", f"{REMOTE_ROOT}/include/news-jsonld.php"))
        files.append((WEB / "include/news-helpers.php", f"{REMOTE_ROOT}/include/news-helpers.php"))

    for local, remote in files:
        if local.exists():
            upload_file(ftp, local, remote)
        else:
            print(f"  ⊘ skipped (missing): {local}")

    ftp.quit()
    print("Done.")


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Add to .gitignore for creds**

Verify `.gitignore` doesn't accidentally commit env files. Add a line `.env` if missing.

- [ ] **Step 3: Commit**

```bash
git add upload_news.py
git commit -m "feat(news): add Python FTP fallback uploader for manual deploys"
```

---

### Task 24: Staging smoke test

Goal: prove the full system works against `/news-staging/` on cPanel before going live at `/news/`.

- [ ] **Step 1: Create staging folder on cPanel**

Via cPanel File Manager or FTP: create `/public_html/news-staging/`.

- [ ] **Step 2: Point build temporarily at staging**

Edit `scripts/build-news.php` locally to use `/news-staging/` URL prefix (in canonical URL, sitemap entries, and output dir), OR better, add a `--env=staging` flag and make the build parameterize the URL.

If parameterizing is too much for a smoke test, just do a manual search-replace on the generated files.

- [ ] **Step 3: Build + FTP**

```bash
IPU_FTP_HOST=... IPU_FTP_USER=... IPU_FTP_PASS=... python3 upload_news.py
```

(With staging URL override in place.)

- [ ] **Step 4: Verify in browser**

Open `https://ipu.co.in/news-staging/round-2-counselling-schedule.php`. Check:
- Page renders without PHP errors
- Images load (even if placeholder)
- Breadcrumbs work
- FAQ accordion expands

- [ ] **Step 5: Validate schema with Google Rich Results Test**

Open https://search.google.com/test/rich-results → paste staging URL → confirm:
- NewsArticle detected with no errors
- FAQPage detected with no errors
- Breadcrumb detected with no errors

- [ ] **Step 6: Validate with Mobile-Friendly Test**

https://search.google.com/test/mobile-friendly → paste URL → confirm mobile-friendly.

- [ ] **Step 7: Validate llms.txt addition**

Fetch `https://ipu.co.in/llms.txt` → confirm `## IPU News` section appears with the staging post.

- [ ] **Step 8: Trigger n8n daily scraper in dry-run**

In n8n UI, disable the FTP node, run the workflow manually. Inspect:
- Which source items were captured
- What Claude produced
- That dedup correctly skipped items

Fix any prompt issues.

- [ ] **Step 9: Trigger Slack digest**

Force the Slack DM send. Confirm message arrives with all 4 buttons. Tap each button and verify it does the right thing (Read works; Edit opens GitHub; Unpublish removes; Suggest-edit opens modal).

- [ ] **Step 10: Cleanup staging**

Once satisfied, remove staging files:

```bash
python3 -c "import ftplib; f=ftplib.FTP(...); f.rmd('/public_html/news-staging')"
```

Revert the URL override in `scripts/build-news.php`. Commit:

```bash
git add scripts/build-news.php
git commit -m "chore: revert staging URL override after smoke test passed"
```

---

### Task 25: Cutover to production

- [ ] **Step 1: Replace placeholder category images**

Replace the five 1x1 JPEGs in `website_download/assets/images/news/` with real branded images (1200×630 recommended for OG sharing). FTP them with:

```bash
python3 upload_news.py
```

- [ ] **Step 2: Enable daily scraper**

In n8n UI: activate the daily scraper workflow.

- [ ] **Step 3: Add /news/ link to main nav**

Edit `website_download/include/components/navbar.php` — add a "News" link pointing to `/news/`. Commit and FTP.

- [ ] **Step 4: Submit sitemap to Google Search Console**

In GSC: Sitemaps → (existing sitemap already registered) → request re-crawl. Within 48h the new `/news/*` URLs should start appearing in GSC.

- [ ] **Step 5: First-week monitoring**

Daily for one week:
- Confirm Slack digest arrives
- Spot-check 1 random post for factual accuracy
- Watch GSC for errors on new URLs
- Watch n8n execution log for failures

At end of week, write a short "week 1 report" as a commit message body on a trivial commit (e.g., a date bump in `llms.txt`): how many posts, how many edits, any issues, any prompt tuning applied.

**Implementation complete.**

---

## Appendix: Open Questions to Resolve During Execution

From spec Section 15 — confirm at the start of Phase 2:

- Exact cron time (default: `30 2 * * *` UTC = 08:00 IST)
- Slack workspace + user ID for the DM target
- Whether n8n can reach cPanel over FTP directly, or needs an SSH tunnel
- Hosting decision for IPU n8n instance (self-hosted alongside KYNE vs fresh VPS vs cloud)
- Expand the internal-link whitelist in `news-rewrite-system.md` — which existing ipu.co.in URLs can AI link to? (Current draft lists 7; spec author to review and add/remove.)
