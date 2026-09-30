<?php session_cache_limiter('public'); session_cache_expire(30); session_start(); ob_start(); include_once("include/form-handler.php");
$cp = [
  'slug' => 'msit-cutoff.php',
  'short' => 'MSIT',
  'institute' => 'Maharaja Surajmal Institute Technology',
  'title' => 'MSIT Cutoff – JEE Main Closing Ranks by Branch & Round (Delhi / Outside)',
  'meta' => 'MSIT Janakpuri cutoff: JEE Main closing ranks for CSE, IT, ECE and more, Delhi and outside-Delhi quota, rounds 1 to 3. Compare branches and plan your choice filling.',
  'h1' => 'MSIT Cutoff – Branch-wise JEE Main Closing Ranks',
  'intro' => 'Maharaja Surajmal Institute of Technology (MSIT), Janakpuri, admits B.Tech students through GGSIPU counselling on JEE Main rank. This page shows the closing rank range for each branch, for Delhi-quota and outside-Delhi candidates, across counselling rounds 1 to 3.',
  'published' => '2026-10-30',
  'related' => [
    ['title' => 'MSIT Admission Guide', 'url' => '/msit-admission.php', 'desc' => 'Courses, fees, placements and admission process at MSIT'],
    ['title' => 'IPU B.Tech Cutoff Analysis', 'url' => '/ipu-btech-cutoff-analysis.php', 'desc' => 'Compare closing ranks across all IPU engineering colleges'],
    ['title' => 'B.Tech Management Quota at IPU', 'url' => '/btech-management-quota-ipu.php', 'desc' => 'How management-quota seats work for B.Tech'],
  ],
];
include __DIR__ . '/include/components/college-cutoff-page.php';
