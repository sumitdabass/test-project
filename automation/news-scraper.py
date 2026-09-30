#!/usr/bin/env python3
"""Daily IPU news scraper.

Sources (official IPU only — no third-party editorial):
  1. https://ipu.ac.in/              — university homepage notifications
  2. https://ipu.admissions.nic.in/  — admission portal announcements

Flow:
  1. Fetch each source's HTML.
  2. Extract candidate links whose anchor text or URL contains admission/
     counselling/CET/result/notification keywords.
  3. Skip items whose source link is in content/news/.seen-links.json, or
     whose target slug already exists under content/news/.
  4. For each new item, call OpenAI GPT-4o mini with the system prompt from
     deployment/prompts/news-rewrite-system.md, asking for JSON matching our
     news post schema.
  5. If OpenAI returns `{"skip": true}`, skip the item.
  6. Otherwise write the MD file to content/news/<slug>.md.

Env vars (required in GH Actions; loaded from .env locally):
  GEMINI_API_KEY  — Google AI Studio / Gemini API key (AIza...)

Tuning knobs (defaults are sensible):
  NEWS_MAX_ITEMS_PER_RUN = 1         # hard cap per run — per-user choice (one post per day)
  NEWS_MODEL = "gemini-flash-latest" # see https://ai.google.dev/gemini-api/docs/models
"""
from __future__ import annotations

import html
import json
import os
import re
import sys
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

HERE = Path(__file__).resolve().parent
REPO = HERE.parent
CONTENT_DIR = REPO / "content" / "news"
PROMPT_PATH = REPO / "deployment" / "prompts" / "news-rewrite-system.md"
# Source links already turned into a post (or rejected by the model). Lives in
# content/news/ so the workflow's `git add content/news/` commits it.
SEEN_PATH = CONTENT_DIR / ".seen-links.json"

SOURCES = [
    {"name": "ipu.admissions.nic.in/current-events",      "url": "https://ipu.admissions.nic.in/current-events/"},
    {"name": "ipu.admissions.nic.in/schedule-notices",    "url": "https://ipu.admissions.nic.in/schedule-notices/"},
    {"name": "ipu.admissions.nic.in/news-events",         "url": "https://ipu.admissions.nic.in/news-events/"},
    {"name": "ipu.admissions.nic.in/cut-off-2026-27",     "url": "https://ipu.admissions.nic.in/cut-off-2026-27/"},
    {"name": "ipu.admissions.nic.in/intake-2026-27",      "url": "https://ipu.admissions.nic.in/intake-2026-27/"},
    {"name": "ipu.admissions.nic.in/create-cut-off-2025", "url": "https://ipu.admissions.nic.in/create-cut-off-2025-2026/"},
    {"name": "ipu.admissions.nic.in/seat-intake-2025",    "url": "https://ipu.admissions.nic.in/seat-intake-2025-2026/"},
]

# Only consider links whose URL OR anchor text matches at least one of these
# (case-insensitive). Keeps us from feeding every "Contact Us" or "Privacy" link
# into OpenAI.
KEYWORDS = [
    "admission", "counselling", "counseling", "cet",
    "result", "notification", "notice", "allotment",
    "merit", "cutoff", "cut-off", "schedule", "registration",
    "seat", "vacant", "round", "intake", "extension",
    "extended", "opportunity", "last date", "session",
]

# Anchor texts that look like navigation / chrome, never content.
NAV_BLOCKLIST = {
    "session 2026-27", "intake 2026-27", "cut off 2026-27", "refund 2025",
    "intake 2025-26/mq seats", "cutoff 2025-26", "mq 2025-26", "contact us",
    "home", "about", "privacy policy", "terms", "apply online",
}

MAX_ITEMS = int(os.environ.get("NEWS_MAX_ITEMS_PER_RUN", "1"))
MODEL = os.environ.get("NEWS_MODEL", "gemini-flash-latest")

LINK_RE = re.compile(r'<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>', re.I | re.S)
TAG_RE = re.compile(r"<[^>]+>")


def slugify(title: str) -> str:
    s = unicodedata.normalize("NFKD", title).encode("ascii", "ignore").decode()
    s = s.lower()
    s = re.sub(r"[^\w\s-]", " ", s)
    s = re.sub(r"\s+", "-", s.strip())
    s = re.sub(r"-+", "-", s)
    return s.strip("-")[:80]  # cap to avoid ridiculous filenames


def fetch_html(url: str) -> str:
    req = urllib.request.Request(
        url,
        headers={
            "User-Agent": "Mozilla/5.0 (compatible; IPU-news-bot/1.0)",
            "Accept": "text/html,application/xhtml+xml",
        },
    )
    with urllib.request.urlopen(req, timeout=30) as resp:
        return resp.read().decode("utf-8", errors="replace")


