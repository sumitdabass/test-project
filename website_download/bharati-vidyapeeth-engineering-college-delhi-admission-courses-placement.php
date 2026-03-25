<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
include_once("include/base-head.php");

?>

<?php

include_once("include/form-handler.php");

?>

<!--====== Title ======-->
<title>MAIT & MAIMS Rohini: Top GGSIPU Colleges Offering BBA, MBA, B.Tech, BJMC & More</title>

<meta name="description" content="Discover MAIT and MAIMS, premier colleges in Sector 22, Rohini (Delhi), affiliated with GGSIPU. Explore popular courses like B.Tech (CS, IT, AI, ML), BBA, MBA, BJMC, B.Com, and BA (Eco) with top-notch faculty and campus facilities">
<meta name="keywords" content="MAIT Rohini, MAIMS Rohini, MAIT B.Tech Admission, MAIMS Courses, GGSIPU Colleges in Rohini, BBA in Delhi, BJMC in IPU, B.Tech AI ML IPU, MBA MAIMS, BA Economics IPU, Top Colleges in Sector 22 Rohini">    


</head>

<body>

    <!--====== HEADER PART START ======-->
    <?php include_once("include/base-nav.php") ?>
    <!--====== HEADER PART END ======-->




    <!--====== BANNER PART START ======-->

    <section class="banner-area banner-three mt-0 bg_cover d-flex align-items-end">
        <div class="container">
            <div class="row align-items-end">
                <div class="col-lg-12 col-md-12">
                    <div class="banner-content">
                        <h1 class="white center ft-35">
                            Exploring MAIT & MAIMS: Premier Colleges in Sector 22 Rohini Offering Diverse Career-Oriented Programs
                        </h1>

                    </div>
                </div>

            </div>
        </div>
        <div class="banner-shape"></div>
    </section>
    <!--====== BANNER PART ENDS ======-->


    <!--====== Blog PART STARt ======-->
    <section class="blog-wrapper pt-130 pb-130">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog-details">
                        <img loading="lazy" src="assets/images/exploring-MAIT-and-MAIMS.jpg" class="main-img" alt="Exploring MAIT & MAIMS Rohini">

                        <h2 class="title">Exploring MAIT & MAIMS: Premier Colleges in Sector 22 Rohini Offering Diverse Career-Oriented Programs
                        </h2>
                        <p>Located in the heart of Sector 22, Rohini, *Maharaja Agrasen Institute of Technology (MAIT)* and *Maharaja Agrasen Institute of Management Studies (MAIMS)* are two of the most prestigious institutions affiliated with Guru Gobind Singh Indraprastha University (GGSIPU). Known for their academic excellence, modern infrastructure, and student-centric approach, both institutions have become top choices for students pursuing professional and technical education in Delhi NCR.</p>

                        <p><strong>About MAIT </strong></p>

                        <p>Maharaja Agrasen Institute of Technology (MAIT)* is a reputed engineering college offering a robust curriculum, state-of-the-art laboratories, and experienced faculty. It is particularly known for its focus on innovation, entrepreneurship, and technical excellence. </p>
                        
                        <p><strong>Courses Offered at MAIT: </strong>T</p>

                        <ul>
                            <li>B.Tech in Computer Science (CS) </li>
                            <li>B.Tech in Information Technology (IT)</li>
                            <li>B.Tech in Artificial Intelligence (AI) </li>
                            <li>B.Tech in Machine Learning (ML)</li>
                            <li>B.Tech in Data Science (DS) </li>
                            <li>B.Tech in Electronics and Communication Engineering (ECE) </li>
                            <li>B.Tech in Mechanical Engineering (Mech)</li>
                          
                        </ul>

                        <p><strong>About MAIMS </strong></p>

                        <p>Maharaja Agrasen Institute of Management Studies (MAIMS)* is a leading management and media education institute. With a blend of academic rigor and industry exposure, MAIMS prepares students for leadership roles across sectors. </p>

                        
                        <p><strong>Courses Offered at MAIMS:</strong></p>

                        <ol>
                            <li>BBA (Bachelor of Business Administration) </li>
                            <li>MBA (Master of Business Administration) </li>
                            <li>BJMC (Bachelor of Journalism and Mass Communication) </li>
                            <li>B.Com (Bachelor of Commerce) </li>
                            <li>BA (Hons.) Economics </li>
                            
                        </ol>

                       <br>

                        <p><strong>Why Choose MAIT & MAIMS? </strong></p>

                       
                        <ul>
                            <li>Prime Location:* Conveniently located in Sector 22, Rohini, well-connected by metro and public transport. </li>
                            <li>Industry-Relevant Curriculum:* Programs designed to meet evolving industry demands.</li>
                            <li>Modern Infrastructure:* Equipped with smart classrooms, tech-enabled labs, libraries, and auditoriums.</li>
                            <li>
                                Placement Support:* Excellent placement records with top recruiters from tech, finance, media, and consulting sectors.
                            </li>
                            <li>
                                Holistic Development:* Active student societies, fests, workshops, and cultural events.
                            </li>
                            
                        </ul>

                        
                        <p>
                        For more details on B.Tech admission 2025, counselling, and quota-based admissions, call: 
                        <b><?php include("include/phone.php"); ?> </b>
                        </p>
                    </div>



                </div>
                <div class="col-lg-4">
                    <?php include_once("include/sidebar-cta.php") ?>
                </div>
            </div>
        </div>
    </section>
    <!--====== Blog PART END ======-->


    <!--====== COUNTER PART START ======-->
    <section class="counter-area pt-60 bg_cover" style="background-image: url(assets/images/counter-bg-2.jpg);">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <div class="counter-item text-center mt-30">

                        <h3 class="title"> Call Our Helpline <a href="tel:9899991342"> +91- 9899991342 </a> </h3>

                    </div>
                </div>

            </div>
        </div>
    </section>
    <!--====== COUNTER PART ENDS ======-->



    <?php
    $related_pages = [
        ['title' => 'IPU B.Tech Admission 2026', 'url' => '/IPU-B-Tech-admission-2026.php', 'desc' => 'JEE Main eligibility, top colleges, cutoffs & admission process'],
        ['title' => 'All IPU Colleges List 2026', 'url' => '/ipu-colleges-list.php', 'desc' => 'Complete list of 60+ IPU affiliated colleges in Delhi'],
        ['title' => 'IPU Helpline – Call 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission guidance from our expert team. Mon-Sat 9AM-7PM'],
    ];
    include 'include/components/related-pages.php';
    ?>

    <?php include_once("include/base-footer.php") ?>

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
    </body>

</html>