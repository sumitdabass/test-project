<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("include/base-head.php");

$email = $_SESSION['enh_email'] ?? '';
$phone = $_SESSION['enh_phone'] ?? '';

unset($_SESSION['enh_email'], $_SESSION['enh_phone']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Thank You – Enquiry Received | IPU Admission 2026 Helpline</title>
<meta name="description" content="Your IPU admission enquiry has been received. Call 9899991342 now for instant guidance on B.Tech, MBA, Law, BBA & more under IP University 2026.">
<meta name="robots" content="noindex, follow">
<link rel="canonical" href="https://ipu.co.in/thank-you.php">

<!-- Open Graph -->
<meta property="og:title" content="Thank You – IPU Admission Enquiry Received">
<meta property="og:description" content="Your enquiry is received. Call 9899991342 now for free IPU admission guidance.">
<meta property="og:url" content="https://ipu.co.in/thank-you.php">
<meta property="og:type" content="website">

<!-- Styles loaded via base-head.php -->

<style>
/* ===== THANK YOU PAGE STYLES ===== */
.ty-hero {
  background: linear-gradient(135deg, #0d1b6e 0%, #1a3a9c 60%, #0a2a8c 100%);
  padding: 60px 20px 50px;
  text-align: center;
  color: #fff;
  position: relative;
  overflow: hidden;
}
.ty-hero::before {
  content: '';
  position: absolute;
  top: -50px; left: -50px;
  width: 200px; height: 200px;
  background: rgba(255,255,255,0.04);
  border-radius: 50%;
}
.ty-hero::after {
  content: '';
  position: absolute;
  bottom: -60px; right: -40px;
  width: 250px; height: 250px;
  background: rgba(255,255,255,0.04);
  border-radius: 50%;
}
.ty-check {
  width: 80px; height: 80px;
  background: #28a745;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
  font-size: 36px;
  box-shadow: 0 0 0 12px rgba(40,167,69,0.2);
  animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
  0%   { box-shadow: 0 0 0 12px rgba(40,167,69,0.2); }
  50%  { box-shadow: 0 0 0 22px rgba(40,167,69,0.05); }
  100% { box-shadow: 0 0 0 12px rgba(40,167,69,0.2); }
}
.ty-hero h1 { font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; }
.ty-hero p  { font-size: 1.1rem; color: rgba(255,255,255,0.85); max-width: 500px; margin: 0 auto; }

/* ===== URGENT CALL SECTION ===== */
.ty-call-box {
  background: #fff8e1;
  border: 3px solid #f59e0b;
  border-radius: 16px;
  padding: 36px 28px;
  text-align: center;
  max-width: 620px;
  margin: -30px auto 0;
  position: relative;
  z-index: 10;
  box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
.ty-call-badge {
  display: inline-block;
  background: #dc3545;
  color: #fff;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 5px 14px;
  border-radius: 30px;
  margin-bottom: 14px;
  animation: blink-badge 1.6s ease-in-out infinite;
}
@keyframes blink-badge {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.6; }
}
.ty-call-box h2 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a3a9c;
  margin-bottom: 8px;
}
.ty-call-box p {
  color: #555;
  font-size: 0.97rem;
  margin-bottom: 22px;
}
.ty-call-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, #e65c00, #f9a825);
  color: #fff !important;
  font-size: 1.35rem;
  font-weight: 800;
  padding: 16px 36px;
  border-radius: 50px;
  text-decoration: none;
  box-shadow: 0 6px 20px rgba(230,92,0,0.4);
  transition: transform 0.2s, box-shadow 0.2s;
  animation: pulse-btn 2.2s ease-in-out infinite;
}
.ty-call-btn:hover {
  transform: scale(1.04);
  box-shadow: 0 10px 28px rgba(230,92,0,0.5);
  color: #fff !important;
  text-decoration: none;
}
@keyframes pulse-btn {
  0%, 100% { box-shadow: 0 6px 20px rgba(230,92,0,0.4); }
  50%       { box-shadow: 0 6px 30px rgba(230,92,0,0.65); }
}
.ty-call-btn i { font-size: 1.5rem; }
.ty-whatsapp-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #25d366;
  color: #fff !important;
  font-size: 1rem;
  font-weight: 700;
  padding: 12px 28px;
  border-radius: 50px;
  text-decoration: none;
  margin-top: 14px;
  transition: background 0.2s;
}
.ty-whatsapp-btn:hover { background: #1da853; color: #fff !important; text-decoration: none; }
.ty-timing {
  margin-top: 12px;
  font-size: 0.82rem;
  color: #777;
}
.ty-timing i { color: #f59e0b; }

/* ===== NEXT STEPS ===== */
.ty-steps-section {
  background: #f8f9ff;
  padding: 50px 20px;
}
.ty-steps-section h2 {
  text-align: center;
  font-size: 1.6rem;
  font-weight: 800;
  color: #1a3a9c;
  margin-bottom: 32px;
}
.ty-steps {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: center;
  max-width: 900px;
  margin: 0 auto;
}
.ty-step {
  background: #fff;
  border-radius: 12px;
  padding: 24px 20px;
  text-align: center;
  flex: 1 1 200px;
  max-width: 230px;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
  border-top: 4px solid #1a3a9c;
}
.ty-step-num {
  width: 42px; height: 42px;
  background: #1a3a9c;
  color: #fff;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1.1rem;
  margin: 0 auto 12px;
}
.ty-step h4 { font-size: 0.95rem; font-weight: 700; color: #1a3a9c; margin-bottom: 6px; }
.ty-step p  { font-size: 0.84rem; color: #666; margin: 0; }

/* ===== CROSS NAVIGATION ===== */
.ty-nav-section { padding: 50px 20px; background: #fff; }
.ty-nav-section h2 {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a3a9c;
  margin-bottom: 8px;
}
.ty-nav-section .ty-sub {
  text-align: center;
  color: #666;
  font-size: 0.93rem;
  margin-bottom: 32px;
}
.ty-courses-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  justify-content: center;
  max-width: 960px;
  margin: 0 auto 40px;
}
.ty-course-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #f0f4ff;
  border: 2px solid transparent;
  border-radius: 12px;
  padding: 18px 16px;
  min-width: 130px;
  text-decoration: none;
  color: #1a3a9c;
  font-weight: 700;
  font-size: 0.88rem;
  transition: all 0.2s;
  text-align: center;
}
.ty-course-card:hover {
  background: #1a3a9c;
  color: #fff !important;
  text-decoration: none;
  transform: translateY(-3px);
  box-shadow: 0 6px 18px rgba(26,58,156,0.25);
}
.ty-course-card .ty-icon { font-size: 1.6rem; margin-bottom: 8px; }
.ty-course-card .ty-exam { font-size: 0.73rem; font-weight: 500; color: #888; margin-top: 4px; }
.ty-course-card:hover .ty-exam { color: rgba(255,255,255,0.75); }

.ty-colleges-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
  max-width: 800px;
  margin: 0 auto;
}
.ty-college-link {
  background: #fff;
  border: 2px solid #e0e6ff;
  border-radius: 8px;
  padding: 10px 18px;
  text-decoration: none;
  color: #333;
  font-size: 0.87rem;
  font-weight: 600;
  transition: all 0.2s;
}
.ty-college-link:hover {
  border-color: #1a3a9c;
  color: #1a3a9c;
  text-decoration: none;
  background: #f0f4ff;
}

.ty-colleges-heading {
  text-align: center;
  font-size: 1.15rem;
  font-weight: 700;
  color: #333;
  margin: 0 auto 16px;
}

/* ===== TRUST STRIP ===== */
.ty-trust-strip {
  background: #0d1b6e;
  color: #fff;
  padding: 30px 20px;
  text-align: center;
}
.ty-trust-strip .ty-trust-items {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: center;
  max-width: 800px;
  margin: 0 auto;
}
.ty-trust-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.95rem;
  color: rgba(255,255,255,0.9);
}
.ty-trust-item i { font-size: 1.4rem; color: #f9a825; }

/* ===== MOBILE ===== */
@media (max-width: 576px) {
  .ty-hero h1 { font-size: 1.7rem; }
  .ty-call-btn { font-size: 1.1rem; padding: 14px 24px; }
  .ty-call-box { margin: -20px 16px 0; }
}
</style>

<!-- Conversion tracked via GTM on thank-you pageview -->
<script>window.dataLayer = window.dataLayer || []; dataLayer.push({'event': 'form_submission', 'page_type': 'thank-you'});</script>
</head>
<body>

<!-- GTM noscript -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5GXCN7Z" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<?php include_once("include/base-nav.php"); ?>

<!-- ===== HERO ===== -->
<section class="ty-hero">
  <div class="ty-check">&#10003;</div>
  <h1>Enquiry Received!</h1>
  <p>We've got your details. Our expert will reach you within 2 hours.</p>
</section>

<!-- ===== URGENT CALL BOX ===== -->
<div class="container">
  <div class="ty-call-box">
    <span class="ty-call-badge">&#9679; Lines Open Now</span>
    <h2>Don't Wait — Call Us Right Now</h2>
    <p>Get <strong>instant answers</strong> on seat availability, cutoffs &amp; counselling steps. Our experts are available <strong>Mon–Sat, 9AM–7PM</strong>.</p>
    <a href="tel:9899991342" class="ty-call-btn">
      <i class="fa fa-phone"></i> 9899991342 — Call Free
    </a>
    <br>
    <a href="https://wa.me/919899991342?text=Hi%2C+I+just+submitted+an+enquiry+for+IPU+Admission+2026.+Please+guide+me." class="ty-whatsapp-btn" target="_blank" rel="noopener">
      <i class="fa fa-whatsapp"></i> WhatsApp Us Instead
    </a>
    <p class="ty-timing"><i class="fa fa-clock-o"></i> Mon–Sat &nbsp;9:00 AM – 7:00 PM &nbsp;|&nbsp; 100% Free Guidance</p>
  </div>
</div>

<!-- ===== WHAT HAPPENS NEXT ===== -->
<section class="ty-steps-section">
  <div class="container">
    <h2>What Happens Next?</h2>
    <div class="ty-steps">
      <div class="ty-step">
        <div class="ty-step-num">1</div>
        <h4>Expert Assigned</h4>
        <p>A dedicated expert is assigned to your enquiry within minutes.</p>
      </div>
      <div class="ty-step">
        <div class="ty-step-num">2</div>
        <h4>Call / WhatsApp</h4>
        <p>They'll call or WhatsApp you to understand your profile &amp; goals.</p>
      </div>
      <div class="ty-step">
        <div class="ty-step-num">3</div>
        <h4>Personalized Plan</h4>
        <p>You receive a free step-by-step admission roadmap tailored for you.</p>
      </div>
      <div class="ty-step">
        <div class="ty-step-num">4</div>
        <h4>Seat Secured</h4>
        <p>We guide you through counselling till your seat is confirmed.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== COURSE CROSS NAVIGATION ===== -->
<section class="ty-nav-section">
  <div class="container">
    <h2>Explore IPU Admission Guides</h2>
    <p class="ty-sub">Read our free guides while you wait for our call</p>

    <div class="ty-courses-grid">
      <a href="IPU-B-Tech-admission-2026.php" class="ty-course-card">
        <span class="ty-icon">&#127979;</span>
        B.Tech
        <span class="ty-exam">via JEE Main</span>
      </a>
      <a href="mba-admission-ip-university.php" class="ty-course-card">
        <span class="ty-icon">&#128200;</span>
        MBA
        <span class="ty-exam">via CAT / CMAT</span>
      </a>
      <a href="IPU-Law-Admission-2026.php" class="ty-course-card">
        <span class="ty-icon">&#9878;</span>
        BA LLB / BBA LLB
        <span class="ty-exam">via CLAT</span>
      </a>
      <a href="ipu-bba-admission.php" class="ty-course-card">
        <span class="ty-icon">&#127891;</span>
        BBA
        <span class="ty-exam">via CUET</span>
      </a>
      <a href="ipu-admission-guide.php" class="ty-course-card">
        <span class="ty-icon">&#128187;</span>
        MCA
        <span class="ty-exam">via NIMCET / CET</span>
      </a>
      <a href="ipu-admission-guide.php" class="ty-course-card">
        <span class="ty-icon">&#128240;</span>
        BJMC
        <span class="ty-exam">via CUET</span>
      </a>
      <a href="IP-University-management-quota-admission-eligibility-criteria.php" class="ty-course-card">
        <span class="ty-icon">&#127942;</span>
        Management Quota
        <span class="ty-exam">Direct Admission</span>
      </a>
      <a href="GGSIPU-counselling-for-B-Tech-admission.php" class="ty-course-card">
        <span class="ty-icon">&#128203;</span>
        IPU Counselling
        <span class="ty-exam">Step-by-Step Guide</span>
      </a>
    </div>

    <p class="ty-colleges-heading">Top IP University Colleges</p>
    <div class="ty-colleges-grid">
      <a href="b-tech-colleges-under-IP-university.php" class="ty-college-link">&#127963; MAIT – Rohini</a>
      <a href="b-tech-colleges-under-IP-university.php" class="ty-college-link">&#127963; MSIT – Janakpuri</a>
      <a href="BPIT.php" class="ty-college-link">&#127963; BPIT – Rohini</a>
      <a href="BVP.php" class="ty-college-link">&#127963; BVP – Paschim Vihar</a>
      <a href="vips-pitampura-courses.php" class="ty-college-link">&#127963; VIPS – Pitampura</a>
    </div>
  </div>
</section>

<!-- ===== TRUST STRIP ===== -->
<div class="ty-trust-strip">
  <div class="ty-trust-items">
    <div class="ty-trust-item"><i class="fa fa-users"></i> 5000+ Students Guided</div>
    <div class="ty-trust-item"><i class="fa fa-star"></i> 10+ Years Experience</div>
    <div class="ty-trust-item"><i class="fa fa-check-circle"></i> 100% Free Guidance</div>
    <div class="ty-trust-item"><i class="fa fa-university"></i> 50+ IPU Colleges Covered</div>
    <div class="ty-trust-item"><i class="fa fa-phone"></i>
      <a href="tel:9899991342" style="color:#f9a825;font-weight:700;text-decoration:none;">Call: 9899991342</a>
    </div>
  </div>
</div>

<?php include_once("include/base-footer.php"); ?>

<!-- Enhanced Conversion Tracking -->
<?php if($email || $phone): ?>
<script>
const hashSHA256 = async (data) => {
  const encoder = new TextEncoder();
  const buffer = await crypto.subtle.digest('SHA-256', encoder.encode(data.trim().toLowerCase()));
  return Array.from(new Uint8Array(buffer)).map(b => b.toString(16).padStart(2,'0')).join('');
};
(async function () {
  const email = "<?php echo htmlspecialchars($email); ?>";
  const phone = "<?php echo htmlspecialchars($phone); ?>";
  const userData = {};
  if(email) userData.email = await hashSHA256(email);
  if(phone) userData.phone_number = await hashSHA256(phone);
  if(Object.keys(userData).length){
    gtag('set','user_data',userData);
    gtag('event','conversion',{'send_to':'AW-10900888879/IVcxCLiB87IbEK-6-c0o'});
  }
})();
</script>
<?php endif; ?>

</body>
</html>