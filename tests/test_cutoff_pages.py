import subprocess, sys, time, urllib.request, urllib.error, socket
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


@pytest.mark.parametrize("page,short,inst", [
    ("mait-cutoff.php", "MAIT", "Maharaja Agrasen Institute of Technology"),
    ("msit-cutoff.php", "MSIT", "Maharaja Surajmal Institute Technology"),
])
def test_cutoff_page_renders_clean(server, page, short, inst):
    status, html = _get(f"{server}/{page}")
    assert status == 200
    assert check_html(html, strict_faq_phone=True) == []
    assert f"https://ipu.co.in/{page}" in html            # self-canonical
    assert f"What is the {short} cutoff for CSE?" in html   # data-driven FAQ rendered
    assert "Computer Science" in html                       # branch table rendered
    assert "Fatal error" not in html and "Warning:" not in html


def test_unknown_institute_fails_loudly(server):
    src = (WEB / "mait-cutoff.php").read_text().replace(
        "Maharaja Agrasen Institute of Technology", "Nonexistent Institute")
    probe = WEB / "_probe-cutoff.php"
    probe.write_text(src)
    try:
        status, _ = _get(f"{server}/_probe-cutoff.php")
    finally:
        probe.unlink()
    assert status == 500


def test_admission_pages_link_to_cutoff_pages():
    for adm, cut in (("mait-admission.php", "/mait-cutoff.php"), ("msit-admission.php", "/msit-cutoff.php")):
        assert cut in (WEB / adm).read_text()


def test_mbs_and_dwarka_pages(server):
    for page in ("mbs-college-admission.php", "ipu-colleges-in-dwarka.php"):
        status, html = _get(f"{server}/{page}")
        assert status == 200, page
        assert check_html(html, strict_faq_phone=True) == [], page
        assert f"https://ipu.co.in/{page}" in html
    _, mbs = _get(f"{server}/mbs-college-admission.php")
    for phrase in ("MBS College Dwarka", "Courses Offered", "How to Reach", "Admission Process",
                   "Sector 9", "COA", "AICTE"):
        assert phrase in mbs, phrase
    _, hub = _get(f"{server}/ipu-colleges-in-dwarka.php")
    assert "/mbs-college-admission.php" in hub and "/usict-admission.php" in hub


def test_dwarka_hub_does_not_claim_usar_is_in_dwarka(server):
    # ipu.ac.in and usar-admission.php both place USAR on the East Campus (Surajmal Vihar).
    _, hub = _get(f"{server}/ipu-colleges-in-dwarka.php")
    assert "USICT, USAR, USMS" not in hub
    assert "Surajmal Vihar" in hub            # hub clarifies where USAR actually is
