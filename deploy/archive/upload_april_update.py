#!/usr/bin/env python3
"""
Upload April 2026 updates to ipu.co.in
- Form duplicate fix (3 files)
- SEO components (3 new + 28 updated pages)
- 25 new college pages + template
- Updated sitemap, college list, agent-data, llms.txt
"""
import ftplib
import os
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

# All files changed/created in April 2026 update
FILES_TO_UPLOAD = [
    # Phase 1: Form fix
    "include/form-handler.php",
    "sendemail.php",
    "include/sidebar-cta.php",

    # Phase 2: SEO components (new)
    "include/components/breadcrumb-schema.php",
    "include/components/college-schema.php",
    "include/components/last-updated.php",

    # Phase 2: Updated existing college pages (breadcrumb + last-updated)
    "mait-admission.php",
    "msit-admission.php",
    "BPIT.php",
    "BVP.php",
    "vips-pitampura-courses.php",
    "usict-admission.php",
    "usar-admission.php",
    "usls-admission.php",
    "usms-admission.php",
    "adgitm-admission.php",
    "gtbit-admission.php",
    "hmr-admission.php",
    "jims-admission.php",
    "mabs-admission.php",
    "maims-admission.php",
    "msi-admission.php",
    "iitm-admission.php",

    # Phase 2: Updated course pages
    "IPU-B-Tech-admission-2026.php",
    "mba-admission-ip-university.php",
    "IPU-Law-Admission.php",
    "bca-admission-ipu.php",
    "bcom-admission-ipu.php",
    "ba-english-admission-ipu.php",
    "ba-economics-admission-ipu.php",
    "barch-admission-ipu.php",
    "llm-admission-ipu.php",
    "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",

    # Phase 2: AI/SEO data files
    "api/agent-data.php",
    "llms.txt",

    # Phase 3: College page template
    "include/templates/college-page-template.php",

    # Phase 3: 25 new college pages
    "dtc-admission.php",
    "jemtec-admission.php",
    "echelon-admission.php",
    "dme-admission.php",
    "cpj-admission.php",
    "fairfield-admission.php",
    "rdias-admission.php",
    "gibs-admission.php",
    "jims-kalkaji-admission.php",
    "jims-vasant-kunj-admission.php",
    "ideal-admission.php",
    "kcc-admission.php",
    "tecnia-admission.php",
    "ndim-admission.php",
    "tips-admission.php",
    "meri-admission.php",
    "kasturi-ram-admission.php",
    "lingayas-admission.php",
    "don-bosco-admission.php",
    "gtb4cec-admission.php",
    "bcips-admission.php",
    "sgtbimit-admission.php",
    "sirifort-admission.php",
    "gnit-admission.php",
    "dias-admission.php",

    # Phase 3: Updated sitemap + college list
    "sitemap.xml",
    "ipu-colleges-list.php",
]

def ensure_remote_dir(ftp, path):
    """Create remote directory if it doesn't exist"""
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
                print(f"  Created directory: {d}")
            except ftplib.error_perm:
                pass

def upload():
    success = 0
    failed = 0
    skipped = 0

    try:
        print(f"Connecting to {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
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

            # Navigate to correct remote directory
            ftp.cwd(FTP_REMOTE_PATH)

            if "/" in file_path:
                remote_dir = "/".join(file_path.split("/")[:-1])
                ensure_remote_dir(ftp, remote_dir)
                # Go back to the right place
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
