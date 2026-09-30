# ipu.co.in Keyword Map: Season 2027 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Put every target course and college on the right page, with the right keywords and sourced facts, live and indexed by 12 Feb 2027, so the site captures the most of the 2027 counselling season (impressions ramp from Feb; 367k in May 2026, 2.1M in Jul 2026).

**Architecture:** Mostly additive (tier A) edits to existing pages plus a few new year-free pages (tier B). Facts (cutoffs, fees, packages, campus area) live in data files, and pages render only what is present, so nothing is invented. A small verifier script and a watch-terms comparer make every deploy and every title test measurable against the 2026-09-30 GSC baseline.

**Tech Stack:** Vanilla PHP 8 pages (`website_download/`), Python 3 + pytest for tooling, `php -S` for local checks, `deploy.py` (file-scoped FTP), GSC CSV exports.

**Spec:** `docs/superpowers/specs/2026-09-30-ipu-keyword-map-design.md`

## Global Constraints

- Branch `claude/2026-04-30-ipu-session`. Deploy only with `python3 deploy.py --manifest <file>` (file-scoped FTP; never a full sync). `--dry-run` first.
- **Never edit `include/base-head.php`** (held Phase-2 commit `a7627b8`). New pages may `include` it; they must NOT call `webp_img()` or any function defined only in held includes.
- **Never change URL, `<title>`, meta description, canonical or H1 on an existing ranking page** except the tier-C tasks (Tasks 10 and 11), each needing Sumit's explicit per-page approval first.
- New pages: year-free URL, self-canonical, exactly one H1, exactly one enquiry form (`include/components/sidebar-enquiry.php` only), exactly one BreadcrumbList.
- **No phone number inside `$faqs` answers** on any new or edited FAQ (CTA Wave 2a will strip them from ~94 pages; do not add to the pile). Phone stays in hero/CTA/sidebar/body only, always as `tel:+919899991342`.
- Facts (fees, cutoffs, packages, campus area, seats) come only from: the GGSIPU brochure/gazette, official IPU admission notices, college official sites. Each data row carries a `source` and `as_of`. If unsourced, the sentence is not rendered.
- Internal links: add entries to `$related_pages` arrays (and `href`); scan both when checking orphans.
- Before shipping any existing page file, run the entanglement check (Appendix A step 2).
- Commit messages end with: `Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>`
- Spelling British English; times 24h.

## Review Focus

1. **Institute-key mismatch in the cutoff data.** Keys are typed strings (e.g. `'Maharaja Surajmal Institute Technology'`, `'Bharati Vidyapeeths College of Engineering'`). A wrong key makes the table render nothing silently and leaves a thin page. The cutoff template must fail loudly (Task 4 test).
2. **Cannibalisation.** New `mait-cutoff.php` / `msit-cutoff.php` compete with `mait-admission.php` / `msit-admission.php` for `mait cutoff` / `msit cutoff`. Each admission page must link to its cutoff page, and each cutoff page back (Task 4 test on links).
3. **Phone in FAQ answers** re-creating the Wave 2a cleanup (Task 1 strict check).
4. **Orphans and sitemap gaps** for every new URL (Task 4, 5, 12 checks).
5. **Stale years / unsourced facts:** a package, fee or seat number with no `source`/`as_of` rendering on the page (Task 6 test).

## Keyword master schedule (what goes live when)

Baseline = `seo/baselines/2026-09-30-gsc-export/`. Position = GSC average. "Go-live" is the deploy batch date (Appendix A). Targets are hypotheses to check at the rechecks, not promises.

| # | Entity | Primary keyword (impr, pos) | Secondary keywords | Page | Tier | Task | Go-live | Target by 15 Apr 2027 |
|---|---|---|---|---|---|---|---|---|
| 1 | MAIT | `mait cutoff` (3.7k, 8.9) | `mait cutoff 2026`, `mait delhi cutoff`, `mait cse cutoff` | new `mait-cutoff.php` | B | 4 | 30 Oct | p ≤ 6 |
| 2 | MSIT | `msit cutoff` (5.8k, 7.9) | `msit cutoff 2026`, `msit janakpuri cutoff`, `msit cse cutoff` | new `msit-cutoff.php` | B | 4 | 30 Oct | p ≤ 5.5 |
| 3 | MAIT | `mait` (10.9k, 7.5) | `mait campus area in acres`, `mait cse average package`, `mait direct admission`, `mait management quota` | `mait-admission.php` | A | 7 | 13 Nov | p ≤ 6.5 |
| 4 | MSIT | `msit janakpuri` (1.7k, 9.1) | `msit cse average package`, `msit management quota fees`, `msit direct admission` | `msit-admission.php` | A | 7 | 13 Nov | p ≤ 7.5 |
| 5 | USICT | `usict` (5.5k, 8.9) | `usict cse average package`, `usict placement 2026`, `usict cutoff`, `usict delhi admission process` | `usict-admission.php` | A | 7 | 13 Nov | p ≤ 7.5 |
| 6 | USAR | `usar` (9.4k, 9.1) | `usar delhi`, `usar fees structure`, `usar cutoff`, `usar placement 2026` | `usar-admission.php` | A then C | 7, 10 | 13 Nov / 1 Dec | p ≤ 8, CTR ≥ 1% |
| 7 | USMS | `usms ipu` (3.5k, 8.0) | `usms mba fees`, `usms bba fees`, `usms bcom hons fees` | `usms-admission.php` | A | 7 | 13 Nov | p ≤ 6.5 |
| 8 | USLLS | `uslls ba llb fees` (844, 6.2) | `usls`, `uslls cutoff`, `uslls fees`, `usls ipu` | `usls-admission.php` | A | 7 | 13 Nov | p ≤ 5.5 |
| 9 | ADGITM | `akhilesh das gupta institute` (948, 5.0) | `adgitm`, `adgitm fees`, `adgitm cutoff`, `adgitm delhi` | `adgitm-admission.php` | A then C | 7, 10 | 13 Nov / 1 Dec | CTR ≥ 1% |
| 10 | BPIT | `bpit` (4.6k, 8.8) | `bpit fees`, `bpit fees btech cse`, `bpit cutoff` | `BPIT.php` | A | 7 | 27 Nov | p ≤ 7.5 |
| 11 | BVP | `bvp college` (1.6k, 9.6) | `bvcoe`, `is bharati vidyapeeth under ipu`, `bvp college fees` | `BVP.php` | A | 7 | 27 Nov | p ≤ 8 |
| 12 | VIPS | `vips college fees` (4.3k, 6.6) | `vips bba fees`, `vips bcom hons fees`, `vips management quota`, `vips direct admission` | `vips-admission.php` | A | 7 | 27 Nov | p ≤ 5.5 |
| 13 | MBS Dwarka | `mbs college dwarka` (new) | `mbs college fees`, `mbs college btech`, `mbs school of planning and architecture`, `ipu colleges in dwarka` | new `mbs-college-admission.php` + new `ipu-colleges-in-dwarka.php` | B | 5 | 27 Nov | indexed, p ≤ 10 |
| 14 | B.Tech | `ipu btech` (2.2k, 9.5) | `ipu btech fees`, `ipu colleges for btech`, `ipu btech cutoff` | `IPU-B-Tech-admission-2026.php` (A), `ipu-fees-structure.php` anchors (A) | A | 8 | 27 Nov | p ≤ 7.5 |
| 15 | BBA | `ipu bba fees` (1.6k, 7.1) | `ggsipu bba fees`, `maims bba fees`, `ip university bba fees` | BBA guide + `ipu-fees-structure.php` | A | 8 | 27 Nov | p ≤ 6 |
| 16 | B.Com Hons | `ipu bcom hons fees` (729, 4.7) | `vips bcom hons fees`, `ipu bcom fees` | `bcom-admission-ipu.php` | A | 8 | 27 Nov | hold p ≤ 4.7 |
| 17 | MBA | `ipu mba` (544, 8.7) | `ipu mba fees`, `ggsipu mba fees`, `ipu mba admission process` | `mba-admission-ip-university.php` (A); `mba-management-quota-ipu.php` (rewrite) | A/B | 8, 9 | 27 Nov / 15 Jan | p ≤ 7; mgmt-quota page p ≤ 15 |
| 18 | BA LLB | `ipu ba llb counselling 2026` (371, 8.9) | `ipu ba llb fees`, `uslls ba llb fees`, `ggsipu ba llb admission` | `ultimate-guide-to-ballb-admission-in-ip-university.php` | A | 8 | 27 Nov | p ≤ 6 |
| 19 | BBA LLB | `ggsipu bba llb fees` (266, 4.8 via another page) | `ipu bba llb admission`, `bba llb fees ipu` | `comprehensive-guide-to-bballb-admission-in-ip-university.php` | A then C (bug fix) | 9, 11 | 15 Jan | guide p ≤ 8 |
| 20 | LLB 3-year | `vips 3 year llb fees` (278, 4.2) | `ipu llb admission`, `ipu 3 year llb fees` | `law-3-year-admission-ipu.php` | A | 8 | 27 Nov | hold |
| 21 | Law general | `ipu law colleges` (182, 8.2) | `ipu law college`, `ggsipu law colleges` | `top-law-colleges-ipu.php`, `IPU-Law-Admission.php` | A | 8 | 27 Nov | p ≤ 6 |
| 22 | Homepage | `ipu` (707k, 8.5, CTR 0.16%) | `ggsipu`, `ip university`, `ipu admission` | `index.php` | C | 10 | 1 Dec | CTR ≥ 0.25% (position unlikely to move; official sites outrank) |

LLM already has a page (`llm-admission-ipu.php`, live 200), so the spec's "LLM B candidate" is dropped; LLM only gets tier-A fee FAQs in Task 8.

## Calendar

| Window | What happens | Gate |
|---|---|---|
| 1 – 9 Oct | Task 1 (verifier), Task 2 (diagnostics), Task 3 (watch terms, Ads negatives, pre-flight deploys: CTA Wave 1 + Phase 2) | Sumit go for the two held deploys |
| 12 – 30 Oct | Task 4 (2026 cutoff ingest, cutoff template, MAIT + MSIT pages). **Deploy batch 1: 30 Oct.** | Sumit supplies the 2026 round 1/2/3 cutoff text by **16 Oct** |
| 2 – 13 Nov | Task 6 (college facts dataset), Task 7 college sections, first half. **Deploy batch 2: 13 Nov.** | Sumit supplies brochure path by **2 Nov**; fact rows with sources |
| 16 – 27 Nov | Task 5 (MBS + Dwarka hub), Task 7 second half, Task 8 fee FAQs. **Deploy batch 3: 27 Nov.** | MBS fees/seats sourced |
| 1 Dec | **Tier-C title tests go live** (USAR, ADGITM, homepage), one deploy, Tasks 10 | Sumit approves each title by 27 Nov |
| 15 Dec / 29 Dec | Stop-loss rechecks (Task 3 script) | revert any watch term that dropped more than 2 positions |
| 5 – 15 Jan | Keep/revert decisions; Task 9 (MBA mgmt-quota rewrite, BBA LLB guide), Task 11 (BBA LLB title bug fix, if approved). **Deploy batch 4: 15 Jan = content freeze.** | Sumit approves BBA LLB title fix |
| 18 Jan – 12 Feb | Soak, Task 12 (sitemap, llms.txt, internal-link audit), final QA. GSC sitemap resubmit 1 Feb. No content changes. | none |
| 1 Feb – 1 Mar | Ads pre-season rebuild (Task 13, done by Sumit in the Ads UI) | Sumit |
| Mar – Jun | Season watch: weekly recheck (Task 3 script), counselling date updates only | none |

