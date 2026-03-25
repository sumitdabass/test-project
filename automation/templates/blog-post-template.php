<?php
/**
 * Auto-generated blog post: {{TITLE}}
 * Generated on: {{DATE}}
 * Slug: {{SLUG}}
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/../include/base-head.php'; ?>

    <title>{{TITLE}}</title>
    <meta name="description" content="{{META_DESC}}">
    <link rel="canonical" href="{{CANONICAL_URL}}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{OG_TITLE}}">
    <meta property="og:description" content="{{OG_DESC}}">
    <meta property="og:image" content="{{OG_IMAGE}}">
    <meta property="og:url" content="{{CANONICAL_URL}}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="IPU Admission - ipu.co.in">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{OG_TITLE}}">
    <meta name="twitter:description" content="{{OG_DESC}}">
    <meta name="twitter:image" content="{{OG_IMAGE}}">

    <!-- BlogPosting Schema -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "{{TITLE}}",
        "description": "{{META_DESC}}",
        "image": "{{OG_IMAGE}}",
        "author": {
            "@type": "Organization",
            "name": "IPU Admission",
            "url": "{{SITE_DOMAIN}}"
        },
        "publisher": {
            "@type": "Organization",
            "name": "IPU Admission",
            "url": "{{SITE_DOMAIN}}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{SITE_DOMAIN}}/assets/images/logo.png"
            }
        },
        "datePublished": "{{ISO_DATE}}",
        "dateModified": "{{ISO_DATE}}",
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{CANONICAL_URL}}"
        }
    }
    </script>

    <!-- Breadcrumb Schema -->
    <script type="application/ld+json">
    {{BREADCRUMB_JSON_LD}}
    </script>

    <!-- FAQ Schema -->
    <script type="application/ld+json">
    {{FAQ_JSON_LD}}
    </script>
</head>
<body>

    <!-- Navigation -->
    <?php include __DIR__ . '/../include/base-nav.php'; ?>

    <!-- Hero Compact + Breadcrumb -->
    <section class="hero-compact bg-primary text-white py-3">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/" class="text-white-50">Home</a></li>
                    <li class="breadcrumb-item"><a href="/blog" class="text-white-50">Blog</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page">{{CATEGORY}}</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Article Header -->
    <section class="article-header py-4 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <span class="badge bg-primary mb-2">{{CATEGORY}}</span>
                    <h1 class="display-6 fw-bold">{{H1}}</h1>
                    <div class="article-meta text-muted mt-2">
                        <span><i class="bi bi-calendar3"></i> {{DATE}}</span>
                        <span class="ms-3"><i class="bi bi-clock"></i> 5 min read</span>
                        <span class="ms-3"><i class="bi bi-telephone"></i> Free Counselling: <a href="tel:{{PHONE}}">{{PHONE}}</a></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content: 2-Column Layout -->
    <section class="article-body py-5">
        <div class="container">
            <div class="row justify-content-center">

                <!-- Content Column (col-8) -->
                <div class="col-lg-8">
                    <article class="blog-content">
                        {{CONTENT}}
                    </article>

                    <!-- FAQ Section -->
                    <div class="faq-section mt-5 pt-4 border-top">
                        <h2 class="h3 mb-4">Frequently Asked Questions</h2>
                        <div class="accordion" id="faqAccordion">
                            {{FAQ_HTML}}
                        </div>
                    </div>

                    <!-- Related Pages -->
                    <div class="related-pages mt-5 pt-4 border-top">
                        <h2 class="h4 mb-3">Related Pages</h2>
                        <div class="list-group">
                            {{RELATED_PAGES}}
                        </div>
                    </div>

                    <!-- CTA Banner -->
                    <div class="cta-banner mt-5 p-4 bg-primary text-white rounded-3 text-center">
                        <h3 class="h4">Need Help with IPU Admission {{YEAR}}?</h3>
                        <p class="mb-3">Get FREE counselling from our admission experts. We guide you through the entire process.</p>
                        <a href="tel:{{PHONE}}" class="btn btn-light btn-lg">
                            <i class="bi bi-telephone-fill"></i> Call {{PHONE}} Now
                        </a>
                    </div>
                </div>

                <!-- Sidebar Column (col-4) -->
                <div class="col-lg-4">
                    <div class="sidebar sticky-lg-top" style="top: 100px;">
                        <?php include __DIR__ . '/../include/sidebar-cta.php'; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/../include/base-footer.php'; ?>

</body>
</html>
