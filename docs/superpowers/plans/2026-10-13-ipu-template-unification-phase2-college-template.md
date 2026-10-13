# ipu.co.in Template Unification: College Template (29 pages) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the 29 data-driven college pages (`*-admission.php` using `include/templates/college-page-template.php`) onto the unified shell proven on BPIT, behind a per-page flag so the rollout can be canaried, while fixing their duplicate H1.

**Architecture:** The template keeps its current markup as layout `v1` (default, byte-identical) and gains layout `v2` (page-hero, `.ipu-body` grid, `sidebar-enquiry`, card CTA, `<details>` FAQ). A page opts in with one line in its `$college_data`: `'layout' => 'v2'`. A new `components/faq-details.php` renders the FAQ in the new style with FAQPage JSON-LD identical to `faq-section.php`. Per-page invariants (title, meta, canonical, headings, links, FAQ schema, college schema) are pinned against a baseline captured from the live site.

**Tech Stack:** PHP 8.5 includes, pytest with a `php -S` fixture (pattern from `tests/test_bpit_pilot.py`), `scripts/seo_verify.py`.

**Spec:** `docs/superpowers/specs/2026-10-13-ipu-template-unification-design.md` (Phase 2, item 1). Predecessor plan: `docs/superpowers/plans/2026-10-13-ipu-template-unification-phase1-bpit-pilot.md` (must be complete: foundation CSS, hero Enquire/meta slots, `$cta_card`, BPIT pilot).

## Global Constraints

- Everything in the Phase 1 plan's Global Constraints applies (no copy/CTA label/phone/URL/title/meta/canonical/JSON-LD content changes except the explicit list below; one `<h1>`; one `id="enquiry-form"`; form contract unchanged; `trust-bar.php` and `index.php` untouched; no `.blog-details` class on `.ipu-*` layouts; nothing deployed or pushed without the owner's go; commits end with `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`; never chain `git commit` after `;`).
- Owner decision (2026-10-13): **keep the hero H1 as the single H1, fix its `&amp;amp;` encoding bug, and demote the body H1 to a non-H1 line with its text unchanged.**
- Explicit allowed changes on the 29 pages: (a) the H1 tag change above; (b) hero H1 text `&amp;amp;` becomes `&` (rendered text "Courses, Cutoff & Placements"); (c) breadcrumb labels/URLs become `Home / Colleges / {short_name} College` (`/`, `/ipu-colleges-list.php`, current) in BOTH the visible hero and BreadcrumbList JSON-LD (was `Home / Admissions / {short_name} Admission`); (d) add nothing else to head.
- `layout` defaults to `v1`. A v1 page's rendered HTML must stay byte-identical (modulo nothing) to its pre-change HTML.
- Hold list: none of the 29 pages is USAR, homepage or ADGIPS. If one is added later, skip it until the 5 Jan 2027 decision.

## Review Focus

- A v1 page must not change at all when the template gains v2 (the flag defaults off); a snapshot test proves it.
- The old hero and body each had an `<h1>`; after conversion exactly one remains and the demoted line keeps the same words and stays visible.
- `faq-section.php` is used by ~97 pages; it must not be edited. The new component must emit the same FAQPage JSON-LD (same questions, same answers) as the old one for the same `$faqs`.
- The `ai_summary` block, `about_text` (raw HTML containing its own `<h2>`), `admission_text`, `cutoffs`, `fees`, `cutoff_institute` (11 pages) and `related_pages` are optional per page; v2 must render every combination without a PHP notice and without dropping content.
- The BreadcrumbList label change must match the visible breadcrumb on every page, including short names containing `&` or quotes.

---

## File Structure

