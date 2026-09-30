<?php
/**
 * Persistent phone-number dedup — defends against duplicate enquiries that
 * slip past session + cookie dedup (different browsers, cleared cookies,
 * incognito mode, mobile-to-desktop, etc.).
 *
 * Storage: a small flat file containing one SHA-256(phone)|epoch per line.
 * Pruned on read; bounded by PHONE_DEDUP_TTL_SEC.
 *
 * Location: include/.private/phone_dedup.log
 *   - Inside /include/ which is blocked by .htaccess (`RewriteRule ^include/ - [F,L]`)
 *   - Filename starts with a dot to skip default Apache directory listing.
 *
 * Public API:
 *   phone_recently_seen(string $phone): bool   — true if phone was logged in the last TTL window
 *   phone_record_seen(string $phone): void     — append phone hash + now() to the log
 */

if (!defined('PHONE_DEDUP_TTL_SEC')) {
    define('PHONE_DEDUP_TTL_SEC', 7 * 24 * 60 * 60); // 7 days
}

if (!function_exists('_phone_dedup_log_path')) {
    function _phone_dedup_log_path(): string {
        $dir = __DIR__ . '/../.private';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        return $dir . '/phone_dedup.log';
    }
}

if (!function_exists('phone_recently_seen')) {
    function phone_recently_seen(string $phone): bool {
        $phone = trim($phone);
        if ($phone === '') return false;
        $log = _phone_dedup_log_path();
        if (!file_exists($log)) return false;
        $hash = hash('sha256', $phone);
        $cutoff = time() - PHONE_DEDUP_TTL_SEC;
        $fh = @fopen($log, 'r');
        if ($fh === false) return false;
        while (($line = fgets($fh)) !== false) {
            $line = rtrim($line);
            if ($line === '') continue;
            $parts = explode('|', $line, 2);
            if (count($parts) !== 2) continue;
            [$h, $t] = $parts;
            if ((int)$t < $cutoff) continue;
            if ($h === $hash) {
                fclose($fh);
                return true;
            }
        }
        fclose($fh);
        return false;
    }
}

if (!function_exists('phone_record_seen')) {
    function phone_record_seen(string $phone): void {
        $phone = trim($phone);
        if ($phone === '') return;
        $log = _phone_dedup_log_path();
        $hash = hash('sha256', $phone);
        $line = $hash . '|' . time() . PHP_EOL;
        // Open with LOCK_EX to serialize concurrent writes.
        $fh = @fopen($log, 'a');
        if ($fh === false) return;
        if (flock($fh, LOCK_EX)) {
            fwrite($fh, $line);
            fflush($fh);
            flock($fh, LOCK_UN);
        }
        fclose($fh);

        // Opportunistic prune: every ~50th write, rewrite file dropping expired entries.
        // Keeps the file from growing unboundedly without per-write overhead.
        if (mt_rand(1, 50) === 1) {
            _phone_dedup_prune($log);
        }
    }
}

if (!function_exists('_phone_dedup_prune')) {
    function _phone_dedup_prune(string $log): void {
        if (!file_exists($log)) return;
        $cutoff = time() - PHONE_DEDUP_TTL_SEC;
        $keep = [];
        $fh = @fopen($log, 'r');
        if ($fh === false) return;
        while (($line = fgets($fh)) !== false) {
            $line = rtrim($line);
            if ($line === '') continue;
            $parts = explode('|', $line, 2);
            if (count($parts) !== 2) continue;
            if ((int)$parts[1] >= $cutoff) {
                $keep[] = $line;
            }
        }
        fclose($fh);
        $tmp = $log . '.tmp';
        if (@file_put_contents($tmp, implode(PHP_EOL, $keep) . ($keep ? PHP_EOL : ''), LOCK_EX) !== false) {
            @rename($tmp, $log);
        }
    }
}

if (!function_exists('lead_record')) {
    /**
     * Append-only lead capture log for Phase B+ measurement.
     * Records one line per successful submission: ISO timestamp + SHA-256(phone) + source.
     * Never pruned — line count is the lead-volume metric.
     *
     * Storage: include/.private/leads.log (web-blocked by .htaccess; hidden by leading dot).
     */
    function lead_record(string $phone, string $source = ''): void {
        $phone = trim($phone);
        if ($phone === '') return;
        $dir = __DIR__ . '/../.private';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }
        $log = $dir . '/leads.log';
        $line = date('c') . "\t" . hash('sha256', $phone) . "\t" . $source . PHP_EOL;
        $fh = @fopen($log, 'a');
        if ($fh === false) return;
        if (flock($fh, LOCK_EX)) {
            fwrite($fh, $line);
            fflush($fh);
            flock($fh, LOCK_UN);
        }
        fclose($fh);
    }
}
