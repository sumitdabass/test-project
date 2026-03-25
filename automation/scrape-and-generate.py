#!/usr/bin/env python3
"""
Scrape IPU admission notifications and generate SEO-optimized blog posts
using the Anthropic Claude API.
"""

import json
import os
import re
import sys
import hashlib
from datetime import datetime
from pathlib import Path

import requests
from bs4 import BeautifulSoup
import anthropic

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------
BASE_DIR = Path(__file__).resolve().parent
PROJECT_ROOT = BASE_DIR.parent
PUBLISHED_JSON = BASE_DIR / "published.json"
TEMPLATE_PATH = BASE_DIR / "templates" / "blog-post-template.php"
OUTPUT_DIR = PROJECT_ROOT / "website_download" / "blog"
NEW_FILES_LIST = BASE_DIR / "new-files.json"

ADMISSION_URL = "https://ipu.admissions.nic.in"
IPU_NEWS_URL = "https://ipu.ac.in"

CURRENT_YEAR = datetime.now().year
PHONE_NUMBER = "9899991342"
SITE_DOMAIN = "https://ipu.co.in"

# Internal pages that Claude can reference for internal linking
INTERNAL_PAGES = [
    {"url": "ipu-admission-guide.php", "title": "IPU Admission Guide"},
    {"url": "mba-admission-ip-university.php", "title": "MBA Admission in IP University"},
    {"url": "IPU-B-Tech-admission-2026.php", "title": "IPU B.Tech Admission"},
    {"url": "IPU-Law-Admission-2026.php", "title": "IPU Law Admission"},
    {"url": "ipu-bba-admission.php", "title": "BBA Admission in IP University"},
    {"url": "guide-to-bjmc-colleges-under-ip-university.php", "title": "BJMC Colleges Under IPU"},
    {"url": "best-btech-colleges-ipu.php", "title": "Best B.Tech Colleges Under IPU"},
    {"url": "ipu-choice-filling-strategy.php", "title": "IPU Choice Filling Strategy"},
    {"url": "GGSIPU-counselling-for-B-Tech-admission.php", "title": "GGSIPU Counselling for B.Tech"},
    {"url": "economics-admission-ip-university.php", "title": "Economics Admission in IPU"},
    {"url": "ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php", "title": "IPU CET Exam Date & Admit Card"},
    {"url": "IP-University-management-quota-admission-eligibility-criteria.php", "title": "Management Quota Admission in IPU"},
]

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def load_published():
    """Load the list of already-published notification IDs."""
    if PUBLISHED_JSON.exists():
        with open(PUBLISHED_JSON, "r", encoding="utf-8") as f:
            return json.load(f)
    return []


def save_published(data):
    """Persist the published list."""
    with open(PUBLISHED_JSON, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2, ensure_ascii=False)


def notification_id(title: str, url: str) -> str:
    """Create a deterministic ID for a notification."""
    raw = f"{title.strip().lower()}|{url.strip().lower()}"
    return hashlib.sha256(raw.encode()).hexdigest()[:16]


def slugify(text: str) -> str:
    """Convert text to a URL-friendly slug."""
    text = text.lower().strip()
    text = re.sub(r"[^a-z0-9\s-]", "", text)
    text = re.sub(r"[\s_]+", "-", text)
    text = re.sub(r"-+", "-", text)
    return text.strip("-")[:80]


# ---------------------------------------------------------------------------
# Scraping
# ---------------------------------------------------------------------------

