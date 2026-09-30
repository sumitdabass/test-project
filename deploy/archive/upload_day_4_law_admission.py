#!/usr/bin/env python3
"""
Phase B Day 4 deploy — Law-Admission FAQ enrichment + HowTo + schema cleanup.

1 file:
  - IPU-Law-Admission.php
    * Removed pre-existing schema-only FAQPage in <head> (8 Q/As, no
      visible match — Google policy violation, same as Day 2 counselling)
    * Existing brochure-sourced $faqs array extended with 3 unique
      new Q/As (admission process, management quota, best law college)
    * Added Day 4 HowTo JSON-LD (6 steps for law admission via CLAT)
    * No title/H1/meta/canonical/copy changes

Pre-deploy verification done:
  - php -l clean
  - localhost: 1 FAQPage (compliant component) + 1 HowTo, trust-bar
    still renders, hero H1 unchanged, 3 new Q/As visible + in JSON-LD

Post-deploy verify:
  URL='https://ipu.co.in/IPU-Law-Admission.php?cb=$(date +%s)'
  curl -s "$URL" | grep -c '"@type": "FAQPage"'           # → 1
  curl -s "$URL" | grep -c '"@type": "HowTo"'             # → 1
  curl -s "$URL" | grep -c 'What is the IPU law admission process for 2026'  # → 2
  curl -s "$URL" | grep -c 'Is there management quota for IPU law'           # → 2
  curl -s "$URL" | grep -c '100,000+'                     # → 1
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
    "IPU-Law-Admission.php",
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
