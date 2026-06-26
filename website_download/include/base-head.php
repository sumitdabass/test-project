<?php include_once __DIR__ . '/image-helper.php'; // webp_img()/responsive_img() available site-wide ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#0d1b6e">
<link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" href="/assets/images/favicon.ico">

<!-- Open Graph fallback — pages with their own og:image take precedence -->
<meta property="og:image" content="<?= htmlspecialchars($og_image ?? 'https://ipu.co.in/assets/images/IP-University-b-tech-admission.jpg', ENT_QUOTES) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="IPU Admission Guide — GGSIPU counselling, cutoffs, fees, management seats">
<meta property="og:site_name" content="IPU Admission Guide">
<meta property="og:locale" content="en_IN">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="<?= htmlspecialchars($og_image ?? 'https://ipu.co.in/assets/images/IP-University-b-tech-admission.jpg', ENT_QUOTES) ?>">

<!-- Critical CSS (inlined for fast first paint) -->
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',system-ui,-apple-system,sans-serif;font-weight:400;color:#1a1a2e;line-height:1.7;background:#fff}
img{max-width:100%}
a{display:inline-block;text-decoration:none;color:#1a3a9c;transition:color .2s}
a:hover{color:#0d1b6e}
h1,h2,h3,h4,h5,h6{font-family:'Inter',system-ui,-apple-system,sans-serif;color:#0d1b6e;font-weight:700;line-height:1.3}
h1{font-size:clamp(2rem,5vw,3.2rem)}
h2{font-size:clamp(1.5rem,3vw,2.4rem)}
h3{font-size:clamp(1.25rem,2.5vw,1.8rem)}
p{color:#4a5568;margin-bottom:1rem}
.container{max-width:1200px;margin:0 auto;padding:0 15px}

/* Header & Nav */
.header-area{position:relative;z-index:100}
.header-nav{background:#fff;padding:10px 0;transition:all .3s ease}
.header-nav.sticky{position:fixed;top:0;left:0;right:0;background:#fff;box-shadow:0 2px 20px rgba(0,0,0,.08);z-index:1000;animation:slideDown .3s ease}
@keyframes slideDown{from{transform:translateY(-100%)}to{transform:translateY(0)}}
.navigation{display:flex;align-items:center;justify-content:space-between}
.navbar{padding:0}
.navbar-brand img{height:45px}
.nav-link{color:#1a1a2e;font-weight:500;font-size:15px;padding:8px 16px!important;border-radius:6px;transition:all .2s}
.nav-link:hover,.nav-link.active{color:#1a3a9c;background:rgba(26,58,156,.06)}

/* Phone Bar */
.ipu-phone-bar{background:#0d1b6e;color:#fff;text-align:right;padding:8px 20px;font-size:13px;font-weight:600;letter-spacing:.3px}
.ipu-phone-bar a{color:#f59e0b;text-decoration:none;font-weight:700}
.ipu-phone-bar a:hover{color:#FFD700}
@media(max-width:768px){.ipu-phone-bar{text-align:center;font-size:12px}}

/* Navbar Toggler */
.navbar-toggler{border:none;padding:8px;background:transparent}
.navbar-toggler:focus{box-shadow:none}
.toggler-icon{display:block;width:24px;height:2px;background:#1a1a2e;margin:5px 0;transition:all .3s ease;border-radius:2px}
.navbar-toggler.active .toggler-icon:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
.navbar-toggler.active .toggler-icon:nth-child(2){opacity:0}
.navbar-toggler.active .toggler-icon:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}

/* Phone CTA Button in Nav */
.nav-phone-btn{display:inline-flex;align-items:center;gap:8px;background:#e65c00;color:#fff!important;padding:10px 20px;border-radius:50px;font-weight:700;font-size:14px;text-decoration:none;transition:all .2s;box-shadow:0 2px 8px rgba(230,92,0,.3)}
.nav-phone-btn:hover{background:#cc5200;color:#fff!important;transform:translateY(-1px);box-shadow:0 4px 12px rgba(230,92,0,.4)}
.nav-phone-btn svg{width:16px;height:16px;fill:currentColor}

/* Override ALL old theme nav/header styles */
.header-area,.header-area.header-absolute{position:relative!important}
.header-nav{position:relative!important;top:0!important;padding:0!important;background:#fff!important}
.header-nav .navbar{padding:8px 0!important}
.header-nav .navbar .navbar-nav .nav-item a,
.header-nav .navigation .navbar .navbar-nav .nav-item a{line-height:normal!important;font-size:14px!important;color:#1a1a2e!important;padding:8px 12px!important;margin:0 2px!important;border-radius:6px;display:block!important}
.header-nav .navbar .navbar-nav .nav-item a:hover,
.header-nav .navbar .navbar-nav .nav-item a.active,
.header-nav .navigation .navbar .navbar-nav .nav-item a:hover,
.header-nav .navigation .navbar .navbar-nav .nav-item a.active{color:#1a3a9c!important;background:rgba(26,58,156,.06)}
.navbar-brand{padding:0!important;margin-right:16px!important}
.navbar-brand img{display:none!important}
.banner-area{margin-top:0!important}

/* Desktop: show nav inline */
@media(min-width:992px){
.header-nav .navbar .navbar-collapse,
.header-nav .navigation .navbar .navbar-collapse{position:static!important;background:none!important;box-shadow:none!important;padding:0!important;display:flex!important;flex-basis:auto!important}
}
/* Mobile: dropdown menu */
@media(max-width:991px){
.header-nav .navbar .navbar-collapse,
.header-nav .navigation .navbar .navbar-collapse{position:absolute!important;top:100%!important;left:0!important;right:0!important;background:#0d1b6e!important;padding:16px!important;box-shadow:0 10px 30px rgba(0,0,0,.2)!important;z-index:1000!important;border-radius:0 0 12px 12px}
.header-nav .navbar .navbar-collapse:not(.show),
.header-nav .navigation .navbar .navbar-collapse:not(.show){display:none!important}
.header-nav .navbar .navbar-collapse.show,
.header-nav .navigation .navbar .navbar-collapse.show{display:block!important}
.header-nav .navbar .navbar-nav .nav-item a,
.header-nav .navigation .navbar .navbar-nav .nav-item a{color:#fff!important;padding:12px 16px!important;font-size:15px!important;border-radius:8px}
.header-nav .navbar .navbar-nav .nav-item a:hover,
.header-nav .navbar .navbar-nav .nav-item a.active{color:#f59e0b!important;background:rgba(255,255,255,.1)}
.nav-phone-btn{display:none!important}
.navbar-toggler{display:block!important}
}

/* Hero Banner */
.hero-section{background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 60%,#2a5ac8 100%);color:#fff;padding:80px 0 60px;position:relative;overflow:hidden}
.hero-section h1{color:#fff;margin-bottom:16px}
.hero-section p{color:rgba(255,255,255,.85);font-size:1.1rem}
.hero-compact{padding:40px 0 30px}
.hero-compact h1{font-size:clamp(1.5rem,3vw,2.2rem)}

/* Preloader */
#preloader{position:fixed;top:0;left:0;width:100%;height:100%;background:#fff;z-index:99999;display:flex;align-items:center;justify-content:center}
#preloader .spinner{width:40px;height:40px;border:3px solid #f0f4ff;border-top-color:#1a3a9c;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ===== ipu design tokens ===== */
:root{
  --ipu-ink:#0d1b6e;
  --ipu-ink-2:#1a3a9c;
  --ipu-ink-3:#2a5ac8;
  --ipu-amber:#f59e0b;
  --ipu-orange:#e65c00;
  --ipu-orange-hover:#cc5200;
  --ipu-bg:#f8faff;
  --ipu-paper:#fff;
  --ipu-rule:#e2e8f0;
  --ipu-rule-soft:#d0d9f0;
  --ipu-highlight:#e8f0ff;
  --ipu-accent-soft:#fff3e0;
  --ipu-shadow-sm:0 2px 8px rgba(13,27,110,.06);
  --ipu-shadow-md:0 8px 24px rgba(13,27,110,.10);
  --ipu-shadow-lg:0 20px 60px rgba(13,27,110,.18);
  --ipu-cta-shadow:0 3px 12px rgba(230,92,0,.30);
  --ipu-radius:12px;
  --ipu-radius-lg:16px;
}

/* ===== ipu primitives ===== */
.ipu-input{width:100%;min-height:44px;padding:12px 16px;border:1px solid var(--ipu-rule);border-radius:8px;font-size:16px;font-family:inherit;color:var(--ipu-ink);background:#fff;transition:border-color .2s,box-shadow .2s;margin-bottom:10px;display:block}
.ipu-input:focus{outline:none;border-color:var(--ipu-ink-2);box-shadow:0 0 0 3px rgba(26,58,156,.14)}
.ipu-input::placeholder{color:#94a3b8}
select.ipu-input{color:#64748b}
.ipu-btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:14px 22px;background:var(--ipu-orange);color:#fff;border:none;border-radius:8px;font-family:inherit;font-size:16px;font-weight:700;cursor:pointer;transition:background .2s;box-shadow:var(--ipu-cta-shadow);text-decoration:none}
.ipu-btn-primary:hover{background:var(--ipu-orange-hover);color:#fff}

/* ===== sidebar-enquiry component ===== */
.ipu-enquiry{display:flex;flex-direction:column;gap:14px}
.ipu-enquiry__phone{background:linear-gradient(135deg,var(--ipu-ink) 0%,var(--ipu-ink-2) 100%);color:#fff;padding:20px 22px;border-radius:var(--ipu-radius);position:relative;overflow:hidden}
.ipu-enquiry__phone::before{content:"";position:absolute;right:-30px;top:-30px;width:110px;height:110px;background:radial-gradient(circle,rgba(245,158,11,.20),transparent 65%)}
.ipu-enquiry__phone-badge{display:inline-flex;align-items:center;gap:6px;font-size:10.5px;letter-spacing:.18em;text-transform:uppercase;color:var(--ipu-amber);font-weight:700;margin-bottom:8px}
.ipu-enquiry__phone-badge::before{content:"";width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.25);animation:ipuPulse 1.6s ease-in-out infinite;display:inline-block}
@keyframes ipuPulse{50%{box-shadow:0 0 0 6px rgba(34,197,94,.10)}}
.ipu-enquiry__phone-label{font-size:12.5px;color:rgba(255,255,255,.75);margin:0 0 6px;line-height:1.4}
.ipu-enquiry__phone-num{display:flex;align-items:center;gap:10px;color:var(--ipu-amber);font-weight:700;font-size:26px;line-height:1;margin-bottom:6px;text-decoration:none}
.ipu-enquiry__phone-num:hover{color:var(--ipu-amber)}
.ipu-enquiry__phone-hours{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.5);font-weight:600}
.ipu-enquiry__form-wrap{background:var(--ipu-paper);border:1px solid var(--ipu-rule);border-radius:var(--ipu-radius-lg);padding:22px;box-shadow:var(--ipu-shadow-md)}
.ipu-enquiry__heading{font-size:1.1rem;color:var(--ipu-ink);margin:0 0 4px;text-align:center;font-weight:700}
.ipu-enquiry__subheading{font-size:13px;color:#64748b;text-align:center;margin:0 0 14px}
.ipu-enquiry__error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:8px 12px;border-radius:6px;font-size:13px;margin-bottom:12px}
.ipu-enquiry__submit{width:100%;margin-top:4px;font-size:16px}
.ipu-enquiry__fine{font-size:11px;color:#94a3b8;text-align:center;margin:10px 0 0}
.ipu-enquiry__popular{background:var(--ipu-highlight);padding:18px 22px;border-radius:var(--ipu-radius)}
.ipu-enquiry__popular h4{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--ipu-orange);font-weight:700;margin:0 0 12px}
.ipu-enquiry__popular ul{list-style:none;padding:0;margin:0}
.ipu-enquiry__popular li{border-top:1px solid rgba(13,27,110,.10)}
.ipu-enquiry__popular li:first-child{border-top:0}
.ipu-enquiry__popular a{display:flex;justify-content:space-between;align-items:center;padding:9px 0;color:var(--ipu-ink);font-size:13.5px;font-weight:500;line-height:1.4;text-decoration:none}
.ipu-enquiry__popular a:hover{color:var(--ipu-orange)}

/* ===== page-hero component ===== */
.ipu-page-hero{background:linear-gradient(135deg,var(--ipu-ink) 0%,var(--ipu-ink-2) 60%,var(--ipu-ink-3) 100%);color:#fff;padding:64px 0 56px;position:relative;overflow:hidden}
.ipu-page-hero h1,.ipu-page-hero p,.ipu-page-hero a{color:#fff}
.ipu-page-hero__crumbs{font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.65);margin-bottom:14px}
.ipu-page-hero__crumbs ol{list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:8px}
.ipu-page-hero__crumbs li::after{content:"/";margin-left:8px;color:rgba(255,255,255,.35)}
.ipu-page-hero__crumbs li:last-child::after{content:""}
.ipu-page-hero__crumbs a{color:rgba(255,255,255,.85);text-decoration:none}
.ipu-page-hero__crumbs a:hover{color:var(--ipu-amber)}
.ipu-page-hero__kicker{font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.7);margin:0 0 10px;font-weight:600}
.ipu-page-hero__h1{font-size:clamp(1.85rem,4.5vw,2.8rem);line-height:1.15;margin:0 0 16px;font-weight:700}
.ipu-page-hero__h1 em{font-style:italic;font-weight:400;color:var(--ipu-amber)}
.ipu-page-hero__chips{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.ipu-page-hero__chips span{background:rgba(255,255,255,.1);padding:6px 14px;border-radius:20px;font-size:13px;color:rgba(255,255,255,.9)}
.ipu-page-hero__intro{font-size:16px;line-height:1.7;color:rgba(255,255,255,.85);max-width:560px;margin:0 0 18px}
.ipu-page-hero__intro a{color:var(--ipu-amber);font-weight:600}
.ipu-page-hero__call{display:inline-flex;align-items:center;gap:8px;padding:13px 24px;font-weight:700;font-size:15px}
@media(max-width:991px){.ipu-page-hero{padding:48px 0 32px}.ipu-page-hero__h1{font-size:clamp(1.5rem,5.5vw,2rem)}}

/* Inner-page Banner (banner-three) — inlined to prevent CLS from deferred bundle.min.css */
.bg_cover{background-position:center center;background-size:cover;background-repeat:no-repeat}
.banner-area{position:relative}
.banner-area.banner-three,.banner-area.banner-three.mt-0{height:auto;min-height:300px;padding-top:120px;padding-bottom:50px;background:#0b2c5d;color:#fff}
.banner-area.banner-three h1,.banner-area.banner-three h2,.banner-area.banner-three h3,.banner-area.banner-three h4,.banner-area.banner-three h5,.banner-area.banner-three h6,.banner-area.banner-three p{color:#fff}
.banner-shape{position:absolute;left:0;top:0;width:100%;height:100%;z-index:-1}
.ft-35{font-size:35px;line-height:1.25}
.white{color:#fff}
@media only screen and (min-width:768px) and (max-width:991px){.banner-area.banner-three,.banner-area.banner-three.mt-0{min-height:250px;padding-top:100px;padding-bottom:40px}}
@media (max-width:767px){.banner-area.banner-three,.banner-area.banner-three.mt-0{min-height:200px;padding-top:90px;padding-bottom:30px}}

/* Mobile Call CTA */
@media(max-width:768px){
  .mobile-call-cta{position:fixed;bottom:0;left:0;right:0;background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 100%);padding:12px 16px calc(12px + env(safe-area-inset-bottom));z-index:9999;box-shadow:0 -2px 10px rgba(0,0,0,.3)}
  .mobile-call-btn{display:flex;align-items:center;justify-content:center;gap:8px;min-height:48px;background:linear-gradient(135deg,#f59e0b 0%,#FFD700 100%);border:none;padding:12px;border-radius:50px;color:#0d1b6e;font-weight:700;font-size:16px;text-decoration:none;width:100%;box-shadow:0 2px 8px rgba(0,0,0,.2)}
  body{padding-bottom:calc(68px + env(safe-area-inset-bottom))}
}
@media(min-width:769px){.mobile-call-cta{display:none}}
/* go-top: 48px tap target; lift above mobile-call-cta on small screens */
.go-top{width:48px;height:48px;}
@media (max-width:768px){.go-top{width:48px;height:48px;bottom:calc(96px + env(safe-area-inset-bottom)) !important;z-index:9998;}}
</style>

<!-- Self-hosted Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" onload="this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>

<!-- Bootstrap 5 CSS -->
<link rel="stylesheet" href="/assets/css/bootstrap5.min.css">

<!-- Main CSS Bundle (deferred) — pages can opt out by setting $skip_legacy_css = true before the include -->
<?php if (empty($skip_legacy_css)): ?>
<link rel="stylesheet" href="/assets/css/bundle.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/assets/css/bundle.min.css"></noscript>
<?php endif; ?>

<!-- Google Tag Manager -->
<script>
(function(w,d,s,l,i){
  w[l]=w[l]||[];
  w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
  var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
  j.async=true;
  j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
  f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5GXCN7Z');
</script>

<!-- All tracking managed by GTM: AW-10900888879, G-9VS3CTJ8SV, Meta Pixel, Clarity, WCM -->

<!-- phone_click custom event for tel: links (GTM → GA4 conversion) -->
<script>
document.addEventListener('click', function(e) {
  var a = e.target.closest('a[href^="tel:"]');
  if (!a) return;
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: 'phone_click' });
});
</script>

</head>
