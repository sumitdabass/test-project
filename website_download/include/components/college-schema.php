<?php
/**
 * College Schema Component — CollegeOrUniversity JSON-LD for SEO
 *
 * Usage:
 *   $college = [
 *     'name'          => 'Maharaja Agrasen Institute of Technology',
 *     'short_name'    => 'MAIT',
 *     'url'           => 'https://ipu.co.in/mait-admission.php',
 *     'address'       => 'Sector-22, Rohini, Delhi-110085',
 *     'founded'       => '1999',           // optional
 *     'courses'       => ['B.Tech CSE', 'B.Tech IT'],  // optional
 *     'total_seats'   => 780,              // optional
 *     'accreditation' => 'NAAC, AICTE',   // optional
 *   ];
 *   include 'include/components/college-schema.php';
 *
 * Notes:
 *   - parentOrganization is always GGSIPU
 *   - addressRegion: "Delhi NCR", addressCountry: "IN"
 *   - Optional fields (founded, accreditation, total_seats) are omitted when not set
 *   - courses are mapped to the "knowsAbout" property
 *   - Skipped entirely when $college['name'] is empty or unset
 *   - All string values are sanitised with htmlspecialchars
 */

$college = $college ?? [];

if (empty($college['name'])) return;

// Helpers
$esc = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');

$name        = $esc($college['name']);
$url         = $esc($college['url'] ?? '');
$address     = $esc($college['address'] ?? '');
$short_name  = $esc($college['short_name'] ?? $college['name']);
$courses     = $college['courses'] ?? [];
$total_seats = isset($college['total_seats']) ? (int) $college['total_seats'] : null;
$founded     = isset($college['founded']) ? $esc((string) $college['founded']) : null;
$accred      = isset($college['accreditation']) ? $esc($college['accreditation']) : null;
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollegeOrUniversity",
  "name": "<?= $name ?>",
  "alternateName": "<?= $short_name ?>",
  "url": "<?= $url ?>",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "<?= $address ?>",
    "addressRegion": "Delhi NCR",
    "addressCountry": "IN"
  },
  "parentOrganization": {
    "@type": "CollegeOrUniversity",
    "name": "Guru Gobind Singh Indraprastha University",
    "alternateName": "GGSIPU",
    "url": "https://ipu.ac.in"
  }<?php if ($founded !== null): ?>,
  "foundingDate": "<?= $founded ?>"<?php endif; ?><?php if ($accred !== null): ?>,
  "accreditation": "<?= $accred ?>"<?php endif; ?><?php if ($total_seats !== null): ?>,
  "numberOfStudents": <?= $total_seats ?><?php endif; ?><?php if (!empty($courses)): ?>,
  "knowsAbout": [
    <?php foreach ($courses as $i => $course): ?>"<?= $esc((string) $course) ?>"<?= $i < count($courses) - 1 ? ',' : '' ?>
<?php endforeach; ?>
  ]<?php endif; ?>

}
</script>
