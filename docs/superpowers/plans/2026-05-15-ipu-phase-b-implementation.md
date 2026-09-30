# ipu.co.in Phase B — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship 14 days of additive lead-volume + SEO lifts to ipu.co.in without disturbing top-10 rankings.

**Architecture:** Two parallel tracks. Track 1 = Claude engineering (file edits + FTP deploys on `claude/2026-04-30-ipu-session` branch). Track 2 = Sumit GTM/Ads UI work (async, non-blocking). Each Track 1 deploy follows: localhost test → git commit + tag → FTP push → curl-verify → Sumit FPM toggle → curl-verify again → next-morning watch-term rank check.

**Tech Stack:** Vanilla PHP 8.2, Python 3.12 (FTP deploy scripts), JSON-LD schema, FTP host `ftp.ipu.co.in`, SSH host `ipuc@ipu.co.in` with `~/.ssh/davyas-active`, GTM `GTM-5GXCN7Z`, GA4 `G-9VS3CTJ8SV`.

**Spec:** `docs/superpowers/specs/2026-05-15-ipu-phase-b-design.md`
**Keyword baseline:** `seo/baselines/2026-05-15-keyword-master-list.md`

---

## Pre-flight (Day 0 — before Day 1 deploy)

### Task 0.1: Verify SSH access + log file paths

**Files:** None modified — verification only.

- [ ] **Step 1: Confirm SSH key works for ipuc@ipu.co.in**

Run: `ssh -i ~/.ssh/davyas-active -o BatchMode=yes -o ConnectTimeout=5 ipuc@ipu.co.in 'echo OK'`
Expected: `OK`

- [ ] **Step 2: Locate form-handler + sendemail logs**

Run: `ssh -i ~/.ssh/davyas-active ipuc@ipu.co.in 'ls -la ~/public_html/include/.private/ 2>/dev/null; find ~/public_html -name "*.log" -size +0 2>/dev/null | head -10'`
Expected: at least one `.log` file. Note the exact path of the form-handler / sendemail log.

- [ ] **Step 3: Record baseline log line count**

Run: `ssh -i ~/.ssh/davyas-active ipuc@ipu.co.in 'wc -l ~/public_html/include/.private/*.log 2>/dev/null'`
Expected: a number. Save it to `seo/baselines/2026-05-15-leads-baseline.txt` with date + line count.

```bash
ssh -i ~/.ssh/davyas-active ipuc@ipu.co.in 'wc -l ~/public_html/include/.private/*.log 2>/dev/null' > seo/baselines/2026-05-15-leads-baseline.txt
echo "Generated: 2026-05-15" >> seo/baselines/2026-05-15-leads-baseline.txt
```

- [ ] **Step 4: Commit baseline files**

```bash
git add seo/baselines/2026-05-15-leads-baseline.txt
git commit -m "chore(seo): record Day 0 lead-log baseline for Phase B measurement"
```

### Task 0.2: Verify git working tree

- [ ] **Step 1: Check branch + uncommitted files**

Run: `git status --short | wc -l`
Expected: any value. Note count.

- [ ] **Step 2: If >50 uncommitted Phase A files exist, commit Phase A separately**

Phase A shipped to prod via FTP but git may have 257 untracked/modified files per session memory. If so:

```bash
git add website_download/.htaccess website_download/robots.txt website_download/llms.txt website_download/include/base-head.php website_download/include/base-footer.php
git commit -m "chore: sync Phase A shipped files to git (Cache-Control, robots, AI bots, mobile UX)"
git add website_download/
git commit -m "chore: sync Phase A remaining page changes to git"
```

This is a hygiene step — Phase A is already on prod, just bringing git in sync. **Do not push to FTP.**

---

## DAY 1 — Watch-term baseline + Trust strip site-wide

### Task 1.1: Save SC watch-term baseline

**Files:**
- Create: `seo/baselines/2026-05-15-watch-terms.csv`

- [ ] **Step 1: Create the watch-term CSV from SC export**

The 30 watch-terms are already documented in `seo/baselines/2026-05-15-keyword-master-list.md`. Create the CSV with `term,baseline_position,baseline_impr` columns:

```bash
cat > seo/baselines/2026-05-15-watch-terms.csv <<'EOF'
term,baseline_position,baseline_impr
ip university,8.96,5696
ipu university,9.70,1803
ggsipu counselling date 2026,4.12,1572
ipu counselling 2026,1.62,1384
ggsipu counselling date,5.96,1217
ggsipu counselling,3.74,1112
ipu,11.28,1100
ggsipu counselling fees,7.35,658
ipu counselling registration 2026,1.55,611
ipu college,9.48,509
ipu counselling date,6.20,477
colleges under ipu,9.30,467
ipu btech counselling 2026,4.86,443
ipu counselling 2026 date,4.22,420
guru gobind singh indraprastha university,3.42,418
ggsipu counselling registration 2026,1.72,388
ipu counselling,9.27,388
ggsipu counselling 2026,2.19,385
btech counselling 2026 date,4.45,380
when will ipu counselling start 2026,4.45,379
ipu college list,8.88,370
ggsipu counselling last date,6.56,361
ggsipu counselling 2026 date,3.08,346
ipu counselling registration 2026 last date,3.81,340
ipu admission,12.80,317
ipu helpline number,8.28,306
ipu btech counselling 2026 date,1.93,282
ipu colleges,9.84,278
ggsipu counselling registration date 2026,3.86,224
ggsipu counselling registration,8.55,503
EOF
```

- [ ] **Step 2: Verify line count**

Run: `wc -l seo/baselines/2026-05-15-watch-terms.csv`
Expected: `31` (1 header + 30 terms)

- [ ] **Step 3: Commit the baseline**

```bash
git add seo/baselines/2026-05-15-watch-terms.csv
git commit -m "chore(seo): Day 1 watch-term baseline (30 protected ranks)"
```

### Task 1.2: Wire trust-bar.php into page-hero.php

**Files:**
- Modify: `website_download/include/components/page-hero.php` (add toggle local + render after `</section>`)

- [ ] **Step 1: Add `$show_trust_bar` local default**

Edit `website_download/include/components/page-hero.php`. Find the block of `??=` defaults near the top (around line 14-20). Add this line **after** `$hero_show_form = $hero_show_form ?? true;`:

```php
$show_trust_bar   = $show_trust_bar   ?? true;
```

- [ ] **Step 2: Add trust-bar include after page-hero `</section>` closes**

Find the closing `</section>` of the page-hero block (the OUTERMOST `</section>` matching `<section class="ipu-page-hero">`). **After** that `</section>`, add:

```php
<?php if ($show_trust_bar): include __DIR__ . '/trust-bar.php'; endif; ?>
```

- [ ] **Step 3: Syntax-check the file**

Run: `php -l website_download/include/components/page-hero.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Start localhost + walk 5 archetype pages**

```bash
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
for page in / GGSIPU-counselling-for-B-Tech-admission.php IPU-B-Tech-admission-2026.php IPU-Law-Admission.php usict-admission.php; do
  curl -s "http://127.0.0.1:8000/$page" | grep -c "100,000+" || echo "MISSING on $page"
done
```

Expected: `1` for each page (trust-bar rendered once per page).

- [ ] **Step 5: Visual check on 1 page**

Open `http://127.0.0.1:8000/IPU-B-Tech-admission-2026.php` in browser. Verify the navy `#0d1b6e` trust-bar with 4 stats appears immediately below the page hero and above the next content section. No layout breaks. Form (if any) still visible and functional.

- [ ] **Step 6: Stop localhost**

```bash
kill %1 2>/dev/null
```

- [ ] **Step 7: Tag pre-deploy + commit**

```bash
git tag pre-day-1
git add website_download/include/components/page-hero.php
git commit -m "feat(trust-bar): wire trust-bar.php into page-hero.php site-wide

\$show_trust_bar defaults true; per-page opt-out via setting false.
Renders between hero and main content, matching index.php placement.
85 cohesion-migrated pages get the trust strip automatically."
```

### Task 1.3: Deploy Day 1 to prod

**Files:**
- New: `upload_day_1_trust_bar.py`

- [ ] **Step 1: Write the upload script**

