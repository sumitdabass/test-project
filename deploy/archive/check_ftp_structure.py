#!/usr/bin/env python3
import ftplib

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"

try:
    print(f"🔗 Connecting to {FTP_HOST}...")
    ftp = ftplib.FTP(FTP_HOST)
    ftp.login(FTP_USER, FTP_PASS)
    print("✅ Logged in successfully!\n")
    
    # Get current directory
    cwd = ftp.pwd()
    print(f"📁 Current directory: {cwd}\n")
    
    # List root contents
    print("📂 Root directory contents:")
    print("-" * 60)
    ftp.dir()
    
    ftp.quit()
    
except Exception as e:
    print(f"❌ Error: {e}")
