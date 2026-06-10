#!/usr/bin/env python3
"""
CWV LCP Fix Deploy - 2026-05-06

Fixes:
- Removes loading="lazy" from <img class="main-img"> on 20 hub pages
- Replaces with fetchpriority="high" decoding="async"

Reason: main-img is the LCP element on each hub page. Lazy-loading the LCP
defers its load until layout is computed, hurting LCP score. The high-priority
hint signals the browser to fetch this image with the highest priority,
parallel to HTML parsing.

Expected impact: 200-600ms LCP improvement on mobile per page (varies with
network), based on web.dev fetchpriority docs.
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
        "BVP.php",
        "BPIT.php",
        "IP-University-management-quota-admission-eligibility-criteria.php",
        "IPU-B-Tech-admission-2026.php",
        "IPU-Law-Admission.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "bharati-vidyapeeth-engineering-college-delhi-admission-courses-placement.php",
        "cuet-bba-admission-ipu.php",
        "cuet-btech-admission-ipu.php",
        "cuet-admission-ipu.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        "cuet-law-admission-ipu.php",
        "economics-admission-ip-university.php",
        "exploring-MAIT-and-MAIMS.php",
        "cuet-bcom-admission-ipu.php",
        "guide-to-bjmc-colleges-under-ip-university.php",
        "explore-MSIT-and-MSI-janakpuri.php",
        "ipu-btech-cutoff-analysis.php",
        "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
    ],
}


def main():
    started = time.time()
    print(f"[*] Connecting to {FTP_HOST} as {FTP_USER} ...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"[+] Connected. CWD = {ftp.pwd()}")

    total = ok = 0
    for subdir, files in FILES_TO_UPLOAD.items():
        if subdir:
            ftp.cwd(FTP_REMOTE_PATH + "/" + subdir)
        else:
            ftp.cwd(FTP_REMOTE_PATH)
        for fname in files:
            total += 1
            local = os.path.join(LOCAL_BASE, subdir, fname) if subdir else os.path.join(LOCAL_BASE, fname)
            if not os.path.exists(local):
                print(f"[!] MISSING: {local}"); continue
            size = os.path.getsize(local)
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR {fname}", f)
            print(f"[+] {fname} ({size:,} bytes)")
            ok += 1

    ftp.quit()
    print("")
    print(f"[=] {ok}/{total} files uploaded in {time.time()-started:.1f}s")
    if ok != total:
        sys.exit(1)


if __name__ == "__main__":
    main()
