#!/usr/bin/env python3
"""Structural SEO checks for ipu.co.in pages.

Usage:
    python3 scripts/seo_verify.py --base http://localhost:8000 mait-cutoff.php msit-cutoff.php
    python3 scripts/seo_verify.py --base https://ipu.co.in --strict-faq-phone /mbs-college-admission.php
Exit code 1 if any page has a problem.
"""
from __future__ import annotations
import argparse, json, re, sys, urllib.request

PHONE = "9899991342"
LD_RE = re.compile(r'<script[^>]+type="application/ld\+json"[^>]*>(.*?)</script>', re.S | re.I)


def _walk(node):
    if isinstance(node, dict):
        yield node
        for v in node.values():
            yield from _walk(v)
    elif isinstance(node, list):
        for v in node:
            yield from _walk(v)


def check_html(html: str, strict_faq_phone: bool = False) -> list[str]:
    problems: list[str] = []
    n = len(re.findall(r"<h1[\s>]", html, re.I))
    if n != 1:
        problems.append(f"h1 count {n} != 1")
    n = len(re.findall(r'<link[^>]+rel="canonical"', html, re.I))
    if n != 1:
        problems.append(f"canonical count {n} != 1")
    n = len(re.findall(r'<meta[^>]+name="description"', html, re.I))
    if n != 1:
        problems.append(f"meta description count {n} != 1")
    n = len(re.findall(r'id="enquiry-form"', html))
    if n > 1:
        problems.append(f"enquiry form count {n} > 1")
    if re.search(r"""href=["']tel:(?!\+91)""", html):
        problems.append("bare tel: link (must be tel:+91...)")

    breadcrumbs = 0
    faq_blocks = 0
    for block in LD_RE.findall(html):
        try:
            data = json.loads(block)
        except json.JSONDecodeError:
            problems.append("invalid JSON-LD block")
            continue
        for node in _walk(data):
            t = node.get("@type")
            if t == "BreadcrumbList":
                breadcrumbs += 1
            if t == "FAQPage":
                faq_blocks += 1
                qs = [q for q in node.get("mainEntity", []) if q.get("@type") == "Question"]
                if len(qs) < 3:
                    problems.append(f"FAQPage has {len(qs)} questions (< 3)")
                if strict_faq_phone:
                    for q in qs:
                        if PHONE in q.get("acceptedAnswer", {}).get("text", ""):
                            problems.append("phone number inside FAQ answer")
                            break
    if faq_blocks > 1:
        problems.append(f"FAQPage blocks {faq_blocks} > 1")
    if breadcrumbs > 1:
        problems.append(f"BreadcrumbList count {breadcrumbs} > 1")
    return problems


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", required=True)
    ap.add_argument("--strict-faq-phone", action="store_true")
    ap.add_argument("paths", nargs="+")
    args = ap.parse_args()
    failed = 0
    for p in args.paths:
        url = args.base.rstrip("/") + "/" + p.lstrip("/")
        try:
            with urllib.request.urlopen(url, timeout=20) as r:
                status, html = r.status, r.read().decode("utf-8", "replace")
        except Exception as e:  # network/HTTP error is a failure, report and continue
            print(f"FAIL {url}: {e}")
            failed += 1
            continue
        problems = check_html(html, args.strict_faq_phone)
        if status != 200:
            problems.insert(0, f"HTTP {status}")
        if re.search(r"(Fatal error|Parse error|Warning:|Notice:)", html):
            problems.append("PHP error text in output")
        print(("FAIL " if problems else "OK   ") + url + ("  " + "; ".join(problems) if problems else ""))
        failed += bool(problems)
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