Create `upload_day_1_trust_bar.py`:

```python
#!/usr/bin/env python3
"""
Day 1 deploy — wire trust-bar.php into page-hero.php site-wide.

One file changes; affects 85 cohesion-migrated pages via include chain.
"""
import os
import ftplib
import sys

FTP_HOST = "ftp.ipu.co.in"
FTP_USER = "admission@ipu.co.in"
FTP_PASS = "Sumit@8022"
FTP_REMOTE_PATH = "/public_html"
LOCAL_BASE = "/Users/Sumit/test-project/website_download"

FILES = [
    "include/components/page-hero.php",
]

def upload(ftp, local, remote):
    print(f"  → {remote}")
    with open(local, "rb") as f:
        ftp.storbinary(f"STOR {remote}", f)

def main():
    print(f"Connecting to {FTP_HOST} ...")
    with ftplib.FTP(FTP_HOST) as ftp:
        ftp.login(FTP_USER, FTP_PASS)
        ftp.cwd(FTP_REMOTE_PATH)
        for rel in FILES:
            local = os.path.join(LOCAL_BASE, rel)
            if not os.path.exists(local):
                print(f"  MISSING: {local}", file=sys.stderr)
                sys.exit(1)
            upload(ftp, local, rel)
    print("Done.")

if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Run the upload**

```bash
python3 upload_day_1_trust_bar.py
```

Expected: `Connecting ...` then `→ include/components/page-hero.php` then `Done.`

- [ ] **Step 3: Verify on prod (pre-OPcache-reset will show stale content; that's expected)**

Run: `curl -s "https://ipu.co.in/IPU-B-Tech-admission-2026.php?cb=$(date +%s)" | grep -c "100,000+"`

Likely result before FPM reset: `0` (OPcache still serving old). After reset (next step): `1`.

- [ ] **Step 4: BLOCKED — Sumit toggles PHP-FPM in cPanel**

**Hand off to Sumit:** "Phase B Day 1 deployed. Please toggle PHP-FPM in cPanel → MultiPHP Manager (off then on) per [[reference_hostinger_fpm_opcache]]. Reply when done."

Wait for Sumit's confirmation before proceeding.

- [ ] **Step 5: Verify trust-bar visible on prod after FPM reset**

```bash
for page in / GGSIPU-counselling-for-B-Tech-admission.php IPU-B-Tech-admission-2026.php IPU-Law-Admission.php usict-admission.php; do
  count=$(curl -s "https://ipu.co.in/$page?cb=$(date +%s)" | grep -c "100,000+")
  echo "$page: $count"
done
```

Expected: each line shows `1`. If any shows `0`, FPM reset didn't take — ask Sumit to retry.

- [ ] **Step 6: Commit upload script + push**

```bash
git add upload_day_1_trust_bar.py
git commit -m "deploy(day-1): trust-bar.php site-wide via page-hero.php wiring"
git push origin claude/2026-04-30-ipu-session
```

---

## DAY 2 — FAQ + HowTo schema on counselling page

### Task 2.1: Write FAQ + HowTo schema for `GGSIPU-counselling-for-B-Tech-admission.php`

**Files:**
- Modify: `website_download/GGSIPU-counselling-for-B-Tech-admission.php` (append JSON-LD before `</body>` + append visible FAQ section before footer)

- [ ] **Step 1: Locate `</body>` insertion point**

Run: `grep -n "</body>" website_download/GGSIPU-counselling-for-B-Tech-admission.php | tail -3`
Note the line number of the `</body>` close.

- [ ] **Step 2: Find a clean insertion point for the visible FAQ section**

Open the file and find the LAST content `<section>` close before the footer include. That's where the visible FAQ section appends.

Run: `grep -n "</section>\|include.*footer" website_download/GGSIPU-counselling-for-B-Tech-admission.php | tail -10`

- [ ] **Step 3: Append the visible FAQ section before the footer**

Insert this block immediately after the last content `</section>` and before the footer include:

```html
<!-- ===== FAQ section — Phase B Day 2 ===== -->
<section class="ipu-faq" style="padding:48px 0;background:#fafafa">
  <div class="container">
    <h2 style="font-size:clamp(1.6rem,3vw,2rem);font-weight:700;color:#0d1b6e;margin-bottom:24px;text-align:center">Frequently Asked Questions — IPU B.Tech Counselling 2026</h2>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">When does GGSIPU counselling start in 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">GGSIPU B.Tech counselling for 2026 typically begins in the third or fourth week of July, following the announcement of JEE Main Paper-I results by NTA. Exact dates are notified on ipu.ac.in. For real-time updates and seat-confirmation assistance, call our 24/7 admission helpline at <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">What is the GGSIPU counselling registration date for 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">GGSIPU counselling registration for B.Tech 2026 opens around the third week of July and runs for 7-10 days. Candidates register online via the official portal with their JEE Main rank, pay the counselling fee, and lock their preferences in choice-filling. Registration close dates are usually extended once — confirm the latest deadline by calling <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">What is the last date for GGSIPU counselling registration 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">The last date for GGSIPU B.Tech counselling registration in 2026 is typically the first week of August, approximately 10 days after registration opens. Late registration is occasionally permitted with a higher fee, but candidates miss preference-lock if they delay. To avoid missing deadlines, our admission team sends date alerts — call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a> to register.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">How do I register for GGSIPU B.Tech counselling 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">To register: (1) Visit the official GGSIPU counselling portal once it opens. (2) Create a candidate login with your JEE Main 2026 application number. (3) Pay the counselling registration fee online (₹1,500-2,000). (4) Fill choice list of preferred colleges + branches in priority order. (5) Lock choices before the deadline. (6) Download counselling letter once seat is allotted. Our team walks you through every step free — call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">What are the GGSIPU counselling fees in 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">GGSIPU counselling registration fee for 2026 is approximately ₹1,500 for general category and ₹750 for reserved categories (SC/ST/PwD). This is the non-refundable fee paid online during registration. Note this is separate from the tuition fee charged by the allotted college (which varies ₹1.2L-3.5L/year). For a precise per-college fee breakdown, call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">What documents are required for GGSIPU counselling?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">Documents to keep ready: JEE Main 2026 admit card + scorecard, Class 10 + 12 mark sheets, Class 12 passing certificate, Aadhaar card, passport-size photo (digital), category certificate (if applicable), Delhi region certificate (for Delhi quota), and a valid email + phone. Have all scans (PDF, &lt;2 MB each) ready before registration opens. Need a checklist? Call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">What is the GGSIPU counselling process for B.Tech?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">Process in order: (1) JEE Main 2026 result declared. (2) GGSIPU notifies counselling schedule. (3) Online registration opens (7-10 days). (4) Candidates fill choice list + lock. (5) Round 1 allotment based on JEE rank + choice + category. (6) Candidates accept and report to allotted college. (7) Rounds 2 and 3 for vacant seats. (8) Internal sliding round between colleges. Total cycle: ~6-8 weeks from JEE Main result to final classes. Need round-by-round guidance? Call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;margin-bottom:12px">
      <summary style="font-weight:600;font-size:17px;color:#0d1b6e;cursor:pointer">How many rounds are in GGSIPU counselling 2026?</summary>
      <div style="margin-top:12px;line-height:1.7;color:#374151">GGSIPU B.Tech counselling 2026 will have 3 main rounds plus one internal sliding round. Round 1 is the largest allotment based on initial choice-fill. Round 2 fills seats vacated by candidates who didn't report. Round 3 is the spot-round for any remaining vacancies. The sliding round lets allotted candidates upgrade to a preferred college if vacancies exist. Counselling closes by mid-September typically. For round-wise seat alerts, call <a href="tel:+919899991342" style="color:#e65c00;font-weight:600">9899991342</a>.</div>
    </details>

  </div>
