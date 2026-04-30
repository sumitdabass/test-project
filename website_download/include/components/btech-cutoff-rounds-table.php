<?php
/**
 * B.Tech Cutoff Rounds Table — renders Round 1/2/3 cutoff data for one institute
 *
 * Source: GGSIPU 2025-26 official counselling cutoff
 * Data:   include/data/btech-cutoffs-2025.php (auto-generated)
 *
 * Usage:
 *   $cutoff_institute = 'Maharaja Agrasen Institute of Technology';
 *   include 'include/components/btech-cutoff-rounds-table.php';
 *
 * Renders nothing if the institute key isn't in the dataset (safe to include).
 */

$cutoff_institute = $cutoff_institute ?? null;
if (!$cutoff_institute) return;

static $btech_cutoff_data_2025 = null;
if ($btech_cutoff_data_2025 === null) {
    $btech_cutoff_data_2025 = include __DIR__ . '/../data/btech-cutoffs-2025.php';
}

$rows = $btech_cutoff_data_2025[$cutoff_institute] ?? null;
if (!$rows) return;

$fmt = function ($cell) {
    if (!$cell || !isset($cell['min']) || !isset($cell['max'])) return '<span style="color:#94a3b8">—</span>';
    return number_format((int) $cell['min']) . ' – ' . number_format((int) $cell['max']);
};

$cutoff_table_id = 'btc-' . substr(md5($cutoff_institute), 0, 8);
?>

<section style="padding:40px 0;border-top:1px solid #e2e8f0">
  <div class="container">
    <h2 style="font-size:1.5rem;color:#0d1b6e;margin-bottom:8px">B.Tech Cutoff 2025 — Round-wise (Rank Range)</h2>
    <p style="font-size:14px;color:#4a5568;margin-bottom:8px">
      JEE Main rank range (Min – Max) at which seats closed in each counselling round at <strong><?= htmlspecialchars($cutoff_institute) ?></strong>.
      Lower numbers mean tighter competition. Source: GGSIPU 2025-26 official counselling.
    </p>
    <p style="font-size:13px;color:#64748b;margin-bottom:20px">
      Need help reading these numbers or planning your choice list? Call <a href="tel:9899991342" style="color:#e65c00;font-weight:600">9899991342</a> for free guidance.
    </p>

    <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;border:1px solid #e2e8f0;border-radius:8px">
      <table id="<?= $cutoff_table_id ?>" style="width:100%;min-width:780px;border-collapse:collapse;font-size:13px">
        <thead>
          <tr style="background:#0d1b6e;color:#fff">
            <th rowspan="2" style="padding:10px;text-align:left;border-right:1px solid rgba(255,255,255,.15)">Branch</th>
            <th colspan="2" style="padding:10px;text-align:center;border-right:1px solid rgba(255,255,255,.15)">Round 1</th>
            <th colspan="2" style="padding:10px;text-align:center;border-right:1px solid rgba(255,255,255,.15)">Round 2</th>
            <th colspan="2" style="padding:10px;text-align:center">Round 3</th>
          </tr>
          <tr style="background:#1a3a9c;color:#fff;font-size:12px">
            <th style="padding:8px;text-align:center;font-weight:600">Delhi</th>
            <th style="padding:8px;text-align:center;font-weight:600;border-right:1px solid rgba(255,255,255,.15)">Outside</th>
            <th style="padding:8px;text-align:center;font-weight:600">Delhi</th>
            <th style="padding:8px;text-align:center;font-weight:600;border-right:1px solid rgba(255,255,255,.15)">Outside</th>
            <th style="padding:8px;text-align:center;font-weight:600">Delhi</th>
            <th style="padding:8px;text-align:center;font-weight:600">Outside</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 0; foreach ($rows as $branch => $rounds): $bg = $i++ % 2 === 0 ? '#fff' : '#f8faff'; ?>
          <tr style="background:<?= $bg ?>;border-top:1px solid #e2e8f0">
            <td style="padding:10px;font-weight:600;color:#0d1b6e"><?= htmlspecialchars($branch) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap"><?= $fmt($rounds['round_1']['delhi'] ?? null) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap;border-right:1px solid #e2e8f0"><?= $fmt($rounds['round_1']['outside'] ?? null) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap"><?= $fmt($rounds['round_2']['delhi'] ?? null) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap;border-right:1px solid #e2e8f0"><?= $fmt($rounds['round_2']['outside'] ?? null) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap"><?= $fmt($rounds['round_3']['delhi'] ?? null) ?></td>
            <td style="padding:10px;text-align:center;white-space:nowrap"><?= $fmt($rounds['round_3']['outside'] ?? null) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="font-size:12px;color:#94a3b8;margin:12px 0 0">
      Notes: Rank values are the JEE Main All India Rank used during GGSIPU counselling. "Delhi" = Delhi Region quota seats; "Outside" = Outside Delhi Region seats. "—" indicates the branch had no allotment in that round / region. Cutoffs vary year-to-year; treat 2025 numbers as guidance, not a guarantee.
    </p>
  </div>
</section>
<?php unset($cutoff_institute); ?>
