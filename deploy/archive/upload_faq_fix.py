#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = [
    "index.php",
    "BVP.php",
    "mait-admission.php",
    "maims-admission.php",
    "mca-admission-ipu.php",
    "mba-admission-ip-university.php",
    "ipu-admission-guide.php",
    "ipu-cet-cutoff-2025.php",
    "ipu-bjmc-cutoff-2025.php",
    "ipu-bcom-cutoff-2025.php",
    "ipu-ba-economics-cutoff-2025.php",
    "ipu-mba-cutoff-2025.php",
    "GGSIPU-counselling-for-B-Tech-admission.php",
]

def upload():
    print(f"Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print("Connected.\n")

    success = 0
    for f in FILES_TO_UPLOAD:
        local = os.path.join(LOCAL_BASE, f)
        remote_dir = os.path.dirname(f)
        if remote_dir:
            try:
                ftp.cwd(FTP_REMOTE_PATH + "/" + remote_dir)
            except ftplib.error_perm:
                ftp.mkd(FTP_REMOTE_PATH + "/" + remote_dir)
                ftp.cwd(FTP_REMOTE_PATH + "/" + remote_dir)
        else:
            ftp.cwd(FTP_REMOTE_PATH)

        with open(local, "rb") as fp:
            ftp.storbinary(f"STOR {os.path.basename(f)}", fp)
        success += 1
        print(f"  Uploaded: {f}")

    ftp.quit()
    print(f"\nDone: {success}/{len(FILES_TO_UPLOAD)} files deployed.")

if __name__ == "__main__":
    upload()
