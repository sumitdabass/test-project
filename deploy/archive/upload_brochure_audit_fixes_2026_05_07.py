#!/usr/bin/env python3
"""
Brochure-vs-Website Audit Fixes Deploy — 2026-05-07

Cross-checked every page against UG Brochure 2026-27 + PG Brochure 2026-27. Many
factual errors (entrance hierarchy, programme codes, fees, seat counts, 3-yr LLB
fabrications, BCA duration, mgmt-quota %, JSON-LD fee fabrications, B.Arch
JEE-Paper-2 claim, USAR school name, etc.) are now brochure-cited and corrected.

Batches deployed (12+ batches, ~22 files):
1.  Mgmt-quota cap 15% → 10% across 7 files (8 occurrences) — JSON-LD + body
    text. Brochure source: Ch 12 p.122 Section 12(1)(a) DPCI Rules 2007.
2.  JSON-LD fee fabrications: btech-mgmt-quota Rs.2-4L → 1.41-1.55L per 6th
    SFRC; mba-mgmt-quota "CAT not mandatory" → IS mandatory per Ch 12 Note 2;
    VIPS removed from B.Tech mgmt-quota college list (VIPS doesn't offer
    B.Tech).
3.  B.Arch (barch-admission-ipu.php): NATA only — JEE Main Paper 2 NOT accepted
    by IPU. Eligibility PCM 50% → Phys+Math+1-of-many at 45%. Source attribution
    PG → UG brochure. Comparison table rewritten as NATA-only.
4.  BCA: duration 3-yr → 4-yr (NEP 2020), entrance CUET-only → CET primary +
    CUET fallback (Code 114, Brochure p.35), Math/CS optional → mandatory.
5.  LL.M.: leftover wrong specs (Constitutional/International) → Corporate/IPR/
    Criminal Justice/ADR (per PG Brochure Ch 13). "IPU CET for Law PG" claim
    removed (no such CET exists; CLAT-PG only).
6.  M.Tech: blanket "GATE+CET+CUET fallback" → per-code table per PG Brochure
    Table 1.1. Code 141 (AI&DS) flagged as no-CET-fallback; Code 151 (Industrial
    Biotech) flagged as GAT-B only.
7.  B.Com + BBA hub: CUET-primary framing → CET primary + CUET fallback per
    Codes 146 + 125. B.Com 3-yr → 4-yr. BBA 50%-55% range → fixed 50% per
    brochure p.42.
8.  BBA-LLB / BA-LLB: "exclusively/strictly CLAT" → CLAT priority 1 + CUET
    priority 2 per Code 121. Eligibility detailed with BCI bar.
9.  IPU-Law-Admission.php: FAQ self-contradiction fixed (3-yr LLB IS at IPU);
    "CUET or CLAT" inverted → CLAT primary + CUET fallback.
10. ballb-management-quota-ipu.php: full restructure — added Code 121, BCI bar,
    Rs.2,500 reg-fee cap, 6th SFRC fee citation; removed false claim that CLAT
    isn't mandatory.
11. top-mba-colleges-ipu.php / top-mca-colleges-ipu.php: USMS Rs.1,30,000 →
    Rs.1,93,600 (per PG Brochure §14.1H); USICT Rs.1,30,000 → Rs.1,45,200 (per
    PG Brochure §14.1G); fee range Rs.1.10-1.60L → Rs.1.10-1.93L.
12. ipu-btech-via-cuet.php: "alternative pathway" framing → CUET as second-
    priority vacant-seat fallback per Important Instruction #37 + UG Brochure
    §1.2 p.29.
13a. BVP.php: identity confusion fixed — H1/breadcrumb/body now consistently
    "Bharati Vidyapeeth's College of Engineering" (brochure SN 11), not the
    unrelated "Bharatiya Vidya Bhavan". Orphan URL
    bharati-vidyapeeth-engineering-college-... (which contained MAIT/MAIMS
    content) now 301s to BVP.php.
13b. usar-admission.php: "Robotics & Automation" → "Automation & Design" per
    Brochure Ch 13 SN 14. Programme list rewritten with the four B.Tech/M.Tech
    Dual-Degrees (132 each) + PG Diploma (60). usls-admission.php: removed
    fabricated "LLB (3-Year)" row at USL&LS (the 3-yr LLB is at affiliated
    colleges, not USL&LS per brochure SN 11).
13c. usms-admission.php: removed fabricated "MBA International Business" (not
    in brochure SN 3); added missing MBA(FA), MBA(A), MBA(W), BBA, B.Com(H).
    usict-admission.php: standalone B.Tech rows (60 seats) → 5-yr B.Tech/M.Tech
    Dual-Degree rows (180/120/120/60/60) per brochure SN 1.
13d. BPIT.php: seat counts corrected to brochure SN 10 (CSE 180, IT 180, ECE
    120, EEE 60, CSE-DS 60). MAIT.php: B.Tech IT 60→300, MBA 60→180; full
    programme list expanded per SN 47. MSIT.php: CSE 180→240, IT 120→180,
    added EEE per SN 49.
14. base-head.php (carry-over): inline mgmt-quota citation patterns also
    deployed via this push. (Already shipped 2026-05-07 CLS fix earlier.)

Skipped from audit's punch list (still pending):
- BA English fees + duration (BA English Code 184 — fees in §14.1C, 4-yr)
- BA Economics duration + USS-vs-affiliated clarity
- 6th SFRC citation phrasing standardisation across all college pages
- Some seat-count rows on MAIT/MSIT/USICT secondary tables
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
        # Mgmt-quota %
        "ballb-management-quota-ipu.php",
        "mba-management-quota-ipu.php",
        "btech-management-quota-ipu.php",
        "comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php",
        "bvicam-admission.php",
        "dspsr-admission.php",
        "vips-admission.php",
        # B.Arch
        "barch-admission-ipu.php",
        # BCA
        "bca-admission-ipu.php",
        "top-bca-colleges-ipu.php",
        # LL.M.
        "llm-admission-ipu.php",
        # M.Tech
        "mtech-admission-ipu.php",
        # B.Com / BBA / BBA-LLB / BA-LLB
        "bcom-admission-ipu.php",
        "ultimate-guide-to-ballb-admission-in-ip-university.php",
        "comprehensive-guide-to-bballb-admission-in-ip-university.php",
        # IPU-Law-Admission.php
        "IPU-Law-Admission.php",
        # Top-MBA / Top-MCA fees
        "top-mba-colleges-ipu.php",
        "top-mca-colleges-ipu.php",
        # ipu-btech-via-cuet
        "ipu-btech-via-cuet.php",
        # College pages
        "BVP.php",
        "bharati-vidyapeeth-engineering-college-delhi-admission-courses-placement.php",
        "usar-admission.php",
        "usls-admission.php",
        "usms-admission.php",
        "usict-admission.php",
        "BPIT.php",
        "mait-admission.php",
        "msit-admission.php",
    ],
}


def main():
    started = time.time()
    print(f"[*] Connecting to {FTP_HOST} as {FTP_USER} ...")
    ftp = ftplib.FTP(FTP_HOST, timeout=60)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.cwd(FTP_REMOTE_PATH)
    print(f"[+] Connected. CWD = {ftp.pwd()}")

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
