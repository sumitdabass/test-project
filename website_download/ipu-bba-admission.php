<?php
ob_start();
// 301 Redirect - clean URL for BBA admission
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://ipu.co.in/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php");
exit();
?>