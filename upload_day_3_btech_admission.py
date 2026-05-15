#!/usr/bin/env python3
"""
Phase B Day 3 deploy — B.Tech admission FAQ enrichment + 301 hardening.

2 files:
  - IPU-B-Tech-admission-2026.php
    * Existing brochure-sourced $faqs array extended with 2 new Q/As:
      "How do I apply" + "Is there a management quota"
    * Added Day 3 HowTo JSON-LD (6 steps) before </body>
    * No title/H1/meta/canonical/copy changes
  - .htaccess
    * Existing 2025 → 2026 301 rule given [NC] flag (case-insensitive)
    * Catches the mixed-case orphan /IPU-B-Tech-admission-2025.php
      (80 impressions at pos 31.65 — equity leak fix)

Pre-deploy verification done:
  - php -l clean
  - localhost: 1 FAQPage (8 Q/As, compliant), 1 HowTo, 2 new Q/As visible,
    trust-bar still renders, hero H1 unchanged

Post-deploy verify:
  URL='https://ipu.co.in/IPU-B-Tech-admission-2026.php?cb=$(date +%s)'
  curl -s "$URL" | grep -c '"@type": "HowTo"'                 # → 1
  curl -s "$URL" | grep -c 'How do I apply for IPU B.Tech'    # → 2 (visible + JSON-LD)
  curl -s "$URL" | grep -c 'Is there a management quota'      # → 2
  curl -sI 'https://ipu.co.in/IPU-B-Tech-admission-2025.php' | grep -i Location  # → 301 to 2026
  curl -sI 'https://ipu.co.in/ipu-b-tech-admission-2025.php' | grep -i Location  # → 301 to 2026
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
    "IPU-B-Tech-admission-2026.php",
    ".htaccess",
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
