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



from seo_verify import check_html

BASE = json.load(open(ROOT / "tests" / "fixtures" / "college_baseline.json"))
V1_SNAPSHOT = {"dme-admission.php": ROOT / "tests" / "fixtures" / "college_v1_dme.html",
               "dias-admission.php": ROOT / "tests" / "fixtures" / "college_v1_dias.html"}


def render(component: str, **local_vars) -> str:
    """Render include/components/<component>.php with PHP locals set."""
    assigns = "".join(f"${k}=json_decode({json.dumps(json.dumps(v))},true);" for k, v in local_vars.items())
    code = f"{assigns} include {json.dumps(str(WEB / 'include' / 'components' / (component + '.php')))};"
    return subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB).stdout


def _template_output(html):
    """Body after the last </head> plus every JSON-LD block (shared head CSS may change; the template must not)."""
    return html.split("</head>")[-1] + "".join(re.findall(r'<script type="application/ld\+json">.*?</script>', html, re.S))


@pytest.mark.parametrize("page", sorted(V1_SNAPSHOT))
def test_v1_page_html_is_unchanged(server, page):
    status, html = _get(f"{server}/{page}")
    assert status == 200
    assert _template_output(html) == _template_output(V1_SNAPSHOT[page].read_text(encoding="utf-8"))


FAQS = [{"question": "Is X good?", "answer": "Yes, <b>very</b> good."},
        {"question": "Fees & seats?", "answer": "Rs. 1,00,000 per year."}]


def _faq_schema(html):
    blocks = re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)
    assert len(blocks) == 1
    return json.loads(blocks[0])


def test_faq_details_schema_equals_old_component():
    new = render("faq-details", faqs=FAQS)
    old = render("faq-section", faqs=FAQS)
    assert _faq_schema(new) == _faq_schema(old)


def test_faq_details_markup_and_empty_input():
    html = render("faq-details", faqs=FAQS)
    assert html.count("<details") == 2 and html.count("<summary") == 2
    assert "<h3>Is X good?</h3>" in html or "<h3>Fees &amp; seats?</h3>" in html
    assert "Yes, <b>very</b> good." in html
    assert render("faq-details", faqs=[]).strip() == ""


FIXTURE = """<?php
$college_data = [
  'layout' => 'v2', 'slug' => '_college_v2_fixture', 'name' => 'Fixture Institute & Co', 'short_name' => 'FIX',
  'title' => 'FIX Admission 2026 | Test', 'meta_desc' => 'Fixture meta.', 'address' => 'Delhi', 'total_seats' => 60,
  'last_updated' => '2026-04-06', 'ai_summary' => 'Summary text.',
  'about_text' => '<h2>About FIX</h2><p>About copy.</p>',
  'courses' => [['name' => 'BBA', 'duration' => '3 Years', 'seats' => '60']],
  'admission_text' => '<ol><li>Step one</li></ol>',
  'placements' => [['label' => 'Average', 'value' => '5 LPA']],
  'campus_life' => ['Labs'],
  'cutoffs' => [['branch' => 'BBA', 'round1' => '80', 'last_round' => '70']],
  'fees' => [['course' => 'BBA', 'fee' => 'Rs. 1', 'seats' => 60]],
  'faqs' => [['question' => 'Q1?', 'answer' => 'A1'], ['question' => 'Q2?', 'answer' => 'A2'], ['question' => 'Q3?', 'answer' => 'A3']],
  'related_pages' => [['title' => 'T', 'url' => '/x.php', 'desc' => 'D']],
];
include 'include/templates/college-page-template.php';
"""


def _serve_temp(server, name, php):
    f = WEB / name
    f.write_text(php, encoding="utf-8")
    try:
        return _get(f"{server}/{name}")
    finally:
        f.unlink()


@pytest.fixture()
def v2_page(server):
    status, html = _serve_temp(server, "_college_v2_fixture.php", FIXTURE)
    assert status == 200
    return html


def test_v2_single_h1_with_encoded_ampersand(v2_page):
    assert len(re.findall(r"<h1[\s>]", v2_page)) == 1
    h1 = re.search(r"<h1[^>]*>(.*?)</h1>", v2_page, re.S).group(1)
    assert "&amp;amp;" not in h1 and "Cutoff &amp; Placements" in h1


def test_v2_demoted_title_line_keeps_text(v2_page):
    after_h1 = v2_page.split("</h1>")[1].split("</section>")[0]
    assert "FIX IPU Admission 2026 – Complete Guide" in after_h1


def test_v2_breadcrumb_matches_schema(v2_page):
    crumbs = re.search(r'<nav class="ipu-page-hero__crumbs".*?</nav>', v2_page, re.S).group(0)
    assert all(x in crumbs for x in ("Home", "Colleges", "FIX College"))
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', v2_page, re.S)]
    bl = [b for b in ld if b.get("@type") == "BreadcrumbList"][0]
    assert [i["name"] for i in bl["itemListElement"]] == ["Home", "Colleges", "FIX College"]


def test_v2_structure(v2_page):
    assert v2_page.count('id="enquiry-form"') == 1
    assert "blog-details" not in v2_page and "sidebar-cta" not in v2_page
    assert v2_page.count("<details") == 3
    body = v2_page.split("</head>")[-1]
    a, f = body.index("About FIX"), body.index('id="enquiry-form"')
    assert a < f < body.index("Courses Offered at FIX")
    assert 'href="tel:9899991342"' not in v2_page
    assert len(re.findall(r'"@type":\s*"FAQPage"', v2_page)) == 1
    assert len(re.findall(r'"@type":\s*"BreadcrumbList"', v2_page)) == 1


MIN = ("<?php $college_data=['layout'=>'v2','slug'=>'_college_v2_min','name'=>'Min College','short_name'=>'MIN',"
       "'title'=>'MIN','meta_desc'=>'m','address'=>'Delhi','last_updated'=>'2026-04-06'];"
       "include 'include/templates/college-page-template.php';")


def test_v2_minimal_data_renders_without_notices(server):
    status, html = _serve_temp(server, "_college_v2_min.php", MIN)
    assert status == 200
    assert not re.search(r"(Warning|Notice|Deprecated|Fatal error)", html)
    assert len(re.findall(r"<h1[\s>]", html)) == 1


def test_v1_default_when_flag_absent(server):
    status, html = _serve_temp(server, "_college_v1_min.php", MIN.replace("'layout'=>'v2',", "").replace("_college_v2_min", "_college_v1_min"))
    assert status == 200
    body = html.split("</head>")[-1]
    assert "hero-section" in body and "ipu-body" not in body
