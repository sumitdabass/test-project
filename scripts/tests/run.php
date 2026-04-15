<?php
require __DIR__ . '/TestCase.php';
$files = glob(__DIR__ . '/test_*.php');
foreach ($files as $f) {
    echo "Running " . basename($f) . "...\n";
    require $f;
}
exit(TestCase::report());
