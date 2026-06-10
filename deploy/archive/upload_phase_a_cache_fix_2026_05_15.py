#!/usr/bin/env python3
"""
Phase A cache-fix re-deploy — 2026-05-15 (follow-up)

.user.ini didn't activate Cache-Control after 5+ min — Hostinger likely has
a higher-level php.ini or LiteSpeed config overriding session.cache_limiter.

Fix: inline `session_cache_limiter('public'); session_cache_expire(30);` before
every session_start() call across all PHP files. PHP_INI_ALL means the runtime
call takes precedence over server config.

This re-deploys all currently-modified files (113 total).
"""
import os
import ftplib
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

with open("/tmp/phase_a_b_files.txt") as fh:
    FILES = [line.strip() for line in fh if line.strip()]


def ensure_remote_dir(ftp, path):
    parts = [p for p in path.split("/") if p]
    for p in parts:
        try:
            ftp.cwd(p)
        except ftplib.error_perm:
            ftp.mkd(p)
            ftp.cwd(p)


def upload_one(ftp, rel_path):
    local = os.path.join(LOCAL_BASE, rel_path)
    if not os.path.exists(local):
        return False, f"MISSING: {local}"
    ftp.cwd("/" + FTP_REMOTE_PATH.strip("/"))
    sub = os.path.dirname(rel_path)
    if sub:
        ensure_remote_dir(ftp, sub)
    fname = os.path.basename(rel_path)
    with open(local, "rb") as fh:
        ftp.storbinary(f"STOR {fname}", fh)
    return True, f"  OK  {rel_path}"


def main():
    print(f"Cache-fix re-deploy — {len(FILES)} files to {FTP_HOST}{FTP_REMOTE_PATH}\n")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("Connected.\n")
    except Exception as e:
        print(f"FTP connection failed: {e}", file=sys.stderr)
        sys.exit(1)

    ok, fail = 0, 0
    for rel in FILES:
        try:
            success, msg = upload_one(ftp, rel)
            print(msg)
            if success:
                ok += 1
            else:
                fail += 1
        except Exception as e:
            print(f"  FAIL  {rel}: {e}")
            fail += 1

    ftp.quit()
    print(f"\nUploaded {ok}/{len(FILES)} ({fail} failed)")
    sys.exit(0 if fail == 0 else 2)


if __name__ == "__main__":
    main()
