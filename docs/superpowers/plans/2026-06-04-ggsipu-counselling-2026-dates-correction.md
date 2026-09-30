# GGSIPU Counselling 2026 — Dates Correction Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Correct the contradictory/stale GGSIPU counselling dates on the two highest-traffic counselling pages to the official "tentatively 8 June 2026" schedule (Notification 26/2026), add a dated callout + `Event` JSON-LD on each, and publish a news post — during peak counselling-date search season.

**Architecture:** Vanilla PHP site (no framework, no test suite). "Tests" are grep consistency checks, JSON-LD validity checks, and a localhost crosslink walk. Each task is a focused edit verified immediately. Deploy is the final gated checkpoint (FTP via `upload_*.py`).

**Tech Stack:** PHP 8.x static pages in `website_download/`; news pipeline `content/news/*.md` → `php scripts/build-news.php`; FTP deploy scripts.

**Source of truth:** GGSIPU Notification 26/2026 (03.06.2026) — centralized online counselling enrolment "likely to be started from 08th June 2026, tentatively" for B.Tech (131) + 11 other listed programmes; MBA (101) & MCA (105) separate.

**Accuracy rule (every task):** phrase as **"tentatively from 8 June 2026"** + cite **Notification 26/2026 (03.06.2026)**; assert only the start date + centralized/online + programme list; mark everything downstream "to be notified"; never touch `<title>`/`<meta>`/`canonical`/`<h1>`/URL.

---

## Files

- Modify: `website_download/GGSIPU-counselling-for-B-Tech-admission.php` (callout, timeline table, FAQ schema, Event JSON-LD)
- Modify: `website_download/ipu-counselling.php` (callout, dates table + lead-in, last_updated bump, Article dateModified, Event JSON-LD)
- Create: `content/news/ggsipu-centralized-online-counselling-2026-from-8-june.md`
- Regenerate (by build script): `website_download/news/*.php`, sitemap, `llms.txt`

---

### Task 1: B.Tech page — dated callout box

**Files:** Modify `website_download/GGSIPU-counselling-for-B-Tech-admission.php`

- [ ] **Step 1: Insert callout** between the Overview `</div>` (line 62) and the `<!-- Counselling Timeline -->` comment (line 64).

Find:
```php
      </div>

      <!-- Counselling Timeline -->
```
Replace with:
```php
      </div>

      <!-- Counselling Update Callout (Notification 26/2026, 03.06.2026) -->
      <div style="background:#fff8e6;border:1px solid #f7b731;border-left:5px solid #f7b731;border-radius:8px;padding:16px 18px;margin-bottom:32px;">
        <p style="margin:0 0 6px;font-weight:700;color:#1a3a6b;font-size:1.02rem;">&#128226; Counselling Update &mdash; 3 June 2026</p>
        <p style="margin:0;color:#444;line-height:1.7;font-size:0.96rem;">GGSIPU has notified (Notification No. 26/2026, dated 03.06.2026) that enrolment for <strong>Centralized Online Counselling 2026-27</strong> is <strong>likely to begin tentatively from 8 June 2026</strong>. B.Tech is listed under <strong>programme code 131</strong>. Exact registration, choice-filling and seat-allotment dates are yet to be notified on <a href="https://ipu.ac.in" target="_blank" rel="noopener">ipu.ac.in</a>. For real-time help, call our 24x7 admission helpline <a href="tel:+919899991342"><strong>9899991342</strong></a>.</p>
      </div>

      <!-- Counselling Timeline -->
```

- [ ] **Step 2: Verify** the callout is present and unique.

Run: `grep -c "Counselling Update Callout" website_download/GGSIPU-counselling-for-B-Tech-admission.php`
Expected: `1`

- [ ] **Step 3: Commit**

```bash
git add website_download/GGSIPU-counselling-for-B-Tech-admission.php
git commit -m "seo(counselling): add 8-Jun-2026 dated callout to B.Tech workhorse"
```

---

### Task 2: B.Tech page — fix the timeline table

**Files:** Modify `website_download/GGSIPU-counselling-for-B-Tech-admission.php`

