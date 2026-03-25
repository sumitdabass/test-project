#!/usr/bin/env python3
"""
Page Migration Script for ipu.co.in
Migrates existing PHP pages from old template (common-head + header + footer + jQuery)
to new template (base-head + base-nav + base-footer + vanilla JS).

Preserves all content, meta tags, and schema markup.
"""

import os
import re
import glob

WEBSITE_DIR = os.path.join(os.path.dirname(__file__), '..', 'website_download')

# Pages to skip (already using new template, or special pages)
SKIP_FILES = {
    'index.php',        # Already migrated
    'index-new.php',    # New version
    'index-old.php',    # Backup
    'sendemail.php',    # Backend handler
    'thank-you.php',    # Special page (will migrate separately)
    # New pages already using new template
    'usict-admission.php',
    'usar-admission.php',
    'usls-admission.php',
    'usms-admission.php',
    'bcom-admission-ipu.php',
    'ba-english-admission-ipu.php',
    'ba-economics-admission-ipu.php',
    'college-admission-delhi.php',
    'top-btech-colleges-delhi.php',
    'ipu-colleges-list.php',
    'mait-admission.php',
    'msit-admission.php',
}


def migrate_page(filepath):
    """Migrate a single PHP page to the new template."""
    filename = os.path.basename(filepath)

    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()

    original = content

    # 1. Replace common-head.php with base-head.php
    content = content.replace('include_once("include/common-head.php")', 'include_once("include/base-head.php")')
    content = content.replace("include_once('include/common-head.php')", 'include_once("include/base-head.php")')
    content = content.replace('include("include/common-head.php")', 'include("include/base-head.php")')
    content = content.replace('include_once("include/head.php")', 'include_once("include/base-head.php")')

    # 2. Replace header.php with base-nav.php
    content = content.replace('include_once("include/header.php")', 'include_once("include/base-nav.php")')
    content = content.replace("include_once('include/header.php')", 'include_once("include/base-nav.php")')
    content = content.replace('include("include/header.php")', 'include("include/base-nav.php")')
    content = content.replace('include_once("include/header2.php")', 'include_once("include/base-nav.php")')
    # Handle the quirky spacing variant
    content = re.sub(r'<\?php\s+include_once\("include/header\.php"\)\s*\?>', '<?php include_once("include/base-nav.php"); ?>', content)
    content = re.sub(r'<\?php\s+include_once\("include/header\.php"\)\s*;?\s*\?>', '<?php include_once("include/base-nav.php"); ?>', content)

    # 3. Replace footer.php with base-footer.php
    content = content.replace('include_once("include/footer.php")', 'include_once("include/base-footer.php")')
    content = content.replace("include_once('include/footer.php')", 'include_once("include/base-footer.php")')
    content = content.replace('include("include/footer.php")', 'include("include/base-footer.php")')

    # 4. Replace form-code.php / form-codecopy.php with form-handler.php
    content = content.replace('include_once("include/form-code.php")', 'include_once("include/form-handler.php")')
    content = content.replace('include_once("include/form-codecopy.php")', 'include_once("include/form-handler.php")')
    content = content.replace("include_once('include/form-codecopy.php')", 'include_once("include/form-handler.php")')

    # 5. Remove old style2.css link (now bundled)
    content = re.sub(r'<link\s+rel="stylesheet"\s+href="assets/css/style2\.css"\s*/?\s*>', '', content)

    # 6. Remove old jQuery and JS script tags (base-footer.php includes new ones)
    content = re.sub(r'<script\s+src="assets/js/vendor/jquery-1\.12\.4\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/bootstrap\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/main\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/popper\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/slick\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/isotope\.pkgd\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/imagesloaded\.pkgd\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/jquery\.[a-z.-]+\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/waypoints\.min\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/ajax-contact\.js"\s*>\s*</script>\s*', '', content)
    content = re.sub(r'<script\s+src="assets/js/vendor/modernizr-3\.6\.0\.min\.js"\s*>\s*</script>\s*', '', content)

    # 7. Replace blog-sidebar.php with sidebar-cta.php
    content = content.replace('include_once("include/blog-sidebar.php")', 'include_once("include/sidebar-cta.php")')
    content = content.replace("include_once('include/blog-sidebar.php')", 'include_once("include/sidebar-cta.php")')
    content = content.replace('include("include/blog-sidebar.php")', 'include("include/sidebar-cta.php")')

    # 8. Replace banner-enquiry.php / bigin-sidebar-form.php with sidebar-cta.php
    content = content.replace('include_once("include/banner-enquiry.php")', 'include_once("include/sidebar-cta.php")')
    content = content.replace('include_once("include/bigin-sidebar-form.php")', 'include_once("include/sidebar-cta.php")')

    # 9. Replace floating-call-button.php include (now in base-nav.php)
    content = re.sub(r'<\?php\s+include_once\("include/floating-call-button\.php"\)\s*;?\s*\?>\s*', '', content)

    # 10. Add loading="lazy" to images that don't have it
    content = re.sub(
        r'<img\s+(?!.*loading=)src="(assets/images/[^"]+)"',
        r'<img loading="lazy" src="\1"',
        content
    )

    # 11. Update Bootstrap 4 data attributes to Bootstrap 5
    content = content.replace('data-toggle="collapse"', 'data-bs-toggle="collapse"')
    content = content.replace('data-target="', 'data-bs-target="')
    content = content.replace('data-toggle="tab"', 'data-bs-toggle="tab"')
    content = content.replace('data-toggle="pill"', 'data-bs-toggle="pill"')
    content = content.replace('data-dismiss="', 'data-bs-dismiss="')
    content = content.replace('data-toggle="modal"', 'data-bs-toggle="modal"')
    content = content.replace('data-parent="', 'data-bs-parent="')

    # 12. Fix duplicate required attributes
    content = re.sub(r'(required)\s+required', r'\1', content)

    # 13. Fix duplicate id attributes
    content = re.sub(r'(id="[^"]+"\s+)id="[^"]+"\s+', r'\1', content)

    # Check if anything changed
    if content == original:
        return False

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    return True


def migrate_all():
    """Migrate all PHP pages."""
    php_files = glob.glob(os.path.join(WEBSITE_DIR, '*.php'))

    migrated = 0
    skipped = 0
    unchanged = 0

    for filepath in sorted(php_files):
        filename = os.path.basename(filepath)

        if filename in SKIP_FILES:
            skipped += 1
            continue

        if migrate_page(filepath):
            migrated += 1
            print(f"  Migrated: {filename}")
        else:
            unchanged += 1
            print(f"  Unchanged: {filename}")

    # Also migrate course/index.php if it exists
    course_index = os.path.join(WEBSITE_DIR, 'course', 'index.php')
    if os.path.exists(course_index):
        if migrate_page(course_index):
            migrated += 1
            print(f"  Migrated: course/index.php")

    print(f"\nResults: {migrated} migrated, {skipped} skipped, {unchanged} unchanged")
    print(f"Total files processed: {migrated + skipped + unchanged}")


if __name__ == '__main__':
    print("Migrating pages to new template...\n")
    migrate_all()
    print("\nDone!")
