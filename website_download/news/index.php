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
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Counselling" data-title="ggsipu centralized online counselling 2026-27 to begin tentatively from 8 june">
          <?php $post = array (
  'title' => 'GGSIPU Centralized Online Counselling 2026-27 to Begin Tentatively from 8 June',
  'slug' => 'ipu-centralized-online-counselling-enrolment',
  'date' => '2026-06-04',
  'date_modified' => '2026-06-04',
  'category' => 'Counselling',
  'tags' => 
  array (
    0 => 'GGSIPU Counselling',
    1 => 'IPU Admission 2026',
    2 => 'Online Counselling Enrolment',
    3 => 'Counselling 2026',
  ),
  'featured' => true,
  'is_urgent' => true,
  'tldr' => 'GGSIPU (Notification 26/2026, dated 03.06.2026) has announced that enrolment for its centralized online counselling 2026-27 is likely to begin tentatively from 8 June 2026 for B.Tech and 11 other programmes. MBA and MCA schedules will follow separately.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'When does GGSIPU online counselling 2026 start?',
      'a' => 'Per GGSIPU Notification 26/2026 (03.06.2026), enrolment for centralized online counselling 2026-27 is likely to begin tentatively from 8 June 2026. The university has stated the date is tentative; confirm on ipu.ac.in.',
    ),
    1 => 
    array (
      'q' => 'Which programmes are covered in this counselling?',
      'a' => 'B.Tech (131), BCA (114), BA LL.B./BBA LL.B. (121), LL.M. (112), B.Ed. (122), BBA & 5-year Integrated (125), BA JMC (126), LE B.Tech (128), B.Com Hons (146), B.Ed. Special Education (159), BA English Hons (184) and BA Economics Hons (197). MBA (101) and MCA (105) schedules will be announced separately.',
    ),
    2 => 
    array (
      'q' => 'Is the 8 June 2026 date confirmed?',
      'a' => 'No. GGSIPU\'s notification states the start is \'likely\' and \'tentative\'. Check ipu.ac.in and ipu.admissions.nic.in for the confirmed schedule, or call 9899991342.',
    ),
  ),
  'image' => 'assets/images/news/counselling.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ip university extends application deadline for new medical and paramedical courses">
          <?php $post = array (
  'title' => 'IP University Extends Application Deadline for New Medical and Paramedical Courses',
  'slug' => 'ipu-extends-application-deadline-new-medical-paramedical-courses',
  'date' => '2026-06-03',
  'date_modified' => '2026-06-03',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'IPU Admissions',
    1 => 'Paramedical',
    2 => 'Medical Courses',
    3 => 'Application Extension',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has extended the online application deadline for its newly introduced medical and paramedical programmes for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programmes are eligible for this application extension?',
      'a' => 'The extension is applicable to the newly introduced medical and paramedical programmes offered by GGSIPU for the academic session 2026-27.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the exact revised deadline to apply?',
      'a' => 'Candidates must refer to the official notification dated June 1, 2026, on the official IPU website (ipu.ac.in) to verify the final submission date.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ip university extends application deadline for new medical and paramedical courses">
          <?php $post = array (
  'title' => 'IP University Extends Application Deadline for New Medical and Paramedical Courses',
  'slug' => 'medical-paramedical-application-deadline-extended',
  'date' => '2026-06-02',
  'date_modified' => '2026-06-02',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'paramedical courses',
    2 => 'medical courses',
    3 => 'application extension',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has extended the online application deadline for its newly introduced medical and paramedical programmes for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programmes are eligible for this application extension?',
      'a' => 'The extension applies specifically to the newly introduced medical and paramedical programmes for the academic session 2026-27.',
    ),
    1 => 
    array (
      'q' => 'What is the final date to submit the online application form?',
      'a' => 'The official notification announcement does not specify the exact closing date. Candidates must refer to the official notification at ipu.ac.in to confirm the revised deadline.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues warning against admission touts for 2026-27 session">
          <?php $post = array (
  'title' => 'GGSIPU Issues Warning Against Admission Touts for 2026-27 Session',
  'slug' => 'ggsipu-warning-admission-touts',
  'date' => '2026-06-01',
  'date_modified' => '2026-06-01',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'university advisory',
    2 => 'admission alert',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an official advisory warning candidates and parents to beware of unauthorized touts promising direct admissions for the 2026-27 session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What is the GGSIPU warning about?',
      'a' => 'GGSIPU has warned candidates and parents against touts and unauthorized agents falsely promising guaranteed admissions for the 2026-27 academic year.',
    ),
    1 => 
    array (
      'q' => 'How are admissions officially conducted at GGSIPU?',
      'a' => 'Admissions are strictly merit-based and conducted through the official university portals. No third-party agents are authorized to allocate seats.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues warning against touts promising admissions for 2026-27">
          <?php $post = array (
  'title' => 'GGSIPU Issues Warning Against Touts Promising Admissions for 2026-27',
  'slug' => 'ggsipu-admission-warning-touts-fraud',
  'date' => '2026-05-31',
  'date_modified' => '2026-05-31',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'admission warning',
    2 => 'ipu admission fraud',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an official advisory warning candidates against touts and unauthorized agencies promising guaranteed admissions for the 2026-27 session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Has GGSIPU authorized any third-party agencies for admissions?',
      'a' => 'No, GGSIPU has not authorized any middlemen, touts, or external agencies to facilitate admissions.',
    ),
    1 => 
    array (
      'q' => 'How are admissions processed at GGSIPU?',
      'a' => 'Admissions are strictly merit-based and conducted through the official university counselling process. All official updates are posted on ipu.ac.in.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues warning against admission touts and fake promises">
          <?php $post = array (
  'title' => 'GGSIPU Issues Warning Against Admission Touts and Fake Promises',
  'slug' => 'ggsipu-warning-admission-touts-fake-promises',
  'date' => '2026-05-30',
  'date_modified' => '2026-05-30',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU Admissions',
    1 => 'Official Advisory',
    2 => 'IPU Counselling',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an official advisory warning candidates and parents against unauthorized agents promising guaranteed admissions for the 2026-27 session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Can third-party agents guarantee admission in GGSIPU?',
      'a' => 'No. GGSIPU does not authorize any external agents or consultants. All admissions are strictly merit-based and conducted through the official university counselling process.',
    ),
    1 => 
    array (
      'q' => 'Where should I verify official GGSIPU admission updates?',
      'a' => 'Always refer directly to the official university websites at ipu.ac.in and ipu.admissions.nic.in for verified information.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-12 mid-cta-strip-col">
          <div class="mid-cta-strip">
            <div class="cta-text">
              <h3>&#128222; Confused About IPU Admission 2026?</h3>
              <p>Talk to our expert right now — Free guidance, no charges, instant answers.</p>
            </div>
            <a href="tel:+919899991342" class="cta-btn">📞 Call Free: 9899991342</a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues warning against touts promising admissions for 2026-27">
          <?php $post = array (
  'title' => 'GGSIPU Issues Warning Against Touts Promising Admissions for 2026-27',
  'slug' => 'ggsipu-warning-touts-admission-fraud',
  'date' => '2026-05-29',
  'date_modified' => '2026-05-29',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'official advisory',
    2 => 'admission fraud warning',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an official advisory warning students and parents against unauthorized individuals promising guaranteed admissions for the 2026-27 session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Can agents or middlemen guarantee admission to GGSIPU?',
      'a' => 'No. GGSIPU does not authorize any external agents, consultants, or middlemen. Admissions are strictly merit-based.',
    ),
    1 => 
    array (
      'q' => 'How are seats allocated at GGSIPU?',
      'a' => 'Seats are allocated purely on merit through official entrance exams (like CET or national-level tests) and the university\'s centralized counselling process.',
    ),
    2 => 
    array (
      'q' => 'Where can I find authentic GGSIPU admission updates?',
      'a' => 'All official schedules, notifications, and allotment results are published exclusively on ipu.ac.in and ipu.admissions.nic.in.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues warning against fake admission agents and touts">
          <?php $post = array (
  'title' => 'GGSIPU Issues Warning Against Fake Admission Agents and Touts',
  'slug' => 'ggsipu-admissions-warning-touts',
  'date' => '2026-05-28',
  'date_modified' => '2026-05-28',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'admission alert',
    2 => 'official advisory',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an official advisory warning students and parents against touts and unauthorized agents promising direct admissions for the 2026-27 session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Does GGSIPU authorize any agents for direct admission?',
      'a' => 'No, GGSIPU does not authorize any third-party agents, touts, or external coordinators to facilitate or guarantee admissions.',
    ),
    1 => 
    array (
      'q' => 'How are admissions conducted at GGSIPU?',
      'a' => 'Admissions are strictly merit-based and conducted through the official university common entrance tests (CET), national-level exams, and the centralized online counselling process.',
    ),
    2 => 
    array (
      'q' => 'Where should students check for official admission updates?',
      'a' => 'Students and parents should exclusively visit the official university websites at ipu.ac.in and ipu.admissions.nic.in for genuine updates.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu withdraws online applications for m.sc. speech language pathology (code 168)">
          <?php $post = array (
  'title' => 'GGSIPU Withdraws Online Applications for M.Sc. Speech Language Pathology (Code 168)',
  'slug' => 'msc-speech-language-pathology-application-withdrawn',
  'date' => '2026-05-27',
  'date_modified' => '2026-05-27',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU Admissions',
    1 => 'M.Sc. Speech Language Pathology',
    2 => 'IPU Application Form',
    3 => 'Course Code 168',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has withdrawn the online application form submission for the M.Sc. Speech Language Pathology (Code 168) program.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which GGSIPU program application has been withdrawn?',
      'a' => 'The online application form submission for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, under course code 168, has been withdrawn.',
    ),
    1 => 
    array (
      'q' => 'When was this decision announced?',
      'a' => 'The official notification regarding the withdrawal of the application process was dated May 19, 2026.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu withdraws online applications for m.sc. speech language pathology (code 168)">
          <?php $post = array (
  'title' => 'GGSIPU Withdraws Online Applications for M.Sc. Speech Language Pathology (Code 168)',
  'slug' => 'ggsipu-withdraws-msc-slp-applications',
  'date' => '2026-05-26',
  'date_modified' => '2026-05-26',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu admissions',
    1 => 'msc speech language pathology',
    2 => 'cet code 168',
    3 => 'application withdrawal',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has withdrawn the online application form submission for the M.Sc. Speech Language Pathology (Code 168) program.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which GGSIPU program application has been withdrawn?',
      'a' => 'The online application submission for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, under CET code 168, has been withdrawn.',
    ),
    1 => 
    array (
      'q' => 'When was this withdrawal notification released?',
      'a' => 'The official notification regarding the withdrawal of the application forms was issued on May 19, 2026.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu withdraws online applications for m.sc. speech language pathology (code 168)">
          <?php $post = array (
  'title' => 'GGSIPU Withdraws Online Applications for M.Sc. Speech Language Pathology (Code 168)',
  'slug' => 'msc-speech-language-pathology-applications-withdrawn',
  'date' => '2026-05-25',
  'date_modified' => '2026-05-25',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'ggsipu',
    1 => 'msc slp',
    2 => 'application withdrawal',
    3 => 'ipu admissions',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has officially withdrawn the online application form submission process for the M.Sc. Speech Language Pathology (Code 168) program.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which IPU program\'s application process has been withdrawn?',
      'a' => 'The submission of online application forms has been withdrawn for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, which is registered under CET Code 168.',
    ),
    1 => 
    array (
      'q' => 'When was the notification regarding the application withdrawal released?',
      'a' => 'The official notification announcing the withdrawal of the application forms was dated May 19, 2026.',
    ),
    2 => 
    array (
      'q' => 'Where can I find updates on other GGSIPU admission processes?',
      'a' => 'For updates on other active programs and general admission procedures, you can check the official university portal at ipu.ac.in.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu withdraws m.sc. speech language pathology applications">
          <?php $post = array (
  'title' => 'IPU Withdraws M.Sc. Speech Language Pathology Applications',
  'slug' => 'ipu-withdraws-msc-speech-language-pathology-applications',
  'date' => '2026-05-23',
  'date_modified' => '2026-05-23',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU',
    1 => 'M.Sc. SLP',
    2 => 'Application Withdrawal',
    3 => 'IPU Admissions',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has withdrawn the online application form submission process for the M.Sc. Speech Language Pathology (Code 168) program.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which GGSIPU program application has been withdrawn?',
      'a' => 'The online application form submission for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, under CET Code 168, has been withdrawn.',
    ),
    1 => 
    array (
      'q' => 'When was the withdrawal notification issued?',
      'a' => 'The official notification announcing the withdrawal was dated May 19, 2026.',
    ),
    2 => 
    array (
      'q' => 'Where can candidates find updates regarding this decision?',
      'a' => 'Candidates are advised to refer to the official university websites at ipu.ac.in and ipu.admissions.nic.in for any further notifications or clarifications.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu withdraws applications for m.sc. speech language pathology (code 168)">
          <?php $post = array (
  'title' => 'GGSIPU Withdraws Applications for M.Sc. Speech Language Pathology (Code 168)',
  'slug' => 'msc-slp-application-withdrawal',
  'date' => '2026-05-21',
  'date_modified' => '2026-05-21',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU Admissions',
    1 => 'M.Sc. SLP',
    2 => 'Application Withdrawal',
    3 => 'Admission Notice',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has withdrawn the online application form submission process for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, Code 168.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which program\'s application process has been withdrawn?',
      'a' => 'The online application form submission for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, under CET Code 168, has been withdrawn.',
    ),
    1 => 
    array (
      'q' => 'When was this withdrawal notice issued?',
      'a' => 'The official notification regarding the withdrawal was dated and released on May 19, 2026.',
    ),
    2 => 
    array (
      'q' => 'Where can I check the official notification?',
      'a' => 'The official notice is available on the GGSIPU admissions portal. You can also refer to the official university website at ipu.ac.in for further updates.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu withdraws online applications for m.sc. speech language pathology (code 168)">
          <?php $post = array (
  'title' => 'IPU Withdraws Online Applications for M.Sc. Speech Language Pathology (Code 168)',
  'slug' => 'ipu-withdraws-msc-slp-applications',
  'date' => '2026-05-20',
  'date_modified' => '2026-05-20',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU Admissions',
    1 => 'M.Sc. SLP',
    2 => 'IPU Applications',
    3 => 'Course Code 168',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has withdrawn the online application form submission process for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, Code 168.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which program\'s application form has been withdrawn by GGSIPU?',
      'a' => 'GGSIPU has withdrawn the online application form submission process for the M.Sc. Speech Language Pathology (M.Sc. SLP) program, which is registered under course code 168.',
    ),
    1 => 
    array (
      'q' => 'Where can candidates find official updates regarding this withdrawal?',
      'a' => 'Candidates should refer to the official notification published on the university\'s website at ipu.ac.in for further details and official updates.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu updates paramedical course names for 2026-27 session">
          <?php $post = array (
  'title' => 'GGSIPU Updates Paramedical Course Names for 2026-27 Session',
  'slug' => 'ggsipu-paramedical-course-name-changes',
  'date' => '2026-05-19',
  'date_modified' => '2026-05-19',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Paramedical',
    1 => 'GGSIPU',
    2 => 'RCI',
    3 => 'Admissions 2026',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has updated the names of specific paramedical courses for the 2026-27 academic session to align with Rehabilitation Council of India (RCI) guidelines.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Why are the course names changing?',
      'a' => 'The names are being updated to comply with the latest nomenclature guidelines from the Rehabilitation Council of India (RCI).',
    ),
    1 => 
    array (
      'q' => 'When do these changes take effect?',
      'a' => 'These changes are applicable starting from the Academic Session 2026-27.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="General" data-title="ggsipu advisory: verify institutions to avoid fake universities">
          <?php $post = array (
  'title' => 'GGSIPU Advisory: Verify Institutions to Avoid Fake Universities',
  'slug' => 'verify-heis-avoid-fake-universities',
  'date' => '2026-05-18',
  'date_modified' => '2026-05-18',
  'category' => 'General',
  'tags' => 
  array (
    0 => 'Admissions',
    1 => 'Advisory',
    2 => 'Fake Universities',
    3 => 'Verification',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU advises students to verify the legitimacy of Higher Educational Institutions before admission to avoid unrecognized or fake universities.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'How can I check if a university is fake?',
      'a' => 'Students should visit the official University Grants Commission (UGC) website to view the current list of recognized and fake universities.',
    ),
    1 => 
    array (
      'q' => 'Are all GGSIPU colleges verified?',
      'a' => 'Yes, all colleges and institutes listed in the official GGSIPU admission brochure and counselling portal are recognized by the university.',
    ),
  ),
  'image' => 'assets/images/news/general.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu issues advisory on verifying institutions to avoid fake universities">
          <?php $post = array (
  'title' => 'GGSIPU Issues Advisory on Verifying Institutions to Avoid Fake Universities',
  'slug' => 'verify-institutions-avoid-fake-universities',
  'date' => '2026-05-17',
  'date_modified' => '2026-05-17',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Admissions',
    1 => 'Fake Universities',
    2 => 'Student Alert',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has issued an advisory urging students to verify the legitimacy of Higher Educational Institutions to avoid admission in fake universities.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What is the purpose of this alert?',
      'a' => 'The alert is intended to encourage students to verify Higher Educational Institutions (HEIs) to avoid taking admission in fake universities.',
    ),
    1 => 
    array (
      'q' => 'What should students do before taking admission?',
      'a' => 'Students should perform a thorough verification of the institution\'s legitimacy to ensure it is not a fake university.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu opens applications for new medical and paramedical courses 2026-27">
          <?php $post = array (
  'title' => 'IPU Opens Applications for New Medical and Paramedical Courses 2026-27',
  'slug' => 'new-medical-paramedical-applications',
  'date' => '2026-05-15',
  'date_modified' => '2026-05-15',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Medical Admissions',
    1 => 'Paramedical',
    2 => 'GGSIPU',
    3 => 'Application Form',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has started accepting online applications for several newly introduced medical and paramedical programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which academic session are these new courses for?',
      'a' => 'These newly introduced medical and paramedical programs are for the 2026-27 academic session.',
    ),
    1 => 
    array (
      'q' => 'How can I apply for these new programs?',
      'a' => 'Candidates must submit their application forms online through the official university admission portal at ipu.admissions.nic.in.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu opens applications for new medical and para-medical programs 2026-27">
          <?php $post = array (
  'title' => 'IPU Opens Applications for New Medical and Para-Medical Programs 2026-27',
  'slug' => 'ipu-new-medical-paramedical-applications',
  'date' => '2026-05-14',
  'date_modified' => '2026-05-14',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'IPU Admissions',
    1 => 'Medical Programs',
    2 => 'Para-Medical',
    3 => 'Application Form',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU is now accepting online applications for newly introduced medical and para-medical programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What programs are covered in this notification?',
      'a' => 'The notification concerns the submission of online application forms for newly introduced medical and para-medical programs at IP University.',
    ),
    1 => 
    array (
      'q' => 'Which academic session is this for?',
      'a' => 'These applications are for the upcoming Academic Session 2026-27.',
    ),
    2 => 
    array (
      'q' => 'How can I apply for these new courses?',
      'a' => 'Interested candidates must submit their application forms online through the official university admission portal.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="new medical and para-medical program applications open at ggsipu">
          <?php $post = array (
  'title' => 'New Medical and Para-Medical Program Applications Open at GGSIPU',
  'slug' => 'new-medical-paramedical-applications-open',
  'date' => '2026-05-13',
  'date_modified' => '2026-05-13',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Medical',
    1 => 'Para-Medical',
    2 => 'Admissions',
    3 => 'GGSIPU',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has started accepting online applications for its newly introduced medical and para-medical programs for the 2026-27 academic year.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What programs are covered in this new notification?',
      'a' => 'The notification concerns newly introduced medical and para-medical programs for the 2026-27 academic session.',
    ),
    1 => 
    array (
      'q' => 'How can I submit my application for these programs?',
      'a' => 'Interested candidates must complete the online application process through the official GGSIPU admission portal.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu opens applications for new medical and para-medical courses 2026-27">
          <?php $post = array (
  'title' => 'IPU Opens Applications for New Medical and Para-Medical Courses 2026-27',
  'slug' => 'ipu-new-medical-paramedical-applications-2026',
  'date' => '2026-05-12',
  'date_modified' => '2026-05-12',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Medical Admissions',
    1 => 'Para Medical',
    2 => 'IPU Applications',
    3 => 'GGSIPU',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has started accepting online applications for its newly introduced medical and para-medical programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'What programs are covered in this notification?',
      'a' => 'The notification pertains to the newly introduced medical and para-medical programs for the 2026-27 academic session.',
    ),
    1 => 
    array (
      'q' => 'How can I apply for these new courses?',
      'a' => 'Interested candidates must submit their application forms online through the official GGSIPU admission portal.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu introduces new medical and para medical programmes for 2026-27">
          <?php $post = array (
  'title' => 'GGSIPU Introduces New Medical and Para Medical Programmes for 2026-27',
  'slug' => 'new-medical-paramedical-programmes-2026-27',
  'date' => '2026-05-08',
  'date_modified' => '2026-05-08',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Medical',
    1 => 'Para Medical',
    2 => 'New Courses',
    3 => 'GGSIPU Admissions 2026',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has announced the introduction of several new medical and para-medical programmes for the upcoming 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'When will the new medical programmes start at IPU?',
      'a' => 'The new programmes are scheduled to be introduced for the Academic Session 2026-27.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the official notice for these courses?',
      'a' => 'The official notification was released on the university\'s admission portal (ipu.admissions.nic.in) on April 30, 2026.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ggsipu announces new medical and paramedical programs for 2026-27">
          <?php $post = array (
  'title' => 'GGSIPU Announces New Medical and Paramedical Programs for 2026-27',
  'slug' => 'new-medical-paramedical-programmes-introduced',
  'date' => '2026-05-07',
  'date_modified' => '2026-05-07',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'GGSIPU',
    1 => 'Medical Admissions',
    2 => 'Paramedical',
    3 => 'New Courses',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has announced the introduction of new medical and paramedical programmes for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'When will the new medical courses be introduced?',
      'a' => 'The new medical and paramedical programmes are scheduled to be introduced for the academic session 2026-27.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the official announcement?',
      'a' => 'The announcement is available on the official GGSIPU admissions website (ipu.admissions.nic.in).',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu to launch new medical and para medical programs for 2026-27">
          <?php $post = array (
  'title' => 'IPU to Launch New Medical and Para Medical Programs for 2026-27',
  'slug' => 'ipu-new-medical-paramedical-programs-2026',
  'date' => '2026-05-06',
  'date_modified' => '2026-05-06',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'Medical',
    1 => 'Para Medical',
    2 => 'New Programs',
    3 => 'Admissions 2026',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has announced the introduction of new medical and para-medical courses starting from the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'When will the new medical programs begin at IPU?',
      'a' => 'The new programs are scheduled to be introduced for the 2026-27 academic session.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the official notification for these courses?',
      'a' => 'The notice was released on April 30, 2026, and is available on the official IPU admissions portal.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu b.tech lateral entry seat intake released for diploma and b.sc graduates">
          <?php $post = array (
  'title' => 'IPU B.Tech Lateral Entry Seat Intake Released for Diploma and B.Sc Graduates',
  'slug' => 'ipu-btech-lateral-entry-seat-intake-released',
  'date' => '2026-04-30',
  'date_modified' => '2026-04-30',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'B.Tech Lateral Entry',
    1 => 'GGSIPU Admissions',
    2 => 'Seat Intake',
    3 => 'CET 128',
    4 => 'CET 129',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has released the seat intake details for B.Tech Lateral Entry (CET Codes 128 & 129) for the 2026-27 academic session, including Management Quota seats.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which CET codes are included in the seat intake announcement?',
      'a' => 'The announcement covers CET Code 128 for Diploma holders and CET Code 129 for B.Sc. graduates entering B.Tech programs.',
    ),
    1 => 
    array (
      'q' => 'Are Management Quota seats included in this intake?',
      'a' => 'Yes, the seat allocation details explicitly include the intake for the Management Quota (MQ) for the current academic session.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu b.tech lateral entry seat intake released for 2026-27">
          <?php $post = array (
  'title' => 'IPU B.Tech Lateral Entry Seat Intake Released for 2026-27',
  'slug' => 'ipu-btech-lateral-entry-seat-intake-announced',
  'date' => '2026-04-29',
  'date_modified' => '2026-04-29',
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
  'tldr' => 'GGSIPU has published the official seat intake for B.Tech Lateral Entry (CET 128 and 129) for the 2026-27 session, including Management Quota details.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programs are included in this seat allocation update?',
      'a' => 'The update covers B.Tech Lateral Entry for Diploma holders (CET Code 128) and B.Sc. graduates (CET Code 129).',
    ),
    1 => 
    array (
      'q' => 'Are Management Quota seats included in this intake list?',
      'a' => 'Yes, the official notification includes the seat intake for both general admissions and the Management Quota (MQ).',
    ),
    2 => 
    array (
      'q' => 'Where can I find the specific seat counts for each college?',
      'a' => 'Students should refer to the official PDF document on the IPU admissions portal for the college-wise breakdown.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu b.tech lateral entry seat intake released (cet 128 &amp; 129)">
          <?php $post = array (
  'title' => 'IPU B.Tech Lateral Entry Seat Intake Released (CET 128 & 129)',
  'slug' => 'ipu-btech-lateral-entry-seat-intake',
  'date' => '2026-04-27',
  'date_modified' => '2026-04-27',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'B.Tech Lateral Entry',
    1 => 'CET 128',
    2 => 'CET 129',
    3 => 'Seat Intake',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has released the seat intake for B.Tech Lateral Entry (CET 128/129) for the 2026-27 session, including Management Quota seats.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which CET codes are covered in this seat intake?',
      'a' => 'The intake covers CET Code 128 for Diploma holders and CET Code 129 for B.Sc. graduates seeking lateral entry to B.Tech.',
    ),
    1 => 
    array (
      'q' => 'Are Management Quota seats included in this announcement?',
      'a' => 'Yes, the seat allocation includes the intake for the Management Quota (MQ) for the 2026-27 academic session.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu b.tech lateral entry seat intake released for 2026 session">
          <?php $post = array (
  'title' => 'IPU B.Tech Lateral Entry Seat Intake Released for 2026 Session',
  'slug' => 'ipu-btech-lateral-entry-seat-intake-2026',
  'date' => '2026-04-26',
  'date_modified' => '2026-04-26',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'B.Tech Lateral Entry',
    1 => 'CET 128',
    2 => 'CET 129',
    3 => 'Seat Intake',
    4 => 'Management Quota',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has released the seat intake for B.Tech Lateral Entry (CET 128 and 129) for the 2026 session, including Management Quota seat details.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which CET codes are covered in this seat intake announcement?',
      'a' => 'The announcement covers CET Code 128 for Diploma holders and CET Code 129 for B.Sc graduates.',
    ),
    1 => 
    array (
      'q' => 'Does the seat allocation include Management Quota?',
      'a' => 'Yes, the official seat matrix includes the intake for the Management Quota (MQ) for the 2026 academic session.',
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
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ipu updates bpt and bot admission criteria and nomenclature for 2026-27">
          <?php $post = array (
  'title' => 'IPU Updates BPT and BOT Admission Criteria and Nomenclature for 2026-27',
  'slug' => 'ipu-bpt-bot-admission-criteria-nomenclature-changes',
  'date' => '2026-04-23',
  'date_modified' => '2026-04-23',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'BPT',
    1 => 'BOT',
    2 => 'Admission Criteria',
    3 => 'GGSIPU',
  ),
  'featured' => false,
  'is_urgent' => false,
  'tldr' => 'GGSIPU has announced changes to the nomenclature and admission criteria for B.PT and B.OT programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which programs are affected by the new IPU notice?',
      'a' => 'The notice specifically concerns the Bachelor of Physiotherapy (BPT) and Bachelor of Occupational Therapy (BOT) programs.',
    ),
    1 => 
    array (
      'q' => 'What changes have been implemented for the 2026-27 session?',
      'a' => 'The university has updated the nomenclature and the specific admission criteria required for eligibility in these courses.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
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
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="CET" data-title="final extension for ipu cet 2026 registration announced">
          <?php $post = array (
  'title' => 'Final Extension for IPU CET 2026 Registration Announced',
  'slug' => 'final-opportunity-ipu-cet-registration',
  'date' => '2026-04-21',
  'date_modified' => '2026-04-21',
  'category' => 'CET',
  'tags' => 
  array (
    0 => 'IPU CET',
    1 => 'Registration',
    2 => 'Admissions 2026',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has announced the last and final opportunity for candidates to register for the Common Entrance Test (CET) for the 2026 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Is this the last date to register for IPU CET?',
      'a' => 'Yes, the university has officially notified that this is the last and final opportunity for CET registration.',
    ),
    1 => 
    array (
      'q' => 'Where can I complete the registration?',
      'a' => 'Candidates must visit the official university portal at ipu.admissions.nic.in to submit their applications.',
    ),
  ),
  'image' => 'assets/images/news/cet.jpg',
  'read_time' => 1,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="Admissions" data-title="ip university extends application deadline for 2026-27 admissions">
          <?php $post = array (
  'title' => 'IP University Extends Application Deadline for 2026-27 Admissions',
  'slug' => 'ipu-application-deadline-extended',
  'date' => '2026-04-20',
  'date_modified' => '2026-04-20',
  'category' => 'Admissions',
  'tags' => 
  array (
    0 => 'IPU Admissions 2026',
    1 => 'Application Form',
    2 => 'National Level Test',
    3 => 'Merit Based Admission',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has extended the last date for online application submissions for National Level Test and merit-based programs for the 2026-27 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Which admission categories are affected by this extension?',
      'a' => 'The extension applies to programs where admission is granted based on National Level Tests or merit in the qualifying examination for the 2026-27 session.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the specific new deadline for IPU applications?',
      'a' => 'Candidates should refer to the official notification on the university\'s admission portal at ipu.admissions.nic.in for the exact closing dates.',
    ),
  ),
  'image' => 'assets/images/news/admissions.jpg',
  'read_time' => 2,
); include __DIR__ . '/../include/news-card.php'; ?>
        </div>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="CET" data-title="final opportunity for ggsipu cet 2026 registration announced">
          <?php $post = array (
  'title' => 'Final Opportunity for GGSIPU CET 2026 Registration Announced',
  'slug' => 'last-opportunity-ipu-cet-registration',
  'date' => '2026-04-19',
  'date_modified' => '2026-04-19',
  'category' => 'CET',
  'tags' => 
  array (
    0 => 'IPU CET',
    1 => 'Registration',
    2 => 'Admissions 2026',
  ),
  'featured' => false,
  'is_urgent' => true,
  'tldr' => 'GGSIPU has announced a final window for candidates to register for the Common Entrance Test (CET) for the 2026 academic session.',
  'faq' => 
  array (
    0 => 
    array (
      'q' => 'Is this the last chance to register for the IPU CET?',
      'a' => 'Yes, according to the official notification, this is the last and final opportunity for CET registration.',
    ),
    1 => 
    array (
      'q' => 'Where can I find the official registration link?',
      'a' => 'Candidates should visit the official GGSIPU admission portal at ipu.admissions.nic.in to complete their application.',
    ),
  ),
  'image' => 'assets/images/news/cet.jpg',
  'read_time' => 2,
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
