#!/usr/bin/env python3
"""
Phase B Day 1 deploy — trust-bar site-wide + leads.log instrumentation.

4 files:
  - include/components/page-hero.php  — Day 1 trust-bar wiring
  - include/helpers/phone-dedup.php   — Day 0 lead_record() helper
  - include/form-handler.php          — Day 0 leads.log call (1 line)
  - sendemail.php                     — Day 0 leads.log call (1 line)

Pre-deploy verification done:
  - php -l: 0 errors on all 4 files
  - localhost smoke test: trust-bar renders on 4 cohesion archetypes
    (homepage 1x, counselling 1x, B.Tech-admission 1x, Law-admission 1x;
     usict-admission 0x as expected — legacy hero-banner page)
  - lead_record() smoke test: localhost POST to sendemail.php → leads.log
    appended with 1 line (ISO timestamp + SHA-256(phone) + 'sendemail')

Post-deploy verify (after Sumit FPM toggle):
  curl -s 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)' | grep -c '100,000+'
  → expect 1

Sumit action required mid-deploy:
  cPanel → MultiPHP Manager → toggle PHP-FPM off then on
  (per [[reference_hostinger_fpm_opcache]]).
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
    "include/components/page-hero.php",
    "include/helpers/phone-dedup.php",
    "include/form-handler.php",
    "sendemail.php",
]


def upload(ftp, local, remote):
    print(f"  → {remote}")
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)


def main():
    # Pre-flight: verify all files exist locally
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
    print("Done.\n")
    print("NEXT: ask Sumit to toggle PHP-FPM in cPanel → MultiPHP Manager.")
    print("Then run: curl -s 'https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)' | grep -c '100,000+'  → expect 1")


if __name__ == "__main__":
    main()
