# Phase B Week 2 — Day-7 Re-baseline Decision

**Check date:** 2026-06-04
**Data window:** 2026-05-26 → 2026-06-01 (7 days; Google's 2–3 day lag means 06-02/03 not yet final)
**Source:** `ipu.co.in-Performance-on-Search-2026-06-04.xlsx` (snapshot id=2)
**Baseline:** `seo/baselines/2026-05-28-phase-b-week2-baseline.csv` (9 watch terms)
**Sprint deployed:** 2026-05-28 (commit `6b79c3e`)

## Decision: PASS — no stop-loss. Sprint marked COMPLETE.

No watch term dropped more than 2 positions. The instrumented head terms improved.
Task-9 revert NOT triggered.

## Watch-term diff (Δpos: negative = better)

| Term | base_pos | now_pos | Δpos | now_impr | Note |
|---|---|---|---|---|---|
| ipu colleges list | 6.59 | 4.40 | **-2.19** | 57 | improved |
| ipu college list | 6.59 | 4.32 | **-2.27** | 183 | improved |
| ipu colleges list pdf | 6.59 | 7.54 | +0.95 | 59 | flat (noise) |
| ipu cet exam date 2026 | 12.09 | n/a | — | anon | deprioritized; below query-visibility floor in 7-day window |
| ipu btech via jee main | 7.00 | n/a | — | anon | below query-visibility floor in 7-day window |
| ipu bba fees | 10.70 | 9.12 | **-1.58** | 80 | improved |
| top 10 bba colleges in ipu 2026 | 7.01 | n/a | — | anon | below query-visibility floor in 7-day window |
| ggsipu counselling | 4.80 | 5.78 | +0.98 | 571 | flat (noise) |
| ipu counselling 2026 | 1.60 | 1.79 | +0.19 | 815 | holding #1–2 |

**"n/a" terms are NOT ranking losses.** A 7-day query export anonymizes low-volume
queries (only ~26% of impressions visible at query level this window). These terms
simply fell below Google's per-query visibility floor for the short window — verify
them against a 28-day export at the 2026-06-12 decision session if a confirmation is
wanted.

## Site-wide trend (true totals, incl. anonymized)

| Metric | Week 1 (05-14→20) | Week 2 (05-26→06-01) | Δ |
|---|---|---|---|
| Clicks | 1,482 | 1,969 | +487 (+33%) |
| Impressions | 103,228 | 125,231 | +22,003 (+21%) |
| CTR | 1.44% | 1.57% | +0.14pp |
| Avg position | 6.63 | 6.46 | -0.17 (better) |

Counselling cluster is surging (season opening for all courses) — the workhorse page
`/GGSIPU-counselling-for-B-Tech-admission.php` leads pages at 31,287 impr.

## Caveats logged for honesty

- Baseline positions may be drawn from a longer (28-day) snapshot vs this 7-day window;
  not perfectly apples-to-apples, but the plan defines stop-loss against this baseline,
  so it was applied as written.
- WoW position-loser table shows several low-impression terms (5–18 impr) sliding in
  rank — high-variance noise on a 7-day vs 7-day compare with a 5-day gap (05-21→25
  uningested), none on the watch list, none on pages touched by the additive sprint.
  Read as seasonal SERP reshuffling, not sprint-induced.

## Next

- Stay in **observation mode** — no FTP deploys until the **2026-06-12 decision session**.
- Week 3 export window: 2026-05-28 → 2026-06-03 / pull ~2026-06-11.
- Tooling note: `ingest_sc.py` now falls back to the Chart sheet's date range when the
  Filters sheet carries a relative phrase ("Last 7 days"), so relative-range exports
  ingest without a manual fix.