| File | Responsibility | Change |
|---|---|---|
| `scripts/college_baseline.py` | Capture per-page SEO baseline from live (title, meta, canonical, headings, links, FAQ, JSON-LD) | Create |
| `tests/fixtures/college_baseline.json` | Baseline for all 29 template pages | Create |
| `tests/fixtures/college_v1_dme.html` | Pre-change rendered HTML of one v1 page (snapshot) | Create |
| `website_download/include/components/faq-details.php` | `<details>` FAQ + FAQPage JSON-LD (same output as `faq-section.php`) | Create |
| `website_download/include/templates/college-page-template.php` | Add `v2` layout behind `$cd['layout']` | Modify |
| `website_download/dme-admission.php` | Canary: `'layout' => 'v2'` | Modify |
| 28 other `*-admission.php` template pages | Same one-line flag, batched | Modify (Task 5) |
| `tests/test_college_template.py` | Component, snapshot and per-page invariants | Create |

---

### Task 1: Baseline capture and v1 snapshot

**Files:**
- Create: `scripts/college_baseline.py`, `tests/fixtures/college_baseline.json`, `tests/fixtures/college_v1_dme.html`, `tests/test_college_template.py`

**Interfaces:**
- Produces: `college_baseline.json` = `{page: {title, description, canonical, headings, links, faq, jsonld_types}}`; `ROOT`, `WEB`, `server`, `_get` helpers in `tests/test_college_template.py` (copy the `server` fixture and `_get` from `tests/test_cutoff_pages.py`).

- [ ] **Step 1: Write the capture script**

Create `scripts/college_baseline.py`:
```python
#!/usr/bin/env python3
"""Capture per-page SEO baseline for every college-template page from the live site."""
import json, re, subprocess, sys, urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"


def template_pages():
    out = subprocess.run(["grep", "-l", "college-page-template", *map(str, sorted(WEB.glob("*.php")))],
                         capture_output=True, text=True).stdout.split()
    return [Path(p).name for p in out]


def grab(html):
    body = html.split("</head>")[-1]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]
    faq = []
    for block in ld:
        if block.get("@type") == "FAQPage":
            faq = [(q["name"], q["acceptedAnswer"]["text"]) for q in block["mainEntity"]]
    return {
        "title": g(r"<title>(.*?)</title>"),
        "description": g(r'<meta name="description" content="(.*?)"'),
        "canonical": g(r'<link rel="canonical" href="(.*?)"'),
        "headings": [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)],
        "links": sorted(set(re.findall(r'href="([^"#][^"]*)"', body))),
        "faq": faq,
        "jsonld_types": sorted(str(b.get("@type")) for b in ld),
    }


if __name__ == "__main__":
    base = sys.argv[1] if len(sys.argv) > 1 else "https://ipu.co.in"
    data = {p: grab(urllib.request.urlopen(f"{base}/{p}", timeout=30).read().decode("utf-8", "replace"))
            for p in template_pages()}
    (ROOT / "tests" / "fixtures").mkdir(exist_ok=True)
    (ROOT / "tests" / "fixtures" / "college_baseline.json").write_text(
        json.dumps(data, indent=1, ensure_ascii=False), encoding="utf-8")
    print(len(data), "pages captured")
```

- [ ] **Step 2: Run it against live and check the count**

Run: `cd /Users/Sumit/test-project && python3 scripts/college_baseline.py`
Expected: `29 pages captured` (30 files match the grep; if a file is the template itself, exclude it: `college-page-template.php` lives under `include/templates/`, not `website_download/*.php`, so 29 or 30 is correct; if the count differs from `grep -l college-page-template website_download/*.php | wc -l`, stop and reconcile).

- [ ] **Step 3: Capture the v1 snapshot of one page from the local server**

Run:
```bash
cd /Users/Sumit/test-project/website_download && (php -S 127.0.0.1:8000 >/dev/null 2>&1 &) ; sleep 1; curl -s http://127.0.0.1:8000/dme-admission.php > ../tests/fixtures/college_v1_dme.html; pkill -f 'php -S 127.0.0.1:8000'; wc -c ../tests/fixtures/college_v1_dme.html
```
Expected: a non-empty file (~60 KB or more). The snapshot is taken BEFORE any template edit.