def scrape_admissions_nic():
    """
    Scrape https://ipu.admissions.nic.in for the latest admission notifications.
    Returns a list of dicts: {title, url, date, source}.
    """
    notifications = []
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0.0.0 Safari/537.36"
        )
    }

    try:
        resp = requests.get(ADMISSION_URL, headers=headers, timeout=30)
        resp.raise_for_status()
        soup = BeautifulSoup(resp.text, "html.parser")

        # The site typically lists notifications in table rows or list items
        # with links. We try multiple selectors.
        link_elements = []

        # Try table-based layout
        for row in soup.select("table tr"):
            links = row.find_all("a", href=True)
            for a in links:
                link_elements.append(a)

        # Try list-based layout
        for li in soup.select("ul li a[href]"):
            link_elements.append(li)

        # Try div-based notification panels
        for a in soup.select("div.notification a[href], div.content a[href], div.list a[href]"):
            link_elements.append(a)

        # Try marquee or scrolling elements
        for a in soup.select("marquee a[href], .marquee a[href]"):
            link_elements.append(a)

        # General fallback: any link that looks like a notification
        if not link_elements:
            for a in soup.find_all("a", href=True):
                href = a["href"].lower()
                text = a.get_text(strip=True).lower()
                if any(kw in href or kw in text for kw in [
                    "admission", "notice", "circular", "notification",
                    "counselling", "counseling", "schedule", "result"
                ]):
                    link_elements.append(a)

        seen_urls = set()
        for a in link_elements:
            title = a.get_text(strip=True)
            href = a["href"]

            if not title or len(title) < 10:
                continue

            # Build absolute URL
            if href.startswith("/"):
                href = ADMISSION_URL.rstrip("/") + href
            elif not href.startswith("http"):
                href = ADMISSION_URL.rstrip("/") + "/" + href

            if href in seen_urls:
                continue
            seen_urls.add(href)

            # Try to extract date from surrounding elements
            date_str = ""
            parent = a.find_parent("tr") or a.find_parent("li") or a.find_parent("div")
            if parent:
                text = parent.get_text()
                date_match = re.search(
                    r"(\d{1,2}[./-]\d{1,2}[./-]\d{2,4})", text
                )
                if date_match:
                    date_str = date_match.group(1)

            if not date_str:
                date_str = datetime.now().strftime("%d-%m-%Y")

            notifications.append({
                "title": title,
                "url": href,
                "date": date_str,
                "source": "ipu.admissions.nic.in",
            })

        print(f"[Scraper] Found {len(notifications)} notifications from admissions.nic.in")

    except requests.RequestException as e:
        print(f"[Scraper] Error fetching {ADMISSION_URL}: {e}", file=sys.stderr)

    return notifications


def scrape_ipu_ac():
    """
    Scrape https://ipu.ac.in for news and circulars.
    Returns a list of dicts: {title, url, date, source}.
    """
    notifications = []
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0.0.0 Safari/537.36"
        )
    }

    try:
        resp = requests.get(IPU_NEWS_URL, headers=headers, timeout=30)
        resp.raise_for_status()
        soup = BeautifulSoup(resp.text, "html.parser")

        link_elements = []

        # Try common patterns for news/circulars on university sites
        for selector in [
            "div.news a[href]",
            "div.circular a[href]",
            "div.notice a[href]",
            "div.latest a[href]",
            "#news a[href]",
            "#circulars a[href]",
            ".news-section a[href]",
            ".notification-list a[href]",
            "marquee a[href]",
            ".marquee a[href]",
        ]:
            for a in soup.select(selector):
                link_elements.append(a)

        # Fallback: scan all links for relevant keywords
        if not link_elements:
            for a in soup.find_all("a", href=True):
                href = a["href"].lower()
                text = a.get_text(strip=True).lower()
                if any(kw in href or kw in text for kw in [
                    "circular", "notice", "news", "admission",
                    "result", "exam", "schedule", "counselling"
                ]):
                    link_elements.append(a)

        seen_urls = set()
        for a in link_elements:
            title = a.get_text(strip=True)
            href = a["href"]

            if not title or len(title) < 10:
                continue

            # Build absolute URL
            if href.startswith("/"):
                href = IPU_NEWS_URL.rstrip("/") + href
            elif not href.startswith("http"):
                href = IPU_NEWS_URL.rstrip("/") + "/" + href

            if href in seen_urls:
                continue
            seen_urls.add(href)

            # Try to extract date
            date_str = ""
            parent = a.find_parent("tr") or a.find_parent("li") or a.find_parent("div")
            if parent:
                date_match = re.search(
                    r"(\d{1,2}[./-]\d{1,2}[./-]\d{2,4})", parent.get_text()
                )
                if date_match:
                    date_str = date_match.group(1)

            if not date_str:
                date_str = datetime.now().strftime("%d-%m-%Y")

            notifications.append({
                "title": title,
                "url": href,
                "date": date_str,
                "source": "ipu.ac.in",
            })

        print(f"[Scraper] Found {len(notifications)} notifications from ipu.ac.in")

    except requests.RequestException as e:
        print(f"[Scraper] Error fetching {IPU_NEWS_URL}: {e}", file=sys.stderr)

    return notifications


