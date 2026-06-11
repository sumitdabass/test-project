# IPU Phase 3 — Additive SEO — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Lift the page-1-bottom money terms and strengthen topical clustering on ipu.co.in using **only additive** changes — internal links, a comparison table, missing JSON-LD, and `llms.txt`/Organization enrichment — with zero edits to any existing ranking element.

**Architecture:** Vanilla PHP 8. Reuse existing components: `include/components/related-pages.php` (`$related_pages = [['title','url','desc'], …]`), `faq-section.php` (`$faqs = [['question','answer'], …]`, auto-emits FAQPage), `breadcrumb-schema.php` (`$breadcrumbs = [[name,url], …]`, last url empty), `college-schema.php` (`$college = ['name','short_name','url','address','founded'?,'courses'?,'total_seats'?,'accreditation'?]`, emits CollegeOrUniversity). Verification via `php -l`, JSON-LD parse, `php -S localhost:8000` + `curl`, and a localhost crosslink walk.

**Tech Stack:** PHP 8.x, Bootstrap 5. No website unit tests — verify with lint + grep + curl + JSON parse.

**Reference:** program spec §5 Phase 3/4; reanalysis findings O3–O7 (2026-06-11); 3-month GSC data + baseline `seo/baselines/2026-06-11-3month-watch-terms.csv`.

---

## HARD CONSTRAINTS (every task)

- **MUST NOT affect current SEO negatively.** Never change/rewrite an existing `<title>`, meta description, canonical, H1, URL, or existing body copy on any ranking page. Phase 3 work is strictly **ADD**: new internal links, a new table, new JSON-LD, new `llms.txt`/schema properties. (`feedback_seo_safety_ipu`, owner directive 2026-06-11)
- **Verify-missing-before-adding for ALL schema/FAQ.** The reanalysis had false positives (e.g. Law page already has Breadcrumb+FAQ; homepage already has FAQ). Each schema/FAQ task MUST first confirm the target is genuinely absent on the page; if already present, SKIP that page and note it. Duplicate JSON-LD is a regression — do not create it.
- **Never invent facts.** Cutoffs, fees, seats, addresses, placement numbers MUST be sourced from existing on-site content / data files. If a figure isn't already on the site, omit it (component optional fields are omittable). No fabricated numbers.
- **Site-wide template changes require the localhost crosslink walk** before deploy (`feedback_localhost_crosslink_test`) — Task 1 touches the footer (all 130 pages).
- **Gated deploy, scoped manifest only** (no `--sync`). Owner approves the push (Task 7). After deploy, schedule the watch-term recheck vs the 2026-06-11 baseline; stop-loss revert if any watch term drops > 2 positions.
- Branch `claude/2026-04-30-ipu-session`, no worktree. This plan is authoritative over existing file state.

---

## Files

- Modify: `website_download/include/base-footer.php` (Task 1 — add pillar link to Popular Courses)
- Modify: `website_download/ipu-b-tech-pillar.php`, `website_download/ipu-admission-guide.php`, `website_download/college-admission-delhi.php` (Task 2 — hub→spoke `$related_pages`)
- Modify: `website_download/ipu-colleges-list.php`, `website_download/ipu-bba-cutoff.php` (Task 3 — add related-pages where missing, verify-first)
- Modify: `website_download/best-btech-colleges-ipu.php` (Task 4 — comparison table)
- Modify: `website_download/ipu-b-tech-pillar.php` (Task 5 — BreadcrumbList JSON-LD)
- Modify: `website_download/BPIT.php` (Task 6 — CollegeOrUniversity schema)
- Modify: `website_download/llms.txt`, `website_download/index.php` (Task 7 — llms.txt refresh + Org sameAs)

---

## Task 1: Add the B.Tech pillar to the sitewide footer "Popular Courses"

