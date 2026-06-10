<!-- ===== Sticky Call Widgets (Mobile + Desktop) ===== -->
<style>
  @media (max-width: 768px) {
    .mobile-call-cta {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(135deg, #0b2c5d 0%, #1a4d8a 100%);
      padding: 12px 10px;
      z-index: 9999;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.3);
    }
    .call-btn-container {
      display: flex;
      gap: 10px;
    }
    .mobile-call-btn {
      flex: 1;
      background: linear-gradient(135deg, #FFD700 0%, #FFC700 100%);
      border: none;
      padding: 12px 15px;
      border-radius: 25px;
      color: #0b2c5d;
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: transform 0.2s;
    }
    .mobile-call-btn:active { transform: scale(0.98); }
    .mobile-call-btn:hover { background: #FFD700; }
    body { padding-bottom: 65px; }
  }
  @media (min-width: 769px) {
    .desktop-call-widget {
      position: fixed;
      right: 20px;
      bottom: 80px;
      background: #0b2c5d;
      padding: 15px 20px;
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
      z-index: 999;
      text-align: center;
    }
    .desktop-call-widget p {
      color: white;
      font-size: 12px;
      margin: 5px 0;
    }
    .desktop-call-widget button {
      background: linear-gradient(135deg, #FFD700 0%, #FFC700 100%);
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      color: #0b2c5d;
      font-weight: 700;
      cursor: pointer;
      margin-top: 8px;
      font-size: 13px;
    }
  }
</style>

<!-- Mobile Sticky Call Button -->
<div class="mobile-call-cta" id="mobileCallCTA">
  <div class="call-btn-container">
    <a href="tel:9899991342" class="mobile-call-btn" style="display:inline-block;text-decoration:none;">
      📱 CALL: 9899991342
    </a>
  </div>
</div>

<!-- Desktop Call Widget -->
<div class="desktop-call-widget" id="desktopCallWidget">
  <p><strong>Need Counselling?</strong></p>
  <p>Expert guidance for B.Tech, BBA &amp; Law</p>
  <a href="tel:9899991342" style="display:inline-block;background:linear-gradient(135deg, #FFD700 0%, #FFC700 100%);border:none;padding:10px 20px;border-radius:20px;color:#0b2c5d;font-weight:700;text-decoration:none;margin-top:8px;font-size:13px;">📱 Call Now</a>
</div>

<!-- Hide desktop widget when footer is visible -->
<script>
  window.addEventListener('scroll', function() {
    if (window.innerWidth > 768) {
      var footer = document.querySelector('footer');
      var widget = document.getElementById('desktopCallWidget');
      if (footer && widget) {
        widget.style.display = footer.getBoundingClientRect().top < window.innerHeight ? 'none' : 'block';
      }
    }
  });
</script>
