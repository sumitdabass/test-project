#!/usr/bin/env python3
"""Capture per-page SEO baseline for every college-template page from the live site."""
import json, re, subprocess, sys, urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"


def template_pages():
    out = subprocess.run(["grep", "-l", "college-page-template", *map(str, sorted(WEB.glob("*.php")))],
                         capture_output=True, text=True).stdout.split()
    return [Path(p).name for p in out]


def grab(html):
    body = html.split("</head>")[-1]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]
    faq = []
    for block in ld:
        if block.get("@type") == "FAQPage":
            faq = [(q["name"], q["acceptedAnswer"]["text"]) for q in block["mainEntity"]]
    return {
        "title": g(r"<title>(.*?)</title>"),
        "description": g(r'<meta name="description" content="(.*?)"'),
        "canonical": g(r'<link rel="canonical" href="(.*?)"'),
        "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
        "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
        "faq": faq,
        "jsonld_types": sorted(str(b.get("@type")) for b in ld),
        "college_ld": next(({k: b.get(k) for k in ("name", "alternateName", "url")} for b in ld
                            if b.get("@type") == "CollegeOrUniversity"), None),
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    data = {p: grab(urllib.request.urlopen(f"{base}/{p}", timeout=30).read().decode("utf-8", "replace"))
            for p in template_pages()}
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "college_baseline.json").write_text(
        json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data), "pages captured")
