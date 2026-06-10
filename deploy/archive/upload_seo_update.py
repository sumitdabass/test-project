#!/usr/bin/env python3
"""
Upload April 7, 2026 SEO updates to ipu.co.in
- PG brochure data updates (eligibility, fees, entrance)
- New PG course pages (MCA, M.Tech, M.Ed)
- Title/meta rewrites for CTR boost
- 4 new SEO content pages
- Expanded FAQ schema
- Privacy policy page + footer update
- College data corrections
"""
import ftplib
import os
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = [
    # Brochure-updated course pages
    "mba-admission-ip-university.php",
    "IPU-B-Tech-admission-2026.php",
    "IPU-Law-Admission.php",
    "bca-admission-ipu.php",
    "bcom-admission-ipu.php",
    "ba-economics-admission-ipu.php",
    "ba-english-admission-ipu.php",
    "barch-admission-ipu.php",
    "llm-admission-ipu.php",
    "guide-to-bjmc-colleges-under-ip-university.php",
    "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
    "ipu-admission-guide.php",
    "ultimate-guide-to-ballb-admission-in-ip-university.php",
    "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",

    # New PG course pages
    "mca-admission-ipu.php",
    "mtech-admission-ipu.php",
    "med-admission-ipu.php",

    # College pages updated with PG programmes
    "mait-admission.php",
    "vips-pitampura-courses.php",
    "usict-admission.php",
    "usar-admission.php",
    "usms-admission.php",
    "usls-admission.php",
    "hmr-admission.php",
    "jims-admission.php",
    "maims-admission.php",
    "iitm-admission.php",
    "dias-admission.php",
    "dtc-admission.php",
    "echelon-admission.php",
    "msit-admission.php",
    "msi-admission.php",

    # Title/meta rewrites
    "index.php",
    "GGSIPU-counselling-for-B-Tech-admission.php",
    "ipu-helpline-contact-number.php",

    # 4 new SEO content pages
    "ipu-counselling-2026.php",
    "mait-delhi-fees-courses-placements.php",
    "maims-delhi-fees-courses.php",
    "ipu-fees-structure-2026.php",

    # Privacy policy + footer
    "privacy-policy.php",
    "include/base-footer.php",
]

def ensure_remote_dir(ftp, path):
    dirs = path.split("/")
    for d in dirs:
        if not d:
            continue
        try:
            ftp.cwd(d)
        except ftplib.error_perm:
            try:
                ftp.mkd(d)
                ftp.cwd(d)
            except ftplib.error_perm:
                pass

def upload():
    success = 0
    failed = 0
    skipped = 0

    try:
        print(f"Connecting to {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=60)
        ftp.login(FTP_USER, FTP_PASS)
        print("Connected!\n")
        ftp.cwd(FTP_REMOTE_PATH)

        print(f"Uploading {len(FILES_TO_UPLOAD)} files...\n")

        for file_path in FILES_TO_UPLOAD:
            local_file = os.path.join(LOCAL_BASE, file_path)

            if not os.path.exists(local_file):
                print(f"  SKIP: {file_path} (not found)")
                skipped += 1
                continue

            ftp.cwd(FTP_REMOTE_PATH)
            if "/" in file_path:
                remote_dir = "/".join(file_path.split("/")[:-1])
                ensure_remote_dir(ftp, remote_dir)
                ftp.cwd(FTP_REMOTE_PATH)
                ftp.cwd(remote_dir)
                remote_filename = file_path.split("/")[-1]
            else:
                remote_filename = file_path

            try:
                with open(local_file, "rb") as f:
                    ftp.storbinary(f"STOR {remote_filename}", f)
                print(f"  OK: {file_path}")
                success += 1
            except Exception as e:
                print(f"  FAIL: {file_path} - {e}")
                failed += 1

        ftp.quit()

    except Exception as e:
        print(f"\nConnection error: {e}")
        sys.exit(1)

    print(f"\n{'='*50}")
    print(f"Upload complete!")
    print(f"  Uploaded: {success}")
    print(f"  Failed:   {failed}")
    print(f"  Skipped:  {skipped}")
    print(f"  Total:    {len(FILES_TO_UPLOAD)}")
    print(f"{'='*50}")

if __name__ == "__main__":
    upload()
