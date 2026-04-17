# IPU.co.in — Deep Analysis (Search Console + Internal Linking + Code) — 2026-04-17

**Purpose:** Deeper-than-the-audit analysis across three axes Sumit asked for — Google Search Console data (28 days), internal link graph, and complete code-quality review (PHP/CSS/JS). **Analysis only — no code changes in this pass; action items will be specced later.**

**Constraints:** No design changes, no content changes.

**Companion docs:**
- Main audit: `2026-04-17-ipu-website-audit.md` (P0–P3 roadmap)
- This doc extends Section 2 (SEO) + Section 4 (internal linking) of the main audit with raw data-driven detail.

---

## Part A — Google Search Console (last 28 days)

Source: `ipu.co.in-Performance-on-Search-2026-04-17.xlsx`. Date range implied by file: ~2026-03-20 → 2026-04-17.

### A.1 Top-line metrics

| Country | Clicks | Impressions | CTR | Avg. Position |
|---|---|---|---|---|
| **India** | **629** | **98,340** | **0.64%** | 6.53 |
| United States | 3 | 4,391 | 0.07% | 9.68 |
| Other (145 countries) | ~3 | ~3,000 | — | — |
| **Total** | **~635** | **~106K** | **0.60%** | — |

**CTR of 0.6% at avg position 6.5 is well below industry benchmarks** — at position 6–7, healthy CTR is 2–3%. The 4× gap says **your titles/descriptions aren't compelling enough for the rank you're already getting**. More money sits in fixing titles than in climbing rank.

### A.2 Devices

| Device | Clicks | Impressions | CTR | Avg. Position |
|---|---|---|---|---|
| Mobile | 444 | 67,956 | **0.65%** | 5.96 |
| Desktop | 180 | 35,333 | 0.51% | 8.13 |
| Tablet | 13 | 2,278 | 0.57% | 5.81 |

**Mobile ranks better than desktop (5.96 vs 8.13)** — your mobile UX is Google-favored. But CTR is still only 0.65% on mobile. Desktop ranks deeper AND converts worse — less urgent to fix since Ads audience is 92% mobile anyway.

### A.3 Top organic queries

| Query | Clicks | Imp. | CTR | Position | Read |
|---|---|---|---|---|---|
| `ipu` | 29 | 18,962 | **0.15%** | 9.56 | ⚠️ 19K impressions wasted at page-1-bottom |
| `ip university` | 24 | 7,715 | 0.31% | 9.06 | ⚠️ Same story |
| `ipu counselling 2026 last date` | 6 | 84 | **7.14%** | 3.75 | ✅ Small volume but excellent CTR at a strong position |
| `ggsipu` | 5 | 2,227 | 0.22% | 8.92 | ⚠️ Brand term, page 1 bottom |
| `ipu counselling 2026` | 4 | 737 | 0.54% | **1.93** | ✅ Already at position 2 — huge CTR potential if title improves |
| `ipu btech counselling 2026` | 4 | 107 | 3.74% | 2.64 | ✅ High-intent, good rank |
| `ipu. ac. in` | 3 | 712 | 0.42% | 9.14 | Intent-hijack (Ads side) |
| `ggsipu counselling date 2026` | 3 | 357 | 0.84% | **2.18** | ✅ Position 2 but 0.84% CTR — title loses to other results |
| `ipu official website` | 3 | 295 | 1.02% | 6.25 | Intent-hijack |
| `ggsipu helpline number` | 3 | 53 | **5.66%** | 7.36 | ✅ Call-intent, high CTR |

**Big takeaways:**
1. **Branded queries `ipu` + `ip university`** — 26,677 impressions / 53 clicks = **0.20% CTR**. This is the mother lode. A title/description rewrite on the homepage (+ possibly moving from position 9 toward top 5) could 10-20× these clicks without a single line of content or design change.
2. **Queries at position 2 but low CTR** (`ipu counselling 2026`, `ggsipu counselling date 2026`): your page is in the right spot but the SERP snippet isn't winning the click. Usually a title/meta-description problem.
3. **"ggsipu helpline number" — 5.66% CTR at position 7.36.** Low impressions (53) but direct call-intent. Worth pushing this page's rank; would directly grow phone calls.

### A.4 Top organic pages