Why these dates: GSC impressions climb from Feb (51k) through Mar (86k) and Apr (116k), then explode in May (367k). Google needs about 4 weeks to settle a change, and a stop-loss recheck needs 2 and 4 weeks of data, so the last risky edit has to land by mid-Jan. Oct to Jan is the cheapest window to be wrong: last year those months earned 57–197 clicks.

## File structure

| File | Responsibility |
|---|---|
| `scripts/seo_verify.py` (new) | Structural SEO checks for any page, local or live |
| `tests/test_seo_verify.py` (new) | pytest for the verifier |
| `seo/scripts/watch_terms.py` (new) | Build the watch-terms baseline and compare a later GSC export against it |
| `tests/test_watch_terms.py` (new) | pytest for the comparer |
| `seo/baselines/2026-09-30-keyword-map-watch-terms.csv` (generated) | The watch list plus baseline impressions and position |
| `docs/audits/2026-10-keyword-diagnostics.md` (new) | Findings from Task 2 |
| `docs/ads/2026-10-negatives.csv` (new) | Ads negative-keyword list for Sumit to apply |
| `data/btech-cutoff-2026/` (new) | Raw 2026 round files, parser copies |
| `website_download/include/data/btech-cutoffs-2026.php` (generated) | 2026 cutoff data |
| `website_download/include/components/college-cutoff-page.php` (new) | Shared template for college cutoff pages |
| `website_download/mait-cutoff.php`, `msit-cutoff.php` (new) | Two thin page files that set `$cp` and include the template |
| `website_download/include/data/college-facts-2026.json` (new) | Sourced facts per college (campus area, packages, fees) |
| `website_download/include/components/college-facts-faq.php` (new) | Appends FAQ entries from the facts file, only for sourced fields |
| `website_download/mbs-college-admission.php`, `ipu-colleges-in-dwarka.php` (new) | MBS page and Dwarka hub |

---

### Task 1: SEO verifier script

**Files:**
- Create: `scripts/seo_verify.py`
- Test: `tests/test_seo_verify.py`

**Interfaces:**
- Produces: `check_html(html: str, strict_faq_phone: bool = False) -> list[str]` (empty list = pass); CLI `python3 scripts/seo_verify.py --base URL [--strict-faq-phone] PATH...` exiting 1 on any problem.

- [ ] **Step 1: Write the failing tests**

```python
# tests/test_seo_verify.py
import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from seo_verify import check_html

GOOD = """<html><head><title>MAIT Cutoff – JEE Main Closing Ranks by Branch</title>
<link rel="canonical" href="https://ipu.co.in/mait-cutoff.php">
<meta name="description" content="MAIT cutoff by branch and round.">
<script type="application/ld+json">{"@type":"BreadcrumbList","itemListElement":[]}</script>
<script type="application/ld+json">{"@type":"FAQPage","mainEntity":[
{"@type":"Question","name":"Q1","acceptedAnswer":{"@type":"Answer","text":"A1"}},
{"@type":"Question","name":"Q2","acceptedAnswer":{"@type":"Answer","text":"A2"}},
{"@type":"Question","name":"Q3","acceptedAnswer":{"@type":"Answer","text":"A3"}}]}</script>
</head><body><h1>MAIT Cutoff</h1><div id="enquiry-form"></div>
<a href="tel:+919899991342">Call</a></body></html>"""

def test_good_page_passes():
    assert check_html(GOOD) == []

def test_two_h1_fails():
    assert any("h1" in p for p in check_html(GOOD.replace("</body>", "<h1>x</h1></body>")))

def test_missing_canonical_fails():
    bad = GOOD.replace('<link rel="canonical" href="https://ipu.co.in/mait-cutoff.php">', "")
    assert any("canonical" in p for p in check_html(bad))

def test_two_descriptions_fail():
    bad = GOOD.replace("</head>", '<meta name="description" content="dup"></head>')
    assert any("meta description" in p for p in check_html(bad))

def test_two_forms_fail():
    assert any("enquiry" in p for p in check_html(GOOD.replace("</body>", '<div id="enquiry-form"></div></body>')))

def test_two_breadcrumbs_fail():
    bc = '<script type="application/ld+json">{"@type":"BreadcrumbList"}</script>'
    assert any("BreadcrumbList" in p for p in check_html(GOOD.replace("</head>", bc + "</head>")))

def test_bad_json_ld_fails():
    bad = GOOD.replace("</head>", '<script type="application/ld+json">{oops}</script></head>')
    assert any("JSON-LD" in p for p in check_html(bad))

def test_bare_tel_fails():
    assert any("tel:" in p for p in check_html(GOOD.replace("tel:+919899991342", "tel:9899991342")))

def test_phone_in_faq_only_fails_when_strict():
    bad = GOOD.replace('"text":"A1"', '"text":"Call 9899991342"')
    assert check_html(bad) == []
    assert any("FAQ" in p for p in check_html(bad, strict_faq_phone=True))

def test_short_faq_fails():
    bad = GOOD.replace('{"@type":"Question","name":"Q3","acceptedAnswer":{"@type":"Answer","text":"A3"}}',
                       '{"@type":"Question","name":"Q2b","acceptedAnswer":{"@type":"Answer","text":"A2b"}}').replace(
                       ',\n{"@type":"Question","name":"Q2","acceptedAnswer":{"@type":"Answer","text":"A2"}}', "")
    assert any("FAQPage" in p for p in check_html(bad))
```

- [ ] **Step 2: Run tests, confirm they fail**

Run: `cd /Users/Sumit/test-project && python3 -m pytest tests/test_seo_verify.py -q`
Expected: FAIL (`ModuleNotFoundError: seo_verify`)

- [ ] **Step 3: Implement**

```python
#!/usr/bin/env python3
"""Structural SEO checks for ipu.co.in pages.

Usage:
    python3 scripts/seo_verify.py --base http://localhost:8000 mait-cutoff.php msit-cutoff.php
    python3 scripts/seo_verify.py --base https://ipu.co.in --strict-faq-phone /mbs-college-admission.php
Exit code 1 if any page has a problem.
"""
from __future__ import annotations
import argparse, json, re, sys, urllib.request

PHONE = "9899991342"
LD_RE = re.compile(r'<script[^>]+type="application/ld\+json"[^>]*>(.*?)</script>', re.S | re.I)


def _walk(node):
    if isinstance(node, dict):
        yield node
        for v in node.values():
            yield from _walk(v)
    elif isinstance(node, list):
        for v in node:
            yield from _walk(v)


def check_html(html: str, strict_faq_phone: bool = False) -> list[str]:
    problems: list[str] = []
    n = len(re.findall(r"<h1[\s>]", html, re.I))
    if n != 1:
        problems.append(f"h1 count {n} != 1")
    n = len(re.findall(r'<link[^>]+rel="canonical"', html, re.I))
    if n != 1:
        problems.append(f"canonical count {n} != 1")
    n = len(re.findall(r'<meta[^>]+name="description"', html, re.I))
    if n != 1:
        problems.append(f"meta description count {n} != 1")
    n = len(re.findall(r'id="enquiry-form"', html))
    if n > 1:
        problems.append(f"enquiry form count {n} > 1")
    if re.search(r"tel:(?!\+91)", html):
        problems.append("bare tel: link (must be tel:+91...)")

    breadcrumbs = 0
    for block in LD_RE.findall(html):
        try:
            data = json.loads(block)
        except json.JSONDecodeError:
            problems.append("invalid JSON-LD block")
            continue
        for node in _walk(data):
            t = node.get("@type")
            if t == "BreadcrumbList":
                breadcrumbs += 1
            if t == "FAQPage":
                qs = [q for q in node.get("mainEntity", []) if q.get("@type") == "Question"]
                if len(qs) < 3:
                    problems.append(f"FAQPage has {len(qs)} questions (< 3)")
                if strict_faq_phone:
                    for q in qs:
                        if PHONE in q.get("acceptedAnswer", {}).get("text", ""):
                            problems.append("phone number inside FAQ answer")
                            break
    if breadcrumbs > 1:
        problems.append(f"BreadcrumbList count {breadcrumbs} > 1")
    return problems


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", required=True)
    ap.add_argument("--strict-faq-phone", action="store_true")
    ap.add_argument("paths", nargs="+")
    args = ap.parse_args()
    failed = 0
    for p in args.paths:
        url = args.base.rstrip("/") + "/" + p.lstrip("/")
        try:
            with urllib.request.urlopen(url, timeout=20) as r:
                status, html = r.status, r.read().decode("utf-8", "replace")
        except Exception as e:  # network/HTTP error is a failure, report and continue
            print(f"FAIL {url}: {e}")
            failed += 1
            continue
        problems = check_html(html, args.strict_faq_phone)
        if status != 200:
            problems.insert(0, f"HTTP {status}")
        if re.search(r"(Fatal error|Parse error|Warning:|Notice:)", html):
            problems.append("PHP error text in output")
        print(("FAIL " if problems else "OK   ") + url + ("  " + "; ".join(problems) if problems else ""))
        failed += bool(problems)
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
```

- [ ] **Step 4: Run tests, confirm they pass**

Run: `python3 -m pytest tests/test_seo_verify.py -q`
Expected: 10 passed

- [ ] **Step 5: Commit**

