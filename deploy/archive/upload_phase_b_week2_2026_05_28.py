#!/usr/bin/env python3
"""
Phase B Week 2 — Additive SEO sprint deploy — 2026-05-28

Surgical FTP push of 17 PHP files implementing additive content + schema +
internal-link sweep. NO title/meta/H1/canonical/URL changes (SEO-safety per
feedback_seo_safety_ipu.md). Observation-mode safe.

Course-correction 2026-05-28: CET admit-card focus dropped; counselling sweep
expanded to cover Sumit's target courses (B.Tech, MBA, Law, BBA, BCom, BA Eng,
BA Eco). Display/Remarketing data excluded from any deploy decisioning per
feedback_no_display_remarketing_decisions.md.

Changes:
  Task 2  ipu-colleges-list.php                                ItemList schema + 3 FAQs + counselling link
  Task 3  comprehensive-guide-to-bba-...-top-10-institutions.php   BBA Fees section + 2 FAQs
  Task 4  13 target-course pages                                One varied anchor -> counselling workhorse
  Task 4b 2 B.Tech cluster pages                                One contextual anchor -> ipu-btech-via-jee-main.php

Pre-deploy validation already done (Task 5 cross-link walk):
  - php -l: 0 errors across all 17 .php files
  - php -S smoke: HTTP 200 + 0 warnings on every changed page
  - JSON-LD parse: Article + ItemList(25) + BreadcrumbList + FAQPage all validate
  - Curl-verify: each anchor present in rendered HTML

Post-deploy verify (Task 8):
  curl -s https://ipu.co.in/ipu-colleges-list.php | grep -c '"@type": "ItemList"'              # expect: 1
  curl -s https://ipu.co.in/comprehensive-guide-to-bba-...-top-10-institutions.php | grep -c 'bba-fees-ipu-2026'  # expect: 1
  for f in <13 target-course pages>; do
    echo "$f: $(curl -s https://ipu.co.in/$f | grep -c GGSIPU-counselling-for-B-Tech-admission)"
  done
"""
import os
import ftplib
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = os.environ.get("IPU_FTP_PASS") or "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES = [
    # Task 2
    "ipu-colleges-list.php",
    # Task 3
    "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
    # Task 4 — 13 counselling-link sweeps (target-course aligned, 2026-05-28 course correction)
    "top-law-colleges-ipu.php",
    "bca-admission-ipu.php",
    "top-bca-colleges-ipu.php",
    "bcom-admission-ipu.php",
    "top-bcom-colleges-ipu.php",
    "cuet-law-admission-ipu.php",
    "ultimate-guide-to-ballb-admission-in-ip-university.php",
    "mca-admission-ipu.php",
    "top-mca-colleges-ipu.php",
    "ba-economics-admission-ipu.php",
    "top-mba-colleges-ipu.php",
    "top-bba-colleges-ipu.php",
    "ba-english-admission-ipu.php",
    # Task 4b — JEE Main pathway anchor enrichment
    "IPU-B-Tech-admission-2026.php",
    "ipu-btech-cutoff-2025.php",
]


def main():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"Connected. Pushing {len(FILES)} files.\n")

    pushed = 0
    skipped = 0
    for rel in FILES:
        local_path = os.path.join(LOCAL_BASE, rel)
        if not os.path.exists(local_path):
            print(f"  MISSING: {rel} — skipping")
            skipped += 1
            continue
        size = os.path.getsize(local_path)
        with open(local_path, "rb") as fh:
            ftp.storbinary(f"STOR {rel}", fh)
        print(f"  OK   {rel}  ({size:,} bytes)")
        pushed += 1

    ftp.quit()
    print(f"\nDone. Pushed {pushed} / Skipped {skipped} / Total {len(FILES)}.")


if __name__ == "__main__":
    main()
