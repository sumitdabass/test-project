#!/usr/bin/env python3
"""
CSS Build Script for ipu.co.in
Concatenates and minifies CSS files into a single bundle.
"""

import os
import re

BASE_DIR = os.path.join(os.path.dirname(__file__), '..', 'website_download', 'assets', 'css')

# CSS files to bundle (in dependency order)
# Bootstrap and Font Awesome are kept separate (already minified vendor files)
CSS_FILES = [
    'default.css',
    'nice-select.css',
    'magnific-popup.css',
    'slick.css',
    'flaticon.css',
    'style.css',
    'style2.css',
]

def minify_css(css):
    """Basic CSS minification."""
    # Remove comments
    css = re.sub(r'/\*[\s\S]*?\*/', '', css)
    # Remove whitespace around selectors and properties
    css = re.sub(r'\s+', ' ', css)
    # Remove spaces around { } : ; ,
    css = re.sub(r'\s*{\s*', '{', css)
    css = re.sub(r'\s*}\s*', '}', css)
    css = re.sub(r'\s*:\s*', ':', css)
    css = re.sub(r'\s*;\s*', ';', css)
    css = re.sub(r'\s*,\s*', ',', css)
    # Remove trailing semicolons before }
    css = re.sub(r';}', '}', css)
    # Remove empty rules
    css = re.sub(r'[^{}]+{\s*}', '', css)
    return css.strip()


def extract_critical_css(css):
    """Extract critical above-the-fold CSS rules for inlining."""
    critical_selectors = [
        # Reset & base
        r'body\s*{[^}]+}',
        r'\*\s*{[^}]+}',
        r'img\s*{[^}]+}',
        r'a\s*{[^}]+}',
        r'a:focus[^{]*{[^}]+}',
        r'a:hover[^{]*{[^}]+}',
        # Header & nav
        r'\.header-area[^{]*{[^}]+}',
        r'\.header-nav[^{]*{[^}]+}',
        r'\.sticky[^{]*{[^}]+}',
        r'\.navigation[^{]*{[^}]+}',
        r'\.navbar[^{]*{[^}]+}',
        r'\.navbar-toggler[^{]*{[^}]+}',
        r'\.toggler-icon[^{]*{[^}]+}',
        r'\.nav-link[^{]*{[^}]+}',
        r'\.nav-item[^{]*{[^}]+}',
        # Phone bar & CTA
        r'\.ipu-phone-bar[^{]*{[^}]+}',
        r'\.mobile-call-cta[^{]*{[^}]+}',
        r'\.mobile-call-btn[^{]*{[^}]+}',
        r'\.call-btn-container[^{]*{[^}]+}',
        r'\.desktop-call-widget[^{]*{[^}]+}',
        # Hero/banner
        r'\.banner-area[^{]*{[^}]+}',
        r'\.banner-content[^{]*{[^}]+}',
        # Preloader
        r'#preloader[^{]*{[^}]+}',
        # Typography
        r'h[1-6][^{]*{[^}]+}',
        r'\.title[^{]*{[^}]+}',
        # Layout
        r'\.container[^{]*{[^}]+}',
        r'\.row[^{]*{[^}]+}',
    ]

    critical = []
    for selector in critical_selectors:
        matches = re.findall(selector, css, re.IGNORECASE)
        critical.extend(matches)

    return '\n'.join(critical)


def build():
    """Build the CSS bundle."""
    combined = []

    for filename in CSS_FILES:
        filepath = os.path.join(BASE_DIR, filename)
        if not os.path.exists(filepath):
            print(f"  WARNING: {filename} not found, skipping")
            continue

        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()

        # Fix broken @import in style.css
        content = re.sub(r'@import\s+url\(["\']?\.\./\.\./\.\./css["\']?\)\s*;?', '', content)

        combined.append(f'/* === {filename} === */\n{content}')
        print(f"  Added: {filename} ({len(content)} chars)")

    full_css = '\n'.join(combined)

    # Write unminified bundle (for debugging)
    output_path = os.path.join(BASE_DIR, 'bundle.css')
    with open(output_path, 'w', encoding='utf-8') as f:
        f.write(full_css)
    print(f"\nBundle (unminified): {output_path} ({len(full_css)} chars)")

    # Write minified bundle
    minified = minify_css(full_css)
    output_min_path = os.path.join(BASE_DIR, 'bundle.min.css')
    with open(output_min_path, 'w', encoding='utf-8') as f:
        f.write(minified)
    print(f"Bundle (minified): {output_min_path} ({len(minified)} chars)")

    # Extract and write critical CSS
    critical = extract_critical_css(full_css)
    critical_minified = minify_css(critical)
    critical_path = os.path.join(BASE_DIR, 'critical.min.css')
    with open(critical_path, 'w', encoding='utf-8') as f:
        f.write(critical_minified)
    print(f"Critical CSS: {critical_path} ({len(critical_minified)} chars)")

    # Stats
    total_original = sum(
        os.path.getsize(os.path.join(BASE_DIR, f))
        for f in CSS_FILES
        if os.path.exists(os.path.join(BASE_DIR, f))
    )
    print(f"\nOriginal total: {total_original:,} bytes ({len(CSS_FILES)} files)")
    print(f"Minified bundle: {len(minified):,} bytes (1 file)")
    print(f"Savings: {total_original - len(minified):,} bytes ({(1 - len(minified)/total_original)*100:.1f}%)")


if __name__ == '__main__':
    print("Building CSS bundle for ipu.co.in...\n")
    build()
    print("\nDone!")
