#!/usr/bin/env python3
"""
Upload SEO improvements based on 2026-04-24 GSC report analysis:
- Homepage: title/meta rewrite + new Management Seat/Quota section with internal links
- Management quota hub: target "management seat" keyword alongside "management quota"
- Counselling page: title/meta rewrite (leads with "IPU Counselling 2026")
- Helpline page: title now leads with the phone number
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
    "index.php",
    "IP-University-management-quota-admission-eligibility-criteria.php",
    "GGSIPU-counselling-for-B-Tech-admission.php",
    "ipu-helpline-contact-number.php",
]


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

            try:
                size = os.path.getsize(local_file)
                with open(local_file, "rb") as f:
                    ftp.storbinary(f"STOR {file_path}", f)
                print(f"  OK:   {file_path} ({size:,} bytes)")
                success += 1
            except Exception as e:
                print(f"  FAIL: {file_path} - {e}")
                failed += 1

        ftp.quit()

    except Exception as e:
        print(f"\nConnection error: {e}")
        sys.exit(1)

    print(f"\n{'='*60}")
    print(f"Upload complete!")
    print(f"  Uploaded: {success}")
    print(f"  Failed:   {failed}")
    print(f"  Skipped:  {skipped}")
    print(f"  Total:    {len(FILES_TO_UPLOAD)}")
    print(f"{'='*60}")
    print("\nNext steps:")
    print("  1. Visit https://ipu.co.in/ to confirm homepage changes look right")
    print("  2. Visit https://ipu.co.in/IP-University-management-quota-admission-eligibility-criteria.php")
    print("  3. Submit these URLs to Google Search Console for re-indexing:")
    for f in FILES_TO_UPLOAD:
        url = "https://ipu.co.in/" + ("" if f == "index.php" else f)
        print(f"     - {url}")
    print("  4. Monitor GSC daily for the next 2 weeks for rank volatility")


if __name__ == "__main__":
    upload()
