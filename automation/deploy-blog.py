#!/usr/bin/env python3
"""
Deploy newly generated blog posts to the FTP server.
Updates blog-data.php and sitemap.xml on the remote server.
"""

import json
import os
import sys
import ftplib
import io
import re
from datetime import datetime
from pathlib import Path

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = BASE_DIR.parent
NEW_FILES_LIST = BASE_DIR / "new-files.json"
WEBSITE_DIR = PROJECT_ROOT / "website_download"

SITE_DOMAIN = "https://ipu.co.in"
REMOTE_ROOT = "/public_html"  # Adjust to your FTP root

# ---------------------------------------------------------------------------
# FTP Helpers
# ---------------------------------------------------------------------------

def get_ftp_connection() -> ftplib.FTP:
    """Create and return an FTP connection using environment variables."""
    host = os.environ.get("FTP_HOST")
    user = os.environ.get("FTP_USER")
    password = os.environ.get("FTP_PASS")

    if not all([host, user, password]):
        print("[FTP] Missing FTP credentials in environment variables.", file=sys.stderr)
        print("[FTP] Required: FTP_HOST, FTP_USER, FTP_PASS", file=sys.stderr)
        sys.exit(1)

    try:
        ftp = ftplib.FTP(host, timeout=60)
        ftp.login(user, password)
        ftp.set_pasv(True)
        print(f"[FTP] Connected to {host}")
        return ftp
    except ftplib.all_errors as e:
        print(f"[FTP] Connection failed: {e}", file=sys.stderr)
        sys.exit(1)


def ensure_remote_dir(ftp: ftplib.FTP, remote_path: str):
    """Ensure a remote directory exists, creating it if needed."""
    dirs = remote_path.strip("/").split("/")
    current = ""
    for d in dirs:
        current += f"/{d}"
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            try:
                ftp.mkd(current)
                print(f"[FTP] Created directory: {current}")
            except ftplib.error_perm:
                pass  # May already exist


def upload_file(ftp: ftplib.FTP, local_path: Path, remote_path: str):
    """Upload a single file to the FTP server."""
    try:
        # Ensure parent directory exists
        remote_dir = "/".join(remote_path.split("/")[:-1])
        if remote_dir:
            ensure_remote_dir(ftp, remote_dir)

        with open(local_path, "rb") as f:
            ftp.storbinary(f"STOR {remote_path}", f)
        print(f"[FTP] Uploaded: {remote_path}")
        return True
    except ftplib.all_errors as e:
        print(f"[FTP] Failed to upload {remote_path}: {e}", file=sys.stderr)
        return False


def upload_content(ftp: ftplib.FTP, content: str, remote_path: str):
    """Upload string content directly to a remote file."""
    try:
        remote_dir = "/".join(remote_path.split("/")[:-1])
        if remote_dir:
            ensure_remote_dir(ftp, remote_dir)

        bio = io.BytesIO(content.encode("utf-8"))
        ftp.storbinary(f"STOR {remote_path}", bio)
        print(f"[FTP] Uploaded content to: {remote_path}")
        return True
    except ftplib.all_errors as e:
        print(f"[FTP] Failed to upload content to {remote_path}: {e}", file=sys.stderr)
        return False


def download_content(ftp: ftplib.FTP, remote_path: str) -> str | None:
    """Download a remote file and return its content as a string."""
    try:
        bio = io.BytesIO()
        ftp.retrbinary(f"RETR {remote_path}", bio.write)
        return bio.getvalue().decode("utf-8")
    except ftplib.all_errors as e:
        print(f"[FTP] Failed to download {remote_path}: {e}", file=sys.stderr)
        return None


# ---------------------------------------------------------------------------
# Blog Data Update
# ---------------------------------------------------------------------------

