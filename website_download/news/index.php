<?php include_once __DIR__ . '/../include/base-head.php'; ?>
<title>IPU News &amp; Announcements — Latest Updates for 2026-27</title>
<meta name="description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<link rel="canonical" href="https://ipu.co.in/news/">
<meta name="robots" content="index, follow">
<body>
<?php include_once __DIR__ . '/../include/base-nav.php'; ?>
<main class="news-index">
  <header class="news-index__header"><h1>IPU News &amp; Announcements</h1><p>Latest updates on GGSIPU admissions, counselling, CET, and results.</p></header>
  <nav class="news-categories"><a href="/news/">All</a><a href="/news/?cat=counselling">Counselling</a><a href="/news/?cat=cet">CET</a><a href="/news/?cat=admissions">Admissions</a><a href="/news/?cat=results">Results</a><a href="/news/?cat=general">General</a></nav>
  <div class="news-grid">
    <?php $post = array (
  'title' => 'IPU News & Announcements — New Section Launched',
  'slug' => 'welcome-news-launched',
  'date' => '2026-04-15',
  'date_modified' => '2026-04-15',
  'category' => 'General',
  'tags' => 
  array (
    0 => 'announcement',
  ),
  'featured' => true,
  'is_urgent' => false,
  'image' => 'assets/images/news/general.jpg',
  'tldr' => 'We\'ve launched a dedicated section for IPU admission news, counselling schedules, CET updates, and results. Real updates begin here shortly, sourced directly from official IPU channels.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What will I find here?',
      'a' => 'Timely updates on GGSIPU admissions, counselling rounds, CET schedules, results, and official notifications — sourced directly from ipu.ac.in and ipuadmissions.nic.in.',
    ),
    1 => 
    array (
      'q' => 'How often is this updated?',
      'a' => 'Daily. An automated pipeline monitors official IPU sources every morning and publishes new updates within hours.',
    ),
  ),
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
  </div>
</main>
<?php include_once __DIR__ . '/../include/base-footer.php'; ?>
</body>
</html>