# ---------------------------------------------------------------------------
# Content Generation with Claude API
# ---------------------------------------------------------------------------

def generate_blog_content(notification: dict) -> dict | None:
    """
    Use the Anthropic Claude API to generate a full blog post for a notification.
    Returns a dict with keys: seo_title, meta_desc, h1, content_html, faqs, internal_links, category.
    """
    api_key = os.environ.get("ANTHROPIC_API_KEY")
    if not api_key:
        print("[Generator] ANTHROPIC_API_KEY not set. Skipping generation.", file=sys.stderr)
        return None

    client = anthropic.Anthropic(api_key=api_key)

    internal_pages_str = "\n".join(
        f"- {p['title']}: {SITE_DOMAIN}/{p['url']}" for p in INTERNAL_PAGES
    )

    prompt = f"""You are an expert SEO content writer for ipu.co.in, a website that helps students
with IP University (GGSIPU) admissions. Write a blog post about the following notification.

NOTIFICATION TITLE: {notification['title']}
NOTIFICATION SOURCE: {notification['source']}
NOTIFICATION URL: {notification['url']}
DATE: {notification['date']}

INSTRUCTIONS:
1. Write factual, student-friendly content about this notification.
2. Include practical guidance for students: what they need to do, deadlines, documents required, etc.
3. Mention that students can call {PHONE_NUMBER} for FREE counselling and admission guidance.
4. The current year is {CURRENT_YEAR}.

REQUIRED OUTPUT (respond in valid JSON only, no markdown code fences):
{{
    "seo_title": "SEO title, 55-60 characters, must include 'IPU' and '{CURRENT_YEAR}'",
    "meta_desc": "Meta description, exactly 150 characters, must include phone {PHONE_NUMBER}",
    "h1": "H1 heading for the page, can be longer and more descriptive than SEO title",
    "category": "One of: B.Tech, MBA, Law, BBA, BJMC, CET, Admissions, Results, Counselling, General",
    "content_html": "800-1200 word article in HTML format. Use <h2> and <h3> for headings, <ul>/<li> for bullet points, <p> for paragraphs, <strong> for emphasis. Include practical info, deadlines, eligibility, process steps. Add a section about free counselling with phone {PHONE_NUMBER}.",
    "faqs": [
        {{"question": "FAQ question 1", "answer": "Detailed answer 1"}},
        {{"question": "FAQ question 2", "answer": "Detailed answer 2"}},
        {{"question": "FAQ question 3", "answer": "Detailed answer 3"}}
    ],
    "internal_links": ["url1.php", "url2.php", "url3.php"]
}}

AVAILABLE INTERNAL PAGES FOR LINKING:
{internal_pages_str}

Pick 3 of the above internal pages that are most relevant to this notification.

IMPORTANT:
- Output ONLY valid JSON, no other text.
- The content_html should be 800-1200 words of actual article content.
- Make the content genuinely helpful and informative for students.
- FAQs should have 3-5 practical questions students would ask.
"""

    try:
        message = client.messages.create(
            model="claude-sonnet-4-20250514",
            max_tokens=4096,
            messages=[{"role": "user", "content": prompt}],
        )

        response_text = message.content[0].text.strip()

        # Clean up response in case Claude wraps it in code fences
        if response_text.startswith("```"):
            response_text = re.sub(r"^```(?:json)?\s*", "", response_text)
            response_text = re.sub(r"\s*```$", "", response_text)

        data = json.loads(response_text)
        return data

    except json.JSONDecodeError as e:
        print(f"[Generator] Failed to parse Claude response as JSON: {e}", file=sys.stderr)
        print(f"[Generator] Raw response: {response_text[:500]}", file=sys.stderr)
        return None
    except anthropic.APIError as e:
        print(f"[Generator] Anthropic API error: {e}", file=sys.stderr)
        return None
    except Exception as e:
        print(f"[Generator] Unexpected error: {e}", file=sys.stderr)
        return None