- [ ] **Step 4: Write the failing tests**

Create `tests/test_college_template.py` with the `server`/`_get` helpers copied from `tests/test_cutoff_pages.py`, `import json, re`, and:
```python
BASE = json.load(open(ROOT / "tests" / "fixtures" / "college_baseline.json"))
V1_DME = (ROOT / "tests" / "fixtures" / "college_v1_dme.html").read_text(encoding="utf-8")


def test_v1_page_html_is_unchanged(server):
    status, html = _get(server + "/dme-admission.php")
    assert status == 200
    assert html == V1_DME  # fails once dme is flipped to v2 in Task 4; replaced there by a different v1 page
```
Note: this test is intentionally pinned to a page that Task 4 flips; Task 4 Step 2 moves the snapshot to a page that stays v1. Keep this test green until then.

- [ ] **Step 5: Run and commit**

Run: `python3 -m pytest tests/test_college_template.py -v` (PASS: snapshot equals current output).
Commit:
```bash
cd /Users/Sumit/test-project && git add scripts/college_baseline.py tests/fixtures/college_baseline.json tests/fixtures/college_v1_dme.html tests/test_college_template.py && git commit -m "test(college): live SEO baseline and v1 snapshot for the 29 template pages

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 2: `faq-details.php` component

**Files:**
- Create: `website_download/include/components/faq-details.php`
- Test: `tests/test_college_template.py`

**Interfaces:**
- Consumes: `render()` helper (copy the one from `tests/test_template_foundation.py` into this test file).
- Produces: component taking `$faqs` (`[['question'=>…, 'answer'=>HTML], …]`) and optional `$faq_heading`; renders `<section class="ipu-faq">` with `<details><summary><h3>Q</h3></summary><div>A</div></details>` and one FAQPage JSON-LD block whose JSON equals what `faq-section.php` emits for the same input.

- [ ] **Step 1: Write the failing tests**

```python
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
    assert "<h3>Is X good?</h3>" in html
    assert "Yes, <b>very</b> good." in html
    assert render("faq-details", faqs=[]).strip() == ""
```

- [ ] **Step 2: Run, expect FAIL** (`faq-details.php` missing): `python3 -m pytest tests/test_college_template.py -v -k faq_details`

- [ ] **Step 3: Create the component**

```php
<?php
/**
 * FAQ (details) — unified-shell FAQ with FAQPage JSON-LD identical to faq-section.php.
 *   $faqs = [['question' => '...', 'answer' => '<html allowed>'], ...];
 *   $faq_heading = 'Frequently Asked Questions'; // optional
 */
$faqs = $faqs ?? [];
$faq_heading = $faq_heading ?? 'Frequently Asked Questions';
if (empty($faqs)) return;
?>
<section class="ipu-faq">
  <h2><?= htmlspecialchars($faq_heading) ?></h2>
  <?php foreach ($faqs as $faq): ?>
  <details>
    <summary><h3><?= htmlspecialchars($faq['question']) ?></h3></summary>
    <div class="ipu-faq__a"><?= $faq['answer'] ?></div>
  </details>
  <?php endforeach; ?>
</section>

