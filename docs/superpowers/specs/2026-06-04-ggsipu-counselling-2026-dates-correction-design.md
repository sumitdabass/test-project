# GGSIPU Counselling 2026 — Dates Correction + Official-Schedule Freshness Sprint

**Date:** 2026-06-04
**Site:** ipu.co.in (production; local `/Users/Sumit/test-project`, vanilla PHP)
**Status:** Design approved; ready for implementation plan.

## Context & trigger

Day-7 re-baseline (2026-06-04) confirmed the Phase B Week 2 sprint passed. While
exploring the counselling workhorse page for a Week-3 opportunity, two findings forced
this sprint:

1. **The counselling "...date 2026" query cluster is surging** (peak season): the
   workhorse page `GGSIPU-counselling-for-B-Tech-admission.php` sits at **pos 6.67 on
   31,287 impressions**; `ipu-counselling.php` at pos 4.48 / 13,328 impr. Date-intent
   queries dominate the top-20 (`ggsipu counselling date 2026` 705 impr @ 7.59 / 0.28%
   CTR, etc.).
2. **Both pages carry wrong dates.** The B.Tech page's visible timeline table says
   April–July; its FAQ schema says "third/fourth week of July" — a direct contradiction.
   The general page says registration opens "2nd week of May" and "official dates will
   be released in May 2026."

GGSIPU **Notification 26/2026, dated 03.06.2026** supersedes all of it.

## Source of truth (authoritative)

GGSIPU Notification No. 26/2026 (F.No. IPU-7/Academic/2026-27/2289), dated 03.06.2026:

> "Enrolment for Centralized Online Counselling is **likely to be started from 08th
> June, 2026, tentatively**" — for the programmes listed below.

Programmes (code): LL.M. (112), BCA (114), BA LL.B./BBA LL.B. (121), B.Ed. (122),
BBA & Allied / 5-yr BBA-MBA Integrated (125), BA JMC (126), LE B.Tech for Diploma
Holders (128), **B.Tech (131)**, B.Com (Hons) (146), B.Ed. Special Education (159),
BA English Hons (184), BA Economics Hons (197). **MBA (101) and MCA/MCA(SE) (105)
schedules to be displayed separately, in due course.**

### Accuracy guardrails (non-negotiable)

- Always phrase as **"tentatively from 8 June 2026"** and **cite Notification 26/2026
  (03.06.2026)** — never a hard "starts 8 June."
- Assert **only** what the notification states: the tentative enrolment start date, that
  it is centralized/online, and the programme list. Do **NOT** invent fees, last dates,
  round dates, or reporting windows — mark those "to be notified."
- MBA (101) and MCA (105): explicitly note their schedule is "to be announced separately."

## SEO-safety rails (per project rules)

- **Zero changes** to `<title>`, `<meta name="description">`, `rel="canonical"`, `<h1>`,
  or URL on either ranking page. Only body dates, JSON-LD schema, and an additive callout.
- One enquiry form per page rule unaffected (we add no forms).
- Changes are corrections + additive blocks — consistent with the additive-only posture.

## Scope

Three deliverables (approved scope: B.Tech page + general page + news post).

### Deliverable 1 — `website_download/GGSIPU-counselling-for-B-Tech-admission.php`

1. **Dated callout box** inserted near the top of the article body (after the intro,
   before/at the Timeline section). Styled to the page's existing palette
   (`#1a3a6b` heading blue, `#f7b731` accent). Content: the tentative 8-Jun-2026
   enrolment date, B.Tech = code 131, citation to Notification 26/2026, helpline CTA
   to 9899991342. Include a visible "Updated 3 June 2026" stamp.
2. **Fix the timeline table** (currently lines ~76–82): row 1 = "Online Counselling
   Enrolment — Tentatively from 8 June 2026 (Notification 26/2026)". Downstream rows
   (choice filling, Round 1/2/3, spot, closure) relabelled "To be notified" rather than
   the stale April/May/July guesses. Update the footnote to cite the notification.
