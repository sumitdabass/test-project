#!/usr/bin/env python3
"""Upload the /news/ module to cPanel via FTP.

Scope: uploads ONLY files owned by the news module — never touches other
parts of the site. Exits 0 on success, non-zero on failure.

Environment variables (required):
    FTP_HOST     e.g. ftp.ipu.co.in
    FTP_USER     cPanel FTP username
    FTP_PASS     cPanel FTP password
    REMOTE_ROOT  optional, default '/public_html'

Usage:
    # upload everything owned by the news module
    python3 upload_news.py

    # upload + delete remote posts that no longer exist locally (used by CI)
    python3 upload_news.py --sync

    # upload only a single post + the pieces that reference it
    python3 upload_news.py --slug round-2-counselling-schedule

    # dry-run: list what would upload, don't connect
    python3 upload_news.py --dry-run
"""
from __future__ import annotations

import argparse
import ftplib
import os
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
WEB = HERE / "website_download"


def ensure_remote_dir(ftp: ftplib.FTP, path: str) -> None:
    """mkdir -p equivalent over FTP. Walks to each segment and creates if missing."""
    parts = [p for p in path.split("/") if p]
    cwd = ""
    for part in parts:
        cwd += "/" + part
        try:
            ftp.cwd(cwd)
        except ftplib.error_perm:
            ftp.mkd(cwd)
            ftp.cwd(cwd)


def upload_file(ftp: ftplib.FTP, local: Path, remote: str) -> None:
    ensure_remote_dir(ftp, remote.rsplit("/", 1)[0])
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)
    print(f"  ✓ {remote}")


def files_for_slug(slug: str, remote_root: str) -> list[tuple[Path, str]]:
    """A single-post deploy: the post page + the regenerated index + sitemap + llms.txt."""
    return [
        (WEB / f"news/{slug}.php", f"{remote_root}/news/{slug}.php"),
        (WEB / "news/index.php", f"{remote_root}/news/index.php"),
        (WEB / "sitemap.xml", f"{remote_root}/sitemap.xml"),
        (WEB / "llms.txt", f"{remote_root}/llms.txt"),
    ]


def files_for_full(remote_root: str) -> list[tuple[Path, str]]:
    """Full news-module deploy: every news page, all include templates, category images, sitemap, llms.txt."""
    out: list[tuple[Path, str]] = []

    # generated post pages + index
    for php in sorted((WEB / "news").glob("*.php")):
        out.append((php, f"{remote_root}/news/{php.name}"))

    # news-specific includes (template changes need to FTP even though they're not in /news/)
    for inc in ("news-template.php", "news-card.php", "news-helpers.php", "news-jsonld.php"):
        local = WEB / "include" / inc
        if local.exists():
            out.append((local, f"{remote_root}/include/{inc}"))

    # category images
    for img in sorted((WEB / "assets/images/news").glob("*")):
        if img.is_file():
            out.append((img, f"{remote_root}/assets/images/news/{img.name}"))

    # site-level files the news module updates
    for site_file in ("sitemap.xml", "llms.txt"):
        local = WEB / site_file
        if local.exists():
            out.append((local, f"{remote_root}/{site_file}"))

    return out


def sync_delete_remote_orphans(ftp: ftplib.FTP, remote_news_dir: str, local_news_dir: Path) -> list[str]:
    """List .php files in the remote /news/ dir; delete any that don't have a local
    counterpart. Preserves anything else (subdirectories, non-PHP files)."""
    local_names = {p.name for p in local_news_dir.glob("*.php")}
    # LIST the remote dir
    listing: list[str] = []
    try:
        ftp.cwd(remote_news_dir)
        ftp.retrlines("NLST", listing.append)
    except ftplib.error_perm:
        return []  # remote dir doesn't exist yet — nothing to clean
    finally:
        ftp.cwd("/")

    deleted: list[str] = []
    for remote_name in listing:
        if not remote_name.endswith(".php"):
            continue
        if remote_name in local_names:
            continue
        remote_path = f"{remote_news_dir}/{remote_name}"
        try:
            ftp.delete(remote_path)
            deleted.append(remote_path)
            print(f"  ✗ deleted orphan: {remote_path}")
        except ftplib.error_perm as e:
            print(f"  ⚠ could not delete {remote_path}: {e}")
    return deleted


def main() -> int:
    parser = argparse.ArgumentParser(description="Upload the /news/ module to cPanel via FTP.")
    parser.add_argument("--slug", help="single-post deploy: only this post (+ index, sitemap, llms.txt)")
    parser.add_argument("--sync", action="store_true",
                        help="after uploading, delete any remote /news/*.php that no longer exists locally")
    parser.add_argument("--dry-run", action="store_true", help="list files, skip the FTP connection")
    args = parser.parse_args()

    if args.slug and args.sync:
        print("--sync not meaningful with --slug (single-post deploy)", file=sys.stderr)
        return 2

    remote_root = os.environ.get("REMOTE_ROOT", "/public_html").rstrip("/")

    if args.slug:
        files = files_for_slug(args.slug, remote_root)
    else:
        files = files_for_full(remote_root)

    missing = [local for local, _ in files if not local.exists()]
    if missing:
        print("Missing local files (did you run `php scripts/build-news.php` first?):", file=sys.stderr)
        for m in missing:
            print(f"  - {m}", file=sys.stderr)
        return 1

    print(f"Plan: {len(files)} files → {remote_root}/")
    for local, remote in files:
        print(f"  {local.relative_to(HERE)} → {remote}")

    if args.dry_run:
        print("dry-run; nothing uploaded")
        return 0

    for required in ("FTP_HOST", "FTP_USER", "FTP_PASS"):
        if not os.environ.get(required):
            print(f"{required} not set in environment", file=sys.stderr)
            return 2

    ftp = ftplib.FTP(os.environ["FTP_HOST"], timeout=60)
    ftp.login(os.environ["FTP_USER"], os.environ["FTP_PASS"])
    ftp.set_pasv(True)
    print(f"connected to {os.environ['FTP_HOST']} as {os.environ['FTP_USER']}")

    try:
        for local, remote in files:
            upload_file(ftp, local, remote)
        deleted: list[str] = []
        if args.sync:
            print(f"\nsync-checking {remote_root}/news for orphans...")
            deleted = sync_delete_remote_orphans(ftp, f"{remote_root}/news", WEB / "news")
    finally:
        ftp.quit()

    print(f"\nuploaded {len(files)} files", end="")
    if args.sync:
        print(f"; deleted {len(deleted)} remote orphan(s)")
    else:
        print()
    return 0


if __name__ == "__main__":
    sys.exit(main())