GSC shows the B.Tech cluster is the #1 traffic driver, but `ipu-b-tech-pillar.php` has only 3 inbound links and isn't in nav/footer (O3). Adding ONE link to the footer "Popular Courses" list gives it a sitewide inbound link. Purely additive (a new `<li>`).

**Files:** Modify `website_download/include/base-footer.php`

- [ ] **Step 1: Locate the Popular Courses list** (around line 40–48). It contains `<li>` links like `B.Tech Admission → /IPU-B-Tech-admission-2026.php`, `MBA Admission → /mba-admission-ip-university.php`.

- [ ] **Step 2: Add ONE new `<li>` inside that `<ul>`, matching the existing markup exactly.** Insert after the B.Tech Admission item:

```php
            <li style="margin-bottom:10px"><a href="/ipu-b-tech-pillar.php" style="color:rgba(255,255,255,.7);font-size:14px;text-decoration:none">B.Tech Admission Hub</a></li>
```
(Use the SAME inline style string as the sibling `<li>` items — copy it verbatim from the adjacent item so styling is identical.)

- [ ] **Step 3: Lint.** Run: `php -l website_download/include/base-footer.php` → `No syntax errors detected`.

- [ ] **Step 4: Confirm the link renders sitewide and nothing else changed.**

```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s http://localhost:8000/index.php | grep -c 'ipu-b-tech-pillar.php'
curl -s http://localhost:8000/BPIT.php  | grep -c 'ipu-b-tech-pillar.php'
kill %1
```
Expected: ≥1 on both (footer is shared).

- [ ] **Step 5: Commit.**

```bash
git add website_download/include/base-footer.php
git commit -m "feat(seo): link B.Tech pillar from sitewide footer Popular Courses (additive inbound link)"
```

---

## Task 2: Hub → spoke internal linking on the 3 hub pages

The hubs (`ipu-b-tech-pillar.php`, `ipu-admission-guide.php`, `college-admission-delhi.php`) re-link the same ~10 nav-level pages and don't link DOWN to cutoff/best-colleges/strategy spokes (O3). Each hub already includes `related-pages.php` via a `$related_pages` array — EXPAND that array with spoke links using varied, descriptive anchors. Additive (more entries in the existing Related Guides section).

**Files:** Modify `website_download/ipu-b-tech-pillar.php` (the `$related_pages` near line 120), `website_download/ipu-admission-guide.php`, `website_download/college-admission-delhi.php`

- [ ] **Step 1: For each hub, read its existing `$related_pages` array** (grep `related_pages` in the file). Keep existing entries; APPEND new ones. Only append targets that (a) exist on disk and (b) aren't already in the array.