- [ ] **Step 1: Replace the stale table body** (the seven `<tr>` rows). Find the block starting `<tr style="background:#f8f9fa;"><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Online Counselling Registration</td>` through the `Final Admission Closure` row, and replace the entire `<tbody>...</tbody>` content with:

```php
            <tbody>
              <tr style="background:#f8f9fa;"><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Online Counselling Enrolment Opens</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;"><strong>Tentatively 8 June 2026</strong> (Notification 26/2026)</td></tr>
              <tr><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Choice Filling &amp; Locking</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">To be notified</td></tr>
              <tr style="background:#f8f9fa;"><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Round 1 Seat Allotment</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">To be notified</td></tr>
              <tr><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Round 2 &amp; 3 Seat Allotment</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">To be notified</td></tr>
              <tr style="background:#f8f9fa;"><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Spot Round (if applicable)</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">To be notified</td></tr>
              <tr><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">Document Verification</td><td style="padding:11px 15px;border-bottom:1px solid #e0e0e0;">After Each Allotment Round</td></tr>
              <tr style="background:#f8f9fa;"><td style="padding:11px 15px;">Final Admission Closure</td><td style="padding:11px 15px;">To be notified</td></tr>
            </tbody>
```

- [ ] **Step 2: Update the footnote** below the table.

Find:
```php
        <p style="font-size:0.82rem;color:#888;margin-top:8px;">* Dates are subject to official GGSIPU notifications. Check ipu.ac.in for updates.</p>
```
Replace with:
```php
        <p style="font-size:0.82rem;color:#888;margin-top:8px;">* Enrolment start per GGSIPU Notification 26/2026 (03.06.2026) and stated as tentative. Remaining dates are yet to be notified &mdash; check ipu.ac.in for official updates.</p>
```

- [ ] **Step 3: Verify** no stale month guesses remain in the table and the new anchor is present.

Run: `grep -nE "April 2026|May &ndash; June 2026|June &ndash; July 2026|July 2026" website_download/GGSIPU-counselling-for-B-Tech-admission.php`
Expected: no output (empty)
Run: `grep -c "Tentatively 8 June 2026" website_download/GGSIPU-counselling-for-B-Tech-admission.php`
Expected: `>= 1`

- [ ] **Step 4: Commit**

```bash
git add website_download/GGSIPU-counselling-for-B-Tech-admission.php
git commit -m "seo(counselling): correct B.Tech timeline table to official 8-Jun-2026 schedule"
```

---

### Task 3: B.Tech page — fix FAQ schema date answers

**Files:** Modify `website_download/GGSIPU-counselling-for-B-Tech-admission.php`

The FAQPage schema (around lines 304–308) has three date answers that say "third/fourth week of July". Update them to the official tentative schedule so the schema matches the visible table.

- [ ] **Step 1: Replace the "When does GGSIPU counselling start" answer.**

Find:
```
"acceptedAnswer":{"@type":"Answer","text":"GGSIPU B.Tech counselling for 2026 typically begins in the third or fourth week of July, following the announcement of JEE Main Paper-I results by NTA. Exact dates are notified on ipu.ac.in. For real-time updates and seat-confirmation assistance, call our 24/7 admission helpline at 9899991342."}}
```
Replace with:
```
"acceptedAnswer":{"@type":"Answer","text":"As per GGSIPU Notification 26/2026 (03.06.2026), enrolment for centralized online B.Tech counselling 2026-27 is likely to begin tentatively from 8 June 2026. The university has stated this date is tentative; confirm on ipu.ac.in. For real-time updates and seat-confirmation assistance, call our 24/7 admission helpline at 9899991342."}}
```

- [ ] **Step 2: Replace the "registration date" answer.**

Find:
```
"acceptedAnswer":{"@type":"Answer","text":"GGSIPU counselling registration for B.Tech 2026 opens around the third week of July and runs for 7-10 days. Candidates register online via the official portal with their JEE Main rank, pay the counselling fee, and lock their preferences in choice-filling. Confirm the latest deadline by calling 9899991342."}}
```
Replace with:
```
"acceptedAnswer":{"@type":"Answer","text":"GGSIPU counselling enrolment for B.Tech 2026 is scheduled to open tentatively from 8 June 2026 (Notification 26/2026). Candidates register online via the official portal with their JEE Main rank, pay the counselling fee, and lock their preferences in choice-filling. Confirm the latest deadline by calling 9899991342."}}
```

