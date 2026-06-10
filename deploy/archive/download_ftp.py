#!/usr/bin/env python3
import ftplib
import os
from pathlib import Path

# FTP credentials
host = "ftp.ipu.co.in"
user = "admission@ipu.co.in"
password = "Sumit@8022"
remote_path = "/public_html"
local_path = "/Users/Sumit/test-project/website_download"

# Create local directory if it doesn't exist
Path(local_path).mkdir(parents=True, exist_ok=True)

def download_recursive(ftp, remote_dir, local_dir):
    """Recursively download files from FTP server"""
    try:
        # Create local directory
        Path(local_dir).mkdir(parents=True, exist_ok=True)
        
        # List contents of remote directory
        items = ftp.nlst(remote_dir)
        print(f"Found {len(items)} items in {remote_dir}")
        
        for item in items:
            # Skip hidden files and current/parent directories
            if item.endswith('/.') or item.endswith('/..') or '/.well-known' in item:
                continue
                
            remote_file = item
            local_file = os.path.join(local_dir, os.path.basename(item))
            
            try:
                # Try to download as file
                print(f"Downloading: {remote_file}")
                with open(local_file, 'wb') as f:
                    ftp.retrbinary(f'RETR {remote_file}', f.write)
            except ftplib.all_errors:
                # If it's a directory, recurse
                try:
                    print(f"Entering directory: {remote_file}")
                    download_recursive(ftp, remote_file, local_file)
                except:
                    print(f"Skipping: {remote_file}")
                    pass
    except Exception as e:
        print(f"Error: {e}")

try:
    # Connect to FTP server
    print(f"Connecting to {host}...")
    ftp = ftplib.FTP(host)
    ftp.login(user, password)
    print("Connected!")
    
    # Download website
    print(f"Starting download from {remote_path}...")
    download_recursive(ftp, remote_path, local_path)
    
    ftp.quit()
    print("Download completed!")
    
except Exception as e:
    print(f"Connection error: {e}")
