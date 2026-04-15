<?php
class TestCase {
    public static int $passed = 0;
    public static int $failed = 0;
    public static array $failures = [];

    public static function assertEqual($expected, $actual, string $msg = ''): void {
        if ($expected === $actual) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected: " . var_export($expected, true) . "\n  Actual:   " . var_export($actual, true);
        }
    }

    public static function assertContains(string $needle, string $haystack, string $msg = ''): void {
        if (strpos($haystack, $needle) !== false) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected to find: $needle\n  In: " . substr($haystack, 0, 200);
        }
    }

    public static function assertNotContains(string $needle, string $haystack, string $msg = ''): void {
        if (strpos($haystack, $needle) === false) {
            self::$passed++;
        } else {
            self::$failed++;
            self::$failures[] = "FAIL: $msg\n  Expected NOT to find: $needle";
        }
    }

    public static function assertTrue($actual, string $msg = ''): void {
        self::assertEqual(true, (bool)$actual, $msg);
    }

    public static function report(): int {
        echo "\n" . self::$passed . " passed, " . self::$failed . " failed\n";
        foreach (self::$failures as $f) { echo "\n$f\n"; }
        return self::$failed > 0 ? 1 : 0;
    }
}
