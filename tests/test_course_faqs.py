import json, re, subprocess, sys, time, urllib.request, urllib.error, socket
from pathlib import Path
import pytest

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
PHONE = "9899991342"
LD_RE = re.compile(r'<script[^>]+type="application/ld\+json"[^>]*>(.*?)</script>', re.S | re.I)


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


def faq_answers(html):
    out = {}
    for block in LD_RE.findall(html):
        try:
            data = json.loads(block)
        except json.JSONDecodeError:
            continue
        nodes = data if isinstance(data, list) else [data]
        for n in nodes:
            if isinstance(n, dict) and n.get("@type") == "FAQPage":
                for q in n.get("mainEntity", []):
                    out[q["name"]] = q["acceptedAnswer"]["text"]
    return out


# (page, question, fragment that must appear in the answer; all figures come from ipu-fees-structure.php)
CASES = [
    ("IPU-B-Tech-admission-2026.php", "What are IPU B.Tech fees?", "1,55,700"),
    ("IPU-B-Tech-admission-2026.php", "What is the B.Tech cutoff for IPU colleges?", "round-wise JEE Main ranks"),
    ("law-3-year-admission-ipu.php", "What are IPU 3-year LLB fees?", "USLLS"),
    ("llm-admission-ipu.php", "What are IPU LLM fees?", "USLLS"),
    ("top-law-colleges-ipu.php", "What are IPU law college fees?", "USLLS"),
    ("IPU-Law-Admission.php", "What are IPU law college fees?", "USLLS"),
]


@pytest.mark.parametrize("page,question,fragment", CASES)
def test_new_faq_present_and_phone_free(server, page, question, fragment):
    with urllib.request.urlopen(f"{server}/{page}", timeout=20) as r:
        html = r.read().decode("utf-8", "replace")
    answers = faq_answers(html)
    assert question in answers, f"{page}: missing FAQ {question!r}"
    assert fragment in answers[question]
    assert PHONE not in answers[question]


def test_fee_anchors_on_fee_page():
    text = (WEB / "ipu-fees-structure.php").read_text()
    for a in ("btech-fees", "bba-fees", "bcom-fees", "mba-fees", "ballb-fees", "llb-fees", "llm-fees"):
        assert f'id="{a}"' in text, a


def test_cutoff_faq_keeps_its_link_in_visible_html(server):
    # FAQPage JSON-LD strips tags, so the link must be checked in the visible markup.
    with urllib.request.urlopen(f"{server}/IPU-B-Tech-admission-2026.php", timeout=20) as r:
        html = r.read().decode("utf-8", "replace")
    assert 'href="/ipu-btech-cutoff-2025.php"' in html
