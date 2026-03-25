<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("include/base-head.php");
include_once("include/form-handler.php");

/* ================================================
   ALL IP UNIVERSITY BLOGS - ENHANCED 2026
   ================================================ */

$blogs = [
    ["category"=>"MBA","title"=>"MBA Admission in IP University 2026–27: Fees, CAT Cutoff & Colleges","url"=>"mba-admission-ip-university.php","img"=>"assets/images/blog-ipu-mba.jpg","alt"=>"MBA Admission in IP University","excerpt"=>"Complete guide to MBA admission at GGSIPU — CAT/CMAT cutoffs, top colleges, fees structure & career scope.","read_time"=>"7"],
    ["category"=>"Economics","title"=>"Economics Admission 2025: Eligibility, Colleges & Career Scope","url"=>"economics-admission-2025.php","img"=>"assets/images/economics-admission-2025.jpg","alt"=>"Economics Admission 2025","excerpt"=>"Everything you need to know about Economics programmes under IPU — eligibility, top colleges & career paths.","read_time"=>"5"],
    ["category"=>"Law","title"=>"IPU Law Admission 2025: Eligibility, Colleges & Guidance Process","url"=>"IPU-Law-Admission-2025.php","img"=>"assets/images/IPU-Law-Admission-2025.jpg","alt"=>"IPU Law Admission","excerpt"=>"Step-by-step guide to IPU Law admission — eligibility criteria, top law colleges, fees & admission process.","read_time"=>"6"],
    ["category"=>"Law","title"=>"Ultimate Guide to BA LL.B & BBA LL.B Admission in IP University","url"=>"ultimate-guide-to-ballb-admission-in-ip-university.php","img"=>"assets/images/ipu-bballb.jpg","alt"=>"BALLB Admission","excerpt"=>"Detailed guide covering BA LL.B and BBA LL.B admissions at IP University — process, cutoffs & top colleges.","read_time"=>"8"],
    ["category"=>"Law","title"=>"Comprehensive Guide to BBA LL.B Admission in IP University","url"=>"comprehensive-guide-to-bballb-admission-in-ip-university.php","img"=>"assets/images/ipu-bballb.jpg","alt"=>"BBALLB Admission","excerpt"=>"All you need to know about BBA LL.B admission — eligibility, entrance process, best colleges & placement.","read_time"=>"7"],
    ["category"=>"BJMC","title"=>"Guide to BJMC Colleges under IP University","url"=>"guide-to-bjmc-colleges-under-ip-university.php","img"=>"assets/images/IPU-BJMC-Admission.jpg","alt"=>"BJMC Colleges","excerpt"=>"Top BJMC colleges affiliated with IP University — admission process, seats, fees & media career scope.","read_time"=>"5"],
    ["category"=>"CET","title"=>"IPU CET Exam Date, Result & Admit Card","url"=>"ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php","img"=>"assets/images/ipu-cet-2025-exam-dates-and-admit-card.jpg","alt"=>"IPU CET","excerpt"=>"Stay updated on IPU CET 2026 exam schedule, admit card release dates, result declaration & how to apply.","read_time"=>"4"],
    ["category"=>"B.Tech","title"=>"IPU B.Tech Admission 2025: Eligibility & Guidance","url"=>"IPU-B-Tech-admission-2025.php","img"=>"assets/images/ipu-b-tech-admission-2025.jpg","alt"=>"IPU BTech Admission","excerpt"=>"Complete B.Tech admission guide for GGSIPU — JEE cutoffs, eligibility, top engineering colleges & fees.","read_time"=>"8"],
    ["category"=>"B.Tech","title"=>"How to Participate in GGSIPU Guidance Process","url"=>"GGSIPU-counselling-for-B-Tech-admission.php","img"=>"assets/images/ggsipu-counselling.jpg","alt"=>"GGSIPU Guidance Process","excerpt"=>"Step-by-step walkthrough of the GGSIPU guidance process — document checklist, round schedule & allotment.","read_time"=>"6"],
    ["category"=>"B.Tech","title"=>"Best B.Tech Colleges under IP University","url"=>"b-tech-colleges-under-IP-university.php","img"=>"assets/images/IP-University-b-tech-admission.jpg","alt"=>"Best BTech Colleges IPU","excerpt"=>"Ranked list of top B.Tech colleges under IP University with placement records, fees & admission cut-offs.","read_time"=>"7"],
    ["category"=>"BBA","title"=>"Top BBA Colleges under IP University","url"=>"comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php","img"=>"assets/images/BBA.jpg","alt"=>"Top BBA Colleges IPU","excerpt"=>"Discover the best BBA colleges under GGSIPU — placements, fees, specialisations & entrance requirements.","read_time"=>"6"],
    ["category"=>"Colleges","title"=>"Explore MSIT & MSI Janakpuri","url"=>"explore-MSIT-and-MSI-janakpuri.php","img"=>"assets/images/explore-MSIT-and-MSI-janakpuri.jpg","alt"=>"MSIT MSI Janakpuri","excerpt"=>"In-depth look at MSIT and MSI Janakpuri — courses offered, admission process, campus & placements.","read_time"=>"5"],
    ["category"=>"Colleges","title"=>"Exploring MAIT & MAIMS Rohini","url"=>"exploring-MAIT-and-MAIMS.php","img"=>"assets/images/exploring-MAIT-and-MAIMS.jpg","alt"=>"MAIT MAIMS Rohini","excerpt"=>"Everything about MAIT and MAIMS Rohini — programmes, eligibility, fees, infrastructure & career prospects.","read_time"=>"5"],
    ["category"=>"Colleges","title"=>"VIPS Pitampura: Courses & Law Programs","url"=>"vips-pitampura-courses.php","img"=>"assets/images/vips-pitampura-courses.jpg","alt"=>"VIPS Pitampura","excerpt"=>"Detailed profile of VIPS Pitampura — law and management programmes, fees, placements & admission guide.","read_time"=>"5"],
    ["category"=>"Admissions","title"=>"IP University Management Quota Admission","url"=>"IP-University-management-quota-admission-eligibility-criteria.php","img"=>"assets/images/blog1.jpg","alt"=>"IPU Management Quota","excerpt"=>"How to secure a seat through Management Quota at IPU — eligibility, process, fees & which colleges offer it.","read_time"=>"6"],
];

