<?php 

    session_start();
    include_once(__DIR__ . "/../include/base-head.php");

?>



	<!--====== Title ======-->
	<title>IPU|GGSIPU</title>
	<link rel="canonical" href="https://ipu.co.in/course/">
	<meta name="description" content="Explore courses offered under GGSIPU (IP University) — B.Tech, BBA, BCA, B.Com, BA LLB, MBA and more. Admission process, eligibility and counselling guidance.">
	<meta name="robots" content="index, follow">
	<meta property="og:type" content="website">
	<meta property="og:url" content="https://ipu.co.in/course/">
	<meta property="og:title" content="Courses Offered Under GGSIPU (IP University) — Admission Guide">
	<meta property="og:description" content="B.Tech, BBA, BCA, B.Com, BA LLB, MBA and more under IP University. Eligibility, process and counselling guidance.">
	<meta name="twitter:card" content="summary_large_image">

</head>

<body>


	
	<!--====== HEADER PART START ======-->
	<?php include_once(__DIR__ . "/../include/base-nav.php") ?>
	<!--====== HEADER PART END ======-->
	
	<!--====== BANNER PART START ======-->
	<section class="banner-area banner-two mt-0 bg_cover d-flex align-items-end">
		<div class="container">
			<div class="row align-items-end">
				<div class="col-lg-7 col-md-7">
					<div class="banner-content">
						<h3 class="white">
							Guru Gobind Singh Indraprastha , Delhi
						</h3>
						<h1 class="title mt10">
							ADMISSIONS OPEN
						</h1>
						
						<h6 class="mt10 white">
							UG, PG, M.Phil, Ph.D and Diploma Programmes
						</h6>
						<h6 class="mt10 white">
							Graded as "A" by the NAAC
						</h6>
						<h6 class="mt10 white">
							Admission Open for BBA,BCA,MBA,MCA,BJMC,B.Com,B.Tech,LAW,BA(Eng),BA(Eco)
						</h6>
						<h6 class="mt10 white">
							Last Date of Registration
						</h6>
						<h6 class="mt10 white">
							Managment Quota Seats
						</h6>
						<h6 class="mt10 white">
						Admission Helpline  <b> <?php include(__DIR__ . "/../include/phone.php"); ?>  </b>
						</h6>
						<ul class="mt10">
							<li>
								<a class="main-btn main-btn-3" >  Contact Us</a>
							</li>
							
						</ul>
					</div>
				</div>
				

				<div class="col-lg-5">
					<div class="banner-form">
						<div class="banner-form-inner white-bg">
<form method="POST" action="/sendemail.php" class="enquiry-form" novalidate>
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>
    <input type="hidden" name="page_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
    <input type="hidden" name="form_loaded_at" value="">
    <h3 class="title">Enquire now</h3>
    <div class="input-box mt-10">
        <input type="text" name="name" required placeholder="Your Name" autocomplete="name" />
    </div>
    <div class="input-box mt-10">
        <input type="email" name="email" placeholder="Your Email (optional)" autocomplete="email" />
    </div>
    <div class="input-box mt-10">
        <input type="tel" name="phone" required placeholder="Phone Number" inputmode="tel" autocomplete="tel" pattern="[6-9][0-9]{9}" maxlength="10" />
    </div>
    <div class="input-box mt-10">
        <input type="text" name="course" required placeholder="Enter Course" />
    </div>
    <div class="input-box mt-10">
        <button type="submit" name="submit">Submit Now</button>
    </div>
