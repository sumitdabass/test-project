<?php session_cache_limiter('public'); session_cache_expire(30); session_start(); ob_start(); include_once("include/form-handler.php");
$cp = [
  'slug' => 'mait-cutoff.php',
  'short' => 'MAIT',
  'institute' => 'Maharaja Agrasen Institute of Technology',
  'title' => 'MAIT Cutoff – JEE Main Closing Ranks by Branch & Round (Delhi / Outside)',
  'meta' => 'MAIT Delhi cutoff: JEE Main closing ranks for CSE, IT, ECE and more, Delhi and outside-Delhi quota, rounds 1 to 3. Compare branches and plan your choice filling.',
  'h1' => 'MAIT Cutoff – Branch-wise JEE Main Closing Ranks',
  'intro' => 'Maharaja Agrasen Institute of Technology (MAIT), Rohini, admits B.Tech students through GGSIPU counselling on JEE Main rank. This page shows the closing rank range for each branch, for Delhi-quota and outside-Delhi candidates, across counselling rounds 1 to 3.',
  'published' => '2026-10-30',
  'related' => [
    ['title' => 'MAIT Admission Guide', 'url' => '/mait-admission.php', 'desc' => 'Courses, fees, placements and admission process at MAIT'],
    ['title' => 'IPU B.Tech Cutoff Analysis', 'url' => '/ipu-btech-cutoff-analysis.php', 'desc' => 'Compare closing ranks across all IPU engineering colleges'],
    ['title' => 'B.Tech Management Quota at IPU', 'url' => '/btech-management-quota-ipu.php', 'desc' => 'How management-quota seats work for B.Tech'],
  ],
];
include __DIR__ . '/include/components/college-cutoff-page.php';
