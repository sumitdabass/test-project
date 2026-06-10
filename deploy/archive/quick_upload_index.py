#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"

LOCAL_BASE = "/Users/Sumit/test-project/website_download"
FILES_TO_UPLOAD = ["index.php"]

try:
    print(f"🔗 Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Connected!\n")
    
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"📤 Uploading corrected index.php...\n")
    
    local_file = os.path.join(LOCAL_BASE, "index.php")
    with open(local_file, "rb") as f:
        ftp.storbinary("STOR index.php", f)
    
    print("✅ Successfully uploaded index.php")
    print("\n🎉 Fix deployed! Menu bar issue should be resolved.")
    
    ftp.quit()
    
except Exception as e:
    print(f"❌ Error: {e}")