</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="banner-shape"></div>
	</section>
	
	
		<!--====== BLOG PART -2 START ======-->
	<section class="blog-area pt-80 pb-70">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-6 col-md-8">
					<div class="section-title text-center">
						<img loading="lazy" src="assets/images/item.png" alt="" width="50" height="50">
						<h2 class="title">
							OUR COURSES
						</h2>
					</div>
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/blog-4.jpg" alt="" width="370" height="270">
							</a>
						</div>
						<div class="blog-content">
							<h4>B.Tech <small>( CET )</small></h4>
							<p>
                                    Joint Entrance Exam (JEE) Main Paper Conducted by National Testing Agency (NTA).
                                    
                                    
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?></b>
                                    
                                    <!-- write content here -->
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/BBA.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>BBA <small>( CET )</small></h4>
							<p>
							 Admission shall be on the basis of the merit of the written test CET/CUET. 
							<br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/BJMC.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>BJMC <small>( CET )</small></h4>
							<p>
								
									Admission shall be on the basis of the merit of the written test CET/CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/Bcom.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>B.Com <small>( CET )</small></h4>
							<p>
									Admission shall be on the basis of the merit of the written test CET/CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/MBA.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>MBA <small>( CET )</small></h4>
							<p>
									Admission shall be on the basis of the merit of the written test CAT/CMAT/CET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/BA-ENGLISH.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>Ba( English) <small>( CET )</small></h4>
							<p>
									Admission shall be on the basis of the merit of the written test CET/CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/bca.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>BCA <small>( CET )</small> </h4>
							<p>
									Admission shall be on the basis of the merit of the written test CET/CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/mca.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>MCA <small>( CET )</small> </h4>
							<p>
									Admission shall be on the basis of the merit of the written test NIMCET / CET
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/mbbs.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>MBBS <small>( CET )</small> </h4>
							<p>
									Admission shall be on the basis of the merit of the written test NEET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/barch.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>B.Arch <small>( CET )</small> </h4>
							<p>
									Admission shall be on the basis of the merit of the written test NATA.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/law.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4>LAW (BALLB/BBALLB) <small>( CET )</small></h4>
							<p>
									Admission shall be on the basis of the merit of the written test CLAT/ CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				<div class="col-lg-4 col-md-6">
					<div class="blog-item blog-item-two mt-30">
						<div class="blog-thumb">
							<a href="#">
							<img loading="lazy" src="assets/images/BA-ENGLISH.jpg" alt="" width="470" height="343">
							</a>
						</div>
						<div class="blog-content">
							<h4> Ba( Economics ) <small>( CET )</small></h4>
							<p>
									Admission shall be on the basis of the merit of the written test CET/CUET.
                                    <br> For more Information call Helpline Number <b><?php include(__DIR__ . "/../include/phone.php"); ?> </b>
                                </p>							
							<a href="#registration">Apply Now <i class="flaticon-add"></i></a>
						</div>
					</div>
				</div>
				
				
				
				
			</div>
		</div>
	</section>

	
	<!--====== COUNTER PART START ======-->
	<section class="counter-area pt-60 bg_cover" style="background-image: url(assets/images/counter-bg-2.jpg);">
		<div class="container">
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="counter-item text-center mt-30">
						
						<h3 class="title"> Call Our Helpline <br>  <b> <?php include(__DIR__ . "/../include/phone.php"); ?>  </b> </h3>
						
					</div>
				</div>
				
			</div>
		</div>
	</section>
	<!--====== COUNTER PART ENDS ======-->
	
	
	<!--====== BLOG PART START ======-->
	<?php include_once(__DIR__ . "/../include/home-blog.php") ?>
	
	<!--====== BLOG PART ENDS ======-->
	
	
	<?php include_once(__DIR__ . "/../include/base-footer.php") ?>

	<!--====== jquery js ======-->
	<!--====== Bootstrap js ======-->
	<!--====== Slick js ======-->
	<!--====== Isotope js ======-->
	<!--====== Images Loaded js ======-->
	<!--====== nice select js ======-->
	<!--====== Magnific Popup js ======-->
	<!--====== counterup js ======-->
	<!--====== appear js ======-->
	<!--====== waypoints js ======-->
	<!--====== Ajax Contact js ======-->
	<!--====== Main js ======-->
	<?php $breadcrumbs = [['Home', '/'], ['Courses', '']]; include __DIR__ . '/../include/components/breadcrumb-schema.php'; ?>
	</body>

</html>