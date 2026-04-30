<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../include/base-head.php';
include_once __DIR__ . '/../include/form-handler.php';
?>
<title>IPU News &amp; Announcements — Latest Updates for 2026-27</title>
<meta name="description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<link rel="canonical" href="https://ipu.co.in/news/">
<meta name="robots" content="index, follow">
<meta property="og:title" content="IPU News &amp; Announcements — Latest Updates for 2026-27">
<meta property="og:description" content="Latest news and announcements from GGSIPU — counselling schedules, CET updates, admission notifications, results.">
<meta property="og:url" content="https://ipu.co.in/news/">
<meta property="og:type" content="website">
<style>
/* ============ NEWS PAGE — MATCHES BLOG STYLING ============ */
.news-hero { position: relative; padding: 120px 0 60px; text-align: center; color: #fff; overflow: hidden; -webkit-clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%); clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%); }
.news-hero::before { content: ''; position: absolute; inset: 0; background-color: #0b2c5d; opacity: 0.85; z-index: 0; }
.news-hero::after { content: ''; position: absolute; inset: 0; background-image: url(/assets/images/banner-bg-2.jpg); background-size: cover; background-position: center; z-index: -1; }
.news-hero .container { position: relative; z-index: 1; }
.news-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; color: #fff; }
.news-hero p { font-size: 1.05rem; opacity: .88; color: #fff; }
@media (max-width: 767px) { .news-hero { padding: 90px 0 50px; -webkit-clip-path: none; clip-path: none; } }
.breadcrumb-wrap { background: #f0f4ff; padding: 10px 0; font-size: .85rem; border-bottom: 1px solid #dce6ff; }
.breadcrumb-wrap a { color: #1a4a9f; text-decoration: none; }
.breadcrumb-wrap a:hover { text-decoration: underline; }
.breadcrumb-wrap span { color: #666; }
.cat-filter { padding: 22px 0 10px; }
.cat-filter .btn-cat { display: inline-block; padding: 7px 18px; margin: 4px 4px; border-radius: 25px; border: 2px solid #1a4a9f; color: #1a4a9f; background: #fff; font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .2s; text-transform: uppercase; letter-spacing: .4px; }
.cat-filter .btn-cat:hover, .cat-filter .btn-cat.active { background: #1a4a9f; color: #fff; }
.blog-card-wrap { background: #fff; border-radius: 12px; box-shadow: 0 2px 14px rgba(0,0,0,.08); overflow: hidden; transition: transform .2s, box-shadow .2s; height: 100%; display: flex; flex-direction: column; }
.blog-card-wrap:hover { transform: translateY(-4px); box-shadow: 0 8px 28px rgba(26,74,159,.18); }
.blog-card-wrap img { width: 100%; height: 185px; object-fit: cover; }
.blog-card-body { padding: 16px 18px 18px; flex: 1; display: flex; flex-direction: column; }
.blog-cat-tag { display: inline-block; background: #e8effe; color: #1a4a9f; font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .5px; }
.blog-card-title { font-size: .97rem; font-weight: 700; color: #1a2d6b; line-height: 1.4; margin-bottom: 8px; text-decoration: none; display: block; }
.blog-card-title:hover { color: #e65c00; }
.blog-excerpt { font-size: .82rem; color: #555; line-height: 1.5; flex: 1; margin-bottom: 12px; }
.blog-meta { display: flex; justify-content: space-between; align-items: center; font-size: .78rem; color: #888; border-top: 1px solid #f0f0f0; padding-top: 10px; margin-top: auto; }
.blog-read-more { color: #1a4a9f; font-weight: 700; font-size: .82rem; text-decoration: none; }
.blog-read-more:hover { color: #e65c00; }
.urgent-badge { display: inline-block; background: #dc2626; color: #fff; font-size: .68rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; margin-left: 6px; text-transform: uppercase; letter-spacing: .4px; }
.mid-cta-strip { background: linear-gradient(90deg, #e65c00 0%, #f5820a 100%); border-radius: 12px; padding: 22px 28px; margin: 10px 0 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.mid-cta-strip .cta-text { color: #fff; }
.mid-cta-strip .cta-text h3 { font-size: 1.15rem; font-weight: 800; margin: 0 0 4px; }
.mid-cta-strip .cta-text p { font-size: .88rem; margin: 0; opacity: .9; }
.mid-cta-strip .cta-btn { background: #fff; color: #e65c00; font-weight: 800; font-size: 1rem; padding: 11px 24px; border-radius: 30px; text-decoration: none; white-space: nowrap; transition: background .2s; }
.mid-cta-strip .cta-btn:hover { background: #fff3e8; }
.blog-search-wrap { position: relative; margin-bottom: 8px; }
.blog-search-wrap input { width: 100%; padding: 10px 40px 10px 15px; border-radius: 25px; border: 2px solid #dde3f5; font-size: .9rem; }
.blog-search-wrap input:focus { border-color: #1a4a9f; outline: none; }
.blog-search-wrap .search-icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #999; }
.blog-item.hidden { display: none !important; }
.no-results-msg { display: none; text-align: center; color: #888; padding: 30px; font-size: .95rem; width: 100%; }
</style>
<body>

<?php include_once __DIR__ . '/../include/base-nav.php'; ?>

<section class="news-hero">
  <div class="container">
    <h1>IPU News &amp; Announcements</h1>
    <p>Latest updates on GGSIPU admissions, counselling, CET &amp; results</p>
  </div>
</section>

<nav class="breadcrumb-wrap" aria-label="breadcrumb">
  <div class="container">
    <a href="/">Home</a> &rsaquo; <span>News</span>
  </div>
</nav>

<section class="py-4">
<div class="container">

  <div class="row mb-2">
    <div class="col-lg-9 col-md-12">
      <div class="blog-search-wrap">
        <input type="text" id="newsSearch" placeholder="Search news… e.g. CET, counselling, results" autocomplete="off">
        <span class="search-icon">&#128269;</span>
      </div>
    </div>
  </div>

  <div class="cat-filter mb-3">
        <button class="btn-cat active" data-cat="All">All</button>
        <button class="btn-cat" data-cat="Counselling">Counselling</button>
        <button class="btn-cat" data-cat="CET">CET</button>
        <button class="btn-cat" data-cat="Admissions">Admissions</button>
        <button class="btn-cat" data-cat="Results">Results</button>
        <button class="btn-cat" data-cat="General">General</button>
  </div>

  <div class="row">
    <div class="col-lg-9 col-md-12 order-2 order-lg-1">
      <div class="row" id="newsGrid">
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu b.tech lateral entry seat intake released (cet 128 &amp; 129)">
          <?php $post = array (
  'title' => 'IPU B.Tech Lateral Entry Seat Intake Released (CET 128 & 129)',
  'slug' => 'ipu-btech-lateral-entry-seat-intake-released',
  'date' => '2026-04-25',
  'date_modified' => '2026-04-25',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'B.Tech Lateral Entry',
    1 => 'CET 128',
    2 => 'CET 129',
    3 => 'Seat Matrix',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has released the official seat intake for B.Tech Lateral Entry for Diploma holders and B.Sc graduates, including Management Quota seats.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programs are included in this seat intake notice?',
      'a' => 'The notice details the intake for B.Tech Lateral Entry for Diploma holders (CET Code 128) and B.Sc graduates (CET Code 129).',
    ),
    1 => 
    array (
      'q' => 'Does the seat matrix include the Management Quota?',
      'a' => 'Yes, the allocation includes seats for the Management Quota (MQ) for the current academic session.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="CET" data-title="ipu combines cet for allied health science programs (codes 119 and 124)">
          <?php $post = array (
  'title' => 'IPU Combines CET for Allied Health Science Programs (Codes 119 and 124)',
  'slug' => 'ipu-cet-clubbing-allied-health-sciences',
  'date' => '2026-04-24',
  'date_modified' => '2026-04-24',
  'category' => 'CET',
  'tags' => 
  array (
    0 => 'CET',
    1 => 'Allied Health Sciences',
    2 => 'BPT',
    3 => 'BOT',
    4 => 'BASLP',
    5 => 'BPO',
    6 => 'BMLS',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has merged the Common Entrance Tests for B.PT, B.OT, BASLP, BPO, and B.MLS into a single examination for the current academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programs are affected by the CET clubbing?',
      'a' => 'The programs include B.PT, B.OT (formerly Code 119) and BASLP, BPO, and B.MLS (formerly Code 124).',
    ),
    1 => 
    array (
      'q' => 'Will there be separate exams for Code 119 and Code 124?',
      'a' => 'No, the university has announced that these tests will now be clubbed into a single entrance examination.',
    ),
  ),
  'image' => 'assets/images/news/cet.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu updates b.pt and b.ot admission criteria for 2026-27 session">
          <?php $post = array (
  'title' => 'IPU Updates B.PT and B.OT Admission Criteria for 2026-27 Session',
  'slug' => 'ipu-bpt-bot-nomenclature-admission-changes',
  'date' => '2026-04-22',
  'date_modified' => '2026-04-22',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'B.PT',
    1 => 'B.OT',
    2 => 'GGSIPU Admissions',
    3 => 'Paramedical',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has announced changes to the nomenclature and admission criteria for B.PT and B.OT programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which courses are affected by the new GGSIPU notice?',
      'a' => 'The notice specifically concerns the Bachelor of Physiotherapy (B.PT) and Bachelor of Occupational Therapy (B.OT) programs.',
    ),
    1 => 
    array (
      'q' => 'When do these changes come into effect?',
      'a' => 'The changes in nomenclature and admission criteria are applicable for the 2026-27 academic session.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu extends application deadline for national level test and merit admissions">
          <?php $post = array (
  'title' => 'IPU Extends Application Deadline for National Level Test and Merit Admissions',
  'slug' => 'ipu-application-deadline-extension-national-level-test',
  'date' => '2026-04-17',
  'date_modified' => '2026-04-17',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'IPU Admission 2026',
    1 => 'Application Extension',
    2 => 'National Level Test',
    3 => 'Merit Admission',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has extended the deadline for online applications for courses based on National Level Tests and merit for the 2026-27 academic year.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which admissions are covered by this extension?',
      'a' => 'The extension applies to online application forms for National Level Test and merit-based admissions for the 2026-27 session.',
    ),
    1 => 
    array (
      'q' => 'How can I verify the new deadline?',
      'a' => 'Candidates should refer to the official notification at ipu.ac.in for the exact closing date and time.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="CET" data-title="final opportunity for ipu cet 2026 registration announced">
          <?php $post = array (
  'title' => 'Final Opportunity for IPU CET 2026 Registration Announced',
  'slug' => 'final-opportunity-ipu-cet-registration',
  'date' => '2026-04-15',
  'date_modified' => '2026-04-15',
  'category' => 'CET',
  'tags' => 
  array (
    0 => 'IPU CET',
    1 => 'Registration',
    2 => 'GGSIPU Admissions',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has announced a final registration window for the Common Entrance Test (CET) 2026, marking the last chance for candidates to apply for admissions.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Is this the final deadline for IPU CET registration?',
      'a' => 'Yes, the university has officially stated that this notification represents the last and final opportunity for candidates to register.',
    ),
    1 => 
    array (
      'q' => 'Where can I complete the registration process?',
      'a' => 'Candidates must visit the official GGSIPU admissions portal to fill out the application form and pay the registration fee.',
    ),
  ),
  'image' => 'assets/images/news/cet.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="General" data-title="ipu news &amp; announcements — new section launched">
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
        <div class="col-12 mid-cta-strip-col">
          <div class="mid-cta-strip">
            <div class="cta-text">
              <h3>&#128222; Confused About IPU Admission 2026?</h3>
              <p>Talk to our expert right now — Free guidance, no charges, instant answers.</p>
            </div>
            <a href="tel:9899991342" class="cta-btn">📞 Call Free: 9899991342</a>
          </div>
        </div>
        <div class="no-results-msg" id="noResults">No news found. Try a different search or category.</div>
      </div>
    </div>

    <div class="col-lg-3 col-md-12 order-1 order-lg-2 mb-4">
      <div style="position:sticky;top:80px">
        <?php include_once __DIR__ . '/../include/sidebar-cta.php'; ?>
        <?php include_once __DIR__ . '/../include/news-popular-blogs.php'; ?>
      </div>
    </div>
  </div>

</div>
</section>

<?php include_once __DIR__ . '/../include/base-footer.php'; ?>

<script>
const filterBtns = document.querySelectorAll('.btn-cat');
const newsItems = document.querySelectorAll('.blog-item');
const noResults = document.getElementById('noResults');
const searchInput = document.getElementById('newsSearch');

let activeCategory = 'All';
let searchQuery = '';

function applyFilters() {
  let visibleCount = 0;
  newsItems.forEach(item => {
    const cat = item.dataset.category;
    const title = item.dataset.title || '';
    const matchesCat = activeCategory === 'All' || cat === activeCategory;
    const matchesSearch = searchQuery === '' || title.includes(searchQuery.toLowerCase());
    if (matchesCat && matchesSearch) {
      item.classList.remove('hidden');
      visibleCount++;
    } else {
      item.classList.add('hidden');
    }
  });
  noResults.style.display = visibleCount === 0 ? 'block' : 'none';
  const midCTA = document.querySelector('.mid-cta-strip-col');
  if (midCTA) midCTA.style.display = (activeCategory === 'All' && searchQuery === '') ? '' : 'none';
}

filterBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    filterBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeCategory = btn.dataset.cat;
    applyFilters();
  });
});

searchInput.addEventListener('input', () => {
  searchQuery = searchInput.value.trim();
  applyFilters();
});
</script>
</body>
</html>
