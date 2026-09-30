#!/usr/bin/env python3
"""
Phase B Day 2 deploy — FAQ + HowTo schema on counselling page.

1 file:
  - GGSIPU-counselling-for-B-Tech-admission.php
    * Removed pre-existing schema-only FAQPage (10 Q's, no visible match)
    * Added new 8-Q FAQPage + HowTo + visible <details> section
    * Page now has exactly 1 FAQPage + 1 HowTo, both schema-matches-visible

Pre-deploy verification done:
  - php -l: 0 errors
  - localhost: 1 FAQPage, 1 HowTo, 1 visible FAQ header, trust-bar still
    renders, hero H1 "IPU Counselling 2026" unchanged

Post-deploy verify:
  curl -s 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)' | grep -c '"@type": "FAQPage"'  → 1
  curl -s 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)' | grep -c '"@type": "HowTo"'   → 1
  curl -s 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)' | grep -c 'When does GGSIPU counselling start'  → 2 (1 in JSON-LD + 1 in <summary>)

Google Rich Results Test:
  https://search.google.com/test/rich-results?url=https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php
  Expect: 8 FAQ items + 1 HowTo with 8 steps, zero errors.
"""
import os
import ftplib
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES = [
    "GGSIPU-counselling-for-B-Tech-admission.php",
]


def upload(ftp, local, remote):
    print(f"  → {remote}")
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)


def main():
    for rel in FILES:
        local = os.path.join(LOCAL_BASE, rel)
        if not os.path.exists(local):
            print(f"  MISSING: {local}", file=sys.stderr)
            sys.exit(1)

    print(f"Connecting to {FTP_HOST} ...")
    with ftplib.FTP(FTP_HOST) as ftp:
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(FTP_REMOTE_PATH)
        for rel in FILES:
            local = os.path.join(LOCAL_BASE, rel)
            upload(ftp, local, rel)
    print("Done.")


if __name__ == "__main__":
    main()
