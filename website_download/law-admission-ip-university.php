<?php
ob_start();
// 301 Permanent Redirect - law-admission-ip-university.php -> IPU-Law-Admission-2025.php
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://ipu.co.in/IPU-Law-Admission-2025.php");
exit();
?>