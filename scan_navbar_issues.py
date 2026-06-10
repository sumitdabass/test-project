#!/usr/bin/env python3
import os
import re

base_path = "/Users/Sumit/test-project/website_download"

# Find all PHP files that contain the problematic styles
print("🔍 Scanning for pages with navbar styling issues...\n")

files_to_fix = []
for root, dirs, files in os.walk(base_path):
    for file in files:
        if file.endswith(".php"):
            filepath = os.path.join(root, file)
            try:
                with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                    content = f.read()
                    # Look for the old .mobile-call-cta style
                    if ".mobile-call-cta" in content and "@media(max-width:768px)" in content:
                        rel_path = filepath.replace(base_path + "/", "")
                        files_to_fix.append(filepath)
                        print(f"Found: {rel_path}")
            except Exception as e:
                pass

print(f"\n📊 Total files needing fixes: {len(files_to_fix)}")
print("\nFiles to update:")
for f in files_to_fix:
    print(f"  - {f.replace(base_path, '')}")