- [ ] **Step 3: Replace the "last date" answer.**

Find:
```
"acceptedAnswer":{"@type":"Answer","text":"The last date for GGSIPU B.Tech counselling registration in 2026 is typically the first week of August, approximately 10 days after registration opens. Late registration is occasionally permitted with a higher fee."}}
```
Replace with:
```
"acceptedAnswer":{"@type":"Answer","text":"With enrolment beginning tentatively from 8 June 2026 (Notification 26/2026), the last date for GGSIPU B.Tech counselling registration is yet to be officially notified. Check ipu.ac.in or call 9899991342 for the confirmed deadline once announced."}}
```

- [ ] **Step 4: Verify** no "week of July" remains and JSON is valid.

Run: `grep -nE "week of July|first week of August" website_download/GGSIPU-counselling-for-B-Tech-admission.php`
Expected: no output
Run (validate every JSON-LD block parses):
```bash
python3 - <<'PY'
import re,json,sys
s=open("website_download/GGSIPU-counselling-for-B-Tech-admission.php",encoding="utf-8").read()
blocks=re.findall(r'<script type="application/ld\+json">(.*?)</script>',s,re.S)
for i,b in enumerate(blocks):
    json.loads(b); print(f"block {i}: OK")
PY
```
Expected: each block prints "OK"

- [ ] **Step 5: Commit**

```bash
git add website_download/GGSIPU-counselling-for-B-Tech-admission.php
git commit -m "seo(counselling): align B.Tech FAQ schema dates with official 8-Jun-2026"
```

---

### Task 4: B.Tech page — add Event JSON-LD

**Files:** Modify `website_download/GGSIPU-counselling-for-B-Tech-admission.php`

- [ ] **Step 1: Insert an Event block** immediately before the closing schema comment `<!-- ===== /Schema ===== -->` (line 336).

Find:
```php
</script>
<!-- ===== /Schema ===== -->
</body>
```
Replace with:
```php
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "GGSIPU B.Tech Centralized Online Counselling 2026-27",
  "description": "Enrolment for GGSIPU centralized online counselling for B.Tech (programme code 131), tentatively from 8 June 2026 per University Notification 26/2026 (03.06.2026).",
  "startDate": "2026-06-08",
  "eventAttendanceMode": "https://schema.org/OnlineEventAttendanceMode",
  "eventStatus": "https://schema.org/EventScheduled",
  "location": {"@type":"VirtualLocation","url":"https://ipu.ac.in"},
  "organizer": {"@type":"Organization","name":"Guru Gobind Singh Indraprastha University","url":"https://ipu.ac.in"},
  "image": "https://ipu.co.in/assets/images/news/admissions.jpg"
}
</script>
<!-- ===== /Schema ===== -->
</body>
```

- [ ] **Step 2: Verify** the Event block is present and all JSON-LD still valid.

Run: `grep -c '"@type": "Event"' website_download/GGSIPU-counselling-for-B-Tech-admission.php`
Expected: `1`
Run the same Python JSON-LD validation from Task 3 Step 4. Expected: all blocks "OK".

- [ ] **Step 3: Commit**

```bash
git add website_download/GGSIPU-counselling-for-B-Tech-admission.php
git commit -m "seo(counselling): add Event JSON-LD (startDate 2026-06-08) to B.Tech page"
```

---

### Task 5: General page — callout + freshness bump

**Files:** Modify `website_download/ipu-counselling.php`

- [ ] **Step 1: Insert callout** after the intro helpline paragraph (line 68), before `<h2>What is IPU Counselling?</h2>`.

