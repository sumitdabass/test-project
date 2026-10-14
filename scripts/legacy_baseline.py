#!/usr/bin/env python3
"""Capture the live baseline for every legacy-body page (in-scope list for the phase-4 codemod)."""
import json, re, sys, urllib.request
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
EXCLUDED = {"b-tech-colleges-under-IP-university.php", "ipu-admission-guide.php", "ipu-b-tech-pillar.php",
            "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php", "BPIT.php"}


def legacy_pages():
    """Pages still carrying the old wrapper (or already converted: marker 'ipu-body' + was in baseline)."""
    names = []
    for f in sorted(WEB.glob("*.php")):
        if f.name in EXCLUDED:
            continue
        s = f.read_text(encoding="utf-8", errors="replace")
        if "blog-wrapper" in s:
            names.append(f.name)
    return names


class _Texts(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.skip = 0; self.in_body = False; self.out = []

    def handle_starttag(self, tag, attrs):
        if tag == "body": self.in_body = True
        if tag in ("script", "style"): self.skip += 1

    def handle_endtag(self, tag):
        if tag in ("script", "style"): self.skip -= 1

    def handle_data(self, data):
        if self.in_body and not self.skip:
            t = re.sub(r"\s+", " ", data.replace("\U0001F449", "")).strip()
            if t: self.out.append(t)


def visible_texts(html):
    p = _Texts(); p.feed(html); return p.out


def grab(html):
    body = html.split("</head>")[-1]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    return {
        "title": g(r"<title>(.*?)</title>"),
        "description": g(r'<meta name="description" content="(.*?)"'),
        "canonical": g(r'<link rel="canonical" href="(.*?)"'),
        "robots": g(r'<meta name="robots" content="(.*?)"'),
        "h1": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h1[^>]*>(.*?)</h1>", body, re.S)],
        "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
        "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
        "jsonld": [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)],
        "texts": visible_texts(html),
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    data = {p: grab(urllib.request.urlopen(f"{base}/{p}", timeout=30).read().decode("utf-8", "replace")) for p in legacy_pages()}
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "legacy_baseline.json").write_text(json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data), "pages captured")
