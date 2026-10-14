# ipu.co.in Template Unification: Legacy-Body Pages (31 pages) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the 31 hand-written course, CUET, cutoff, management-quota, listicle and college-guide pages that still sit in the old `blog-wrapper pt-130 pb-130` body onto the unified shell (hero breadcrumb + meta line + Enquire button, `.ipu-body` grid with one sidebar form after the first section, CTA card) without changing any copy, title, meta, canonical or JSON-LD.

**Architecture:** These pages are free-form PHP, not data-driven, so the conversion is done by a deterministic, idempotent codemod (`scripts/unify_legacy_page.py`) rather than by hand. The codemod rewrites only the wrapper: it lifts the visible breadcrumb into `$hero_breadcrumbs`, lifts the `Last Updated` include into the hero, splits the content column into intro (first section) and rest at a top-level section boundary, and emits the `.ipu-body` grid with the existing `sidebar-enquiry.php` include. Every converted page is checked against a baseline captured from the live site: head tags, JSON-LD, links, headings and an ordered/multiset comparison of visible text, so nothing can be lost silently. Three pilot pages go first, then two batches.

**Tech Stack:** Python 3 codemod + pytest (temp `php -S` fixture as in earlier phases), PHP includes, `scripts/seo_verify.py`.

**Spec:** `docs/superpowers/specs/2026-10-13-ipu-template-unification-design.md` (Phase 2, item 3). Predecessors: Phase 1 plan (foundation CSS, hero slots, `$cta_card`, BPIT pilot), Phase 2 plan, Phase 3 plan (all complete).

## Scope

In scope (31): every `website_download/*.php` containing `blog-wrapper` except the exclusions below, i.e. `IPU-B-Tech-admission-2026`, `IPU-Law-Admission`, `IP-University-management-quota-admission-eligibility-criteria`, `BVP`, `ballb-management-quota-ipu`, `bba-management-quota-ipu`, `btech-management-quota-ipu`, `mba-management-quota-ipu`, `best-btech-colleges-ipu`, `top-btech-colleges-ipu-comparison`, the three `comprehensive-guide-to-*` / `guide-to-bjmc-*` / `ultimate-guide-to-ballb-*` guides, the nine `cuet-*` pages plus `cuet-admission-ipu`, `economics-admission-ip-university`, `explore-MSIT-and-MSI-janakpuri`, `exploring-MAIT-and-MAIMS`, `ipu-btech-cutoff-analysis`, `ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card`, `ipu-choice-filling-strategy`, `law-3-year-admission-ipu`, `mba-admission-ip-university`. The script computes the list; the names above are for orientation.

Out of scope (deferred to the "older and outlier pages" plan): `b-tech-colleges-under-IP-university.php` (own H1, no hero, stats layout), `ipu-admission-guide.php`, `ipu-b-tech-pillar.php`, `maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php` (single centred column, different skeleton), `BPIT.php` (done), the 30 college-template pages (done), and every page not using `blog-wrapper`. Hand-written FAQ blocks are not converted to `<details>` here: only 7 of the 31 have a visible FAQ heading, only 2 match a strict h3/p pattern, so the risk outweighs the benefit; they are listed for a later pass.

## Global Constraints

- Everything in the earlier phases' Global Constraints applies (no title/meta/canonical/robots/JSON-LD change; one `<h1>`; one `id="enquiry-form"`; form contract unchanged; no `.blog-details` class on `.ipu-*` layouts; `index.php`, `trust-bar.php`, `faq-section.php` untouched; nothing pushed or deployed without the owner's go; commits end with `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`; never chain `git commit` after `;`; macOS `sed -i ''`).
- **No copy change.** Allowed differences versus live, per page, and nothing else: (a) the visible breadcrumb moves from the body to the hero (same labels, same hrefs, same order; labels are NOT renamed to "Colleges"); (b) `Last Updated`/byline text moves into the hero (same text); (c) the 👉 character is removed from text nodes (link text unchanged); (d) new UI strings: `Enquire` and the CTA card strings `Need Admission Guidance?`, `Talk to our expert counsellors for free guidance on IPU admissions`, `Call: 9899991342`.
- The existing bottom CTA blocks (`counter-area`, phone strips), facts blocks, cutoff tables and `related-pages.php` stay where they are, outside the grid, byte-for-byte.
- Hold list: USAR, the homepage and ADGIPS are not in this list; if one ever is, skip it until 5 Jan 2027.
- A page the codemod cannot convert with every assertion passing is NOT converted by hand-editing around the assertions: it is skipped, listed in the ledger, and left for a manual decision.

## Review Focus