| Path | Clicks | Imp. | CTR | Position | Notes |
|---|---|---|---|---|---|
| `/GGSIPU-counselling-for-B-Tech-admission.php` | **230** | 23,919 | 0.96% | **3.64** | ✅ Top organic earner, good position, CTR still low for rank |
| `/` (homepage) | 163 | 46,825 | 0.35% | 9.40 | ⚠️ Highest impressions, worst CTR — title rewrite target #1 |
| `/IPU-Law-Admission.php` | 52 | 5,611 | 0.93% | 5.19 | OK |
| `/IPU-Law-Admission-2025.php` | 38 | 4,706 | 0.81% | **1.93** | ⚠️ Outdated-year URL ranks at position 2. Cannibalizes the 2026 page. |
| `/b-tech-colleges-under-IP-university.php` | 35 | 5,260 | 0.67% | 5.01 | OK |
| `/economics-admission-2025.php` | 32 | 2,157 | 1.48% | 5.41 | ⚠️ Outdated year, but best CTR on the list |
| `/guide-to-bjmc-colleges-under-ip-university.php` | 28 | 2,057 | 1.36% | 4.16 | OK |
| `/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` | 19 | 4,177 | 0.45% | 7.45 | Low CTR — title/desc issue |
| `/ultimate-guide-to-ballb-admission-in-ip-university.php` | 14 | 3,326 | 0.42% | 5.08 | Low CTR |
| `/ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php` | 6 | 1,716 | 0.35% | 10.73 | Weak position + bad CTR; URL slug is stuffed |
| `/IPU-B-Tech-admission-2026.php` | 6 | 1,451 | 0.41% | 3.87 | ⚠️ Your main Ads landing page only gets **6 organic clicks in 28 days** despite position 3.87 |
| `/blog.php` | 5 | 48 | **10.42%** | 7.71 | ✅ Tiny impressions but huge CTR — under-exposed |
| `/exploring-MAIT-and-MAIMS.php` | **2** | **3,828** | **0.05%** | 4.26 | 🔴 **Worst CTR on the site. 3,828 impressions → 2 clicks at position 4.** Title/snippet catastrophe. |
| `https://www.ipu.co.in/IPU-B-Tech-admission-2025.php` | 2 | 448 | 0.45% | 15.63 | 🔴 `www` subdomain leaking organic traffic + outdated year |
| `/ipu-admission-guide.php` | 2 | 218 | 0.92% | 16.74 | 🔴 Outside top 10; worth studying why it doesn't rank higher given its topical authority |
| `https://www.ipu.co.in/` | 0 | 241 | 0.00% | 8.14 | 🔴 `www` homepage duplicate |
| `https://mail.ipu.co.in/exploring-MAIT-and-MAIMS.php` | **1** | **539** | 0.19% | 4.04 | 🔴 **`mail.` subdomain serving content.** This should 404 / redirect; it's getting 539 impressions. |
| `https://www.ipu.co.in/index.php` | 0 | 15 | — | 10.87 | 🔴 Fourth homepage variant indexed |