<!-- FAQPage Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php foreach ($faqs as $i => $faq): ?>
    {
      "@type": "Question",
      "name": "<?= htmlspecialchars($faq['question'], ENT_QUOTES) ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?= htmlspecialchars(strip_tags($faq['answer']), ENT_QUOTES) ?>"
      }
    }<?= $i < count($faqs) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ]
}
</script>
```
Add to `base-head.php` next to the other `.ipu-faq` rules (one line): `.ipu-faq__a{padding:0 16px 16px}.ipu-faq__a p{margin:0 0 10px}`, and make sure the existing `.ipu-faq details p{padding:0 16px 16px;margin:0}` rule does not double-pad (the new component wraps the answer in a `div`, so that rule does not apply to it).

- [ ] **Step 4: Run tests, then the full suites, commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && git add tests/test_college_template.py website_download/include/components/faq-details.php website_download/include/base-head.php && git commit -m "feat(ui): faq-details component with FAQPage schema identical to faq-section

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Expected: green.

---

### Task 3: v2 layout in the college template (flag-gated)

**Files:**
- Modify: `website_download/include/templates/college-page-template.php`
- Test: `tests/test_college_template.py`

**Interfaces:**
- Consumes: Phase 1 hero locals (`$hero_h1`, `$hero_intro`, `$hero_breadcrumbs`, `$hero_show_form=false`, `$hero_show_enquire=true`, `$last_updated`), `$cta_card`, `.ipu-*` CSS, `faq-details.php`.
- Produces: `$cd['layout']` (`'v1'` default, `'v2'`), no other new data keys.

- [ ] **Step 1: Write the failing tests**

A throw-away fixture page proves v2 without touching a real page. Create `tests/fixtures/college_v2_fixture.php` (it is included by the test via `php -S`; the server root is `website_download`, so the fixture is served by copying: the test writes it to `website_download/_college_v2_fixture.php` and removes it afterwards). In the test:
```python
FIXTURE = '''<?php
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
'''


@pytest.fixture()
def v2_page(server):
    f = WEB / "_college_v2_fixture.php"
    f.write_text(FIXTURE, encoding="utf-8")
    try:
        status, html = _get(server + "/_college_v2_fixture.php")
        assert status == 200
        yield html
    finally:
        f.unlink()


def test_v2_single_h1_with_encoded_ampersand(v2_page):
    assert len(re.findall(r"<h1[\s>]", v2_page)) == 1
    h1 = re.search(r"<h1[^>]*>(.*?)</h1>", v2_page, re.S).group(1)
    assert "&amp;amp;" not in h1 and "Cutoff &amp; Placements" in h1


def test_v2_demoted_title_line_keeps_text(v2_page):
    assert "FIX IPU Admission 2026 – Complete Guide" in v2_page.split("</h1>")[1].split("</section>")[0]


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
    a, f = v2_page.index("About FIX"), v2_page.index('id="enquiry-form"')
    assert a < f < v2_page.index("Courses Offered at FIX")
    assert 'href="tel:9899991342"' not in v2_page
    ld = re.findall(r'"@type":\s*"FAQPage"', v2_page)
    assert len(ld) == 1


def test_v2_minimal_data_renders_without_notices(server):
    f = WEB / "_college_v2_min.php"
    f.write_text("<?php $college_data=['layout'=>'v2','slug'=>'_college_v2_min','name'=>'Min College','short_name'=>'MIN',"
                 "'title'=>'MIN','meta_desc'=>'m','address'=>'Delhi','last_updated'=>'2026-04-06'];"
                 "include 'include/templates/college-page-template.php';", encoding="utf-8")
    try:
        status, html = _get(server + "/_college_v2_min.php")
    finally:
        f.unlink()
    assert status == 200
    assert not re.search(r"(Warning|Notice|Deprecated|Fatal error)", html)
    assert len(re.findall(r"<h1[\s>]", html)) == 1
