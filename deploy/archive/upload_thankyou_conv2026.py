#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = ["thank-you.php"]

def upload():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print("Connected.\n")

    success = 0
    for f in FILES_TO_UPLOAD:
        local = os.path.join(LOCAL_BASE, f)
        with open(local, "rb") as fp:
            ftp.storbinary(f"STOR {os.path.basename(f)}", fp)
        success += 1
        size = os.path.getsize(local)
        print(f"  Uploaded: {f} ({size} bytes)")

    ftp.quit()
    print(f"\nDone: {success}/{len(FILES_TO_UPLOAD)} files deployed.")

if __name__ == "__main__":
    upload()
