<?php
/**
 * Breadcrumb Schema Component — BreadcrumbList JSON-LD for SEO
 *
 * Usage:
 *   $breadcrumbs = [
 *     ['Home', '/'],
 *     ['B.Tech', '/IPU-B-Tech-admission-2026.php'],
 *     ['MAIT', ''],   // last item — current page, no URL
 *   ];
 *   include 'include/components/breadcrumb-schema.php';
 *
 * Notes:
 *   - Each breadcrumb is a two-element array: [name, url]
 *   - The last item should have an empty string URL (current page)
 *   - Skipped entirely when fewer than 2 breadcrumbs are provided
 *   - All output is sanitised with htmlspecialchars
 */

$breadcrumbs = $breadcrumbs ?? [];

if (count($breadcrumbs) < 2) return;

$base_url = 'https://ipu.co.in';
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    <?php foreach ($breadcrumbs as $i => $crumb):
      $position  = $i + 1;
      $name      = htmlspecialchars($crumb[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $url       = isset($crumb[1]) ? trim($crumb[1]) : '';
      $is_last   = ($i === count($breadcrumbs) - 1);
    ?>
    {
      "@type": "ListItem",
      "position": <?= $position ?>,
      "name": "<?= $name ?>"<?php if (!$is_last && $url !== ''): ?>,
      "item": "<?= htmlspecialchars($base_url . $url, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"<?php endif; ?>

    }<?= $i < count($breadcrumbs) - 1 ? ',' : '' ?>

    <?php endforeach; ?>
  ]
}
</script>
