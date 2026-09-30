#!/usr/bin/env python3
import ftplib
import os

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"

LOCAL_BASE = "/Users/Sumit/test-project/website_download"
FILES_TO_UPLOAD = [
    "assets/css/style2.css",
    "index.php"
]

try:
    print(f"🔗 Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Connected!\n")
    
    ftp.cwd(FTP_REMOTE_PATH)
    
    print("📤 Uploading navbar overlay fixes...\n")
    
    for file_path in FILES_TO_UPLOAD:
        local_file = os.path.join(LOCAL_BASE, file_path)
        with open(local_file, "rb") as f:
            ftp.storbinary(f"STOR {file_path}", f)
        print(f"✅ Uploaded: {file_path}")
    
    print("\n✨ All fixes deployed successfully!")
    print("\nWhat was fixed:")
    print("  ✅ Header moved down 32px (below phone bar)")
    print("  ✅ Banner padding adjusted to 100px")
    print("  ✅ Mobile call button hidden on desktop")
    
    ftp.quit()
    
except Exception as e:
    print(f"❌ Error: {e}")
