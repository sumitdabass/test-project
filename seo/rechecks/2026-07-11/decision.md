# Stop-loss recheck — 2026-07-11

**Trigger:** scheduled recheck (~03-Jul, run late 11-Jul) for GIBS repositioning (deploy 26-Jun) + Phase 3 additive SEO (deploy 26-Jun).
**Data:** GSC export `ipu.co.in-Performance-on-Search-2026-07-11.xlsx`, window = **Last 7 days** (04–10 Jul).
**Baselines:** `seo/baselines/2026-06-11-*` (3-month window 10-Mar..09-Jun).

## Verdict: NO REVERT. Stop-loss does not fire.

### Watch terms flagged >2 (all seasonal, not edit-caused)
| term | base | now | Δ |
|---|---|---|---|
| ipu counselling 2026 | 1.8 | 5.90 | +4.1 |
| ggsipu counselling | 5.4 | 8.43 | +3.0 |
| ggsipu counselling 2026 | 2.4 | 7.17 | +4.8 |

**Why not reverted:** query positions dropped while the underlying pages stayed flat
(`/ipu-counselling.php` 4.3→4.72; `/GGSIPU-counselling-for-B-Tech-admission.php` 5.6→5.95)
= SERP competition from official nic.in + aggregators during active counselling season,
not page-relevance loss. Confirmed by: (1) Jun 15-21 report already logged this slide
pre-deploy as "NOT a code regression"; (2) counselling commit 9828f7c touched no
title/meta/canonical/H1 (body/schema only); (3) reverting removes accurate Round 1 dates
without recovering rank.

### Edits under protection — all clean
- GIBS: top-law 6.4→5.26, top-bca 6.3→5.37, top-mca 6.1→4.57 (all improved).
- Phase 3: best-btech 3.72, bba-cutoff 4.76, colleges-list 6.7→8.15 (+1.45, within noise).
- Broad gains: ipu 9.3→8.13, ipu college list 5.8→1.82, ipu colleges 7.9→2.83,
  colleges under ipu 7.5→2.06, ipu helpline 7.0→2.01, ggsipu counselling for btech 6.5→1.89.

### Follow-up
- Counselling head terms expected to recover as season winds down / post Round 2.
  Re-observe on next recheck; do NOT revert Round 1 content.
- Next natural recheck: after Round 2 counselling update, or ~2 weeks.
