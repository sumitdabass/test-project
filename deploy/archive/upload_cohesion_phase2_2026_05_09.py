#!/usr/bin/env python3
"""
Cohesion Phase 2 Deploy — 2026-05-09

Ships:
- Foundations (dormant on prod until later phases adopt):
    - PHP 8.5 deprecation guard at top of index.php
    - Design tokens + sidebar-enquiry/page-hero CSS in base-head.php critical CSS
    - $skip_legacy_css opt-out wrapper around bundle.min.css
    - New shared component: include/components/sidebar-enquiry.php

- Pilot adoption (visible on prod):
    - BPIT.php, vips-admission.php, mait-admission.php — sidebar swapped from
      sidebar-cta.php (or hand-rolled) to sidebar-enquiry component.

Body content of the 3 pilot pages is byte-identical to before. Form contract
preserved (POST /sendemail.php, fields name/phone/email/course/page_url/website).

Plan: docs/superpowers/plans/2026-05-09-ipu-site-cohesion.md (Tasks 1–8)
Spec: docs/superpowers/specs/2026-05-09-ipu-site-cohesion-design.md
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
        # === Foundations ===
        "index.php",                    # PHP 8.5 deprecation guard

        # === Pilot pages (sidebar swapped to sidebar-enquiry) ===
        "BPIT.php",
        "vips-admission.php",
        "mait-admission.php",
    ],
    "include": [
        # === Tokens + dormant component CSS + $skip_legacy_css switch ===
        "base-head.php",
    ],
    "include/components": [
        # === New shared component ===
        "sidebar-enquiry.php",
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
    print(f"\n🚀 Cohesion Phase 2 Deploy — {total} files\n")
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