```bash
git add scripts/seo_verify.py tests/test_seo_verify.py
git commit -m "feat(seo): structural page verifier + tests" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 2: Diagnostics before any edit

**Files:**
- Create: `docs/audits/2026-10-keyword-diagnostics.md`
- Modify: `docs/superpowers/specs/2026-09-30-ipu-keyword-map-design.md` (§3 law row, §7 item 2)

**Already established (2026-09-30, curl and file checks; record them in the audit):**
- `mait-cutoff.php`, `msit-cutoff.php`, `mbs-college-admission.php`, `ipu-colleges-in-dwarka.php` all 404 on prod; `llm-admission-ipu.php` is 200 (LLM page exists).
- `?q=<junk>` on the homepage returns 200 with `rel="canonical" href="https://ipu.co.in/"` and the homepage title, so junk parameter URLs are canonicalised and are low risk.
- `mba-management-quota-ipu.php` is only 8,166 bytes and has just 2 inbound references (itself and `btech-management-quota-ipu.php`); that thinness plus poor linking explains position 30.7.
- `comprehensive-guide-to-bballb-admission-in-ip-university.php` carries TWO `meta name="description"` tags and a title ending in "Meta", and is 14.5 KB. That is a template bug and probably explains position 13.

- [ ] **Step 1: Re-verify the four facts live**

Run:
```bash
for u in mait-cutoff.php msit-cutoff.php mbs-college-admission.php ipu-colleges-in-dwarka.php llm-admission-ipu.php; do echo "$u $(curl -s -o /dev/null -w '%{http_code}' https://ipu.co.in/$u)"; done
curl -s "https://ipu.co.in/?q=zzqxtest123" | grep -o 'rel="canonical"[^>]*'
grep -c 'name="description"' website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php
wc -c website_download/mba-management-quota-ipu.php
grep -l 'mba-management-quota-ipu' website_download/*.php
```
Expected: four 404s then one 200; canonical `https://ipu.co.in/`; description count 2; size about 8166; the grep lists 2 files.

- [ ] **Step 2: Check the `blog-detail.php?url=` legacy pages that still earn clicks**

Run: `grep -E 'blog-detail.php\?url=' seo/baselines/2026-09-30-gsc-export/search/Pages.csv | sort -t, -k2 -nr | head -5`
Expected: the BJMC guide (198 clicks) on top. For each, record whether an equivalent `.php` page exists (`ls website_download | grep -i bjmc`) and whether `.htaccess` already redirects it (`grep -i 'blog-detail' website_download/htaccess`). Record only; redirects are a separate task.

- [ ] **Step 3: Write `docs/audits/2026-10-keyword-diagnostics.md`** with the four established facts, the Step 1 and 2 output, and these decisions: (a) MBA management-quota page gets a full rewrite and 4 inbound links in Task 9; (b) BBA LLB guide duplicate description is fixed in Task 11 (tier C, needs approval) and its body is extended in Task 9; (c) junk-parameter URLs need no action.

- [ ] **Step 4: Correct the spec.** In the spec's §3 Law (general) row remove "B candidate: year-free LLM admission page" and write "LLM page exists (`llm-admission-ipu.php`); A: fee FAQ only." In §7 replace item 2 with "Confirmed 2026-09-30: no mait-cutoff, msit-cutoff or MBS page exists; LLM page exists."

- [ ] **Step 5: Commit**

```bash
git add docs/audits/2026-10-keyword-diagnostics.md docs/superpowers/specs/2026-09-30-ipu-keyword-map-design.md
git commit -m "docs(seo): keyword-map diagnostics; correct LLM and page-existence claims" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 3: Watch terms, Ads negatives, pre-flight deploys

**Files:**
- Create: `seo/scripts/watch_terms.py`, `tests/test_watch_terms.py`, `docs/ads/2026-10-negatives.csv`
- Generate: `seo/baselines/2026-09-30-keyword-map-watch-terms.csv`

**Interfaces:**
- Produces: `WATCH: list[str]`; `build_baseline(queries_csv, out_csv)`; `compare(baseline_csv, new_queries_csv, max_drop=2.0) -> list[dict]` (each dict: `term`, `base_pos`, `new_pos`, `drop`). CLI: `python3 seo/scripts/watch_terms.py build|compare ...`.

- [ ] **Step 1: Write the failing tests**

```python
# tests/test_watch_terms.py
import csv, sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "seo" / "scripts"))
import watch_terms as wt

def _q(path, rows):
    with open(path, "w", newline="") as f:
        w = csv.writer(f)
        w.writerow(["Top queries", "Clicks", "Impressions", "CTR", "Position"])
        for r in rows:
            w.writerow(r)

def test_build_and_compare_flags_only_drops_over_two(tmp_path):
    base_q, new_q, base = tmp_path / "b.csv", tmp_path / "n.csv", tmp_path / "base.csv"
    _q(base_q, [["mait cutoff", 11, 3741, "0.3%", 8.87], ["usar", 48, 9384, "0.5%", 9.13]])
    _q(new_q, [["mait cutoff", 20, 3000, "0.7%", 12.0], ["usar", 50, 9000, "0.6%", 8.0]])
    wt.WATCH[:] = ["mait cutoff", "usar", "not in export"]
    wt.build_baseline(base_q, base)
    bad = wt.compare(base, new_q)
    assert [b["term"] for b in bad] == ["mait cutoff"]
    assert round(bad[0]["drop"], 2) == 3.13

def test_missing_term_is_not_flagged(tmp_path):
    base_q, new_q, base = tmp_path / "b.csv", tmp_path / "n.csv", tmp_path / "base.csv"
    _q(base_q, [["usar", 48, 9384, "0.5%", 9.13]])
    _q(new_q, [])
    wt.WATCH[:] = ["usar"]
    wt.build_baseline(base_q, base)
    assert wt.compare(base, new_q) == []
```

- [ ] **Step 2: Run, confirm fail**

Run: `python3 -m pytest tests/test_watch_terms.py -q` → FAIL (`ModuleNotFoundError: watch_terms`)

- [ ] **Step 3: Implement**

```python
#!/usr/bin/env python3
"""Watch-terms baseline and stop-loss comparer for the keyword-map programme.

  python3 seo/scripts/watch_terms.py build  <queries.csv> <out.csv>
  python3 seo/scripts/watch_terms.py compare <baseline.csv> <new-queries.csv>
A term is flagged when its average position worsens by more than 2.0 versus baseline.
"""
from __future__ import annotations
import csv, sys

WATCH = [
    "ipu", "ggsipu", "ip university", "ipu counselling", "ggsipu counselling",
    "mait", "mait cutoff", "mait cutoff 2026", "mait fees", "msit", "msit cutoff",
    "msit cutoff 2026", "msit janakpuri", "usict", "usict cutoff", "usict placement 2026",
    "usict mca fees", "usar", "usar delhi", "usar cutoff 2025", "usms ipu", "usms mba fees",
    "uslls ba llb fees", "adgitm", "bpit", "bpit fees", "bvp college", "vips", "vips college fees",
    "vips bba fees", "ipu btech", "ipu btech fees", "ipu colleges for btech", "ipu bba fees",
    "ggsipu bba fees", "ipu bcom hons fees", "ipu mba", "ipu mba fees", "ipu ba llb fees",
    "ipu ba llb counselling 2026", "ggsipu bba llb fees", "vips 3 year llb fees",
    "ipu law colleges", "ipu law college", "ipu colleges list", "ipu helpline number",
]


def _read(path):
    with open(path, newline="", encoding="utf-8") as f:
        return {r["Top queries"].strip().lower(): r for r in csv.DictReader(f)}


def build_baseline(queries_csv, out_csv) -> None:
    rows = _read(queries_csv)
    with open(out_csv, "w", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        w.writerow(["term", "impressions", "clicks", "position"])
        for t in WATCH:
            r = rows.get(t)
            if r:
                w.writerow([t, r["Impressions"], r["Clicks"], r["Position"]])


def compare(baseline_csv, new_queries_csv, max_drop: float = 2.0) -> list[dict]:
    new = _read(new_queries_csv)
    out = []
    with open(baseline_csv, newline="", encoding="utf-8") as f:
        for r in csv.DictReader(f):
            n = new.get(r["term"])
            if not n:
                continue
            drop = float(n["Position"]) - float(r["position"])
            if drop > max_drop:
                out.append({"term": r["term"], "base_pos": float(r["position"]),
                            "new_pos": float(n["Position"]), "drop": drop})
    return out


if __name__ == "__main__":
    if len(sys.argv) == 4 and sys.argv[1] == "build":
        build_baseline(sys.argv[2], sys.argv[3])
    elif len(sys.argv) == 4 and sys.argv[1] == "compare":
        bad = compare(sys.argv[2], sys.argv[3])
        for b in bad:
            print(f"REVERT? {b['term']}: {b['base_pos']:.2f} -> {b['new_pos']:.2f} (+{b['drop']:.2f})")
        sys.exit(1 if bad else 0)
    else:
        print(__doc__)
        sys.exit(2)
```

- [ ] **Step 4: Run tests, confirm pass**

Run: `python3 -m pytest tests/test_watch_terms.py -q` → 2 passed

- [ ] **Step 5: Generate the baseline**

Run:
```bash
python3 seo/scripts/watch_terms.py build seo/baselines/2026-09-30-gsc-export/search/Queries.csv seo/baselines/2026-09-30-keyword-map-watch-terms.csv
wc -l seo/baselines/2026-09-30-keyword-map-watch-terms.csv
```
Expected: 30 or more rows (terms missing from the top-1000 export are skipped).

- [ ] **Step 6: Write `docs/ads/2026-10-negatives.csv`** (for Sumit to apply in the Ads UI; Search campaigns only):

```csv
negative_keyword,match_type,reason
ipu cet 2024,phrase,old year; 204 rupees spent no conversion
ipu ac nic,phrase,portal seekers; 109 rupees no conversion
ipu ac in ipu admissions nic in,phrase,portal seekers; 85 rupees no conversion
indraprastha university registration 2026,exact,registration seekers; 141 rupees no conversion
ipu university registration 2026,exact,registration seekers; 88 rupees no conversion
guru gobind singh indraprastha university counselling form,exact,form seekers; 105 rupees no conversion
ip university counselling form,exact,form seekers; 82 rupees no conversion
ggsipu form,exact,form seekers; 69 rupees no conversion
```
`maharaja agrasen institute` (₹77, no conversion) is deliberately NOT negated: it can match MAIT searches that do convert. Review it in the Ads UI with device and time context first.

- [ ] **Step 7: Commit**

```bash
git add seo/scripts/watch_terms.py tests/test_watch_terms.py seo/baselines/2026-09-30-keyword-map-watch-terms.csv docs/ads/2026-10-negatives.csv
git commit -m "feat(seo): watch-terms baseline + stop-loss comparer; Ads negatives list" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 8: Pre-flight deploys (Sumit's go required, outward-facing).** Execute the two existing plans in this order, each as its own deploy with its own live check: (1) CTA Wave 1 per `docs/superpowers/plans/2026-07-26-ipu-cta-system.md` (5 files, NOT `base-head.php`); (2) Phase 2 per `docs/superpowers/plans/2026-06-26-ipu-phase-2-speed-mobile.md` (27 files, includes the intentional `base-head.php`), with PSI before and after. Do them before Task 4 so later deploys are against a stable base. Record outcomes in `docs/audits/2026-10-keyword-diagnostics.md`.

---

### Task 4: 2026 cutoff data, cutoff template, MAIT and MSIT pages

**Files:**
- Create: `data/btech-cutoff-2026/` (copy of the 2025 scripts), `website_download/include/data/btech-cutoffs-2026.php` (generated), `website_download/include/components/college-cutoff-page.php`, `website_download/mait-cutoff.php`, `website_download/msit-cutoff.php`
- Modify: `website_download/mait-admission.php`, `website_download/msit-admission.php` (related-pages entry only), `data/btech-cutoff-2025/json_to_php.py` is left alone (copy instead)

**Interfaces:**
- Consumes: `include/data/btech-cutoffs-2025.php` schema `[institute][branch][round_N] = ['delhi'=>['min','max'], 'outside'=>['min','max']]`; `include/components/{breadcrumb-schema,hero-banner,sidebar-enquiry,cta-strip,faq-section,related-pages}.php`.
- Produces: template contract. A page file sets `$cp = ['slug','short','institute','title','meta','h1','intro','published','related']` then `include __DIR__.'/include/components/college-cutoff-page.php'`. The template renders the whole document through `</html>`.

**Input gate:** Sumit supplies the official GGSIPU 2026 B.Tech round 1/2/3 closing-rank text in the same paste format as `data/btech-cutoff-2025/round-1.txt` (columns: Name of Institute, Branch, Delhi, Outside Delhi; cells like `Min Rank - 62808 Max Rank - 142454`) by **16 Oct**. If 2026 data is not available by then, build the pages on 2025 data only (Step 6 variant) and add 2026 later.

- [ ] **Step 1: Ingest 2026 data (only when files arrive)**

Run:
```bash
cd /Users/Sumit/test-project
mkdir -p data/btech-cutoff-2026
cp data/btech-cutoff-2025/parse.py data/btech-cutoff-2025/json_to_php.py data/btech-cutoff-2026/
# place round-1.txt round-2.txt round-3.txt (2026) into data/btech-cutoff-2026/
sed -i '' 's/btech-cutoffs-2025.php/btech-cutoffs-2026.php/' data/btech-cutoff-2026/json_to_php.py
python3 data/btech-cutoff-2026/parse.py && python3 data/btech-cutoff-2026/json_to_php.py
php -l website_download/include/data/btech-cutoffs-2026.php
php -r '$d = include "website_download/include/data/btech-cutoffs-2026.php"; foreach (["Maharaja Agrasen Institute of Technology","Maharaja Surajmal Institute Technology"] as $k) echo $k, " => ", isset($d[$k]) ? count($d[$k])." branches" : "MISSING", PHP_EOL;'
```
Expected: `No syntax errors`; both institutes print a branch count. If an institute prints MISSING, fix the key from the raw file's exact spelling before continuing. In the copied `json_to_php.py`, also edit the header comment line to say 2026-27.

- [ ] **Step 2: Write the template** `website_download/include/components/college-cutoff-page.php`:

```php
<?php
/**
 * College cutoff page template.
 * Page file sets $cp = ['slug','short','institute','title','meta','h1','intro','published','related'] first.
 * Renders the full document (head through footer). Fails loudly (HTTP 500) if the institute has no data,
 * so a mistyped key can never ship a silently thin page.
 */