def extract_links(html_text: str, base_url: str) -> list[dict]:
    """Pull all <a href="..."> anchors. Resolve relative URLs. Strip inner HTML
    from anchor text. Return list of dicts {link, text}."""
    out = []
    for href, inner in LINK_RE.findall(html_text):
        text = TAG_RE.sub(" ", inner)
        text = html.unescape(text)
        text = re.sub(r"\s+", " ", text).strip()
        if not text or len(text) < 10:
            continue
        link = urllib.parse.urljoin(base_url, href.strip())
        # normalize fragment/trailing slash
        link = link.split("#")[0]
        out.append({"link": link, "text": text})
    return out


def keyword_match(item: dict) -> bool:
    text_lower = item["text"].lower()
    if text_lower in NAV_BLOCKLIST:
        return False
    # filter short/generic social/accessibility chrome
    if len(item["text"]) < 20:
        return False
    if any(s in text_lower for s in ("share on ", "screen reader", "skip to", "click here")):
        return False
    # PDF / government CDN = real notification content
    link_lower = item["link"].lower()
    if ".pdf" in link_lower or "cdnbbsr.s3waas" in link_lower:
        return True
    # non-PDF: check anchor TEXT only (url would false-positive on "admission" in the domain name)
    return any(k in text_lower for k in KEYWORDS)


def existing_slugs() -> set[str]:
    if not CONTENT_DIR.exists():
        return set()
    return {p.stem for p in CONTENT_DIR.glob("*.md")}


def normalise_link(url: str) -> str:
    return url.split("#", 1)[0].strip().rstrip("/").lower()


def load_seen_links() -> set[str] | None:
    """None means no state file yet (first run after dedup was introduced)."""
    if not SEEN_PATH.exists():
        return None
    seen = set(json.loads(SEEN_PATH.read_text(encoding="utf-8")))
    # Posts written since source_url was added also carry their link.
    for md in CONTENT_DIR.glob("*.md"):
        head = md.read_text(encoding="utf-8").split("\n---", 1)[0]
        try:
            src = json.loads(head).get("source_url")
        except json.JSONDecodeError:
            continue
        if src:
            seen.add(normalise_link(src))
    return seen


def save_seen_links(seen: set[str]) -> None:
    SEEN_PATH.parent.mkdir(parents=True, exist_ok=True)
    SEEN_PATH.write_text(json.dumps(sorted(seen), indent=2) + "\n", encoding="utf-8")


def call_llm(system_prompt: str, user_message: str) -> dict:
    """Call Gemini via AI Studio REST API. Returns the parsed JSON object the
    model produced."""
    key = os.environ.get("GEMINI_API_KEY")
    if not key:
        raise RuntimeError("GEMINI_API_KEY not set")
    body = {
        "systemInstruction": {"parts": [{"text": system_prompt}]},
        "contents": [{"role": "user", "parts": [{"text": user_message}]}],
        "generationConfig": {
            "responseMimeType": "application/json",
            "maxOutputTokens": 8192,
            "temperature": 0.4,
        },
    }
    url = f"https://generativelanguage.googleapis.com/v1beta/models/{MODEL}:generateContent?key={key}"
    req = urllib.request.Request(
        url,
        data=json.dumps(body).encode("utf-8"),
        headers={"Content-Type": "application/json"},
    )
    try:
        with urllib.request.urlopen(req, timeout=60) as resp:
            data = json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        raise RuntimeError(f"Gemini HTTP {e.code}: {e.read().decode('utf-8', errors='replace')[:500]}")

    candidates = data.get("candidates") or []
    if not candidates:
        raise RuntimeError(f"Gemini returned no candidates: {json.dumps(data)[:300]}")
    parts = candidates[0].get("content", {}).get("parts") or []
    if not parts:
        finish = candidates[0].get("finishReason", "?")
        raise RuntimeError(f"Gemini returned empty content (finishReason={finish}): {json.dumps(data)[:300]}")
    text = parts[0].get("text", "").strip()
    if not text:
        raise RuntimeError(f"Gemini returned empty text: {json.dumps(data)[:300]}")
    return json.loads(text)


def write_post(fm: dict, body_md: str, image: str, source_url: str) -> Path:
    slug = fm["slug"]
    out = CONTENT_DIR / f"{slug}.md"
    fm_to_write = {k: v for k, v in fm.items() if k != "body_md"}
    fm_to_write["image"] = image
    fm_to_write["source_url"] = source_url
    payload = json.dumps(fm_to_write, indent=2, ensure_ascii=False)
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(f"{payload}\n---\n{body_md.strip()}\n", encoding="utf-8")
    return out


