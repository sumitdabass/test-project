#!/usr/bin/env python3
"""
CLS Fix Deploy - 2026-05-07

Fixes:
- GSC reported CLS 0.21 (desktop) on /GGSIPU-counselling-for-B-Tech-admission.php
- Root cause: bundle.min.css is loaded with media="print" onload="this.media='all'"
  (deferred). The .banner-area.banner-three rule (min-height:300px; padding:120px 0
  50px) lives in that bundle, so the inner-page banner collapses to ~80px at first
  paint and jumps to ~470px when bundle loads — a ~390px shift on every banner-three
  page.
- Fix: inline the banner-three critical CSS in include/base-head.php so the banner
  is correctly sized before bundle arrives. One file change, applies to every page
  that uses banner-three.

Expected impact: CLS 0.21 → <0.05 on the counselling page (and same drop on every
other inner-page using banner-three).
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
    "include": [
        "base-head.php",
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
            print(f"[+] {subdir}/{fname} ({size:,} bytes)")
            ok += 1

    ftp.quit()
    print("")
    print(f"[=] {ok}/{total} files uploaded in {time.time()-started:.1f}s")
    if ok != total:
        sys.exit(1)


if __name__ == "__main__":
    main()
