<?php
/**
 * FAQ (details): unified-shell FAQ with FAQPage JSON-LD identical to faq-section.php.
 *   $faqs = [['question' => '...', 'answer' => '<html allowed>'], ...];
 *   $faq_heading = 'Frequently Asked Questions'; // optional
 */
$faqs = $faqs ?? [];
$faq_heading = $faq_heading ?? 'Frequently Asked Questions';
$faq_schema = $faq_schema ?? true;   // false when the page head already carries the FAQPage JSON-LD
if (empty($faqs)) return;
?>
<section class="ipu-faq">
  <h2><?= htmlspecialchars($faq_heading) ?></h2>
  <?php foreach ($faqs as $faq): ?>
  <details>
    <summary><h3><?= htmlspecialchars($faq['question']) ?></h3></summary>
    <div class="ipu-faq__a"><?= $faq['answer'] ?></div>
  </details>
  <?php endforeach; ?>
</section>

<?php if ($faq_schema): ?>
<!-- FAQPage Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php foreach ($faqs as $i => $faq): ?>
    {
      "@type": "Question",
      "name": "<?= htmlspecialchars($faq['question'], ENT_QUOTES) ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?= htmlspecialchars(strip_tags($faq['answer']), ENT_QUOTES) ?>"
      }
    }<?= $i < count($faqs) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>
<?php endif; ?>
