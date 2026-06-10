#!/usr/bin/env python3
"""FTP deploy — GGSIPU counselling 2026 dates correction (Notification 26/2026).

Scope: the two ranking counselling pages only. News post + index/sitemap/llms are
intentionally NOT deployed here — they require reconciliation with the auto-scraped
news on origin/main first.
"""
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = [
    "GGSIPU-counselling-for-B-Tech-admission.php",
    "ipu-counselling.php",
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