$cp = $cp ?? null;
if (!$cp) { http_response_code(500); exit('college-cutoff-page: $cp not set'); }

$load = function ($file) { return is_file($file) ? include $file : []; };
$data25 = $load(__DIR__ . '/../data/btech-cutoffs-2025.php');
$data26 = $load(__DIR__ . '/../data/btech-cutoffs-2026.php');
$rows25 = $data25[$cp['institute']] ?? [];
$rows26 = $data26[$cp['institute']] ?? [];
if (!$rows25 && !$rows26) {
    error_log('college-cutoff-page: no cutoff data for ' . $cp['institute']);
    http_response_code(500);
    exit('No cutoff data for this institute');
}
$rows = $rows26 ?: $rows25;              // newest available year drives the summary
$data_year = $rows26 ? '2026' : '2025';

$fmt = fn($n) => number_format((int) $n);
// Overall closing-rank span for a branch across rounds 1-3: best (lowest) min to worst (highest) max.
$span = function (array $b, string $quota) {
    $mins = $maxs = [];
    foreach (['round_1', 'round_2', 'round_3'] as $r) {
        if (isset($b[$r][$quota]['min'])) { $mins[] = $b[$r][$quota]['min']; $maxs[] = $b[$r][$quota]['max']; }
    }
    return $mins ? [min($mins), max($maxs)] : null;
};

$url = 'https://ipu.co.in/' . $cp['slug'];
$cse = $rows['Computer Science & Engineering'] ?? null;

$faqs = [];
if ($cse && ($s = $span($cse, 'delhi'))) {
    $faqs[] = ['question' => "What is the {$cp['short']} cutoff for CSE?",
        'answer' => "In the {$data_year} GGSIPU counselling, CSE at {$cp['short']} closed between JEE Main rank " . $fmt($s[0]) . " and " . $fmt($s[1]) . " for Delhi-quota General candidates across rounds 1 to 3. The lower number is the tightest seat in any round; the higher number is where the last seat closed."];
}
if ($cse && ($s = $span($cse, 'outside'))) {
    $faqs[] = ['question' => "What is the {$cp['short']} cutoff for students from outside Delhi?",
        'answer' => "For outside-Delhi candidates, CSE at {$cp['short']} closed between rank " . $fmt($s[0]) . " and " . $fmt($s[1]) . " in the {$data_year} counselling. Outside-Delhi ranks are much tighter because far fewer seats are reserved for them."];
}
$faqs[] = ['question' => "How do I read Min Rank and Max Rank in the {$cp['short']} cutoff table?",
    'answer' => "Min Rank is the best (lowest) JEE Main rank that received a seat in that round; Max Rank is the last rank that received one. If your rank is below the Max Rank for your branch and quota in an earlier round, you had a realistic chance."];
$faqs[] = ['question' => "Is the {$cp['short']} cutoff the same every year?",
    'answer' => "No. Closing ranks move with the number of candidates, seat matrix and branch demand. Use these figures to compare branches and rounds, then check the latest round notice before filling choices."];
$faqs[] = ['question' => "Does {$cp['short']} offer management-quota seats?",
    'answer' => "Some IPU-affiliated engineering colleges fill part of their intake under management or sponsored quota outside the JEE Main merit list. Read our management-quota guide for how the process works."];

