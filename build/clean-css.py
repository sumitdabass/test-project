#!/usr/bin/env python3
"""
CSS Cleaner — Remove unused old theme styles from the bundle.
Removes selectors for features never used on ipu.co.in:
shop, product, pricing, gallery, project, slick (removed jQuery slider),
feature-item, services-item, magnific-popup, nice-select, isotope
"""

import re
import os

BUNDLE_PATH = os.path.join(os.path.dirname(__file__), '..', 'website_download', 'assets', 'css', 'bundle.css')
OUTPUT_PATH = os.path.join(os.path.dirname(__file__), '..', 'website_download', 'assets', 'css', 'bundle.min.css')

# Prefixes of CSS selectors to remove (unused old theme components)
REMOVE_PREFIXES = [
    '.shop-', '.product-', '.pricing-', '.gallery-', '.project-',
    '.feature-item', '.feature-area', '.feature-box',
    '.services-item', '.services-area',
    '#gallerySlider', '.gallery-dots', '.gallery-arrows',
    '.slick-', '.slick ',
    '.mfp-', '.magnific-',
    '.nice-select',
    '.isotope-',
    '#quantityDown', '#quantityUP', '#quantity',
    '.progress-line', '.count-box',
    '.expert-', '#expert-slider',
]

def should_remove_rule(selector):
    """Check if a CSS rule should be removed based on its selector."""
    sel_lower = selector.strip().lower()
    for prefix in REMOVE_PREFIXES:
        if prefix.lower() in sel_lower:
            return True
    return False

def remove_unused_rules(css):
    """Remove CSS rules matching unused selectors."""
    # Split into rules (handling media queries)
    cleaned = []
    removed = 0

    # Process media queries and regular rules
    i = 0
    while i < len(css):
        # Find next rule or media query
        if css[i:].lstrip().startswith('@media'):
            # Find the media query block
            start = css.index('{', i)
            depth = 1
            j = start + 1
            while j < len(css) and depth > 0:
                if css[j] == '{': depth += 1
                elif css[j] == '}': depth -= 1
                j += 1
            block = css[i:j]
            # Check if entire media query content should be removed
            inner = css[start+1:j-1]
            if should_remove_rule(inner):
                removed += 1
                i = j
                continue
            cleaned.append(block)
            i = j
        elif css[i:].lstrip().startswith('@'):
            # Other @ rules (keyframes, font-face)
            start = css.index('{', i) if '{' in css[i:] else len(css)
            if start < len(css):
                depth = 1
                j = start + 1
                while j < len(css) and depth > 0:
                    if css[j] == '{': depth += 1
                    elif css[j] == '}': depth -= 1
                    j += 1
                block = css[i:j]
                cleaned.append(block)
                i = j
            else:
                i = len(css)
        else:
            # Regular rule
            brace = css.find('{', i)
            if brace == -1:
                cleaned.append(css[i:])
                break
            selector = css[i:brace]
            # Find closing brace
            depth = 1
            j = brace + 1
            while j < len(css) and depth > 0:
                if css[j] == '{': depth += 1
                elif css[j] == '}': depth -= 1
                j += 1

            if should_remove_rule(selector):
                removed += 1
            else:
                cleaned.append(css[i:j])
            i = j

    return ''.join(cleaned), removed

def minify(css):
    """Basic CSS minification."""
    css = re.sub(r'/\*[\s\S]*?\*/', '', css)
    css = re.sub(r'\s+', ' ', css)
    css = re.sub(r'\s*{\s*', '{', css)
    css = re.sub(r'\s*}\s*', '}', css)
    css = re.sub(r'\s*:\s*', ':', css)
    css = re.sub(r'\s*;\s*', ';', css)
    css = re.sub(r'\s*,\s*', ',', css)
    css = re.sub(r';}', '}', css)
    css = re.sub(r'[^{}]+{\s*}', '', css)
    return css.strip()

def main():
    with open(BUNDLE_PATH, 'r', encoding='utf-8', errors='ignore') as f:
        css = f.read()

    original_size = len(css)
    print(f"Original bundle: {original_size:,} chars")

    # Remove unused rules
    cleaned, removed_count = remove_unused_rules(css)
    print(f"Removed ~{removed_count} unused rule blocks")

    # Also remove the entire magnific-popup.css section
    cleaned = re.sub(r'/\*\s*===\s*magnific-popup\.css\s*===\s*\*/.*?(?=/\*\s*===|$)', '', cleaned, flags=re.DOTALL)
    # Remove nice-select.css section
    cleaned = re.sub(r'/\*\s*===\s*nice-select\.css\s*===\s*\*/.*?(?=/\*\s*===|$)', '', cleaned, flags=re.DOTALL)
    # Remove slick.css section
    cleaned = re.sub(r'/\*\s*===\s*slick\.css\s*===\s*\*/.*?(?=/\*\s*===|$)', '', cleaned, flags=re.DOTALL)

    cleaned_size = len(cleaned)
    print(f"After cleanup: {cleaned_size:,} chars ({original_size - cleaned_size:,} removed)")

    # Minify
    minified = minify(cleaned)
    print(f"After minify: {len(minified):,} chars")
    print(f"Total savings: {original_size - len(minified):,} chars ({(1 - len(minified)/original_size)*100:.1f}%)")

    with open(OUTPUT_PATH, 'w', encoding='utf-8') as f:
        f.write(minified)

    print(f"\nWritten to: {OUTPUT_PATH}")

if __name__ == '__main__':
    main()