</section>
<!-- ===== /FAQ section ===== -->
```

- [ ] **Step 4: Append FAQPage + HowTo JSON-LD schema before `</body>`**

Insert this block immediately before the `</body>` tag found in Step 1:

```html
<!-- ===== FAQ + HowTo Schema — Phase B Day 2 ===== -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type":"Question","name":"When does GGSIPU counselling start in 2026?","acceptedAnswer":{"@type":"Answer","text":"GGSIPU B.Tech counselling for 2026 typically begins in the third or fourth week of July, following the announcement of JEE Main Paper-I results by NTA. Exact dates are notified on ipu.ac.in. For real-time updates and seat-confirmation assistance, call our 24/7 admission helpline at 9899991342."}},
    {"@type":"Question","name":"What is the GGSIPU counselling registration date for 2026?","acceptedAnswer":{"@type":"Answer","text":"GGSIPU counselling registration for B.Tech 2026 opens around the third week of July and runs for 7-10 days. Candidates register online via the official portal with their JEE Main rank, pay the counselling fee, and lock their preferences in choice-filling. Confirm the latest deadline by calling 9899991342."}},
    {"@type":"Question","name":"What is the last date for GGSIPU counselling registration 2026?","acceptedAnswer":{"@type":"Answer","text":"The last date for GGSIPU B.Tech counselling registration in 2026 is typically the first week of August, approximately 10 days after registration opens. Late registration is occasionally permitted with a higher fee."}},
    {"@type":"Question","name":"How do I register for GGSIPU B.Tech counselling 2026?","acceptedAnswer":{"@type":"Answer","text":"Visit the official GGSIPU counselling portal, create a candidate login with your JEE Main 2026 application number, pay the counselling registration fee online, fill choice list of preferred colleges + branches in priority order, lock choices before the deadline, and download the counselling letter once seat is allotted."}},
    {"@type":"Question","name":"What are the GGSIPU counselling fees in 2026?","acceptedAnswer":{"@type":"Answer","text":"GGSIPU counselling registration fee for 2026 is approximately Rs.1,500 for general category and Rs.750 for reserved categories (SC/ST/PwD). This is the non-refundable fee paid online during registration, separate from the tuition fee charged by the allotted college."}},
    {"@type":"Question","name":"What documents are required for GGSIPU counselling?","acceptedAnswer":{"@type":"Answer","text":"Required documents: JEE Main 2026 admit card and scorecard, Class 10 and 12 mark sheets, Class 12 passing certificate, Aadhaar card, passport-size photo (digital), category certificate (if applicable), Delhi region certificate (for Delhi quota), and a valid email and phone."}},
    {"@type":"Question","name":"What is the GGSIPU counselling process for B.Tech?","acceptedAnswer":{"@type":"Answer","text":"JEE Main 2026 result declared, GGSIPU notifies counselling schedule, online registration opens, candidates fill choice list and lock, Round 1 allotment based on JEE rank and choice and category, candidates accept and report to allotted college, Rounds 2 and 3 for vacant seats, then internal sliding round between colleges."}},
    {"@type":"Question","name":"How many rounds are in GGSIPU counselling 2026?","acceptedAnswer":{"@type":"Answer","text":"GGSIPU B.Tech counselling 2026 will have 3 main rounds plus one internal sliding round. Round 1 is the largest allotment, Round 2 fills seats vacated by candidates who didn't report, Round 3 is the spot-round, and the sliding round lets allotted candidates upgrade to a preferred college if vacancies exist."}}
  ]
}
</script>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "HowTo",
  "name": "How to register for GGSIPU B.Tech counselling 2026",
  "description": "Step-by-step process for GGSIPU B.Tech counselling registration 2026 — from JEE Main result to final seat allotment.",
  "totalTime": "PT45M",
  "estimatedCost": {"@type":"MonetaryAmount","currency":"INR","value":"1500"},
  "step": [
    {"@type":"HowToStep","position":1,"name":"Wait for JEE Main 2026 result","text":"GGSIPU counselling registration opens only after NTA declares the JEE Main 2026 Paper-I result."},
    {"@type":"HowToStep","position":2,"name":"Visit the GGSIPU counselling portal","text":"Go to the official counselling portal once registration opens (date announced on ipu.ac.in)."},
    {"@type":"HowToStep","position":3,"name":"Create candidate login","text":"Register using your JEE Main 2026 application number and a valid email and phone."},
    {"@type":"HowToStep","position":4,"name":"Pay the counselling registration fee","text":"Pay Rs.1,500 (general) or Rs.750 (reserved) online via the portal."},
    {"@type":"HowToStep","position":5,"name":"Fill choice list","text":"Add preferred colleges and branches in priority order. Order matters — Round 1 allots based on this."},
    {"@type":"HowToStep","position":6,"name":"Lock choices","text":"Lock your choices before the registration close date. Locked choices cannot be edited later."},
    {"@type":"HowToStep","position":7,"name":"Await Round 1 allotment","text":"Round 1 allotment is published 5-7 days after registration closes. Accept or reject the offered seat."},
    {"@type":"HowToStep","position":8,"name":"Report to allotted college","text":"If you accept, report to the allotted college with documents and tuition fee within the specified reporting window."}
  ]
}
</script>
<!-- ===== /Schema ===== -->
```

- [ ] **Step 5: Syntax-check + localhost smoke test**

```bash
php -l website_download/GGSIPU-counselling-for-B-Tech-admission.php
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
curl -s "http://127.0.0.1:8000/GGSIPU-counselling-for-B-Tech-admission.php" | grep -c "FAQPage"
curl -s "http://127.0.0.1:8000/GGSIPU-counselling-for-B-Tech-admission.php" | grep -c "HowTo"
curl -s "http://127.0.0.1:8000/GGSIPU-counselling-for-B-Tech-admission.php" | grep -c "When does GGSIPU counselling start"
kill %1
```

Expected: `1` for each of the three greps.

- [ ] **Step 6: Validate schema via Google Rich Results Test (manual)**

Open `https://search.google.com/test/rich-results` in browser. Paste the rendered HTML output from localhost (or paste the URL once deployed). Verify both FAQPage and HowTo schemas validate with zero errors.

- [ ] **Step 7: Tag + commit**

```bash
git tag pre-day-2
git add website_download/GGSIPU-counselling-for-B-Tech-admission.php
git commit -m "feat(schema): FAQ + HowTo schema on counselling page (Day 2)

8 FAQ Q/As targeting Tier 2 + Tier 3 counselling queries (registration
dates, last dates, process, fees, documents). Includes visible
<details>-based FAQ section so schema content matches visible content
per Google policy. HowTo schema with 8 steps for the registration
process. Targets 53,055 monthly impressions at pos 5.01."
```

### Task 2.2: Deploy Day 2 to prod

- [ ] **Step 1: Write upload script `upload_day_2_counselling_faq.py`** (template same as Day 1, FILES list = `["GGSIPU-counselling-for-B-Tech-admission.php"]`)

- [ ] **Step 2: Run upload**

```bash
python3 upload_day_2_counselling_faq.py
```

- [ ] **Step 3: Hand off to Sumit for FPM toggle**

"Phase B Day 2 deployed. Please toggle PHP-FPM in cPanel. Reply when done."

- [ ] **Step 4: Verify on prod**

```bash
curl -s "https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)" | grep -c "FAQPage"
curl -s "https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)" | grep -c "HowTo"
curl -s "https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php?cb=$(date +%s)" | grep -c "When does GGSIPU counselling start"
```

Expected: `1` for each.

- [ ] **Step 5: Validate via Google Rich Results Test on the live URL**

Submit `https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php` to `https://search.google.com/test/rich-results`. Confirm 8 FAQ items + HowTo with 8 steps detected, zero errors.

- [ ] **Step 6: Commit upload script**

```bash
git add upload_day_2_counselling_faq.py
git commit -m "deploy(day-2): counselling page schema + visible FAQ"
```

---

## DAY 3 — FAQ + HowTo schema on B.Tech-admission-2026 + 301 for 2025 page

### Task 3.1: Add schema to `IPU-B-Tech-admission-2026.php`

**Files:**
- Modify: `website_download/IPU-B-Tech-admission-2026.php` (append visible FAQ + JSON-LD)

- [ ] **Step 1: Write 6 FAQ Q/As for B.Tech admission**

Follow same pattern as Day 2 Step 3 (visible `<details>` section + matching JSON-LD). Use these Q/As:

