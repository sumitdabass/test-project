#!/usr/bin/env python3
"""
PG Pages Deploy - 2026-05-06

Source spec: docs/superpowers/specs/2026-05-06-ipu-pg-pages-design.md
Source plan: docs/superpowers/plans/2026-05-06-ipu-pg-pages.md

12 files:
- 1 NEW: law-3-year-admission-ipu.php (Programme Code 238)
- 3 REFRESHED: mba-admission-ip-university.php (Code 101 fixes),
  mca-admission-ipu.php (Code 105 fixes),
  llm-admission-ipu.php (Code 112 — eligibility 50% → 55% critical fix)
- 5 CROSS-LINK: IPU-Law-Admission, cuet-law-admission-ipu,
  comprehensive-guide-to-bballb, ultimate-guide-to-ballb,
  top-law-colleges-ipu (single-sentence pointers)
- 3 SITE-WIRING: sitemap.xml, llms.txt, blog.php

Aborts on any STOR failure. Run from /Users/Sumit/test-project/.
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
        # NEW
        "law-3-year-admission-ipu.php",
        # REFRESHED
        "mba-admission-ip-university.php",
        "mca-admission-ipu.php",
        "llm-admission-ipu.php",
        # CROSS-LINK
        "IPU-Law-Admission.php",
        "cuet-law-admission-ipu.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
        "top-law-colleges-ipu.php",
        # SITE-WIRING
        "sitemap.xml",
        "llms.txt",
        "blog.php",
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
        target = FTP_REMOTE_PATH + ("/" + subdir if subdir else "")
        ftp.cwd(target)
        for fname in files:
            total += 1
            local = (
                os.path.join(LOCAL_BASE, subdir, fname)
                if subdir
                else os.path.join(LOCAL_BASE, fname)
            )
            if not os.path.exists(local):
                print(f"[!] MISSING: {local}")
                continue
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
