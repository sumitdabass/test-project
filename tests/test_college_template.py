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
