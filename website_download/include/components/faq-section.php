<?php
/**
 * FAQ Section Component — Bootstrap 5 Accordion with auto-generated FAQPage schema
 *
 * Usage:
 *   $faqs = [
 *     ['question' => 'How to get admission?', 'answer' => 'You need to...'],
 *     ['question' => 'What is the fee?', 'answer' => 'The fee ranges...'],
 *   ];
 *   $faq_heading = "Frequently Asked Questions"; // optional
 *   include 'include/components/faq-section.php';
 */

$faqs = $faqs ?? [];
$faq_heading = $faq_heading ?? 'Frequently Asked Questions';
$faq_id = 'faq-' . substr(md5(serialize($faqs)), 0, 8);

if (empty($faqs)) return;
?>

<section class="faq-section" style="padding:50px 0">
  <div class="container">
    <h2 style="text-align:center;margin-bottom:32px"><?= htmlspecialchars($faq_heading) ?></h2>

    <div class="accordion faq-accordion" id="<?= $faq_id ?>" style="max-width:800px;margin:0 auto">
      <?php foreach ($faqs as $i => $faq): ?>
      <div class="accordion-item" style="border:1px solid #e2e8f0;border-radius:8px;margin-bottom:12px;overflow:hidden">
        <h3 class="accordion-header">
          <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button"
                  data-bs-toggle="collapse"
                  data-bs-target="#<?= $faq_id ?>-<?= $i ?>"
                  aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                  style="font-weight:600;font-size:15px;color:#0d1b6e;background:#f8faff;padding:16px 20px">
            <?= htmlspecialchars($faq['question']) ?>
          </button>
        </h3>
        <div id="<?= $faq_id ?>-<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
             data-bs-parent="#<?= $faq_id ?>">
          <div class="accordion-body" style="padding:16px 20px;color:#4a5568;font-size:15px;line-height:1.7">
            <?= $faq['answer'] ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

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
