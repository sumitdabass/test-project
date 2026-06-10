$(function() {
	'use strict';

	//===== Prealoder
	$(window).on('load', function(event) {
		$('#preloader')
			.delay(500)
			.fadeOut(500);
	});

	//===== Sticky
	$(window).on('scroll', function(event) {
		var scroll = $(window).scrollTop();
		if (scroll < 110) {
			$('.header-nav').removeClass('sticky');
		} else {
			$('.header-nav').addClass('sticky');
		}
	});

	//===== Mobile Menu
	$('.navbar-toggler').on('click', function() {
		$(this).toggleClass('active');
	});

	$('.navbar-nav a').on('click', function() {
		$('.navbar-toggler').removeClass('active');
	});
	var subMenu = $('.sub-menu-bar .navbar-nav .sub-menu');

	if (subMenu.length) {
		subMenu
			.parent('li')
			.children('a')
			.append(function() {
				return '<button class="sub-nav-toggler"> <i class="fa fa-angle-down"></i> </button>';
			});

		var subMenuToggler = $('.sub-menu-bar .navbar-nav .sub-nav-toggler');

		subMenuToggler.on('click', function() {
			$(this)
				.parent()
				.parent()
				.children('.sub-menu')
				.slideToggle();
			return false;
		});
	}

	//===== seller Active slick slider
	$('.feedback-active').slick({
		dots: false,
		infinite: true,
		autoplay: true,
		autoplaySpeed: 2800,
		arrows: true,
		prevArrow: '<span class="prev"><i class="fa fa-arrow-left"></i></span>',
		nextArrow:
			'<span class="next"><i class="fa fa-arrow-right"></i></span>',
		speed: 1000,
		slidesToShow: 3,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1201,
				settings: {
					slidesToShow: 3
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 2
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 2,
					arrows: false
				}
			},
			{
				breakpoint: 576,
				settings: {
					slidesToShow: 1,
					arrows: false
				}
			}
		]
	});
	$('.feedback-ative-two').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		autoplaySpeed: 2800,
		arrows: false,
		prevArrow: '<span class="prev"><i class="fa fa-arrow-left"></i></span>',
		nextArrow:
			'<span class="next"><i class="fa fa-arrow-right"></i></span>',
		speed: 1000,
		slidesToShow: 2,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 1
				}
			}
		]
	});

	//===== seller Active slick slider
	$('.brand-active').slick({
		dots: false,
		infinite: true,
		autoplay: false,
		autoplaySpeed: 2800,
		arrows: false,
		speed: 1000,
		slidesToShow: 6,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1201,
				settings: {
					slidesToShow: 6
				}
			},
			{
				breakpoint: 992,
				settings: {
					slidesToShow: 4
				}
			},
			{
				breakpoint: 768,
				settings: {
					slidesToShow: 3
				}
			},
			{
				breakpoint: 576,
				settings: {
					slidesToShow: 2
				}
			}
		]
	});

	//===== Expert Active slick slider
	$('#expert-slider').slick({
		dots: false,
		infinite: true,
		autoplay: true,
		autoplaySpeed: 2800,
		arrows: false,
		speed: 1000,
		slidesToShow: 4,
		slidesToScroll: 1,
		responsive: [
			{
				breakpoint: 1200,
				settings: {
					slidesToShow: 3
				}
			},
			{
				breakpoint: 991,
				settings: {
					slidesToShow: 2
				}
			},
			{
				breakpoint: 575,
				settings: {
					slidesToShow: 1
				}
			}
		]
	});

	//===== Isotope Project 1
	$('.container').imagesLoaded(function() {
		var $grid = $('.grid').isotope({
			// options
			transitionDuration: '1s'
		});

		// filter items on button click
		$('.project-menu ul').on('click', 'li', function() {
			var filterValue = $(this).attr('data-filter');
			$grid.isotope({ filter: filterValue });
		});

		//for menu active class
		$('.project-menu ul li').on('click', function(event) {
			$(this)
				.siblings('.active')
				.removeClass('active');
			$(this).addClass('active');
			event.preventDefault();
		});
	});

	//====== Magnific Popup
	$('.video-popup').magnificPopup({
		type: 'iframe'
		// other options
	});

	//===== Magnific Popup
	$('.image-popup').magnificPopup({
		type: 'image',
		gallery: {
			enabled: true
		}
	});

	//===== counter up
	$('.counter').counterUp({
		delay: 10,
		time: 2000
	});

	//===== niceSelect js
	$('select').niceSelect();

	// Progress Bar
	if ($('.progress-line').length) {
		$('.progress-line').appear(
			function() {
				var el = $(this);
				var percent = el.data('width');
				$(el).css('width', percent + '%');
			},
			{
				accY: 0
			}
		);
	}
	if ($('.count-box').length) {
		$('.count-box').appear(
			function() {
				var $t = $(this),
					n = $t.find('.count-text').attr('data-stop'),
					r = parseInt($t.find('.count-text').attr('data-speed'), 10);

				if (!$t.hasClass('counted')) {
					$t.addClass('counted');
					$({
						countNum: $t.find('.count-text').text()
					}).animate(
						{
							countNum: n
						},
						{
							duration: r,
							easing: 'linear',
							step: function() {
								$t.find('.count-text').text(
									Math.floor(this.countNum)
								);
							},
							complete: function() {
								$t.find('.count-text').text(this.countNum);
							}
						}
					);
				}
			},
			{
				accY: 0
			}
		);
	}

	// Scroll Event
	$(window).on('scroll', function() {
		var scrolled = $(window).scrollTop();
		if (scrolled > 300) $('.go-top').addClass('active');
		if (scrolled < 300) $('.go-top').removeClass('active');
	});

	// Click Event
	$('.go-top').on('click', function() {
		$('html, body').animate(
			{
				scrollTop: '0'
			},
			1200
		);
	});

	// Single Features Active
	$('.feature-area').on('mouseover', '.feature-item.style-one', function() {
		$('.feature-item.active').removeClass('active');
		$(this).addClass('active');
	});

	// Service Hover Actve
	$('.services-area').on(
		'mouseover',
		'.services-item.services-item-2',
		function() {
			$('.services-item.services-item-2.active').removeClass('active');
			$(this).addClass('active');
		}
	);

	// ==== Product Quantity
	$('#quantityDown').on('click', function() {
		var numProduct = Number($('#quantity').val());
		if (numProduct > 0)
			$(this)
				.next()
				.val(numProduct - 1);
	});
	$('#quantityUP').on('click', function() {
		var numProduct = Number($('#quantity').val());
		$(this)
			.prev()
			.val(numProduct + 1);
	});

	// ==== Gallery Main Slider
	var galleryDots = $('.gallery-dots');
	var galleryArrow = $('.gallery-arrows');
	$('#gallerySliderActive').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		fade: false,
		infinite: true,
		autoplay: false,
		autoplaySpeed: 6000,
		arrows: true,
		appendArrows: galleryArrow,
		prevArrow:
			'<button class="prev-arrow"><i class="far fa-angle-left"></i></button>',
		nextArrow:
			'<button class="next-arrow"><i class="far fa-angle-right"></i></button>',

		dots: true,
		appendDots: galleryDots,
		customPaging: function(slick, index) {
			var portrait = $(slick.$slides[index]).data('thumb');
			return '<img src=" ' + portrait + ' "/>';
		}
	});

	$('#gallerySliderActive').each(function() {
		// the containers for all your galleries
		var additionalImages = $('.image-popup').not(
			'.slick-slide.slick-cloned .image-popup'
		);
		additionalImages.magnificPopup({
			type: 'image',
			gallery: {
				enabled: true
			},
			mainClass: 'mfp-fade'
		});
	});

	// Bootstrap Accordion Icon
	$('.faq-accordion .card-header button').on('click', function(e) {
		$('.faq-accordion .card-header button').removeClass('active-accordion');
		$(this).addClass('active-accordion');
	});

	// Image Popup For Project Page
	$('.grid').each(function() {
		$('.image-popup').magnificPopup({
			type: 'image',
			gallery: {
				enabled: true
			},
			mainClass: 'mfp-fade'
		});
	});
});