$related_pages = $cp['related'];
$breadcrumbs = [['Home', '/'], ['B.Tech Admission', '/IPU-B-Tech-admission-2026.php'], [$cp['short'] . ' Cutoff', '']];
$ld_article = json_encode([
    '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $cp['h1'],
    'description' => $cp['meta'],
    'author' => ['@type' => 'Organization', 'name' => 'IPU Admission Guide'],
    'publisher' => ['@type' => 'Organization', 'name' => 'IPU Admission Guide', 'url' => 'https://ipu.co.in'],
    'datePublished' => $cp['published'], 'dateModified' => $cp['published'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

include_once __DIR__ . '/../base-head.php';
?>
<title><?= htmlspecialchars($cp['title']) ?></title>
<meta name="description" content="<?= htmlspecialchars($cp['meta']) ?>">
<link rel="canonical" href="<?= $url ?>">
<meta property="og:title" content="<?= htmlspecialchars($cp['title']) ?>">
<meta property="og:description" content="<?= htmlspecialchars($cp['meta']) ?>">
<meta property="og:url" content="<?= $url ?>">
<meta property="og:type" content="article">
<meta property="og:site_name" content="IPU Admission Guide">
<script type="application/ld+json"><?= $ld_article ?></script>
<?php include __DIR__ . '/breadcrumb-schema.php'; ?>
</head>
<body>
<?php include_once __DIR__ . '/../base-nav.php'; ?>
<?php
$hero_title = $cp['h1'];
$hero_breadcrumbs = $breadcrumbs;
$hero_compact = true;
include __DIR__ . '/hero-banner.php';
?>
<section style="padding:50px 0">
<div class="container"><div class="row"><div class="col-lg-8">

  <section id="ai-summary" style="background:#f0f7ff;border-left:4px solid #1a3a9c;padding:20px 24px;border-radius:0 8px 8px 0;margin-bottom:32px">
    <p style="font-weight:700;color:#0d1b6e;margin-bottom:8px">AI Summary</p>
    <p style="margin:0;color:#4a5568;font-size:15px"><?= htmlspecialchars($cp['intro']) ?></p>
  </section>

  <p style="background:#fff8e1;border-left:4px solid #f59e0b;padding:14px 18px;border-radius:0 8px 8px 0;font-size:14px;color:#4a5568">
    <strong>Note:</strong> figures are General category, Delhi (home-state) and Outside-Delhi quota closing ranks from GGSIPU counselling rounds 1 to 3. Category-wise cutoffs are more relaxed.
  </p>

  <h2 style="font-size:1.5rem;color:#0d1b6e">Branch-wise closing ranks, <?= $data_year ?> (all rounds)</h2>
  <div style="overflow-x:auto;border:1px solid #e2e8f0;border-radius:8px">
    <table style="width:100%;min-width:560px;border-collapse:collapse;font-size:14px">
      <thead><tr style="background:#0d1b6e;color:#fff"><th style="padding:10px;text-align:left">Branch</th><th style="padding:10px;text-align:center">Delhi quota</th><th style="padding:10px;text-align:center">Outside Delhi</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $branch => $rounds): $d = $span($rounds, 'delhi'); $o = $span($rounds, 'outside'); ?>
        <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px"><?= htmlspecialchars($branch) ?></td>
          <td style="padding:10px;text-align:center"><?= $d ? $fmt($d[0]) . ' – ' . $fmt($d[1]) : '—' ?></td>
          <td style="padding:10px;text-align:center"><?= $o ? $fmt($o[0]) . ' – ' . $fmt($o[1]) : '—' ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <p style="font-size:13px;color:#64748b;margin-top:8px">Source: GGSIPU <?= $data_year ?> B.Tech counselling, rounds 1, 2 and 3. Read the ranks as Min – Max JEE Main rank.</p>

  <p>Planning your choice list? Call <a href="tel:+919899991342"><strong>9899991342</strong></a> for free rank guidance.</p>

</div>
<div class="col-lg-4"><?php include __DIR__ . '/sidebar-enquiry.php'; ?></div>
</div></div>
</section>

<?php
// Round-by-round detail table (2025 data component); renders nothing if the institute key is absent.
$cutoff_institute = $cp['institute'];
include __DIR__ . '/btech-cutoff-rounds-table.php';
$cta_heading = "Need Help with {$cp['short']} Cutoff Analysis?";
$cta_subtext = "Get free rank analysis and a realistic choice-filling plan";
include __DIR__ . '/cta-strip.php';
include __DIR__ . '/faq-section.php';
include __DIR__ . '/related-pages.php';
include_once __DIR__ . '/../base-footer.php';
?>
</body>
</html>
```

- [ ] **Step 3: Write the two page files**

`website_download/mait-cutoff.php`:
```php
<?php session_cache_limiter('public'); session_cache_expire(30); session_start(); ob_start(); include_once("include/form-handler.php");
$cp = [
  'slug' => 'mait-cutoff.php',
  'short' => 'MAIT',
  'institute' => 'Maharaja Agrasen Institute of Technology',
  'title' => 'MAIT Cutoff – JEE Main Closing Ranks by Branch & Round (Delhi / Outside)',
  'meta' => 'MAIT Delhi cutoff: JEE Main closing ranks for CSE, IT, ECE and more, Delhi and outside-Delhi quota, rounds 1 to 3. Compare branches and plan your choice filling.',
  'h1' => 'MAIT Cutoff – Branch-wise JEE Main Closing Ranks',
  'intro' => 'Maharaja Agrasen Institute of Technology (MAIT), Rohini, admits B.Tech students through GGSIPU counselling on JEE Main rank. This page shows the closing rank range for each branch, for Delhi-quota and outside-Delhi candidates, across counselling rounds 1 to 3.',
  'published' => '2026-10-30',
  'related' => [
    ['title' => 'MAIT Admission Guide', 'url' => '/mait-admission.php', 'desc' => 'Courses, fees, placements and admission process at MAIT'],
    ['title' => 'IPU B.Tech Cutoff Analysis', 'url' => '/ipu-btech-cutoff-analysis.php', 'desc' => 'Compare closing ranks across all IPU engineering colleges'],
    ['title' => 'B.Tech Management Quota at IPU', 'url' => '/btech-management-quota-ipu.php', 'desc' => 'How management-quota seats work for B.Tech'],
  ],
];
include __DIR__ . '/include/components/college-cutoff-page.php';
```
`website_download/msit-cutoff.php`: identical structure with `'slug' => 'msit-cutoff.php'`, `'short' => 'MSIT'`, `'institute' => 'Maharaja Surajmal Institute Technology'`, title `'MSIT Cutoff – JEE Main Closing Ranks by Branch & Round (Delhi / Outside)'`, meta `'MSIT Janakpuri cutoff: JEE Main closing ranks for CSE, IT, ECE and more, Delhi and outside-Delhi quota, rounds 1 to 3. Compare branches and plan your choice filling.'`, h1 `'MSIT Cutoff – Branch-wise JEE Main Closing Ranks'`, intro `'Maharaja Surajmal Institute of Technology (MSIT), Janakpuri, admits B.Tech students through GGSIPU counselling on JEE Main rank. This page shows the closing rank range for each branch, for Delhi-quota and outside-Delhi candidates, across counselling rounds 1 to 3.'`, related pointing to `/msit-admission.php` ("MSIT Admission Guide"), `/ipu-btech-cutoff-analysis.php`, `/btech-management-quota-ipu.php`.

- [ ] **Step 4: Lint and run locally**

Run:
```bash
cd /Users/Sumit/test-project/website_download
for f in include/components/college-cutoff-page.php mait-cutoff.php msit-cutoff.php; do php -l $f; done
(php -S 127.0.0.1:8123 >/tmp/ipu-php.log 2>&1 &) ; sleep 1
cd .. && python3 scripts/seo_verify.py --base http://127.0.0.1:8123 --strict-faq-phone mait-cutoff.php msit-cutoff.php
```
Expected: `No syntax errors` three times; `OK   http://127.0.0.1:8123/mait-cutoff.php` and the same for msit. If `FAIL`, fix the named problem. Also open `http://127.0.0.1:8123/mait-cutoff.php` in headless Chrome at 375 and 1280 px and confirm the table scrolls horizontally without breaking the page.

- [ ] **Step 5: Prove the fail-loudly behaviour (Review Focus 1)**

Run:
```bash
cd website_download && cp mait-cutoff.php /tmp/mait-cutoff-backup.php
sed "s/Maharaja Agrasen Institute of Technology/Nonexistent Institute/" mait-cutoff.php > _probe-cutoff.php
curl -s -o /dev/null -w '%{http_code}\n' http://127.0.0.1:8123/_probe-cutoff.php; rm -f _probe-cutoff.php
```
Expected: `500`. The probe file is removed. Never commit it.

- [ ] **Step 6: Cross-link (cannibalisation guard, Review Focus 2).** In `mait-admission.php` and `msit-admission.php`, append to the existing `$related_pages` array one entry each: `['title' => 'MAIT Cutoff – Branch-wise Closing Ranks', 'url' => '/mait-cutoff.php', 'desc' => 'JEE Main closing ranks by branch and round']` (and the MSIT equivalent). These two files are already live and untouched by held commits: run the entanglement check (Appendix A step 2) on both before deploying.

- [ ] **Step 7: Commit**

```bash
git add data/btech-cutoff-2026 website_download/include/data/btech-cutoffs-2026.php website_download/include/components/college-cutoff-page.php website_download/mait-cutoff.php website_download/msit-cutoff.php website_download/mait-admission.php website_download/msit-admission.php
git commit -m "feat(seo): MAIT and MSIT cutoff pages from shared data-driven template" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
(If 2026 data is not ready, omit its two paths from `git add`.)

- [ ] **Step 8: Add to sitemap, then deploy batch 1 on 30 Oct** per Appendix A (files: the two new pages, the template, both data files, the two admission pages, sitemap).

---

### Task 5: MBS College page and Dwarka hub

**Files:**
- Create: `website_download/mbs-college-admission.php`, `website_download/ipu-colleges-in-dwarka.php`
- Modify: `website_download/barch-admission-ipu.php`, `website_download/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` (related-pages entries only), `seo/data` none

**Facts that may be stated (fetched from mbscollege.org on 2026-09-30):** affiliated to GGSIPU, approved by COA and AICTE; Dwarka Sector 9, New Delhi; 2-acre campus about 2 minutes from Dwarka Sector 10 metro; 17+ years; programmes B.Arch (5-year), B.Tech CSE, B.Tech AI & ML, B.Tech ECE, B.Tech Civil, BBA, BCA, B.Com (Hons). Existing site pages say MBS College Dwarka BBA intake 60 and "formerly MBS School of Planning & Architecture". **Not yet sourced:** fees, cutoffs, B.Tech seat counts, placements. Those render only from `college-facts-2026.json` (Task 6) and are omitted until sourced.

- [ ] **Step 1: Write a structure test first.** Add to `tests/test_seo_verify.py`:

```python
def test_mbs_page_contract(tmp_path):
    # Real page is verified live in Step 4; this pins the keyword-bearing H2 phrases the plan requires.
    required = ["MBS College Dwarka", "Courses Offered", "How to Reach", "Admission Process"]
    import pathlib
    src = pathlib.Path(__file__).resolve().parents[1] / "website_download" / "mbs-college-admission.php"
    if not src.exists():
        import pytest; pytest.skip("page not built yet")
    text = src.read_text()
    for phrase in required:
        assert phrase in text
```
Run it: `python3 -m pytest tests/test_seo_verify.py::test_mbs_page_contract -q` → SKIP now, PASS after Step 2.

- [ ] **Step 2: Build `mbs-college-admission.php`** from the `usict-admission.php` skeleton (lines 1 to 62 head/hero pattern, sidebar, `cta-strip`, `faq-section`, `related-pages`). Content spec:
  - `<title>` `MBS College Dwarka – Admission, Courses, Fees & Seats | IPU Affiliated`; meta description `MBS College Dwarka (Sector 9), affiliated to GGSIPU: B.Arch, B.Tech CSE, AI & ML, ECE, Civil, BBA, BCA and B.Com Hons. Courses, admission process and how to apply. Call 9899991342.`; canonical `https://ipu.co.in/mbs-college-admission.php`; one H1 `MBS College Dwarka Admission – Courses, Fees & Admission Process`.
  - H2s (exact, these carry the keywords): `MBS College Dwarka at a Glance`, `Courses Offered`, `How to Reach MBS College Dwarka`, `Admission Process at MBS College`, `MBS College (formerly MBS School of Planning & Architecture)`, then FAQ.
  - Body uses only the facts listed above. Fees, cutoff and seats sections call `college-facts-faq.php` with `$facts_key = 'mbs'` and are skipped when absent.
  - FAQ (no phone in answers): `Is MBS College Dwarka affiliated to IPU?` (yes, GGSIPU; approved by COA and AICTE); `Where is MBS College located?` (Sector 9 Dwarka, about 2 minutes from Dwarka Sector 10 metro); `Which courses does MBS College Dwarka offer?` (the list above); `Is MBS College the same as MBS School of Planning & Architecture?` (formerly; say "according to IPU listings" only if Step 3 of Task 6 confirms).
  - Related: `/barch-admission-ipu.php`, `/ipu-colleges-in-dwarka.php`, `/top-btech-colleges-delhi.php`, `/top-bba-colleges-ipu.php`.

- [ ] **Step 3: Build `ipu-colleges-in-dwarka.php`** (hub). Title `IPU Colleges in Dwarka – List of GGSIPU Affiliated Colleges Near Dwarka Metro`; meta `IPU colleges in Dwarka, Delhi: USICT, USAR, USMS, USLLS on the GGSIPU campus plus MBS College and other affiliated colleges. Courses, location and admission links.`; H1 `IPU Colleges in Dwarka – GGSIPU Campus and Affiliated Colleges`. A table with one row per college that the site already covers and that is in Dwarka (USICT, USAR, USMS, USLLS per existing pages; MBS College). Every row links to that college's page. Only include a college if its page states a Dwarka location (verify with `grep -il dwarka website_download/<page>.php`). Internal links from: `top-ipu-colleges.php`, `ipu-colleges-list.php` (related-pages entries).

- [ ] **Step 4: Lint, verify locally, then live after deploy**

Run:
```bash
cd website_download && for f in mbs-college-admission.php ipu-colleges-in-dwarka.php; do php -l $f; done
cd .. && python3 scripts/seo_verify.py --base http://127.0.0.1:8123 --strict-faq-phone mbs-college-admission.php ipu-colleges-in-dwarka.php
python3 -m pytest tests/test_seo_verify.py -q
```
Expected: no syntax errors; both OK; all tests pass.

- [ ] **Step 5: Commit**

```bash
git add website_download/mbs-college-admission.php website_download/ipu-colleges-in-dwarka.php website_download/barch-admission-ipu.php website_download/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php tests/test_seo_verify.py
git commit -m "feat(seo): MBS College Dwarka page and IPU colleges in Dwarka hub" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Deploy in batch 3 (27 Nov), Appendix A. Entanglement-check the two modified existing pages first.

---

### Task 6: College facts dataset (sourced) and FAQ component

**Files:**
- Create: `website_download/include/data/college-facts-2026.json`, `website_download/include/components/college-facts-faq.php`
- Test: `tests/test_college_facts.py`

**Interfaces:**
- Produces: JSON schema `{ "<key>": {"short": str, "name": str, "campus_area_acres": {"v": number, "source": str, "as_of": "YYYY-MM-DD"}, "avg_package_cse_lpa": {...}, "highest_package_lpa": {...}, "fees": [{"course": str, "per_year_inr": int, "source": str, "as_of": "YYYY-MM-DD"}] } }`. Keys: `mait, msit, vips, bpit, bvp, usict, usms, usar, usls, adgitm, mbs`. Every field is optional; a field without `source` and `as_of` is ignored. Component contract: set `$facts_key` and `$faqs`, include it, it appends FAQ entries to `$faqs`.

**Input gate:** by **2 Nov** Sumit supplies the brochure path (memory: `~/Desktop/UG 2026.pdf`, but that file is currently missing; `qpdf` repair was needed before) plus any package and campus-area figures he trusts. Otherwise the facts are collected from each college's official site (fetch and record the exact URL in `source`). Do not use aggregator sites. Leave fields out rather than guess.

- [ ] **Step 1: Write the failing tests**

```python
# tests/test_college_facts.py
import json, subprocess, textwrap
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FACTS = ROOT / "website_download" / "include" / "data" / "college-facts-2026.json"
COMPONENT = ROOT / "website_download" / "include" / "components" / "college-facts-faq.php"

def test_every_field_has_source_and_as_of():
    data = json.loads(FACTS.read_text())
    for key, college in data.items():
        for field, val in college.items():
            if field in ("short", "name"):
                continue
            items = val if isinstance(val, list) else [val]
            for item in items:
                assert item.get("source"), f"{key}.{field} missing source"
                assert item.get("as_of"), f"{key}.{field} missing as_of"

def _run(facts, key):
    code = textwrap.dedent(f"""
      <?php $faqs = []; $facts_key = '{key}'; $facts_file_override = '{facts}';
      include '{COMPONENT}'; echo json_encode($faqs);""")
    return json.loads(subprocess.run(["php"], input=code, text=True, capture_output=True).stdout)

def test_component_skips_unsourced_fields(tmp_path):
    f = tmp_path / "f.json"
    f.write_text(json.dumps({"x": {"short": "X", "name": "X College",
        "campus_area_acres": {"v": 5},                      # no source -> must be skipped
        "highest_package_lpa": {"v": 30, "source": "https://example.test", "as_of": "2026-09-01"}}}))
    out = _run(f, "x")
    assert len(out) == 1 and "30" in out[0]["answer"] and "9899991342" not in out[0]["answer"]

def test_component_empty_when_key_missing(tmp_path):
    f = tmp_path / "f.json"; f.write_text("{}")
    assert _run(f, "nope") == []
```

- [ ] **Step 2: Run, confirm fail** (`python3 -m pytest tests/test_college_facts.py -q` → FAIL: file not found)

- [ ] **Step 3: Write the component**

```php
<?php
/**
 * Appends FAQ entries from college-facts-2026.json to $faqs. Only fields with BOTH source and as_of render.
 * Usage: $facts_key = 'mait'; include 'include/components/college-facts-faq.php'; (before faq-section.php)
 * No phone numbers in answers (CTA Wave 2a strips them from FAQ JSON-LD).
 */
$faqs = $faqs ?? [];
$facts_key = $facts_key ?? null;
$__file = $facts_file_override ?? (__DIR__ . '/../data/college-facts-2026.json');
$__all = is_file($__file) ? json_decode(file_get_contents($__file), true) : [];
$__c = ($facts_key && is_array($__all)) ? ($__all[$facts_key] ?? null) : null;
if ($__c) {
    $__ok = fn($f) => is_array($f) && !empty($f['source']) && !empty($f['as_of']);
    $__short = $__c['short'] ?? strtoupper($facts_key);
    $__cite = fn($f) => ' (Source: ' . $f['source'] . ', as of ' . $f['as_of'] . '.)';
    if (isset($__c['campus_area_acres']) && $__ok($__c['campus_area_acres'])) {
        $f = $__c['campus_area_acres'];
        $faqs[] = ['question' => "What is the campus area of $__short?", 'answer' => "$__short's campus covers about {$f['v']} acres." . $__cite($f)];
    }
    if (isset($__c['avg_package_cse_lpa']) && $__ok($__c['avg_package_cse_lpa'])) {
        $f = $__c['avg_package_cse_lpa'];
        $faqs[] = ['question' => "What is the average CSE package at $__short?", 'answer' => "The average package reported for CSE at $__short is {$f['v']} LPA." . $__cite($f)];
    }
    if (isset($__c['highest_package_lpa']) && $__ok($__c['highest_package_lpa'])) {
        $f = $__c['highest_package_lpa'];
        $faqs[] = ['question' => "What is the highest package at $__short?", 'answer' => "The highest package reported at $__short is {$f['v']} LPA." . $__cite($f)];
    }
    foreach (($__c['fees'] ?? []) as $fee) {
        if ($__ok($fee) && isset($fee['course'], $fee['per_year_inr'])) {
            $faqs[] = ['question' => "What are the {$fee['course']} fees at $__short?",
                       'answer' => "{$fee['course']} tuition at $__short is Rs. " . number_format((int) $fee['per_year_inr']) . " per year." . $__cite($fee) . " University, exam and other charges are extra."];
        }
    }
}
```

- [ ] **Step 4: Create the facts file** with only what has been sourced. Start with exactly this (fills in as sources arrive), so the tests have a valid file:

```json
{
  "mbs": {"short": "MBS College", "name": "MBS College, Dwarka"}
}
```
Then add rows per college as Sumit or the official sources supply them, each with `source` and `as_of`. Target list per college: `campus_area_acres` (MAIT has 1.9k impressions for this query), `avg_package_cse_lpa` (MAIT 1.8k, MSIT 1.6k, USICT 3.1k), `highest_package_lpa`, and `fees` rows for BBA (VIPS, MAIMS, USMS), B.Com Hons (VIPS, USMS), MBA (USMS), BA LLB (USLLS, VIPS), B.Tech CSE (MAIT, MSIT, BPIT, BVP, VIPS).

- [ ] **Step 5: Run tests, confirm pass**

Run: `python3 -m pytest tests/test_college_facts.py -q` → 3 passed
Run: `php -l website_download/include/components/college-facts-faq.php` → `No syntax errors`

- [ ] **Step 6: Commit**

```bash
git add website_download/include/data/college-facts-2026.json website_download/include/components/college-facts-faq.php tests/test_college_facts.py
git commit -m "feat(seo): sourced college-facts dataset and FAQ component" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 7: College page sections (tier A)

**Files (modify, one edit each):** `mait-admission.php`, `msit-admission.php`, `usict-admission.php`, `usar-admission.php`, `usms-admission.php`, `usls-admission.php`, `adgitm-admission.php`, `BPIT.php`, `BVP.php`, `vips-admission.php`

**Interfaces:** Consumes `college-facts-faq.php` (Task 6) and `$facts_key`. Each page already builds `$faqs` and includes `include 'include/components/faq-section.php';` once.

Keys: `mait-admission.php`→`mait`, `msit-admission.php`→`msit`, `usict-admission.php`→`usict`, `usar-admission.php`→`usar`, `usms-admission.php`→`usms`, `usls-admission.php`→`usls`, `adgitm-admission.php`→`adgitm`, `BPIT.php`→`bpit`, `BVP.php`→`bvp`, `vips-admission.php`→`vips`.

- [ ] **Step 1: Confirm each page has exactly one `faq-section.php` include and no other `$faqs` logic**

Run: `cd website_download && for f in mait-admission msit-admission usict-admission usar-admission usms-admission usls-admission adgitm-admission BPIT BVP vips-admission; do echo "$f $(grep -c "components/faq-section.php" $f.php)"; done`
Expected: every count `1`. Any other value: open that file and place the insertion manually before its actual FAQ include.

- [ ] **Step 2: For each page, insert two lines immediately before the faq include.** Example, `mait-admission.php` (Edit tool: `old_string` = `include 'include/components/faq-section.php';`, `new_string` as below; if the file uses double quotes, match its style):

```php
$facts_key = 'mait'; include 'include/components/college-facts-faq.php';
include 'include/components/faq-section.php';
```

- [ ] **Step 3: Add keyword-bearing copy only where the data exists and only in body text, not in title/meta/H1.** For each college, add one sourced paragraph or table under the existing relevant section, with these target phrases used naturally (and only when the fact is sourced):
  - MAIT: "MAIT direct admission / management quota" (link to `/btech-management-quota-ipu.php`), "MAIT campus area", "MAIT CSE average package", link to `/mait-cutoff.php` with anchor text `MAIT cutoff`.
  - MSIT: "MSIT management quota fees for B.Tech CSE", "MSIT direct admission", link to `/msit-cutoff.php` with anchor text `MSIT cutoff`.
  - USICT: "USICT admission process" steps (JEE Main, GGSIPU registration, choice filling, allotment), "USICT cutoff" link to `/ipu-btech-cutoff-2025.php`.
  - USAR: "USAR Delhi", "USAR fees structure" (from `ipu-fees-structure.php` B.Arch/B.Tech figures), "USAR placement".
  - USMS: "USMS IPU", MBA/BBA/B.Com Hons fee lines (Task 6 data).
  - USLS/USLLS: use both spellings in body and FAQ (`USLLS (often written USLS)`).
  - ADGITM: full name as it appears officially (verify spelling first: the cutoff data key reads `Dr. Akhilesh Das Gupta Institute of Professional Studies`, while the college's current name may be "Institute of Technology & Management"; use the official current name and mention the former one only with a source).
  - BPIT: fee table for CSE, "BPIT cutoff" section pointing to the rounds table already on the page.
  - BVP: a short block near the top answering `Is Bharati Vidyapeeth (BVCOE) under IPU?` (it is a GGSIPU-affiliated college; the page already ranks p3.8 for that query from elsewhere).
  - VIPS: management-quota and fee lines from Task 6.

- [ ] **Step 4: Lint and verify every edited page locally**

Run:
```bash
cd /Users/Sumit/test-project/website_download
for f in mait-admission msit-admission usict-admission usar-admission usms-admission usls-admission adgitm-admission BPIT BVP vips-admission; do php -l $f.php | grep -v 'No syntax'; done
cd .. && python3 scripts/seo_verify.py --base http://127.0.0.1:8123 mait-admission.php msit-admission.php usict-admission.php usar-admission.php usms-admission.php usls-admission.php adgitm-admission.php BPIT.php BVP.php vips-admission.php
```
Expected: no lint output; ten `OK` lines (no `--strict-faq-phone` here: these existing pages already carry phones in older FAQ answers, which Wave 2a removes; only the NEW entries must be phone-free, which the component guarantees). If a page reports a duplicate H1/canonical/description that existed before the edit, record it in the audit and leave it (not this task's scope).

- [ ] **Step 5: Title/meta/H1/URL untouched check**

Run: `git diff --stat -- website_download/*.php | tail -3; git diff -U0 -- website_download/mait-admission.php website_download/msit-admission.php website_download/usict-admission.php website_download/usar-admission.php website_download/usms-admission.php website_download/usls-admission.php website_download/adgitm-admission.php website_download/BPIT.php website_download/BVP.php website_download/vips-admission.php | grep -E '^[+-].*(<title>|name="description"|rel="canonical"|<h1|hero_title)'`
Expected: NO lines printed. Any line printed means a protected element changed: revert that hunk.

- [ ] **Step 6: Commit and deploy.** Batch 2 (13 Nov): MAIT, MSIT, USICT, USAR, USMS, USLS, ADGITM. Batch 3 (27 Nov): BPIT, BVP, VIPS. Each after Appendix A step 2 (entanglement check).

```bash
git add website_download/mait-admission.php website_download/msit-admission.php website_download/usict-admission.php website_download/usar-admission.php website_download/usms-admission.php website_download/usls-admission.php website_download/adgitm-admission.php website_download/BPIT.php website_download/BVP.php website_download/vips-admission.php
git commit -m "feat(seo): sourced FAQ and keyword sections on college pages (tier A)" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 8: Course pages: fee FAQs and keyword sections (tier A)

**Files (modify):** `IPU-B-Tech-admission-2026.php`, `ipu-fees-structure.php`, `comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php`, `bcom-admission-ipu.php`, `mba-admission-ip-university.php`, `ultimate-guide-to-ballb-admission-in-ip-university.php`, `law-3-year-admission-ipu.php`, `top-law-colleges-ipu.php`, `IPU-Law-Admission.php`, `llm-admission-ipu.php`

**Interfaces:** Consumes the fee table already on `ipu-fees-structure.php` ("IPU Master Fee Comparison Table 2026-27", sourced to the Brochure 2026-27 and the 6th SFRC Gazette of 14.07.2025) and Task 6 fee rows. Produces FAQ entries only.

- [ ] **Step 1: Write the per-page FAQ entries using only figures already printed on `ipu-fees-structure.php`** (no new numbers), phone-free. Insert each set into that page's `$faqs` array (append before its closing `];`). Questions (exact wording carries the keywords) and answer source:
  - `IPU-B-Tech-admission-2026.php`: `What are IPU B.Tech fees?` (USICT Rs. 1,69,400 – 2,25,471 per year; MAIT/MSIT/VIPS/BPIT and similar Rs. 1,55,700 per year; both from the fee table); `Which are the best IPU colleges for B.Tech?` (link sentence to `/best-btech-colleges-ipu.php`); `What is the B.Tech cutoff for IPU colleges?` (link to `/ipu-btech-cutoff-2025.php`).
  - BBA guide: `What are IPU BBA fees?` (Rs. 1,20,000 – 1,50,000 per year, about Rs. 3.6 – 4.5 lakh total); `What are VIPS / MAIMS BBA fees?` only if Task 6 has sourced rows, else omit.
  - `bcom-admission-ipu.php`: `What are IPU B.Com Hons fees?` (Rs. 40,000 – 1,50,000 per year, about Rs. 1.2 – 4.5 lakh total).
  - `mba-admission-ip-university.php`: `What are IPU MBA fees?` (Rs. 1,30,000 per year, about Rs. 2.6 lakh total); `What is the IPU MBA admission process?` (steps: CAT/CMAT/MAT/XAT or IPU CET route as the page already states; do not add a new claim).
  - BA LLB guide: `What are IPU BA LLB fees?` (Rs. 1,45,200 – 2,12,587 per year, about Rs. 8.9 lakh total); `What is the IPU BA LLB counselling process?` (CLAT-based counselling as the page already states).
  - `law-3-year-admission-ipu.php`: `What are IPU 3-year LLB fees?` (Rs. 1,30,000 per year, about Rs. 3.9 lakh total).
  - `llm-admission-ipu.php`: `What are IPU LLM fees?` (Rs. 1,30,000 for the 1-year programme).
  - `top-law-colleges-ipu.php`, `IPU-Law-Admission.php`: `Which are the best IPU law colleges?` (link to `/top-law-colleges-ipu.php`); `What are IPU law college fees?` (link to `/ipu-fees-structure.php`, cite the BA LLB and LLB ranges above).
- [ ] **Step 2: On `ipu-fees-structure.php` add id anchors** to the existing rows' first cells so other pages can deep-link (`<td id="btech-fees" ...>` style) without changing any visible text, title, meta or H1. Anchors: `btech-fees`, `bba-fees`, `bcom-fees`, `mba-fees`, `ballb-fees`, `llb-fees`, `llm-fees`.
- [ ] **Step 3: Verify** as Task 7 Step 4 (lint, `seo_verify.py` without strict) plus Step 5's protected-element diff check over these ten files. Expected: no protected lines changed.
- [ ] **Step 4: Commit and deploy (batch 3, 27 Nov)**

```bash
git add website_download/IPU-B-Tech-admission-2026.php website_download/ipu-fees-structure.php website_download/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php website_download/bcom-admission-ipu.php website_download/mba-admission-ip-university.php website_download/ultimate-guide-to-ballb-admission-in-ip-university.php website_download/law-3-year-admission-ipu.php website_download/top-law-colleges-ipu.php website_download/IPU-Law-Admission.php website_download/llm-admission-ipu.php
git commit -m "feat(seo): fee and process FAQs on course pages (tier A)" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
Note: `IPU-Law-Admission.php` and the BBA guide overlap the held Phase-2 commit (WebP changes). If Phase 2 is already deployed (Task 3 Step 8) this is moot; if not, run Appendix A step 2 and, if a held `webp_img()` call appears, deploy only after Phase 2.

---

### Task 9: MBA management-quota rewrite and BBA LLB guide extension (batch 4, 15 Jan)

**Files (modify):** `mba-management-quota-ipu.php`, `comprehensive-guide-to-bballb-admission-in-ip-university.php`; add inbound links in `mba-admission-ip-university.php`, `top-mba-colleges-ipu.php`, `usms-admission.php`, `IP-University-management-quota-admission-eligibility-criteria.php` (related-pages only).

- [ ] **Step 1: Baseline both pages** (before): record size, H2 list, internal inbound count.

Run: `cd website_download && for f in mba-management-quota-ipu comprehensive-guide-to-bballb-admission-in-ip-university; do echo "== $f $(wc -c <$f.php) bytes"; grep -o '<h2[^>]*>[^<]*' $f.php; echo "inbound: $(grep -l "$f" *.php | wc -l)"; done`

- [ ] **Step 2: Expand `mba-management-quota-ipu.php`** from 8 KB toward a full guide. Keep its title, meta, canonical and H1 exactly as they are (tier A). Add sections: `What is management quota for MBA at IPU`, `Which IPU colleges offer MBA management-quota seats` (only colleges whose pages already state MBA: USMS is university quota, so describe per existing pages), `Eligibility and entrance scores (CAT, CMAT, MAT, XAT, IPU CET as already stated on the site)`, `MBA fees at IPU` (Rs. 1,30,000 per year from the fee table), `How to apply`. Every claim must already exist on a sourced page of this site; otherwise omit it. Add a `$faqs` block (4 to 6 entries, phone-free) and a `$related_pages` block including `/mba-admission-ip-university.php`, `/top-mba-colleges-ipu.php`, `/usms-admission.php`, `/ipu-mba-cutoff-2025.php`.
- [ ] **Step 3: Add inbound links** to that page from the four pages above (`$related_pages` entries, anchor `MBA Management Quota at IPU`). Target at least 5 inbound references (was 2).
- [ ] **Step 4: Extend the BBA LLB guide body** (title and the duplicate description stay untouched until Task 11): add H2s `IPU BBA LLB fees` (Rs. 1,45,200 – 2,12,587 per year for BA LLB / BBA LLB from the fee table), `BBA LLB admission process via CLAT` (only as already stated), `BBA LLB colleges under IPU` (only colleges the site already lists for BBA LLB: check `grep -il 'bba llb\|bballb' website_download/*.php`). Add phone-free FAQ entries: `What are IPU BBA LLB fees?`, `Which colleges offer BBA LLB in IPU?`, `Is CLAT required for IPU BBA LLB?` (as already stated on the page).
- [ ] **Step 5: Verify and commit**

Run: `php -l` both files; `python3 scripts/seo_verify.py --base http://127.0.0.1:8123 mba-management-quota-ipu.php comprehensive-guide-to-bballb-admission-in-ip-university.php` (the BBA LLB guide will FAIL on "meta description count 2 != 1" until Task 11; that failure is expected and is the bug Task 11 fixes). Protected-element diff check (Task 7 Step 5) on these two files: expected no lines.

```bash
git add website_download/mba-management-quota-ipu.php website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php website_download/mba-admission-ip-university.php website_download/top-mba-colleges-ipu.php website_download/usms-admission.php website_download/IP-University-management-quota-admission-eligibility-criteria.php
git commit -m "feat(seo): expand MBA management-quota page; extend BBA LLB guide body (tier A)" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 10: Tier-C title tests (USAR, ADGITM, homepage) on 1 Dec

**Needs Sumit's explicit approval per page by 27 Nov.** Do not start without it.

**Files (modify):** `usar-admission.php`, `adgitm-admission.php`, `index.php`: `<title>` and meta description only. H1 and URL stay.

Current to proposed (57 to 62 characters for titles):

| Page | Current title | Proposed title | Proposed meta description |
|---|---|---|---|
| `usar-admission.php` | `USAR IPU Admission 2026 – Automation & Design Dual-Degree Courses` | `USAR IPU Delhi 2026 – Admission, Cutoff, Fees & Placements` | `USAR IPU Delhi: dual-degree B.Tech/M.Tech in AI&DS, AI&ML, IIOT and A&R with 132 seats each. Cutoff, fees, placements and admission process. Call 9899991342.` |
| `adgitm-admission.php` | `ADGITM Admission 2026 \| IPU B.Tech, MBA, MCA Courses` | `ADGITM Delhi Admission 2026 – Fees, Cutoff & Courses` | `ADGITM Delhi (Dr. Akhilesh Das Gupta Institute) under IPU: B.Tech, MBA, MCA courses, fees, cutoff and placements. Free admission guidance at 9899991342.` (verify the official full name first, see Task 7) |
| `index.php` | `IPU Admission 2026 \| IP University (GGSIPU) Counselling & Management Seat Help` | `IPU (IP University) Admission 2026 – Colleges, Cutoff, Fees & Counselling` | `IP University (GGSIPU) admission 2026: colleges list, cutoffs, fees, counselling dates and management-quota help for B.Tech, BBA, Law. Free helpline 9899991342.` |

Honest expectation: these are CTR tests on terms stuck at positions 8–10. A title change will not move the homepage's position for `ipu` (the official sites hold it); the goal is CTR on `ipu` from 0.16% to about 0.25% or better (about 700k impressions, so a meaningful click gain if demand returns).

- [ ] **Step 1: Snapshot current `<title>` and meta of the three pages into `seo/rechecks/2026-12-01/decision.md`** (so a revert is one copy away).
- [ ] **Step 2: Apply the three title/meta edits** with the Edit tool (exact `old_string` = current title tag or description attribute).
- [ ] **Step 3: Verify locally** `python3 scripts/seo_verify.py --base http://127.0.0.1:8123 usar-admission.php adgitm-admission.php index.php`. Expected: three OK.
- [ ] **Step 4: Commit, then deploy these three files alone on 1 Dec** (Appendix A; entanglement-check `index.php` especially).

```bash
git add website_download/usar-admission.php website_download/adgitm-admission.php website_download/index.php seo/rechecks/2026-12-01/decision.md
git commit -m "test(seo): title/meta CTR tests on USAR, ADGITM and homepage (tier C, approved)" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```
- [ ] **Step 5: Rechecks.** On **15 Dec** and **29 Dec**: export GSC Queries, then `python3 seo/scripts/watch_terms.py compare seo/baselines/2026-09-30-keyword-map-watch-terms.csv <new Queries.csv>`. Revert the specific page's title/meta if any of its own terms (`usar`, `usar delhi`, `adgitm`, `ipu`, `ggsipu`, `ip university`) is flagged. Seasonality guard: compare against the same terms on untouched pages (e.g. `vips`, `bpit`); if they fell too, the cause is demand, not the edit. Record each decision in `seo/rechecks/<date>/decision.md`. Final keep/revert by **5 Jan**.

---

### Task 11: BBA LLB guide title and duplicate-description fix (tier C, needs approval)

**Files (modify):** `comprehensive-guide-to-bballb-admission-in-ip-university.php`

The page has two `<meta name="description">` tags and a title ending in "Meta". That is a bug, not a strategy, but it is still a ranking page, so it needs your approval.

- [ ] **Step 1: Snapshot** the current title and both descriptions into `seo/rechecks/2027-01-05/decision.md`.
- [ ] **Step 2: Keep the second description (the newer, focused one) and remove the first.** Proposed title: `IPU BBA LLB Admission 2026 – Fees, CLAT Eligibility & Top Colleges`. Do not alter the canonical or H1.
- [ ] **Step 3: Verify** `python3 scripts/seo_verify.py --base http://127.0.0.1:8123 comprehensive-guide-to-bballb-admission-in-ip-university.php` → `OK`.
- [ ] **Step 4: Commit and deploy in batch 4 (15 Jan)**, with a recheck on **29 Jan** and **12 Feb** for `ggsipu bba llb fees`, `ipu bba llb admission`.

```bash
git add website_download/comprehensive-guide-to-bballb-admission-in-ip-university.php seo/rechecks/2027-01-05/decision.md
git commit -m "fix(seo): BBA LLB guide duplicate meta description and title (tier C, approved)" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 12: Sitemap, llms.txt, link audit, final QA (18 Jan – 12 Feb)

**Files (modify):** `website_download/sitemap.xml`, `website_download/llms.txt`

- [ ] **Step 1: Pull the live sitemap first** (news scraper edits it on prod; see the 2026-07-11 note): `curl -s https://ipu.co.in/sitemap.xml -o /tmp/sitemap-live.xml` then diff against local; reconcile before editing.
- [ ] **Step 2: Add `<url>` blocks** for `mait-cutoff.php`, `msit-cutoff.php`, `mbs-college-admission.php`, `ipu-colleges-in-dwarka.php` (canonical `https://ipu.co.in/...`, `lastmod` = deploy date, `changefreq` monthly, `priority` 0.7). Do this before each batch that adds a page (Tasks 4 and 5), not only here; this task confirms it.
- [ ] **Step 3: Add the same four pages to `llms.txt`** under the colleges section, one line each in the file's existing format.
- [ ] **Step 4: Orphan and link audit.** For each new URL confirm at least 3 inbound references counting both `href=` and `'url' =>`:

Run: `cd website_download && for p in mait-cutoff msit-cutoff mbs-college-admission ipu-colleges-in-dwarka mba-management-quota-ipu; do echo "$p $(grep -lE "(href=\"|'url' => ')/?$p\.php" *.php | grep -v "^$p.php" | wc -l)"; done`
Expected: each count at least 3 (management-quota page at least 5). Add `$related_pages` entries where short.

- [ ] **Step 5: Final live QA.**

Run: `python3 scripts/seo_verify.py --base https://ipu.co.in mait-cutoff.php msit-cutoff.php mbs-college-admission.php ipu-colleges-in-dwarka.php mba-management-quota-ipu.php comprehensive-guide-to-bballb-admission-in-ip-university.php usar-admission.php adgitm-admission.php index.php`
Expected: all OK. Submit the sitemap in GSC on **1 Feb** and request indexing for the four new URLs.

- [ ] **Step 6: Commit**

```bash
git add website_download/sitemap.xml website_download/llms.txt
git commit -m "chore(seo): sitemap and llms.txt for keyword-map pages" -m "Co-Authored-By: Claude Sonnet 5.5 <noreply@anthropic.com>"
```

---

### Task 13: Google Ads actions (Sumit in the Ads UI; Search network only)

No code. Do not use Display or Remarketing data for these decisions.

- [ ] **Step 1 (by 9 Oct): apply `docs/ads/2026-10-negatives.csv`** as negatives on the Search campaigns (`Btech 2026 session`, `IPU Responsive AD`, `Phone Call - Btech`, `DSA Campaign`, `RSA`, `Phone Call`).
- [ ] **Step 2 (by 9 Oct): pause `Lead Search 2026 for MBA PDGM LAW`** (₹308 per conversion, 2.5k impressions in all time) and keep MBA/law demand in the campaigns that already convert.
- [ ] **Step 3 (1 Feb – 1 Mar): pre-season keyword groups,** one ad group per entity with these keyword themes: `mait direct admission`, `mait management quota`, `msit management quota fees`, `msit direct admission`, `vips management quota`, `usict admission process`, `ip university bba fees`, `ipu mba admission process`, `ipu llb admission`, plus the MBS/Dwarka terms. Landing pages: the matching college or cutoff page, never the homepage.
- [ ] **Step 4 (ongoing): fix conversion tracking.** Several campaigns show conversion rates of 39–107%, so conversions probably count call-button taps or page views. Ask for a tracked lead definition (form submit and call over 30 seconds) before trusting any cost-per-conversion figure; ipu.co.in has no CRM feed, so lead counts need another source.
- [ ] **Step 5 (weekly from 1 Mar): review search terms** and add new negatives.

---

## Appendix A: Deploy batch procedure (run for every batch)

1. **Manifest.** Write `changes-<batch>.txt`, one path per line (`website_download/...`).
2. **Entanglement check (every EXISTING file in the manifest):**
   ```bash
   for f in $(grep -v '^#' changes-<batch>.txt); do
     [ -f "$f" ] && git log --oneline -S'webp_img' -- "$f" | head -3 | sed "s|^|$f: |"
   done
   git log --oneline -1 a7627b8
   ```
   If any shipped file lists a commit that also appears in `git show --stat a7627b8`, it carries held Phase-2 work: stop and ship Phase 2 first (Task 3 Step 8) or drop that file.
3. **Lint:** `for f in $(grep '\.php$' changes-<batch>.txt); do php -l $f; done` → no errors.
4. **Dry run:** `python3 deploy.py --manifest changes-<batch>.txt --dry-run` → list matches the manifest.
5. **Credentials** only from `.env` via env (`FTP_HOST/FTP_USER/FTP_PASS`): read the three lines to a temp file, `set -a; source`, shred after (memory: [[reference_ipu_project_location_deploy]]). Sumit confirms the go before the real deploy.
6. **Deploy:** `python3 deploy.py --manifest changes-<batch>.txt`
7. **Live check:** `python3 scripts/seo_verify.py --base https://ipu.co.in <paths>`; plus `curl -s -o /dev/null -w '%{http_code}'` on 5 unrelated high-traffic pages (`/`, `/ipu-colleges-list.php`, `/IPU-B-Tech-admission-2026.php`, `/IPU-Law-Admission.php`, `/ipu-counselling.php`) → all 200. Any non-200: roll back the last file set immediately.
8. **`git pull` before any sync-style step** ([[feedback_git_pull_before_sync]]): GitHub Actions auto-commits news; `git fetch && git pull --rebase` first.

## Self-review

- **Spec coverage:** §3 courses → master schedule rows 14–21 and Tasks 8, 9, 11; §4 colleges → rows 1–13 and Tasks 4, 5, 6, 7; §5 ordering → Calendar; §6 Ads → Tasks 3 and 13; §7 open items → Task 2 (existence checks), Task 6 (brochure gate), Task 10 (C-tier approvals). Junk-parameter URLs and `blog-detail.php` redirects are intentionally separate (recorded in Task 2, no action needed for junk URLs).
- **Corrections to the spec recorded:** LLM page already exists; dedicated pages for USICT/USAR/USMS/USLS/ADGITM already exist; the MBA management-quota page is thin and poorly linked; the BBA LLB guide has a duplicate meta description.
- **Placeholder scan:** data-gated steps name the exact input, owner and deadline; no "TBD" text in instructions. Fee and package sentences render only from sourced data.
- **Type/name consistency:** `$cp`, `$facts_key`, `$faqs`, `check_html`, `compare`, `build_baseline`, `WATCH` are used identically across tasks.
- **Risk note:** Task 7 edits `BPIT.php`, `BVP.php`, `vips-admission.php` and Task 8 edits `IPU-Law-Admission.php` and the BBA guide, all of which may overlap held Phase-2 commit `a7627b8`. Appendix A step 2 catches this; Task 3 Step 8 removes the risk by shipping Phase 2 first.
