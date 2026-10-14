#!/usr/bin/env python3
"""Capture the live baseline for every legacy-body page (in-scope list for the phase-4 codemod)."""
import json, re, sys, urllib.request
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
EXCLUDED = {"b-tech-colleges-under-IP-university.php", "ipu-admission-guide.php", "ipu-b-tech-pillar.php",
            "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php", "BPIT.php"}


FIXTURE = ROOT / "tests" / "fixtures" / "legacy_baseline.json"


def legacy_pages():
    """In-scope page names. Once the baseline fixture exists it is the source of truth (converted pages no longer
    contain 'blog-wrapper'); before that, pages still carrying the old wrapper."""
    if FIXTURE.exists():
        return sorted(json.loads(FIXTURE.read_text(encoding="utf-8")))
    names = []
    for f in sorted(WEB.glob("*.php")):
        if f.name in EXCLUDED:
            continue
        if "blog-wrapper" in f.read_text(encoding="utf-8", errors="replace"):
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


_MOVED = [
    r'<aside class="ipu-enquiry">.*?</aside>',                          # sidebar form (moves)
    r'<nav aria-label="breadcrumb"[^>]*>.*?</nav>',                     # old body breadcrumb (moves to hero)
    r'<nav class="ipu-page-hero__crumbs".*?</nav>',                     # hero breadcrumb
    r'<p(?:\s[^>]*)?>(?:(?!</p>).)*?Last Updated:(?:(?!</p>).)*?</p>',        # '<p' exactly (not <path>/<picture>)        # Last Updated line (moves to hero)
    r'<div class="ipu-cta-card">.*?</a>\s*</div>',                     # CTA card (new)
    r'<a href="#enquiry-form" class="ipu-page-hero__enquire"[^>]*>.*?</a>',   # hero Enquire (new)
]
_UI = {"Enquire", "Need Admission Guidance?", "Talk to our expert counsellors for free guidance on IPU admissions", "Call: 9899991342"}


def core_view(html):
    """(ordered visible texts, ordered hrefs/srcs) of the page body with moved/added blocks removed."""
    body = html.split("</head>")[-1]
    for pat in _MOVED:
        body = re.sub(pat, "", body, flags=re.S)
    texts = [t for t in visible_texts("<body>" + body) if t not in _UI]
    links = re.findall(r'(?:href|src)="([^"#][^"]*)"', body)
    return texts, links


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
        "core": dict(zip(("texts", "links"), core_view(html))),
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    data = {p: grab(urllib.request.urlopen(f"{base}/{p}", timeout=30).read().decode("utf-8", "replace")) for p in legacy_pages()}
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "legacy_baseline.json").write_text(json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data), "pages captured")
