#!/usr/bin/env python3
"""
Phase A — Post-cohesion SEO + Mobile + AI audit deploy — 2026-05-15

Surgical FTP push of 46 files implementing the 7 P0 audit findings:
  A.1  .user.ini             session.cache_limiter=public  -> unlock CDN+browser caching site-wide
  A.2  46 PHP files          /ipu-bba-admission.php (301) -> direct link to consolidated BBA listicle
  A.3  18 PHP files          width/height on fetchpriority=high LCP images (CLS)
  A.4  base-head.php         env(safe-area-inset-bottom) on .mobile-call-cta + 16px font on btn
  A.5  robots.txt            modern AI bots (PerplexityBot, Google-Extended, Applebot-Extended, etc.)
  A.6  base-head.php         og:image fallback + viewport-fit + theme-color + apple-touch-icon
  A.7  last-updated.php +    visible "Last Updated · By IPU Admission Team" byline (dark/light themed)
       page-hero.php

Pre-deploy validation already done:
  - php -l: 0 errors across all 45 .php files
  - php -S smoke: 11 archetype pages return 200 with no warnings
  - curl headers: Cache-Control: public, max-age=1800 with cache_limiter=public override
  - Visual checks: byline renders, LCP images have dimensions, BBA links rewritten

Post-deploy verify:
  curl -sI https://ipu.co.in/ | grep -i cache-control          # expect: public, max-age=1800
  curl -s https://ipu.co.in/robots.txt | grep PerplexityBot    # expect: present
  curl -s https://ipu.co.in/ | grep -c ipu-bba-admission.php   # expect: 0
"""
import os
import ftplib
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

# Files relative to website_download/
FILES = [
    # New config
    ".user.ini",

    # Robots + AI bot manifest
    "robots.txt",
    "llms.txt",

    # Shared chrome (base-head = og:image, viewport-fit, theme-color, safe-area, mobile font)
    "include/base-head.php",
    "include/base-footer.php",
    "include/components/last-updated.php",
    "include/components/page-hero.php",
    "include/components/sidebar-enquiry.php",
    "include/sidebar-cta.php",
    "include/blog-data.php",

    # Pages with LCP image width/height + BBA-link rewrites
    "index.php",
    "index-new.php",
    "BPIT.php",
    "BVP.php",
    "IP-University-management-quota-admission-eligibility-criteria.php",
    "IPU-B-Tech-admission-2026.php",
    "IPU-Law-Admission.php",
    "api/agent-data.php",
    "bca-admission-ipu.php",
    "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
    "comprehensive-guide-to-bballb-admission-in-ip-university.php",
    "cuet-admission-ipu.php",
    "cuet-bba-admission-ipu.php",
    "cuet-bcom-admission-ipu.php",
    "cuet-btech-admission-ipu.php",
    "cuet-law-admission-ipu.php",
    "dme-admission.php",
    "economics-admission-ip-university.php",
    "explore-MSIT-and-MSI-janakpuri.php",
    "exploring-MAIT-and-MAIMS.php",
    "gibs-admission.php",
    "guide-to-bjmc-colleges-under-ip-university.php",
    "ideal-admission.php",
    "ipu-btech-cutoff-analysis.php",
    "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php",
    "ipu-colleges-list.php",
    "ipu-counselling.php",
    "ipu-fees-structure.php",
    "ipu-helpline-contact-number.php",
    "jemtec-admission.php",
    "jims-kalkaji-admission.php",
    "jims-vasant-kunj-admission.php",
    "kcc-admission.php",
    "law-3-year-admission-ipu.php",
    "maims-delhi-fees-courses.php",
    "rdias-admission.php",
    "thank-you.php",
    "ultimate-guide-to-ballb-admission-in-ip-university.php",
]


def ensure_remote_dir(ftp, path):
    """Recursively cd into / mkdir remote directory tree."""
    parts = [p for p in path.split("/") if p]
    for p in parts:
        try:
            ftp.cwd(p)
        except ftplib.error_perm:
            ftp.mkd(p)
            ftp.cwd(p)


def upload_one(ftp, rel_path):
    local = os.path.join(LOCAL_BASE, rel_path)
    if not os.path.exists(local):
        return False, f"MISSING: {local}"
    # Reset to docroot
    ftp.cwd("/" + FTP_REMOTE_PATH.strip("/"))
    sub = os.path.dirname(rel_path)
    if sub:
        ensure_remote_dir(ftp, sub)
    fname = os.path.basename(rel_path)
    with open(local, "rb") as fh:
        ftp.storbinary(f"STOR {fname}", fh)
    return True, f"  OK  {rel_path}"


def main():
    print(f"Phase A deploy — 46 files to {FTP_HOST}{FTP_REMOTE_PATH}\n")
    try:
        ftp = ftplib.FTP(FTP_HOST, timeout=30)
        ftp.login(FTP_USER, FTP_PASS)
        print("Connected.\n")
    except Exception as e:
        print(f"FTP connection failed: {e}", file=sys.stderr)
        sys.exit(1)

    ok, fail = 0, 0
    for rel in FILES:
        try:
            success, msg = upload_one(ftp, rel)
            print(msg)
            if success:
                ok += 1
            else:
                fail += 1
        except Exception as e:
            print(f"  FAIL  {rel}: {e}")
            fail += 1

    ftp.quit()
    print(f"\nUploaded {ok}/{len(FILES)} ({fail} failed)")
    sys.exit(0 if fail == 0 else 2)


if __name__ == "__main__":
    main()