$categories = ["All","B.Tech","MBA","Law","BBA","BJMC","CET","Economics","Colleges","Admissions"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IPU Admission Blogs 2026 | GGSIPU Guidance Hub – B.Tech, MBA, Law & More</title>
<meta name="description" content="Read expert IPU admission blogs for 2026 — B.Tech, MBA, Law, BBA, BJMC guidance. Get free expert help. Call 9899991342.">
<link rel="canonical" href="https://ipu.co.in/blog.php">
<meta name="robots" content="index, follow">
<meta property="og:title" content="IPU Admission Blogs 2026 | GGSIPU Expert Guidance Hub">
<meta property="og:description" content="All IP University admission blogs — B.Tech, MBA, Law, BBA, BJMC & more. Free expert guidance. Call 9899991342.">
<meta property="og:url" content="https://ipu.co.in/blog.php">
<meta property="og:type" content="website">
<meta property="og:image" content="https://ipu.co.in/assets/images/blog-ipu-mba.jpg">
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@graph":[
    {
      "@type":"BreadcrumbList",
      "itemListElement":[
        {"@type":"ListItem","position":1,"name":"Home","item":"https://ipu.co.in/"},
        {"@type":"ListItem","position":2,"name":"Blog","item":"https://ipu.co.in/blog.php"}
      ]
    },
    {
      "@type":"WebPage",
      "@id":"https://ipu.co.in/blog.php",
      "url":"https://ipu.co.in/blog.php",
      "name":"IPU Admission Blogs 2026",
      "description":"Expert blogs on IPU admission 2026 — B.Tech, MBA, Law, BBA, BJMC guidance hub.",
      "publisher":{"@type":"Organization","name":"ipu.co.in","url":"https://ipu.co.in"}
    },
    {
      "@type":"ItemList",
      "itemListElement":[
        <?php foreach($blogs as $i => $b): ?>
        {"@type":"ListItem","position":<?php echo $i+1; ?>,"name":"<?php echo addslashes($b['title']); ?>","url":"https://ipu.co.in/<?php echo $b['url']; ?>"}<?php echo ($i < count($blogs)-1) ? ',' : ''; ?>
        <?php endforeach; ?>
      ]
    }
  ]
}
</script>
<style>
/* ============ BLOG PAGE ENHANCEMENTS ============ */
.blog-hero {
  position: relative;
  padding: 120px 0 60px;
  text-align: center;
  color: #fff;
  overflow: hidden;
  -webkit-clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%);
  clip-path: polygon(0 0, 100% 0, 100% 90%, 0 101%);
}
.blog-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background-color: #0b2c5d;
  opacity: 0.85;
  z-index: 0;
}
.blog-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: url(assets/images/banner-bg-2.jpg);
  background-size: cover;
  background-position: center;
  z-index: -1;
}
.blog-hero .container { position: relative; z-index: 1; }
.blog-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; color: #fff; }
.blog-hero p { font-size: 1.05rem; opacity: .88; color: #fff; }
@media (max-width: 767px) {
  .blog-hero { padding: 90px 0 50px; -webkit-clip-path: none; clip-path: none; }
}
.breadcrumb-wrap {
  background: #f0f4ff;
  padding: 10px 0;
  font-size: .85rem;
  border-bottom: 1px solid #dce6ff;
}
.breadcrumb-wrap a { color: #1a4a9f; text-decoration: none; }
.breadcrumb-wrap a:hover { text-decoration: underline; }
.breadcrumb-wrap span { color: #666; }
/* Category Filter */
.cat-filter { padding: 22px 0 10px; }
.cat-filter .btn-cat {
  display: inline-block;
  padding: 7px 18px;
  margin: 4px 4px;
  border-radius: 25px;
  border: 2px solid #1a4a9f;
  color: #1a4a9f;
  background: #fff;
  font-size: .82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s;
  text-transform: uppercase;
  letter-spacing: .4px;
}
.cat-filter .btn-cat:hover,
.cat-filter .btn-cat.active {
  background: #1a4a9f;
  color: #fff;
}
/* Blog Cards */
.blog-card-wrap {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 14px rgba(0,0,0,.08);
  overflow: hidden;
  transition: transform .2s, box-shadow .2s;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.blog-card-wrap:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 28px rgba(26,74,159,.18);
}
.blog-card-wrap img {
  width: 100%; height: 185px; object-fit: cover;
}
.blog-card-body {
  padding: 16px 18px 18px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.blog-cat-tag {
  display: inline-block;
  background: #e8effe;
  color: #1a4a9f;
  font-size: .72rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: .5px;
}
.blog-card-title {
  font-size: .97rem;
  font-weight: 700;
  color: #1a2d6b;
  line-height: 1.4;
  margin-bottom: 8px;
  text-decoration: none;
  display: block;
}
.blog-card-title:hover { color: #e65c00; }
.blog-excerpt {
  font-size: .82rem;
  color: #555;
  line-height: 1.5;
  flex: 1;
  margin-bottom: 12px;
}
.blog-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .78rem;
  color: #888;
  border-top: 1px solid #f0f0f0;
  padding-top: 10px;
  margin-top: auto;
}
.blog-read-more {
  color: #1a4a9f;
  font-weight: 700;
  font-size: .82rem;
  text-decoration: none;
}
.blog-read-more:hover { color: #e65c00; }
/* Mid-page CTA */
.mid-cta-strip {
  background: linear-gradient(90deg, #e65c00 0%, #f5820a 100%);
  border-radius: 12px;
  padding: 22px 28px;
  margin: 10px 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}
.mid-cta-strip .cta-text { color: #fff; }
.mid-cta-strip .cta-text h3 { font-size: 1.15rem; font-weight: 800; margin: 0 0 4px; }
.mid-cta-strip .cta-text p { font-size: .88rem; margin: 0; opacity: .9; }
.mid-cta-strip .cta-btn {
  background: #fff;
  color: #e65c00;
  font-weight: 800;
  font-size: 1rem;
  padding: 11px 24px;
  border-radius: 30px;
  text-decoration: none;
  white-space: nowrap;
  transition: background .2s;
}
.mid-cta-strip .cta-btn:hover { background: #fff3e8; }
/* Sidebar */
.sidebar-card {
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 16px rgba(0,0,0,.1);
  overflow: hidden;
  position: sticky;
  top: 80px;
}
.sidebar-call-banner {
  background: linear-gradient(135deg, #0d2b6b, #1a4a9f);
  padding: 18px 20px;
  text-align: center;
  color: #fff;
}
.sidebar-call-banner .avail-badge {
  display: inline-block;
  background: #28a745;
  color: #fff;
  font-size: .7rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  margin-bottom: 8px;
  letter-spacing: .5px;
  text-transform: uppercase;
}
.sidebar-call-banner h4 { font-size: .95rem; font-weight: 700; margin: 0 0 10px; }
.sidebar-call-btn {
  display: block;
  background: #ff6600;
  color: #fff !important;
  text-decoration: none !important;
  padding: 11px 16px;
  border-radius: 8px;
  font-weight: 800;
  font-size: 1rem;
  margin-bottom: 8px;
  animation: pulse-call 2s infinite;
  letter-spacing: .3px;
}
@keyframes pulse-call {
  0%,100% { box-shadow: 0 0 0 0 rgba(255,102,0,.5); }
  50% { box-shadow: 0 0 0 8px rgba(255,102,0,0); }
}
.sidebar-whatsapp-btn {
  display: block;
  background: #25d366;
  color: #fff !important;
  text-decoration: none !important;
  padding: 9px 16px;
  border-radius: 8px;
  font-weight: 700;
  font-size: .88rem;
}
.sidebar-whatsapp-btn:hover { background: #1db954; }
.sidebar-form { padding: 18px 20px 20px; }
.sidebar-form label { font-size: .82rem; font-weight: 600; color: #333; margin-bottom: 3px; display: block; }
.sidebar-form .form-control {
  border-radius: 7px;
  border: 1.5px solid #dde3f5;
  font-size: .88rem;
  padding: 9px 12px;
  margin-bottom: 12px;
  width: 100%;
}
.sidebar-form .form-control:focus { border-color: #1a4a9f; outline: none; box-shadow: 0 0 0 3px rgba(26,74,159,.12); }
.sidebar-submit-btn {
  width: 100%;
  background: linear-gradient(90deg, #1a4a9f, #2563eb);
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-weight: 700;
  font-size: .95rem;
  cursor: pointer;
  transition: opacity .2s;
}
.sidebar-submit-btn:hover { opacity: .9; }
/* Search bar */
.blog-search-wrap {
  position: relative;
  margin-bottom: 8px;
}
.blog-search-wrap input {
  width: 100%;
  padding: 10px 40px 10px 15px;
  border-radius: 25px;
  border: 2px solid #dde3f5;
  font-size: .9rem;
}
.blog-search-wrap input:focus { border-color: #1a4a9f; outline: none; }
.blog-search-wrap .search-icon {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}
/* Hidden blogs */
.blog-item.hidden { display: none !important; }
.no-results-msg { display: none; text-align: center; color: #888; padding: 30px; font-size: .95rem; width: 100%; }
</style>
</head>
<body>

<?php include_once("include/base-nav.php"); ?>

<!-- Hero Section -->
<section class="blog-hero">
  <div class="container">
    <h1>IP University (GGSIPU) Blogs 2026</h1>
    <p>Complete Admission & Expert Guidance Knowledge Hub</p>
  </div>
</section>

<!-- Breadcrumb -->
<nav class="breadcrumb-wrap" aria-label="breadcrumb">
  <div class="container">
    <a href="/">Home</a> &rsaquo; <span>Blog</span>
  </div>
</nav>

<!-- Main Content -->
<section class="py-4">
<div class="container">

  <!-- Search Bar -->
  <div class="row mb-2">
    <div class="col-lg-9 col-md-12">
      <div class="blog-search-wrap">
        <input type="text" id="blogSearch" placeholder="Search blogs… e.g. B.Tech, MBA, Law" autocomplete="off">
        <span class="search-icon">&#128269;</span>
      </div>
    </div>
  </div>

  <!-- Category Filter -->
  <div class="cat-filter mb-3">
    <?php foreach($categories as $cat): ?>
    <button class="btn-cat <?php echo $cat==='All' ? 'active' : ''; ?>" data-cat="<?php echo $cat; ?>"><?php echo $cat; ?></button>
    <?php endforeach; ?>
  </div>

  <div class="row">
    <!-- Blog Grid -->
    <div class="col-lg-9 col-md-12 order-2 order-lg-1">
      <div class="row" id="blogGrid">
        <?php foreach($blogs as $i => $blog): ?>
        <div class="col-lg-4 col-md-6 mb-4 blog-item" data-category="<?php echo $blog['category']; ?>" data-title="<?php echo strtolower($blog['title']); ?>">
          <div class="blog-card-wrap">
            <a href="<?php echo $blog['url']; ?>">
              <img src="<?php echo $blog['img']; ?>" alt="<?php echo $blog['alt']; ?>" loading="lazy">
            </a>
            <div class="blog-card-body">
              <span class="blog-cat-tag"><?php echo $blog['category']; ?></span>
              <a href="<?php echo $blog['url']; ?>" class="blog-card-title"><?php echo $blog['title']; ?></a>
              <p class="blog-excerpt"><?php echo $blog['excerpt']; ?></p>
              <div class="blog-meta">
                <span>&#9200; <?php echo $blog['read_time']; ?> min read</span>
                <a href="<?php echo $blog['url']; ?>" class="blog-read-more">Read More &rarr;</a>
              </div>
            </div>
          </div>
        </div>
        <?php if($i === 5): ?>
        <!-- Mid-page CTA Strip after 6th card -->
        <div class="col-12 mid-cta-strip-col">
          <div class="mid-cta-strip">
            <div class="cta-text">
              <h3>&#128222; Confused About IPU Admission 2026?</h3>
              <p>Talk to our expert right now — Free guidance, no charges, instant answers.</p>
            </div>
            <a href="tel:9899991342" class="cta-btn">📞 Call Free: 9899991342</a>
          </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <div class="no-results-msg" id="noResults">No blogs found. Try a different search or category.</div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3 col-md-12 order-1 order-lg-2 mb-4">
      <div style="position:sticky;top:80px">
        <?php include_once("include/sidebar-cta.php"); ?>
      </div>
    </div>
  </div><!-- /.row -->

</div><!-- /.container -->
</section>

<?php include_once("include/base-footer.php"); ?>

<script>
// ---- Category Filter ----
const filterBtns = document.querySelectorAll('.btn-cat');
const blogItems = document.querySelectorAll('.blog-item');
const noResults = document.getElementById('noResults');
const searchInput = document.getElementById('blogSearch');

let activeCategory = 'All';
let searchQuery = '';

function applyFilters() {
  let visibleCount = 0;
  blogItems.forEach(item => {
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
  // Hide mid-CTA strip when filtering
  const midCTA = document.querySelector('.mid-cta-strip-col');
  if(midCTA) midCTA.style.display = (activeCategory === 'All' && searchQuery === '') ? '' : 'none';
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