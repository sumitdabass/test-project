#!/usr/bin/env python3
import ftplib
import os
from pathlib import Path

# FTP Credentials
FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"

# Local directories with modified files
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

# Files to upload (relative paths from website_download)
FILES_TO_UPLOAD = [
    "explore-MSIT-and-MSI-janakpuri.php",
    "BVP.php",
    "vips-pitampura-courses.php",
    "include/common-head.php"
]

def upload_files_to_ftp():
    """Upload modified files to FTP server"""
    try:
        # Connect to FTP server
        print(f"🔗 Connecting to {FTP_HOST}...")
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("✅ Connected and logged in successfully!")
        
        # Change to remote directory
        print(f"\n📁 Navigating to {FTP_REMOTE_PATH}...")
        ftp.cwd(FTP_REMOTE_PATH)
        print("✅ Changed to public_html directory")
        
        # Upload each file
        print("\n📤 Uploading modified files...\n")
        for file_path in FILES_TO_UPLOAD:
            local_file = os.path.join(LOCAL_BASE, file_path)
            remote_file = file_path
            
            # Check if local file exists
            if not os.path.exists(local_file):
                print(f"❌ Local file not found: {local_file}")
                continue
            
            # Create subdirectory if needed (for include/common-head.php)
            if "/" in remote_file:
                subdir = remote_file.split("/")[0]
                try:
                    ftp.cwd(subdir)
                    ftp.cwd("..")
                    print(f"   📁 Subdirectory '{subdir}' exists")
                except ftplib.all_errors:
                    print(f"   ⚠️  Subdirectory '{subdir}' not found, attempting to create...")
                    try:
                        ftp.mkd(subdir)
                        print(f"   ✅ Created subdirectory '{subdir}'")
                    except ftplib.all_errors as e:
                        print(f"   ❌ Could not create subdirectory: {e}")
            
            # Upload file
            try:
                with open(local_file, "rb") as f:
                    ftp.storbinary(f"STOR {remote_file}", f)
                print(f"✅ Uploaded: {remote_file}")
            except Exception as e:
                print(f"❌ Failed to upload {remote_file}: {e}")
        
        # Close connection
        ftp.quit()
        print("\n✅ FTP connection closed successfully!")
        print("\n🎉 Upload complete! Your changes are now live on the production server.")
        
    except ftplib.all_errors as e:
        print(f"❌ FTP Error: {e}")
        return False
    except Exception as e:
        print(f"❌ Error: {e}")
        return False
    
    return True

if __name__ == "__main__":
    print("=" * 60)
    print("IPU Website - FTP Upload Script")
    print("=" * 60)
    print(f"\nServer: {FTP_HOST}")
    print(f"User: {FTP_USER}")
    print(f"Remote Path: {FTP_REMOTE_PATH}\n")
    
    upload_files_to_ftp()
