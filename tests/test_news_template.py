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
