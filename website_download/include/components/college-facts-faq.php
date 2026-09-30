<?php
/**
 * Appends FAQ entries from college-facts-2026.json to $faqs. Only fields with BOTH source and as_of render.
 * Usage: $facts_key = 'mait'; include 'include/components/college-facts-faq.php'; (before faq-section.php)
 * No phone numbers in answers (CTA Wave 2a strips them from FAQ JSON-LD).
 */
$faqs = $faqs ?? [];
$facts_key = $facts_key ?? null;
$__file = $facts_file_override ?? (__DIR__ . '/../data/college-facts-2026.json');
$__all = is_file($__file) ? json_decode(file_get_contents($__file), true) : [];
$__c = ($facts_key && is_array($__all)) ? ($__all[$facts_key] ?? null) : null;
if ($__c) {
    $__ok = fn($f) => is_array($f) && !empty($f['source']) && !empty($f['as_of']);
    $__short = $__c['short'] ?? strtoupper($facts_key);
    $__note = fn($f) => !empty($f['note']) ? ' (' . $f['note'] . ')' : '';
    $__cite = fn($f) => ' (Source: ' . $f['source'] . ', as of ' . $f['as_of'] . '.)';
    if (isset($__c['campus_area_acres']) && $__ok($__c['campus_area_acres'])) {
        $f = $__c['campus_area_acres'];
        $faqs[] = ['question' => "What is the campus area of $__short?", 'answer' => "$__short's campus covers about {$f['v']} acres." . $__cite($f)];
    }
    if (isset($__c['avg_package_cse_lpa']) && $__ok($__c['avg_package_cse_lpa'])) {
        $f = $__c['avg_package_cse_lpa'];
        $faqs[] = ['question' => "What is the average CSE package at $__short?", 'answer' => "The average package reported for CSE at $__short is {$f['v']} LPA" . $__note($f) . "." . $__cite($f)];
    }
    if (isset($__c['avg_package_lpa']) && $__ok($__c['avg_package_lpa'])) {
        $f = $__c['avg_package_lpa'];
        $faqs[] = ['question' => "What is the average package at $__short?", 'answer' => "The average package reported at $__short is {$f['v']} LPA" . $__note($f) . "." . $__cite($f)];
    }
    if (isset($__c['highest_package_lpa']) && $__ok($__c['highest_package_lpa'])) {
        $f = $__c['highest_package_lpa'];
        $faqs[] = ['question' => "What is the highest package at $__short?", 'answer' => "The highest package reported at $__short is {$f['v']} LPA" . $__note($f) . "." . $__cite($f)];
    }
    if (isset($__c['accreditation']) && $__ok($__c['accreditation'])) {
        $f = $__c['accreditation'];
        $faqs[] = ['question' => "What accreditation does $__short have?", 'answer' => "$__short: {$f['v']}." . $__cite($f)];
    }
    if (isset($__c['intake']) && $__ok($__c['intake']) && is_array($__c['intake']['v'] ?? null)) {
        $f = $__c['intake'];
        $parts = array_map(fn($r) => $r['programme'] . ' ' . $r['seats'], $f['v']);
        $faqs[] = ['question' => "What is the intake at $__short?", 'answer' => "Intake listed by $__short: " . implode('; ', $parts) . "." . $__cite($f)];
    }
    // Indian digit grouping (1,60,100), as used in the site's fee tables.
    $__inr = function (int $n): string {
        $s = (string) $n;
        if (strlen($s) <= 3) { return $s; }
        return preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($s, 0, -3)) . ',' . substr($s, -3);
    };
    foreach (($__c['fees'] ?? []) as $fee) {
        if ($__ok($fee) && isset($fee['course'], $fee['per_year_inr'])) {
            $faqs[] = ['question' => "What are the {$fee['course']} fees at $__short?",
                       'answer' => "{$fee['course']} fee at $__short: Rs. " . $__inr((int) $fee['per_year_inr']) . (!empty($fee['note']) ? ' (' . $fee['note'] . ')' : ' per year') . "." . $__cite($fee)];
        }
    }
}
