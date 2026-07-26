<?php
// base-nav.php — Modern minimal navigation with phone bar
// Replaces: header.php, header2.php, call-widgets.php
?>

<style>
  :root{ --action:#ff7a1a; --action-label:#0d1b6e; --ink:#0d1b6e; --border-strong:#d4d3df; }

  /* Desktop header call button */
  .nav-call-btn{display:none}
  @media(min-width:992px){
    .nav-call-btn{display:inline-flex;align-items:center;gap:7px;background:var(--action);color:var(--action-label);
      font-weight:800;font-size:14px;line-height:1;text-decoration:none;padding:11px 18px;border-radius:999px;
      white-space:nowrap;flex:0 0 auto;transition:filter .15s ease}
    .nav-call-btn:hover{filter:brightness(.92);color:var(--action-label)}
    .nav-call-btn svg{fill:var(--action-label)}
    .header-area .navbar-nav.mx-auto{margin-right:12px !important}
  }

  /* Recolour ONLY call-type primary buttons (hero / cta-strip / hero-banner) — icon is currentColor */
  .ipu-btn-primary[href^="tel:"]{background:var(--action) !important;color:var(--action-label) !important}
  .ipu-btn-primary[href^="tel:"] svg{fill:var(--action-label)}

  /* College-block call button (injected by JS on ranked pages) */
  .cb-call-btn{display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:var(--action);
    color:var(--action-label);font-weight:800;font-size:14px;text-decoration:none;padding:9px 16px;border-radius:999px}
  .cb-call-btn svg{fill:var(--action-label)}

  /* Mobile sticky bar — Call(7) + Enquire(3), mobile only */
  @media(max-width:768px){
    .mobile-call-cta{background:#fff !important;padding:10px 12px calc(10px + env(safe-area-inset-bottom)) !important;
      border-top:1px solid var(--border-strong);box-shadow:0 -2px 10px rgba(5,0,56,.08) !important;
      display:flex !important;gap:8px;align-items:stretch}
    .mobile-call-btn{flex:7 1 0;width:auto !important;background:var(--action) !important;color:var(--action-label) !important;
      border-radius:999px !important;min-height:50px;font-size:16px;font-weight:800;box-shadow:none !important;margin:0}
    .mobile-call-btn svg{fill:var(--action-label)}
    .mobile-enquire-btn{flex:3 1 0;display:flex;align-items:center;justify-content:center;min-height:50px;
      background:#fff;border:1.5px solid var(--ink);border-radius:999px;color:var(--ink);font-weight:800;
      font-size:15px;text-decoration:none;white-space:nowrap}
    body{padding-bottom:74px}
  }
  @media(min-width:769px){ .mobile-call-cta{display:none !important} }
</style>

<!-- GTM noscript fallback -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5GXCN7Z" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

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
              <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/news/') === 0 ? 'active' : '' ?>"
                 href="/news/">News</a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'active' : '' ?>"
                 href="/blog.php">Blog</a>
            </li>
          </ul>

        </div>

        <!-- Desktop header call button -->
        <a class="nav-call-btn" href="tel:+919899991342" data-cta-src="header">
          <svg width="17" height="17" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
          Call now
        </a>

      </nav>
    </div>
  </div>
</header>

<!-- Mobile Sticky Call CTA — Call (7) + Enquire (3) -->
<div class="mobile-call-cta" id="mobileCallCTA">
  <a href="tel:+919899991342" class="mobile-call-btn" data-cta-src="sticky">
    <svg width="18" height="18" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
    Call a counsellor
  </a>
  <a href="#enquiry-form" class="mobile-enquire-btn" data-cta-src="sticky-enquire">Enquire</a>
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
<script>
(function(){
  document.addEventListener('click', function(e){
    var el = e.target.closest && e.target.closest('[data-cta-src]');
    if(!el) return;
    var src = el.getAttribute('data-cta-src') || 'unknown';
    var evt = src.indexOf('enquire') > -1 ? 'cta_enquire' : 'cta_call';
    if(typeof window.gtag === 'function'){
      window.gtag('event', evt, { cta_src: src, page_path: location.pathname });
    } else if(window.dataLayer){
      window.dataLayer.push({ event: evt, cta_src: src, page_path: location.pathname });
    }
  }, true);
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var blocks = document.querySelectorAll('.college-block');
  if(!blocks.length) return;
  blocks.forEach(function(b){
    if(b.querySelector('.cb-call-btn')) return;
    var h = b.querySelector('h3');
    var name = h ? h.textContent.replace(/^\s*\d+\s*/,'').split('–')[0].trim() : 'this college';
    var a = document.createElement('a');
    a.className = 'cb-call-btn';
    a.href = 'tel:+919899991342';
    a.setAttribute('data-cta-src', 'row');
    a.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg> Ask about ' + name;
    b.appendChild(a);
  });
});
</script>

