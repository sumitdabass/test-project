#!/usr/bin/env python3
"""
Image Optimization Script for ipu.co.in
Converts images to WebP format and generates responsive sizes.
Requires: pip install Pillow
"""

import os
import sys

try:
    from PIL import Image
except ImportError:
    print("Pillow not installed. Run: pip install Pillow")
    sys.exit(1)

BASE_DIR = os.path.join(os.path.dirname(__file__), '..', 'website_download', 'assets', 'images')
WEBP_QUALITY = 80
RESPONSIVE_WIDTHS = [400, 800, 1200]
SKIP_EXTENSIONS = {'.ico', '.gif', '.svg', '.webp'}
SKIP_FILES = {'favicon.ico', 'call.gif'}


def optimize_image(filepath):
    """Convert a single image to WebP and generate responsive sizes."""
    filename = os.path.basename(filepath)
    name, ext = os.path.splitext(filename)

    if ext.lower() in SKIP_EXTENSIONS or filename in SKIP_FILES:
        return

    try:
        with Image.open(filepath) as img:
            original_size = os.path.getsize(filepath)
            width, height = img.size

            # Convert to RGB if necessary (for PNG with alpha)
            if img.mode in ('RGBA', 'P'):
                rgb_img = Image.new('RGB', img.size, (255, 255, 255))
                if img.mode == 'P':
                    img = img.convert('RGBA')
                rgb_img.paste(img, mask=img.split()[3] if img.mode == 'RGBA' else None)
                img = rgb_img
            elif img.mode != 'RGB':
                img = img.convert('RGB')

            # Save WebP at original size
            webp_path = os.path.join(BASE_DIR, f"{name}.webp")
            img.save(webp_path, 'WEBP', quality=WEBP_QUALITY, method=6)
            webp_size = os.path.getsize(webp_path)

            savings = ((original_size - webp_size) / original_size) * 100
            print(f"  {filename}: {original_size:,}B -> {webp_size:,}B (WebP, -{savings:.0f}%)")

            # Generate responsive sizes (only if image is large enough)
            for target_width in RESPONSIVE_WIDTHS:
                if width > target_width:
                    ratio = target_width / width
                    target_height = int(height * ratio)
                    resized = img.resize((target_width, target_height), Image.LANCZOS)

                    responsive_path = os.path.join(BASE_DIR, f"{name}-{target_width}w.webp")
                    resized.save(responsive_path, 'WEBP', quality=WEBP_QUALITY, method=6)

    except Exception as e:
        print(f"  ERROR processing {filename}: {e}")


def build():
    """Process all images in the assets/images directory."""
    if not os.path.exists(BASE_DIR):
        print(f"Image directory not found: {BASE_DIR}")
        return

    image_files = [
        f for f in os.listdir(BASE_DIR)
        if os.path.isfile(os.path.join(BASE_DIR, f))
        and os.path.splitext(f)[1].lower() in ('.jpg', '.jpeg', '.png')
    ]

    print(f"Found {len(image_files)} images to optimize\n")

    total_original = 0
    total_webp = 0

    for filename in sorted(image_files):
        filepath = os.path.join(BASE_DIR, filename)
        total_original += os.path.getsize(filepath)
        optimize_image(filepath)

        name = os.path.splitext(filename)[0]
        webp_path = os.path.join(BASE_DIR, f"{name}.webp")
        if os.path.exists(webp_path):
            total_webp += os.path.getsize(webp_path)

    print(f"\nTotal original: {total_original:,} bytes")
    print(f"Total WebP: {total_webp:,} bytes")
    if total_original > 0:
        print(f"Total savings: {total_original - total_webp:,} bytes ({(1 - total_webp/total_original)*100:.1f}%)")


if __name__ == '__main__':
    print("Optimizing images for ipu.co.in...\n")
    build()
    print("\nDone!")
