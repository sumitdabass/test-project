#!/usr/bin/env python3
"""
CUET Admission Pages Deploy - 2026-05-06

Ships:
- 5 new evergreen CUET admission pages (no year in URL):
    cuet-admission-ipu.php       (hub)
    cuet-btech-admission-ipu.php
    cuet-bba-admission-ipu.php
    cuet-bcom-admission-ipu.php
    cuet-law-admission-ipu.php
- blog.php updated with new "CUET" category + 5 entries
- 4 existing course-hub pages cross-linked to the new CUET pages:
    IPU-B-Tech-admission-2026.php
    comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php
    bcom-admission-ipu.php
    IPU-Law-Admission.php
- sitemap.xml + llms.txt updated

Source: GGSIPU UG Admission Brochure 2026-27 (Important Instructions #21 + #37,
Chapter 2 eligibility, Chapter 12 Management Quota).
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

FILES_TO_UPLOAD = {
    "": [
        # New CUET pages
        "cuet-admission-ipu.php",
        "cuet-btech-admission-ipu.php",
        "cuet-bba-admission-ipu.php",
        "cuet-bcom-admission-ipu.php",
        "cuet-law-admission-ipu.php",

        # Modified hub pages (cross-links + blog index)
        "blog.php",
        "IPU-B-Tech-admission-2026.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "bcom-admission-ipu.php",
        "IPU-Law-Admission.php",

        # Discovery surfaces
        "sitemap.xml",
        "llms.txt",
    ],
}


def ftp_connect():
    print(f"[*] Connecting to {FTP_HOST} as {FTP_USER} ...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"[+] Connected. CWD = {ftp.pwd()}")
    return ftp


def upload_file(ftp, local_path, remote_name):
    if not os.path.exists(local_path):
        print(f"[!] MISSING local: {local_path}")
        return False
    size = os.path.getsize(local_path)
    with open(local_path, "rb") as f:
        ftp.storbinary(f"STOR {remote_name}", f)
    print(f"[+] {remote_name} ({size:,} bytes)")
    return True


def main():
    started = time.time()
    ftp = ftp_connect()

    total = 0
    ok = 0
    for subdir, files in FILES_TO_UPLOAD.items():
        if subdir:
            try:
                ftp.cwd(FTP_REMOTE_PATH + "/" + subdir)
            except ftplib.error_perm:
                print(f"[*] Creating remote dir: {subdir}")
                ftp.mkd(subdir)
                ftp.cwd(FTP_REMOTE_PATH + "/" + subdir)
        else:
            ftp.cwd(FTP_REMOTE_PATH)

        for fname in files:
            total += 1
            local = os.path.join(LOCAL_BASE, subdir, fname) if subdir else os.path.join(LOCAL_BASE, fname)
            if upload_file(ftp, local, fname):
                ok += 1

    ftp.quit()
    dur = time.time() - started
    print("")
    print(f"[=] {ok}/{total} files uploaded in {dur:.1f}s")
    if ok != total:
        sys.exit(1)


if __name__ == "__main__":
    main()