Find:
```php
  <p>For free expert help on registration, choice filling strategy and seat predictions, call our IPU admission helpline at <a href="tel:+919899991342"><strong>9899991342</strong></a>.</p>

  <h2>What is IPU Counselling?</h2>
```
Replace with:
```php
  <p>For free expert help on registration, choice filling strategy and seat predictions, call our IPU admission helpline at <a href="tel:+919899991342"><strong>9899991342</strong></a>.</p>

  <div style="background:#fff8e6;border:1px solid #f7b731;border-left:5px solid #f7b731;border-radius:8px;padding:16px 18px;margin:18px 0;">
    <p style="margin:0 0 6px;font-weight:700;color:#0d1b6e;font-size:1.02rem;">&#128226; Counselling Update &mdash; 3 June 2026</p>
    <p style="margin:0;line-height:1.7;font-size:0.96rem;">GGSIPU has notified (Notification No. 26/2026, dated 03.06.2026) that enrolment for <strong>Centralized Online Counselling 2026-27</strong> is <strong>likely to begin tentatively from 8 June 2026</strong> for all listed programmes &mdash; including B.Tech (131), BCA (114), BA LL.B./BBA LL.B. (121), LL.M. (112), BBA &amp; 5-year Integrated (125), B.Com Hons (146), BA English (184) and BA Economics (197). <strong>MBA (101) and MCA (105)</strong> schedules will be announced separately. Confirm on <a href="https://ipu.ac.in" target="_blank" rel="noopener">ipu.ac.in</a> or call <a href="tel:+919899991342"><strong>9899991342</strong></a>.</p>
  </div>

  <h2>What is IPU Counselling?</h2>
```

- [ ] **Step 2: Bump the last-updated stamp** (line 62).

Find: `<?php $last_updated = '2026-04-07'; include 'include/components/last-updated.php'; ?>`
Replace: `<?php $last_updated = '2026-06-04'; include 'include/components/last-updated.php'; ?>`

- [ ] **Step 3: Bump the Article schema dateModified** (line 30).

Find:
```
  "datePublished": "2026-04-07",
  "dateModified": "2026-04-07"
```
Replace:
```
  "datePublished": "2026-04-07",
  "dateModified": "2026-06-04"
```

- [ ] **Step 4: Verify.**

Run: `grep -c "Counselling Update" website_download/ipu-counselling.php` → Expected `1`
Run: `grep -c "2026-06-04" website_download/ipu-counselling.php` → Expected `>= 2` (last_updated + dateModified)

- [ ] **Step 5: Commit**

```bash
git add website_download/ipu-counselling.php
git commit -m "seo(counselling): add 8-Jun-2026 callout + freshness bump to general counselling page"
```

---

### Task 6: General page — fix the Important Dates table + lead-in

**Files:** Modify `website_download/ipu-counselling.php`

- [ ] **Step 1: Fix the lead-in paragraph** (line 74).

Find:
```php
  <p>The schedule below is tentative based on the past 3 years' GGSIPU counselling pattern. Official dates will be released in May 2026 on the IPU website. For real-time alerts, save our number <a href="tel:+919899991342">9899991342</a> on WhatsApp.</p>
```
Replace with:
```php
  <p>Per GGSIPU Notification 26/2026 (03.06.2026), centralized online counselling enrolment is likely to begin tentatively from 8 June 2026. The remaining dates below are indicative (based on past pattern) and will be confirmed by official GGSIPU notifications. For real-time alerts, save our number <a href="tel:+919899991342">9899991342</a> on WhatsApp.</p>
```

- [ ] **Step 2: Fix the dates table rows.** Replace the `<tbody>...</tbody>` (lines ~83–94) with:

```php
    <tbody>
      <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">Online Counselling Enrolment Opens</td><td style="padding:10px 14px"><strong>Tentatively 8 June 2026</strong> (Notification 26/2026)</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">Last Date to Register &amp; Pay Counselling Fee</td><td style="padding:10px 14px">To be notified</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">Choice Filling (B.Tech, BBA, BCA, Law)</td><td style="padding:10px 14px">To be notified</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">Round 1 Seat Allotment Result</td><td style="padding:10px 14px">To be notified</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">Round 2 &amp; 3 Seat Allotment</td><td style="padding:10px 14px">To be notified</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0;background:#f8faff"><td style="padding:10px 14px">Spot / Physical Counselling Round</td><td style="padding:10px 14px">To be notified</td></tr>
      <tr style="border-bottom:1px solid #e2e8f0"><td style="padding:10px 14px">MBA (101) &amp; MCA (105) Schedule</td><td style="padding:10px 14px">To be announced separately</td></tr>
    </tbody>
```

