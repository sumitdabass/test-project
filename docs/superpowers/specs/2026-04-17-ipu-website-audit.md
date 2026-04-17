# IPU.co.in Website Audit — 2026-04-17

**Scope:** Full technical + on-page + Ads + operational audit of `ipu.co.in`, prioritized by impact on the site's primary KPI: **inbound phone calls** (form submissions are a secondary remarketing asset).

**Source data used in this audit:**
- GA4 "Reports snapshot" (last 90 days, 16 Jan – 15 Apr 2026)
- GA4 "Pages and screens: Page path and screen class" (top 100 paths, 90 days)
- GA4 "User engagement and retention" overview
- IVR call log CSV (`sumit_1776408880.csv`, 1,411 rows, 10 Feb – 17 Apr 2026)
- Google Ads Overview cards (Campaigns, Ads, Keywords, Searches, Demographics, Devices, Time series, Biggest changes, 1 Feb – 17 Apr 2026)
- Google Ads Asset association report (127 rows, 1 Feb – 17 Apr 2026)
- Live fetch of homepage `https://ipu.co.in/`
- Local source of 117 pages, includes, sitemap.xml, robots.txt, llms.txt

**Out of scope (per Sumit's constraint):** URL renames on live pages, page deletions of currently indexed URLs, wholesale content rewrites, design-system changes.

---

## Executive summary

**The business is working.** Ads economics are exceptional — ₹5.42 CPC, ₹18.24 CPA, 15.54% CTR, ~₹13.7K/month spend. Phone calls ≈ 21/day, form submits ≈ 17/day. Two campaigns, tight targeting, growing spend trajectory.

**Three root-cause issues are leaking leads or breaking signals:**

1. **Operational:** 25.7% of incoming calls are missed (363 in 66 days). Biggest single lever in the system.
2. **Tracking blindness:** Zero attribution between web sessions and phone calls. Every call-optimization decision is guesswork today.
3. **Web hygiene debt:** URL canonicalization (`/` vs `/index.php`), year-duplicate pages (`2025` still indexed while `2026` competes for same term), dev/staging URLs (`/k2-work/*`, `/testblog.php`, `/index2.php`…) in Google's index. Splits SEO equity, wastes Ads sitelink clicks.

> **Progress log**
> - 2026-04-17 — P0.3 Click-to-call GA4 event shipped. `phone_click` dataLayer event live on all pages via GTM container `GTM-5GXCN7Z`. Deployed to production (commit `60b0bff`). GA4 Realtime verification + "Mark as Key Event" deferred by user. Next action on this item: confirm events flowing in Realtime, then dedupe vs. existing `phone_clicks` (plural) tag.
> - 2026-04-17 — P0.1 Hero counters fixed. `trust-bar.php` counter `<span>` pattern replaced with hardcoded values (100,000+ Students Graduated, 60+ Affiliated Colleges, 15+ Courses Offered, Est. 1998). No longer depends on the untracked `jquery.counterup.min.js`. Deployed to production (commit `c5ae296`).
> - 2026-04-17 — P0.2 Ads sitelinks swapped 2025 → 2026. All 4 enabled sitelink records pointing at `/IPU-B-Tech-admission-2025.php` (plus the 1 paused account-level record) updated to `/IPU-B-Tech-admission-2026.php` in Google Ads. Wasted sitelink spend stopped.

**Highest-impact findings (headline):**
- Homepage shows broken counter "**0+ Students Graduated**" in the live hero section — visible to every visitor. Immediate credibility killer.
- **Ads sitelinks still point to `/IPU-B-Tech-admission-2025.php`** (last year). Wasted spend on every sitelink click.
- **No WhatsApp CTA** anywhere on the site — India-specific gap for the 92%-mobile audience.
- **Near-zero returning users** (retention cohorts all below 10%) + **no remarketing audience segmentation** visible — form submissions flow into a pipeline but aren't being used as the stated "remarketing asset."

---

## Section 1 — Tracking gaps to close first

Every other recommendation below depends on trusting the numbers. These gaps should be closed **before** we make any change that could affect SEO or Ads, so we can A/B measure.

### 1.1 Click-to-call tracking is not wired

**Finding:** `tel:+919899991342` links exist in `include/base-nav.php` (mobile-call-btn, widget-call-btn) and on key pages (5 instances in homepage alone). No `gtag` / `dataLayer` event fires when users tap these links. GA4 has `52,320 events` in 90 days but none are attributable to phone clicks.

**Why it matters:** Phone calls are the primary KPI. Without click-to-call events, you cannot answer:
- Which landing pages drive the most call clicks?
- Does Ads campaign A drive calls more efficiently than campaign B?
- Is the sticky-bottom call button more effective than the header one?

**Fix:** Add a delegated click handler in `base-head.php` (or in a small `tracking.js`) that fires `gtag('event','phone_click',{…})` when any `a[href^="tel:"]` is clicked. Also fire the same event into `dataLayer` so GTM can route it to Google Ads as a conversion.

**SEO risk:** None.
**Effort:** ~30 min. **Impact:** High (unlocks data-driven call-CRO forever).

### 1.2 Homepage tracked as two separate URLs

**Finding:** GA "Pages and screens" shows both `/` (8,614 views) and `/index.php` (576 views) as separate rows. Both render the same page.

**Why it matters:**
- Views and engagement are split across two URLs in all reports.
- Google may treat them as duplicate content if both are indexable and there's no canonical consolidation.
- The site's canonical tag on the homepage already says `https://ipu.co.in/` — good — but Apache isn't redirecting `/index.php` to `/`.

**Fix:** Add to `.htaccess`:
```
RewriteCond %{THE_REQUEST} /index\.php[\s?] [NC]
RewriteRule ^index\.php$ / [R=301,L]
```
This 301-redirects `/index.php` → `/`. Preserves all link equity, consolidates GA.

**SEO risk:** Low (301s preserve rankings). Test on staging first if possible.
**Effort:** ~15 min + verify. **Impact:** Medium (cleans up GA + prevents future duplication).

### 1.3 Trailing-slash URL variants indexed

**Finding:** GA shows `/IPU-B-Tech-admission-2025.php/`, `/comprehensive-guide-to-bba-…-institutions.php/`, `/index.php/` etc. — same URLs with stray trailing slashes, treated as separate pages. Low views each (1–4) but they exist in Google's index.

**Fix:** `.htaccess` rule to strip trailing slashes from `.php` URLs:
```
RewriteRule ^(.+)\.php/$ /$1.php [R=301,L]
```

**SEO risk:** Low (301). **Effort:** ~10 min. **Impact:** Low individually, but part of overall canonical hygiene.

### 1.4 Bot/low-quality traffic contaminating GA

**Finding:** 
- "Lanzhou" (China) = 298 users in 90 days
- "Singapore" = 503 users in 90 days
- Your audience is 99% Delhi NCR (per call log). Neither Lanzhou nor Singapore matches your customer base.

**Why it matters:** Inflates user counts, skews engagement averages downward (bots visit 1 page then leave), hides real conversion math.

**Fix:**
- GA4 → Admin → Data settings → **Data filters** → create filter for `country != India` (or restrict to specific Indian states).
- Alternative: leave raw data, but always apply a "country = India" comparison when reading reports.

**SEO risk:** None. **Effort:** ~15 min. **Impact:** Low-medium (cleaner data).

### 1.5 Call tracking in Google Ads

**Finding:** Asset report shows exactly **1 "Call" asset** across all campaigns. Unclear whether call-conversion tracking is enabled for it. No call-only ads observed.

**Why it matters:** Your top-converting keyword is "ggsipu helpline" at 52.5% CVR — literal call-intent. Without call extensions + call tracking in Ads, you're counting only form submits as conversions, undercounting true ROI.

**Fix:**
- Enable **Call extensions** on both campaigns. Use the same `9899991342`.
- In Google Ads → Tools → Conversions, add a **"Calls from ads"** conversion action (requires 60-second minimum duration — matches your 94s median).
- Optional: enable call-reporting Google forwarding numbers for per-campaign attribution.

**SEO risk:** None. **Effort:** ~20 min. **Impact:** High (counts real ROI, unlocks Smart Bidding on calls).

---

## Section 2 — Technical SEO findings

### 2.1 Dev/staging URLs in Google's index

**Finding:** These paths show views in GA (so Google is serving them and crawling them):
| Path | 90-day views |
|---|---|
| `/k2-work/ipu2026/` | 2 |
| `/k2-work/ipu2025/` | 2 |
| `/k2-work/ipu/edulanding/` | ~4 |
| `/k2-work/ipu2026/blog.php` | 2 |
| `/k2-work/ipu/ipu2025/` | 2 |
| `/testblog.php` | 78 |
| `/index2.php`, `/index3.php`, `/index4.php` | up to 66 each |
| `/news/welcome-news-launched.php` | 2 (intentional, keep) |

These aren't in `sitemap.xml` but Google discovered them via links or the live server. They pollute the index and dilute link equity.

**Fix options (pick one):**
- **(A)** Add to `robots.txt`: `Disallow: /k2-work/` and `Disallow: /testblog.php` and `Disallow: /index[0-9]*.php`. This prevents future crawling but won't de-index already-indexed pages.
- **(B)** Add `<meta name="robots" content="noindex">` to each of those files, then wait for Google to re-crawl (~2–8 weeks). Most thorough but slowest.
- **(C)** Delete the files from the FTP server entirely (Sumit said "no deletions of indexed pages" — but these are *dev artifacts*, not real content. Worth reconsidering the rule for this subset).

**Recommendation:** **(B)** for staging-like pages + **(A)** belt-and-suspenders robots block. Don't delete anything — Sumit's rule stands for real pages and safety-first is the right default.

**SEO risk:** Low. Noindex + robots on non-organic pages doesn't affect ranking pages.
**Effort:** ~30 min. **Impact:** Medium (cleaner index signals).

### 2.2 Year-specific duplicate pages

**Finding:**
| Old page | Views | Conv | New page | Views | Conv |
|---|---|---|---|---|---|
| `/IPU-B-Tech-admission-2025.php` | 255 | **0** | `/IPU-B-Tech-admission-2026.php` | 1,265 | 20 |
| `/IPU-Law-Admission-2025.php` | 143 | 6 | `/IPU-Law-Admission.php` | 285 | 18 |
| `/economics-admission-2025.php` | 97 | 2 | `/economics-admission-ip-university.php` | 19 | 1 |

The 2025 pages are direct competitors with their 2026 counterparts for the same keywords. Google has to guess which is authoritative; link equity is split.

**Compounded issue:** Ads sitelinks still point at `/IPU-B-Tech-admission-2025.php` (confirmed in Asset association report — used in multiple campaigns). Every sitelink click sends a user to the outdated page.

**Fix:**
1. **Immediate:** Swap Ads sitelinks from `-2025.php` URLs to `-2026.php` / non-year equivalents. This is a 5-minute change in Google Ads. Zero SEO risk. Biggest single Ads-waste fix.
2. **Medium-term:** Add `<link rel="canonical" href="/IPU-B-Tech-admission-2026.php">` to `/IPU-B-Tech-admission-2025.php` (and parallel for Law/Economics). Leaves the 2025 page accessible (so old backlinks don't break) but tells Google the 2026 version is authoritative. No URL change, no redirect, no deletion.
3. **Future preventive:** Per Sumit's own memory (`feedback_page_naming.md`), don't use year in filenames. For 2027+, redesign these as year-agnostic pages with year handled in `<title>` / content only.

**SEO risk:** None for (1); low for (2) because canonical is a hint, not a directive.
**Effort:** 5 min + 30 min. **Impact:** High (Ads waste + rankings consolidation).

### 2.3 Sitemap hygiene

**Finding:** `sitemap.xml` is 560 lines, well-organized with section comments (CORE AUTHORITY, etc.). Does NOT include dev/staging URLs (good). Does include both year variants.

**Fix:** Remove `/economics-admission-2025.php`, `/IPU-Law-Admission-2025.php`, `/IPU-B-Tech-admission-2025.php` entries from sitemap. Let the canonical tag + 301 (if added) handle the rest.

**SEO risk:** Low (pages still accessible, just not in sitemap).
**Effort:** ~10 min. **Impact:** Low (nudges Google toward 2026 pages).

### 2.4 Robots.txt + llms.txt

**Finding:** Both files are well-structured and AI-friendly:
- `robots.txt` explicitly allows GPTBot, CCBot, anthropic-ai, Claude-Web, googlebot-extended, bingbot, etc. Zero crawl-delay. Correct for Sumit's AI-visibility strategy.
- `llms.txt` is detailed with entity info, citation preferences, expertise signals. Updated 2026-04-06.

**No fix needed.** Both files are better than most sites. Flagging only as context for why other recommendations are compatible with the AI-visibility goal.

### 2.5 Per-page SEO tag coverage

**Sampled:**
| Page | `tel:` | canonical | JSON-LD | meta desc |
|---|---|---|---|---|
| `index.php` | 5 | ✓ | ✓ | ✓ |
| `IPU-B-Tech-admission-2026.php` | 1 | ✓ | 3 | ✓ |
| `ipu-admission-guide.php` | 1 | ✓ | 3 | ✓ |
| `GGSIPU-counselling-for-B-Tech-admission.php` | 1 | ✓ | 2 | ✓ |

All sampled pages have baseline SEO tags. No gaps at this level.

**No fix needed** for the top pages. Long-tail pages may still have omissions — to be batch-scanned if desired (Section 6, low priority).

---

## Section 3 — Per-page findings

Organized by business value = `views × (conversion potential)`. Top 10 shown in detail; the rest in a summary table.

### 3.1 `/` (homepage) — 8,614 views, 19s engagement, 745 conversions (48.4% of all)

**Lead-gen status: STRONG, with bugs.**

**What's working:**
- Phone number appears 5× in source; prominent in hero, header, and sticky-bottom button; all `tel:` linked.
- Lead form above the fold with a narrow field set ("Get Free Admission Guidance" + "Request a Callback").
- Hero H1 is keyword-rich: "Expert Guidance for IPU Admission 2026".
- Urgency copy present: "Limited seats, apply early!"

**What's broken / missing:**

**🔴 CRITICAL — Broken counter placeholders in the hero:**
`include/components/trust-bar.php` has counter spans (`data-stop="100000"` etc.) that rely on `jquery.counterup.min.js` to animate. That JS file is **in the untracked files list — not deployed via git.** If cPanel doesn't have it either, counters freeze at `0+` forever. Live page shows "**0+ Students Graduated**, 0+ Courses Offered, 0+ Affiliated Colleges". Visible to every landing visitor — immediate credibility killer.

**Second problem with the same component:** the `data-stop` values themselves are questionable.
- `100000` Students Graduated — `llms.txt` says "5000+ students guided". Two-orders-of-magnitude inconsistency. Pick one true number.
- `15` Courses Offered — understated; IPU offers 10+ major courses and many specializations; "15+" reads weak.
- "Est. 1998" — that's IPU-the-university's year, not ipu.co.in's year. Misleading positioning.

**Fix (two parts):**
1. Ship `jquery.counterup.min.js` to FTP (or replace with a 20-line vanilla JS counter — less dep overhead).
2. Reconcile the numbers to be truthful and consistent with `llms.txt`. Suggest: `5000+ Students Guided | 60+ Affiliated Colleges | 10+ Courses Guided | Since 2015` (matching llms.txt's "serving 5000+ students annually since 2015").

**🟡 No WhatsApp CTA.** In India, 92% mobile audience, WhatsApp is a default communication channel. Adding a floating WhatsApp button (`wa.me/919899991342?text=...pre-fill...`) could capture leads who won't fill a form and won't call during business hours.

**🟡 7+ CTAs visible in first viewport.** Header "CALL 9899991342", hero "Call Now", form "Request a Callback", mid-CTA "Call: 9899991342", multiple "View Details →" college cards. Decision paralysis risk. Consider: single primary CTA (call), single secondary (form), demote college links below fold.

**🟡 No testimonials / social proof.** No reviews, no student quotes, no "5000+ guided" counter (beyond broken placeholder). Trust deficit for first-time Ads visitors.

**🟢 Keep:** ad copy, H1, sticky phone button, `tel:` protocol everywhere.

**Priority:** P0 fix the counter; P1 add WhatsApp; P2 rework CTA density; P2 add testimonials.

### 3.2 `/IPU-B-Tech-admission-2026.php` — 1,265 views, 1m 08s engagement, 20 conversions (1.58% CR)

**Observed in source:** canonical present, 3 JSON-LD blocks, 1 `tel:` link, meta description present.

**Issue:** 1m 08s engagement is **good** (above site median) — users are reading. But 1.58% form-submit CR is low relative to the homepage (8.65%). Likely causes:
- Call-to-call CTA less prominent than homepage (only 1 `tel:` vs 5 on homepage).
- Form may not be above the fold.
- Primary audience (B.Tech aspirants) may prefer call over form.

**Fix:** Add the same mobile sticky-bottom call button as homepage (from `base-nav.php` `.mobile-call-btn`). Raise form visibility. This page gets ~14 views/day — even modest CR lift compounds.

**SEO risk:** None (adding CTAs doesn't harm rankings).
**Effort:** ~1 hr. **Impact:** Medium.

### 3.3 `/ipu-admission-guide.php` — 1,183 views, 31s engagement, 33 conversions (2.79% CR)

Engagement is **below average** (31s vs site 48s). Users skim, don't read. Either:
- Content isn't matching search intent (check ranking queries).
- Page is too long and the form is at the bottom.

**Fix:** Add in-content CTA blocks at 30% and 70% scroll depth. Ensure the first 200 words answer the searcher's question directly (probably "how do I get into IPU").

**Effort:** ~2 hrs (content reshuffling). **Impact:** Medium.

### 3.4 `/GGSIPU-counselling-for-B-Tech-admission.php` — 802 views, 55s engagement, 6 conversions (0.75% CR)

**🟡 High-traffic, low-conversion page.** 55s engagement means users read it, but only 0.75% convert. This page likely ranks for informational queries ("how does IPU counselling work") where users get their answer and leave — not lead-intent queries.

**Fix:**
- Add an explicit "Confused? Call us — we'll guide you through counselling live." CTA block mid-article.
- Check Search Console for the actual ranking queries to confirm intent.

**SEO risk:** None. **Effort:** ~30 min. **Impact:** Low-medium.

### 3.5 `/thank-you.php` — 721 views, 563 key events

**Status:** Working as designed. Views ≈ events = each form submit lands here and fires the conversion event. 24s engagement is normal for a thank-you page.

**Opportunity:** Convert this page from a dead-end into a secondary conversion step:
- Add "While you wait for our callback, here's our WhatsApp: …"
- Add "Or book a specific time on our calendar: …"
- Add a video testimonial or success story
- Trigger a remarketing pixel here for exact-match audience building

**SEO risk:** None (page is noindex already, hopefully — verify).
**Effort:** ~1 hr. **Impact:** Medium.

### 3.6 `/index.php` — 576 views, 35s engagement, 35 conversions

**Status:** This is the homepage double-tracked (see §1.2). The 576 views are duplicates of `/` traffic, and 35 conversions are subset of `/` 745.

**Fix:** Covered in §1.2 (redirect `/index.php` → `/`). No separate work.

### 3.7 `/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` — 452 views, 41s engagement, 16 conversions (3.54% CR)

**Status:** Good CR for an informational page. No specific issues detected at source level.

**Opportunity:** Internal link to this from `/mba-admission-ip-university.php` and `/bcom-admission-ipu.php` (related audience). Build a "BBA hub" cross-link cluster.

**Effort:** ~20 min. **Impact:** Low.

### 3.8 `/ipu-cet-admit-card-exam-date-examination-schedule-and-admit-card.php` — 414 views, 44s engagement, 10 conversions (2.42% CR)

**Status:** Title contains "examination-schedule-and-admit-card" — stuffed and duplicative. **Don't rename** (SEO constraint) but:
- Update the `<title>` tag (HTML) — this doesn't change the URL, and Google uses the `<title>` as the ranking signal, not the URL slug.
- Current title likely includes the full slug verbatim.
- Better title: "IPU CET 2026 Admit Card: Release Date, Download Link, Exam Schedule"

**SEO risk:** Low — title tweaks can temporarily wobble rankings. Do one at a time and monitor.
**Effort:** ~15 min. **Impact:** Medium (this is a high-intent, time-sensitive query).

### 3.9 `/ipu-colleges-list.php` — 321 views, 1m 03s engagement, **0 conversions**

**🔴 HIGH-TRAFFIC ZERO-CONVERSION PAGE.** 1m 03s engagement is above median — users ARE reading. But zero form submits in 90 days.

**Likely causes:**
- No form on the page.
- No phone CTA.
- Users get their answer (the college list) and leave.

**Fix:**
- Add sticky mobile call button (use existing include).
- Add one in-content CTA: "Not sure which college fits you? Call us for personalized advice."
- Add "Compare these colleges" → link to `/top-btech-colleges-ipu-comparison.php` which is an existing page.

**SEO risk:** None. **Effort:** ~30 min. **Impact:** High (321 views × 1m engagement × 0% CR = untapped demand).

### 3.10 `/IPU-B-Tech-admission-2025.php` — 255 views, 23s engagement, **0 conversions**

**🔴 Outdated page still pulling traffic AND being linked from Ads.**

**Fix:** 
1. Swap Ads sitelinks to 2026 version (Ads-console change, 5 min).
2. Add canonical → `/IPU-B-Tech-admission-2026.php`.
3. Add visible banner at the top: "This page covers 2025. [Read the 2026 admission guide →]".

**SEO risk:** None. **Effort:** ~20 min. **Impact:** High (stop bleeding Ads spend + organic).

### 3.11–3.20 — Lower-traffic pages summary

| # | Path | Views | Engagement | Conv | Top issue |
|---|---|---|---|---|---|
| 11 | `/blog.php` | 294 | 24s | 7 | Low engagement — blog index doesn't pull readers in |
| 12 | `/IPU-Law-Admission.php` | 285 | 39s | 18 | Good. Year-dupe with `/IPU-Law-Admission-2025.php` (§2.2) |
| 13 | `/b-tech-colleges-under-IP-university.php` | 272 | 1m 16s | 4 | Best engagement on site, very low CR (1.47%) — add CTA blocks |
| 14 | `/IP-University-management-quota-admission-eligibility-criteria.php` | 265 | 57s | 4 | Low CR on high-intent term — needs stronger CTA |
| 15 | `/IPU-B-Tech-admission-2025.php` | 255 | 23s | 0 | See §3.10 |
| 16 | `/mba-admission-ip-university.php` | 224 | 1m 10s | 3 | High engagement, very low CR |
| 17 | `/exploring-MAIT-and-MAIMS.php` | 167 | 45s | 4 | Fine |
| 18 | `/guide-to-bjmc-colleges-under-ip-university.php` | 159 | 30s | 3 | Fine |
| 19 | `/IPU-Law-Admission-2025.php` | 143 | 30s | 6 | Year-dupe (§2.2) |
| 20 | `/vips-pitampura-courses.php` | 120 | 36s | 1 | Fine — design reference for news-post template |

**Pattern:** Pages with 1min+ engagement and <2% CR (rows 13, 14, 16) share a root cause: users read the content, don't find a strong "what do I do next?" CTA. A shared "in-content conversion block" partial (`include/in-content-cta.php`) reused across these pages would lift aggregate CR meaningfully.

---

## Section 4 — Cross-site patterns

### 4.1 Internal linking

Not directly measurable from this data, but from the top-20 page list:
- `/blog.php` gets 294 views / 24s — low engagement. Blog index isn't doing its job of funneling readers to specific articles.
- `/news/` (new module) has only 19 views in 90 days — no internal links from high-traffic pages yet.

**Fix:** Add "Latest News" widget (reuse `news-popular-blogs.php` pattern) on the homepage below the fold. Cross-link between admission-guide pages and blog/news articles.

**SEO risk:** None. **Effort:** ~2 hrs. **Impact:** Low-medium.

### 4.2 CTA consistency

Observed inconsistencies:
- Homepage has 5 `tel:` links. Most top pages have 1. Scaling the sticky mobile button + mid-content "Call now" block to every top-20 page would be the single highest-leverage site-wide change.
- No WhatsApp button anywhere. Zero friction add — one floating button in `base-head.php` or `base-footer.php` reaches all 117 pages instantly.

**Fix:** Create `include/sticky-mobile-cta.php` (call + WhatsApp, mobile-only, fixed position). Include from `base-footer.php` so it's site-wide by default.

**SEO risk:** None. **Effort:** ~1 hr. **Impact:** **High** (likely the single biggest CR lift).

### 4.3 Form-to-phone friction

Forms collect name + phone + course. Post-submit → `thank-you.php` → expected outcome = "we'll call you back." But:
- Median call duration is 94s — real conversations, so callbacks presumably happen.
- 25.7% missed-call rate suggests inbound is overwhelming your handlers — a callback workflow from form submits may actually be underused.

**Fix (operational):** Audit the form-submit → callback workflow. Are all 1,538 form submits being called back within 15 minutes? Is there a callback SLA? If not, form submits are decaying leads.

**SEO risk:** None (operational). **Effort:** to scope separately. **Impact:** High (improves lead-close rate).

### 4.4 Trust signals

- `llms.txt` claims "10+ years, 5000+ students guided, 99% success rate." None of this appears visibly on the homepage (other than the broken "0+ Students Graduated" counter).
- No testimonials, no student success stories, no university logos with "we helped students get into X".

**Fix:** Add a single "Students We've Helped" strip on the homepage (3–5 real testimonials with photo + college they got into). Even without photos, quote + name + college is acceptable.

**SEO risk:** None. **Effort:** content-dependent. **Impact:** Medium (Ads traffic is cold, trust signals lift CR).

### 4.5 Remarketing audience hygiene

**Finding:** Near-zero returning users in GA (cohort retention all below 10% after day 1). Form submissions are stated to exist "for remarketing only" — but no returning users means:
- Either remarketing isn't running
- Or audiences are built but campaigns don't target them
- Or audiences are too small

**Fix:** In Google Ads → Audiences, verify:
1. Website visitors audience exists (all users in last 540 days)
2. Form submitters audience exists (fire event on `/thank-you.php` landing)
3. At least one campaign targets these audiences specifically (YouTube remarketing, Display, or RLSA on Search)

**SEO risk:** None. **Effort:** ~1 hr. **Impact:** High if currently missing.

---

## Section 5 — Off-page / operational

### 5.1 Missed-call rate (the single biggest lever in the business)

**Data:** 363 missed calls / 1,411 incoming = **25.7% missed rate** over 66 days.

**Context:** Avg connected call = 94s. Handlers: "Sonam ext. 12", "Nikhil Saini ext. 11". Department: Admission (91% of calls).

**Why it matters:** A missed call during business hours from a Delhi-NCR Gen Z prospect is a lost lead. At current scale, 363 missed calls × (say) 20% close rate × lifetime value = meaningful revenue.

**Fixes (ranked):**
1. **Auto-SMS on missed call:** "Sorry we missed your call. We'll ring you back in 10 min. — IPU Admission Guide" + WhatsApp link. Most IVR systems (Exotel, MyOperator, Knowlarity) offer this out of the box.
2. **Callback queue:** Missed-call log → auto-Queue into CRM → assign to available handler. You already have CRM routing (Bigin/Zoho referral seen in GA).
3. **More handlers during peak hours:** Peak appears Mon-Fri 10am-6pm IST (inferred from typical call patterns; confirm via Day/hour Ads card if available).
4. **WhatsApp fallback:** For any missed call, send a WhatsApp with the same intent-capture flow.

**SEO risk:** None (operational, off-site).
**Effort:** 1 day to wire + ongoing. **Impact:** **Highest single ROI in the entire audit.**

### 5.2 Google Ads: sitelink URL updates

Covered in §2.2 & §3.10. Restating for the roadmap: **5-minute, zero-risk, high-impact**. Do first.

### 5.3 Google Ads: call extensions + call conversions

Covered in §1.5. Enable call extensions, add calls-from-ads conversion action. Unlocks Smart Bidding on calls.

### 5.4 Google Ads: Quality Score

**Not shared yet.** To get: Ads → Keywords → Columns → Quality Score. Target: 7+ for all top keywords. Below 7 on a high-spend keyword = overpayment.

### 5.5 Intent-hijack strategy risk

**Finding:** Top Ads keywords include:
- "ipu ac in" (841 clicks) — literally the official university domain
- "ipu admissions nic in" (255 clicks) — literally the official admission portal
- "ggsipu" (brand name)

**Context:** Bidding on official IPU brand terms is extraordinarily profitable (43–53% CVR) but:
- **Legal risk:** IPU could file a trademark/brand complaint with Google. Ads get suspended.
- **Reputation risk:** IPU might publicly disavow ipu.co.in as "not us."
- **Cost risk:** If competitors start bidding similarly, CPC rises sharply.

**Fix:** No change today, but:
- Maintain clear "unofficial" disclaimer in footer: "Independent admission guidance site. Not affiliated with GGSIPU."
- Build organic alternatives ranking for these terms so you're not 100% dependent on Ads for brand searches (hard, long-term).
- Have a backup campaign plan if the official IPU ever complains.

**SEO risk:** Direct SEO not affected. **Effort:** ~30 min footer update. **Impact:** Insurance.

---

## Section 6 — Prioritized roadmap

Each finding tagged: **Impact** (H/M/L), **Effort** (hours), **SEO risk** (none/low/med).

### P0 — Do this week (critical leaks, zero/low risk)

| # | Finding | Impact | Effort | SEO risk |
|---|---|---|---|---|
| P0.1 | Fix broken "0+ Students Graduated" counters on homepage (§3.1) | H | 30m | None |
| P0.2 | Swap Ads sitelinks from `/IPU-B-Tech-admission-2025.php` → 2026 (§2.2, §3.10) | H | 5m | None |
| P0.3 | Wire click-to-call GA4 event (§1.1) | H | 30m | None |
| P0.4 | Enable Google Ads call extensions + call-conversion (§1.5) | H | 20m | None |
| P0.5 | Audit missed-call workflow; pick one automation (§5.1) | **H (highest ROI)** | 1 day | None |

### P1 — Do this month (meaningful lifts, low risk)

| # | Finding | Impact | Effort | SEO risk |
|---|---|---|---|---|
| P1.1 | Add sticky mobile WhatsApp + Call bar in `base-footer.php` (§4.2) | H | 1h | None |
| P1.2 | Add in-content CTA block to the 4 "high-eng / low-CR" pages (§3.11 pattern) | M | 2h | None |
| P1.3 | Canonical + banner on 2025 pages → 2026 (§2.2) | M | 30m | Low |
| P1.4 | Noindex dev/staging URLs + robots.txt block (§2.1) | M | 30m | Low |
| P1.5 | Redirect `/index.php` → `/` via `.htaccess` (§1.2) | M | 15m | Low |
| P1.6 | Audit remarketing audiences; confirm they're being used (§4.5) | H | 1h | None |
| P1.7 | Add testimonials strip to homepage (§4.4) | M | 2h + content | None |
| P1.8 | Filter bot geo from GA (§1.4) | L | 15m | None |

### P2 — Do this quarter (compounding improvements)

| # | Finding | Impact | Effort | SEO risk |
|---|---|---|---|---|
| P2.1 | Rework homepage CTA density; demote secondary CTAs (§3.1) | M | 4h | None |
| P2.2 | Convert `/thank-you.php` into secondary conversion step (§3.5) | M | 1h | None |
| P2.3 | Update stuffed title tags on specific pages (§3.8 pattern) | M | 2h | Low |
| P2.4 | Internal-linking pass: news ↔ top guides ↔ blog (§4.1) | L | 2h | None |
| P2.5 | Strip trailing-slash URL variants (§1.3) | L | 10m | Low |
| P2.6 | Long-tail page batch SEO-tag scan (§2.5) | L | 3h | None |
| P2.7 | Add "unofficial" disclaimer in footer (§5.5) | L | 30m | None |

### P3 — Later / evaluate (larger scope, higher risk)

| # | Finding | Impact | Effort | SEO risk |
|---|---|---|---|---|
| P3.1 | Migrate year-specific pages to year-agnostic (`2026 → ""`) ahead of 2027 | H | 1 wk | **Med** — needs proper 301 plan |
| P3.2 | Form-submit → callback workflow automation (§4.3) | H | 1 wk | None |
| P3.3 | Content-rewrite pages where engagement < 30s and CR < 1% | M | 1–2 wks | Med |

---

## Next step

Turn the P0 items into implementation plans (one plan per item), execute them in order.

The P1 items can be batched or split depending on capacity.

P2+ are nice-to-have — revisit after P0/P1 shows measurable lift in call volume / CR.