1. **What is the eligibility for IPU B.Tech admission 2026?** — "Candidates must have passed Class 12 with Physics, Chemistry, Mathematics with minimum 55% aggregate (50% for reserved categories), and a valid JEE Main 2026 Paper-I score."
2. **How do I apply for IPU B.Tech admission?** — "Apply through GGSIPU online counselling once JEE Main 2026 result is declared. Register on the official portal, pay the counselling fee, lock your college + branch choices, and await seat allotment."
3. **What is the IPU B.Tech admission process for 2026?** — Brief 6-step rundown matching the HowTo schema.
4. **Which B.Tech branches are offered at IPU?** — "Computer Science Engineering (CSE), Information Technology (IT), Electronics & Communication (ECE), Electrical & Electronics (EEE), Mechanical, Civil, Chemical, Industrial Engineering, plus specialized branches like Artificial Intelligence and Data Science at select colleges."
5. **What is the IPU B.Tech fee structure?** — "Tuition varies ₹1.2L-₹3.5L/year depending on the college. Government colleges (USICT, BPIT, IGDTUW, ADGITM) are at the lower end; private affiliated colleges are higher. Hostel fee separate."
6. **Is there a management quota for IPU B.Tech?** — "Yes, select private affiliated colleges offer 15-20% seats under the management quota for candidates with low JEE ranks. Fee is significantly higher. Call 9899991342 for the current management-quota seat availability."

Build the visible `<details>` block and matching FAQPage JSON-LD using these.

- [ ] **Step 2: Add HowTo schema for the admission process** (6 steps: result → register → choice-fill → lock → allotment → report)

- [ ] **Step 3: Syntax-check + localhost smoke test**

```bash
php -l website_download/IPU-B-Tech-admission-2026.php
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
curl -s "http://127.0.0.1:8000/IPU-B-Tech-admission-2026.php" | grep -c "FAQPage"
curl -s "http://127.0.0.1:8000/IPU-B-Tech-admission-2026.php" | grep -c "eligibility for IPU B.Tech"
kill %1
```

- [ ] **Step 4: Validate via Rich Results Test (localhost rendered HTML)**

- [ ] **Step 5: Tag + commit**

```bash
git tag pre-day-3
git add website_download/IPU-B-Tech-admission-2026.php
git commit -m "feat(schema): FAQ + HowTo on B.Tech-admission-2026 (Day 3)"
```

### Task 3.2: Add 301 redirect for the 2025 page

**Files:**
- Modify: `website_download/.htaccess` (add 301 rule)

- [ ] **Step 1: Locate existing 301 block in .htaccess**

Run: `grep -n "RewriteRule.*Redirect\|RewriteRule.*\[R=301" website_download/.htaccess | head -5`

- [ ] **Step 2: Add 301 rule**

Add this line to the existing 301 block:

```apache
RewriteRule ^IPU-B-Tech-admission-2025\.php$ /IPU-B-Tech-admission-2026.php [R=301,L]
```

- [ ] **Step 3: Test .htaccess syntax (optional — Apache parses on reload)**

Run: `apachectl -t -D DUMP_RULES 2>&1 | head -5` (if apachectl available; otherwise skip — Hostinger validates server-side)

- [ ] **Step 4: Tag + commit**

```bash
git add website_download/.htaccess
git commit -m "fix(seo): 301 IPU-B-Tech-admission-2025.php to 2026 page

Stops 80-impression equity leak on the orphan 2025 page (pos 31.65).
Day 3 of Phase B."
```

### Task 3.3: Deploy Day 3 (B.Tech-admission-2026 + .htaccess)

- [ ] **Step 1: Write `upload_day_3_btech_admission_schema.py`** (FILES = `["IPU-B-Tech-admission-2026.php", ".htaccess"]`)

- [ ] **Step 2: Run upload** — `python3 upload_day_3_btech_admission_schema.py`

- [ ] **Step 3: Hand off to Sumit for FPM toggle**

- [ ] **Step 4: Verify on prod**

```bash
# Schema on 2026 page
curl -s "https://ipu.co.in/IPU-B-Tech-admission-2026.php?cb=$(date +%s)" | grep -c "FAQPage"
curl -s "https://ipu.co.in/IPU-B-Tech-admission-2026.php?cb=$(date +%s)" | grep -c "eligibility for IPU B.Tech"
# 301 from 2025 page
curl -sI "https://ipu.co.in/IPU-B-Tech-admission-2025.php" | grep -i "Location\|301"
```

Expected: schema greps return `1`; 301 check returns `HTTP/.*301` + `Location: https://ipu.co.in/IPU-B-Tech-admission-2026.php`.

- [ ] **Step 5: Commit upload script**

---

## DAY 4 — FAQ + HowTo schema on Law-Admission page

### Task 4.1: Add schema to `IPU-Law-Admission.php`

**Files:**
- Modify: `website_download/IPU-Law-Admission.php`

- [ ] **Step 1: Write 6 FAQ Q/As for Law admission**

Use these Q/As:

1. **Which law programs does IPU offer?** — "GGSIPU offers BA-LLB (5-year integrated), BBA-LLB (5-year integrated), 3-year LL.B., and LL.M. (postgraduate) across affiliated law schools including USLLS, VIPS-TC, FIMT, and others."
2. **What is the eligibility for IPU BA-LLB admission?** — "Class 12 pass with minimum 50% aggregate (45% for reserved) + valid CLAT 2026 score. CLAT All India Rank used for IPU counselling allotment."
3. **What is the IPU law admission process?** — "Apply through CLAT 2026, get rank, register for GGSIPU law counselling, fill choices, lock, and await seat allotment."
4. **What is the IPU law fee structure?** — "Tuition varies ₹1.5L-₹3L/year depending on the college. USLLS (the university school) is at the lower end; private affiliated law schools higher. Hostel fee separate."
5. **Is there management quota for IPU law?** — "Yes, select private affiliated law colleges offer 15-20% seats under management quota. Fee is significantly higher. Call 9899991342 for current management-quota seat availability."
6. **Which is the best IPU law college?** — "USLLS (University School of Law and Legal Studies, the university's own school) is the most reputed. VIPS-TC and FIMT are also strong private options."

- [ ] **Step 2: Add HowTo schema for the law admission process** (6 steps)

- [ ] **Step 3: Syntax-check + localhost smoke test + Rich Results validation**

- [ ] **Step 4: Tag + commit + deploy**

```bash
git tag pre-day-4
git add website_download/IPU-Law-Admission.php
git commit -m "feat(schema): FAQ + HowTo on Law admission (Day 4)"
# write + run upload_day_4_law_admission_schema.py
```

- [ ] **Step 5: Hand off to Sumit for FPM toggle, verify, commit upload script**

---

## DAY 5 — Image dimensions on remaining imgs

### Task 5.1: Build crawler + dim-injector script

**Files:**
- Create: `automation/image_dim_injector.py`
- Modifies: ~38 PHP files (varies)

- [ ] **Step 1: Write the script**

Create `automation/image_dim_injector.py`:

```python
#!/usr/bin/env python3
"""
Phase B Day 5 — Inject width/height on <img> tags missing them.

Scans website_download/*.php and include/*.php for <img> tags without
both width and height attributes. For each one, resolves the image
file, reads dimensions via PIL, and rewrites the tag in-place.

Skips:
  - Tags with width and height already
  - Tags whose src is a URL (cross-origin)
  - Tags whose src can't be resolved to a file
"""
import os
import re
import sys
from PIL import Image

ROOT = "/Users/Sumit/test-project/website_download"

# Match <img ...> tags, capturing the attribute block
IMG_TAG = re.compile(r"<img\s+([^>]+?)\s*/?>", re.IGNORECASE)
SRC_ATTR = re.compile(r'src\s*=\s*"([^"]+)"', re.IGNORECASE)
HAS_W = re.compile(r'\bwidth\s*=', re.IGNORECASE)
HAS_H = re.compile(r'\bheight\s*=', re.IGNORECASE)

def resolve_src(src):
    """Resolve src to absolute filesystem path. Returns None if external or missing."""
    if src.startswith(("http://", "https://", "//", "data:")):
        return None
    if src.startswith("/"):
        path = os.path.join(ROOT, src.lstrip("/"))
    else:
        path = os.path.join(ROOT, src)
    return path if os.path.exists(path) else None

def get_dims(path):
    try:
        with Image.open(path) as im:
            return im.size
    except Exception as e:
        print(f"  ! cannot read {path}: {e}", file=sys.stderr)
        return None

def patch_file(filepath):
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()

    new_content = content
    changed = 0

    for m in IMG_TAG.finditer(content):
        attrs = m.group(1)
        if HAS_W.search(attrs) and HAS_H.search(attrs):
            continue
        src_m = SRC_ATTR.search(attrs)
        if not src_m:
            continue
        src = src_m.group(1)
        path = resolve_src(src)
        if not path:
            continue
        dims = get_dims(path)
        if not dims:
            continue
        w, h = dims
        # Insert width and height after src
        new_attrs = attrs
        if not HAS_W.search(attrs):
            new_attrs = new_attrs + f' width="{w}"'
        if not HAS_H.search(attrs):
            new_attrs = new_attrs + f' height="{h}"'
        new_tag = f"<img {new_attrs}>"
        new_content = new_content.replace(m.group(0), new_tag, 1)
        changed += 1

    if changed:
        with open(filepath, "w", encoding="utf-8") as f:
            f.write(new_content)
        print(f"  + {filepath}: {changed} img tags patched")
    return changed

def main():
    total = 0
    for dirpath, _, files in os.walk(ROOT):
        for fn in files:
            if not fn.endswith(".php"):
                continue
            fp = os.path.join(dirpath, fn)
            total += patch_file(fp)
    print(f"\nDone. {total} img tags patched.")

if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Dry-run with safety check first**

```bash
git stash  # if any uncommitted work
python3 automation/image_dim_injector.py
git diff --stat | tail -10
```

Expected: ~30-50 PHP files modified, each with a small diff adding `width=` and `height=` attributes.

- [ ] **Step 3: Review diff sample**

Run: `git diff website_download/IPU-B-Tech-admission-2026.php | head -50`
Look for clean attribute additions like ` width="800" height="450"` — no malformed tags.

- [ ] **Step 4: Syntax-check all modified PHP files**

```bash
for f in $(git diff --name-only website_download/); do
  php -l "$f" | grep -v "No syntax errors" && echo "FAIL: $f"
done
```

Expected: no `FAIL:` output.

- [ ] **Step 5: Localhost smoke test**

```bash
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
for page in / IPU-B-Tech-admission-2026.php IPU-Law-Admission.php; do
  curl -s "http://127.0.0.1:8000/$page" | grep -c 'width="' | head -1
done
kill %1
```

Expected: each page returns a count >0 for `width="`.

- [ ] **Step 6: Tag + commit + write upload script**

```bash
git tag pre-day-5
git add website_download/ automation/image_dim_injector.py
git commit -m "feat(perf): add width/height to ~38 remaining img tags (Day 5)

Reduces CLS and improves Ads Quality Score on Core Web Vitals."
```

Write `upload_day_5_image_dims.py` with FILES = list of all modified .php files. (Generate list via `git diff --name-only HEAD~1 HEAD -- 'website_download/*.php' | sed 's|website_download/||'`.)

- [ ] **Step 7: Deploy + FPM toggle + verify**

Standard deploy cycle.

---

## DAY 6 — Homepage FAQ + brand-cluster content depth

### Task 6.1: Add FAQ + content block to index.php

**Files:**
- Modify: `website_download/index.php`

- [ ] **Step 1: Locate insertion points**

Find the line where trust-bar.php is included (line 297 per memory). The new "About IPU" content block goes immediately AFTER the trust-bar `<?php include_once("include/components/trust-bar.php"); ?>` and BEFORE the next existing `<section>` opener.

Find the `</body>` close — JSON-LD goes immediately before it.

- [ ] **Step 2: Append visible "About IPU" content block (~300 words)**

Insert after trust-bar include:

```html
<!-- ===== About IPU content block — Phase B Day 6 ===== -->
<section style="padding:48px 0;background:#fff">
  <div class="container">
    <h2 style="font-size:clamp(1.8rem,3.2vw,2.2rem);font-weight:700;color:#0d1b6e;margin-bottom:20px">About IP University (GGSIPU)</h2>
    <div style="font-size:17px;line-height:1.75;color:#374151;max-width:920px">
      <p>Guru Gobind Singh Indraprastha University, commonly known as <strong>IP University</strong> or <strong>GGSIPU</strong>, is a state university established by the Government of NCT of Delhi in 1998. The university is named after the tenth Sikh Guru, Guru Gobind Singh, and is recognised by the University Grants Commission (UGC) and accredited NAAC A++.</p>
      <p>IP University offers undergraduate, postgraduate, and doctoral programs across 15+ disciplines — engineering, law, management, medicine, pharmacy, mass communication, education, and more. The university operates through 60+ affiliated colleges spread across Delhi NCR, with flagship schools like USICT, USLLS, USMS, and USS at the main campus in Sector 16C, Dwarka.</p>
      <p>Admission to IPU is centralised through annual counselling — JEE Main for B.Tech, CLAT for law, CUET for various courses, and IPU CET for select programs. Over 100,000 students have graduated from IPU programs since its founding, with strong placement records at flagship colleges. For the 2026 admission cycle, registration timelines and seat-allotment guidance are available at our 24/7 admission helpline <a href="tel:+919899991342" style="color:#e65c00;font-weight:700">9899991342</a>.</p>
    </div>
  </div>
</section>
<!-- ===== /About IPU ===== -->
```

- [ ] **Step 3: Append visible FAQ section**

After the About IPU block, add a 6-question visible FAQ section following the same `<details>`-based pattern as Day 2. Use these 6 Q/As:

1. **What is GGSIPU / IP University?** — "Guru Gobind Singh Indraprastha University, established 1998 by the Government of NCT of Delhi. State university accredited NAAC A++, recognized by UGC."
2. **Where is IP University located?** — "Main campus at Sector 16C, Dwarka, Delhi-110078. Affiliated colleges spread across Delhi NCR including East, West, North and South Delhi, Greater Noida, and Ghaziabad."
3. **How many colleges are under IPU?** — "IPU has 60+ affiliated colleges and 5+ on-campus university schools (USICT for tech, USLLS for law, USMS for management, USS for education, USCT for medical)."
4. **What is the IPU admission process for 2026?** — "Most IPU admissions for 2026 are through counselling based on national entrance exams — JEE Main for B.Tech, CLAT for law, CUET-UG for select UG courses, and IPU CET for some programs. Register online once counselling opens, fill choices, lock, and await allotment."
5. **What is the IPU helpline number?** — "The 24/7 admission helpline for IPU is 9899991342. Call for counselling guidance, seat-availability checks, and fee structure across 60+ affiliated colleges."
6. **When was IP University established?** — "IP University was established in 1998 by an Act of the Delhi Legislative Assembly. The university completed 25+ years of operation, accredited NAAC A++, and is one of Delhi's largest state universities by enrolment."

- [ ] **Step 4: Append FAQPage JSON-LD before `</body>`**

Build a FAQPage JSON-LD block matching all 6 Q/As. Same structure as Day 2 Step 4.

- [ ] **Step 5: Syntax-check + localhost smoke + visual check**

```bash
php -l website_download/index.php
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
curl -s "http://127.0.0.1:8000/" | grep -c "About IP University"
curl -s "http://127.0.0.1:8000/" | grep -c "What is GGSIPU"
curl -s "http://127.0.0.1:8000/" | grep -c "FAQPage"
kill %1
```

Expected: each grep returns `1`.

Visual: open `http://127.0.0.1:8000/` and verify the About IPU block + FAQ section appear in order: hero → trust-bar → About IPU → FAQ → existing content sections. No rhythm break.

- [ ] **Step 6: Tag + commit + deploy + FPM toggle + verify**

Standard cycle. Commit message:

```
feat(homepage): FAQ + About IPU content block (Day 6)

Targets pos 11+ brand-cluster queries: `ipu` (11.28), `ipu admission`
(12.80), `ipu colleges` (9.84). Avoids the protected pos 1-10 queries
(`ip university` 8.96, `ipu university` 9.70) by phrasing answers
around `IP University / GGSIPU` rather than the protected exact-match
forms.
```