```

- [ ] **Step 2: Run, expect FAIL** (v2 not implemented): `python3 -m pytest tests/test_college_template.py -v -k v2`; `test_v1_page_html_is_unchanged` must still pass.

- [ ] **Step 3: Implement the flag**

At the top of the template, after the aliases: `$layout = $cd['layout'] ?? 'v1';`. Wrap the existing breadcrumb schema + hero + body (everything from `$breadcrumbs = [...]` through the closing `</section>` of "Main Content", plus the CTA strip and FAQ blocks) in `<?php if ($layout !== 'v2'): ?> ... <?php else: ?> v2 ... <?php endif; ?>`, leaving the v1 text byte-identical. Keep, outside the conditional and in this order: head schema for `college-schema.php`, `</head><body>`, `base-nav.php`; after the conditional, the cutoff-rounds include, `related-pages.php`, `base-footer.php`. The head-side `breadcrumb-schema.php` include must also move inside the conditional (v1 keeps the old labels; v2 uses the new ones), so split it: v1 branch keeps `['Admissions', '/ipu-admission-guide.php'], [$short_name . ' Admission', '']`; v2 branch uses:
```php
$breadcrumbs = [['Home', '/'], ['Colleges', '/ipu-colleges-list.php'], [$short_name . ' College', '']];
include 'include/components/breadcrumb-schema.php';
unset($breadcrumbs);
```
Because the schema include sits in `<head>` territory before `</head>`, structure the file as two conditional regions (head-schema region and body region), each guarded by `$layout`.

The v2 body region:
```php
<?php
$hero_h1 = htmlspecialchars($short_name . ' Admission 2026 – Courses, Cutoff & Placements');
$hero_intro = htmlspecialchars($short_name) . ' IPU Admission 2026 – Complete Guide';
$hero_breadcrumbs = [['Home', '/'], ['Colleges', '/ipu-colleges-list.php'], [$short_name . ' College', '']];
$hero_show_form = false;
$hero_show_enquire = true;
$last_updated = $cd['last_updated'] ?? date('Y-m-d');
include 'include/components/page-hero.php';
unset($hero_h1, $hero_intro, $hero_breadcrumbs, $hero_show_enquire, $last_updated);
?>

<div class="ipu-body">
  <div class="ipu-body__intro">
    <section id="ai-summary" aria-hidden="true" class="ipu-tint">
      <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
      <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($cd['ai_summary'] ?? '') ?></p>
    </section>
    <?= $cd['about_text'] ?? '' ?>
  </div>

  <div class="ipu-body__aside">
    <?php include 'include/components/sidebar-enquiry.php'; ?>
  </div>

  <div class="ipu-body__rest">
    <!-- courses (table in .ipu-tablewrap), admission process, CTA card, cutoffs (tablewrap), placements, campus life, CTA paragraph, fees block (tablewrap), FAQ -->
  </div>
</div>
```
Fill `__rest` by moving the v1 sections verbatim (same headings, same `$cd` loops and escaping) with these changes only: each `<table ...>` is wrapped in `<div class="ipu-tablewrap">…</div>`; after the admission process section insert `<?php $cta_card = true; $cta_heading = 'Need Help with ' . $short_name . ' Admission?'; $cta_subtext = 'Get free expert counselling on cutoffs, choice filling and management quota'; include 'include/components/cta-strip.php'; ?>` (the old full-width strip below the page is dropped in v2: it is this card); the FAQ becomes `<?php $faqs = $cd['faqs'] ?? []; include 'include/components/faq-details.php'; unset($faqs); ?>`. The "CTA paragraph" with `tel:+919899991342` stays. The `hero_h1` string uses a plain `&` inside `htmlspecialchars` so it renders as `&amp;` once, not `&amp;amp;`.

- [ ] **Step 4: Run all tests, verify v1 unchanged, commit**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -3 && php scripts/tests/run.php 2>/dev/null | tail -1
```
Expected: all green, including `test_v1_page_html_is_unchanged`. Then:
```bash
git add tests/test_college_template.py website_download/include/templates/college-page-template.php && git commit -m "feat(ui): college template v2 layout behind \$college_data['layout'] (v1 unchanged)

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 4: Canary page (`dme-admission.php`)

**Files:**
- Modify: `website_download/dme-admission.php` (one line), `tests/test_college_template.py`
- Create: `tests/fixtures/college_v1_dias.html` (replacement v1 snapshot)

**Interfaces:**
- Consumes: baseline JSON from Task 1, v2 layout from Task 3.

- [ ] **Step 1: Move the v1 snapshot to a page that stays v1**

Capture `dias-admission.php` as in Task 1 Step 3 (BEFORE flipping dme) into `tests/fixtures/college_v1_dias.html`; change `test_v1_page_html_is_unchanged` to use `dias-admission.php` and that fixture; delete `tests/fixtures/college_v1_dme.html`. Run: PASS.

- [ ] **Step 2: Write the per-page invariants test (parametrised over flipped pages)**

```python
V2_PAGES = ["dme-admission.php"]   # Task 5 appends the rest