- [ ] **Step 2: `ipu-b-tech-pillar.php` — append these spoke entries** to `$related_pages` (verify each URL exists with `ls website_download/<file>` first; drop any that don't):

```php
    ['title' => 'IPU B.Tech Cutoff 2025 (Round-wise)', 'url' => '/ipu-btech-cutoff-2025.php', 'desc' => 'Branch & college-wise closing ranks across counselling rounds.'],
    ['title' => 'Best B.Tech Colleges under IPU — Compared', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare top IPU engineering colleges on cutoff, fees and placements.'],
    ['title' => 'IPU B.Tech via CUET', 'url' => '/ipu-btech-via-cuet.php', 'desc' => 'How CUET scores map to IPU B.Tech admission.'],
    ['title' => 'IPU Choice-Filling Strategy', 'url' => '/ipu-choice-filling-strategy.php', 'desc' => 'Order your college/branch preferences to maximise your seat.'],
    ['title' => 'Top B.Tech Colleges in Delhi', 'url' => '/top-btech-colleges-delhi.php', 'desc' => 'Delhi NCR engineering colleges accepting IPU counselling.'],
```

- [ ] **Step 3: `ipu-admission-guide.php` — append** (verify URLs exist; drop missing):

```php
    ['title' => 'GGSIPU B.Tech Counselling', 'url' => '/GGSIPU-counselling-for-B-Tech-admission.php', 'desc' => 'Step-by-step IPU B.Tech counselling, dates and registration.'],
    ['title' => 'IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => 'Full directory of colleges affiliated to IP University.'],
    ['title' => 'IPU Law Admission', 'url' => '/IPU-Law-Admission.php', 'desc' => 'BA LLB / BBA LLB admission and counselling under IPU.'],
    ['title' => 'IPU BBA Cutoff', 'url' => '/ipu-bba-cutoff.php', 'desc' => 'Programme-wise BBA closing ranks under IP University.'],
```

- [ ] **Step 4: `college-admission-delhi.php` — append** (verify URLs exist; drop missing):

```php
    ['title' => 'Top IPU Colleges', 'url' => '/top-ipu-colleges.php', 'desc' => 'Highest-ranked colleges under IP University.'],
    ['title' => 'Best B.Tech Colleges under IPU', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare IPU engineering colleges.'],
    ['title' => 'IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => 'Full IP University affiliated-college directory.'],
```

- [ ] **Step 5: Lint all three.** Run: `php -l website_download/ipu-b-tech-pillar.php && php -l website_download/ipu-admission-guide.php && php -l website_download/college-admission-delhi.php` → all `No syntax errors detected`.

- [ ] **Step 6: Confirm the new spoke links render** on each hub:

```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
for p in ipu-b-tech-pillar.php ipu-admission-guide.php college-admission-delhi.php; do
  echo "$p new-links: $(curl -s http://localhost:8000/$p | grep -oE '/(ipu-btech-cutoff-2025|best-btech-colleges-ipu|ipu-colleges-list|ipu-choice-filling-strategy|top-ipu-colleges)\.php' | sort -u | wc -l)"
done
kill %1
```
Expected: each hub shows several (>0) of the new spoke links.

- [ ] **Step 7: Commit.**

```bash
git add website_download/ipu-b-tech-pillar.php website_download/ipu-admission-guide.php website_download/college-admission-delhi.php
git commit -m "feat(seo): hub→spoke internal links on 3 hubs (additive related-pages, varied anchors)"
```

---

## Task 3: Add related-pages blocks to high-value pages that lack them

`ipu-colleges-list.php` (1,745 clicks — the #2 page) and `ipu-bba-cutoff.php` have no related-pages block (O3). Add one. **Verify-first** (skip if already present).

**Files:** Modify `website_download/ipu-colleges-list.php`, `website_download/ipu-bba-cutoff.php`

- [ ] **Step 1: Verify neither already includes related-pages.** Run: `grep -c 'related-pages' website_download/ipu-colleges-list.php website_download/ipu-bba-cutoff.php`. For any file returning ≥1, SKIP it (note in report).

- [ ] **Step 2: For each file lacking it, find the footer include line** (`include_once("include/base-footer.php")`) and insert the block IMMEDIATELY BEFORE it.

For `ipu-colleges-list.php`:
```php
<?php
$related_pages = [
  ['title' => 'Top IPU Colleges', 'url' => '/top-ipu-colleges.php', 'desc' => 'Highest-ranked colleges under IP University.'],
  ['title' => 'Best B.Tech Colleges under IPU', 'url' => '/best-btech-colleges-ipu.php', 'desc' => 'Compare top IPU engineering colleges.'],
  ['title' => 'GGSIPU B.Tech Counselling', 'url' => '/GGSIPU-counselling-for-B-Tech-admission.php', 'desc' => 'Counselling process, dates and registration.'],
  ['title' => 'IPU Admission Guide', 'url' => '/ipu-admission-guide.php', 'desc' => 'Everything about IP University admission.'],
];
include 'include/components/related-pages.php';
?>
```

For `ipu-bba-cutoff.php`:
```php
<?php
$related_pages = [
  ['title' => 'Top BBA Colleges under IPU', 'url' => '/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php', 'desc' => 'Compare the top 10 BBA colleges under IP University.'],
  ['title' => 'IPU BBA Admission', 'url' => '/ipu-bba-admission.php', 'desc' => 'BBA admission process and eligibility under IPU.'],
  ['title' => 'IPU Colleges List', 'url' => '/ipu-colleges-list.php', 'desc' => 'Full IP University affiliated-college directory.'],
];
include 'include/components/related-pages.php';
?>
```
(Verify each `url` exists on disk first; drop any that don't. Confirm the relative `include` path resolves the same way the page's existing `base-footer.php` include does — match the page's include style.)

- [ ] **Step 3: Lint.** `php -l` both files → `No syntax errors detected`.

- [ ] **Step 4: Confirm the Related Guides section renders.**

```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
for p in ipu-colleges-list.php ipu-bba-cutoff.php; do echo "$p: $(curl -s http://localhost:8000/$p | grep -c 'Related Guides')"; done
kill %1
```
Expected: ≥1 each (unless skipped in Step 1).

- [ ] **Step 5: Commit.**

```bash
git add website_download/ipu-colleges-list.php website_download/ipu-bba-cutoff.php
git commit -m "feat(seo): add related-pages block to colleges-list + bba-cutoff (additive, verify-first)"
```

---

## Task 4: Add a comparison TABLE to best-btech-colleges-ipu.php

The "best colleges" page has **0 `<table>`** (O4) despite a "Compare Top IPU Engineering Colleges" section (~line 257). A clean comparison table is the highest-value AI-extraction / AI-Overview win. Additive content in an existing compare section.

**Files:** Modify `website_download/best-btech-colleges-ipu.php`

- [ ] **Step 1: SOURCE THE DATA — do not invent.** Read the existing content of `best-btech-colleges-ipu.php` AND `ipu-btech-cutoff-2025.php` to extract the college names, branches, closing ranks/cutoffs, fees, and placement figures **already stated on the site**. Build the table ONLY from figures that already appear on these pages. If a cell's value isn't on-site, leave it "—". List which source you took each column from in your report.

- [ ] **Step 2: Locate the "Compare Top IPU Engineering Colleges" `<h2>` (~line 257)** and insert a responsive Bootstrap table immediately after that heading's intro paragraph. Use this structure (fill rows from Step 1 data; keep it to the top 6–8 colleges already listed on the page):

```html
<div class="table-responsive" style="margin:24px 0">
  <table class="table table-bordered table-striped" style="font-size:14px">
    <thead style="background:#0d1b6e;color:#fff">
      <tr><th>College</th><th>Popular B.Tech Branches</th><th>Approx. Closing Rank (IPU)</th><th>Approx. Annual Fee</th><th>Placement Highlight</th></tr>
    </thead>
    <tbody>
      <!-- one <tr> per college, values sourced ONLY from existing on-site content (Step 1); use — for unknown -->
      <tr><td>MAIT, Rohini</td><td>CSE, IT, ECE</td><td>—</td><td>—</td><td>—</td></tr>
    </tbody>
  </table>
</div>
```

- [ ] **Step 3: Lint.** `php -l website_download/best-btech-colleges-ipu.php` → `No syntax errors detected`.

- [ ] **Step 4: Confirm exactly one new table renders and no existing H1/title changed.**

```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s http://localhost:8000/best-btech-colleges-ipu.php | grep -c '<table'
curl -s http://localhost:8000/best-btech-colleges-ipu.php | grep -oiE '<title>[^<]*</title>'
kill %1
```
Expected: table count = 1; title string UNCHANGED from before (compare to `git show HEAD:website_download/best-btech-colleges-ipu.php | grep -i '<title>'`).

- [ ] **Step 5: Commit.**

```bash
git add website_download/best-btech-colleges-ipu.php
git commit -m "feat(seo): add sourced comparison table to best-btech-colleges (additive, AI-extraction)"
```

---

## Task 5: Add BreadcrumbList JSON-LD to ipu-b-tech-pillar.php

The pillar renders an HTML breadcrumb (lines 33–37) but emits **no BreadcrumbList JSON-LD** (O5, verified). Add it via the component, mirroring the visible breadcrumb. **Verify-first.**

**Files:** Modify `website_download/ipu-b-tech-pillar.php`

- [ ] **Step 1: Verify it's genuinely missing.** Run: `grep -c 'BreadcrumbList' website_download/ipu-b-tech-pillar.php`. If ≥1, SKIP this task and note it.

- [ ] **Step 2: Insert the breadcrumb-schema include** matching the visible breadcrumb (Home → IPU Admission Guide → B.Tech Admission Hub). Place it near the other JSON-LD / top of the body (the component emits a `<script type=application/ld+json>`):

```php
<?php $breadcrumbs = [['Home', '/'], ['IPU Admission Guide', '/ipu-admission-guide.php'], ['B.Tech Admission Hub', '']]; include 'include/components/breadcrumb-schema.php'; ?>
```
(Confirm the relative `include 'include/components/breadcrumb-schema.php'` path matches how the page already includes components, e.g. its `related-pages.php` include at line 120.)

- [ ] **Step 3: Lint + validate JSON-LD parses + exactly one BreadcrumbList.**

```bash
php -l website_download/ipu-b-tech-pillar.php
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s http://localhost:8000/ipu-b-tech-pillar.php | grep -c 'BreadcrumbList'
curl -s http://localhost:8000/ipu-b-tech-pillar.php | python3 -c "import sys,re,json; [json.loads(b) for b in re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S)]; print('JSON-LD OK')"
kill %1
```
Expected: `No syntax errors detected`; BreadcrumbList count = 1; `JSON-LD OK`.

- [ ] **Step 4: Commit.**

```bash
git add website_download/ipu-b-tech-pillar.php
git commit -m "feat(seo): add BreadcrumbList JSON-LD to B.Tech pillar (additive, verify-first)"
```

---

## Task 6: Add CollegeOrUniversity schema to BPIT.php

`BPIT.php` emits FAQ + Breadcrumb but no CollegeOrUniversity entity schema (O5, verified — it lacks the `college-schema.php` include). Add it, sourcing facts ONLY from the page's existing "About BPIT" body. **Verify-first.**

**Files:** Modify `website_download/BPIT.php`

- [ ] **Step 1: Verify missing.** Run: `grep -c 'CollegeOrUniversity' website_download/BPIT.php`. If ≥1, SKIP and note it.

- [ ] **Step 2: Read BPIT.php's body** to extract the real college name, location/address, founding year, courses, seats, accreditation **as already stated on the page**. Omit any optional field not present on-site (the component drops unset optional fields).

- [ ] **Step 3: Insert the college-schema include** near the existing breadcrumb-schema include (~line 17). Fill ONLY from Step 2 (example shape — replace values with sourced ones; omit fields you can't source):

```php
<?php
$college = [
  'name'       => 'Bhagwan Parshuram Institute of Technology',
  'short_name' => 'BPIT',
  'url'        => 'https://ipu.co.in/BPIT.php',
  'address'    => '<sourced from page, else omit this key>',
  // 'founded' => '<year if on page>',
  // 'courses' => [<branches listed on page>],
  // 'total_seats' => <int if on page>,
  // 'accreditation' => '<if on page>',
];
include 'include/components/college-schema.php';
?>
```

- [ ] **Step 4: Lint + validate + exactly one CollegeOrUniversity.**

```bash
php -l website_download/BPIT.php
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s http://localhost:8000/BPIT.php | grep -c 'CollegeOrUniversity'
curl -s http://localhost:8000/BPIT.php | python3 -c "import sys,re,json; [json.loads(b) for b in re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S)]; print('JSON-LD OK')"
kill %1
```
Expected: `No syntax errors detected`; CollegeOrUniversity count = 1; `JSON-LD OK`.

- [ ] **Step 5: Commit.**

```bash
git add website_download/BPIT.php
git commit -m "feat(seo): add CollegeOrUniversity schema to BPIT (additive, sourced facts, verify-first)"
```

---

## Task 7: Refresh llms.txt + enrich Organization sameAs

`llms.txt` is stale (`Last updated: 2026-04-06`) with ~12 duplicate news headlines (O7). And homepage Organization `sameAs` lists only 2 profiles (O7). Both additive/cleanup.

**Files:** Modify `website_download/llms.txt`, `website_download/index.php`

- [ ] **Step 1: Update the date.** In `llms.txt` line 4, change `# Last updated: 2026-04-06` to `# Last updated: 2026-06-11`.

- [ ] **Step 2: De-duplicate the News section.** Find the News block and remove repeated near-identical headlines (e.g. multiple "Speech Language Pathology withdrawn" / "tout warning" variants), keeping ONE of each. Do not touch non-news sections.

- [ ] **Step 3: Org sameAs — verify-first then append.** In `index.php`, find the Organization JSON-LD `"sameAs"` array (currently Facebook + Instagram). ONLY append additional profile URLs that genuinely belong to IPU Admission Guide / Davyas (e.g. a YouTube or LinkedIn URL) **if such a URL already appears elsewhere on the site** (footer social links). If no additional verified profile URL exists on-site, SKIP this step (do not invent profile URLs). Do not alter existing array entries.

- [ ] **Step 4: Validate.**

```bash
# llms.txt date + no obvious dup news lines
grep -n 'Last updated' website_download/llms.txt
# homepage JSON-LD still parses
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
curl -s http://localhost:8000/index.php | python3 -c "import sys,re,json; [json.loads(b) for b in re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S)]; print('JSON-LD OK')"
kill %1
```
Expected: date shows `2026-06-11`; `JSON-LD OK`.

- [ ] **Step 5: Commit.**

```bash
git add website_download/llms.txt website_download/index.php
git commit -m "feat(ai-seo): refresh llms.txt date + de-dup news; enrich Org sameAs (verify-first)"
```

---

## Task 8: Pre-deploy gate — quality check, crosslink walk, gated deploy, watch

**Files:** none (process). **BLOCKS on owner go-ahead before the FTP push.**

- [ ] **Step 1: Build the manifest** from all files actually modified across Tasks 1–7:

```bash
cat > /tmp/phase3-manifest.txt <<'EOF'
website_download/include/base-footer.php
website_download/ipu-b-tech-pillar.php
website_download/ipu-admission-guide.php
website_download/college-admission-delhi.php
website_download/ipu-colleges-list.php
website_download/ipu-bba-cutoff.php
website_download/best-btech-colleges-ipu.php
website_download/BPIT.php
website_download/llms.txt
website_download/index.php
EOF
```
(Drop any file a task SKIPPED.)

- [ ] **Step 2: Lint every PHP file in the manifest.**

```bash
for f in $(grep '\.php$' /tmp/phase3-manifest.txt); do php -l "$f" | grep -q "No syntax" && echo "ok $f" || echo "FAIL $f"; done
```
Expected: all `ok`.

- [ ] **Step 3: Validate JSON-LD on every page touched** (no duplicate/broken schema):

```bash
cd website_download && php -S localhost:8000 >/tmp/ipu-srv.log 2>&1 & sleep 1
for p in index.php ipu-b-tech-pillar.php BPIT.php best-btech-colleges-ipu.php IPU-Law-Admission.php; do
  curl -s "http://localhost:8000/$p" | python3 -c "import sys,re,json; b=re.findall(r'<script type=\"application/ld\+json\">(.*?)</script>', sys.stdin.read(), re.S); [json.loads(x) for x in b]; print('$p:', len(b),'blocks OK')"
done
kill %1
```
Expected: each page parses; BreadcrumbList/CollegeOrUniversity counts are exactly 1 where added.

- [ ] **Step 4: Localhost crosslink walk** (Task 1 changed the sitewide footer — `feedback_localhost_crosslink_test`). Load homepage + a B.Tech page + a college page + a cutoff page on `php -S localhost:8000`; click/curl the footer pillar link and the new hub spoke links; confirm all resolve 200 and nav/footer render intact on each archetype.

- [ ] **Step 5: Diff-guard — confirm NO ranking element changed.** For each modified PHP page, diff against the last deployed version and confirm `<title>`, `<h1>`, canonical, and meta description are unchanged:

```bash
for f in $(grep '\.php$' /tmp/phase3-manifest.txt); do
  echo "== $f =="; diff <(git show HEAD~8:"$f" 2>/dev/null | grep -iE '<title>|<h1|rel="canonical"|name="description"') \
                       <(grep -iE '<title>|<h1|rel="canonical"|name="description"' "$f") && echo "  ranking elements UNCHANGED" || echo "  ⚠️ REVIEW DIFF ABOVE";
done
```
Expected: "ranking elements UNCHANGED" for every file. Any diff = STOP and review (must be additive only).

- [ ] **Step 6: Dry-run.** Run: `python3 deploy.py --manifest /tmp/phase3-manifest.txt --dry-run`. Expected: lists exactly the manifest files, no deletions.

- [ ] **Step 7: 🚦 PAUSE — request owner go-ahead.** Present dry-run + diff-guard results. Do NOT push without explicit approval.

- [ ] **Step 8: On approval, real push + live verify.**

```bash
set -a; source .env; set +a
python3 deploy.py --manifest /tmp/phase3-manifest.txt
# live spot-checks
curl -s https://ipu.co.in/index.php | grep -c 'ipu-b-tech-pillar.php'
curl -s https://ipu.co.in/ipu-b-tech-pillar.php | grep -c 'BreadcrumbList'
curl -s https://ipu.co.in/BPIT.php | grep -c 'CollegeOrUniversity'
curl -s https://ipu.co.in/best-btech-colleges-ipu.php | grep -c '<table'
curl -s https://ipu.co.in/llms.txt | grep 'Last updated'
```

- [ ] **Step 9: Post-deploy SEO safety.** Run all new JSON-LD through Google Rich Results validation (owner or via curl to the validator). Schedule the watch-term recheck vs `seo/baselines/2026-06-11-3month-watch-terms.csv` for 7 days out (~2026-06-18); **stop-loss: revert any page whose watch term drops > 2 positions.** Resubmit nothing — sitemap already covers these pages.

---

## Self-review notes
- Every task ADDS (links, a table, JSON-LD, llms.txt lines); no task edits an existing title/meta/canonical/H1/URL or rewrites ranking copy. Task 8 Step 5 is a hard diff-guard enforcing this.
- All schema/FAQ tasks (3, 5, 6, 7) verify-missing-first to avoid duplicate JSON-LD (the reanalysis false-positive risk).
- No fabricated facts: Tasks 4 and 6 source figures from existing on-site content and omit unknowns.
- Component APIs used (`related-pages`, `breadcrumb-schema`, `college-schema`) match their documented signatures (verified 2026-06-11).
- Targets are GSC-led: pillar/B.Tech cluster (#1 traffic), colleges-list (#2), best-colleges (AI-extraction), all currently page-1-bottom with headroom.

## Deferred / owner-gated (NOT in this plan)
- Homepage CTR at pos 9.5 and head terms `ipu` / `ip university` (huge impressions) — improving these needs ranking gains; additive links/schema here help indirectly, but any title/meta CTR optimisation is a ranking-element change → owner-gated.
- The 4 ranking-element items from Phase 2A's appendix (course `<title>`, breadcrumb dedup on ~29 template pages, blog.php DOCTYPE, BBA title cannibalization) remain owner decisions.
- Rolling CollegeOrUniversity schema out to the other standalone college pages beyond BPIT — follow-up once BPIT proves the pattern.