- [ ] **Step 3: Verify** stale month guesses are gone from the dates table context.

Run: `grep -nE "2nd week of May 2026|3rd week of June 2026|Last week of June 2026|1st week of July 2026|released in May 2026" website_download/ipu-counselling.php`
Expected: no output
Run: `grep -c "Tentatively 8 June 2026" website_download/ipu-counselling.php` → Expected `>= 1`

> Note: the prose sections further down ("Registration opens in mid-May", "opens by mid-May", "PG counselling typically opens in June") are course-wise descriptions, not the headline dates table. Leave them — the corrected table + callout carry the authoritative date, and rewriting every prose mention risks H1/heading churn. (Out of scope per spec.)

- [ ] **Step 4: Commit**

```bash
git add website_download/ipu-counselling.php
git commit -m "seo(counselling): correct general page Important Dates table to 8-Jun-2026"
```

---

### Task 7: General page — add Event JSON-LD

**Files:** Modify `website_download/ipu-counselling.php`

- [ ] **Step 1: Insert an Event block** right after the Article schema `</script>` (line 32).

Find:
```php
  "dateModified": "2026-06-04"
}
</script>

<?php
```
Replace with:
```php
  "dateModified": "2026-06-04"
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Event",
  "name": "GGSIPU Centralized Online Counselling 2026-27",
  "description": "Enrolment for GGSIPU centralized online counselling 2026-27 across all listed programmes (B.Tech, BCA, Law, BBA, B.Com, BA English/Economics, LL.M., B.Ed.), tentatively from 8 June 2026 per University Notification 26/2026 (03.06.2026). MBA and MCA schedules to be announced separately.",
  "startDate": "2026-06-08",
  "eventAttendanceMode": "https://schema.org/OnlineEventAttendanceMode",
  "eventStatus": "https://schema.org/EventScheduled",
  "location": {"@type":"VirtualLocation","url":"https://ipu.ac.in"},
  "organizer": {"@type":"Organization","name":"Guru Gobind Singh Indraprastha University","url":"https://ipu.ac.in"},
  "image": "https://ipu.co.in/assets/images/news/admissions.jpg"
}
</script>

<?php
```

- [ ] **Step 2: Verify** the Event block is present and all JSON-LD valid.

Run: `grep -c '"@type": "Event"' website_download/ipu-counselling.php` → Expected `1`
Run:
```bash
python3 - <<'PY'
import re,json
s=open("website_download/ipu-counselling.php",encoding="utf-8").read()
for i,b in enumerate(re.findall(r'<script type="application/ld\+json">(.*?)</script>',s,re.S)):
    json.loads(b); print(f"block {i}: OK")
PY
```
Expected: all blocks "OK"

- [ ] **Step 3: Commit**

```bash
git add website_download/ipu-counselling.php
git commit -m "seo(counselling): add Event JSON-LD to general counselling page"
```

---

### Task 8: News post + compile

**Files:** Create `content/news/ggsipu-centralized-online-counselling-2026-from-8-june.md`

- [ ] **Step 1: Create the MD file** with this exact content:

````markdown
{
  "title": "GGSIPU Centralized Online Counselling 2026-27 to Begin Tentatively from 8 June",
  "slug": "ggsipu-centralized-online-counselling-2026-from-8-june",
  "date": "2026-06-04",
  "date_modified": "2026-06-04",
  "category": "Admissions",
  "tags": ["GGSIPU", "IPU Counselling", "Counselling 2026", "Admissions"],
  "featured": true,
  "is_urgent": true,
  "tldr": "GGSIPU (Notification 26/2026, dated 03.06.2026) has announced that enrolment for its centralized online counselling 2026-27 is likely to begin tentatively from 8 June 2026 for B.Tech and 11 other programmes. MBA and MCA schedules will follow separately.",
  "faq": [
    {
      "q": "When does GGSIPU online counselling 2026 start?",
      "a": "Per GGSIPU Notification 26/2026 (03.06.2026), enrolment for centralized online counselling 2026-27 is likely to begin tentatively from 8 June 2026. The university has stated the date is tentative; confirm on ipu.ac.in."
    },
    {
      "q": "Which programmes are covered in this counselling?",
      "a": "B.Tech (131), BCA (114), BA LL.B./BBA LL.B. (121), LL.M. (112), B.Ed. (122), BBA & 5-year Integrated (125), BA JMC (126), LE B.Tech (128), B.Com Hons (146), B.Ed. Special Education (159), BA English Hons (184) and BA Economics Hons (197). MBA (101) and MCA (105) schedules will be announced separately."
    },
    {
      "q": "Is the 8 June 2026 date confirmed?",
      "a": "No. GGSIPU's notification states the start is 'likely' and 'tentative'. Check ipu.ac.in and ipu.admissions.nic.in for the confirmed schedule, or call 9899991342."
    }
  ],
  "image": "assets/images/news/admissions.jpg"
}
---
## GGSIPU Notifies Counselling 2026-27 Start

Guru Gobind Singh Indraprastha University (GGSIPU) has issued **Notification No. 26/2026** (F.No. IPU-7/Academic/2026-27/2289), dated **3 June 2026**, on enrolment for its **Centralized Online Counselling** for the 2026-27 academic session.

