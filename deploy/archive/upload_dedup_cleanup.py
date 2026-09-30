#!/usr/bin/env python3
"""
Deploy duplicate-conversion cleanup:
  - Upload 5 files with deduplicated tracking
  - Delete 3 dead include files from server
  - Remove 3 dead include files locally
"""
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

UPLOADS = [
    "include/phone.php",
    "include/blog-sidebar.php",
    "BVP.php",
    "vips-pitampura-courses.php",
    "explore-MSIT-and-MSI-janakpuri.php",
]

DELETES = [
    "include/head.php",
    "include/announcement.php",
    "include/floating-call-button.php",
]

def main():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print("Connected.\n")

    # Uploads
    print("=== UPLOADS ===")
    for rel in UPLOADS:
        local = os.path.join(LOCAL_BASE, rel)
        remote_dir = FTP_REMOTE_PATH + "/" + os.path.dirname(rel) if "/" in rel else FTP_REMOTE_PATH
        ftp.cwd(remote_dir)
        with open(local, "rb") as fp:
            ftp.storbinary(f"STOR {os.path.basename(rel)}", fp)
        print(f"  Uploaded: {rel} ({os.path.getsize(local)} bytes)")

    # Server-side deletes
    print("\n=== SERVER DELETES ===")
    ftp.cwd(FTP_REMOTE_PATH + "/include")
    for rel in DELETES:
        name = os.path.basename(rel)
        try:
            ftp.delete(name)
            print(f"  Deleted: {rel}")
        except ftplib.error_perm as e:
            print(f"  Skip:    {rel} ({e})")

    ftp.quit()

    # Local deletes
    print("\n=== LOCAL DELETES ===")
    for rel in DELETES:
        local = os.path.join(LOCAL_BASE, rel)
        if os.path.exists(local):
            os.remove(local)
            print(f"  Deleted: {rel}")
        else:
            print(f"  Skip:    {rel} (already absent)")

    print("\nDone.")

if __name__ == "__main__":
    main()