def _norm_link(u):
    return "/" if u == "https://ipu.co.in/" else u


@pytest.mark.parametrize("page", V2_PAGES)
def test_v2_page_invariants_against_live_baseline(server, page):
    status, html = _get(f"{server}/{page}")
    assert status == 200
    base = BASE[page]
    g = lambda pat: (re.search(pat, html, re.S) or [None, None])[1]
    assert g(r"<title>(.*?)</title>") == base["title"]
    assert g(r'<meta name="description" content="(.*?)"') == base["description"]
    assert g(r'<link rel="canonical" href="(.*?)"') == base["canonical"]
    assert len(re.findall(r"<h1[\s>]", html)) == 1
    assert html.count('id="enquiry-form"') == 1
    assert check_html(html) == []          # from scripts/seo_verify.py (import as in test_bpit_pilot.py)
    body = html.split("</head>")[-1]
    got = [re.sub(r"<[^>]+>", "", m).strip() for m in re.findall(r"<h[23][^>]*>(.*?)</h[23]>", body, re.S)]
    skip = {"Need Help with", "Get Free Admission Guidance"}
    drop = lambda hs: sorted(h for h in hs if not any(h.startswith(s) for s in skip))
    assert drop(got) == drop(base["headings"])
    now = {_norm_link(u) for u in re.findall(r'href="([^"#][^"]*)"', body)}
    lost = [l for l in base["links"] if _norm_link(l) not in now and "ipu-admission-guide.php" not in l]
    assert lost == []
    ld = [json.loads(x) for x in re.findall(r'<script type="application/ld\+json">(.*?)</script>', html, re.S)]
    faq = [(q["name"], q["acceptedAnswer"]["text"]) for b in ld if b.get("@type") == "FAQPage" for q in b["mainEntity"]]
    assert faq == [tuple(x) for x in base["faq"]]
```
(`ipu-admission-guide.php` is exempt because the old `Admissions` breadcrumb link is deliberately replaced by `Colleges`; `lost` may also legitimately drop the old hero's `Admissions` link only. Any other lost link fails.) Add `from seo_verify import check_html` and the `sys.path` line as in `tests/test_bpit_pilot.py`.

- [ ] **Step 3: Run; expect FAIL** (dme still v1): the H1 count assertion fails (2 H1s).

- [ ] **Step 4: Flip dme**

In `website_download/dme-admission.php` add as the first array entry: `'layout' => 'v2',`. Run: `python3 -m pytest tests/test_college_template.py -v` expected PASS.

- [ ] **Step 5: Visual check and commit**

Capture `http://127.0.0.1:8000/dme-admission.php` at 1280px (headless Chrome, window 1280x3600) and compare with BPIT: breadcrumb, orange Call + Enquire, trust bar, grid, sticky sidebar, CTA card heading visible, tables scroll in their wrapper, FAQ `<details>`. Fix CSS in `base-head.php` only. Then:
```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /dme-admission.php && git add tests website_download/dme-admission.php && git commit -m "feat(ui): dme-admission on college template v2 (canary)

Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 5: Flip the remaining 28 pages

**Files:**
- Modify: the 28 other template pages (one line each), `tests/test_college_template.py`

**Interfaces:**
- Consumes: Task 4 invariants test.

- [ ] **Step 1: Make the test cover all pages and watch it fail**

Replace `V2_PAGES = ["dme-admission.php"]` with `V2_PAGES = sorted(BASE)` (all 29). Run: `python3 -m pytest tests/test_college_template.py -v`
Expected: 28 FAIL (still v1), `dme` PASS; `test_v1_page_html_is_unchanged` now FAILS for dias once flipped, so replace it first: change that test to assert the snapshot only while `'layout'` is absent in `dias-admission.php`, and delete it in Step 3 once every page is v2 (the flag-default behaviour stays covered by `test_v2_*` fixtures plus `test_v1_default_when_flag_absent` below).

Add:
```python
def test_v1_default_when_flag_absent(server):
    f = WEB / "_college_v1_min.php"
    f.write_text("<?php $college_data=['slug'=>'_college_v1_min','name'=>'Min','short_name'=>'MIN','title'=>'MIN','meta_desc'=>'m','address'=>'Delhi','last_updated'=>'2026-04-06'];include 'include/templates/college-page-template.php';", encoding="utf-8")
    try:
        _, html = _get(server + "/_college_v1_min.php")
    finally:
        f.unlink()
    assert "hero-section" in html and "ipu-body" not in html
