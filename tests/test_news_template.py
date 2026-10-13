import json, os, re, shutil, socket, subprocess, sys, tempfile, time, urllib.error, urllib.request
from pathlib import Path
import pytest

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
sys.path.insert(0, str(ROOT / "scripts"))
from seo_verify import check_html

BASE = json.load(open(ROOT / "tests" / "fixtures" / "news_baseline.json"))


def _free_port():
    s = socket.socket(); s.bind(("127.0.0.1", 0)); p = s.getsockname()[1]; s.close(); return p


def _get(url):
    try:
        with urllib.request.urlopen(url, timeout=30) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")


def build_site(tmp: Path):
    """Temp web root: symlink everything from website_download except news/sitemap/llms, then build all news into it."""
    web = tmp / "web"; web.mkdir()
    for p in WEB.iterdir():
        if p.name in ("news", "sitemap.xml", "llms.txt"):
            continue
        (web / p.name).symlink_to(p)
    (web / "news").mkdir()
    shutil.copy(WEB / "sitemap.xml", web / "sitemap.xml")
    shutil.copy(WEB / "llms.txt", web / "llms.txt")
    code = (f"require {json.dumps(str(ROOT / 'scripts' / 'build-news.php'))};"
            f"news_build_all({json.dumps(str(ROOT / 'content' / 'news'))}, {json.dumps(str(web))});")
    subprocess.run(["php", "-r", code], check=True, capture_output=True, text=True)
    return web


def serve(layout):
    tmp = Path(tempfile.mkdtemp())
    web = build_site(tmp)
    port = _free_port()
    env = dict(os.environ, NEWS_LAYOUT=layout)
    proc = subprocess.Popen(["php", "-S", f"127.0.0.1:{port}", "-t", str(web)], env=env,
                            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, cwd=web)
    base = f"http://127.0.0.1:{port}"
    for _ in range(50):
        try:
            urllib.request.urlopen(base + "/robots.txt", timeout=1); break
        except urllib.error.HTTPError:
            break
        except Exception:
            time.sleep(0.1)
    return base, proc, tmp


@pytest.fixture(scope="module")
def v1_site():
    base, proc, tmp = serve("v1")
    yield base
    proc.terminate(); proc.wait(); shutil.rmtree(tmp, ignore_errors=True)


SLUGS = sorted(k for k in BASE if k != "index")


def test_baseline_has_articles():
    assert len(SLUGS) >= 50


@pytest.mark.parametrize("slug", SLUGS)
def test_v1_matches_live_head_and_jsonld(v1_site, slug):
    """Parity guard: the current template, built locally, equals what live serves today."""
    status, html = _get(f"{v1_site}/news/{slug}.php")
    if status == 404:
        pytest.skip("post not in local content/news")
    b = BASE[slug]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    assert g(r"<title>(.*?)</title>") == b["title"]
    assert g(r'<link rel="canonical" href="(.*?)"') == b["canonical"]
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]
    assert ld == b["jsonld"]


@pytest.fixture(scope="module")
def v2_site():
    base, proc, tmp = serve("v2")
    yield base
    proc.terminate(); proc.wait(); shutil.rmtree(tmp, ignore_errors=True)


def _ld(html):
    return [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]


@pytest.mark.parametrize("slug", SLUGS)
def test_v2_article_invariants(v2_site, slug):
    status, html = _get(f"{v2_site}/news/{slug}.php")
    if status == 404:
        pytest.skip("post not in local content/news")
    assert status == 200
    b = BASE[slug]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    assert g(r"<title>(.*?)</title>") == b["title"]
    assert g(r'<meta name="description" content="(.*?)"') == b["description"]
    assert g(r'<link rel="canonical" href="(.*?)"') == b["canonical"]
    assert g(r'<meta name="robots" content="(.*?)"') == b["robots"]
    assert _ld(html) == b["jsonld"]                                   # all three JSON-LD blocks byte-equal
    meta = {m[0]: m[1] for m in re.findall(r'<meta (?:property|name)="((?:og|twitter|article):[^"]+)" content="(.*?)"', html)}
    expected = dict(b["meta"]); expected["og:site_name"] = "IPU Admission Guide"
    assert meta == expected
    body = html.split("</head>")[-1]
    assert len(re.findall(r"<h1[\s>]", body)) == 1 and html.count('id="enquiry-form"') == 1
    assert "breadcrumb-wrap" not in html and "order-1 order-lg-2" not in html and "sidebar-cta" not in html
    assert "⚠" not in html and 'href="tel:9899991342"' not in html
    assert check_html(html) == [] or all("FAQPage has" in p for p in check_html(html))  # short FAQ lists are a content matter, not a template one
    crumbs = re.search(r'<nav class="ipu-page-hero__crumbs".*?</nav>', html, re.S).group(0)
    bl = [x for x in _ld(html) if x.get("@type") == "BreadcrumbList"][0]
    import html as h
    assert [h.unescape(re.sub(r"<[^>]+>", "", li)).strip() for li in re.findall(r"<li>(.*?)</li>", crumbs, re.S)] == \
           [h.unescape(i["name"]) for i in bl["itemListElement"]]
    assert len(re.findall(r'"@type":\s*"FAQPage"', html)) == len([x for x in b["jsonld"] if x.get("@type") == "FAQPage"])
    # headings: article body headings unchanged (FAQ H2 text is the same, questions become H3)
    got = [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)]
    sidebar = ("Get Free Admission Guidance", "Popular Guides", "Popular Blogs", "Get Free Expert Guidance")
    strip = lambda hs: sorted(x for x in hs if x not in sidebar)
    base_h = [x for x in b["headings"]]
    assert set(strip(base_h)) <= set(strip(got))
    now = set(re.findall(r'href="([^"#][^"]*)"', body))
    assert [l for l in b["links"] if l not in now and l not in ("/", "https://ipu.co.in/")] == []


