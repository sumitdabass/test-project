<?php
// include/components/page-hero.php
// Form-bearing page hero. Replaces banner-three across course hubs.
// Pages keep their own H1/intro/breadcrumbs — this only rewraps them.
// Locals (all optional):
//   $hero_kicker      string   — small uppercase label above H1
//   $hero_h1          string   — H1 text (HTML allowed for inline emphasis like <em>…</em>)
//   $hero_intro       string   — intro paragraph (HTML allowed for anchors)
//   $hero_chips       array    — list of strings rendered as pill chips
//   $hero_breadcrumbs array    — list of [label, url] tuples; tuple with empty url renders as current
//   $hero_show_form   bool     — render the in-flow sidebar enquiry on desktop (default true)
//   $hero_slot_html   string   — raw HTML override for the left column (escapes nothing)

$hero_kicker      = $hero_kicker      ?? null;
$hero_h1          = $hero_h1          ?? null;
$hero_intro       = $hero_intro       ?? null;
$hero_chips       = $hero_chips       ?? [];
$hero_breadcrumbs = $hero_breadcrumbs ?? [];
$hero_show_form   = $hero_show_form   ?? true;
$show_trust_bar   = $show_trust_bar   ?? true;
$hero_slot_html   = $hero_slot_html   ?? null;
?>
<section class="ipu-page-hero">
  <div class="container">
    <div class="row align-items-center">

      <div class="col-lg-<?= $hero_show_form ? '7' : '12' ?> mb-4 mb-lg-0">
        <?php if ($hero_slot_html !== null): ?>
          <?= $hero_slot_html ?>
        <?php else: ?>

          <?php if (!empty($hero_breadcrumbs)): ?>
            <nav class="ipu-page-hero__crumbs" aria-label="Breadcrumb">
              <ol>
                <?php foreach ($hero_breadcrumbs as $c): ?>
                  <li>
                    <?php if (!empty($c[1])): ?>
                      <a href="<?= htmlspecialchars($c[1]) ?>"><?= htmlspecialchars($c[0]) ?></a>
                    <?php else: ?>
                      <span aria-current="page"><?= htmlspecialchars($c[0]) ?></span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            </nav>
          <?php endif; ?>

          <?php if ($hero_kicker): ?>
            <p class="ipu-page-hero__kicker"><?= htmlspecialchars($hero_kicker) ?></p>
          <?php endif; ?>

          <?php if ($hero_h1): ?>
            <h1 class="ipu-page-hero__h1"><?= $hero_h1 ?></h1>
          <?php endif; ?>

          <?php if (!empty($last_updated)): ?>
            <?php $last_updated_theme = 'dark'; include __DIR__ . '/last-updated.php'; ?>
          <?php endif; ?>

          <?php if (!empty($hero_chips)): ?>
            <div class="ipu-page-hero__chips">
              <?php foreach ($hero_chips as $chip): ?>
                <span><?= htmlspecialchars($chip) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($hero_intro): ?>
            <p class="ipu-page-hero__intro"><?= $hero_intro ?></p>
          <?php endif; ?>

          <a href="tel:+919899991342" class="ipu-btn-primary ipu-page-hero__call">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.58.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg>
            Call: 9899991342
          </a>

        <?php endif; ?>
      </div>

      <?php if ($hero_show_form): ?>
        <div class="col-lg-5">
          <?php include __DIR__ . '/sidebar-enquiry.php'; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<?php if ($show_trust_bar): include __DIR__ . '/trust-bar.php'; endif; ?>
