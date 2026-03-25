<?php
// base-nav.php — Modern minimal navigation with phone bar
// Replaces: header.php, header2.php, call-widgets.php
?>

<!-- GTM noscript fallback -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5GXCN7Z" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<!-- Preloader -->
<div id="preloader"><div class="spinner"></div></div>


<!-- Header -->
<header class="header-area">
  <div class="header-nav">
    <div class="container">
      <nav class="navbar navbar-expand-lg" style="display:flex;justify-content:space-between;align-items:center">

        <!-- Brand Name (no logo) -->
        <a class="navbar-brand" href="/" style="text-decoration:none;font-family:'Poppins',sans-serif;font-weight:700;font-size:16px;color:#0d1b6e">
          IPU Admission Guide
        </a>

        <!-- Mobile Hamburger Menu (right-aligned) -->
        <button class="navbar-toggler" type="button" id="mobileMenuBtn" order="2"
                aria-label="Toggle navigation"
                style="display:none;border:none;background:none;padding:8px;cursor:pointer;margin-left:auto">
          <svg id="hamburgerIcon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0d1b6e" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
          <svg id="closeIcon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0d1b6e" stroke-width="2.5" stroke-linecap="round" style="display:none">
            <line x1="6" y1="6" x2="18" y2="18"/>
            <line x1="6" y1="18" x2="18" y2="6"/>
          </svg>
        </button>
        <style>
          @media(max-width:991px){#mobileMenuBtn{display:block!important}}
        </style>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>"
                 href="/">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ipu-admission-guide.php' ? 'active' : '' ?>"
                 href="/ipu-admission-guide.php">Admissions</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'IP-University-management-quota-admission-eligibility-criteria.php' ? 'active' : '' ?>"
                 href="/IP-University-management-quota-admission-eligibility-criteria.php">Management Quota</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'GGSIPU-counselling-for-B-Tech-admission.php' ? 'active' : '' ?>"
                 href="/GGSIPU-counselling-for-B-Tech-admission.php">Counselling</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ipu-colleges-list.php' ? 'active' : '' ?>"
                 href="/ipu-colleges-list.php">Colleges</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ipu-helpline-contact-number.php' ? 'active' : '' ?>"
                 href="/ipu-helpline-contact-number.php">Helpline</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : '' ?>"
                 href="/blog.php">Blog</a>
            </li>
          </ul>

        </div>

      </nav>
    </div>
  </div>
</header>

<!-- Mobile Sticky Call CTA -->
<div class="mobile-call-cta" id="mobileCallCTA">
  <a href="tel:+919899991342" class="mobile-call-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="#0d1b6e"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    CALL: 9899991342
  </a>
</div>

<!-- Mobile Menu Toggle Script -->
<script>
(function(){
  var btn = document.getElementById('mobileMenuBtn');
  var nav = document.getElementById('mainNav');
  var hamburger = document.getElementById('hamburgerIcon');
  var close = document.getElementById('closeIcon');
  if(btn && nav){
    btn.addEventListener('click', function(){
      var isOpen = nav.classList.contains('show');
      if(isOpen){
        nav.classList.remove('show');
        hamburger.style.display='block';
        close.style.display='none';
      } else {
        nav.classList.add('show');
        hamburger.style.display='none';
        close.style.display='block';
      }
    });
    // Close menu when a link is clicked
    nav.querySelectorAll('a').forEach(function(link){
      link.addEventListener('click', function(){
        nav.classList.remove('show');
        hamburger.style.display='block';
        close.style.display='none';
      });
    });
  }
})();
</script>

<!-- Desktop Call Widget -->
<div class="desktop-call-widget" id="desktopCallWidget">
  <p><strong>Need Admission Help?</strong></p>
  <p>Expert guidance for B.Tech, BBA, Law &amp; MBA</p>
  <a href="tel:+919899991342" class="widget-call-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="#0d1b6e" style="vertical-align:middle;margin-right:4px"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    Call Now
  </a>
</div>
