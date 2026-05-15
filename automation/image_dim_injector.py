#!/usr/bin/env python3
"""
Phase B Day 5 — Inject width/height on <img> tags missing them.

Scans website_download/*.php and include/*.php for <img> tags without
both width and height attributes. For each one, resolves the image
file, reads dimensions via PIL, and rewrites the tag in-place.

Skips:
  - Tags with width AND height already set
  - Tags whose src is a URL (cross-origin)
  - Tags whose src can't be resolved to a file
"""
import os
import re
import sys
from PIL import Image

ROOT = "/Users/Sumit/test-project/website_download"

# Match <img ...> tags, capturing the attribute block
IMG_TAG = re.compile(r"<img\s+([^>]+?)\s*/?>", re.IGNORECASE)
SRC_ATTR = re.compile(r'src\s*=\s*"([^"]+)"', re.IGNORECASE)
HAS_W = re.compile(r'\bwidth\s*=', re.IGNORECASE)
HAS_H = re.compile(r'\bheight\s*=', re.IGNORECASE)

def resolve_src(src, file_dir):
    """Resolve src to absolute filesystem path. Returns None if external or missing."""
    if src.startswith(("http://", "https://", "//", "data:")):
        return None
    if src.startswith("/"):
        path = os.path.join(ROOT, src.lstrip("/"))
        return path if os.path.exists(path) else None
    # relative — try from the .php file's own directory first, then from ROOT
    path = os.path.join(file_dir, src)
    if os.path.exists(path):
        return path
    path_from_root = os.path.join(ROOT, src)
    return path_from_root if os.path.exists(path_from_root) else None

def get_dims(path):
    try:
        with Image.open(path) as im:
            return im.size
    except Exception as e:
        print(f"  ! cannot read {path}: {e}", file=sys.stderr)
        return None

def patch_file(filepath):
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = content
    changed = 0
    file_dir = os.path.dirname(filepath)

    for m in IMG_TAG.finditer(content):
        attrs = m.group(1)
        if HAS_W.search(attrs) and HAS_H.search(attrs):
            continue
        src_m = SRC_ATTR.search(attrs)
        if not src_m:
            continue
        src = src_m.group(1)
        path = resolve_src(src, file_dir)
        if not path:
            continue
        dims = get_dims(path)
        if not dims:
            continue
        w, h = dims
        new_attrs = attrs
        if not HAS_W.search(attrs):
            new_attrs = new_attrs + f' width="{w}"'
        if not HAS_H.search(attrs):
            new_attrs = new_attrs + f' height="{h}"'
        new_tag = f"<img {new_attrs}>"
        # Use str.replace with a single replacement to avoid touching other tags
        new_content = new_content.replace(m.group(0), new_tag, 1)
        changed += 1

    if changed:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"  + {os.path.relpath(filepath, ROOT)}: {changed} img tag(s) patched")
    return changed

def main():
    total = 0
    files_changed = 0
    for dirpath, _, files in os.walk(ROOT):
        # Skip the .private dir and any vendor/node_modules
        if "/.private" in dirpath or "/vendor" in dirpath or "/node_modules" in dirpath:
            continue
        for fn in files:
            if not fn.endswith(".php"):
                continue
            fp = os.path.join(dirpath, fn)
            changed = patch_file(fp)
            if changed:
                total += changed
                files_changed += 1
    print(f"\nDone. {total} img tag(s) patched across {files_changed} file(s).")

if __name__ == "__main__":
    main()
