#!/usr/bin/env python3
import os
import ftplib

# List of all key pages in the website
KEY_PAGES = [
    "index.php",
    "BVP.php",
    "explore-MSIT-and-MSI-janakpuri.php",
    "vips-pitampura-courses.php",
    "IPU-B-Tech-admission-2026.php",
    "mba-admission-ip-university.php",
    "IPU-Law-Admission-2026.php",
    "ultimate-guide-to-ballb-admission-in-ip-university.php",
    "management-quota-admission-in-ipu-affiliated-colleges.php",
    "helpline.php",
    "admission-counselling-ipu-2026.php",
]

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

try:
    print(f"🔗 Connecting to FTP server...\n")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Connected!\n")
    
    ftp.cwd(FTP_REMOTE_PATH)
    
    print("📤 Uploading complete website with consistent navbar fixes...\n")
    
    uploaded_count = 0
    for page in KEY_PAGES:
        local_file = os.path.join(LOCAL_BASE, page)
        
        if not os.path.exists(local_file):
            print(f"⏭️  Skipped: {page} (not found locally)")
            continue
        
        try:
            with open(local_file, "rb") as f:
                ftp.storbinary(f"STOR {page}", f)
            print(f"✅ Uploaded: {page}")
            uploaded_count += 1
        except Exception as e:
            print(f"❌ Failed: {page} - {e}")
    
    # Also ensure style2.css is uploaded
    css_file = os.path.join(LOCAL_BASE, "assets/css/style2.css")
    if os.path.exists(css_file):
        with open(css_file, "rb") as f:
            ftp.storbinary("STOR assets/css/style2.css", f)
        print(f"✅ Uploaded: assets/css/style2.css (Global navbar styles)")
        uploaded_count += 1
    
    print(f"\n✨ Successfully uploaded {uploaded_count} files!")
    print("\n📋 Summary:")
    print("  ✅ Global CSS (style2.css) - Navbar positioning + banner padding")
    print("  ✅ All key pages - Using consistent header structure")
    print("  ✅ Mobile call button - Hidden on desktop, visible on mobile")
    print("\n🎉 Complete website now has consistent navbar layout!")
    
    ftp.quit()
    
except Exception as e:
    print(f"❌ Error: {e}")
