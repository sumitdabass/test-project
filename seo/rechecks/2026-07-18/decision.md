# Stop-loss recheck — 2026-07-18

**Trigger:** next natural recheck (1 wk after 07-11). No new deploys since 26-Jun.
**Data:** GSC export `ipu.co.in-Performance-on-Search-2026-07-18.xlsx`, window = **Last 7 days** (10–16 Jul).
**Baselines:** `seo/baselines/2026-06-11-*` (3-month 10-Mar..09-Jun); prior recheck `seo/rechecks/2026-07-11/`.

## Verdict: NO REVERT. Stop-loss does not fire. Seasonal slide recovering as predicted.

### Watch terms — week-over-week (04-10 Jul → 10-16 Jul)
| term | base | 07-11 | 07-18 | trend |
|---|---|---|---|---|
| ipu counselling 2026 | 1.8 | 5.90 | 5.86 | flat, holding |
| ggsipu counselling | 5.4 | 8.43 | 8.34 | flat |
| ggsipu counselling 2026 | 2.4 | 7.17 | **4.93** | recovering +2.24 |
| ggsipu counselling for btech | — | 1.89 | 2.03 | strong |

Predicted post-season recovery is materializing (ggsipu counselling 2026 back toward baseline). Confirms 07-11 read: seasonal SERP competition, not a code regression. Do NOT revert Round 1 counselling content.

### Protected / key pages — all healthy
- ipu-counselling.php pos 5.15 (was 4.72; within seasonal noise), 339 clicks
- GGSIPU-counselling-for-B-Tech pos 6.57, 179 clicks
- GIBS edits holding: top-law 5.42, top-mca 5.30, top-bca 5.65
- best-btech 3.49, ipu-bba-cutoff 4.61 — good

### Site-level (7d)
~4,850 clicks / ~64k impressions per day, avg pos ~6.5, CTR ~1.2%. Mobile = 78% of clicks.

### Standout opportunity (unchanged from prior weeks)
`ipu-colleges-list.php` = **121,573 impressions** (more than the next ~5 pages combined), pos **8.33**, CTR **0.45%**. Head terms "ipu" (68k imp, pos 8.38) + "ggsipu" (30k imp, pos 9.7) feed it. Title/meta/H1 are SEO-frozen; lift must come from position (additive content depth / internal links) not a meta rewrite. Biggest single lever on the site.

### Follow-up
- Next recheck ~2 weeks, or after any deploy.
- Backlog to action = `docs/audits/2026-07-11-full-site-recheck.md` (additive-safe batch H1–H7/M1 + C1 FTP rotation).

---

## DEPLOY LOG — 2026-07-18 (68 clean files → prod)

**Deployed** commit `a43ed33` (internal-linking push) + the non-Phase-2 slice of `1f85241` (audit batch) via `deploy.py --manifest` (FTP, 68/68 uploaded, exit 0).

**Scoping decision:** 13 files are edited by BOTH the audit batch and the HELD Phase 2 (`a7627b8`); a file on disk carries both edits, so uploading them would ship Phase 2. Excluded those 13 (incl. global `include/base-head.php` + `IPU-Law-Admission.php`) → deployed only the 68 Phase-2-clean files. Phase 2 stays fully held for the post-15-Aug window.

**What went live:**
- Internal-linking push (23 of 24 files): 29 stub links repointed off 301s + 4 orphans de-orphaned (`ipu-fees-structure` +4 inbound, `ipu-ba-llb-cutoff` +3, `barch-admission-ipu` +2, `med-admission-ipu` +2).
- Audit fixes (non-entangled): sitemap 404 drop, duplicate enquiry-form removals, CRLF mail-header hardening (form-handler + sendemail), breadcrumb dedup (breadcrumb-schema + hero-banner shipped together), preloader removal (base-nav + app.js; base-head dead CSS waits with Phase 2).

**Live verification (curl):** 58 pages HTTP 200; 10 non-200 all expected-correct (8 `include/*` `.htaccess`-blocked 403, `law-admission-ip-university.php` 301 source, `sendemail.php` 302 on GET). De-orphan links confirmed present on prod. Live crosslink scan = **845 links across 22 content pages, 0 stub links.**

**Still held / open:**
- Phase 2 speed `a7627b8` + its 13 entangled files → post-15-Aug deploy.
- C1: FTP password still committed in git history — rotate cPanel pw (Sumit) → then de-hardcode scripts.

**Watch for next recheck (~01-Aug):** did the de-orphaned high-intent pages move? `ipu-fees-structure.php` (was pos 6.1 / 2.2% CTR), `ipu-ba-llb-cutoff.php`, and whether the 301→direct repoints firmed up any positions.