```

- [ ] **Step 2: Flip in two batches**

Batch A (the 8 highest-traffic pages: gtb4cec, fairfield, dtc, echelon, meri, usct, jims-vasant-kunj, ndim) then batch B (the remaining 20). For each page insert `'layout' => 'v2',` as the first entry of `$college_data` with a script (not by hand):
```bash
cd /Users/Sumit/test-project/website_download && for f in gtb4cec fairfield dtc echelon meri usct jims-vasant-kunj ndim; do python3 - "$f-admission.php" <<'E'
import sys,re
p=sys.argv[1]; s=open(p,encoding='utf-8').read()
assert "'layout'" not in s and s.count("$college_data = [")==1, p
open(p,'w',encoding='utf-8').write(s.replace("$college_data = [\n","$college_data = [\n    'layout' => 'v2',\n",1))
E
done; git diff --stat | tail -3
```
Expected: each file shows `1 insertion`. After each batch run `python3 -m pytest tests/test_college_template.py -q` and fix any page-specific failure at its cause (for example a short name containing `&`, a page without `faqs`, a page whose `about_text` has no `<h2>`); never relax the invariants test to pass.

- [ ] **Step 3: Full verification and commit per batch**

```bash
cd /Users/Sumit/test-project && python3 -m pytest tests -q 2>&1 | tail -2 && php scripts/tests/run.php 2>/dev/null | tail -1 && for p in $(grep -l "'layout' => 'v2'" website_download/*.php | xargs -n1 basename); do python3 scripts/seo_verify.py --base http://127.0.0.1:8000 /$p | tail -1; done | sort | uniq -c
```
Expected: all `OK` (the two-H1 failures on these pages are gone), commit message `feat(ui): college pages batch A/B on template v2` with the attribution line. After batch B delete `test_v1_page_html_is_unchanged` and `tests/fixtures/college_v1_dias.html` in the same commit.

---

### Task 6: Verification and hand-off (no deploy)

**Files:** none modified.

- [ ] **Step 1: Branch-wide checks**

Run both suites, `git diff --stat <phase-1-HEAD>..HEAD` (only the files listed above), and confirm `index.php`, `trust-bar.php`, `faq-section.php`, `usar-admission.php` and the ADGIPS page are absent from the diff.

- [ ] **Step 2: Visual spot checks**

At 390px and 1280px compare three pages with different data shapes (one with `cutoffs`+`fees`, one with `cutoff_institute`, one minimal) against `IPU Page Templates.dc.html`; note any horizontal overflow.

- [ ] **Step 3: Hand off for the owner's go**

Report: commits, test counts, the 29 pages now on v2, the H1 fix and breadcrumb label change (Admissions to Colleges) so the owner can confirm, and the rollout recommendation: deploy `college-page-template.php` + `faq-details.php` + `base-head.php` + `dme-admission.php` first (canary, template change is inert for v1 pages), watch GSC for dme for about a week, then ship batch A, then batch B, each as a file-scoped deploy from a clean worktree with a live backup first. Do not push or deploy.
