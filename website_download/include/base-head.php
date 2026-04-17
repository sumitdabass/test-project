<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="/assets/images/favicon.ico" type="image/x-icon">

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

/* Mobile Call CTA */
@media(max-width:768px){
  .mobile-call-cta{position:fixed;bottom:0;left:0;right:0;background:linear-gradient(135deg,#0d1b6e 0%,#1a3a9c 100%);padding:12px 16px;z-index:9999;box-shadow:0 -2px 10px rgba(0,0,0,.3)}
  .mobile-call-btn{display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#f59e0b 0%,#FFD700 100%);border:none;padding:12px;border-radius:50px;color:#0d1b6e;font-weight:700;font-size:15px;text-decoration:none;width:100%;box-shadow:0 2px 8px rgba(0,0,0,.2)}
  body{padding-bottom:68px}
}
@media(min-width:769px){.mobile-call-cta{display:none}}

/* Desktop Call Widget */
@media(min-width:769px){
  .desktop-call-widget{position:fixed;right:20px;bottom:80px;background:#0d1b6e;padding:20px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.2);z-index:999;text-align:center;max-width:220px}
  .desktop-call-widget p{color:#fff;font-size:13px;margin:6px 0}
  .desktop-call-widget .widget-call-btn{display:inline-block;background:linear-gradient(135deg,#f59e0b 0%,#FFD700 100%);padding:10px 22px;border-radius:50px;color:#0d1b6e;font-weight:700;text-decoration:none;font-size:14px;margin-top:8px;transition:transform .2s}
  .desktop-call-widget .widget-call-btn:hover{transform:scale(1.05)}
}
@media(max-width:768px){.desktop-call-widget{display:none}}
</style>

<!-- Self-hosted Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" onload="this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>

<!-- Bootstrap 5 CSS -->
<link rel="stylesheet" href="/assets/css/bootstrap5.min.css">

<!-- Main CSS Bundle (deferred) -->
<link rel="stylesheet" href="/assets/css/bundle.min.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/assets/css/bundle.min.css"></noscript>

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