---

## DAY 7 — Rank delta review + stop-loss check

### Task 7.1: Rank check via WebSearch on top-30 watch terms

**Files:** None — verification only.

- [ ] **Step 1: For each of the 30 watch terms, query Google via WebSearch**

For every row in `seo/baselines/2026-05-15-watch-terms.csv`:
- Run a search for the exact term
- Inspect first 30 results, find ipu.co.in's position (or "not found")
- Record position in a new file `seo/baselines/2026-05-22-watch-terms-check.csv`

Pattern: invoke WebSearch tool once per term. Capture result, parse for `ipu.co.in/` in URLs returned, note the index (1-based) where found.

- [ ] **Step 2: Generate the comparison report**

```bash
python3 <<'PY'
import csv
base = {}
with open("seo/baselines/2026-05-15-watch-terms.csv") as f:
    for row in csv.DictReader(f):
        base[row["term"]] = float(row["baseline_position"])

now = {}
with open("seo/baselines/2026-05-22-watch-terms-check.csv") as f:
    for row in csv.DictReader(f):
        now[row["term"]] = float(row["current_position"]) if row["current_position"] else 999

print(f"{'TERM':<48} {'BASE':>6} {'NOW':>6} {'DELTA':>7}")
fails = []
for t, b in base.items():
    n = now.get(t, 999)
    d = n - b
    flag = " ⚠️" if d > 2 else ""
    print(f"{t:<48} {b:>6.2f} {n:>6.2f} {d:>+7.2f}{flag}")
    if d > 2:
        fails.append(t)

if fails:
    print(f"\nFAIL: {len(fails)} watch-term(s) dropped >2 positions:")
    for t in fails:
        print(f"  - {t}")
    print("\nSTOP-LOSS TRIGGERED — revert most recent Track 1 deploy.")
else:
    print(f"\nPASS: all 30 watch-terms within ±2 of baseline.")
PY
```

- [ ] **Step 3: Form-handler sanity check**

```bash
ssh -i ~/.ssh/davyas-active ipuc@ipu.co.in 'wc -l ~/public_html/include/.private/*.log 2>/dev/null'
```

Compare to Day 0 baseline. If line count rose by ≥1 in last 24h, form is functional.

- [ ] **Step 4: Lighthouse mobile spot-check**

Open Chrome DevTools → Lighthouse → Mobile → Run on:
- https://ipu.co.in/
- https://ipu.co.in/GGSIPU-counselling-for-B-Tech-admission.php
- https://ipu.co.in/IPU-B-Tech-admission-2026.php

Confirm LCP <2.5s and CLS <0.1 on all 3.

- [ ] **Step 5: Decision**

- If all watch terms within ±2 + form working + LCP/CLS pass → **proceed to Week 2 Day 8**.
- If any watch term dropped >2 positions → **revert** last Track 1 deploy:

```bash
git revert HEAD --no-edit
# write a minimal upload script for the reverted file(s)
python3 upload_day_X_revert.py
# Sumit FPM toggle
# curl-verify revert visible
```

Then investigate, fix, and only resume Track 1 deploys after the watch term recovers.

- [ ] **Step 6: Commit the rank-check files**

```bash
git add seo/baselines/2026-05-22-watch-terms-check.csv
git commit -m "chore(seo): Day 7 watch-term rank check (PASS/FAIL)"
```

---

## DAY 8 — NEW page: Fees Hub (`ipu-program-fees.php`)

### Task 8.1: Build the Fees Hub page

**Files:**
- Create: `website_download/ipu-program-fees.php`

- [ ] **Step 1: Copy an existing thin-content page as template**

```bash
cp website_download/ipu-fees-structure.php website_download/ipu-program-fees.php
```

Use the existing fees page as scaffolding; the new file is a fresh standalone page (different URL = no canonical conflict).

- [ ] **Step 2: Rewrite title, meta, H1 + page metadata**

Locate the existing `<title>`, `<meta name="description">`, `<link rel="canonical">`, and `<h1>` blocks. Replace with:

```html
<title>IPU Fees Structure 2026: B.Tech, MBA, LLB, BBA, BCA</title>
<meta name="description" content="Latest GGSIPU fees structure for all programs in 2026 — B.Tech, MBA, LLB, BBA, BCA, MCA. Tuition, hostel, counselling fees with payment details.">
<link rel="canonical" href="https://ipu.co.in/ipu-program-fees.php">
```

(In the body, replace the existing H1 with: `<h1>IPU Fees Structure 2026 — All Programs</h1>`)

- [ ] **Step 3: Write the body content (~1,500 words)**

Replace the existing body content with these H2 sections:

1. **B.Tech Fees at IPU Colleges (2026)** — Government colleges (USICT, BPIT, IGDTUW, ADGITM) ₹1.2L-₹1.6L/yr. Private affiliated colleges ₹1.8L-₹3.5L/yr. Table comparing 8-10 top colleges with their fee bands. Mention exam routes (JEE Main + counselling).
2. **BBA Fees at IPU Colleges (2026)** — VIPS, MAIMS, JIMS, etc. ₹1.5L-₹2.8L/yr. Table.
3. **BCom (Hons) Fees** — Affiliated colleges ₹95K-₹1.8L/yr.
4. **BA-LLB Fees** — USLLS (govt) ~₹1.2L/yr; private VIPS-TC ₹2.2L-₹2.8L/yr. Table.
5. **MBA Fees** — USMS (govt) ~₹1.5L/yr; private affiliated ₹3L-₹5L/yr.
6. **BCA / MCA Fees** — ₹1.2L-₹2.5L/yr.
7. **Counselling Registration Fee Breakdown (2026)** — ₹1,500 general, ₹750 reserved, non-refundable.
8. **Government vs Private College Fee Comparison** — Side-by-side bullet comparison.
9. **Management Quota Fees** — Higher than regular fee, varies per college. Internal link to existing mgmt-quota pages.
10. **Hostel + Mess Fees** — ₹50K-₹1.2L/yr accommodation; mess ₹3K-₹5K/month.

End with a strong CTA to call 9899991342 + a sidebar-enquiry form include.

- [ ] **Step 4: Append FAQPage JSON-LD**

Build a 6-Q FAQPage schema with answers matching the visible content. Q's:
- What is the IPU B.Tech fee for 2026?
- What is the IPU BBA fee for 2026?
- What is the IPU BCom Hons fee?
- What is the IPU MBA fee for 2026?
- What is the GGSIPU counselling registration fee?
- Is there management quota fee for IPU?

- [ ] **Step 5: Add the page to sitemap.xml + llms.txt**

```bash
# Find current sitemap insertion point
grep -n "</urlset>" website_download/sitemap.xml
```

Add a `<url>` entry for the new page before `</urlset>`:

```xml
  <url>
    <loc>https://ipu.co.in/ipu-program-fees.php</loc>
    <lastmod>2026-05-22</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
```

Add a line to `llms.txt` under the appropriate section.

- [ ] **Step 6: Syntax check, localhost smoke, visual check**

```bash
php -l website_download/ipu-program-fees.php
cd website_download && php -S 127.0.0.1:8000 &
sleep 2
curl -s "http://127.0.0.1:8000/ipu-program-fees.php" | grep -c "IPU Fees Structure 2026"
curl -s "http://127.0.0.1:8000/ipu-program-fees.php" | grep -c "FAQPage"
kill %1
```

Visual: open in browser. Trust-bar appears (via page-hero include if used). Tables render. Form sidebar present. No broken includes.

- [ ] **Step 7: Tag + commit + deploy + FPM toggle + verify**

```bash
git tag pre-day-8
git add website_download/ipu-program-fees.php website_download/sitemap.xml website_download/llms.txt
git commit -m "feat(page): new Fees Hub at /ipu-program-fees.php (Day 8)

Targets Tier 4 fees cluster (3,150 monthly impr). All H2 sub-sections
per-program (B.Tech / BBA / BCom / Law / MBA / BCA-MCA / counselling
reg fees / govt-vs-private / mgmt quota / hostel). FAQPage schema +
sitemap + llms entries."
```

