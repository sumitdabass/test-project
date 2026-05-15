<?php
/**
 * Last Updated + Byline — Visible freshness + E-E-A-T signal for SEO/AI
 *
 * Usage:
 *   $last_updated = '2026-04-06';   // YYYY-MM-DD format (optional, defaults to today)
 *   $byline       = 'IPU Admission Team';  // optional, defaults to "IPU Admission Team"
 *   include 'include/components/last-updated.php';
 */

$last_updated       = $last_updated       ?? date('Y-m-d');
$byline             = $byline             ?? 'IPU Admission Team';
$last_updated_theme = $last_updated_theme ?? 'light';   // 'light' (gray on white) or 'dark' (white-ish on navy hero)

$_lu_color  = $last_updated_theme === 'dark' ? 'rgba(255,255,255,.85)' : '#64748b';
$_lu_dot    = $last_updated_theme === 'dark' ? 'rgba(255,255,255,.4)'  : '#cbd5e1';

$timestamp    = strtotime($last_updated);
$display_date = date('j F Y', $timestamp);
$machine_date = date('Y-m-d', $timestamp);
?>
<p style="font-size:13px;color:<?= $_lu_color ?>;margin:0 0 20px;display:flex;align-items:center;flex-wrap:wrap;gap:6px 12px">
  <span style="display:inline-flex;align-items:center;gap:6px">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
         stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0">
      <circle cx="12" cy="12" r="10"></circle>
      <polyline points="12 6 12 12 16 14"></polyline>
    </svg>
    Last Updated:
    <time datetime="<?= htmlspecialchars($machine_date, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
      <?= htmlspecialchars($display_date, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
    </time>
  </span>
  <span aria-hidden="true" style="color:<?= $_lu_dot ?>">&middot;</span>
  <span style="display:inline-flex;align-items:center;gap:6px" rel="author">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
         stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0">
      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
      <circle cx="12" cy="7" r="4"></circle>
    </svg>
    By <?= htmlspecialchars($byline, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>
  </span>
</p>