- The splitter must never cut inside an open element, a `<?php ?>` block, an HTML comment, or a `<script>`/`<style>`; the converted page's DOM must be balanced and its visible text multiset identical to live apart from the allowed differences.
- A page whose first content node is not a heading, has a single section, has `<hr>` separators between sections, or uses `.section-block` wrappers must still split at a section boundary or stay whole in the intro.
- The visible breadcrumb on pages that also emit BreadcrumbList JSON-LD (3 pages set `$breadcrumbs`) must still match the schema labels; the codemod must not change either.
- `$last_updated` is assigned by 16 pages in the body; moving it into the hero's PHP block must not break pages that use `$last_updated` again later (Article/Course schema, `last-updated.php` a second time).
- Removing `.blog-details` removes legacy paragraph/list spacing: the new CSS must give the same readable rhythm under `.ipu-body` without a `.blog-details` clash (the earlier phase's cascade bug).
- The codemod must be idempotent (second run is a no-op) and must refuse already-converted pages.

---

## File Structure

| File | Responsibility | Change |
|---|---|---|
| `scripts/legacy_baseline.py` | Capture per-page baseline from live: head tags, JSON-LD, links, headings, visible-text multiset | Create |
| `tests/fixtures/legacy_baseline.json` | Baseline for the 31 in-scope pages | Create |
| `scripts/unify_legacy_page.py` | The codemod (segmenter, breadcrumb lift, split, emit) | Create |
| `tests/test_unify_legacy_page.py` | Unit tests for the codemod on synthetic inputs | Create |
| `tests/test_legacy_pages.py` | Per-page invariants against the live baseline (runs on converted pages) | Create |
| `website_download/include/base-head.php` | `.ipu-body` typography for raw content (p, ul, ol, h3, hr, img, table) | Modify |
| 31 page files | Converted by the codemod in three steps | Modify |

---

### Task 1: Baseline capture and invariants harness

**Files:**
- Create: `scripts/legacy_baseline.py`, `tests/fixtures/legacy_baseline.json`, `tests/test_legacy_pages.py`

**Interfaces:**
- Produces: `legacy_pages() -> list[str]` (the in-scope page names, computed from the filesystem: contains `blog-wrapper`, not in the exclusion set); `visible_texts(html) -> list[str]` (ordered text nodes of `<body>`, whitespace-collapsed, 👉 removed, script/style/php-comment ignored); baseline JSON `{page: {title, description, canonical, robots, h1, headings, links, jsonld, texts}}`.

- [ ] **Step 1: Write the capture script**

Create `scripts/legacy_baseline.py`:
```python
#!/usr/bin/env python3
"""Capture the live baseline for every legacy-body page (in-scope list for the phase-4 codemod)."""
import json, re, sys, urllib.request
from html.parser import HTMLParser
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
EXCLUDED = {"b-tech-colleges-under-IP-university.php", "ipu-admission-guide.php", "ipu-b-tech-pillar.php",
            "maharaja-agrasen-business-school-one-of-the-best-PGDM-colleges-in-delhi.php", "BPIT.php"}


def legacy_pages():
    """Pages still carrying the old wrapper (or already converted: marker 'ipu-body' + was in baseline)."""
    names = []
    for f in sorted(WEB.glob("*.php")):
        if f.name in EXCLUDED:
            continue
        s = f.read_text(encoding="utf-8", errors="replace")
        if "blog-wrapper" in s:
            names.append(f.name)
    return names


class _Texts(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.skip = 0; self.in_body = False; self.out = []

    def handle_starttag(self, tag, attrs):
        if tag == "body": self.in_body = True
        if tag in ("script", "style"): self.skip += 1

    def handle_endtag(self, tag):
        if tag in ("script", "style"): self.skip -= 1

    def handle_data(self, data):
        if self.in_body and not self.skip:
            t = re.sub(r"\s+", " ", data.replace("\U0001F449", "")).strip()
            if t: self.out.append(t)


def visible_texts(html):
    p = _Texts(); p.feed(html); return p.out


def grab(html):
    body = html.split("</head>")[-1]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    return {
        "title": g(r"<title>(.*?)</title>"),
        "description": g(r'<meta name="description" content="(.*?)"'),
        "canonical": g(r'<link rel="canonical" href="(.*?)"'),
        "robots": g(r'<meta name="robots" content="(.*?)"'),
        "h1": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h1[^>]*>(.*?)</h1>", body, re.S)],
        "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
        "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
        "jsonld": [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)],
        "texts": visible_texts(html),
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    data = {p: grab(urllib.request.urlopen(f"{base}/{p}", timeout=30).read().decode("utf-8", "replace")) for p in legacy_pages()}
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "legacy_baseline.json").write_text(json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data), "pages captured")
```

- [ ] **Step 2: Run it against live**

Run: `cd /Users/Sumit/test-project && python3 scripts/legacy_baseline.py`
Expected: `31 pages captured`. If the number differs, list the difference against the Scope section and reconcile before continuing. Do not run the baseline AFTER any page is converted: the baseline must come from live (unconverted) pages. If live has changed since the repo (a page edited on live only), compare each page's local HTML to its live HTML first and stop on any difference you cannot explain.

- [ ] **Step 3: Write the invariants test (it must fail for a converted page that loses something)**

Create `tests/test_legacy_pages.py` (copy the `server`/`_get` helpers from `tests/test_cutoff_pages.py`; add `import json, re, sys`, `from collections import Counter`, `sys.path.insert(0, str(ROOT / "scripts"))`, `from legacy_baseline import legacy_pages, visible_texts`, `from seo_verify import check_html`):
```python
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
```

- [ ] **Step 4: Run and commit**

Run: `python3 -m pytest tests/test_legacy_pages.py -q 2>&1 | tail -3`. Expected: only `test_baseline_covers_in_scope_pages` runs (nothing converted yet, so `CONVERTED` is empty) and passes.
```bash
cd /Users/Sumit/test-project && git add scripts/legacy_baseline.py tests/fixtures/legacy_baseline.json tests/test_legacy_pages.py && git commit -m "test(legacy): live baseline and invariants harness for the 31 legacy-body pages

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 2: `.ipu-body` typography for raw content

**Files:**
- Modify: `website_download/include/base-head.php`
- Test: `tests/test_template_foundation.py`

**Interfaces:**
- Produces: scoped rules under `.ipu-body__intro` and `.ipu-body__rest` for raw `p`, `ul`, `ol`, `h3`, `hr`, `img`, `table` so unwrapped legacy content keeps a readable rhythm without `.blog-details`.

- [ ] **Step 1: Write the failing test**

```python
def test_ipu_body_raw_content_typography_is_scoped():
    css = HEAD.replace(" ", "").replace("\n", "")
    for rule in (".ipu-body__intro p,.ipu-body__rest p{margin:0 0 14px;line-height:1.7}",
                 ".ipu-body__intro ul,.ipu-body__intro ol,.ipu-body__rest ul,.ipu-body__rest ol{padding-left:22px;margin:0 0 14px}",
                 ".ipu-body__rest h3,.ipu-body__intro h3{font-size:18px;font-weight:700;margin:20px 0 8px;color:var(--ipu-ink)}",
                 ".ipu-body__intro img,.ipu-body__rest img{max-width:100%;height:auto}",
                 ".ipu-body__intro hr,.ipu-body__rest hr{border:0;border-top:1px solid var(--ipu-rule);margin:24px 0}"):
        assert rule.replace(" ", "") in css, rule
```
Run: `python3 -m pytest tests/test_template_foundation.py -q -k raw_content` Expected: FAIL.

- [ ] **Step 2: Add the CSS** (after the existing `.ipu-body h2` rules in `base-head.php`, one rule per line exactly as in the test):
```css
.ipu-body__intro p,.ipu-body__rest p{margin:0 0 14px;line-height:1.7}
.ipu-body__intro ul,.ipu-body__intro ol,.ipu-body__rest ul,.ipu-body__rest ol{padding-left:22px;margin:0 0 14px}
.ipu-body__rest h3,.ipu-body__intro h3{font-size:18px;font-weight:700;margin:20px 0 8px;color:var(--ipu-ink)}
.ipu-body__intro img,.ipu-body__rest img{max-width:100%;height:auto}
.ipu-body__intro hr,.ipu-body__rest hr{border:0;border-top:1px solid var(--ipu-rule);margin:24px 0}
.ipu-body__rest .ipu-card h3,.ipu-body__rest .ipu-tint h3{margin-top:0}
```
(The last rule keeps the earlier `.ipu-card h3` look; check BPIT still renders the same, since those classes live in the same containers.)

- [ ] **Step 3: Run suites and commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && git add tests/test_template_foundation.py website_download/include/base-head.php && git commit -m "feat(ui): scoped typography for raw content inside the unified body grid

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Then re-screenshot BPIT at 1280px and compare with the Phase 1 render; fix any regression in `base-head.php` before moving on.

---

### Task 3: The codemod

**Files:**
- Create: `scripts/unify_legacy_page.py`, `tests/test_unify_legacy_page.py`

**Interfaces:**
- Produces: `convert(source: str) -> str` (pure function: raises `ConvertError` with a reason when any assertion fails; returns the converted source; idempotence: raises `ConvertError("already converted")` when `ipu-body` is present), `top_level_nodes(html) -> list[tuple[str,str]]`, `split_sections(nodes) -> tuple[list, list]`, and a CLI: `python3 scripts/unify_legacy_page.py [--apply] page.php ...` printing `OK page` or `SKIP page: reason` (dry-run by default).

- [ ] **Step 1: Write the failing unit tests**

Create `tests/test_unify_legacy_page.py`:
```python
import sys
from pathlib import Path
import pytest
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from unify_legacy_page import ConvertError, convert, split_sections, top_level_nodes

PAGE = """<?php
include_once("include/base-head.php");
?>
<title>T</title>
</head>
<body>
<?php include_once("include/base-nav.php") ?>
<?php
$hero_h1 = 'Course Guide';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>
<section class="blog-wrapper pt-130 pb-130">
<div class="container">
<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="ipu-admission-guide.php">IPU Admission Guide</a></li>
<li class="breadcrumb-item active">Course Guide</li>
</ol>
</nav>
<div class="row">
<div class="col-lg-8">
<div class="blog-details">
<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>
<h2>First</h2>
<p>
&#128073; intro <a href="a.php">A</a>
</p>
<p>\U0001F449 <a href="b.php">Link B</a></p>
<hr>
<h2>Second</h2>
<p>second para</p>
<h2>Third</h2>
<p>third</p>
</div>
</div>
<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>
</div>
</div>
</section>
<?php include_once("include/base-footer.php"); ?>
</body>
</html>
"""


def test_top_level_nodes_balanced_and_php_opaque():
    nodes = top_level_nodes('<p>a<?php if ($x > 1): ?>b<?php endif; ?></p><hr><div><h2>x</h2></div>text')
    assert [k for k, _ in nodes] == ["el", "el", "el", "text"]
    assert nodes[0][1] == '<p>a<?php if ($x > 1): ?>b<?php endif; ?></p>'


def test_split_sections_before_second_boundary():
    nodes = top_level_nodes("<h2>A</h2><p>x</p><hr><h2>B</h2><p>y</p><h2>C</h2>")
    intro, rest = split_sections(nodes)
    assert "".join(r for _, r in intro) == "<h2>A</h2><p>x</p><hr>"
    assert "".join(r for _, r in rest) == "<h2>B</h2><p>y</p><h2>C</h2>"


def test_split_sections_section_block_wrappers():
    nodes = top_level_nodes('<div class="section-block"><h2>A</h2><p>x</p></div><div class="section-block"><h2>B</h2></div>')
    intro, rest = split_sections(nodes)
    assert "".join(r for _, r in intro).count("<h2>A</h2>") == 1 and "<h2>B</h2>" in "".join(r for _, r in rest)


def test_split_sections_single_section_stays_whole():
    nodes = top_level_nodes("<h2>A</h2><p>x</p>")
    intro, rest = split_sections(nodes)
    assert rest == [] and "".join(r for _, r in intro) == "<h2>A</h2><p>x</p>"


def test_convert_full_page():
    out = convert(PAGE)
    assert "blog-wrapper" not in out and "breadcrumb-item" not in out and "blog-details" not in out
    assert "$hero_breadcrumbs = [['Home', 'https://ipu.co.in/'], ['IPU Admission Guide', 'ipu-admission-guide.php'], ['Course Guide', '']];" in out
    assert "$hero_show_enquire = true;" in out
    assert "$last_updated = '2026-06-24';" in out.split("page-hero.php")[0]          # lifted into the hero block
    assert out.count("last-updated.php") == 0                                         # hero renders it
    assert "\U0001F449" not in out and "&#128073;" not in out
    assert out.count("sidebar-enquiry.php") == 1
    ib = out[out.index('<div class="ipu-body">'):]
    assert ib.index("<h2>First</h2>") < ib.index("sidebar-enquiry.php") < ib.index("<h2>Second</h2>") < ib.index("<h2>Third</h2>")
    assert "cta-strip.php" in ib.split("<h2>Second</h2>")[0].split("ipu-body__rest")[1]   # CTA card opens the rest column
    assert '<a href="a.php">A</a>' in out and '<a href="b.php">Link B</a>' in out    # links untouched


def test_convert_is_idempotent_and_refuses_unknown_shapes():
    out = convert(PAGE)
    with pytest.raises(ConvertError, match="already converted"):
        convert(out)
    with pytest.raises(ConvertError, match="sidebar"):
        convert(PAGE.replace("sidebar-enquiry.php", "sidebar-cta.php"))
    with pytest.raises(ConvertError, match="blog-wrapper"):
        convert("<html><body><p>no wrapper</p></body></html>")
```
Run: `python3 -m pytest tests/test_unify_legacy_page.py -q` Expected: FAIL (module missing).

- [ ] **Step 2: Implement the codemod**

Create `scripts/unify_legacy_page.py`:
```python
#!/usr/bin/env python3
"""Convert a legacy blog-wrapper page to the unified .ipu-body shell. Deterministic, idempotent, strict.

Usage: python3 scripts/unify_legacy_page.py [--apply] website_download/page.php ...
Dry run by default: prints OK / SKIP per page.
"""
import re, sys
from pathlib import Path

VOID = {"br", "hr", "img", "input", "meta", "link", "source", "col", "area", "base", "wbr"}
EMOJI = "\U0001F449"
PHP = re.compile(r"<\?(?:php|=).*?\?>", re.S)
TAG = re.compile(r"<!--.*?-->|<script\b.*?</script>|<style\b.*?</style>|<(/?)([a-zA-Z][a-zA-Z0-9]*)\b[^>]*?(/?)>|[^<]+|<", re.S)


class ConvertError(Exception):
    pass


def _mask(html):
    blocks = []
    def sub(m):
        blocks.append(m.group(0)); return f"\x00{len(blocks) - 1}\x00"
    return PHP.sub(sub, html), blocks


def _unmask(text, blocks):
    return re.sub(r"\x00(\d+)\x00", lambda m: blocks[int(m.group(1))], text)


def top_level_nodes(html):
    """[(kind, raw)] with kind in {'el','text','comment'}; elements balanced; PHP blocks stay inside their node."""
    masked, blocks = _mask(html)
    nodes, cur, depth = [], [], 0
    for m in TAG.finditer(masked):
        raw = m.group(0)
        if m.group(2) is None:                       # comment / script / style / text / stray '<'
            kind = "comment" if raw.startswith("<!--") else ("el" if raw.startswith(("<script", "<style")) else "text")
            if depth == 0: nodes.append((kind, raw))
            else: cur.append(raw)
            continue
        closing, tag, selfc = m.group(1), m.group(2).lower(), m.group(3)
        if not closing:
            if tag in VOID or selfc:
                if depth == 0: nodes.append(("el", raw))
                else: cur.append(raw)
            else:
                cur.append(raw); depth += 1
        else:
            cur.append(raw); depth -= 1
            if depth < 0: raise ConvertError("unbalanced closing tag in content")
            if depth == 0:
                nodes.append(("el", "".join(cur))); cur = []
    if depth != 0: raise ConvertError("unbalanced content (unclosed element)")
    return [(k, _unmask(r, blocks)) for k, r in nodes]


def _is_boundary(kind, raw):
    return kind == "el" and bool(re.match(r"<h2\b", raw) or re.match(r"<(?:div|section)\b[^>]*>\s*<h2\b", raw))


def split_sections(nodes):
    seen = 0
    for i, (k, r) in enumerate(nodes):
        if _is_boundary(k, r):
            seen += 1
            if seen == 2:
                return nodes[:i], nodes[i:]
    return nodes, []


def _find_balanced(s, start, tag):
    """Index just past the closing tag matching the <tag ...> that begins at s[start]."""
    masked, blocks = _mask(s)
    # map start to masked offset
    off = len(_mask(s[:start])[0])
    depth = 0
    for m in re.finditer(rf"<(/?){tag}\b[^>]*?>", masked[off:]):
        depth += -1 if m.group(1) else 1
        if depth == 0:
            masked_end = off + m.end()
            # translate masked offset back to original offset
            return len(_unmask(masked[:masked_end], blocks))
    raise ConvertError(f"unbalanced <{tag}>")


def convert(src):
    if "ipu-body" in src:
        raise ConvertError("already converted")
    m = re.search(r'<section class="blog-wrapper[^"]*">', src)
    if not m:
        raise ConvertError("no blog-wrapper section")
    sec_start = m.start()
    sec_end = _find_balanced(src, sec_start, "section")
    section = src[sec_start:sec_end]
    inner = section[m.end() - sec_start:-len("</section>")]

    # container
    cm = re.match(r'\s*<div class="container">', inner)
    if not cm:
        raise ConvertError("wrapper has no .container")
    c_start = inner.index('<div class="container">')
    c_end = _find_balanced(inner, c_start, "div")
    container = inner[c_start + len('<div class="container">'):c_end - len("</div>")]

    # optional visible breadcrumb
    crumbs = []
    nm = re.match(r'\s*<nav aria-label="breadcrumb"[^>]*>.*?</nav>', container, re.S)
    if nm:
        for li in re.findall(r'<li class="breadcrumb-item[^"]*"[^>]*>(.*?)</li>', nm.group(0), re.S):
            a = re.match(r'\s*<a href="([^"]*)"[^>]*>(.*?)</a>\s*$', li, re.S)
            if a: crumbs.append((re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", a.group(2))).strip(), a.group(1)))
            else: crumbs.append((re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", li)).strip(), ""))
        container = container[nm.end():]
    if nm and (len(crumbs) < 2 or crumbs[-1][1] != ""):
        raise ConvertError("breadcrumb shape not understood")

    # row > col-lg-8 + col-lg-4
    rm = re.match(r'\s*<div class="row[^"]*">', container)
    if not rm:
        raise ConvertError("no .row after breadcrumb")
    row_start = rm.start()
    row_end = _find_balanced(container, container.index("<div", row_start), "div")
    row_inner = container[rm.end():row_end - len("</div>")]
    c8 = re.match(r'\s*<div class="col-lg-8">', row_inner)
    if not c8:
        raise ConvertError("no col-lg-8 content column")
    c8_end = _find_balanced(row_inner, row_inner.index("<div", c8.start()), "div")
    content = row_inner[c8.end():c8_end - len("</div>")]
    side = row_inner[c8_end:]
    sm = re.fullmatch(r'\s*<div class="col-lg-4">\s*(<\?php include __DIR__ \. \'/include/components/sidebar-enquiry\.php\'; \?>)\s*</div>\s*', side)
    if not sm:
        raise ConvertError("sidebar column is not exactly the sidebar-enquiry include")
    sidebar_include = sm.group(1)
    if container[row_end:].strip():
        raise ConvertError("unexpected content after the row inside the container")

    # unwrap a sole .blog-details wrapper
    bd = re.fullmatch(r'\s*<div class="blog-details">(.*)</div>\s*', content, re.S)
    if bd and _find_balanced(content, content.index("<div"), "div") >= len(content.rstrip()):
        content = bd.group(1)

    # lift the Last Updated include into the hero
    lu = re.findall(r"<\?php\s*\$last_updated\s*=\s*'([0-9-]+)';\s*include\s*'include/components/last-updated\.php';\s*\?>", content)
    if len(lu) > 1:
        raise ConvertError("more than one last-updated include")
    last_updated = None
    if lu:
        last_updated = lu[0]
        content = re.sub(r"<\?php\s*\$last_updated\s*=\s*'[0-9-]+';\s*include\s*'include/components/last-updated\.php';\s*\?>\s*", "", content, count=1)

    # emoji removal in text (links keep their text)
    content = content.replace("&#128073;", "").replace(EMOJI, "")
    content = re.sub(r"(<p>)[ \t]*", r"\1", content)           # '<p>👉 <a' -> '<p><a'

    nodes = top_level_nodes(content)
    intro, rest = split_sections(nodes)
    join = lambda ns: "".join(r for _, r in ns).strip("\n")

    # hero block: the PHP block that includes page-hero.php
    hm = None
    for cand in re.finditer(r"<\?php(?:(?!\?>).)*?components/page-hero\.php';(?:(?!\?>).)*?\?>", src, re.S):
        hm = cand
    if not hm or hm.start() > sec_start:
        raise ConvertError("no hero include before the wrapper")
    hero = hm.group(0)
    add = []
    if crumbs:
        items = ", ".join("['" + l.replace("\\", "\\\\").replace("'", "\\'") + "', '" + u.replace("'", "\\'") + "']" for l, u in crumbs)
        add.append(f"$hero_breadcrumbs = [{items}];")
    add.append("$hero_show_enquire = true;")
    if last_updated:
        add.append(f"$last_updated = '{last_updated}';")
    new_hero = hero.replace("include __DIR__ . '/include/components/page-hero.php';", "\n".join(add) + "\ninclude __DIR__ . '/include/components/page-hero.php';", 1)
    if new_hero == hero:
        raise ConvertError("hero include statement not in the expected form")

    cta = "<?php $cta_card = true; include 'include/components/cta-strip.php'; ?>"
    rest_html = (cta + "\n" + join(rest)) if rest else ""
    grid = f"""<div class="ipu-body">

  <div class="ipu-body__intro">
{join(intro)}
  </div>

  <div class="ipu-body__aside">
    {sidebar_include}
  </div>

  <div class="ipu-body__rest">
{rest_html}
  </div>

</div>"""
    out = src[:hm.start()] + new_hero + src[hm.end():sec_start] + grid + src[sec_end:]
    return out


def main(argv):
    apply = "--apply" in argv
    rc = 0
    for name in [a for a in argv if not a.startswith("--")]:
        p = Path(name)
        try:
            out = convert(p.read_text(encoding="utf-8"))
        except ConvertError as e:
            print(f"SKIP {p.name}: {e}"); rc = 1; continue
        if apply: p.write_text(out, encoding="utf-8")
        print(f"OK   {p.name}")
    return rc


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
```
Implementation notes for the executor: the `_find_balanced` offsets and the container-extraction lines above are the delicate part. If any index arithmetic misbehaves on the synthetic fixture, simplify it (for example by building the nested-element extraction on `top_level_nodes` instead of regex offsets) but keep the public signatures and every `ConvertError` reason string used by the tests. The tests, not this text, are the specification.

- [ ] **Step 3: Run the unit tests until green**

Run: `python3 -m pytest tests/test_unify_legacy_page.py -v 2>&1 | tail -15`
Expected: all PASS. Fix the codemod, never the tests, unless a test contradicts the Scope section.

- [ ] **Step 4: Dry-run on every in-scope page and ledger the result**

Run: `cd /Users/Sumit/test-project/website_download && python3 ../scripts/unify_legacy_page.py $(python3 -c "import sys; sys.path.insert(0,'../scripts'); from legacy_baseline import legacy_pages; print(' '.join(legacy_pages()))")`
Expected: a list of `OK`/`SKIP page: reason`. Expected from the plan author's dry run on 2026-10-14 (read-only, scratch copy of this exact codemod): 21 `OK`, 10 `SKIP`. The skips were: `BVP`, `IP-University-management-quota-admission-eligibility-criteria`, `IPU-B-Tech-admission-2026`, `comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions`, `exploring-MAIT-and-MAIMS`, `guide-to-bjmc-colleges-under-ip-university`, `top-btech-colleges-ipu-comparison` (all "no .row after breadcrumb": something other than `<div class="row">` follows the breadcrumb, to be inspected and given a strict rule plus unit test), `economics-admission-ip-university` and `ipu-btech-cutoff-analysis` ("sidebar column is not exactly the sidebar-enquiry include": extra markup in the sidebar column), and `explore-MSIT-and-MSI-janakpuri` ("unbalanced content": an unclosed element in the source, which is itself a defect to report). Task 5 therefore covers the 21 clean pages (minus the 3 pilots); Task 6 handles the 10 skips one rule at a time. Record every SKIP and its reason in the ledger. Pages that SKIP are not forced; each reason decides whether the codemod gets a new strict rule (with a new unit test) or the page waits for a manual decision.

- [ ] **Step 5: Commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && git add scripts/unify_legacy_page.py tests/test_unify_legacy_page.py && git commit -m "feat(tools): strict idempotent codemod for legacy blog-wrapper pages

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 4: Pilot on three pages

**Files:**
- Modify (via the codemod): `bba-management-quota-ipu.php` (skeleton A: no visible breadcrumb), `cuet-admission-ipu.php` (skeleton B: breadcrumb + `Last Updated`), `best-btech-colleges-ipu.php` (skeleton D: `.section-block` wrappers, college cards)
- Test: `tests/test_legacy_pages.py` (runs on whatever is converted)

- [ ] **Step 1: Run the invariants test before converting and watch it have nothing to check**

`python3 -m pytest tests/test_legacy_pages.py -q` Expected: 1 passed (no converted pages yet).

- [ ] **Step 2: Convert the three pilot pages**

```bash
cd /Users/Sumit/test-project/website_download && python3 ../scripts/unify_legacy_page.py --apply bba-management-quota-ipu.php cuet-admission-ipu.php best-btech-colleges-ipu.php && git diff --stat | tail -4
```
Expected: three `OK` lines. If any is `SKIP`, fix the codemod per Task 3 Step 4's rule, then re-run.

- [ ] **Step 3: Run the invariants test**

Run: `python3 -m pytest tests/test_legacy_pages.py -q 2>&1 | tail -8`
Expected: 3 converted-page tests PASS. A failure is a finding: fix the codemod (and add a unit test for the shape it missed), reset the page with `git checkout -- <page>` only AFTER the pilot pages are committed or backed up with `cp` (never lose uncommitted work), and re-run.

- [ ] **Step 4: Visual review**

Start `php -S 127.0.0.1:8000 -t website_download` and screenshot the three pages at 1280px (headless Chrome, window 1280x3600). Compare each against the same page on production. Checklist: hero breadcrumb and meta line, Call + Enquire, form after the first section, sticky sidebar on desktop, CTA card heading visible, content rhythm acceptable (paragraph and list spacing, `.college-card` grid on the listicle, tables not overflowing), no stray dot. Fix shared CSS in `base-head.php` only (with a test), re-run the invariants.

- [ ] **Step 5: Commit the pilot and stop for the owner's look**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /bba-management-quota-ipu.php /cuet-admission-ipu.php /best-btech-colleges-ipu.php && git add website_download tests && git commit -m "feat(ui): legacy pilot (3 pages) on the unified body grid via codemod

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 5: Batch 1 (the remaining skeleton-A and skeleton-B pages)

**Files:** every remaining in-scope page whose dry-run was `OK` and whose skeleton is A or B (about 21 pages: the management-quota pages, the CUET pages, law and MBA pages, IPU B.Tech/Law admission guides, cutoff analysis, choice filling, admit-card page, the two `comprehensive`/`ultimate` guides).

- [ ] **Step 1: Select the pages**

```bash
cd /Users/Sumit/test-project/website_download && python3 ../scripts/unify_legacy_page.py $(python3 -c "import sys; sys.path.insert(0,'../scripts'); from legacy_baseline import legacy_pages; print(' '.join(legacy_pages()))") | grep '^OK' | awk '{print $2}' | grep -vE '^(exploring-MAIT|explore-MSIT|BVP|best-btech|top-btech)' > /tmp/batch1.txt; wc -l /tmp/batch1.txt
```
(The remaining college-guide pages `BVP`, `exploring-MAIT-and-MAIMS`, `explore-MSIT-and-MSI-janakpuri`, `top-btech-colleges-ipu-comparison` go in Task 6.) The filter excludes only pages converted in the pilot because the codemod refuses already-converted pages (`SKIP … already converted`, which `grep '^OK'` drops).

- [ ] **Step 2: Convert, test, review**

```bash
cd /Users/Sumit/test-project/website_download && python3 ../scripts/unify_legacy_page.py --apply $(cat /tmp/batch1.txt) && cd .. && python3 -m pytest tests/test_legacy_pages.py -q 2>&1 | tail -6
```
Expected: all converted pages PASS. For any failure, apply the Task 4 Step 3 rule (fix the codemod, add a unit test, re-run on a clean copy of the page). Spot-check three pages visually at 1280px (one with a `counter-area` bottom CTA, one with tables, one with images).

- [ ] **Step 3: Full suites, SEO verify, commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && for p in $(cat /tmp/batch1.txt); do python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /$p | tail -1 | cut -c1-120; done | sort | uniq -c | sort -rn | head && git add website_download tests && git commit -m "feat(ui): legacy pages batch 1 on the unified body grid via codemod

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: `seo_verify` results identical to the same pages at the pre-conversion commit (compare with a temporary `git worktree` of `HEAD~1` on another port; report any page that changes status). Known pre-existing failures on some of these pages (two H1s, FAQPage under 3 questions, BreadcrumbList twice) are not caused by this work and are listed for the owner, not fixed here.

---

### Task 6: Batch 2 (listicles and bespoke college guides) and verification

**Files:** the remaining in-scope pages (`BVP.php`, `exploring-MAIT-and-MAIMS.php`, `explore-MSIT-and-MSI-janakpuri.php`, `top-btech-colleges-ipu-comparison.php`, and any batch-1 SKIPs whose codemod rule was added).

- [ ] **Step 1: Convert what the codemod accepts; list the rest**

Run the codemod `--apply` on the remaining names, then the invariants test. Every page that SKIPs stays unconverted and is listed in the ledger with its reason.

- [ ] **Step 2: Branch-wide verification**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && git diff --stat <phase-3-HEAD>..HEAD | tail -3 && git diff --name-only <phase-3-HEAD>..HEAD | grep -E 'index\.php$|trust-bar|faq-section|sitemap|usar-admission' ; echo "(none above = good)"
```
Expected: green; only in-scope page files, `base-head.php`, scripts and tests changed; `index.php`, `trust-bar.php`, `faq-section.php`, `sitemap.xml`, `usar-admission.php` absent. Count converted pages (`grep -l ipu-body website_download/*.php`) and list the unconverted remainder.

- [ ] **Step 3: Hand off for the owner's go (no deploy)**

Report: commits, test counts, pages converted versus skipped (with reasons), what visibly changes (hero breadcrumb and meta line, Enquire button, form after the first section on mobile, CTA card, emoji removed), and the pre-existing seo_verify findings. Recommended rollout: static files, so deploy in three file-scoped batches from a clean worktree (pilot first, watch Search Console for about a week, then batch 1, then batch 2), each with a live backup and `cmp` against `HEAD~N` for drift, then curl 200 plus a fatal-error grep. `base-head.php` ships with the first batch (it is a shared include; its new rules are scoped and inert on pages that do not use `.ipu-body__*`). Do not push or deploy.
