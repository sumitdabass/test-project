#!/usr/bin/env python3
"""
SEO Overhaul Deploy — 2026-05-05

Ships:
- 3 new college pages (VIPS-TC, DSPSR, BVICAM) sourced from GGSIPU UG Brochure 2026-27
- FAQ + AI-summary + Course schema on 6 course hub pages (BBA/BCom/BCA/BJMC/BTech/Law)
- VIPS legacy URL (vips-pitampura-courses.php) consolidated into 301 → vips-admission.php
- 28 internal links refactored to new VIPS URL
- 2 stale 2025 pages converted to 301 redirects (BTech-2025, economics-2025)
- 4 top-N college pages: honourable mentions for new colleges + table-row anchor links
- News section: enhanced NewsArticle schema (+ author/keywords/lang),
  hidden AI summary, related-news + course-hub interlink widget,
  CollectionPage schema on /news/ index
- Updated sitemap.xml + llms.txt with all new + modified URLs
"""
import ftplib
import os
import sys
import time

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

# Files keyed by remote subdirectory ("" = /public_html root)
FILES_TO_UPLOAD = {
    "": [
        # === New college pages ===
        "vips-admission.php",
        "dspsr-admission.php",
        "bvicam-admission.php",

        # === Course hub pages (FAQ + AI summary + Course schema) ===
        "IPU-B-Tech-admission-2026.php",
        "IPU-Law-Admission.php",
        "bca-admission-ipu.php",
        "bcom-admission-ipu.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "guide-to-bjmc-colleges-under-ip-university.php",

        # === Discovery surfaces ===
        "ipu-colleges-list.php",
        "sitemap.xml",
        "llms.txt",

        # === 301 redirects (legacy URLs) ===
        "vips-pitampura-courses.php",
        "IPU-B-Tech-admission-2025.php",
        "economics-admission-2025.php",

        # === Top-N college pages with honourable mentions + anchored table links ===
        "top-bba-colleges-ipu.php",
        "top-bca-colleges-ipu.php",
        "top-bcom-colleges-ipu.php",
        "top-law-colleges-ipu.php",

        # === Files with VIPS legacy URL refactored to vips-admission.php ===
        "BVP.php",
        "BPIT.php",
        "IP-University-management-quota-admission-eligibility-criteria.php",
        "bba-management-quota-ipu.php",
        "best-btech-colleges-ipu.php",
        "blog.php",
        "economics-admission-ip-university.php",
        "explore-MSIT-and-MSI-janakpuri.php",
        "exploring-MAIT-and-MAIMS.php",
        "index.php",
        "index-new.php",
        "index-old.php",
        "ipu-admission-guide.php",
        "ipu-ba-economics-cutoff-2025.php",
        "ipu-bba-cutoff-2025.php",
        "ipu-bcom-cutoff-2025.php",
        "ipu-bjmc-cutoff-2025.php",
        "ipu-btech-cutoff-2025.php",
        "ipu-btech-via-jee-main.php",
        "ipu-law-cutoff-2025.php",
        "thank-you.php",
        "top-btech-colleges-ipu-comparison.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
    ],
    "include": [
        # News template enhancements + new related-content widget
        "news-template.php",
        "news-jsonld.php",
        "news-popular-blogs.php",
        "news-related-content.php",  # NEW
    ],
    "news": [
        "index.php",  # CollectionPage schema + AI summary
    ],
}


def upload_file(ftp, local_path, remote_dir, remote_name):
    if not os.path.exists(local_path):
        print(f"  ❌ MISSING LOCAL: {local_path}")
        return False
    target = f"{FTP_REMOTE_PATH}/{remote_dir}".rstrip("/") + f"/{remote_name}"
    try:
        with open(local_path, "rb") as f:
            ftp.storbinary(f"STOR {target}", f)
        size = os.path.getsize(local_path)
        print(f"  ✅ {target}  ({size:,} bytes)")
        return True
    except Exception as e:
        print(f"  ❌ FAIL {target}: {e}")
        return False


def main():
    total = sum(len(v) for v in FILES_TO_UPLOAD.values())
    print(f"\n🚀 SEO Overhaul Deploy — {total} files\n")
    print(f"   Host:   {FTP_HOST}")
    print(f"   Remote: {FTP_REMOTE_PATH}")
    print(f"   Local:  {LOCAL_BASE}\n")

    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        ftp.set_pasv(True)
        print(f"   ✅ Connected to {FTP_HOST} as {FTP_USER}\n")
    except Exception as e:
        print(f"   ❌ Connection failed: {e}")
        sys.exit(1)

    ok = fail = 0
    for subdir, files in FILES_TO_UPLOAD.items():
        label = f"/{subdir}" if subdir else "/"
        print(f"\n📂 Uploading to {FTP_REMOTE_PATH}{label}")
        for fname in files:
            local_path = os.path.join(LOCAL_BASE, subdir, fname) if subdir else os.path.join(LOCAL_BASE, fname)
            if upload_file(ftp, local_path, subdir, fname):
                ok += 1
            else:
                fail += 1
            time.sleep(0.05)

    try:
        ftp.quit()
    except Exception:
        pass

    print(f"\n{'='*60}")
    print(f"   ✅ Uploaded:  {ok}/{total}")
    if fail:
        print(f"   ❌ Failed:    {fail}/{total}")
    print(f"{'='*60}\n")
    sys.exit(0 if fail == 0 else 2)


if __name__ == "__main__":
    main()
