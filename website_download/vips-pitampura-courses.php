<?php
// 301 Redirect — VIPS legacy URL consolidated into vips-admission.php (2026-05-05)
// vips-admission.php is the new canonical brochure-aligned admission page;
// preserves SEO equity from the older listicle-style URL.
header("HTTP/1.1 301 Moved Permanently");
header("Location: https://ipu.co.in/vips-admission.php");
exit();
?>
