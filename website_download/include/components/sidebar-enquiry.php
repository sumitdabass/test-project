<?php
// include/components/sidebar-enquiry.php
// Unified enquiry sidebar — visual sibling of the index.php hero form.
// Form contract MUST match form-handler.php / sendemail.php exactly:
//   POST /sendemail.php
//   fields: name, phone, email, course, page_url, website (honeypot)
// Locals (all optional):
//   $enquiry_heading       string  — card heading, default "Get Free Admission Guidance"
//   $enquiry_subheading    string  — card subheading
//   $enquiry_show_phone    bool    — render the navy phone block above the form (default true)
//   $enquiry_show_popular  bool    — render the popular-guides list below the form (default true)
//   $enquiry_popular       array   — list of [label, url] tuples for the popular block

$enquiry_heading      = $enquiry_heading      ?? 'Get Free Admission Guidance';
$enquiry_subheading   = $enquiry_subheading   ?? 'No charges. Our expert team will call you.';
$enquiry_show_phone   = $enquiry_show_phone   ?? true;
$enquiry_show_popular = $enquiry_show_popular ?? true;
$enquiry_popular      = $enquiry_popular      ?? [
    ['B.Tech Admission 2026', '/IPU-B-Tech-admission-2026.php'],
    ['MBA Admission Guide',   '/mba-admission-ip-university.php'],
    ['Law Admission 2026',    '/IPU-Law-Admission.php'],
    ['BBA Admission Guide',   '/ipu-bba-admission.php'],
    ['Management Quota',      '/IP-University-management-quota-admission-eligibility-criteria.php'],
];

// form-handler.php may set $form_error; tolerate it being unset.
$form_error = $form_error ?? null;
?>
<aside class="ipu-enquiry">

  <?php if ($enquiry_show_phone): ?>
  <div class="ipu-enquiry__phone">
    <span class="ipu-enquiry__phone-badge">Counsellors online</span>
    <p class="ipu-enquiry__phone-label">Talk to our admission team</p>
    <a class="ipu-enquiry__phone-num" href="tel:+919899991342">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
      9899991342
    </a>
    <span class="ipu-enquiry__phone-hours">Mon–Sat · 9 AM – 7 PM</span>
  </div>
  <?php endif; ?>

  <div class="ipu-enquiry__form-wrap">
    <h3 class="ipu-enquiry__heading"><?= htmlspecialchars($enquiry_heading) ?></h3>
    <p class="ipu-enquiry__subheading"><?= htmlspecialchars($enquiry_subheading) ?></p>

    <?php if ($form_error): ?>
      <div class="ipu-enquiry__error"><?= htmlspecialchars($form_error) ?></div>
    <?php endif; ?>

    <form class="ipu-enquiry__form enquiry-form" method="POST" action="/sendemail.php" novalidate>
      <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
      </div>
      <input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

      <input class="ipu-input" type="text"  name="name"  placeholder="Full Name"           required autocomplete="name">
      <input class="ipu-input" type="tel"   name="phone" placeholder="Phone Number"        required inputmode="tel" autocomplete="tel" pattern="[6-9][0-9]{9}" maxlength="10">
      <input class="ipu-input" type="email" name="email" placeholder="Email (optional)"    autocomplete="email">

      <select class="ipu-input" name="course" required>
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
        <option value="Management Quota">Management Quota</option>
        <option value="Counselling">Admission Help</option>
        <option value="Other">Other</option>
      </select>

      <button class="ipu-btn-primary ipu-enquiry__submit" type="submit">Request a Callback</button>
      <p class="ipu-enquiry__fine">100% Free. No spam, ever.</p>
    </form>
  </div>

  <?php if ($enquiry_show_popular && !empty($enquiry_popular)): ?>
  <div class="ipu-enquiry__popular">
    <h4>Popular Guides</h4>
    <ul>
      <?php foreach ($enquiry_popular as $p): ?>
        <li><a href="<?= htmlspecialchars($p[1]) ?>"><?= htmlspecialchars($p[0]) ?> <span aria-hidden="true">→</span></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

</aside>