# ---------------------------------------------------------------------------
# PHP File Generation
# ---------------------------------------------------------------------------

def build_faq_json_ld(faqs: list) -> str:
    """Build FAQ schema JSON-LD."""
    faq_entities = []
    for faq in faqs:
        faq_entities.append({
            "@type": "Question",
            "name": faq["question"],
            "acceptedAnswer": {
                "@type": "Answer",
                "text": faq["answer"],
            },
        })

    schema = {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": faq_entities,
    }
    return json.dumps(schema, indent=2, ensure_ascii=False)


def build_breadcrumb_json_ld(title: str, slug: str) -> str:
    """Build BreadcrumbList schema JSON-LD."""
    schema = {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Home",
                "item": SITE_DOMAIN,
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "Blog",
                "item": f"{SITE_DOMAIN}/blog",
            },
            {
                "@type": "ListItem",
                "position": 3,
                "name": title,
                "item": f"{SITE_DOMAIN}/blog/{slug}.php",
            },
        ],
    }
    return json.dumps(schema, indent=2, ensure_ascii=False)


def generate_php_file(notification: dict, blog_data: dict, slug: str) -> str | None:
    """
    Read the PHP template and fill in placeholders.
    Returns the filled template string.
    """
    if not TEMPLATE_PATH.exists():
        print(f"[Generator] Template not found at {TEMPLATE_PATH}", file=sys.stderr)
        return None

    with open(TEMPLATE_PATH, "r", encoding="utf-8") as f:
        template = f.read()

    today = datetime.now().strftime("%B %d, %Y")
    iso_date = datetime.now().strftime("%Y-%m-%d")
    canonical_url = f"{SITE_DOMAIN}/blog/{slug}.php"

    faq_json_ld = build_faq_json_ld(blog_data.get("faqs", []))
    breadcrumb_json_ld = build_breadcrumb_json_ld(blog_data["seo_title"], slug)

    # Build FAQ HTML
    faq_html_parts = []
    for faq in blog_data.get("faqs", []):
        faq_html_parts.append(
            f'<div class="faq-item mb-3">\n'
            f'  <h3 class="faq-question h5">{faq["question"]}</h3>\n'
            f'  <p class="faq-answer">{faq["answer"]}</p>\n'
            f'</div>'
        )
    faq_html = "\n".join(faq_html_parts)

    # Build related pages HTML
    related_html_parts = []
    for link_url in blog_data.get("internal_links", []):
        # Find the matching internal page
        for page in INTERNAL_PAGES:
            if page["url"] == link_url:
                related_html_parts.append(
                    f'<a href="/{page["url"]}" class="list-group-item list-group-item-action">'
                    f'{page["title"]}</a>'
                )
                break

    related_html = "\n".join(related_html_parts) if related_html_parts else ""

    replacements = {
        "{{TITLE}}": blog_data["seo_title"],
        "{{META_DESC}}": blog_data["meta_desc"],
        "{{CANONICAL_URL}}": canonical_url,
        "{{H1}}": blog_data["h1"],
        "{{CONTENT}}": blog_data["content_html"],
        "{{FAQ_HTML}}": faq_html,
        "{{RELATED_PAGES}}": related_html,
        "{{DATE}}": today,
        "{{ISO_DATE}}": iso_date,
        "{{SLUG}}": slug,
        "{{FAQ_JSON_LD}}": faq_json_ld,
        "{{BREADCRUMB_JSON_LD}}": breadcrumb_json_ld,
        "{{OG_TITLE}}": blog_data["seo_title"],
        "{{OG_DESC}}": blog_data["meta_desc"],
        "{{OG_IMAGE}}": f"{SITE_DOMAIN}/assets/images/blog-default-og.jpg",
        "{{CATEGORY}}": blog_data.get("category", "Admissions"),
        "{{PHONE}}": PHONE_NUMBER,
        "{{SITE_DOMAIN}}": SITE_DOMAIN,
        "{{YEAR}}": str(CURRENT_YEAR),
    }

    result = template
    for placeholder, value in replacements.items():
        result = result.replace(placeholder, value)

    return result


