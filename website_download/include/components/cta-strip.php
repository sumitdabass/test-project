<?php
/**
 * CTA Strip Component — Mid-page phone call-to-action
 *
 * Usage:
 *   $cta_heading = "Need Help with B.Tech Admission?";
 *   $cta_subtext = "Get free expert counselling today";
 *   include 'include/components/cta-strip.php';
 */
$cta_heading = $cta_heading ?? 'Need Admission Guidance?';
$cta_subtext = $cta_subtext ?? 'Talk to our expert counsellors for free guidance on IPU admissions';
?>

<section style="background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 100%);padding:40px 0;margin:40px 0">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8 col-md-7 mb-3 mb-md-0">
        <h3 style="color:#fff;margin-bottom:8px;font-size:clamp(1.2rem,2vw,1.6rem)"><?= htmlspecialchars($cta_heading) ?></h3>
        <p style="color:rgba(255,255,255,.75);margin:0;font-size:15px"><?= htmlspecialchars($cta_subtext) ?></p>
      </div>
      <div class="col-lg-4 col-md-5 text-md-end">
        <a href="tel:+919899991342" style="display:inline-flex;align-items:center;gap:10px;background:linear-gradient(135deg,#f59e0b,#FFD700);color:#0d1b6e;padding:14px 32px;border-radius:50px;font-weight:700;font-size:16px;text-decoration:none;box-shadow:0 4px 15px rgba(245,158,11,.3);transition:transform .2s"
           onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="#0d1b6e"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
          Call: 9899991342
        </a>
      </div>
    </div>
  </div>
</section>
