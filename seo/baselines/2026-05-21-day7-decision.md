# Phase B Day 7 — Stop-Loss Gate Decision

**Date:** 2026-05-21 (Day 6 + 6 days; meets plan's "5-6 days for re-crawl" threshold)
**Branch:** `claude/2026-04-30-ipu-session` @ d6c27db (Day 6 homepage FAQ enrichment)
**Decision:** **CONDITIONAL GO** — smoke test clear, awaiting SC export for full clearance before Week 2 ships.

## Evidence collected

### Smoke test (US-locale WebSearch, 6 highest-sensitivity terms)

Source-of-truth: `seo/baselines/2026-05-21-watch-terms-check.csv`

- **3 of 3 cash cows where ipu.co.in's content directly competes are intact:**
  - `ipu colleges` (baseline 9.84) → still **#3** in US SERP
  - `ggsipu counselling 2026` (baseline 2.19) → Day-2 page still **#3** in US SERP
  - `ipu admission` (baseline 12.80) → still **#5** in US SERP
- **3 of 3 broad terms (ip university / ipu university / guru gobind singh indraprastha university) do not surface ipu.co.in in US top 10.** This is a US-locale baseline limitation — terms with India SC baseline pos 3-10 are crowded out by .ac.in + Wikipedia + social in US. Not a regression signal; the same searches would have shown the same pattern at Day 0.
- **`site:ipu.co.in <term>` returns the correct canonical page as #1 across all 6 terms.** No deindexing of any kind.

### Leads.log (prod SSH)

- Day 0 baseline: 0 lines (logging didn't exist)
- Day 7 (now): **323 lines** since Day 1 deploy → ~46 leads/day average
- Most recent entry: `2026-05-21T04:59:59+00:00` → log is live and writing
- **`lead_record()` helper from Day 1 working correctly across both form-handler.php and sendemail.php.**

## What's NOT verified by this smoke check

The stop-loss rule is **"any term drops >2 positions → revert."** US WebSearch cannot detect a 4 → 7 drop on `ggsipu counselling registration 2026` (baseline 1.72) — Google personalization + India-vs-US locale + ranked search noise will obscure ±2 deltas at borderline-page-1.

**True position diff requires a fresh Search Console Performance → Queries export for the last 7 days** to compare against `2026-05-15-keyword-master-list.md`.

## Conditional rules

**If Sumit pulls SC and the diff shows all 29 watch-terms within ±2 of baseline:** proceed to Week 2 (Days 8-14: Fees Hub + Helpline Hub + Admit-Card schema + subdomain leak fix + Colleges-list schema + Week-2 review).

**If SC reveals any term dropped >2 positions:**
1. Most likely culprit by surface area = Day 6 homepage FAQ 6→12 (broadest change; affects ip university / ipu / ipu colleges / guru gobind singh indraprastha university cluster).
2. Revert path: `git revert d6c27db 10f4424 16600ff` (Day 6 trio) → re-FTP `index.php`.
3. Re-check after 5 days.

## What's pending from Sumit

1. **Search Console Queries CSV** — Last 7 days (2026-05-14 → 2026-05-21), all queries, drop to `/Users/Sumit/Downloads/` for diff against baseline.
2. **GTM conversion audit** (from Phase A memory) — 4 checks still open: phone_click tag config, redundant thank-you conversions, WCM `cc=ZZ → cc=IN`, GA4 sign_up linking.
3. **Ads exports** (deferred from Week 1) — landing-pages-30d, search-terms-30d, conversions-by-action-60d.

Item 1 is the only blocker for Week 2 GA decision. Items 2-3 are independent.
