#!/usr/bin/env python3
"""Canonical FTP deployer for ipu.co.in. Replaces the ~36 one-off upload_*.py scripts.

Credentials come ONLY from the environment (never hardcoded):
    FTP_HOST, FTP_USER, FTP_PASS, REMOTE_ROOT (default /public_html)

Usage:
    python3 deploy.py --files website_download/a.php website_download/b.php
    python3 deploy.py --manifest changes.txt          # one local path per line
    python3 deploy.py --files ... --dry-run            # list, never connect (DEFAULT-safe)
    python3 deploy.py --delete /public_html/old.php --yes   # confirm-gated remote delete
Local paths under website_download/ map to REMOTE_ROOT preserving the sub-path.
"""
from __future__ import annotations
import argparse, ftplib, os, sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
WEB = HERE / "website_download"

def remote_for(local: Path, remote_root: str) -> str:
    rel = local.resolve().relative_to(WEB.resolve())
    return f"{remote_root}/{rel.as_posix()}"

def ensure_remote_dir(ftp: ftplib.FTP, path: str) -> None:
    cwd = ""
    for part in [p for p in path.split("/") if p]:
        cwd += "/" + part
        try:
            ftp.cwd(cwd)
        except ftplib.error_perm:
            ftp.mkd(cwd); ftp.cwd(cwd)

def load_manifest(path: str) -> list[Path]:
    out = []
    with open(path) as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith("#"):
                out.append((HERE / line).resolve())
    return out

def main() -> int:
    ap = argparse.ArgumentParser(description="Canonical FTP deployer for ipu.co.in")
    ap.add_argument("--files", nargs="*", default=[], help="local paths to upload")
    ap.add_argument("--manifest", help="file with one local path per line")
    ap.add_argument("--delete", nargs="*", default=[], help="remote paths to delete (needs --yes)")
    ap.add_argument("--dry-run", action="store_true", help="list actions, never connect")
    ap.add_argument("--yes", action="store_true", help="confirm destructive --delete")
    args = ap.parse_args()

    remote_root = os.environ.get("REMOTE_ROOT", "/public_html").rstrip("/")

    locals_: list[Path] = [(HERE / f).resolve() for f in args.files]
    if args.manifest:
        locals_ += load_manifest(args.manifest)

    uploads = [(p, remote_for(p, remote_root)) for p in locals_]
    missing = [p for p, _ in uploads if not p.exists()]
    if missing:
        print("Missing local files:", file=sys.stderr)
        for m in missing:
            print(f"  - {m}", file=sys.stderr)
        return 1

    print(f"Plan: upload {len(uploads)} file(s) -> {remote_root}/")
    for p, r in uploads:
        print(f"  {p.relative_to(HERE)} -> {r}")
    if args.delete:
        print(f"Plan: DELETE {len(args.delete)} remote path(s):")
        for d in args.delete:
            print(f"  x {d}")

    if args.dry_run:
        print("dry-run; nothing uploaded or deleted")
        return 0

    if args.delete and not args.yes:
        if os.environ.get("DEPLOY_NONINTERACTIVE"):
            print("Refusing --delete without --yes (non-interactive). Re-run with --yes to confirm.", file=sys.stderr)
            return 2
        ans = input(f"Delete {len(args.delete)} remote file(s)? type 'yes': ")
        if ans.strip() != "yes":
            print("aborted", file=sys.stderr); return 2

    for var in ("FTP_HOST", "FTP_USER", "FTP_PASS"):
        if not os.environ.get(var):
            print(f"{var} not set in environment", file=sys.stderr); return 2

    ftp = ftplib.FTP(os.environ["FTP_HOST"], timeout=60)
    ftp.login(os.environ["FTP_USER"], os.environ["FTP_PASS"]); ftp.set_pasv(True)
    print(f"connected to {os.environ['FTP_HOST']} as {os.environ['FTP_USER']}")
    try:
        for p, r in uploads:
            ensure_remote_dir(ftp, r.rsplit("/", 1)[0])
            with open(p, "rb") as fh:
                ftp.storbinary(f"STOR {r}", fh)
            print(f"  uploaded {r}")
        for d in args.delete:
            try:
                ftp.delete(d); print(f"  deleted {d}")
            except ftplib.error_perm as e:
                print(f"  could not delete {d}: {e}", file=sys.stderr)
    finally:
        ftp.quit()
    print(f"done: {len(uploads)} uploaded, {len(args.delete)} delete(s) attempted")
    return 0

if __name__ == "__main__":
    sys.exit(main())
