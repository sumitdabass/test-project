<?php
/**
 * Hero Banner Component
 *
 * Usage:
 *   $hero_title = "Page Title";
 *   $hero_subtitle = "Optional subtitle";
 *   $hero_breadcrumbs = [['Home', '/'], ['Admissions', '/ipu-admission-guide.php'], ['Current Page', '']];
 *   $hero_compact = true; // false for full-size homepage hero
 *   $hero_show_form = false;
 *   include 'include/components/hero-banner.php';
 */

$hero_title = $hero_title ?? 'IPU Admission Guide';
$hero_subtitle = $hero_subtitle ?? '';
$hero_breadcrumbs = $hero_breadcrumbs ?? [];
$hero_compact = $hero_compact ?? true;
$hero_show_form = $hero_show_form ?? false;
?>

<section class="hero-section <?= $hero_compact ? 'hero-compact' : '' ?>">
  <div class="container">
    <div class="row align-items-center">

      <div class="<?= $hero_show_form ? 'col-lg-7' : 'col-lg-12' ?>">
        <?php if (!empty($hero_breadcrumbs)): ?>
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" style="margin-bottom:12px">
          <ol style="display:flex;flex-wrap:wrap;list-style:none;padding:0;margin:0;gap:4px;font-size:13px">
            <?php foreach ($hero_breadcrumbs as $i => $crumb): ?>
              <?php if ($i > 0): ?><li style="color:rgba(255,255,255,.5)">/</li><?php endif; ?>
              <?php if (!empty($crumb[1]) && $i < count($hero_breadcrumbs) - 1): ?>
                <li><a href="<?= htmlspecialchars($crumb[1]) ?>" style="color:rgba(255,255,255,.7);text-decoration:none"><?= htmlspecialchars($crumb[0]) ?></a></li>
              <?php else: ?>
                <li style="color:#f59e0b"><?= htmlspecialchars($crumb[0]) ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ol>
        </nav>
        <?php endif; ?>

        <h1><?= $hero_title ?></h1>
        <?php if ($hero_subtitle): ?>
          <p style="font-size:1.1rem;max-width:600px"><?= $hero_subtitle ?></p>
        <?php endif; ?>

        <?php if (!$hero_compact): ?>
        <div style="margin-top:24px;display:flex;flex-wrap:wrap;gap:12px">
          <a href="tel:+919899991342" class="nav-phone-btn" style="font-size:16px;padding:14px 28px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
            Call: 9899991342
          </a>
          <a href="#enquiry-form" style="display:inline-flex;align-items:center;gap:8px;background:transparent;border:2px solid rgba(255,255,255,.4);color:#fff;padding:12px 28px;border-radius:50px;font-weight:600;font-size:15px;text-decoration:none;transition:all .2s">
            Request a Callback
          </a>
        </div>
        <?php endif; ?>
      </div>

      <?php if ($hero_show_form): ?>
      <div class="col-lg-5 mt-4 mt-lg-0">
        <?php include __DIR__ . '/../sidebar-cta.php'; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <?php if (!empty($hero_breadcrumbs)): ?>
  <!-- BreadcrumbList Schema -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      <?php foreach ($hero_breadcrumbs as $i => $crumb): ?>
      {
        "@type": "ListItem",
        "position": <?= $i + 1 ?>,
        "name": "<?= htmlspecialchars($crumb[0]) ?>"<?php if (!empty($crumb[1])): ?>,
        "item": "https://ipu.co.in<?= htmlspecialchars($crumb[1]) ?>"<?php endif; ?>
      }<?= $i < count($hero_breadcrumbs) - 1 ? ',' : '' ?>
      <?php endforeach; ?>
    ]
  }
  </script>
  <?php endif; ?>
</section>
