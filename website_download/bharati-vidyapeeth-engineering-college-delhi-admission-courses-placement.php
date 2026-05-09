<?php
// 301 Redirect — filename promised Bharati Vidyapeeth's College of Engineering
// but legacy content was about MAIT/MAIMS Rohini. Consolidating to BVP.php
// (the correct page for Bharati Vidyapeeth's College of Engineering, Paschim Vihar —
// brochure SN 11, UG 2026-27 Ch 13). MAIT/MAIMS content lives at /mait-admission.php
// and /exploring-MAIT-and-MAIMS.php. Audit-driven cleanup, 2026-05-07.
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://ipu.co.in/BVP.php");
exit();
?>
