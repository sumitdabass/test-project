<?php
/**
 * Visible "college facts" section for pages that already carry hand-written FAQPage JSON-LD.
 * Renders the same sourced facts as college-facts-faq.php but as plain headings and paragraphs, with NO schema,
 * so the page never ends up with two FAQPage blocks.
 * Usage: $facts_key = 'bpit'; include 'include/components/college-facts-block.php';
 */
$__saved_faqs = $faqs ?? null;
$faqs = [];
include __DIR__ . '/college-facts-faq.php';
$__items = $faqs;
$faqs = $__saved_faqs;
if ($__items): ?>
<section style="padding:30px 0;border-top:1px solid #e2e8f0">
  <div class="container">
    <h2 style="font-size:1.4rem;color:#0d1b6e;margin-bottom:16px">Key facts from the college</h2>
    <?php foreach ($__items as $__i): ?>
      <h3 style="font-size:1.05rem;color:#0d1b6e;margin:16px 0 4px"><?= htmlspecialchars($__i['question']) ?></h3>
      <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($__i['answer']) ?></p>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
