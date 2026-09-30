#!/usr/bin/env python3
"""
P0 Audit Fixes Deploy — 2026-05-07

Fixes from the multi-agent site re-analysis (4 parallel audits):

CONVERSION (P0):
- thank-you.php: replaced gtag() calls with dataLayer.push() — gtag.js was never
  loaded (only GTM is), so every enhanced conversion since the GTM-only
  migration was throwing ReferenceError and never reaching Google Ads.
- thank-you.php: gated the `form_submission` dataLayer event on ?src=submit so
  honeypot/dedup/cooldown redirects no longer inflate reported conversions.
- include/form-handler.php + sendemail.php: success path now redirects to
  /thank-you.php?src=submit (the success flag).
- b-tech-colleges-under-IP-university.php: form field name `mobile` → `phone`
  (the page's form-handler.php only reads $_POST['phone'], so every lead from
  this high-traffic page was being silently dropped). Also added honeypot,
  pattern, autocomplete, error rendering.
- sendemail.php: filter_var(... FILTER_VALIDATE_EMAIL) + CR/LF guard on
  Reply-To to prevent any email-header-injection edge case.

PROD WARNINGS (P0):
- course/index.php: 19 relative `include("include/...")` calls fixed to use
  __DIR__ — they were resolving to course/include/* (which doesn't exist) and
  spamming PHP warnings into the rendered HTML on every pageview.

EXPOSURE / SECURITY (P0):
- .htaccess: added <Files> deny rules for AI_AGENT_README.md and
  seo-report-march-2026.html (defence-in-depth on top of the existing
  log/zip/sql block).
- .htaccess: 301 redirect index-old.php and index-new.php → / so they can't
  rank as duplicate canonicals.
- robots.txt: Disallow /thank-you.php /sendemail.php /AI_AGENT_README.md
  /seo-report-march-2026.html.

SEO LINK HEALTH (P0):
- blog.php: 3 blog cards updated from dead 2025 URLs to live 2026 canonicals
  (economics-admission-2025 → economics-admission-ip-university; Law-2025 →
  IPU-Law-Admission; B-Tech-2025 → IPU-B-Tech-admission-2026). Also retagged
  one MCA tile that was mis-categorised as "MBA" + added MCA to category list.
- sitemap.xml: removed redirect entry (ipu-bba-admission.php), deduped
  llm-admission-ipu.php (older entry), refreshed 23 stale 2026-02-15 lastmods
  to 2026-05-07, and appended 8 previously-missing live pages (mca, mtech,
  med, economics-ip-university, top-btech-comparison, BVP college, MABS,
  privacy-policy).

PROD-SIDE DELETES handled inline (FTP DELE):
- error_log
- course/error_log
- AI_AGENT_README.md
- seo-report-march-2026.html
- assets/images.zip
- .DS_Store (best-effort)

Audit was wrong about and skipped:
- index.php:530 dead include_once 'include/blog.php' (file is 447 lines, no
  such include).
- blog.php:94 dead include_once 'include/blogside.php' (line is CSS; no such
  include anywhere).
- testblog.php (does not exist).
- include/base-nav.php containing redirect-target hrefs (it does not).
"""
import ftplib
import os
import sys
import time

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES_TO_UPLOAD = {
    "": [
        ".htaccess",
        "robots.txt",
        "thank-you.php",
        "sendemail.php",
        "b-tech-colleges-under-IP-university.php",
        "blog.php",
        "sitemap.xml",
    ],
    "include": [
        "form-handler.php",
    ],
    "course": [
        "index.php",
    ],
}

# Best-effort FTP DELE — failures are non-fatal (file may already be gone).
FILES_TO_DELETE = [
    "error_log",
    "course/error_log",
    "AI_AGENT_README.md",
    "seo-report-march-2026.html",
    "assets/images.zip",
    ".DS_Store",
]


def main():
    started = time.time()
    print(f"[*] Connecting to {FTP_HOST} as {FTP_USER} ...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"[+] Connected. CWD = {ftp.pwd()}")

    # ── Phase 1: deletes (best effort) ────────────────────────────────────
    print("\n[*] Phase 1: deleting exposed/junk files (best effort)")
    for relpath in FILES_TO_DELETE:
        try:
            ftp.delete(f"{FTP_REMOTE_PATH}/{relpath}")
            print(f"[-] deleted {relpath}")
        except ftplib.error_perm as e:
            print(f"[ ] skip {relpath} ({e})")

    # ── Phase 2: uploads ─────────────────────────────────────────────────
    print("\n[*] Phase 2: uploading fixed files")
    total = ok = 0
    for subdir, files in FILES_TO_UPLOAD.items():
        if subdir:
            ftp.cwd(f"{FTP_REMOTE_PATH}/{subdir}")
        else:
            ftp.cwd(FTP_REMOTE_PATH)
        for fname in files:
            total += 1
            local = os.path.join(LOCAL_BASE, subdir, fname) if subdir else os.path.join(LOCAL_BASE, fname)
            if not os.path.exists(local):
                print(f"[!] MISSING LOCAL: {local}"); continue
            size = os.path.getsize(local)
            with open(local, "rb") as f:
                ftp.storbinary(f"STOR {fname}", f)
            relpath = f"{subdir}/{fname}" if subdir else fname
            print(f"[+] {relpath} ({size:,} bytes)")
            ok += 1

    ftp.quit()
    print("")
    print(f"[=] {ok}/{total} files uploaded in {time.time()-started:.1f}s")
    if ok != total:
        sys.exit(1)


if __name__ == "__main__":
    main()
