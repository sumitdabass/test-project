<?php
/**
 * College page: unified-shell (v2) body.
 * Included by college-page-template.php when $college_data['layout'] === 'v2'.
 * Uses the template's scope: $cd, $short_name, $name, $last_updated.
 */
$hero_h1 = htmlspecialchars($short_name . ' Admission 2026 – Courses, Cutoff & Placements');
$hero_intro = htmlspecialchars($short_name) . ' IPU Admission 2026 – Complete Guide';
$hero_breadcrumbs = [['Home', '/'], ['Colleges', '/ipu-colleges-list.php'], [$short_name . ' College', '']];
$hero_show_form = false;
$hero_show_enquire = true;
include 'include/components/page-hero.php';
unset($hero_h1, $hero_intro, $hero_breadcrumbs, $hero_show_enquire);
?>

<div class="ipu-body">

  <div class="ipu-body__intro">
    <!-- AI Summary (exposed to crawlers & LLMs) -->
    <section id="ai-summary" aria-hidden="true" class="ipu-tint">
      <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
      <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($cd['ai_summary'] ?? '') ?></p>
    </section>

    <!-- About -->
    <?= $cd['about_text'] ?? '' ?>
  </div>

  <div class="ipu-body__aside">
    <?php include 'include/components/sidebar-enquiry.php'; ?>
  </div>

  <div class="ipu-body__rest">

    <!-- Courses Table -->
    <?php if (!empty($cd['courses'])): ?>
    <section>
      <h2>Courses Offered at <?= htmlspecialchars($short_name) ?></h2>
      <div class="ipu-tablewrap">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:#0d1b6e;color:#fff">
              <th style="padding:10px 14px;text-align:left">Programme</th>
              <th style="padding:10px 14px;text-align:left">Duration</th>
              <th style="padding:10px 14px;text-align:left">Seats (Approx)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cd['courses'] as $i => $course): ?>
            <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f8faff' : '' ?>">
              <td style="padding:10px 14px"><?= htmlspecialchars($course['name']) ?></td>
              <td style="padding:10px 14px"><?= htmlspecialchars($course['duration']) ?></td>
              <td style="padding:10px 14px"><?= htmlspecialchars((string)($course['seats'] ?? '')) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <!-- Admission Process -->
    <?php if (!empty($cd['admission_text'])): ?>
    <section>
      <h2><?= htmlspecialchars($short_name) ?> Admission Process 2026</h2>
      <?= $cd['admission_text'] ?>
    </section>
    <?php endif; ?>

    <?php
    $cta_card = true;
    $cta_heading = 'Need Help with ' . $short_name . ' Admission?';
    $cta_subtext = 'Get free expert counselling on cutoffs, choice filling and management quota';
    include 'include/components/cta-strip.php';
    ?>

    <!-- Cutoffs Table (optional) -->
    <?php if (!empty($cd['cutoffs'])): ?>
    <section>
      <h2><?= htmlspecialchars($short_name) ?> Cutoff Trends</h2>
      <div class="ipu-tablewrap">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="background:#0d1b6e;color:#fff">
              <th style="padding:10px 14px;text-align:left">Branch</th>
              <th style="padding:10px 14px;text-align:left">Round 1 Cutoff (Percentile)</th>
              <th style="padding:10px 14px;text-align:left">Last Round Cutoff</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cd['cutoffs'] as $i => $cutoff): ?>
            <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f8faff' : '' ?>">
              <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['branch']) ?></td>
              <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['round1']) ?></td>
              <td style="padding:10px 14px"><?= htmlspecialchars($cutoff['last_round']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
    <?php endif; ?>

    <!-- Placements -->
    <?php if (!empty($cd['placements'])): ?>
    <section>
      <h2>Placements at <?= htmlspecialchars($short_name) ?></h2>
      <p><?= htmlspecialchars($short_name) ?> has a strong placement record among IPU-affiliated colleges. Key highlights:</p>
      <ul>
        <?php foreach ($cd['placements'] as $item): ?>
        <li><strong><?= htmlspecialchars($item['label']) ?>:</strong> <?= htmlspecialchars($item['value']) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <!-- Campus Life -->
    <?php if (!empty($cd['campus_life'])): ?>
    <section>
      <h2>Campus Life at <?= htmlspecialchars($short_name) ?></h2>
      <ul>
        <?php foreach ($cd['campus_life'] as $item): ?>
        <li><?= htmlspecialchars($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <!-- CTA paragraph -->
    <p><?= htmlspecialchars($short_name) ?> is a top choice for students targeting admission under IPU. For personalised admission guidance, call <a href="tel:+919899991342">9899991342</a> today.</p>

    <!-- Fee & Seat Intake (optional) -->
    <?php if (!empty($cd['fees'])): ?>
    <div style="margin:30px 0;padding:24px;background:#f8faff;border-radius:12px;border:1px solid #e2e8f0">
      <h3 style="color:#0d1b6e;margin-bottom:16px">Fee Structure &amp; Seat Intake (2025-26)</h3>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px">As per 6th SFRC, Delhi Gazette Notification dated 14.07.2025</p>
      <div class="ipu-tablewrap"><table style="width:100%;border-collapse:collapse;font-size:14px">
        <thead>
          <tr style="background:#0d1b6e;color:#fff">
            <th style="padding:10px;text-align:left;border-radius:6px 0 0 0">Course</th>
            <th style="padding:10px;text-align:center">Annual Fee</th>
            <th style="padding:10px;text-align:center;border-radius:0 6px 0 0">Seats</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cd['fees'] as $i => $row): ?>
          <tr style="border-bottom:1px solid #e2e8f0<?= $i % 2 === 1 ? ';background:#f0f4ff' : '' ?>">
            <td style="padding:10px"><?= htmlspecialchars($row['course']) ?></td>
            <td style="padding:10px;text-align:center"><?= htmlspecialchars($row['fee']) ?></td>
            <td style="padding:10px;text-align:center"><?= htmlspecialchars((string)($row['seats'] ?? '')) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <p style="font-size:12px;color:#94a3b8;margin:12px 0 0">Source: GGSIPU Official Notification. Additional charges: University fee Rs. 20,000/yr + Exam fee Rs. 3,000/yr + Innovation fee Rs. 500/yr. Alumni contribution Rs. 2,000 (one-time). Total seats at <?= htmlspecialchars($short_name) ?>: ~<?= (int)($cd['total_seats'] ?? 0) ?>.</p>
    </div>
    <?php endif; ?>


    <!-- FAQ -->
    <?php
    $faqs = $cd['faqs'] ?? [];
    include 'include/components/faq-details.php';
    unset($faqs);
    ?>

  </div><!-- /ipu-body__rest -->

</div><!-- /ipu-body -->
