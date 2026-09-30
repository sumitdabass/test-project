<?php
/**
 * lead-fallback.php — last-resort recoverable store for leads whose
 * primary delivery (email and/or Google Sheet) failed. Writes the FULL
 * lead as one JSON line so nothing is lost. Best-effort, never throws.
 */
if (!function_exists('lead_fallback_save')) {
    function lead_fallback_save(array $lead, string $reason): void {
        $dir = __DIR__ . '/../.private';
        if (!is_dir($dir)) { @mkdir($dir, 0700, true); }
        $file = $dir . '/leads-fallback.jsonl';
        $lead['_reason'] = $reason;
        $lead['_at']     = date('c');
        $line = json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        $fh = @fopen($file, 'a');
        if ($fh === false) { @error_log("lead_fallback_save: cannot open $file"); return; }
        if (flock($fh, LOCK_EX)) { fwrite($fh, $line); fflush($fh); flock($fh, LOCK_UN); }
        @fclose($fh);
    }
}
