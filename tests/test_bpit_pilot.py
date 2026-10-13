import json, re, subprocess, sys, time, urllib.request, urllib.error, socket
from pathlib import Path
import pytest

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
sys.path.insert(0, str(ROOT / "scripts"))
from seo_verify import check_html


def _free_port():
    s = socket.socket(); s.bind(("127.0.0.1", 0)); p = s.getsockname()[1]; s.close(); return p


@pytest.fixture(scope="module")
def server():
    port = _free_port()
    proc = subprocess.Popen(["php", "-S", f"127.0.0.1:{port}", "-t", str(WEB)],
                            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, cwd=WEB)
    base = f"http://127.0.0.1:{port}"
    for _ in range(50):
        try:
            urllib.request.urlopen(base + "/robots.txt", timeout=1); break
        except urllib.error.HTTPError:
            break
        except Exception:
            time.sleep(0.1)
    yield base
    proc.terminate(); proc.wait()


def _get(url):
    try:
        with urllib.request.urlopen(url, timeout=20) as r:
            return r.status, r.read().decode("utf-8", "replace")
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode("utf-8", "replace")



BASE = json.load(open(ROOT / "tests" / "fixtures" / "bpit_baseline.json"))


@pytest.fixture(scope="module")
def page(server):
    status, html = _get(server + "/BPIT.php")
    assert status == 200
    return html


def _g(pat, html):
    m = re.search(pat, html, re.S)
    return m.group(1) if m else None


def _body(html):
    return html.split("</head>")[-1]


def _headings(html):
    return [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", _body(html), re.S)]


def test_seo_invariants_unchanged(page):
    assert _g(r"<title>(.*?)</title>", page) == BASE["title"]
    assert _g(r'<meta name="description" content="(.*?)"', page) == BASE["description"]
    assert _g(r'<link rel="canonical" href="(.*?)"', page) == BASE["canonical"]


def test_structure_one_h1_one_form_no_legacy(page):
    assert len(re.findall(r"<h1[\s>]", page)) == 1
    assert page.count('id="enquiry-form"') == 1
    assert "breadcrumb-item" not in page
    assert "pt-130" not in page and "blog-wrapper" not in page
    assert "\U0001F449" not in page
    assert check_html(page) == []


def test_headings_same_text_apart_from_cta_and_order_of_core_sections(page):
    skip = "Need Admission Guidance?"
    got = [h for h in _headings(page) if h != skip]
    expected = [h for h in BASE["headings"] if h != skip]
    assert sorted(got) == sorted(expected)
    core = ["About BPIT College (IP University)", "Courses Offered at BPIT", "Admission Process (IP University)",
            "Placement Overview", "Campus & Location Advantage"]
    idx = [got.index(h) for h in core]
    assert idx == sorted(idx)


def test_every_baseline_link_still_present(page):
    norm = lambda u: "/" if u == "https://ipu.co.in/" else u  # old breadcrumb Home link == hero Home link
    now = {norm(u) for u in re.findall(r'href="([^"#][^"]*)"', _body(page))}
    missing = [l for l in BASE["links"] if norm(l) not in now]
    assert missing == []


def test_hero_breadcrumb_and_schema_match(page):
    crumbs = _g(r'(<nav class="ipu-page-hero__crumbs".*?</nav>)', page) or ""
    assert "Home" in crumbs and "Colleges" in crumbs and "BPIT College" in crumbs
    ld = re.findall(r'<script type="application/ld\+json">(.*?)</script>', page, re.S)
    bl = [json.loads(x) for x in ld if '"BreadcrumbList"' in x][0]
    assert [i["name"] for i in bl["itemListElement"]] == ["Home", "Colleges", "BPIT College"]


def test_faq_visible_equals_schema(page):
    visible = re.findall(r"<details[^>]*>\s*<summary[^>]*>\s*<h3[^>]*>(.*?)</h3>.*?</summary>\s*<p>(.*?)</p>", page, re.S)
    assert len(visible) == 3
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', page, re.S) if '"FAQPage"' in x]
    bpit = [l for l in ld if any("BPIT good for B.Tech" in q["name"] for q in l["mainEntity"])][0]
    schema = [(q["name"], q["acceptedAnswer"]["text"]) for q in bpit["mainEntity"]]
    norm = lambda s: re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", s)).strip().replace("&amp;", "&")
    assert [(norm(a), norm(b)) for a, b in visible] == [(norm(a), norm(b)) for a, b in schema]


def test_dom_order_form_after_about_before_courses(page):
    body = _body(page)
    a = body.index("About BPIT College (IP University)")
    f = body.index('id="enquiry-form"')
    c = body.index("Courses Offered at BPIT")
    assert a < f < c


def test_social_tags_present_and_phone_links_normalised(page):
    for tag in ("og:title", "og:description", "og:url", "og:type", "twitter:title", "twitter:description"):
        assert tag in page
    assert 'href="tel:9899991342"' not in page
    assert 'href="#enquiry-form"' in page