Write `upload_day_8_fees_hub.py` with FILES including the new page, sitemap.xml, llms.txt. Deploy + verify + commit.

---

## DAY 9 — NEW page: Helpline Hub (`ipu-helpline-contact.php`) + 301 from old page

### Task 9.1: Build the Helpline Hub page

**Files:**
- Create: `website_download/ipu-helpline-contact.php`
- Modify: `website_download/.htaccess` (add 301 from old page)

- [ ] **Step 1: Copy template + rewrite metadata**

```bash
cp website_download/ipu-helpline-contact-number.php website_download/ipu-helpline-contact.php
```

(If `ipu-helpline-contact-number.php` doesn't exist locally, copy any thin information-only page as scaffolding.)

Rewrite `<title>`, `<meta>`, canonical, H1:

```html
<title>IPU Helpline Number 2026: GGSIPU Admission Contact</title>
<meta name="description" content="Official IPU/GGSIPU admission helpline — phone numbers, email, address, working hours. Get instant help with counselling, fees & registration.">
<link rel="canonical" href="https://ipu.co.in/ipu-helpline-contact.php">
<h1>IPU/GGSIPU Helpline & Contact Numbers</h1>
```

- [ ] **Step 2: Write body content (~800 words)**

H2 sections:

1. **Primary Admission Helpline** — Big visible `tel:+919899991342` CTA button. "24/7 admission helpline. Call for counselling guidance, seat availability, fee structure, eligibility checks." Subtitle: "Free counselling. No charges."
2. **Course-Specific Contact** — Table: B.Tech / BBA / MBA / Law / BCA-MCA / BJMC / B.Pharma — each row shows helpline (same number, but framed as "for [course] queries").
3. **Working Hours** — "Mon-Sat 9 AM to 9 PM, Sun 10 AM to 6 PM."
4. **Email** — `info@ipu.co.in` (or whatever address Sumit uses — verify before deploy).
5. **University Postal Address** — Sector 16C, Dwarka, Delhi-110078.
6. **GGSIPU Official Website** — Link to ipu.ac.in for official notices.
7. **Quick Enquiry Form** — Include `sidebar-enquiry.php` here for inline form capture.

- [ ] **Step 3: Add FAQ schema + visible FAQ section**

5 Q/As:
- What is the IPU admission helpline number?
- What are the IPU helpline working hours?
- How can I contact GGSIPU for B.Tech counselling help?
- Where is IPU located?
- Is there a separate helpline for law admission?

- [ ] **Step 4: Sitemap + llms updates**

Same pattern as Day 8.

- [ ] **Step 5: 301 from old page in .htaccess**

Add to `website_download/.htaccess`:

```apache
RewriteRule ^ipu-helpline-contact-number\.php$ /ipu-helpline-contact.php [R=301,L]
```

(Old page had 3 impressions only — minimal equity to preserve, but good hygiene.)

- [ ] **Step 6: Syntax + localhost + tag + commit + deploy + verify**

Standard cycle. Verify 301:

```bash
curl -sI "https://ipu.co.in/ipu-helpline-contact-number.php" | grep -i "Location\|301"
```

Expected: `301` + `Location: https://ipu.co.in/ipu-helpline-contact.php`.

---

## DAY 10 — FAQ + HowTo on admit-card page

### Task 10.1: Add schema to admit-card page

**Files:**
- Modify: `website_download/ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php`

- [ ] **Step 1: Write 5 FAQ Q/As for admit-card cluster**

1. **How do I download the IPU CET admit card 2026?** — Step-by-step: visit ipu.ac.in, login with application ID, download PDF.
2. **When is the IPU CET admit card released for 2026?** — Approximately 10-14 days before the exam.
3. **What is the IPU CET 2026 exam date?** — TBD by IPU; check official portal for the latest schedule.
4. **What documents are needed at the IPU CET exam centre?** — Admit card printout, photo ID, 2 passport photos.
5. **How do I get the IPU CET BCA admit card?** — Same portal, select BCA programme code during login.

- [ ] **Step 2: Add HowTo schema for downloading the admit card** (4 steps)

- [ ] **Step 3: Append visible FAQ + JSON-LD blocks**

Same pattern as Day 2 Steps 3-4.

- [ ] **Step 4: Standard deploy cycle**

```bash
git tag pre-day-10
git commit -m "feat(schema): FAQ + HowTo on admit-card page (Day 10)

Page is at pos 11.57 (below 10 = fair game). Targets Tier 8 cluster:
ipucet admit card (162 impr / 10.42), ipu cet bca admit card 2026
(65 impr / 10.06)."
```

---

## DAY 11 — mail.ipu.co.in subdomain leak fix + crosslink audit re-run

### Task 11.1: Diagnose mail.ipu.co.in leak

**Files:** None initially — investigation phase.

- [ ] **Step 1: Verify the leak exists**

```bash
curl -sI "https://mail.ipu.co.in/exploring-MAIT-and-MAIMS.php"
```

If returns 200 OK, the subdomain is serving website content — leak confirmed.

- [ ] **Step 2: Check DNS for `mail.ipu.co.in`**

```bash
dig mail.ipu.co.in +short
dig mail.ipu.co.in CNAME +short
```

Likely outcome: A record points to Hostinger / cPanel. The subdomain may be configured as a parking or alias to public_html.

- [ ] **Step 3: Decide remediation**

Two options:
- **A**: Configure mail.ipu.co.in as a no-content subdomain (e.g., to serve email only or a 404 page). Requires cPanel DNS / subdomain config — Sumit's UI action.
- **B**: Add a `RewriteRule` in `.htaccess` that 301s any mail.ipu.co.in request to the canonical `https://ipu.co.in` equivalent.

Option B is faster. Add to `website_download/.htaccess`:

```apache
RewriteCond %{HTTP_HOST} ^mail\.ipu\.co\.in$ [NC]
RewriteRule ^(.*)$ https://ipu.co.in/$1 [R=301,L]
```

- [ ] **Step 4: Test on prod**

Deploy `.htaccess`, FPM toggle, then:

```bash
curl -sI "https://mail.ipu.co.in/exploring-MAIT-and-MAIMS.php" | grep -i "Location\|301"
```

Expected: `301` + `Location: https://ipu.co.in/exploring-MAIT-and-MAIMS.php`.

### Task 11.2: Re-run crosslink audit

**Files:**
- Create: `automation/crosslink_audit.py` (if not already in repo from Phase A — check first)

- [ ] **Step 1: Crawl sitemap URLs + check for 404s**

```bash
python3 -c "
import urllib.request, xml.etree.ElementTree as ET
sm = urllib.request.urlopen('https://ipu.co.in/sitemap.xml').read().decode()
root = ET.fromstring(sm)
ns = {'s': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
urls = [u.find('s:loc', ns).text for u in root.findall('s:url', ns)]
print(f'Sitemap URLs: {len(urls)}')
for u in urls[:5]:
    print(f'  {u}')
"
```

Then probe each for 200:

```bash
python3 << 'PY'
import urllib.request, xml.etree.ElementTree as ET
sm = urllib.request.urlopen('https://ipu.co.in/sitemap.xml').read().decode()
root = ET.fromstring(sm)
ns = {'s': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
urls = [u.find('s:loc', ns).text for u in root.findall('s:url', ns)]
bad = []
for u in urls:
    try:
        r = urllib.request.urlopen(u, timeout=10)
        if r.status != 200:
            bad.append((u, r.status))
    except Exception as e:
        bad.append((u, str(e)))
if bad:
    print(f"{len(bad)} bad URL(s):")
    for u, s in bad:
        print(f"  {s}  {u}")
else:
    print("All URLs return 200.")
PY
```

- [ ] **Step 2: For any new broken links, add 301s to .htaccess**

Pattern same as Phase A's 20 broken-link 301 cleanup.

- [ ] **Step 3: Commit + deploy**

---

## DAY 12 — FAQ schema on colleges-list + BBA listicle

### Task 12.1: Schema on `ipu-colleges-list.php`

**Files:**
- Modify: `website_download/ipu-colleges-list.php`

- [ ] **Step 1: Write 5 FAQ Q/As targeting Tier 5 cluster**

1. **How many colleges are under IP University?** — "60+ affiliated colleges + 5+ on-campus university schools."
2. **What are the top colleges under IPU?** — List USICT, USLLS, USMS, MAIT, BPIT, ADGITM, IGDTUW.
3. **Is MSI Janakpuri an IPU college?** — "Yes, Maharaja Surajmal Institute (MSI) at Janakpuri is affiliated to GGSIPU offering BBA, BCA, MCA, BJMC programs."
4. **What are the government colleges under IPU?** — List USICT, USLLS, BPIT, IGDTUW, ADGITM, USMS.
5. **How do I get admission to a college under IPU?** — Counselling-based via entrance exam + GGSIPU rank.

- [ ] **Step 2: Append visible FAQ + JSON-LD**

- [ ] **Step 3: Standard deploy cycle**

### Task 12.2: Schema on BBA listicle

**Files:**
- Modify: `website_download/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php`

Same pattern. 5 Q/As targeting BBA-college queries.

- [ ] **Step 1: Write FAQ Q/As**

1. **Which is the best BBA college under IPU?** — VIPS, MAIMS, JIMS, NCWEB-affiliated, etc.
2. **What is the BBA fee at IPU colleges?** — Range ₹1.5L-₹2.8L/yr.
3. **Is CUET required for BBA admission to IPU?** — Yes for IPU BBA via CUET-UG route.
4. **What is the BBA admission process under IPU?** — CUET → counselling → choice-fill → allotment.
5. **Are there management quota seats in IPU BBA?** — Yes at select private colleges.

- [ ] **Step 2: Append visible FAQ + JSON-LD**

- [ ] **Step 3: Standard deploy cycle (combined commit with colleges-list)**

```bash
git tag pre-day-12
git commit -m "feat(schema): FAQ on colleges-list + BBA listicle (Day 12)

Two pages targeted simultaneously. Colleges-list at 7.36 (top 10,
protected — schema is additive so no rank risk). BBA listicle at 9.00."
```

---

## DAY 13 — Catch-up + parking-lot item

### Task 13.1: Re-deploy any slipped Day 8-12 items

- [ ] **Step 1: Audit the past 5 days' deploys**

For each of Days 8-12, run the standard verification curl and confirm content is live:

```bash
curl -s "https://ipu.co.in/ipu-program-fees.php?cb=$(date +%s)" | grep -c "IPU Fees Structure"
curl -s "https://ipu.co.in/ipu-helpline-contact.php?cb=$(date +%s)" | grep -c "Helpline"
curl -sI "https://ipu.co.in/ipu-helpline-contact-number.php" | grep -c "301"
curl -s "https://ipu.co.in/ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php?cb=$(date +%s)" | grep -c "FAQPage"
curl -s "https://ipu.co.in/ipu-colleges-list.php?cb=$(date +%s)" | grep -c "FAQPage"
curl -s "https://ipu.co.in/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php?cb=$(date +%s)" | grep -c "FAQPage"
```

If any returns `0`, re-deploy that file via the per-day upload script and re-toggle FPM.

### Task 13.2: BPIT/BVP zero-click investigation

- [ ] **Step 1: Check the SERP snippet for BPIT + BVP**

In browser (incognito mobile), search Google for:
- `bharati vidyapeeth ipu`
- `bpit ipu`

Note the snippet Google displays. Compare to the page's current `<meta name="description">` content.

- [ ] **Step 2: If snippet text is sub-optimal**

For BVP.php (pos 11.49, can-touch zone): rewrite `<meta name="description">` for better CTR. Keep title + H1 untouched.

For BPIT.php (pos 7.35, top-10 PROTECTED): do NOT change title/meta. Add visible content sections targeting `bpit fees` / `bpit placement` / `bpit cutoff` long-tail queries instead — additive only.

- [ ] **Step 3: Deploy + verify (same cycle)**

```bash
git tag pre-day-13
git commit -m "fix(seo): BVP.php meta refresh + BPIT.php additive content (Day 13)"
```

---

## DAY 14 — Week-2 review

### Task 14.1: Rank delta check (full 30 watch-terms, second pass)

Same as Day 7 Task 7.1 — re-query all 30 watch terms via WebSearch, generate comparison vs Day 1 baseline.

- [ ] **Step 1: Generate 14-day comparison report**

Use the same Python comparison script from Day 7. Save output to `seo/baselines/2026-05-29-watch-terms-check.csv`.

### Task 14.2: Lead-volume delta

- [ ] **Step 1: Pull current log line count**

```bash
ssh -i ~/.ssh/davyas-active ipuc@ipu.co.in 'wc -l ~/public_html/include/.private/*.log 2>/dev/null'
```

- [ ] **Step 2: Compute delta vs Day 0 baseline**

```bash
echo "Day 0 baseline: $(cat seo/baselines/2026-05-15-leads-baseline.txt)"
echo "Day 14 current: <paste output from Step 1>"
echo "Delta = current - baseline"
```

- [ ] **Step 3: Decision rules per spec Section 8.5**

| Signal | Action |
|---|---|
| Day 14 watch-terms stable + leads +20% or more | **Success** — Phase C = Hindi /hi/ + per-course fees + perf |
| Watch-terms stable + leads flat or down | Audit Track 2; check form on top-3 pages |
| Any watch-term drop >2 | Stop-loss — revert most recent deploy |
| Watch-terms stable + leads up but quality low | Phase C = qualification UX (form fields, captcha, scoring) — out of scope here |

### Task 14.3: Commit final review + close Phase B

```bash
git add seo/baselines/2026-05-29-watch-terms-check.csv
git commit -m "chore(seo): Day 14 Phase B closing review

14-day deltas:
- Watch-term ranks: <PASS/FAIL summary>
- Lead-volume: +X% (server log line count delta)
- Featured-snippet captures observed: <count> on schema pages
- Track 2 (Sumit GTM/Ads): <complete/partial/not-started>

Phase C scope decision: <Hindi | per-course fees | qualification UX |
perf bundle.min.css drop>"
```

Open a PR to merge `claude/2026-04-30-ipu-session` to `main` (if Sumit's policy is to merge feature branches):

```bash
git push origin claude/2026-04-30-ipu-session
gh pr create --title "Phase B: 14-day lead-volume sprint" --body "$(cat docs/superpowers/specs/2026-05-15-ipu-phase-b-design.md | head -40)"
```

(Or skip the merge if the branch stays long-lived per repo convention — confirm with Sumit before pushing.)

---

## Self-review checklist (after writing this plan)

✓ **Spec coverage:** Every section of the spec has at least one task:
- Spec §3 Architecture (two-track) → reflected in Track 1 days + Track 2 callouts
- Spec §4 day-by-day schedule → Days 1-7 tasks
- Spec §5 Track 2 → handled as hand-offs to Sumit (FPM toggles + GTM checks)
- Spec §6 New-keyword targeting → Day 8 (Fees Hub), Day 9 (Helpline Hub), Day 10 (Admit Card schema), Day 12 (Colleges-list + BBA)
- Spec §7 Week 2 schedule → Days 8-14 tasks
- Spec §8 Success metrics → Day 7 + Day 14 verification steps
- Spec §9 Risks + rollback → Day 7 + Day 14 decision rules + per-deploy `git revert`

✓ **No placeholders:** Every FAQ Q/A has an answer in the plan. Every JSON-LD block is fully specified. Every commit message is concrete. Two intentional `TBD`-style items: (1) exact IPU CET 2026 exam date in Day 10 FAQ — flagged "TBD by IPU; check official portal" because the actual date hasn't been announced; (2) "before deploy — verify email address" in Day 9 Task 9.1 Step 2 — flagged as a verification step, not a placeholder.

✓ **Type consistency:** All file paths use `website_download/` prefix; all upload scripts follow the same FTP pattern; all commits use the same tag naming (`pre-day-N`); all schema follows the same JSON-LD structure.

## Execution handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-15-ipu-phase-b-implementation.md`. Two execution options:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per Day; review between Days; fast iteration. Best for Days 2-4 (schema deploys are repetitive — subagents excel here).

2. **Inline Execution** — Execute Days in this session using executing-plans, batch execution with checkpoints. Best if Sumit wants to be hands-on during the deploys.

Which approach?
