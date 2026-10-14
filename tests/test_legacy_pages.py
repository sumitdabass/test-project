import json, re, subprocess, sys, time, urllib.request, urllib.error, socket
from collections import Counter
from pathlib import Path
import pytest

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
sys.path.insert(0, str(ROOT / "scripts"))
from seo_verify import check_html
from legacy_baseline import legacy_pages, visible_texts


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



BASE = json.load(open(ROOT / "tests" / "fixtures" / "legacy_baseline.json"))
ALLOWED_NEW = {"Enquire", "Need Admission Guidance?", "Talk to our expert counsellors for free guidance on IPU admissions", "Call: 9899991342"}


def _converted(page):
    return "ipu-body" in (WEB / page).read_text(encoding="utf-8", errors="replace")


CONVERTED = [p for p in sorted(BASE) if _converted(p)]


class _Balance(__import__("html.parser", fromlist=["HTMLParser"]).HTMLParser):
    VOID = {"br", "hr", "img", "input", "meta", "link", "source", "col", "area", "base", "wbr", "path", "circle", "rect", "polyline", "line"}

    def __init__(self):
        super().__init__(); self.stack = []; self.errors = []

    def handle_starttag(self, tag, attrs):
        if tag not in self.VOID: self.stack.append(tag)

    def handle_startendtag(self, tag, attrs):
        pass

    def handle_endtag(self, tag):
        if tag in self.VOID: return
        if not self.stack or self.stack[-1] != tag:
            self.errors.append(f"unbalanced </{tag}> with stack tail {self.stack[-3:]}")
            if tag in self.stack:
                while self.stack and self.stack.pop() != tag: pass
        else:
            self.stack.pop()


@pytest.mark.parametrize("page", CONVERTED)
def test_converted_page_invariants(server, page):
    status, html = _get(f"{server}/{page}")
    assert status == 200
    b = BASE[page]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    assert g(r"<title>(.*?)</title>") == b["title"]
    assert g(r'<meta name="description" content="(.*?)"') == b["description"]
    assert g(r'<link rel="canonical" href="(.*?)"') == b["canonical"]
    assert g(r'<meta name="robots" content="(.*?)"') == b["robots"]
    assert [x for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)] and \
           [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)] == b["jsonld"]
    body = html.split("</head>")[-1]
    assert len(re.findall(r"<h1[\s>]", body)) == len(b["h1"]) == 1
    assert html.count('id="enquiry-form"') == 1
    for legacy in ("blog-wrapper", "pt-130", "breadcrumb-item", "blog-details", "\U0001F449"):
        assert legacy not in body, legacy
    assert not re.search(r"\n(Warning|Notice|Deprecated|Fatal error): ", html)
    # DOM balanced
    bal = _Balance(); bal.feed(body); assert bal.errors == [] and bal.stack == []
    # visible text: nothing lost, nothing new except the allowed UI strings
    old, new = Counter(b["texts"]), Counter(visible_texts(html))
    lost = old - new
    added = new - old
    assert dict(lost) == {}, f"text lost: {list(lost)[:5]}"
    unexpected = {k: v for k, v in added.items() if k not in ALLOWED_NEW}
    # the hero breadcrumb repeats labels that were in the body breadcrumb; allow exactly those repeats
    nav = re.search(r'<nav class="ipu-page-hero__crumbs".*?</nav>', html, re.S)
    crumb_labels = {x for pair in re.findall(r'<li><a href="[^"]*">([^<]+)</a></li>|<span aria-current="page">([^<]+)</span>', nav.group(0))
                    for x in pair if x} if nav else set()
    assert {k: v for k, v in unexpected.items() if k not in crumb_labels} == {}, f"unexpected new text: {unexpected}"
    # links: every baseline href still present
    now = set(re.findall(r'href="([^"#][^"]*)"', body))
    assert [l for l in b["links"] if l not in now] == []
    # headings: same ordered list apart from the CTA card heading and the sidebar form heading
    drop = {"Need Admission Guidance?", "Get Free Admission Guidance"}
    got = [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)]
    assert [h for h in got if h not in drop] == [h for h in b["headings"] if h not in drop]
    assert check_html(html) == [] or all(p.startswith("FAQPage has") for p in check_html(html))


def test_baseline_covers_in_scope_pages():
    assert len(BASE) == 31
