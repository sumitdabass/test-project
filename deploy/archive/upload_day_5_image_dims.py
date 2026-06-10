#!/usr/bin/env python3
"""
Phase B Day 5 deploy — image dimensions injected on 28 <img> tags across 5 files.

5 files (the universe of pages with prior <img> tags missing dimensions):
  - course/index.php                                       (13 tags)
  - include/home-blog.php                                  (7 tags)
  - include/our-courses.php                                (6 tags)
  - include/footer.php                                     (1 tag)
  - maharaja-agrasen-business-school-...                   (1 tag)

Pre-deploy verification done:
  - automation/image_dim_injector.py ran cleanly
  - php -l passes on all 5 files
  - localhost smoke: img tags now render with width="N" height="M"
  - 5 unresolved tags (PHP-variable srcs in template helpers) correctly skipped

Post-deploy verify:
  curl -s 'https://ipu.co.in/course/' | grep -oE '<img [^>]*width="[0-9]+"' | wc -l
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
    "course/index.php",
    "include/home-blog.php",
    "include/our-courses.php",
    "include/footer.php",
    "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php",
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
