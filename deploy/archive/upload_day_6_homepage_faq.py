#!/usr/bin/env python3
"""
Phase B Day 6 deploy — homepage FAQ enrichment + schema cleanup.

1 file:
  - index.php
    * Removed pre-existing schema-only FAQPage in <head> (8 Q/As, no
      visible match — Day 2 + Day 4 pattern)
    * Existing $faqs array extended from 6 → 12 Q/As (6 new on brand
      cluster: definition, location, college count, admission process,
      helpline, year founded). FAQ accordion at line ~420 now renders
      all 12. faq-section.php emits compliant FAQPage JSON-LD.
    * NO 'About IPU' 300-word content block added (deferred to Phase C
      per Sumit's 'keep basic, don't hamper' directive)
    * NO title/H1/meta/canonical/hero/trust-bar changes

Pre-deploy verification done:
  - php -l clean
  - localhost: 1 FAQPage (12 Q/As, compliant), 1 accordion, hero NAAC
    unchanged, trust-bar still renders, no duplicate FAQ headers

Post-deploy verify:
  URL='https://ipu.co.in/?cb=$(date +%s)'
  curl -s "$URL" | grep -c '"@type": "FAQPage"'                      # → 1
  curl -s "$URL" | grep -c 'What is GGSIPU / IP University'          # → 2 (accordion + JSON-LD)
  curl -s "$URL" | grep -c 'What is the IPU helpline number'         # → 2
  curl -s "$URL" | grep -c '100,000+'                                # → 1 (trust-bar)
  curl -s "$URL" | grep -c 'NAAC A++ Accredited'                     # → ≥1 (hero + body)
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
    "index.php",
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
