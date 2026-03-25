<?php
/**
 * Course Card Component
 *
 * Usage:
 *   $courses = [
 *     ['name' => 'B.Tech', 'exam' => 'JEE Main / CUET', 'url' => '/IPU-B-Tech-admission-2026.php', 'icon' => 'laptop'],
 *     ['name' => 'MBA', 'exam' => 'CAT / CMAT', 'url' => '/mba-admission-ip-university.php', 'icon' => 'briefcase'],
 *   ];
 *   include 'include/components/course-card.php';
 */

$courses = $courses ?? [];
if (empty($courses)) return;

$icons = [
    'laptop' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M2 17h20"/></svg>',
    'briefcase' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
    'scale' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v18M3 7l9-4 9 4M3 7l3 6h6l3-6M15 7l3 6h-6"/></svg>',
    'chart' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>',
    'book' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>',
    'pen' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>',
    'globe' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>',
    'code' => '<svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>',
];
?>

<section style="padding:50px 0">
  <div class="container">
    <h2 style="text-align:center;margin-bottom:12px">IPU Courses & Admissions 2026</h2>
    <p style="text-align:center;color:#4a5568;margin-bottom:36px;max-width:600px;margin-left:auto;margin-right:auto">
      Explore admission guides for all IP University programs with eligibility, process, and college details.
    </p>

    <div class="row g-4">
      <?php foreach ($courses as $course): ?>
      <div class="col-lg-3 col-md-4 col-6">
        <a href="<?= htmlspecialchars($course['url']) ?>" style="display:block;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px 16px;text-align:center;text-decoration:none;transition:all .2s;height:100%"
           onmouseover="this.style.borderColor='#1a3a9c';this.style.boxShadow='0 4px 20px rgba(26,58,156,.12)';this.style.transform='translateY(-4px)'"
           onmouseout="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';this.style.transform='none'">
          <div style="width:56px;height:56px;background:#f0f4ff;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#1a3a9c">
            <?= $icons[$course['icon'] ?? 'book'] ?? $icons['book'] ?>
          </div>
          <h4 style="font-size:16px;color:#0d1b6e;margin-bottom:4px"><?= htmlspecialchars($course['name']) ?></h4>
          <p style="font-size:13px;color:#4a5568;margin:0"><?= htmlspecialchars($course['exam'] ?? '') ?></p>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