3. **Fix the FAQ schema** date answers (currently ~lines 304–308): the start /
   registration-date / last-date answers change from "third/fourth week of July" to the
   tentative 8-Jun-2026 wording, consistent with the visible table. Last-date answer
   becomes "to be notified." No new FAQ entries (avoids H1/title pressure).
4. **Add `Event` JSON-LD** block: `@type Event`, name "GGSIPU B.Tech Centralized Online
   Counselling 2026", `startDate 2026-06-08`, `eventAttendanceMode OnlineEventAttendanceMode`,
   `eventStatus EventScheduled`, `location`/`url` → official portal, `organizer` GGSIPU,
   `description` citing tentative status. (Use a future `endDate` omitted or noted as TBD —
   omit rather than guess.)

### Deliverable 2 — `website_download/ipu-counselling.php`

1. **Dated callout box** (same content/style, all-courses framing: centralized counselling
   for all listed programmes, B.Tech/Law/BBA/BCom/BA/BCA, with MBA & MCA separate).
2. **Fix the "Important Dates (Tentative)" table** (lines ~84–93): row "Registration
   Opens — 2nd week of May" → "Enrolment Opens — Tentatively 8 June 2026 (Notification
   26/2026)"; relabel downstream rows "To be notified". Fix the lead-in paragraph
   (line ~74) that falsely says "Official dates will be released in May 2026."
3. **Add `Event` JSON-LD** (centralized, all-courses variant) as a second schema block
   alongside the existing Article schema.

### Deliverable 3 — News post via the pipeline

- New `content/news/ggsipu-centralized-online-counselling-2026-from-8-june.md` with the
  standard JSON frontmatter:
  - `title`: "GGSIPU Centralized Online Counselling 2026-27 to Begin Tentatively from 8 June"
  - `slug`, `date`/`date_modified`: 2026-06-04, `category`: "Admissions",
    `is_urgent`: true, `featured`: true
  - `tldr`, `tags` (GGSIPU, Counselling 2026, Admissions), `faq` (2–3 Q&As),
    `image`: existing `assets/images/news/admissions.jpg`
  - Body: notification facts, the full programme-code table, MBA/MCA caveat, and internal
    links to `/GGSIPU-counselling-for-B-Tech-admission.php`, `/ipu-counselling.php`,
    `/IPU-B-Tech-admission-2026.php`.
- Compile with `php scripts/build-news.php` → regenerates `website_download/news/*.php`,
  sitemap, and llms.txt.

## Verification (static PHP site — no unit test suite)

1. **Consistency check:** grep both pages to confirm the date string ("8 June 2026" /
   "2026-06-08") appears consistently across visible table, callout, FAQ answers, and
   Event JSON-LD — no remaining "April"/"third week of July"/"2nd week of May" stale
   strings in the counselling-dates context.
2. **JSON-LD validity:** extract each `application/ld+json` block and `python -m json.tool`
   / JSON.parse to confirm valid JSON.
3. **Localhost crosslink test:** `php -S localhost:8000` from `website_download/`, load
   both pages + the new news page, click nav/footer/in-body counselling links.
4. **No SEO-safety regressions:** diff confirms title/meta/canonical/H1/URL unchanged on
   both pages.
5. **One-form-per-page** rule unaffected (no forms added).

## Deploy discipline

- **`git fetch && git pull`** before any `--sync`/FTP push — the news GH Action
  auto-commits `content/news/*`, so the local branch must be current first.
- Pre-deploy: lint + visual + curl-verify every changed file.
- FTP via the existing `upload_*.py` suite (module-scoped). News deploy may also occur via
  the `news-build-deploy.yml` GH Action on `content/news/*` change — coordinate so we
  don't double-deploy or fight the Action.
- This consciously breaks the 4-week observation freeze (set 2026-05-21) for a
  time-critical factual correction backed by an official notification. Logged in memory.

## Out of scope (this sprint)

- Per-course page callouts for all 12 programmes (the "full cluster sweep" option) —
  deferred; revisit if Week-3 data warrants.
- Any title/meta/H1/URL optimization.
- Fee/round/last-date specifics (await further GGSIPU notifications).