def update_blog_data(ftp: ftplib.FTP, new_files: list):
    """
    Download blog-data.php, prepend new blog entries, and re-upload.
    """
    remote_blog_data = f"{REMOTE_ROOT}/include/blog-data.php"

    # Download existing blog-data.php
    existing_content = download_content(ftp, remote_blog_data)
    if existing_content is None:
        print("[Deploy] Could not download blog-data.php. Creating new one.", file=sys.stderr)
        existing_content = "<?php\n$blogs = [\n];\n?>"

    # Build new entries PHP code
    new_entries_php = []
    for entry in new_files:
        title_escaped = entry["title"].replace("'", "\\'")
        excerpt_escaped = entry.get("excerpt", "").replace("'", "\\'")
        category = entry.get("category", "Admissions")
        read_time = entry.get("read_time", "5 min")
        url = entry["file"]  # e.g., blog/slug.php
        img = "assets/images/blog-default-og.jpg"

        php_entry = (
            f"    ['category' => '{category}', "
            f"'title' => '{title_escaped}', "
            f"'url' => '{url}', "
            f"'img' => '{img}', "
            f"'excerpt' => '{excerpt_escaped}', "
            f"'read_time' => '{read_time}'],"
        )
        new_entries_php.append(php_entry)

    if not new_entries_php:
        print("[Deploy] No new entries to add to blog-data.php")
        return

    new_entries_block = "\n".join(new_entries_php)

    # Insert new entries at the beginning of the $blogs array
    # Find the opening of the array and insert after it
    pattern = r"(\$blogs\s*=\s*\[)"
    replacement = f"\\1\n{new_entries_block}"
    updated_content = re.sub(pattern, replacement, existing_content, count=1)

    if updated_content == existing_content:
        # Fallback: try to find the array start differently
        insert_point = existing_content.find("$blogs = [")
        if insert_point == -1:
            insert_point = existing_content.find("$blogs=[")
        if insert_point != -1:
            bracket_pos = existing_content.find("[", insert_point)
            updated_content = (
                existing_content[: bracket_pos + 1]
                + "\n"
                + new_entries_block
                + existing_content[bracket_pos + 1 :]
            )

    # Upload updated blog-data.php
    upload_content(ftp, updated_content, remote_blog_data)
    print(f"[Deploy] Updated blog-data.php with {len(new_entries_php)} new entries")


# ---------------------------------------------------------------------------
# Sitemap Update
# ---------------------------------------------------------------------------

def update_sitemap(ftp: ftplib.FTP, new_files: list):
    """
    Download sitemap.xml, add new URLs, and re-upload.
    """
    remote_sitemap = f"{REMOTE_ROOT}/sitemap.xml"

    existing_content = download_content(ftp, remote_sitemap)
    if existing_content is None:
        # Create a basic sitemap
        existing_content = (
            '<?xml version="1.0" encoding="UTF-8"?>\n'
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
            '</urlset>'
        )

    today = datetime.now().strftime("%Y-%m-%d")

    # Build new URL entries
    new_url_entries = []
    for entry in new_files:
        url = entry.get("url", f"{SITE_DOMAIN}/{entry['file']}")
        url_entry = (
            f"  <url>\n"
            f"    <loc>{url}</loc>\n"
            f"    <lastmod>{today}</lastmod>\n"
            f"    <changefreq>monthly</changefreq>\n"
            f"    <priority>0.7</priority>\n"
            f"  </url>"
        )
        new_url_entries.append(url_entry)

    if not new_url_entries:
        print("[Deploy] No new URLs to add to sitemap.xml")
        return

    new_urls_block = "\n".join(new_url_entries)

    # Insert before closing </urlset>
    updated_content = existing_content.replace(
        "</urlset>",
        f"{new_urls_block}\n</urlset>"
    )

    upload_content(ftp, updated_content, remote_sitemap)
    print(f"[Deploy] Updated sitemap.xml with {len(new_url_entries)} new URLs")


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    print("=" * 60)
    print(f"Blog Deploy Script - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)

    # Load the list of newly generated files
    if not NEW_FILES_LIST.exists():
        print("[Deploy] No new-files.json found. Nothing to deploy.")
        return

    with open(NEW_FILES_LIST, "r", encoding="utf-8") as f:
        new_files = json.load(f)

    if not new_files:
        print("[Deploy] No new files to deploy.")
        return

    print(f"[Deploy] {len(new_files)} new files to deploy")

    # Connect to FTP
    ftp = get_ftp_connection()

    try:
        # 1. Upload each new PHP blog file
        uploaded_count = 0
        for entry in new_files:
            local_file = WEBSITE_DIR / entry["file"]
            remote_file = f"{REMOTE_ROOT}/{entry['file']}"

            if local_file.exists():
                if upload_file(ftp, local_file, remote_file):
                    uploaded_count += 1
            else:
                print(f"[Deploy] Local file not found: {local_file}", file=sys.stderr)

        print(f"[Deploy] Uploaded {uploaded_count}/{len(new_files)} blog files")

        # 2. Update blog-data.php with new entries
        update_blog_data(ftp, new_files)

        # 3. Update sitemap.xml with new URLs
        update_sitemap(ftp, new_files)

        print(f"\n{'=' * 60}")
        print(f"[Done] Deployment complete.")
        print(f"  - Blog files uploaded: {uploaded_count}")
        print(f"  - blog-data.php updated")
        print(f"  - sitemap.xml updated")
        print("=" * 60)

    finally:
        try:
            ftp.quit()
        except ftplib.all_errors:
            ftp.close()
        print("[FTP] Disconnected")


if __name__ == "__main__":
    main()