def test_v2_article_mobile_order_and_dates(v2_site):
    slug = SLUGS[0]
    _, html = _get(f"{v2_site}/news/{slug}.php")
    body = html.split("</head>")[-1]
    assert body.index("news-tldr") < body.index('id="enquiry-form"')          # article before sidebar in DOM
    assert re.search(r'<time datetime="\d{4}-\d{2}-\d{2}">[^<]*\d{1,2} [A-Z][a-z]+ \d{4}', body)
    assert "<summary><h3>" in html or "faq" not in html.lower()


def render_post(post, layout="v2"):
    code = ("$post=json_decode(" + json.dumps(json.dumps(post)) + ",true); if (session_status()===PHP_SESSION_NONE) @session_start(); "
            "include " + json.dumps(str(WEB / "include" / "news-template.php")) + ";")
    return subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB / "news",
                          env=dict(os.environ, NEWS_LAYOUT=layout)).stdout


def _post(**kw):
    p = dict(title="Edge & \"Quote\" case", slug="edge-case", date="2026-10-10", date_modified="2026-10-10",
             category="Counselling", tags=["a"], read_time=1, is_urgent=False, image="assets/images/news/counselling.jpg",
             tldr="Short tldr.", faq=[{"q": "Q?", "a": "A."}], body_html="<p>lead</p><h2>One</h2><p>a</p><h2>Two</h2><p>b</p>")
    p.update(kw); return p


def test_v2_edge_posts_render_clean_and_urgent_banner_rules():
    for post in (_post(), _post(faq=[], tags=[]), _post(body_html=""), _post(body_html="<h2>Only</h2><p>x</p>"),
                 _post(date="2026-01-01", date_modified="2026-01-01")):
        out = render_post(post)
        assert not re.search(r"(Warning|Notice|Deprecated|Fatal error)", out)
        assert len(re.findall(r"<h1[\s>]", out)) == 1
    today = __import__("datetime").date.today().isoformat()
    assert 'class="news-urgent"' in render_post(_post(is_urgent=True, date=today, date_modified=today))
    assert 'class="news-urgent"' not in render_post(_post(is_urgent=True, date="2026-01-01", date_modified="2026-01-01"))
    assert 'class="news-urgent"' not in render_post(_post(is_urgent=False, date=today, date_modified=today))


def test_v2_article_popular_blogs_is_a_card_grid_and_index_widget_unchanged(v2_site):
    _, art = _get(f"{v2_site}/news/{SLUGS[0]}.php")
    body = art.split("</head>")[-1]
    assert 'class="news-popular"' in body and "Popular Blogs" in body
    assert "→ " not in body.split('class="news-popular"')[1].split("</div>")[0]   # arrow comes from CSS, not text
    _, idx = _get(f"{v2_site}/news/")
    assert 'class="news-popular"' not in idx and "Popular Blogs" in idx                 # index keeps the sidebar widget


def test_v2_index_invariants(v2_site, v1_site):
    status, html = _get(f"{v2_site}/news/")
    assert status == 200
    b = BASE["index"]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    assert g(r"<title>(.*?)</title>") == b["title"]
    assert g(r'<meta name="description" content="(.*?)"') == b["description"]
    assert g(r'<link rel="canonical" href="(.*?)"') == b["canonical"]
    body = html.split("</head>")[-1]
    assert len(re.findall(r"<h1[\s>]", body)) == 1 and html.count('id="enquiry-form"') == 1
    assert "sidebar-cta" not in html and 'class="news-hero"' not in body and "order-1 order-lg-2" not in body
    for hook in ('id="newsGrid"', 'id="noResults"', 'id="newsSearch"', "mid-cta-strip-col"):
        assert hook in html, hook
    v1 = _get(f"{v1_site}/news/")[1]
    count = lambda h: len(re.findall(r'class="[^"]*\bblog-item\b', h))
    assert count(html) == count(v1) > 50
    cats = lambda h: sorted(re.findall(r'class="btn-cat[^"]*" data-cat="([^"]+)"', h))
    assert cats(html) == cats(v1) and len(cats(html)) >= 4
    assert 'href="tel:9899991342"' not in html and 'href="tel:+919899991342"' in html
    assert not re.search(r"\n(Warning|Notice|Deprecated|Fatal error): ", html)   # PHP's format; article titles can contain 'Notice'
    for emoji in ("\U0001F4DE", "\U0001F50D", "&#128222;", "&#128269;"):
        assert emoji not in body, emoji
