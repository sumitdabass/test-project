<?php
/**
 * Sidebar CTA Component — Phone CTA + Enquiry Form + Popular Guides
 * Replaces: bigin-sidebar-form.php, blog-sidebar.php, banner-enquiry.php
 *
 * Usage: include 'include/sidebar-cta.php';
 */

// Generate form timestamp for anti-spam
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['form_loaded_at'] = time();
?>

<!-- Phone CTA Card -->
<div style="background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 100%);border-radius:12px;padding:24px;text-align:center;margin-bottom:20px">
  <p style="color:rgba(255,255,255,.8);font-size:14px;margin-bottom:4px">Talk to Our Expert Team</p>
  <a href="tel:+919899991342" style="display:inline-flex;align-items:center;gap:8px;color:#f59e0b;font-size:22px;font-weight:700;text-decoration:none;margin-bottom:8px">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="#f59e0b"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    9899991342
  </a>
  <p style="color:rgba(255,255,255,.5);font-size:12px;margin:0">Mon–Sat, 9 AM – 7 PM</p>
</div>

<!-- Enquiry Form -->
<div id="enquiry-form" style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px">
  <h4 style="font-size:16px;color:#0d1b6e;margin-bottom:16px;text-align:center">Get Free Expert Guidance</h4>

  <form method="POST" action="/sendemail.php" class="enquiry-form" novalidate>
    <!-- Honeypot (hidden from humans) -->
    <div style="position:absolute;left:-9999px" aria-hidden="true">
      <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    <input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div style="margin-bottom:12px">
      <input type="text" name="name" placeholder="Your Name" required autocomplete="name"
             style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;transition:border-color .2s"
             onfocus="this.style.borderColor='#1a3a9c'" onblur="this.style.borderColor='#e2e8f0'">
    </div>

    <div style="margin-bottom:12px">
      <input type="tel" name="phone" placeholder="Phone Number" required inputmode="tel" autocomplete="tel"
             pattern="[6-9][0-9]{9}" maxlength="10"
             style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;transition:border-color .2s"
             onfocus="this.style.borderColor='#1a3a9c'" onblur="this.style.borderColor='#e2e8f0'">
    </div>

    <div style="margin-bottom:12px">
      <input type="email" name="email" placeholder="Email (optional)" autocomplete="email"
             style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;transition:border-color .2s"
             onfocus="this.style.borderColor='#1a3a9c'" onblur="this.style.borderColor='#e2e8f0'">
    </div>

    <div style="margin-bottom:16px">
      <select name="course" required class="form-select"
              style="width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;color:#4a5568;background:#fff">
        <option value="">Select Course</option>
        <option value="B.Tech">B.Tech</option>
        <option value="MBA">MBA</option>
        <option value="BBA">BBA</option>
        <option value="BA LLB">BA LLB (Law)</option>
        <option value="BBA LLB">BBA LLB (Law)</option>
        <option value="MCA">MCA</option>
        <option value="BCA">BCA</option>
        <option value="BJMC">BJMC</option>
        <option value="B.Com">B.Com</option>
        <option value="BA Economics">BA Economics</option>
        <option value="BA English">BA English</option>
        <option value="B.Ed">B.Ed</option>
        <option value="LLB">LLB (3 Year)</option>
        <option value="LLM">LLM</option>
        <option value="B.Arch">B.Arch</option>
        <option value="Other">Other</option>
      </select>
    </div>

    <button type="submit"
            style="width:100%;padding:12px;background:#e65c00;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;transition:background .2s"
            onmouseover="this.style.background='#cc5200'" onmouseout="this.style.background='#e65c00'">
      Request a Callback
    </button>

    <p style="font-size:11px;color:#94a3b8;text-align:center;margin-top:10px;margin-bottom:0">
      Free guidance. No spam.
    </p>
  </form>
  <script>
  document.querySelector('.enquiry-form').addEventListener('submit', function(e) {
    var btn = this.querySelector('button[type="submit"]');
    if (btn.disabled) { e.preventDefault(); return false; }
    btn.disabled = true;
    btn.textContent = 'Submitting...';
    btn.style.opacity = '0.7';
  });
  </script>
</div>

<!-- Popular Guides -->
<div style="margin-top:20px;background:#f8faff;border-radius:12px;padding:20px">
  <h4 style="font-size:15px;color:#0d1b6e;margin-bottom:12px">Popular Guides</h4>
  <ul style="list-style:none;padding:0;margin:0">
    <li style="margin-bottom:8px"><a href="/IPU-B-Tech-admission-2026.php" style="font-size:13px;color:#1a3a9c;text-decoration:none">→ B.Tech Admission 2026</a></li>
    <li style="margin-bottom:8px"><a href="/mba-admission-ip-university.php" style="font-size:13px;color:#1a3a9c;text-decoration:none">→ MBA Admission Guide</a></li>
    <li style="margin-bottom:8px"><a href="/IPU-Law-Admission-2026.php" style="font-size:13px;color:#1a3a9c;text-decoration:none">→ Law Admission 2026</a></li>
    <li style="margin-bottom:8px"><a href="/ipu-bba-admission.php" style="font-size:13px;color:#1a3a9c;text-decoration:none">→ BBA Admission Guide</a></li>
    <li style="margin-bottom:0"><a href="/IP-University-management-quota-admission-eligibility-criteria.php" style="font-size:13px;color:#1a3a9c;text-decoration:none">→ Management Quota</a></li>
  </ul>
</div>
