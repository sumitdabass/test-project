<?php
/**
 * Last Updated Component — Visible freshness signal for SEO
 *
 * Usage:
 *   $last_updated = '2026-04-06';   // YYYY-MM-DD format
 *   include 'include/components/last-updated.php';
 *
 * Notes:
 *   - Defaults to today's date when $last_updated is not set
 *   - Renders a <p> with a clock SVG icon, label, and a <time> element
 *   - Date is displayed as "j F Y" (e.g. "6 April 2026")
 */

$last_updated = $last_updated ?? date('Y-m-d');

$timestamp    = strtotime($last_updated);
$display_date = date('j F Y', $timestamp);
$machine_date = date('Y-m-d', $timestamp);
?>
<p style="font-size:13px;color:#64748b;margin-bottom:20px;display:flex;align-items:center;gap:6px">
  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
       fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
       stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0">
    <circle cx="12" cy="12" r="10"></circle>
    <polyline points="12 6 12 12 16 14"></polyline>
  </svg>
  <span>Last Updated:</span>
  <time datetime="<?= htmlspecialchars($machine_date, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
    <?= htmlspecialchars($display_date, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
  </time>
</p>
