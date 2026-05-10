#!/usr/bin/env python3
"""
Cohesion Phase 5 Deploy — 2026-05-10

Ships the in-body sidebar swap on 82 pages: legacy include/sidebar-cta.php
replaced with the unified include/components/sidebar-enquiry.php component.

Form contract preserved (POST /sendemail.php, 6 fields). Each page now
renders the same conversion sidebar as the 3 Phase 2 pilots.

The 32 banner-three pages from Phase 4 are also re-uploaded here because
their in-body sidebar changed too. Net: every conversion-relevant page
(Phase 2 + Phase 4 + Phase 5) now serves identical sidebar chrome.

Plan: docs/superpowers/plans/2026-05-09-ipu-site-cohesion.md (Tasks 16–17)
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

# 82 files from /tmp/sidebar-candidates.txt — every page that used to include sidebar-cta.php
FILES_TO_UPLOAD = {
    "": [
        "BVP.php",
        "GGSIPU-counselling-for-B-Tech-admission.php",
        "IP-University-management-quota-admission-eligibility-criteria.php",
        "IPU-B-Tech-admission-2026.php",
        "IPU-Law-Admission.php",
        "adgitm-admission.php",
        "ba-economics-admission-ipu.php",
        "ba-english-admission-ipu.php",
        "ballb-management-quota-ipu.php",
        "barch-admission-ipu.php",
        "bba-management-quota-ipu.php",
        "bca-admission-ipu.php",
        "bcom-admission-ipu.php",
        "best-btech-colleges-ipu.php",
        "blog.php",
        "btech-management-quota-ipu.php",
        "bvicam-admission.php",
        "college-admission-delhi.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        "cuet-admission-ipu.php",
        "cuet-bba-admission-ipu.php",
        "cuet-bcom-admission-ipu.php",
        "cuet-btech-admission-ipu.php",
        "cuet-law-admission-ipu.php",
        "dspsr-admission.php",
        "economics-admission-ip-university.php",
        "explore-MSIT-and-MSI-janakpuri.php",
        "exploring-MAIT-and-MAIMS.php",
        "gtbit-admission.php",
        "guide-to-bjmc-colleges-under-ip-university.php",
        "hmr-admission.php",
        "iitm-admission.php",
        "ipu-admission-guide.php",
        "ipu-ba-economics-cutoff-2025.php",
        "ipu-bba-cutoff-2025.php",
        "ipu-bcom-cutoff-2025.php",
        "ipu-bjmc-cutoff-2025.php",
        "ipu-btech-cutoff-2025.php",
        "ipu-btech-cutoff-analysis.php",
        "ipu-btech-via-cuet.php",
        "ipu-btech-via-jee-main.php",
        "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
        "ipu-cet-cutoff-2025.php",
        "ipu-choice-filling-strategy.php",
        "ipu-colleges-list.php",
        "ipu-counselling.php",
        "ipu-cutoff-analysis.php",
        "ipu-fees-structure.php",
        "ipu-helpline-contact-number.php",
        "ipu-law-cutoff-2025.php",
        "ipu-mba-cutoff-2025.php",
        "jims-admission.php",
        "law-3-year-admission-ipu.php",
        "llm-admission-ipu.php",
        "mabs-admission.php",
        "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php",
        "maims-admission.php",
        "maims-delhi-fees-courses.php",
        "mait-delhi-fees-courses-placements.php",
        "mba-admission-ip-university.php",
        "mba-management-quota-ipu.php",
        "mca-admission-ipu.php",
        "med-admission-ipu.php",
        "msi-admission.php",
        "msit-admission.php",
        "mtech-admission-ipu.php",
        "top-bba-colleges-ipu.php",
        "top-bca-colleges-ipu.php",
        "top-bcom-colleges-ipu.php",
        "top-btech-colleges-delhi.php",
        "top-btech-colleges-ipu-comparison.php",
        "top-ipu-colleges.php",
        "top-law-colleges-ipu.php",
        "top-mba-colleges-ipu.php",
        "top-mca-colleges-ipu.php",
        "trinity-law-admission.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
        "usar-admission.php",
        "usict-admission.php",
        "usls-admission.php",
        "usms-admission.php",
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
    print(f"\n🚀 Cohesion Phase 5 Deploy — {total} files\n")
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