# ---------------------------------------------------------------------------
# Main Pipeline
# ---------------------------------------------------------------------------

def main():
    print("=" * 60)
    print(f"IPU Blog Auto-Generator - {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("=" * 60)

    # Load already-published entries
    published = load_published()
    published_ids = {entry["id"] for entry in published}
    print(f"[Info] Already published: {len(published_ids)} entries")

    # Scrape both sources
    all_notifications = []
    all_notifications.extend(scrape_admissions_nic())
    all_notifications.extend(scrape_ipu_ac())

    print(f"[Info] Total notifications scraped: {len(all_notifications)}")

    # Filter to only new notifications
    new_notifications = []
    for notif in all_notifications:
        nid = notification_id(notif["title"], notif["url"])
        if nid not in published_ids:
            notif["id"] = nid
            new_notifications.append(notif)

    print(f"[Info] New notifications to process: {len(new_notifications)}")

    if not new_notifications:
        print("[Info] No new notifications found. Exiting.")
        # Write empty new-files list
        with open(NEW_FILES_LIST, "w", encoding="utf-8") as f:
            json.dump([], f)
        return

    # Ensure output directory exists
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    new_files = []

    for i, notif in enumerate(new_notifications, 1):
        print(f"\n[{i}/{len(new_notifications)}] Processing: {notif['title'][:80]}...")

        # Generate content with Claude
        blog_data = generate_blog_content(notif)
        if blog_data is None:
            print(f"  -> Skipped (content generation failed)")
            continue

        # Create slug and PHP file
        slug = slugify(blog_data.get("seo_title", notif["title"]))
        php_content = generate_php_file(notif, blog_data, slug)
        if php_content is None:
            print(f"  -> Skipped (PHP generation failed)")
            continue

        # Write PHP file
        output_file = OUTPUT_DIR / f"{slug}.php"
        with open(output_file, "w", encoding="utf-8") as f:
            f.write(php_content)

        print(f"  -> Generated: {output_file.name}")

        # Track the new file
        file_entry = {
            "file": f"blog/{slug}.php",
            "slug": slug,
            "title": blog_data["seo_title"],
            "meta_desc": blog_data["meta_desc"],
            "category": blog_data.get("category", "Admissions"),
            "date": datetime.now().strftime("%Y-%m-%d"),
            "url": f"{SITE_DOMAIN}/blog/{slug}.php",
            "excerpt": blog_data["meta_desc"][:120],
            "read_time": "5 min",
        }
        new_files.append(file_entry)

        # Mark as published
        published.append({
            "id": notif["id"],
            "title": notif["title"],
            "source_url": notif["url"],
            "blog_slug": slug,
            "generated_at": datetime.now().isoformat(),
            "source": notif["source"],
        })

    # Save updated published list
    save_published(published)

    # Save list of new files for the deploy script
    with open(NEW_FILES_LIST, "w", encoding="utf-8") as f:
        json.dump(new_files, f, indent=2, ensure_ascii=False)

    print(f"\n{'=' * 60}")
    print(f"[Done] Generated {len(new_files)} new blog posts.")
    for nf in new_files:
        print(f"  - {nf['file']}")
    print("=" * 60)


if __name__ == "__main__":
    main()
