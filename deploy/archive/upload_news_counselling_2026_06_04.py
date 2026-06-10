#!/usr/bin/env python3
"""FTP deploy — merged GGSIPU counselling news post + regenerated index/sitemap/llms.

The 35 other news posts already live on prod via the news-build-deploy GH Action;
only these changed files are pushed.
"""
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = [
    "news/ipu-centralized-online-counselling-enrolment.php",
    "news/index.php",
    "sitemap.xml",
    "llms.txt",
]


def upload():
    print(f"🔗 Connecting to {FTP_HOST} ...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"✅ In {FTP_REMOTE_PATH}\n")
    ok = True
    for rel in FILES_TO_UPLOAD:
        local = os.path.join(LOCAL_BASE, rel)
        if not os.path.exists(local):
            print(f"❌ Missing local: {local}")
            ok = False
            continue
        try:
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR {rel}", f)
            print(f"✅ Uploaded: {rel}")
        except Exception as e:  # noqa: BLE001
            print(f"❌ Failed {rel}: {e}")
            ok = False
    ftp.quit()
    print("\n🎉 Done." if ok else "\n⚠️ Completed with errors.")
    return ok


if __name__ == "__main__":
    upload()
