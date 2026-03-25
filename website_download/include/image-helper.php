<?php
/**
 * Image Helper - WebP with fallback and lazy loading
 * Usage: <?php webp_img('assets/images/photo.jpg', 'Alt text', 'img-fluid', true); ?>
 */

function webp_img($src, $alt = '', $class = '', $lazy = true) {
    $webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $src);
    $loading = $lazy ? 'loading="lazy"' : '';
    $classAttr = $class ? "class=\"$class\"" : '';

    // Try to get image dimensions for CLS prevention
    $width = '';
    $height = '';
    $fullPath = __DIR__ . '/../' . $src;
    if (file_exists($fullPath)) {
        $size = @getimagesize($fullPath);
        if ($size) {
            $width = "width=\"{$size[0]}\"";
            $height = "height=\"{$size[1]}\"";
        }
    }

    echo "<picture>";
    echo "<source srcset=\"$webp\" type=\"image/webp\">";
    echo "<img src=\"$src\" alt=\"" . htmlspecialchars($alt) . "\" $classAttr $loading $width $height>";
    echo "</picture>";
}

/**
 * Responsive image with WebP and srcset
 * Usage: <?php responsive_img('assets/images/banner.jpg', 'Banner', 'img-fluid', false); ?>
 */
function responsive_img($src, $alt = '', $class = '', $lazy = true) {
    $name = pathinfo($src, PATHINFO_FILENAME);
    $dir = pathinfo($src, PATHINFO_DIRNAME);
    $loading = $lazy ? 'loading="lazy"' : '';
    $classAttr = $class ? "class=\"$class\"" : '';

    // Build srcset for WebP
    $webpSrcset = [];
    $sizes = [400, 800, 1200];
    foreach ($sizes as $w) {
        $responsivePath = "$dir/$name-{$w}w.webp";
        if (file_exists(__DIR__ . '/../' . $responsivePath)) {
            $webpSrcset[] = "$responsivePath {$w}w";
        }
    }
    // Add original WebP
    $webp = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $src);
    $webpSrcset[] = "$webp 1600w";

    $srcsetAttr = implode(', ', $webpSrcset);

    // Get dimensions
    $width = '';
    $height = '';
    $fullPath = __DIR__ . '/../' . $src;
    if (file_exists($fullPath)) {
        $size = @getimagesize($fullPath);
        if ($size) {
            $width = "width=\"{$size[0]}\"";
            $height = "height=\"{$size[1]}\"";
        }
    }

    echo "<picture>";
    echo "<source srcset=\"$srcsetAttr\" sizes=\"(max-width: 576px) 400px, (max-width: 992px) 800px, 1200px\" type=\"image/webp\">";
    echo "<img src=\"$src\" alt=\"" . htmlspecialchars($alt) . "\" $classAttr $loading $width $height>";
    echo "</picture>";
}
?>
