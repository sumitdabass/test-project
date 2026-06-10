#!/usr/bin/env python3
"""
FTP Upload Script — Deploy the complete ipu.co.in overhaul
Uploads all new and modified files to the production server.

Usage: python3 upload_overhaul.py
"""

import ftplib
import os
import sys

# FTP Configuration
FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
REMOTE_BASE = "/public_html"

LOCAL_BASE = os.path.join(os.path.dirname(__file__), "website_download")

# Files to upload (relative to website_download/)
FILES_TO_UPLOAD = [
    # === NEW TEMPLATE FILES ===
    "include/base-head.php",
    "include/base-nav.php",
    "include/base-footer.php",
    "include/sidebar-cta.php",
    "include/form-handler.php",
    "include/image-helper.php",
    "include/blog-data.php",

    # === COMPONENTS ===
    "include/components/hero-banner.php",
    "include/components/course-card.php",
    "include/components/college-card.php",
    "include/components/cta-strip.php",
    "include/components/faq-section.php",
    "include/components/trust-bar.php",
    "include/components/related-pages.php",

    # === CSS ===
    "assets/css/bootstrap5.min.css",
    "assets/css/bundle.min.css",

    # === JS ===
    "assets/js/vendor/bootstrap.bundle.min.js",
    "assets/js/app.js",

    # === HOMEPAGE (redesigned) ===
    "index.php",

    # === 12 NEW SEO PAGES ===
    "usict-admission.php",
    "usar-admission.php",
    "usls-admission.php",
    "usms-admission.php",
    "bcom-admission-ipu.php",
    "ba-english-admission-ipu.php",
    "ba-economics-admission-ipu.php",
    "college-admission-delhi.php",
    "top-btech-colleges-delhi.php",
    "ipu-colleges-list.php",
    "mait-admission.php",
    "msit-admission.php",

    # === MIGRATED EXISTING PAGES ===
    "BPIT.php",
    "BVP.php",
    "GGSIPU-counselling-for-B-Tech-admission.php",
    "IP-University-management-quota-admission-eligibility-criteria.php",
    "IPU-B-Tech-admission-2025.php",
    "IPU-B-Tech-admission-2026.php",
    "IPU-Law-Admission-2025.php",
    "b-tech-colleges-under-IP-university.php",
    "ballb-management-quota-ipu.php",
    "bba-management-quota-ipu.php",
    "best-btech-colleges-ipu.php",
    "bharati-vidyapeeth-engineering-college-delhi-admission-courses-placement.php",
    "blog.php",
    "btech-management-quota-ipu.php",
    "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
    "comprehensive-guide-to-bballb-admission-in-ip-university.php",
    "economics-admission-2025.php",
    "economics-admission-ip-university.php",
    "explore-MSIT-and-MSI-janakpuri.php",
    "exploring-MAIT-and-MAIMS.php",
    "guide-to-bjmc-colleges-under-ip-university.php",
    "ipu-admission-guide.php",
    "ipu-b-tech-pillar.php",
    "ipu-bba-admission.php",
    "ipu-btech-cutoff-analysis.php",
    "ipu-btech-via-cuet.php",
    "ipu-btech-via-jee-main.php",
    "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
    "ipu-choice-filling-strategy.php",
    "ipu-helpline-contact-number.php",
    "law-admission-ip-university.php",
    "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php",
    "mba-admission-ip-university.php",
    "mba-management-quota-ipu.php",
    "top-btech-colleges-ipu-comparison.php",
    "ultimate-guide-to-ballb-admission-in-ip-university.php",
    "vips-pitampura-courses.php",
    "sendemail.php",
    "thank-you.php",

    # === AI DISCOVERABILITY ===
    "llms.txt",
    "sitemap.xml",
    "robots.txt",
    "api/agent-data.php",
    ".well-known/ai.json",

    # === COURSE SUBDIR ===
    "course/index.php",
]

# WebP images to upload
WEBP_IMAGES = []


def ensure_remote_dir(ftp, path):
    """Ensure a remote directory exists, creating parent dirs as needed."""
    dirs = path.strip("/").split("/")
    current = ""
    for d in dirs:
        current += "/" + d
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            try:
                ftp.mkd(current)
                print(f"    Created dir: {current}")
            except ftplib.error_perm:
                pass


def upload_file(ftp, local_path, remote_path):
    """Upload a single file."""
    remote_dir = os.path.dirname(remote_path)
    if remote_dir:
        ensure_remote_dir(ftp, remote_dir)

    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_path}", f)


def main():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print(f"Connected. Remote dir: {REMOTE_BASE}\n")

    # Upload main files
    total = len(FILES_TO_UPLOAD)
    uploaded = 0
    errors = 0

    for i, relpath in enumerate(FILES_TO_UPLOAD, 1):
        local = os.path.join(LOCAL_BASE, relpath)
        remote = f"{REMOTE_BASE}/{relpath}"

        if not os.path.exists(local):
            print(f"  [{i}/{total}] SKIP (not found): {relpath}")
            continue

        try:
            upload_file(ftp, local, remote)
            uploaded += 1
            size = os.path.getsize(local)
            print(f"  [{i}/{total}] OK: {relpath} ({size:,} bytes)")
        except Exception as e:
            errors += 1
            print(f"  [{i}/{total}] ERROR: {relpath} - {e}")

    # Upload WebP images
    print(f"\nUploading WebP images...")
    webp_dir = os.path.join(LOCAL_BASE, "assets", "images")
    webp_files = [f for f in os.listdir(webp_dir) if f.endswith(".webp")]
    webp_total = len(webp_files)

    for i, filename in enumerate(sorted(webp_files), 1):
        local = os.path.join(webp_dir, filename)
        remote = f"{REMOTE_BASE}/assets/images/{filename}"
        try:
            upload_file(ftp, local, remote)
            uploaded += 1
            if i % 10 == 0 or i == webp_total:
                print(f"  [{i}/{webp_total}] WebP images uploaded...")
        except Exception as e:
            errors += 1
            print(f"  ERROR: {filename} - {e}")

    ftp.quit()

    print(f"\n{'=' * 50}")
    print(f"Upload complete!")
    print(f"  Files uploaded: {uploaded}")
    print(f"  Errors: {errors}")
    print(f"  WebP images: {webp_total}")
    print(f"{'=' * 50}")


if __name__ == "__main__":
    main()
