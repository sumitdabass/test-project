# ipu.co.in Phase B — Lead-volume sprint (2 weeks)

**Date:** 2026-05-15
**Author:** Claude (Opus 4.7) with Sumit
**Status:** Design approved — ready for implementation planning
**Predecessor:** Phase A shipped 2026-05-15 morning (see `project_ipu_phase_a_20260515.md`)

## 1. Goal

**Maximize ipu.co.in lead volume (helpline calls + form fills) over the next 30 days.**

Counselling season is in its 10× growth window — daily SC clicks went from 16 → 156 across the 28 days ending 2026-05-13. Every idle engineering day is wasted leads. Track 2 (Sumit's GTM/Ads audit) opens up the possibility of recovering the helpline-2026 volume drop hypothesized in Phase A as a free win.

Organic SEO compounding is a free byproduct of the additive items shipped, not a goal in its own right.

## 2. Constraints

1. **Keywords ranking pos 1-10: don't touch (preserve as-is). Keywords ranking pos 11+: fair game to optimize.** Per Sumit 2026-05-15: *"keyword whose rank below 10 can be touched no issue / dont touch 1 to 10 / we will improve those."* In practice: no URL / title / meta / canonical / H1 changes that could affect a query currently ranking top-10. Schema (FAQ, HowTo, etc.), image dimensions, and new sub-sections targeting different (pos 11+) queries are allowed because they don't compete with protected ranking signals. Also per [[feedback_seo_safety_ipu]].
2. **Trust strip = `include/components/trust-bar.php`, used verbatim.** Per [[feedback_ipu_trust_strip_canonical]]. No variant designs.
3. **Lead measurement cannot use davya-crm.** Per [[project_ipu_crm_disconnection]] — public-website forms do not feed CRM. Use server-side log counts via SSH (`ipuc@ipu.co.in` with `~/.ssh/davyas-active` per [[reference_ipuc_ssh_key]]).
4. **Pre-deploy quality gate.** Localhost crosslink test across 5 archetype pages per [[feedback_localhost_crosslink_test]]; curl-verify each changed file on prod after FTP push per [[feedback_pre_deploy_quality_check]].
5. **OPcache reset after every deploy.** Per [[reference_hostinger_fpm_opcache]] — toggle PHP-FPM in cPanel MultiPHP Manager.
6. **Evergreen URLs for any new page.** No year suffix per [[feedback_evergreen_urls]].
7. **One enquiry form per page.** Per [[feedback_one_form_per_page]] — if sidebar-enquiry is on the page, page-hero must be header-only.
8. **Stop-loss revert** on any watch-term drop >2 positions vs Day 1 baseline.

## 3. Architecture: two parallel tracks + sync point

```
┌─────────── Track 1 (Claude — engineering) ───────────┐
│ Day 1: Watch-term baseline + Trust strip site-wide   │
│ Day 2: FAQ + HowTo schema on counselling page        │
│ Day 3: FAQ + HowTo schema on B.Tech-admission-2026   │
│        + 301 for 2025 page                           │
│ Day 4: FAQ + HowTo schema on Law-Admission page      │
│ Day 5: Image dims on remaining ~38 imgs              │
│ Day 6: Homepage FAQ + brand-cluster content depth    │
│ Day 7: Rank delta review + stop-loss check           │
└──────────────────────────────────────────────────────┘
                       ║
                       ║ SYNC POINT Day 7
                       ║   → if Ads CSVs in: re-rank Week 2
                       ║   → if not: proceed with SC-data Week 2
                       ▼
┌─────────── Track 2 (Sumit — GTM/Ads UI) ─────────────┐
│ Anytime in 7 days:                                   │
│  • 4 GTM audit checks (phone_click vs old tags,      │
│    WCM cc=ZZ→IN, redundant conv audit, tag config)   │
│  • Export 3 Ads CSVs (landing-page / search-terms /  │
│    conversion-action; 30-day window)                 │
│  • Confirm watch-term additions                      │
└──────────────────────────────────────────────────────┘
```

Tracks share no state. Track 1 ships regardless of Track 2 progress. Track 2's most valuable outcome (`phone_click` tag config fix) directly tests Phase A's helpline-drop hypothesis.

## 4. Track 1 — day-by-day schedule

Every deploy follows: localhost crosslink test → git commit + tag → FTP push → curl-verify on prod → OPcache reset → watch-term rank check next morning.

### Day 1 — Baseline + Trust strip site-wide

- **Watch-term baseline:** Save SC top-30 queries with current positions to `seo/baselines/2026-05-15-watch-terms.csv`.
- **Code change:** Wire `include/components/trust-bar.php` into `include/components/page-hero.php` (on disk: `website_download/include/components/page-hero.php`) once, with a `$show_trust_bar = true` flag (defaulting on; per-page opt-out via setting it false). Placement: between page hero and first body section, matching `index.php` line 297. Render via `include_once("include/components/trust-bar.php")` from inside page-hero.php after the hero block closes.
- **Affected pages:** All 85 cohesion-migrated pages automatically (they all use page-hero.php).
- **Risk:** Zero rank impact. Pure brand reinforcement.

### Day 2 — FAQ + HowTo schema on counselling page

- **Target:** `website_download/GGSIPU-counselling-for-B-Tech-admission.php` (53,055 impr · 775 clicks · pos 5.01 — the cash cow).
- **Code change:** Append two `<script type="application/ld+json">` blocks before `</body>`:
  - **FAQPage** with 6–8 Q/A pairs targeting the long-tail counselling-date and registration queries: "When does GGSIPU counselling start 2026?" / "What is the GGSIPU counselling registration date?" / "What are the counselling fees?" / "How to apply for GGSIPU B.Tech counselling?" / "What documents are required?" / "Last date for counselling registration?".
  - **HowTo** with the counselling registration steps as `HowToStep` items.
- **Visible-content rule:** Schema content must match visible content. If any FAQ Q/A's answer isn't already present on the page, append a small FAQ section below existing content so schema = visible.
- **Risk:** Zero rank risk; potential featured-snippet capture on pos-1-2 low-CTR queries.

### Day 3 — FAQ + HowTo schema on B.Tech-admission-2026 + 301 cleanup

- **Schema target:** `website_download/IPU-B-Tech-admission-2026.php` (13,406 impr · 150 clicks · pos 6.29).
- **Schema content:** FAQPage targeting B.Tech-admission queries; HowTo for the admission process.
- **301 target:** `.htaccess` rule: `IPU-B-Tech-admission-2025.php` → `IPU-B-Tech-admission-2026.php` (stops 80-impression equity leak on the orphan 2025 page).

### Day 4 — FAQ + HowTo schema on Law-Admission page

- **Target:** `website_download/IPU-Law-Admission.php` (10,405 impr · 111 clicks · pos 6.37).
- **Schema content:** FAQPage targeting law-admission queries (BA-LLB, BBA-LLB, LLM); HowTo for the application process.

### Day 5 — Image dimensions on remaining imgs

- **Target:** ~38 `<img>` tags missing `width` and `height` attributes site-wide (Phase A handled the top 18 LCP images; this is the long tail).
- **Mechanism:** New script `upload_phase_b_image_dims.py` modeled on `upload_phase_a_seo_perf_2026_05_15.py`. Reads images, gets natural dimensions, writes attributes back.
- **Risk:** Zero rank. CWV/CLS improvement → better Ads landing-page Quality Score.

### Day 6 — Homepage FAQ + brand-cluster content depth

- **Target:** `website_download/index.php` (17,748 impr · 114 clicks · pos 10.31).
- **Code change:**
  - FAQPage schema with 6 Q/A pairs targeting brand-cluster queries (`What is GGSIPU?`, `Where is IPU located?`, `How many colleges under IPU?`, `What is the IPU admission process?`, `IPU helpline number?`, `When was IPU established?`).
  - Visible FAQ section below existing trust-bar strip (matches schema content).
  - One new "About IPU" content block (~250-400 words, evergreen) inserted immediately below the existing trust-bar strip and above the first existing H2 section on `index.php`. Strictly append; no edits to existing copy, H1, or hero. If localhost visual diff shows the block disrupts hero→trust-bar→content rhythm, skip the content block and ship FAQ schema only.
- **Risk:** Low-medium. Homepage is brand-critical. Stop-loss check Day 7 morning is the safety wire.

### Day 7 — Rank delta review + stop-loss

- **No deploy.** Pull current rank for each top-30 watch-term via Claude's WebSearch tool. Compare to Day 1 baseline.
- **Pass criteria:** All 30 watch-terms within ±2 positions of baseline; form-handler logs show ≥1 lead in last 24h; Lighthouse mobile LCP <2.5s on counselling page + homepage + B.Tech-admission-2026.
- **Fail action:** Revert most recent Track 1 deploy; investigate; no Week 2 deploys until resolved.

## 5. Track 2 — Sumit's GTM/Ads checklist

### 5.1 GTM conversion audit (4 checks, ~15 min)

| # | Where | What to check | Decision |
|---|---|---|---|
| a | Google Ads → Tools → Conversions | Compare last 60 days. Look for the action whose count fell around 2026-04-17 while `phone_click` (`cPhqCMizhZIYEK-6-c0o`) rose. | If old action still counts: pick one as primary; mark the other "Secondary". |
| b | Google Ads → Conversions | Are `hZSHCIK7x_wbEK-6-c0o` and `1jUiCIWo1rkaEK-6-c0o` (the two thank-you page_view conversions) still "Include in Conversions"? | If yes → double-counting; set both to Secondary. |
| c | GTM → Tags → search `cPhqCMizhZIYEK-6-c0o` | Firing trigger should be the custom event `phone_click`, not a CSS-selector click. | If trigger is wrong → fix to Custom Event matching `phone_click`. |
| d | WCM call tracking settings | Country code `cc` param | Change `ZZ` → `IN`. |

### 5.2 Ads CSV exports (3 files, ~10 min)

Save to `/Users/Sumit/Downloads/`:

| Filename | Source | Filters |
|---|---|---|
| `ads-landing-pages-30d.csv` | Google Ads → Reports → Pre-defined → "Landing page" | Last 30 days; columns: URL, Impr, Clicks, Conv, Cost, Conv rate, Avg CPC |
| `ads-search-terms-30d.csv` | Google Ads → Insights → Search terms | Last 30 days, all campaigns; columns: Search term, Impr, Clicks, Cost, Conv |
| `ads-conversions-by-action.csv` | Google Ads → Tools → Conversions → download | Last 60 days, by action, daily breakdown |

### 5.3 Watch-term additions

Top-10 watch-terms baked into the Day 1 baseline:

1. `ip university` (5,696 impr · pos 8.96)
2. `ipu university` (1,803 / 9.70)
3. `ggsipu counselling date 2026` (1,572 / 4.12)
4. `ipu counselling 2026` (1,384 / 1.62)
5. `ggsipu counselling date` (1,217 / 5.96)
6. `ggsipu counselling` (1,112 / 3.74)
7. `ipu` (1,100 / 11.28)
8. `ggsipu counselling fees` (658 / 7.35)
9. `ipu counselling registration 2026` (611 / 1.55)
10. `ipu college` (509 / 9.48)

Sumit may add up to 5 more terms (Ads keywords / brand-defense).

## 6. New-keyword targeting strategy

**The split:**

- **Top-30 SC queries → "PROTECT list"** — never touch the pages ranking for these.
- **Mid-tail SC (151 candidates identified) + Ads search-terms (pending) → "GROW list"** — target via additive routes only.

**Additive routes per cluster:**

| Cluster | Total impr | Route | Target window |
|---|---|---|---|
| **FEES** | 3,150 | New page `ipu-program-fees.php` (Week 2 Day 8); existing `ipu-fees-structure.php` stays untouched | Week 2 |
| **CONTACT / HELPLINE** | 1,940 | New page `ipu-helpline-contact.php` (Week 2 Day 9); 301 from old `ipu-helpline-contact-number.php` to new | Week 2 |
| **COUNSELLING long-tail dates** | 2,000+ | FAQ schema language on Day 2 deploy already targets these | Week 1 Day 2 |
| **ADMIT-CARD** | 508 | Augment existing `ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php` (2,835 impr at pos 11.57) with FAQ + HowTo schema (Week 2 Day 10) | Week 2 |
| **COLLEGES list variants** | ~700 | FAQ schema added to existing `ipu-colleges-list.php` (Week 2 Day 12) | Week 2 |
| **MGMT-QUOTA (striking distance)** | 96 (CTR 3.12% at pos 17) | Internal links from Days 2-4 deploys → existing mgmt-quota pages | Week 1 |

## 7. Week 2 schedule

Default (SC-data-driven if Ads CSVs not in):

| Day | Item |
|---|---|
| 8 | New page: `ipu-program-fees.php` (Fees Hub) |
| 9 | New page: `ipu-helpline-contact.php` (Contact Hub) + 301 from old `ipu-helpline-contact-number.php` |
| 10 | FAQ + HowTo schema on `ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php` |
| 11 | `mail.ipu.co.in` subdomain leak fix + crosslink audit re-run |
| 12 | FAQ schema on `ipu-colleges-list.php` (14,789 impr) + `comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` (5,558 impr) |
| 13 | Catch-up: re-deploy anything that slipped from Days 8-12 / second watch-term rank check / one parking-lot item if all caught up (priority: BPIT/BVP zero-click investigation) |
| 14 | Week-2 review: rank deltas, lead-volume delta from server-side logs, Phase C scope decision |

**Cannibalization-risk note (locked 2026-05-15):** The two NEW pillar pages originally proposed for Days 13-14 (`/about-ip-university.php` and `/ipu-counselling-registration-guide.php`) are **deferred to Phase C**. Cannibalization risk with homepage (brand cluster) and counselling page (Tier 3 registration cluster) is real and unquantified. Phase C will assess with proper data — Ads landing-page CSVs, post-Phase-B rank stability, and an explicit positioning strategy (clear intent differentiation, internal-link discipline, canonical handling). Per Sumit's directive: "keep basic, don't hamper current keyword SEO rank, aim is improvement only."

If Ads CSVs arrive before Day 8, the schedule re-ranks by `Ads cost / Ads conversions = CPL` × volume descending. Decision rule fixed; specific picks depend on data.

## 8. Success metrics

### 8.1 Primary — lead volume

| Metric | Source | Target (Day 14) |
|---|---|---|
| Form fills / 7 days | `wc -l` on `form-handler.php` / `sendemail.php` logs via SSH | +30% vs Day 1 baseline |
| Helpline calls / 7 days | WCM dashboard (Sumit reads) + GA4 `phone_click` (Sumit reads) | Recovery of pre-2026-04-17 helpline volume |
| Total leads / 7 days | Sum of the two above | +20% vs Day 1 baseline |

### 8.2 Secondary — SEO health

| Metric | Source | Pass |
|---|---|---|
| Watch-term rank stability | WebSearch tool on Day 7 + Day 14 | No drops >2 positions |
| Featured-snippet wins | Manual SERP check, incognito mobile | ≥1 captured by Day 14 |
| SC impressions / 7 days | Sumit's weekly SC export | +10% week-over-week |

### 8.3 Page-quality (Ads Quality Score)

| Metric | Source | Pass |
|---|---|---|
| Mobile LCP top-5 pages | Lighthouse | <2.5s |
| CLS top-5 pages | Lighthouse | <0.1 |
| Cache-Control public/1800 verified | `curl -I` 10-page sample | All correct |

### 8.4 Cost (Ads)

| Metric | Source | Pass |
|---|---|---|
| Cost per helpline conversion | Google Ads | <₹15 (per existing benchmark) |
| Wasted spend on negatives | Search-terms CSV | Sumit adds top 10 negatives → drops to zero |

### 8.5 Decision rules

| Signal | Action |
|---|---|
| Day 7 watch-terms stable + form working | Continue to Week 2 |
| Watch-term drops >2 positions | Revert last deploy; no further Track 1 until resolved |
| Day 14 lead volume flat or down | Audit Track 2 completion; if done, check form on top-3 pages |
| Day 14 lead volume up, quality OK | Phase C = Hindi `/hi/` + per-course fees + perf (bundle.min.css drop) |
| Day 14 lead volume up, quality low | Phase C pivots to qualification (form fields, captcha, scoring) — out of scope here |

## 9. Risks & rollback

### 9.1 Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Watch-term rank drop | Low | High | Daily check, ±2 stop-loss, revert |
| Day 6 homepage triggers re-evaluation | Low-medium | High | Append-only; Day 7 morning check before any further deploys |
| Form silently breaks | Medium | Very high | Day-7 sanity check on logs; localhost crosslink test pre-deploy |
| Trust-bar breaks non-standard page layout | Medium | Low | Visual diff on 5 archetypes localhost; defer non-cohesion pages |
| OPcache miss after deploy | High (recurring) | Medium | PHP-FPM toggle in cPanel after every push; curl-verify before declaring done |
| Ads CSVs never arrive | Medium | Low | Week 2 default schedule is SC-data-driven; CSVs unlock better targeting but aren't blocking |
| Helpline-drop hypothesis wrong | Medium | Medium | GTM audit reveals truth either way |
| Schema content ≠ visible content | Low | Medium | Append visible FAQ section so schema = visible |
| FTP partial-deploy failure | Medium | Medium | Re-run curl-verify; re-push any missing files |

### 9.2 Rollback per deploy

1. Each deploy = single git commit on `claude/2026-04-30-ipu-session`.
2. Pre-deploy: tag commit (e.g., `git tag pre-day-2`).
3. If watch-term check fails: `git revert <commit>` → FTP push reverted file(s) → curl-verify revert visible.
4. For Day 9's 301: `.htaccess` is the destructive part. Pre-edit copy of `.htaccess` saved as `.htaccess.pre-day-9` before push; revert is a single file restore.
5. Rollback time: ~5 min from decision to verified.

## 10. Out of scope (deferred to Phase C)

- Hindi `/hi/` variant (needs hreflang done correctly).
- Content-depth expansion on flagship pages (1.7-2.1k → 2.5-3.5k words) — medium-risk rewrite.
- Bundle.min.css drop on 85 pages — Cohesion Task 19.
- DPDP consent on sidebar form — parked.
- Per-course fee pages — Tier 3 optional, only if Fees Hub validates.
- BPIT.php / BVP.php zero-click investigation — parking lot.
- Server-side UTM persistence for attribution.
- GA4 ↔ Ads goal linking — Sumit's parallel UI work.

## 11. Implementation handoff

Next step after spec approval: invoke writing-plans skill to produce the implementation plan (file-level, task-level) keyed to this spec. The implementation plan will:

- Break each Day's deploy into discrete tasks (write code → localhost test → commit+tag → FTP push → curl-verify → OPcache reset)
- List exact files touched per task
- Specify watch-term rank-check command sequences
- Define rollback commands per Day
