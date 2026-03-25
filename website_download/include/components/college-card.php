<?php
/**
 * College Card Component
 *
 * Usage:
 *   $colleges = [
 *     ['name' => 'MAIT', 'full_name' => 'Maharaja Agrasen Institute of Technology', 'location' => 'Rohini', 'url' => '/mait-admission.php', 'img' => 'assets/images/exploring-MAIT-and-MAIMS.jpg'],
 *   ];
 *   $college_section_title = "Top IPU Colleges";
 *   include 'include/components/college-card.php';
 */

$colleges = $colleges ?? [];
$college_section_title = $college_section_title ?? 'Top IPU Affiliated Colleges';
if (empty($colleges)) return;
?>

<section style="padding:50px 0;background:#f8faff">
  <div class="container">
    <h2 style="text-align:center;margin-bottom:32px"><?= htmlspecialchars($college_section_title) ?></h2>

    <div class="row g-4">
      <?php foreach ($colleges as $college): ?>
      <div class="col-lg-4 col-md-6">
        <a href="<?= htmlspecialchars($college['url']) ?>" style="display:block;background:#fff;border-radius:12px;overflow:hidden;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,.06);transition:all .2s;height:100%"
           onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.1)';this.style.transform='translateY(-4px)'"
           onmouseout="this.style.boxShadow='0 1px 3px rgba(0,0,0,.06)';this.style.transform='none'">
          <?php if (!empty($college['img'])): ?>
          <div style="height:180px;overflow:hidden;background:#f0f4ff">
            <?php include_once __DIR__ . '/../image-helper.php'; ?>
            <?php webp_img($college['img'], $college['name'] . ' College', 'img-fluid', true); ?>
          </div>
          <?php endif; ?>
          <div style="padding:20px">
            <h3 style="font-size:18px;color:#0d1b6e;margin-bottom:4px"><?= htmlspecialchars($college['name']) ?></h3>
            <?php if (!empty($college['full_name'])): ?>
              <p style="font-size:13px;color:#4a5568;margin-bottom:8px"><?= htmlspecialchars($college['full_name']) ?></p>
            <?php endif; ?>
            <?php if (!empty($college['location'])): ?>
              <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#64748b;background:#f0f4ff;padding:4px 10px;border-radius:20px">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?= htmlspecialchars($college['location']) ?>
              </span>
            <?php endif; ?>
            <div style="margin-top:12px;color:#1a3a9c;font-size:14px;font-weight:600">
              View Details →
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