def gather_candidates() -> list[dict]:
    """Fetch all sources, extract keyword-matching links, dedup by URL."""
    all_items: list[dict] = []
    for src in SOURCES:
        try:
            html_text = fetch_html(src["url"])
        except Exception as e:
            print(f"WARN: {src['name']} unreachable: {e}", file=sys.stderr)
            continue
        links = extract_links(html_text, src["url"])
        matched = [l for l in links if keyword_match(l)]
        print(f"{src['name']}: {len(links)} links total, {len(matched)} keyword-matched")
        for m in matched:
            m["source"] = src["name"]
            all_items.append(m)

    # dedup by URL
    seen: set[str] = set()
    unique: list[dict] = []
    for it in all_items:
        if it["link"] not in seen:
            seen.add(it["link"])
            unique.append(it)
    print(f"total unique candidates across sources: {len(unique)}")
    return unique


def main() -> int:
    today = datetime.now(timezone.utc).strftime("%Y-%m-%d")
    system_prompt = PROMPT_PATH.read_text(encoding="utf-8")
    system_prompt += f"\n\nToday's date is {today}."

    seen_slugs = existing_slugs()
    print(f"starting: {len(seen_slugs)} existing posts in {CONTENT_DIR}")

    candidates = gather_candidates()

    # The model rewrites each notice under a new slug, so slug checks alone let
    # the same notice through day after day. Dedup on the source link instead.
    seen_links = load_seen_links()
    if seen_links is None:
        if not candidates:
            print("no candidates and no seen-links state; not baselining on an empty fetch")
            return 0
        # Older posts never recorded their source link, so treat everything
        # currently listed as already published and start fresh from tomorrow.
        save_seen_links({normalise_link(c["link"]) for c in candidates})
        print(f"baseline: marked {len(candidates)} current source links as seen; no posts this run")
        return 0

    written: list[Path] = []
    errors: list[tuple[str, str]] = []

    for item in candidates:
        if len(written) >= MAX_ITEMS:
            print(f"hit MAX_ITEMS ({MAX_ITEMS}); stopping")
            break

        link_key = normalise_link(item["link"])
        if link_key in seen_links:
            continue

        tentative = slugify(item["text"])
        if not tentative:
            continue
        if tentative in seen_slugs or any(tentative in s or s in tentative for s in seen_slugs):
            print(f"  skip (slug collision): {item['text'][:80]}")
            continue

        user_message = (
            f"Source: {item['source']}\n"
            f"Link: {item['link']}\n"
            f"Headline / link text: {item['text']}\n"
        )
        print(f"  rewriting: {item['text'][:80]}")
        try:
            rewritten = call_llm(system_prompt, user_message)
        except Exception as e:
            print(f"  ERROR: {e}", file=sys.stderr)
            errors.append((item["text"], str(e)))
            continue

        if rewritten.get("skip"):
            print(f"  skip (model): {rewritten.get('reason', '')}")
            seen_links.add(link_key)
            save_seen_links(seen_links)
            continue

        missing = [k for k in ("title", "slug", "date", "category", "tldr", "body_md") if k not in rewritten]
        if missing:
            print(f"  ERROR: model output missing fields: {missing}", file=sys.stderr)
            errors.append((item["text"], f"missing fields {missing}"))
            continue

        category_slug = rewritten.get("category", "General").lower()
        valid_cats = {"counselling", "cet", "admissions", "results", "general"}
        if category_slug not in valid_cats:
            category_slug = "general"
        image = f"assets/images/news/{category_slug}.jpg"

        out_path = write_post(rewritten, rewritten["body_md"], image, item["link"])
        written.append(out_path)
        seen_slugs.add(rewritten["slug"])
        seen_links.add(link_key)
        save_seen_links(seen_links)
        print(f"  wrote: {out_path.relative_to(REPO)}")

    print(f"\nRun complete. Wrote {len(written)} posts, {len(errors)} errors.")

    summary = os.environ.get("GITHUB_STEP_SUMMARY")
    if summary:
        with open(summary, "a") as f:
            f.write(f"### IPU news scraper\n\nWrote **{len(written)}** new post(s).\n\n")
            for p in written:
                f.write(f"- `{p.relative_to(REPO)}`\n")
            if errors:
                f.write(f"\n**{len(errors)} errors:**\n")
                for t, e in errors:
                    f.write(f"- {t[:80]} — `{e[:200]}`\n")

    # exit success even on some errors, as long as at least one post wrote or
    # everything hit the model-skip gate. Fail only if ALL candidates errored.
    return 1 if (errors and not written) else 0


if __name__ == "__main__":
    sys.exit(main())
