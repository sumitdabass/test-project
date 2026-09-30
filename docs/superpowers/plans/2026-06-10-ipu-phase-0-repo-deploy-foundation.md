# IPU Phase 0 — Repo & Deploy Foundation — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make git the source of truth for production, replace ~36 one-off FTP scripts with one safe env-based deployer, serialize + gate the news auto-deploy pipeline, and remove the dead legacy stack — so every later phase deploys safely.

**Architecture:** Vanilla PHP site under `website_download/`; live pages use the modern `base-head/base-nav/base-footer` + Bootstrap-5 + `app.js` stack. Legacy stack (`index-old.php`, `index-new.php`, `common-head/header/header2/footer/call-widgets.php`, `form-code*.php`, jQuery 1.12.4 + plugins, orphan CSS) is confirmed dead (referenced only by the two 301'd index-* pages and by "Replaces:" comments). Deploy tooling is Python `ftplib`; `upload_news.py` is the clean env-based template. News pipeline: `news-scrape.yml` (daily) → push to `main` → `news-build-deploy.yml` → `build-news.php` → `upload_news.py --sync`.

**Tech Stack:** Python 3.12 (ftplib, argparse, pytest-free stdlib test), PHP 8.2 (custom test harness `scripts/tests/run.php`), GitHub Actions YAML, git.

**Reference spec:** `docs/superpowers/specs/2026-06-10-ipu-site-improvement-program-design.md`

**Hard constraints (from spec):** No FTP-credential rotation (owner-owned). No ranking-element changes. No prod deploy without owner go-ahead — the only prod-touching step (Task 6 legacy removal) ends at a gate. All deletions verified zero-live-reference first.

---

## Files

**Create:**
- `deploy.py` — consolidated env-based deployer (Task 3)
- `tests/test_deploy.py` — tests for deploy.py manifest/preflight/dry-run logic (Task 3)
- `deploy/archive/` — destination for the ~36 retired one-off scripts (Task 3)
- `deploy/DELETION-CHECKLIST-2026-06-10.md` — prod files to remove (Task 6)

**Modify:**
- `.gitignore` (Task 2)
- `.github/workflows/news-scrape.yml` (Task 4, 5)
- `.github/workflows/news-build-deploy.yml` (Task 4, 5)
- `scripts/build-news.php` — add `news_validate_post()` (Task 5)
- `scripts/tests/test_build.php` — add validation test cases (Task 5)
- `automation/news-scraper.py` — fix the "green on partial failure" exit semantics + docstring (Task 5)

**Commit (currently untracked, live):** `website_download/.user.ini`, `website_download/include/phone.php`, `website_download/include/news-related-content.php`, `website_download/ipu-ba-llb-cutoff.php`, `website_download/ipu-bba-cutoff.php`, live images/fonts (Task 1).

**Delete (dead):** see Task 6.

---

## Task 1: Commit the live untracked production code

**Files:**
- Commit: `website_download/.user.ini`, `website_download/include/phone.php`, `website_download/include/news-related-content.php`, `website_download/ipu-ba-llb-cutoff.php`, `website_download/ipu-bba-cutoff.php`, live assets under `website_download/assets/`
- Do NOT commit (handled later / never): the dead legacy files (Task 6), `error_log`/`course/error_log` (Task 7), `AI_AGENT_README.md`, `seo-report-march-2026.html`, `assets/images.zip`, `.htaccess.prod-pull`

- [ ] **Step 1: Classify untracked assets as live vs dead.** Build the set of images/JS/CSS referenced by any page EXCEPT the dead `index-old.php`/`index-new.php`. Run from repo root:

```bash
cd /Users/Sumit/test-project/website_download
# All asset basenames referenced by LIVE pages (exclude the two dead index files)
grep -rhoE "assets/(images|fonts|css|js)/[A-Za-z0-9._/-]+" . --include='*.php' \
  --exclude=index-old.php --exclude=index-new.php \
  | sed 's#.*/##' | sort -u > /tmp/live_assets.txt
wc -l /tmp/live_assets.txt
# Untracked asset files, basename only
git -C /Users/Sumit/test-project status --porcelain website_download/assets | grep '^??' \
  | sed 's/^?? //; s#.*/##' | sort -u > /tmp/untracked_assets.txt
echo "=== untracked assets NOT referenced by any live page (candidate dead) ==="
comm -23 /tmp/untracked_assets.txt /tmp/live_assets.txt
```

Expected: dead-stack images like `call.gif`, `banner-bg*.jpg`, `about-*.jpg`, `avatar-*.png`, `brand-*.png`, `testimonial*.jpg`, `counter-bg*.jpg`, `poster.jpg`, `pricing-bg.jpg`, `item.png`, `shape/*`, `choose-thumb.png`, `banner-man.png` surface as un-referenced. Confirm by spot-grep before treating any as dead — some (e.g. `logo.png`, `favicon.ico`, course images) WILL be referenced and must be committed.

- [ ] **Step 2: Stage the confirmed-live files.** Stage the live PHP/config explicitly, and only the referenced assets:

```bash
cd /Users/Sumit/test-project
git add website_download/.user.ini \
        website_download/include/phone.php \
        website_download/include/news-related-content.php \
        website_download/ipu-ba-llb-cutoff.php \
        website_download/ipu-bba-cutoff.php
# Stage live assets: every referenced one (loop over /tmp/live_assets.txt matches that are untracked)
git add website_download/assets/fonts/
while read -r name; do
  f=$(git status --porcelain website_download/assets | grep '^?? ' | sed 's/^?? //' | grep "/$name$")
  [ -n "$f" ] && git add "$f"
done < /tmp/live_assets.txt
```

- [ ] **Step 3: Verify nothing dead or non-deploy got staged.** 

```bash
cd /Users/Sumit/test-project
git diff --cached --name-only | grep -E "index-old|index-new|common-head|header2?\.php|call-widgets|form-code|error_log|images\.zip|AI_AGENT_README|seo-report-march|\.htaccess\.prod-pull"
```

Expected: NO output (empty). If anything matches, `git restore --staged <file>` it.

- [ ] **Step 4: Commit.**

```bash
git commit -m "chore(repo): track live production files that were never committed

Commits .user.ini, phone.php, news-related-content.php, the two cutoff
pages, fonts, and live images so git = prod. Dead legacy files and
runtime logs deliberately excluded (removed in later Phase-0 tasks)."
```

Expected: commit succeeds; `git status --porcelain website_download/` now shows only the still-untracked DEAD + non-deploy files.

---

## Task 2: Commit the pending .gitignore + ignore non-deploy artifacts

**Files:**
- Modify: `.gitignore`

- [ ] **Step 1: Add ignore rules for runtime logs and non-deploy artifacts.** Append to `.gitignore`:

```gitignore

# Production runtime logs — never commit, never deploy
website_download/error_log
website_download/course/error_log

# Local-only artifacts that must not deploy to webroot
website_download/assets/images.zip
website_download/seo-report-march-2026.html
```

- [ ] **Step 2: Verify the ignored files are now ignored.**

Run: `git check-ignore website_download/error_log website_download/course/error_log website_download/assets/images.zip`
Expected: all three paths echoed back (meaning they are ignored).

- [ ] **Step 3: Commit.**

```bash
git add .gitignore
git commit -m "chore(repo): commit pending .gitignore + ignore runtime logs and non-deploy artifacts"
```

---

## Task 3: Build the consolidated deployer `deploy.py`

**Files:**
- Create: `deploy.py`
- Create: `tests/test_deploy.py`
- Create: `deploy/archive/` (move the one-off scripts here)

- [ ] **Step 1: Write the failing test** for the pure (no-network) logic: manifest resolution, preflight missing-file detection, and dry-run never connecting. Create `tests/test_deploy.py`:

```python
import subprocess, sys, os
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

def run(args, env=None):
    e = dict(os.environ); e.update(env or {})
    return subprocess.run([sys.executable, str(ROOT / "deploy.py"), *args],
                          capture_output=True, text=True, env=e)

def test_dry_run_lists_files_without_credentials():
    # dry-run must NOT require FTP_* and must NOT connect
    r = run(["--files", "website_download/index.php", "--dry-run"],
            env={"FTP_HOST": "", "FTP_USER": "", "FTP_PASS": ""})
    assert r.returncode == 0, r.stderr
    assert "index.php" in r.stdout
    assert "dry-run" in r.stdout.lower()

def test_missing_local_file_fails_preflight():
    r = run(["--files", "website_download/does-not-exist.php", "--dry-run"])
    assert r.returncode == 1
    assert "missing" in (r.stdout + r.stderr).lower()

def test_real_deploy_requires_credentials():
    # without --dry-run and without creds, must refuse (exit 2) before connecting
    r = run(["--files", "website_download/index.php"],
            env={"FTP_HOST": "", "FTP_USER": "", "FTP_PASS": ""})
    assert r.returncode == 2
    assert "FTP_" in (r.stdout + r.stderr)

def test_delete_requires_explicit_flag_and_confirm(tmp_path):
    # --delete without --yes must refuse in non-interactive mode (exit 2)
    r = run(["--manifest", "/dev/null", "--delete", "website_download/x.php"],
            env={"DEPLOY_NONINTERACTIVE": "1"})
    assert r.returncode == 2
    assert "confirm" in (r.stdout + r.stderr).lower()
```

- [ ] **Step 2: Run the test to verify it fails.**

Run: `cd /Users/Sumit/test-project && python3 -m pytest tests/test_deploy.py -q`
Expected: FAIL / errors — `deploy.py` does not exist yet.

- [ ] **Step 3: Implement `deploy.py`.** Model on `upload_news.py` (env creds, dry-run, preflight, exit codes). Create `deploy.py`:

```python
#!/usr/bin/env python3
"""Canonical FTP deployer for ipu.co.in. Replaces the ~36 one-off upload_*.py scripts.

Credentials come ONLY from the environment (never hardcoded):
    FTP_HOST, FTP_USER, FTP_PASS, REMOTE_ROOT (default /public_html)

Usage:
    python3 deploy.py --files website_download/a.php website_download/b.php
    python3 deploy.py --manifest changes.txt          # one local path per line
    python3 deploy.py --files ... --dry-run            # list, never connect (DEFAULT-safe)
    python3 deploy.py --delete /public_html/old.php --yes   # confirm-gated remote delete
Local paths under website_download/ map to REMOTE_ROOT preserving the sub-path.
"""
from __future__ import annotations
import argparse, ftplib, os, sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
WEB = HERE / "website_download"

def remote_for(local: Path, remote_root: str) -> str:
    rel = local.resolve().relative_to(WEB.resolve())
    return f"{remote_root}/{rel.as_posix()}"

def ensure_remote_dir(ftp: ftplib.FTP, path: str) -> None:
    cwd = ""
    for part in [p for p in path.split("/") if p]:
        cwd += "/" + part
        try:
            ftp.cwd(cwd)
        except ftplib.error_perm:
            ftp.mkd(cwd); ftp.cwd(cwd)

def load_manifest(path: str) -> list[Path]:
    out = []
    with open(path) as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith("#"):
                out.append((HERE / line).resolve())
    return out

def main() -> int:
    ap = argparse.ArgumentParser(description="Canonical FTP deployer for ipu.co.in")
    ap.add_argument("--files", nargs="*", default=[], help="local paths to upload")
    ap.add_argument("--manifest", help="file with one local path per line")
    ap.add_argument("--delete", nargs="*", default=[], help="remote paths to delete (needs --yes)")
    ap.add_argument("--dry-run", action="store_true", help="list actions, never connect")
    ap.add_argument("--yes", action="store_true", help="confirm destructive --delete")
    args = ap.parse_args()

    remote_root = os.environ.get("REMOTE_ROOT", "/public_html").rstrip("/")

    locals_: list[Path] = [(HERE / f).resolve() for f in args.files]
    if args.manifest:
        locals_ += load_manifest(args.manifest)

    uploads = [(p, remote_for(p, remote_root)) for p in locals_]
    missing = [p for p, _ in uploads if not p.exists()]
    if missing:
        print("Missing local files:", file=sys.stderr)
        for m in missing:
            print(f"  - {m}", file=sys.stderr)
        return 1

    print(f"Plan: upload {len(uploads)} file(s) → {remote_root}/")
    for p, r in uploads:
        print(f"  {p.relative_to(HERE)} → {r}")
    if args.delete:
        print(f"Plan: DELETE {len(args.delete)} remote path(s):")
        for d in args.delete:
            print(f"  ✗ {d}")

    if args.dry_run:
        print("dry-run; nothing uploaded or deleted")
        return 0

    # destructive delete needs explicit confirmation
    if args.delete and not args.yes:
        if os.environ.get("DEPLOY_NONINTERACTIVE"):
            print("Refusing --delete without --yes (non-interactive). Re-run with --yes to confirm.", file=sys.stderr)
            return 2
        ans = input(f"Delete {len(args.delete)} remote file(s)? type 'yes': ")
        if ans.strip() != "yes":
            print("aborted", file=sys.stderr); return 2

    for var in ("FTP_HOST", "FTP_USER", "FTP_PASS"):
        if not os.environ.get(var):
            print(f"{var} not set in environment", file=sys.stderr); return 2

    ftp = ftplib.FTP(os.environ["FTP_HOST"], timeout=60)
    ftp.login(os.environ["FTP_USER"], os.environ["FTP_PASS"]); ftp.set_pasv(True)
    print(f"connected to {os.environ['FTP_HOST']} as {os.environ['FTP_USER']}")
    try:
        for p, r in uploads:
            ensure_remote_dir(ftp, r.rsplit("/", 1)[0])
            with open(p, "rb") as fh:
                ftp.storbinary(f"STOR {r}", fh)
            print(f"  ✓ {r}")
        for d in args.delete:
            try:
                ftp.delete(d); print(f"  ✗ deleted {d}")
            except ftplib.error_perm as e:
                print(f"  ⚠ could not delete {d}: {e}", file=sys.stderr)
    finally:
        ftp.quit()
    print(f"done: {len(uploads)} uploaded, {len(args.delete)} delete(s) attempted")
    return 0

if __name__ == "__main__":
    sys.exit(main())
```

- [ ] **Step 4: Run the test to verify it passes.**

Run: `cd /Users/Sumit/test-project && python3 -m pytest tests/test_deploy.py -q`
Expected: 4 passed. (If pytest is unavailable, run `python3 tests/test_deploy.py` after adding an `if __name__` harness, or `python3 -m pytest` after `pip install pytest`.)

- [ ] **Step 5: Archive the one-off scripts.**

```bash
cd /Users/Sumit/test-project
mkdir -p deploy/archive
git ls-files 'upload_*.py' | xargs -I{} git mv {} deploy/archive/ 2>/dev/null || true
# move untracked one-offs too
for f in upload_*.py quick_upload_index.py check_ftp_structure.py download_ftp.py; do
  [ -f "$f" ] && mv "$f" deploy/archive/ 2>/dev/null || true
done
# Keep upload_news.py at root (CI depends on its path) — restore if it got moved
[ -f deploy/archive/upload_news.py ] && git mv deploy/archive/upload_news.py upload_news.py 2>/dev/null || \
  ([ -f deploy/archive/upload_news.py ] && mv deploy/archive/upload_news.py .)
ls deploy/archive/ | head
```

Note: `upload_news.py` MUST stay at repo root — `news-build-deploy.yml` calls `python3 upload_news.py`. Verify it is still at root: `test -f upload_news.py && echo OK`.

- [ ] **Step 6: Commit.**

```bash
git add deploy.py tests/test_deploy.py deploy/
git add -A  # picks up the git mv archive moves
git commit -m "feat(deploy): consolidated env-based deploy.py (dry-run default, confirm-gated --delete) + archive ~36 one-off upload scripts

Creds env-only; no secret values changed (FTP rotation is owner-owned, out of scope)."
```

---

## Task 4: Harden both GitHub workflows (concurrency + failure alerts)

**Files:**
- Modify: `.github/workflows/news-scrape.yml`
- Modify: `.github/workflows/news-build-deploy.yml`

- [ ] **Step 1: Add a shared concurrency group + failure notification to `news-scrape.yml`.** Insert a top-level `concurrency:` block right after the `permissions:` block:

```yaml
concurrency:
  group: ipu-deploy
  cancel-in-progress: false
```

Then add a final step to the `scrape-and-commit` job (after "Dispatch build-and-deploy"):

```yaml
      - name: Notify on failure
        if: failure()
        run: |
          echo "::error::News scrape workflow FAILED — check the run log."
          echo "### ❌ News scrape FAILED" >> $GITHUB_STEP_SUMMARY
          echo "Run: ${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}" >> $GITHUB_STEP_SUMMARY
```

- [ ] **Step 2: Add the same concurrency group + failure notification to `news-build-deploy.yml`.** Insert after the `on:` block (before `jobs:`):

```yaml
concurrency:
  group: ipu-deploy
  cancel-in-progress: false
```

Add a final step to `build-and-deploy`:

```yaml
      - name: Notify on failure
        if: failure()
        run: |
          echo "::error::News build & deploy FAILED — prod may be unchanged or partially deployed."
          echo "### ❌ News build & deploy FAILED" >> $GITHUB_STEP_SUMMARY
          echo "Run: ${{ github.server_url }}/${{ github.repository }}/actions/runs/${{ github.run_id }}" >> $GITHUB_STEP_SUMMARY
```

- [ ] **Step 3: Validate the YAML.**

Run: `cd /Users/Sumit/test-project && python3 -c "import yaml,glob; [yaml.safe_load(open(f)) for f in glob.glob('.github/workflows/*.yml')]; print('YAML OK')"`
Expected: `YAML OK` (no exception). Both files share `group: ipu-deploy`, so the daily scrape-deploy and any manual deploy now serialize.

- [ ] **Step 4: Commit.**

```bash
git add .github/workflows/news-scrape.yml .github/workflows/news-build-deploy.yml
git commit -m "ci: serialize both news workflows (shared concurrency group) + add on-failure alerts"
```

---

## Task 5: Gate the news auto-deploy pipeline (content validation + safe exit + deploy gate)

**Files:**
- Modify: `scripts/build-news.php` (add `news_validate_post()`)
- Modify: `scripts/tests/test_build.php` (add validation tests)
- Modify: `automation/news-scraper.py` (exit semantics + docstring)
- Modify: `.github/workflows/news-build-deploy.yml` (require validation pass before deploy)

- [ ] **Step 1: Write failing validation tests.** Append to `scripts/tests/test_build.php` (it uses the project's `TestCase` harness — follow the existing pattern in that file). Add:

```php
// --- news_validate_post() ---
$t->group('news_validate_post');

// valid post passes
$t->ok(news_validate_post([
    'title' => 'GGSIPU Counselling 2026 Schedule',
    'slug' => 'ggsipu-counselling-2026-schedule',
    'date' => '2026-06-10',
    'category' => 'Counselling',
], str_repeat('Real body content. ', 30)) === [], 'valid post yields no errors');

// empty body rejected
$t->ok(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '2026-06-10', 'category' => 'Counselling',
], '   ')) > 0, 'empty body rejected');

// implausible date rejected
$t->ok(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '1999-13-99', 'category' => 'Counselling',
], str_repeat('body ', 30))) > 0, 'bad date rejected');

// unknown category rejected
$t->ok(count(news_validate_post([
    'title' => 'X', 'slug' => 'x', 'date' => '2026-06-10', 'category' => 'NotARealCategory',
], str_repeat('body ', 30))) > 0, 'unknown category rejected');
```

- [ ] **Step 2: Run the suite to confirm the new tests fail.**

Run: `cd /Users/Sumit/test-project && php scripts/tests/run.php`
Expected: failures referencing `news_validate_post` (undefined function).

- [ ] **Step 3: Implement `news_validate_post()` and wire it into the build.** Add to `scripts/build-news.php` (near the top, before `news_build_single_post`):

```php
/** Returns an array of human-readable problems; empty array == valid. */
function news_validate_post(array $fm, string $body): array {
    $errors = [];
    $allowed = ['Counselling', 'Admission', 'Cutoff', 'Exam', 'Result', 'News', 'General', 'Fees'];

    if (trim($fm['title'] ?? '') === '') $errors[] = 'missing title';
    if (trim($fm['slug'] ?? '') === '')  $errors[] = 'missing slug';

    $body_words = str_word_count(strip_tags($body));
    if ($body_words < 40)   $errors[] = "body too short ($body_words words; min 40)";
    if ($body_words > 4000) $errors[] = "body too long ($body_words words; max 4000)";

    $date = $fm['date'] ?? '';
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $errors[] = "implausible date: '$date'";
    } else {
        $y = (int)$d->format('Y');
        if ($y < 2020 || $y > 2030) $errors[] = "date year out of range: $y";
    }

    $cat = $fm['category'] ?? 'General';
    if (!in_array($cat, $allowed, true)) $errors[] = "unknown category: '$cat'";

    return $errors;
}
```

Then enforce it at the top of `news_build_single_post()` (right after `$fm` is parsed, before the `file_put_contents`):

```php
    $problems = news_validate_post($fm, $body_md ?? ($fm['body_md'] ?? ''));
    if ($problems) {
        fwrite(STDERR, "VALIDATION FAILED for $md_path:\n  - " . implode("\n  - ", $problems) . "\n");
        throw new RuntimeException("news_validate_post rejected $md_path");
    }
```

(Adjust the body variable name to match how `news_build_single_post` already reads the markdown body — inspect the function and use its existing local variable.)

- [ ] **Step 4: Run the suite to confirm pass.**

Run: `cd /Users/Sumit/test-project && php scripts/tests/run.php`
Expected: all tests pass, including the 4 new validation cases. A malformed post now throws (fails the CI build) instead of shipping.

- [ ] **Step 5: Add a `--sync` deletion safety floor.** In `upload_news.py`, guard `sync_delete_remote_orphans` so it refuses to delete when the local news dir is suspiciously empty. Add at the very top of `sync_delete_remote_orphans` (after `local_names = ...`):

```python
    if len(local_names) < 3:
        print(f"  ⚠ SAFETY: only {len(local_names)} local news posts — refusing to sync-delete remote orphans", file=sys.stderr)
        return []
```

(3 is a conservative floor; the live news set is well above it. Prevents an empty/corrupt checkout from unpublishing the whole news section.)

- [ ] **Step 6: Fix scraper exit semantics + docstring.** Today the scraper is "green" even when most items error, hiding partial failures. Policy for this plan: keep deploying good posts (exit 0 when ≥1 post wrote) but emit a GitHub `::warning::` so the run is visibly flagged — the Task-4 failure-notifier and the Task-5 build validation are the hard gates against bad content. Replace the final `return 1 if (errors and not written) else 0` line in `main()` with:

```python
    if errors:
        print(f"::warning::scraper had {len(errors)} error(s); see step summary", file=sys.stderr)
    return 0 if (written or not errors) else 1
```

Also fix the docstring lines that reference "OpenAI" / "GPT-4o mini" to say "Gemini (gemini-flash-latest)".

- [ ] **Step 7: Add a deploy gate to `news-build-deploy.yml`.** The build already runs `php scripts/tests/run.php` (which now includes validation) BEFORE deploy — that is the automated content gate. Add a manual approval gate by binding the deploy to a protected GitHub Environment. Add to the `build-and-deploy` job (top level, under `runs-on`):

```yaml
    environment: production-deploy
```

Document in the step summary that `production-deploy` must be created in repo Settings → Environments with a Required Reviewer (owner). Until configured, `workflow_dispatch` still works for manual deploys.

- [ ] **Step 8: Validate YAML + run full PHP suite once more.**

Run: `cd /Users/Sumit/test-project && python3 -c "import yaml,glob; [yaml.safe_load(open(f)) for f in glob.glob('.github/workflows/*.yml')]; print('YAML OK')" && php scripts/tests/run.php`
Expected: `YAML OK` and all PHP tests pass.

- [ ] **Step 9: Commit.**

```bash
git add scripts/build-news.php scripts/tests/test_build.php automation/news-scraper.py .github/workflows/news-build-deploy.yml
git commit -m "ci(news): validate scraped content before build, --sync deletion floor, honest scraper exit, production-deploy approval gate"
```

---

## Task 6: Remove the dead legacy stack

**Files:**
- Delete (tracked, via `git rm`): `website_download/include/footer.php`, plus any tracked legacy files found in Step 1
- Delete (untracked, via `rm`): `index-old.php`, `index-new.php`, `include/common-head.php`, `header.php`, `header2.php`, `call-widgets.php`, `form-code.php`, `form-codecopy.php`, the legacy `assets/js/*` (jquery-1.12.4, plugins, main.js, ajax-contact.js, bootstrap.min.js, popper.min.js), the legacy `assets/css/*` (bootstrap.min.css, bundle.css, default.css, flaticon.css, font-awesome.min.css, magnific-popup.css, nice-select.css, slick.css, style.css, style2.css), `assets/images/call.gif`
- Create: `deploy/DELETION-CHECKLIST-2026-06-10.md`
- KEEP: `assets/css/critical.min.css` (0 refs now, but spec Phase 2 may repurpose for inlined critical CSS)

- [ ] **Step 1: Re-verify zero live references for every deletion candidate.** A file is deletable only if its sole references are `index-old.php`, `index-new.php`, or comments.

```bash
cd /Users/Sumit/test-project/website_download
for f in common-head.php header.php header2.php call-widgets.php form-code.php form-codecopy.php footer.php; do
  echo "=== include/$f real includes (excluding dead index files) ==="
  grep -rnE "(include|require)(_once)?\s*\(?[\"'][^\"']*$f" . --include='*.php' \
    | grep -vE "index-old\.php|index-new\.php"
done
echo "=== confirm index-old/index-new are 301'd to / ==="
grep -nE "index-old|index-new" .htaccess
```

Expected: the per-file greps print NOTHING (only dead-page references existed); `.htaccess` shows 301 redirects for both index-* files. If ANY live page references a candidate, REMOVE it from the deletion set and note why — do not delete it.

- [ ] **Step 2: Confirm the dead index pages redirect on the LIVE site.**

```bash
for u in index-old.php index-new.php; do
  echo -n "$u → "; curl -s -o /dev/null -w "%{http_code} %{redirect_url}\n" "https://ipu.co.in/$u"
done
```

Expected: `301` redirecting to `https://ipu.co.in/` for both. Confirms removing them is safe.

- [ ] **Step 3: Write the prod deletion checklist** so the gated deploy removes these from the live server too (no orphans — per the rsync-orphan rule). Create `deploy/DELETION-CHECKLIST-2026-06-10.md`:

```markdown
# Prod deletion checklist — Phase 0 legacy cleanup (2026-06-10)

Remote root: /public_html. Delete via:
  python3 deploy.py --delete <remote paths…> --yes   (after FTP_* env set)

Pages:
- /public_html/index-old.php
- /public_html/index-new.php

Includes:
- /public_html/include/common-head.php
- /public_html/include/header.php
- /public_html/include/header2.php
- /public_html/include/footer.php
- /public_html/include/call-widgets.php
- /public_html/include/form-code.php
- /public_html/include/form-codecopy.php

Legacy JS:
- /public_html/assets/js/ajax-contact.js
- /public_html/assets/js/main.js
- /public_html/assets/js/bootstrap.min.js
- /public_html/assets/js/popper.min.js
- /public_html/assets/js/slick.min.js
- /public_html/assets/js/isotope.pkgd.min.js
- /public_html/assets/js/imagesloaded.pkgd.min.js
- /public_html/assets/js/jquery.appear.min.js
- /public_html/assets/js/jquery.counterup.min.js
- /public_html/assets/js/jquery.magnific-popup.min.js
- /public_html/assets/js/jquery.nice-select.min.js
- /public_html/assets/js/waypoints.min.js
- /public_html/assets/js/vendor/jquery-1.12.4.min.js
- /public_html/assets/js/vendor/modernizr-3.6.0.min.js

Legacy CSS:
- /public_html/assets/css/bootstrap.min.css
- /public_html/assets/css/bundle.css
- /public_html/assets/css/default.css
- /public_html/assets/css/flaticon.css
- /public_html/assets/css/font-awesome.min.css
- /public_html/assets/css/magnific-popup.css
- /public_html/assets/css/nice-select.css
- /public_html/assets/css/slick.css
- /public_html/assets/css/style.css
- /public_html/assets/css/style2.css

Images:
- /public_html/assets/images/call.gif

KEEP (do NOT delete): assets/css/critical.min.css, assets/css/bootstrap5.min.css,
assets/css/bundle.min.css, assets/js/app.js, assets/js/bootstrap.bundle.min.js
```

- [ ] **Step 4: Remove the files locally.**

```bash
cd /Users/Sumit/test-project
# tracked dead file(s)
git rm website_download/include/footer.php
# untracked dead files
rm -f website_download/index-old.php website_download/index-new.php \
      website_download/include/common-head.php website_download/include/header.php \
      website_download/include/header2.php website_download/include/call-widgets.php \
      website_download/include/form-code.php website_download/include/form-codecopy.php \
      website_download/assets/js/ajax-contact.js website_download/assets/js/main.js \
      website_download/assets/js/bootstrap.min.js website_download/assets/js/popper.min.js \
      website_download/assets/js/slick.min.js website_download/assets/js/isotope.pkgd.min.js \
      website_download/assets/js/imagesloaded.pkgd.min.js website_download/assets/js/jquery.appear.min.js \
      website_download/assets/js/jquery.counterup.min.js website_download/assets/js/jquery.magnific-popup.min.js \
      website_download/assets/js/jquery.nice-select.min.js website_download/assets/js/waypoints.min.js \
      website_download/assets/js/vendor/jquery-1.12.4.min.js website_download/assets/js/vendor/modernizr-3.6.0.min.js \
      website_download/assets/css/bootstrap.min.css website_download/assets/css/bundle.css \
      website_download/assets/css/default.css website_download/assets/css/flaticon.css \
      website_download/assets/css/font-awesome.min.css website_download/assets/css/magnific-popup.css \
      website_download/assets/css/nice-select.css website_download/assets/css/slick.css \
      website_download/assets/css/style.css website_download/assets/css/style2.css \
      website_download/assets/images/call.gif
```

- [ ] **Step 5: Verify a live-stack page still lints and renders.** Pick 3 archetype pages and confirm PHP still parses and they reference none of the removed files:

```bash
cd /Users/Sumit/test-project/website_download
for p in index.php bca-admission-ipu.php GGSIPU-counselling-for-B-Tech-admission.php ipu-counselling.php; do php -l "$p"; done
# none should reference removed assets
grep -rlE "jquery-1\.12\.4|main\.js|ajax-contact|style2?\.css|font-awesome|common-head|header2?\.php|call-widgets|form-code" . --include='*.php' | grep -v "node_modules"
```

Expected: all `php -l` → "No syntax errors"; the grep prints NOTHING.

- [ ] **Step 6: Smoke-serve locally and crosslink-walk.** Per `feedback_localhost_crosslink_test`:

```bash
cd /Users/Sumit/test-project/website_download && php -S localhost:8000 >/tmp/php-srv.log 2>&1 &
sleep 1
for p in / /index.php /bca-admission-ipu.php /ipu-counselling.php /thank-you.php; do
  echo -n "$p → "; curl -s -o /dev/null -w "%{http_code}\n" "http://localhost:8000$p"
done
# kill the server when done
kill %1 2>/dev/null
```

Expected: 200 for each live page (note: pages using `include_once("include/base-…")` resolve relative to docroot, so serve from `website_download/`). Manually confirm nav/footer/CTA render (no missing-include warnings in `/tmp/php-srv.log`).

- [ ] **Step 7: Commit.**

```bash
cd /Users/Sumit/test-project
git add -A
git commit -m "chore(legacy): remove dead legacy stack (index-old/new, legacy includes, jQuery 1.12.4 + plugins, orphan CSS, call.gif)

Confirmed zero live-page references; index-old/new are 301'd to /. Prod
deletion checklist at deploy/DELETION-CHECKLIST-2026-06-10.md for the gated FTP --delete.
Kept critical.min.css for Phase 2 critical-CSS use."
```

---

## Task 7: Repo hygiene — runtime logs + stray root docs

**Files:**
- Delete: `website_download/error_log`, `website_download/course/error_log`
- Move into `docs/status-archive/` or delete: the 12 stray root status `.md` files

- [ ] **Step 1: Delete the runtime log files** (already gitignored in Task 2, so just remove locally).

```bash
cd /Users/Sumit/test-project
rm -f website_download/error_log website_download/course/error_log
```

- [ ] **Step 2: Archive the stray root status docs.** Keep `README.md`. Move the rest into `docs/status-archive/`:

```bash
cd /Users/Sumit/test-project
mkdir -p docs/status-archive
for f in AUDIT_IMPLEMENTATION_COMPLETE.md CODE_CHANGES_SUMMARY.md CONVERSION_AUDIT_STATUS.md \
         CRITICAL_FIXES_IMPLEMENTATION.md GTM_QUICK_CHECKLIST.md GTM_SETUP_GUIDE.md \
         IMPLEMENTATION_CHECKLIST.md IMPLEMENTATION_COMPLETED.md QUICK_START_SUMMARY.md \
         READY_TO_USE_CODE_SNIPPETS.md WEBSITE_ANALYSIS_AND_RECOMMENDATIONS.md seo-analysis.md; do
  [ -f "$f" ] && mv "$f" docs/status-archive/
done
ls docs/status-archive/ | wc -l   # expect 12
```

- [ ] **Step 3: Commit.**

```bash
git add -A
git commit -m "chore(repo): drop runtime error_log files + archive 12 stray root status docs to docs/status-archive/"
```

---

## Task 8: Phase-0 deploy gate (OWNER GO-AHEAD REQUIRED)

**This is the only prod-touching step. STOP and get the owner's go-ahead before running it.**

- [ ] **Step 1: Push the branch + open/merge per the owner's git flow.** (Currently on `claude/2026-04-30-ipu-session`.) Confirm with owner whether to merge to `main` or deploy from branch.

- [ ] **Step 2: Sync before any deploy** (the news GH Action auto-commits `content/news/`): `git fetch origin && git pull --rebase` per `feedback_git_pull_before_sync`.

- [ ] **Step 3: Deploy the prod file deletions** using the checklist (creds from env, never hardcoded):

```bash
cd /Users/Sumit/test-project
export FTP_HOST=... FTP_USER=... FTP_PASS=...   # owner provides; not stored
# dry-run first
python3 deploy.py --delete $(grep '^- /public_html' deploy/DELETION-CHECKLIST-2026-06-10.md | sed 's/^- //') --dry-run
# then execute with confirmation
python3 deploy.py --delete $(grep '^- /public_html' deploy/DELETION-CHECKLIST-2026-06-10.md | sed 's/^- //') --yes
```

- [ ] **Step 4: Prod curl-verify.** Per `feedback_pre_deploy_quality_check`: confirm live pages still 200 and removed assets now 404.

```bash
for u in / /ipu-counselling.php /bca-admission-ipu.php; do echo -n "$u → "; curl -s -o /dev/null -w "%{http_code}\n" "https://ipu.co.in$u"; done
for u in /assets/css/style.css /assets/js/vendor/jquery-1.12.4.min.js /index-old.php; do echo -n "$u → "; curl -s -o /dev/null -w "%{http_code}\n" "https://ipu.co.in$u"; done
```

Expected: live pages 200; `style.css`/`jquery-1.12.4`/`index-old.php` → 404 (or 301 for index-old). 

- [ ] **Step 5: Update memory** — record Phase 0 shipped, deployer consolidation, and the news-pipeline gate.

---

## Self-Review

**Spec coverage** (spec §5 Phase 0 items 1–7):
1. Commit untracked prod code → Task 1 ✓
2. Commit `.gitignore` → Task 2 ✓
3. Consolidate deployer → Task 3 ✓
4. Harden workflows (concurrency + failure notif) → Task 4 ✓
5. Gate news pipeline (validation + approval gate + sync floor) → Task 5 ✓
6. Remove dead legacy stack → Task 6 ✓ (+ prod deletion via Task 8)
7. Delete error_logs + move stray docs → Task 7 ✓
Deploy gating → Task 8 ✓

**Placeholder scan:** Step 3 of Task 5 notes the body variable name must match the existing function local — this is a genuine "inspect-then-match" instruction, not a placeholder; the validation function and its call are fully specified. No TBD/TODO.

**Type/name consistency:** `news_validate_post(array $fm, string $body): array` defined in Task 5 Step 3 and called identically in tests (Step 1) and in `news_build_single_post` (Step 3). `deploy.py` flags `--files/--manifest/--delete/--dry-run/--yes` consistent between tests (Task 3 Step 1) and implementation (Step 3). `upload_news.py` stays at root (Task 3 Step 5) — consistent with CI in Task 5.

**Known follow-through:** Task 5 Step 6 picks the "warn-but-keep-deploying-good-posts" exit policy explicitly; Task 6 keeps `critical.min.css` for Phase 2 (flagged in both the spec and Task 6 header).
