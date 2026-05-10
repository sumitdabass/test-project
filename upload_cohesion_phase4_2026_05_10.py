#!/usr/bin/env python3
"""
Cohesion Phase 4 Deploy — 2026-05-10

Ships banner-three → page-hero migration on 31 pages (course hubs, CUET,
mgmt-quota, college listicles, counselling/process). Each page now has
$hero_show_form=false; the in-body enquiry sidebar continues to carry
conversion (one-form-per-page rule).

No component changes — page-hero.php already on prod from Phase 3.

Plan: docs/superpowers/plans/2026-05-09-ipu-site-cohesion.md (Tasks 13–15)
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
        # College pages with banner
        "BPIT.php",
        "BVP.php",
        "explore-MSIT-and-MSI-janakpuri.php",
        "exploring-MAIT-and-MAIMS.php",
        "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php",

        # Course hubs
        "IPU-Law-Admission.php",
        "law-3-year-admission-ipu.php",
        "mba-admission-ip-university.php",
        "economics-admission-ip-university.php",
        "ipu-b-tech-pillar.php",
        "ipu-admission-guide.php",

        # College listicles + comparisons
        "best-btech-colleges-ipu.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        "guide-to-bjmc-colleges-under-ip-university.php",
        "top-btech-colleges-ipu-comparison.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",

        # CUET pages
        "cuet-admission-ipu.php",
        "cuet-bba-admission-ipu.php",
        "cuet-bcom-admission-ipu.php",
        "cuet-btech-admission-ipu.php",
        "cuet-law-admission-ipu.php",

        # Management quota pages
        "IP-University-management-quota-admission-eligibility-criteria.php",
        "ballb-management-quota-ipu.php",
        "bba-management-quota-ipu.php",
        "btech-management-quota-ipu.php",
        "mba-management-quota-ipu.php",

        # Counselling / process / utility
        "GGSIPU-counselling-for-B-Tech-admission.php",
        "ipu-btech-cutoff-analysis.php",
        "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
        "ipu-choice-filling-strategy.php",
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
    print(f"\n🚀 Cohesion Phase 4 Deploy — {total} files\n")
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
