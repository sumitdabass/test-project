/**
 * ipu.co.in - Main Application JavaScript (Vanilla JS)
 * Replaces jQuery-based main.js + all jQuery plugins
 * Dependencies: Bootstrap 5 (no jQuery required)
 */

(function () {
  'use strict';

  // ===== Preloader =====
  window.addEventListener('load', function () {
    var preloader = document.getElementById('preloader');
    if (preloader) {
      setTimeout(function () {
        preloader.style.opacity = '0';
        preloader.style.transition = 'opacity 0.5s ease';
        setTimeout(function () {
          preloader.style.display = 'none';
        }, 500);
      }, 300);
    }
  });

  // ===== Sticky Header =====
  var headerNav = document.querySelector('.header-nav');
  if (headerNav) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 110) {
        headerNav.classList.add('sticky');
      } else {
        headerNav.classList.remove('sticky');
      }
    });
  }

  // ===== Mobile Menu Toggle =====
  var navToggler = document.querySelector('.navbar-toggler');
  if (navToggler) {
    navToggler.addEventListener('click', function () {
      this.classList.toggle('active');
    });
  }

  // Close mobile menu when a nav link is clicked
  document.querySelectorAll('.navbar-nav a').forEach(function (link) {
    link.addEventListener('click', function () {
      if (navToggler) navToggler.classList.remove('active');
    });
  });

  // ===== Scroll to Top =====
  var goTop = document.querySelector('.go-top');
  if (goTop) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 300) {
        goTop.classList.add('active');
      } else {
        goTop.classList.remove('active');
      }
    });

    goTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ===== Counter Animation (IntersectionObserver) =====
  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-stop') || el.textContent, 10);
    var speed = parseInt(el.getAttribute('data-speed') || 2000, 10);
    var start = 0;
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / speed, 1);
      el.textContent = Math.floor(progress * target);
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = target;
      }
    }

    requestAnimationFrame(step);
  }

  // Observe .counter and .count-text elements
  var counterElements = document.querySelectorAll('.counter, .count-text');
  if (counterElements.length > 0 && 'IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
          entry.target.classList.add('counted');
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counterElements.forEach(function (el) {
      counterObserver.observe(el);
    });
  }

  // ===== Progress Bar Animation =====
  var progressBars = document.querySelectorAll('.progress-line');
  if (progressBars.length > 0 && 'IntersectionObserver' in window) {
    var progressObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var percent = entry.target.getAttribute('data-width');
          entry.target.style.width = percent + '%';
          progressObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });

    progressBars.forEach(function (bar) {
      progressObserver.observe(bar);
    });
  }

  // ===== Feature/Service Hover Active =====
  document.querySelectorAll('.feature-area .feature-item.style-one').forEach(function (item) {
    item.addEventListener('mouseenter', function () {
      document.querySelectorAll('.feature-item.active').forEach(function (el) {
        el.classList.remove('active');
      });
      this.classList.add('active');
    });
  });

  document.querySelectorAll('.services-area .services-item.services-item-2').forEach(function (item) {
    item.addEventListener('mouseenter', function () {
      document.querySelectorAll('.services-item.services-item-2.active').forEach(function (el) {
        el.classList.remove('active');
      });
      this.classList.add('active');
    });
  });

  // ===== Testimonial Carousel (CSS-based with auto-scroll) =====
  function initCarousel(containerSelector) {
    var container = document.querySelector(containerSelector);
    if (!container) return;

    var track = container.querySelector('.carousel-track');
    if (!track) return;

    var slides = Array.from(track.children);
    if (slides.length === 0) return;

    var currentIndex = 0;
    var slideWidth = 0;
    var autoplayInterval = null;

    function updateSlideWidth() {
      var containerWidth = container.offsetWidth;
      var slidesToShow = 3;
      if (containerWidth < 576) slidesToShow = 1;
      else if (containerWidth < 992) slidesToShow = 2;
      slideWidth = containerWidth / slidesToShow;
      slides.forEach(function (s) { s.style.minWidth = slideWidth + 'px'; });
      track.style.transform = 'translateX(-' + (currentIndex * slideWidth) + 'px)';
    }

    function goTo(index) {
      var maxIndex = Math.max(0, slides.length - Math.floor(container.offsetWidth / slideWidth));
      currentIndex = Math.max(0, Math.min(index, maxIndex));
      track.style.transition = 'transform 0.5s ease';
      track.style.transform = 'translateX(-' + (currentIndex * slideWidth) + 'px)';
    }

    function next() { goTo(currentIndex + 1 >= slides.length ? 0 : currentIndex + 1); }
    function prev() { goTo(currentIndex - 1 < 0 ? slides.length - 1 : currentIndex - 1); }

    // Nav buttons
    var prevBtn = container.querySelector('.carousel-prev');
    var nextBtn = container.querySelector('.carousel-next');
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); resetAutoplay(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); resetAutoplay(); });

    function startAutoplay() {
      autoplayInterval = setInterval(next, 3000);
    }
    function resetAutoplay() {
      clearInterval(autoplayInterval);
      startAutoplay();
    }

    updateSlideWidth();
    startAutoplay();
    window.addEventListener('resize', updateSlideWidth);
  }

  // Initialize carousels after DOM is ready
  document.addEventListener('DOMContentLoaded', function () {
    initCarousel('.feedback-carousel');
    initCarousel('.brand-carousel');
  });

  // ===== FAQ Accordion Active State =====
  document.querySelectorAll('.faq-accordion .accordion-button').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.faq-accordion .accordion-button').forEach(function (b) {
        b.classList.remove('active-accordion');
      });
      this.classList.add('active-accordion');
    });
  });

  // ===== Desktop Call Widget - Hide Near Footer =====
  var desktopWidget = document.getElementById('desktopCallWidget');
  if (desktopWidget) {
    window.addEventListener('scroll', function () {
      if (window.innerWidth > 768) {
        var footer = document.querySelector('footer');
        if (footer) {
          desktopWidget.style.display =
            footer.getBoundingClientRect().top < window.innerHeight ? 'none' : 'block';
        }
      }
    });
  }

  // ===== Lazy Image Loading Fallback =====
  if (!('loading' in HTMLImageElement.prototype)) {
    var lazyImages = document.querySelectorAll('img[loading="lazy"]');
    if (lazyImages.length > 0 && 'IntersectionObserver' in window) {
      var lazyObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var img = entry.target;
            if (img.dataset.src) {
              img.src = img.dataset.src;
              img.removeAttribute('data-src');
            }
            lazyObserver.unobserve(img);
          }
        });
      });
      lazyImages.forEach(function (img) { lazyObserver.observe(img); });
    }
  }

  // ===== Form Validation =====
  document.querySelectorAll('.enquiry-form').forEach(function (form) {
    var phoneInput = form.querySelector('input[name="phone"]');
    if (phoneInput) {
      phoneInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        if (this.value.length === 10 && /^[6-9]/.test(this.value)) {
          this.classList.remove('is-invalid');
          this.classList.add('is-valid');
        } else if (this.value.length > 0) {
          this.classList.remove('is-valid');
          this.classList.add('is-invalid');
        }
      });
    }
  });

})();