**Critical SEO hygiene problems exposed by GSC (that the audit didn't surface):**

1. **Three indexed homepage variants** — `ipu.co.in/`, `www.ipu.co.in/`, `www.ipu.co.in/index.php`. Plus `index.php` (from earlier audit). Four URLs competing for the same content. Ranking dilution.
2. **`mail.ipu.co.in/exploring-MAIT-and-MAIMS.php` is serving the marketing site on the mail subdomain.** 539 impressions. This is a DNS/web-server misconfiguration — `mail.` should resolve to a mail service, not the marketing site.
3. **`exploring-MAIT-and-MAIMS.php` ranks at position 4 with 3,828 impressions and 0.05% CTR.** Either the title is wrong for the query intent, or the snippet is so bad users skip. Huge potential here.
4. **`/ipu-admission-guide.php` ranks at position 16.7** — outside top 10, 2 clicks, 0.92% CTR. Given it's a key landing page with strong topical depth, something is pushing Google away (thin internal links? title? content fit?).
5. **Year-specific pages still rank ABOVE their 2026 equivalents** — `/IPU-Law-Admission-2025.php` at position 1.93 vs `/IPU-Law-Admission.php` at 5.19. The 2025 page has the link equity; ranking strategy must account for this.

### A.5 Countries

Long tail (147 countries) is mostly bot noise. India = 99% of real clicks. **No action needed** except the GA4 bot filter already specced in the main audit.

### A.6 Search appearance

Only one non-standard: **Translated results** — 13 clicks / 635 imp. Google auto-translated your pages for 635 impressions to non-English-preferred users. Small, but indicates your content is globally readable — could signal hreflang / language alternate tags as a nice-to-have (low priority).

---

## Part B — Internal link graph analysis

**Source:** 115 `.php` files in `website_download/` scanned for `href="…"` patterns. Page-to-page links only; nav/footer links counted.

### B.1 Inbound-link leaderboard (top 20)

| Rank | Page | Inbound links | GSC clicks |
|---|---|---|---|
| 1 | `vips-pitampura-courses.php` | **23** | — |
| 2 | `IPU-B-Tech-admission-2026.php` | 21 | 6 |
| 3 | `GGSIPU-counselling-for-B-Tech-admission.php` | 20 | **230** |
| 4 | `exploring-MAIT-and-MAIMS.php` | 19 | 2 |
| 5 | `index.php` (homepage) | 17 | 163 |
| 5 | `IP-University-management-quota-admission-eligibility-criteria.php` | 17 | — |
| 7 | `ipu-admission-guide.php` | 15 | 2 |
| 8 | `explore-MSIT-and-MSI-janakpuri.php` | 15 | 1 |
| 9 | `BVP.php` | 15 | — |
| 10 | `mba-admission-ip-university.php` | 12 | — |
| 11 | `BPIT.php` | 12 | — |
| 12 | `b-tech-colleges-under-IP-university.php` | 8 | 35 |
| 20 | `IPU-Law-Admission-2026.php` | 5 | — |

**Insights:**
- **Internal link authority is misaligned with traffic reality.** `vips-pitampura-courses.php` has 23 inbound links (most of any page) but appears nowhere in GSC top-25 organic earners or GA4 top-20 by traffic. Link equity spent on a page nobody searches for.
- **`GGSIPU-counselling-for-B-Tech-admission.php`** — 20 inbound links, 230 GSC clicks. Well-linked AND high-performing. This page does the heavy lifting.
- **`exploring-MAIT-and-MAIMS.php`** — 19 inbound links, but GSC shows 2 clicks / 3,828 imp / 0.05% CTR. Tons of internal authority going to a page whose title is broken in SERPs.
- **`ipu-admission-guide.php`** — 15 inbound links but only 2 GSC clicks at position 16.7. Underperforming despite good internal signal.
- **`IPU-Law-Admission-2026.php`** — only 5 inbound links, while `IPU-Law-Admission-2025.php` (the outdated one) is what's ranking. Link equity isn't flowing to the current-year page.

### B.2 Orphan pages (0 inbound internal links)

6 orphans found:
- `IPU-B-Tech-admission-2025.php` — intentional? Already flagged as outdated.
- `don-bosco-admission.php`
- `kasturi-ram-admission.php`
- `lingayas-admission.php`
- `meri-admission.php`
- `sendemail.php` — probably a utility; should be noindex/robots-blocked anyway.

**Action guidance (not executed in this analysis):** Orphans beyond `IPU-B-Tech-admission-2025.php` are college-specific pages that should be linked from some college hub (e.g., `ipu-colleges-list.php` or a sidebar "Other IPU Colleges"). Otherwise, Google may never discover them, and if it does, they have zero internal authority.

### B.3 Weak pages (1–2 inbound links)

68 pages have 1–2 internal links. Notable:
- `IPU-Law-Admission.php` — 1 inbound (year-less "canonical" version)
- `IPU-Law-Admission-2025.php` — 1 inbound (but ranks higher, see A.4)
- `economics-admission-2025.php` — 1 inbound (ranks position 5.41 with 32 GSC clicks)
- `economics-admission-ip-university.php` — 1 inbound (the year-less "canonical")
- Most individual college pages (BCIPS, CPJ, DME, Echelon, Fairfield, GIBS, GNIT, HMR, Jemtec, etc.)

### B.4 Structural gaps

- **No blog-to-guide cross-linking:** `/blog.php` has 10.42% CTR but only 48 impressions — Google doesn't see it much. Zero internal links from the 20+ high-traffic guide pages point into `blog.php` (or any specific blog post).
- **News module is isolated:** `/news/` has 19 views in 90 days (GA4). No high-traffic page links to `/news/` or to any individual news post. The automated pipeline is generating content nobody can find internally.
- **College pages don't cross-compare:** each college-specific page (BVP, BPIT, MAIT, MAIMS, etc.) could cite 2–3 sibling colleges to build topical clusters, but most just link back to the main admission guide.

---

## Part C — Code quality review (PHP / CSS / JS)

### C.1 File inventory

| Type | Files | Total size |
|---|---|---|
| PHP | 162 | 1.77 MB |
| CSS | 13 | 0.91 MB |
| JS | 16 | 0.38 MB |
| Images (.jpg/.png/.webp/.svg/.gif) | 195 | ~12.6 MB |
| Fonts (.ttf/.eot/.woff/.woff2) | 25 | ~4.6 MB |
| ZIP | **2** | **16.7 MB** |
| **Total** | 423 | **37.1 MB** |

### C.2 Problems found

#### C.2.1 🔴 Old PHP error log on the FTP server

`website_download/error_log` (an actual cPanel error log, untracked) exposes 10+ months of PHP warnings:

```
[14-Dec-2025] PHP Warning: Cannot modify header information - headers already sent by (output started at include/head.php:1) in include/form-code.php on line 49
[18-Dec-2025] (same error, 6+ occurrences)
[29-Jan-2026] PHP Warning: include_once(include/blog.php): Failed to open stream
[29-Jan-2026] PHP Notice: session_start(): Ignoring because a session is already active in index2.php
[30-Jan-2026] PHP Warning: include_once(include/blogside.php): Failed to open stream
[31-Jan-2026] PHP Warning: Undefined array key "captcha" in /testblog.php on line 20
```

**Interpretation of each error:**
- **"Headers already sent"** — `include/form-code.php` calls `header("Location: thank-you.php")` after output has started. Because `include/head.php` writes `<!DOCTYPE html>` immediately on include (no `<?php ?>` open tag before the output). The redirect still works with output-buffering (`ob_start()`), but on pages where `ob_start()` isn't called, redirects silently fail → users submit forms and see a blank screen. Recent `form-handler.php` has proper `ob_start()` + session guard; `form-code.php` (older) does not. Consider migrating all form-code.php callers to form-handler.php.
- **`include/blog.php` missing** — `index.php:530` tries to `include_once('include/blog.php')`. File doesn't exist. Every homepage load logs a warning. Either remove the include or create the file.
- **`include/blogside.php` missing** — same for `blog.php:94`.
- **`session_start()` on `index2.php`** — called when session is already active. Points to `index2.php` being an abandoned duplicate.
- **Undefined `$_POST['captcha']`** — `testblog.php:20` uses an undefined array key. `testblog.php` is a test file that shouldn't be live (already flagged in main audit).

**Action (later):** delete `/error_log` from the FTP root (sensitive data exposure). Fix the four PHP issues. Also add `DirectoryIndex` / `Options -Indexes` hardening in `.htaccess`.

#### C.2.2 🔴 `.zip` files in the FTP root

- `website_download/ipu-well-known.zip` (13 MB) — shouldn't exist in production. Publicly downloadable if someone guesses the URL. Delete from server.

#### C.2.3 🟡 CSS bloat & duplication

Three overlapping CSS bundles coexist:
| File | Size | Minified? | Purpose |
|---|---|---|---|
| `bootstrap.min.css` | 152 KB | yes | Bootstrap 4 |
| `bootstrap5.min.css` | 227 KB | yes | Bootstrap 5 |
| `bundle.css` | 157 KB | no | Project bundle (unminified) |
| `bundle.min.css` | 95 KB | yes | Project bundle (minified) |
| `style.css` | 119 KB | no | Old theme (untracked) |
| `default.css` | 11 KB | no | Unclear |
| `critical.min.css` | 63 KB | yes | Critical CSS |

**Issues:**
- **Both Bootstrap 4 AND 5 CSS files exist** — if pages include both, ~380 KB of duplicate framework. `base-head.php` loads only `bootstrap5.min.css`, but older pages may still include `bootstrap.min.css` (Bootstrap 4). Worth confirming.
- **`bundle.css` (unminified, 157 KB) is 62 KB larger than `bundle.min.css`.** Most likely both ship to production; minified version should be the only one.
- **`style.css` (119 KB)** is legacy theme CSS with different fonts (`Heebo`, `Rubik`) from the current `Inter`. If it's still loaded somewhere, those old font declarations conflict with the homepage's unified-Inter change we just shipped.

**Action (later):** audit which CSS files are loaded on which pages. Consolidate; drop unused files; ship only minified.

#### C.2.4 🟡 JavaScript: old-jQuery plugin stack

`assets/js/` contains:
- `bootstrap.min.js` (57 KB) — Bootstrap 4 JS (should be Bootstrap 5 bundle since we load `bootstrap5.min.css`)
- `jquery.counterup.min.js` — the counter animation lib that wasn't firing (fixed today by hardcoding)
- `jquery.magnific-popup.min.js` (20 KB), `jquery.nice-select.min.js` (2.9 KB), `jquery.appear.min.js` (1.2 KB) — all jQuery-dependent
- `slick.min.js` (42 KB), `isotope.pkgd.min.js` (35 KB), `waypoints.min.js` (7.9 KB) — often-unused on lead-gen pages
- `popper.min.js` (21 KB) — needed by Bootstrap tooltips/dropdowns
- `main.js` (7.8 KB), `app.js` (8.7 KB) — project code

**Total JS budget: ~380 KB** — heavy for a lead-gen site, especially on mobile.

**Issues:**
- Possibly loading **both Bootstrap 4 and 5 JS** somewhere (mirrors the CSS issue).
- jQuery is a dependency for half of these but I didn't see a `jquery.js` in the bundle — they must rely on the global jQuery loaded elsewhere. Confirm it's loaded once, not twice.
- `slick.min.js` and `isotope.pkgd.min.js` are carousel/masonry libraries. If the homepage doesn't use them, they should be deferred or removed.

**Action (later):** measure actual JS usage on the top 5 landing pages; defer/remove unused libs. Mobile-first audience = JS weight matters a lot.

#### C.2.5 ✅ Form security (no issues found)

`include/form-handler.php` has solid defenses:
- Honeypot field `website`
- Time-gate (3-second min fill)
- 5-min session cooldown
- Session phone dedup
- Cookie 24h dedup
- `htmlspecialchars($_POST[...], ENT_QUOTES, 'UTF-8')` on all fields
- Indian phone regex validation `/^[6-9]\d{9}$/`
- `filter_var(... FILTER_VALIDATE_EMAIL)` for email
- No raw SQL (no database at all — emails only)

**No PHP injection, no SQL injection, no XSS surface.** Nicely done.

`include/form-code.php` is the older parallel handler — same sanitization, similar structure. Likely should be deprecated in favor of `form-handler.php`, but not a security risk.

#### C.2.6 ✅ No leaked credentials in committed PHP

Grepped for `password`, `api_key`, `token`, `secret` — no matches in `include/*.php`. Good.

#### C.2.7 🟡 Inline scripts per page

Most top pages have 1–3 `<script>` blocks. Not a security risk but a maintenance one — common JS should live in `main.js` and be cached across pages.

#### C.2.8 `.htaccess` — solid foundation

The existing `.htaccess` (actually named `htaccess` without dot, suggesting it's a local copy) already has:
- HTTPS force redirect
- Gzip compression
- Browser caching (1 month for JS/CSS, 1 year for images/fonts)

**Missing:**
- `/index.php` → `/` 301 (flagged in main audit §1.2)
- Trailing-slash strip (§1.3)
- `Disallow` for dev paths (`/k2-work/*`, `/testblog.php`, `/index2.php`-`4.php`)
- `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Content-Security-Policy` headers (nice-to-have security headers)

---

## Part D — Prioritized summary of new findings

These are NEW items surfaced by this deeper pass, not already in the main audit. Tag format: `[Impact / Effort / SEO risk]`.

### New P0 — serious, address soon

| # | Finding | Tags |
|---|---|---|
| D.P0.1 | **`mail.ipu.co.in/` serving marketing pages.** 539 impressions of MAIT-MAIMS under the mail subdomain. DNS or web-server config issue. | `[H / 1h / Low]` |
| D.P0.2 | **`www.ipu.co.in` and `ipu.co.in` both indexed.** Four homepage variants total in GSC. Need a canonical `www` → apex (or vice versa) 301 in `.htaccess`. | `[H / 30m / Low]` |
| D.P0.3 | **PHP warnings leaking** from missing `include/blog.php` + `include/blogside.php` on every page load. Delete stale `include_once` references OR create stub files. | `[M / 30m / None]` |
| D.P0.4 | **Delete `error_log` + `ipu-well-known.zip` from FTP root.** Sensitive exposure. | `[M / 5m / None]` |

### New P1 — high-leverage SEO wins

| # | Finding | Tags |
|---|---|---|
| D.P1.1 | **Rewrite title + meta-description for homepage** (ranking for `ipu` / `ip university` at position 9.4, CTR 0.35%). Goal: double CTR without moving rank. | `[VERY HIGH / 1h / None]` |
| D.P1.2 | **Rewrite title + meta-description for `exploring-MAIT-and-MAIMS.php`** (3,828 imp, 0.05% CTR at position 4.26 — worst CTR on site). | `[H / 30m / None]` |
| D.P1.3 | **Rewrite titles on position-2 pages** (`ipu counselling 2026`, `ggsipu counselling date 2026`) — ranking is there, CTR isn't. | `[M / 1h / None]` |
| D.P1.4 | **Add canonical + 301 from 2025 year-pages to their 2026/evergreen equivalents.** Year-old pages still outrank the new ones; consolidate equity explicitly. | `[H / 1h / Low]` |
| D.P1.5 | **Link graph rebalancing:** move internal links AWAY from low-GSC pages (`vips-pitampura-courses.php`, `exploring-MAIT-and-MAIMS.php`) TOWARD high-GSC pages (`GGSIPU-counselling-for-B-Tech-admission.php`, `IPU-Law-Admission.php`, `IPU-B-Tech-admission-2026.php`). | `[H / 3h / None]` |
| D.P1.6 | **Connect the 6 orphan college pages** (Don Bosco, Kasturi Ram, Lingayas, MERI — link from `ipu-colleges-list.php` + a sibling-colleges block). | `[M / 1h / None]` |
| D.P1.7 | **Internal links to `/blog.php` and `/news/`** from top 5 guide pages (both content destinations are starved of Google-visible discovery paths). | `[M / 1h / None]` |

### New P2 — code hygiene / infra

| # | Finding | Tags |
|---|---|---|
| D.P2.1 | Migrate remaining `form-code.php` callers to `form-handler.php`. Kill the "headers already sent" warnings. | `[M / 2h / None]` |
| D.P2.2 | **CSS audit** — confirm no page loads both Bootstrap 4 and 5 bundle; drop unminified duplicates; remove `style.css` if unused. Goal: −200 KB off mobile pages. | `[M / 4h / None]` |
| D.P2.3 | **JS audit** — measure which of slick / isotope / magnific / etc. is actually used per page; defer or remove unused. | `[M / 4h / None]` |
| D.P2.4 | Add security headers to `.htaccess` (X-Frame-Options, Referrer-Policy, nosniff). | `[L / 30m / None]` |
| D.P2.5 | Add `Disallow` for `/k2-work/`, `/testblog.php`, `/index[2-9]*.php` to robots.txt (also covered in main audit §2.1 — bump priority after GSC confirms these URLs). | `[M / 15m / Low]` |

### New P3 — nice-to-have

| # | Finding | Tags |
|---|---|---|
| D.P3.1 | Add `hreflang` alternates — GSC shows 13 clicks via "Translated results". Low priority but a free global-reach nudge. | `[L / 1h / None]` |
| D.P3.2 | Build a news-module internal-link hub on top guide pages (news widget in sidebar). | `[L / 2h / None]` |

---

## Part E — What's NOT a problem

Calling these out explicitly so you don't waste time:

- **Form security:** Excellent. Don't touch.
- **llms.txt / robots.txt for AI crawlers:** Excellent. Better than 95% of sites. Don't touch.
- **JSON-LD schema on top pages:** Present and structurally correct. Nothing to fix.
- **HTTPS enforcement + gzip + caching:** Already in `.htaccess`. Solid.
- **Ads economics:** Exceptional (₹18 CPA, 15.54% CTR). Don't touch the ad copy. Do NOT start "optimizing" Ads based on this analysis.
- **Font unification:** Done today. Stable.
- **Mobile breakpoint handling:** Not audited in depth but the site renders mobile-first per `base-head.php` CSS. Fine for now.

---

## What to do with this document

**Nothing, immediately.** This is analysis.

When you want to act: pick items from Parts A–D above, I'll scope each one as a separate spec + plan (using the same brainstorm → spec → plan → execute discipline we followed for P0.1–P0.3).

**Suggested sequencing when the time comes** (paired with the main audit's own roadmap):

1. Do the remaining P0 items from the **main audit** first (call extensions, missed-call automation).
2. Then this doc's D.P0 batch (`mail.` subdomain, `www.` canonical, missing includes, error_log cleanup).
3. Then the title/meta-description rewrites (D.P1.1 through D.P1.3) — these are by far the highest-ROI SEO moves available right now. Pick one page a week so rankings have time to settle.
4. Then link-graph rebalancing + orphan fixes.
5. Then code-hygiene (CSS/JS pruning) — last because risk is highest and ROI is visible but indirect.
