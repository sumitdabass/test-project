#!/usr/bin/env python3
"""Capture the live SEO baseline for every news article and the /news/ index."""
import json, re, sys, urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def grab(html):
    body = html.split("</head>")[-1]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    meta = {m[0]: m[1] for m in re.findall(r'<meta (?:property|name)="((?:og|twitter|article):[^"]+)" content="(.*?)"', html)}
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]
    return {
        "title": g(r"<title>(.*?)</title>"),
        "description": g(r'<meta name="description" content="(.*?)"'),
        "canonical": g(r'<link rel="canonical" href="(.*?)"'),
        "robots": g(r'<meta name="robots" content="(.*?)"'),
        "meta": meta,
        "h1": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h1[^>]*>(.*?)</h1>", body, re.S)],
        "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
        "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
        "jsonld": ld,
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    idx = urllib.request.urlopen(f"{base}/news/", timeout=30).read().decode("utf-8", "replace")
    slugs = sorted(set(re.findall(r'href="/news/([a-z0-9-]+)\.php"', idx)))
    data = {"index": grab(idx)}
    for s in slugs:
        data[s] = grab(urllib.request.urlopen(f"{base}/news/{s}.php", timeout=30).read().decode("utf-8", "replace"))
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "news_baseline.json").write_text(json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data) - 1, "articles + index captured")