As per the notification, enrolment is **likely to be started from 8 June 2026, tentatively**. The university has explicitly stated this date is tentative, and candidates are advised to check the official websites — [ipu.ac.in](https://ipu.ac.in) and [ipu.admissions.nic.in](https://ipu.admissions.nic.in) — regularly for updated information.

## Programmes Covered

| Programme | Code |
|---|---|
| LL.M. | 112 |
| BCA | 114 |
| BA LL.B. / BBA LL.B. | 121 |
| B.Ed. | 122 |
| BBA & Allied / 5-year BBA-MBA Integrated | 125 |
| BA (JMC) | 126 |
| LE B.Tech (for Diploma Holders) | 128 |
| B.Tech | 131 |
| B.Com (Hons) | 146 |
| B.Ed. (Special Education) | 159 |
| BA English (Hons / Hons with Research) | 184 |
| BA Economics (Hons / Hons with Research) | 197 |

The schedule for **MBA (code 101)** and **MCA / MCA (SE) (code 105)** will be displayed on the University websites separately, in due course.

## What Candidates Should Do

Keep your documents and entrance-exam scorecard (JEE Main / CUET / CLAT / IPU CET, as applicable) ready and watch the official portals for the live enrolment link. For free, step-by-step help with registration, choice filling and seat predictions, call our 24x7 admission helpline at **9899991342**.

Related guides:
- [GGSIPU B.Tech Counselling 2026](/GGSIPU-counselling-for-B-Tech-admission.php)
- [IPU Counselling 2026 — Complete Schedule & Process](/ipu-counselling.php)
- [IPU B.Tech Admission 2026](/IPU-B-Tech-admission-2026.php)
````

- [ ] **Step 2: Compile the news pipeline.**

Run: `php scripts/build-news.php`
Expected: builds `website_download/news/ggsipu-centralized-online-counselling-2026-from-8-june.php`, updates sitemap + llms.txt, no PHP errors.

- [ ] **Step 3: Verify** the compiled page exists and JSON-LD (if any) is valid.

Run: `test -f website_download/news/ggsipu-centralized-online-counselling-2026-from-8-june.php && echo OK`
Expected: `OK`
Run: `php -l website_download/news/ggsipu-centralized-online-counselling-2026-from-8-june.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add content/news/ggsipu-centralized-online-counselling-2026-from-8-june.md website_download/news/ website_download/sitemap.xml website_download/llms.txt
git commit -m "news: GGSIPU centralized online counselling 2026-27 from 8 June (Notification 26/2026)"
```

---

### Task 9: Full verification pass (localhost crosslink + consistency)

**Files:** none (verification only)

- [ ] **Step 1: SEO-safety diff guard** — confirm no title/meta/canonical/H1/URL changed on either ranking page.

Run:
```bash
git diff 7f3e4b9 -- website_download/GGSIPU-counselling-for-B-Tech-admission.php website_download/ipu-counselling.php | grep -E "^\+.*(<title>|name=\"description\"|rel=\"canonical\"|<h1)" || echo "OK: no title/meta/canonical/h1 additions"
```
Expected: `OK: no title/meta/canonical/h1 additions`

- [ ] **Step 2: Date consistency** — confirm the canonical date string is present and no stale strings linger in date contexts.

Run:
```bash
for f in GGSIPU-counselling-for-B-Tech-admission ipu-counselling; do
  echo "== $f =="
  grep -c "8 June 2026" "website_download/$f.php"
  grep -nE "third or fourth week of July|2nd week of May 2026|released in May 2026|Registration\s*</td>\s*<td[^>]*>April 2026" "website_download/$f.php" || echo "no stale date strings"
done
```
Expected: count `>= 1` per file; "no stale date strings".

- [ ] **Step 3: Localhost crosslink walk** ([[feedback_localhost_crosslink_test]]).

Run: `php -S localhost:8000 -t website_download >/tmp/ipu_srv.log 2>&1 &` then:
```bash
sleep 1
for u in GGSIPU-counselling-for-B-Tech-admission.php ipu-counselling.php news/ggsipu-centralized-online-counselling-2026-from-8-june.php; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/$u"); echo "$code  $u"
done
curl -s "http://localhost:8000/ipu-counselling.php" | grep -o 'href="/GGSIPU-counselling-for-B-Tech-admission.php"' | head -1
kill %1 2>/dev/null
```
Expected: `200` for all three; the crosslink href found.

- [ ] **Step 4:** No commit (verification only). If anything fails, fix in the owning task and re-run.

---

### Task 10: Deploy (GATED — pre-deploy checkpoint)

**Files:** none (deploy)

> Per [[feedback_pre_deploy_quality_check]] and [[feedback_plan_execution_autorun]]: STOP here for explicit go-ahead before pushing to production. Present the verification results from Task 9 first.

- [ ] **Step 1: Sync local branch first** ([[feedback_git_pull_before_sync]] — the news GH Action auto-commits `content/news/*`).

Run: `git fetch origin && git pull --rebase origin claude/2026-04-30-ipu-session`
Expected: clean (or fast-forward); resolve any news-content conflicts before deploying.

- [ ] **Step 2: Identify the correct upload script** for these files (page + news modules).

Run: `ls upload_*.py && grep -l "news" upload_*.py`
Expected: pick the script(s) that cover `website_download/*.php` and `website_download/news/`.

- [ ] **Step 3: Deploy** the three pages + news + sitemap/llms via the chosen `upload_*.py` (or let `news-build-deploy.yml` handle the news files on push — coordinate to avoid double-deploy).

- [ ] **Step 4: Production curl-verify.**

Run:
```bash
for u in GGSIPU-counselling-for-B-Tech-admission.php ipu-counselling.php news/ggsipu-centralized-online-counselling-2026-from-8-june.php; do
  echo "== $u =="; curl -s "https://ipu.co.in/$u" | grep -o "Tentatively 8 June 2026\|8 June 2026" | head -1
done
```
Expected: the date string present on each live page.

- [ ] **Step 5: Update memory** — note the observation-freeze was consciously broken for this correction, and record the deploy.

---

## Self-Review

**Spec coverage:**
- D1 (B.Tech callout/table/FAQ/Event) → Tasks 1–4 ✓
- D2 (general callout/table/Event + freshness) → Tasks 5–7 ✓
- D3 (news post + compile) → Task 8 ✓
- Verification (consistency, JSON-LD, crosslink, SEO-safety) → Task 9 ✓
- Deploy discipline (git pull, curl-verify, gated) → Task 10 ✓
- Accuracy guardrails (tentative + cited, no fee/round dates) → enforced in every content task ✓

**Placeholder scan:** none — all edits have exact find/replace content; the only "To be notified"/"to be announced" strings are deliberate page copy (the notification gives no further dates).

**Type/string consistency:** canonical date string "8 June 2026" / `2026-06-08`; notification cited as "Notification 26/2026 (03.06.2026)" consistently; Event schema identical shape on both pages.
