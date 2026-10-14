# Google Ads performance audit: ipu.co.in (landing pages, tracking, structure)

Date: 2026-10-14 (off-season; traffic ~60-70 clicks/day, next season Feb-Jul 2027)
Scope: read-only. Nothing under `website_download/` was edited; nothing was changed in GTM, GA4 or Google Ads. Companion data: `ads-pages.csv` (one row per page, 44 pages, all rubric scores and measurements).

---

## 1. Executive summary

1. **Conversion tracking is the biggest risk, not page speed.** The published GTM container (v22, read from the public `gtm.js`) fires the Ads form conversion on *any* view of `/thank-you.php` (event `gtm.dom` + path contains `/thank-you.php`). The `form_submission` event and the `?src=submit` gate in the code are never used by any GTM trigger, so honeypot, 3-second, cooldown and 7-day-dedup redirects (all land on `/thank-you.php`), reloads and direct visits all count. This fits the "39-107% conversion rate" anomaly already noted in the keyword-map doc.
2. **Phone conversion = every tel: tap**, once per tap, with no duration filter (`phone_click` event -> Ads label `rtNZ...`). GA4 additionally receives two events per tap (`phone_click` and `phone_clicks`). Website call conversions (forwarding number) are paused in the container; enhanced conversions are not active (the page pushes an `enhanced_conversion` event that no tag listens to, and the phone is hashed without `+91`).
3. **The placement tracking you built is not being collected.** `cta_call` / `cta_enquire` (with `data-cta-src`) are pushed to `dataLayer` but no GTM trigger exists for them, so hero vs sticky vs row performance is unmeasured. 248 of 373 tel: links on the 44 pages carry no `data-cta-src` at all.
4. **Two form bugs silently lose or corrupt leads.** (a) A phone pasted/autofilled as `+919899991342` or `919899991342` is rewritten by `app.js` to `9198999913` (first 10 digits), which passes the server regex, so the lead arrives with a wrong number (reproduced in headless Chrome). (b) `sendemail.php` drops a submission silently (shows thank-you, sends nothing) if the browser clock is ahead of the server by any amount, because `time() - form_loaded_at` goes negative and is treated as "faster than 3 seconds". Server-side validation errors redirect to `/?error=phone|fields`, which no page reads: the visitor lands on the homepage with no message and loses the form.
5. **Landing-page structure splits 24 thin-banner legacy pages from 17 unified pages.** Among 44 candidate pages, the 24 legacy pages (thin banner, 2 H1s, no call button in the first screen, mobile form median ~5,600 px down) carry 64% of the GSC clicks; the unified `ipu-page-hero` pages score 3.0-4.5/5, the legacy ones 2.5/5. Completing Phase 4 of the template-unification plan is the single largest landing-page win.
6. **A mobile layout defect on 10 unified pages:** `bundle.min.css` forces `.header-area{position:fixed!important}` at <=767 px, the hero starts at y=0 with 48 px top padding under a 56 px header, so the first 8 px of the H1 is hidden (confirmed on live, `btech-management-quota-ipu.php`).
7. **Page weight and speed are already decent on the live site** (HTML 12-18 KB brotli, one 28 KB render-blocking CSS file, lab FCP/LCP 1.0-1.1 s on throttled mobile with third parties blocked, CLS 0). The remaining speed items are small: Google Fonts despite a "self-hosted" comment, 232 KB uncompressed Bootstrap CSS, unused 400w/800w image variants. GTM/GA4/Clarity weight and field data (CrUX) could not be measured.
8. **Honesty/LPX transparency:** the "not affiliated with GGSIPU" disclosure sits only in the footer (6,000-13,000 px down on mobile); the trust bar shows university facts ("100,000+ students graduated", "NAAC A++", "Est. 1998") in the independent site's own voice; the homepage says "IPU Admission 2026 open, limited seats, apply early" (stale in October, urgency unverified); the thank-you page promises a call "within 2 hours" and "Seat Secured". Fix before the ads run, because the helpline and admission terms invite an "is this official?" reading.
9. **Structure for Feb-Jul 2027:** six Search-only campaigns (Management quota, Helpline, Admission by course, College-specific, Counselling/rounds capped, Fees and cutoff capped) plus an exact-match own-brand campaign; do not bid on bare `ipu` / `ggsipu` (1.04M impressions, 0.19% CTR, navigational). Start on manual/max-clicks until the conversions are clean, then tCPA on form + call>=30 s.
10. **Plan:** GTM and form fixes in the next 2 weeks (no site deploy needed for the GTM part), CSS/CTA/disclosure fixes by mid-November, landing pages and Ads Editor build by mid-December, test calls/forms and soft launch in January, all before February. The homepage, USAR and ADGIPS remain on the 5 January 2027 hold.

**Method and limits in one paragraph.** Local copy served with `php -S 127.0.0.1:8023` and measured with Playwright driving `/Applications/Google Chrome.app` (mobile = 390x844 DPR2 touch emulation, Slow-4G + 4x CPU throttle; desktop = 1280x800), third-party hosts blocked so no analytics hits were sent. A comparison against live (44 pages) showed identical page structure (hero type and H1 count match on all 44; the only difference is that local `base-head.php` carries unified-template CSS that is not deployed yet). Live: about 100 requests in total (page fetches, 4 throttled page loads, header checks), well under the 250 cap, although each browser page load issues about 6 sub-requests in a burst. Lighthouse is not installed (`npx --no-install lighthouse` fails), so all speed numbers are proxies. I had no access to GTM, GA4 or Google Ads; section 3.4 lists what the owner must verify there.

---

## 2. Rubric (0-5) and how to read the page table

Each component is scored 0-1 in 0.25 steps; the total is their sum. Scores come from the mobile measurements (78% of clicks) unless stated.

| Component | 1.0 means | Deductions (0.25 each unless stated) |
|---|---|---|
| Speed / stability proxy | gzip HTML <= 20 KB, no heavy body image, no nested document | gzip HTML > 20 KB; body hero image served without `srcset`; DOM > 1,800 nodes; nested `<html>/<head>` (parser recovery) |
| Phone CTA | tap-sized (>= 44 px) call button in the first 844 px, besides the sticky bar | 0.5 if only the sticky bar (or a < 44 px inline link) is in the first screen |
| Form access (mobile) | form <= 1,700 px down | 0.5 if <= 3,500 px; 0.25 beyond (sticky "Enquire" still jumps to it); homepage -0.25 for 14 px inputs (iOS zoom) |
| Message match / hero clarity | one H1, H1 clear of the header, hero has support line + call button | more than one H1; H1 under the header; thin banner or no hero call button; "2025" in title/H1 for a 2026-27 season |
| Trust and honesty | disclosure + trust element in the first two screens | baseline 0.25 for a footer-only disclosure; +0.5 trust element within 1,700 px; +0.25 disclosure within 1,700 px (no page has it) |

Caveats: the message-match score partly overlaps the phone CTA score (a thin banner is both); GSC clicks are the 16-month organic clicks to the URL, used only as an indicator of which URLs people already arrive on. I do not know the account's actual final URLs. The "Ads role" column is my proposal.

### Page ranking (44 candidate pages, best first; ties broken by GSC clicks)

| Rank | Page | Ads role | GSC clicks (16 mo) | Speed | Phone CTA | Form | Message | Trust | Total /5 | Main issues (measured) |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | `/mba-admission-ip-university.php` | Admission MBA | 294 | 1.0 | 1.0 | 1.0 | 0.75 | 0.75 | **4.5** | H1 top 8px under fixed header |
| 2 | `/btech-management-quota-ipu.php` | Management quota B.Tech | 147 | 1.0 | 1.0 | 1.0 | 0.75 | 0.75 | **4.5** | H1 top 8px under fixed header |
| 3 | `/ballb-management-quota-ipu.php` | Management quota BA LLB | 91 | 1.0 | 1.0 | 1.0 | 0.75 | 0.75 | **4.5** | H1 top 8px under fixed header |
| 4 | `/mba-management-quota-ipu.php` | Management quota MBA | 4 | 1.0 | 1.0 | 1.0 | 0.75 | 0.75 | **4.5** | H1 top 8px under fixed header |
| 5 | `/bba-management-quota-ipu.php` | Management quota BBA | 148 | 0.75 | 1.0 | 1.0 | 0.75 | 0.75 | **4.25** | H1 top 8px under fixed header; nested html/head |
| 6 | `/comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php` | Admission BBA | 1,003 | 0.75 | 1.0 | 1.0 | 1.0 | 0.25 | **4.0** | hero image served at 1000w to mobile |
| 7 | `/BPIT.php` | College-specific (BPIT) | 271 | 0.75 | 1.0 | 0.5 | 1.0 | 0.75 | **4.0** | form 1,716px down; hero image served at 1000w to mobile |
| 8 | `/cuet-admission-ipu.php` | CUET hub | 185 | 0.75 | 1.0 | 1.0 | 1.0 | 0.25 | **4.0** | hero image served at 1000w to mobile |
| 9 | `/ipu-admission-guide.php` | Admission generic | 39 | 1.0 | 1.0 | 0.5 | 0.75 | 0.75 | **4.0** | H1 top 8px under fixed header; form 3,242px down |
| 10 | `/IPU-Law-Admission.php` | Admission Law | 1,045 | 0.75 | 1.0 | 1.0 | 0.75 | 0.25 | **3.75** | H1 top 8px under fixed header; hero image served at 1000w to mobile |
| 11 | `/ipu-ba-llb-cutoff.php` | Cutoff Law | 707 | 1.0 | 1.0 | 0.25 | 0.75 | 0.75 | **3.75** | form 5,384px down on mobile; 2025 in title/H1 |
| 12 | `/BVP.php` | College-specific (BVP) | 128 | 0.75 | 1.0 | 0.25 | 1.0 | 0.75 | **3.75** | form 7,299px down on mobile; hero image served at 1000w to mobile |
| 13 | `/GGSIPU-counselling-for-B-Tech-admission.php` | Counselling / rounds | 4,846 | 0.75 | 1.0 | 0.25 | 0.75 | 0.75 | **3.5** | H1 top 8px under fixed header; form 5,913px down on mobile; nested html/head |
| 14 | `/law-3-year-admission-ipu.php` | Admission Law 3-year | 339 | 0.75 | 1.0 | 0.5 | 1.0 | 0.25 | **3.5** | form 1,811px down; hero image served at 1000w to mobile |
| 15 | `/ipu-btech-via-cuet.php` | CUET / B.Tech pathway | 1,562 | 0.75 | 1.0 | 0.25 | 1.0 | 0.25 | **3.25** | form 6,858px down on mobile; nested html/head |
| 16 | `/` | Brand/generic admission (hold list until 5 Jan 2027 for any edit) | 2,514 | 0.75 | 0.5 | 0.75 | 0.75 | 0.25 | **3.0** | no tap-sized call button in first screen (sticky bar only); 14px form inputs (iOS zoom); stale "2026 open, limited seats" banner |
| 17 | `/IPU-B-Tech-admission-2026.php` | Admission B.Tech | 1,059 | 0.75 | 1.0 | 0.25 | 0.75 | 0.25 | **3.0** | H1 top 8px under fixed header; form 6,715px down on mobile; hero image served at 1000w to mobile |
| 18 | `/usict-admission.php` | College-specific (USICT) | 637 | 1.0 | 0.5 | 0.25 | 0.5 | 0.75 | **3.0** | no tap-sized call button in first screen (sticky bar only); form 4,944px down on mobile; 2 H1s; thin banner hero |
| 19 | `/IP-University-management-quota-admission-eligibility-criteria.php` | Management quota hub | 473 | 0.75 | 1.0 | 0.25 | 0.75 | 0.25 | **3.0** | H1 top 8px under fixed header; form 6,861px down on mobile; hero image served at 1000w to mobile |
| 20 | `/vips-admission.php` | College-specific (VIPS) | 1,498 | 1.0 | 0.5 | 0.25 | 0.75 | 0.25 | **2.75** | no tap-sized call button in first screen (sticky bar only); form 7,023px down on mobile; thin banner hero |
| 21 | `/ipu-bba-cutoff.php` | Cutoff BBA | 1,178 | 0.5 | 1.0 | 0.25 | 0.75 | 0.25 | **2.75** | form 7,270px down on mobile; 2025 in title/H1 |
| 22 | `/ipu-colleges-list.php` | Generic college list | 8,834 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 7,875px down on mobile; 2 H1s; thin banner hero |
| 23 | `/top-ipu-colleges.php` | Generic college list | 4,482 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 8,952px down on mobile; 2 H1s; thin banner hero |
| 24 | `/ipu-counselling.php` | Counselling / rounds | 4,013 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 7,246px down on mobile; 2 H1s; thin banner hero |
| 25 | `/ipu-helpline-contact-number.php` | Helpline / contact | 3,418 | 0.75 | 0.5 | 0.25 | 0.75 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 3,603px down on mobile; nested html/head |
| 26 | `/ipu-fees-structure.php` | Fees (generic) | 1,500 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,240px down on mobile; 2 H1s; thin banner hero |
| 27 | `/top-bcom-colleges-ipu.php` | College list B.Com | 1,461 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 6,450px down on mobile; 2 H1s; thin banner hero |
| 28 | `/top-law-colleges-ipu.php` | College list Law | 1,281 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 8,986px down on mobile; 2 H1s; thin banner hero |
| 29 | `/top-mca-colleges-ipu.php` | College list MCA | 1,222 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 6,524px down on mobile; 2 H1s; thin banner hero |
| 30 | `/ipu-btech-cutoff-2025.php` | Cutoff B.Tech | 1,097 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 9,204px down on mobile; thin banner hero; 2025 in title/H1 |
| 31 | `/top-btech-colleges-delhi.php` | College list B.Tech | 1,079 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 4,141px down on mobile; 2 H1s; thin banner hero |
| 32 | `/mait-delhi-fees-courses-placements.php` | College-specific fees (MAIT) | 994 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,926px down on mobile; 2 H1s; thin banner hero |
| 33 | `/top-bba-colleges-ipu.php` | College list BBA | 849 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 7,443px down on mobile; 2 H1s; thin banner hero |
| 34 | `/ipu-cutoff-analysis.php` | Cutoff hub | 837 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 4,953px down on mobile; thin banner hero; 2025 in title/H1 |
| 35 | `/top-mba-colleges-ipu.php` | College list MBA | 821 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 8,160px down on mobile; 2 H1s; thin banner hero |
| 36 | `/top-bca-colleges-ipu.php` | College list BCA | 732 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 6,624px down on mobile; 2 H1s; thin banner hero |
| 37 | `/bcom-admission-ipu.php` | Admission B.Com | 634 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,782px down on mobile; 2 H1s; thin banner hero |
| 38 | `/mca-admission-ipu.php` | Admission MCA | 630 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 6,957px down on mobile; 2 H1s; thin banner hero |
| 39 | `/maims-delhi-fees-courses.php` | College-specific fees (MAIMS) | 606 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,201px down on mobile; 2 H1s; thin banner hero |
| 40 | `/mait-admission.php` | College-specific (MAIT) | 584 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,838px down on mobile; 2 H1s; thin banner hero |
| 41 | `/bca-admission-ipu.php` | Admission BCA | 566 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 6,456px down on mobile; 2 H1s; thin banner hero |
| 42 | `/usar-admission.php` | College-specific (USAR; hold list) | 430 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 4,594px down on mobile; 2 H1s; thin banner hero |
| 43 | `/msit-admission.php` | College-specific (MSIT) | 420 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 5,030px down on mobile; 2 H1s; thin banner hero |
| 44 | `/college-admission-delhi.php` | Admission generic (Delhi) | 3 | 1.0 | 0.5 | 0.25 | 0.5 | 0.25 | **2.5** | no tap-sized call button in first screen (sticky bar only); form 3,708px down on mobile; 2 H1s; thin banner hero |

Measured facts behind the table (mobile 390 px unless stated; full values in `ads-pages.csv`):

- **Hero type.** Unified `ipu-page-hero` (hero call button + optional Enquire + sidebar form after the intro): 17 pages. Legacy `hero-section hero-compact` thin banner: 24 pages (H1 only, 175-229 px tall, call button absent). Two outliers: `ipu-helpline-contact-number.php` (no hero element at all, H1 on a flat navy band, nested `<html><head>` twice, 186 KB raw HTML of which ~45% is indentation whitespace) and `ipu-btech-via-cuet.php` (own `container-fluid hero-s...` hero with a 283x52 call button).
- **Call button.** The sticky bar (256x50, orange, always present <= 768 px) is the only call target in the first screen on 26 of 44 pages. Inline "9899991342" links in body text measure 83x27 px (below the 44-48 px guideline). Desktop always has the header "Call now" (118x39) plus the sidebar number block.
- **Form.** Every page has one form (name, phone, optional email, course select; 4 visible fields + honeypot), one `id="enquiry-form"`, inputs 46-50 px tall at 16 px (homepage: 14 px, so iOS Safari zooms on focus). The mobile form position is the weak point: median 5,583 px down, 32 pages beyond 3,500 px, only 9 within 1,700 px (8 unified pages and the homepage). On desktop the form is in the first screen on legacy pages (y~526-600) and just below it on unified hero pages (y~870-905, partly visible).
- **Phone input.** `type=tel`, `inputmode=tel`, `autocomplete=tel`, `pattern`, `maxlength=10`, input handler strips non-digits and slices to 10 (see executive summary point 4 and fix F3). The form is `novalidate`: validation is purely the `.is-invalid` CSS class plus server-side checks.
- **Sticky bar.** Call (flex 7) + Enquire (flex 3), height 71 px, reserves `body` padding (CLS 0 measured). The fixed layer covers 15% of the viewport on all pages (BPIT: 30% because of an extra fixed element). Label "Call a counsellor" shows no number. The out-of-hours variant in the CTA spec (Wave 2b) is not in `base-nav.php`.
- **Popups / interstitials:** none found on the 44 pages (no modal/exit-intent; `fixed` layers are header, sticky bar and scroll-to-top only).
- **H1.** 21 of 44 pages carry two `<h1>` (legacy: banner H1 plus a second, much larger body H1 at ~3 rem). 10 unified pages have the H1 starting at y=48 under the 56 px fixed header (listed in the CSV: `h1_covered_by_fixed_header`).
- **Weight (live, brotli):** HTML 12.3 KB (btech mgmt quota), `bootstrap5.min.css` 28.3 KB (232,803 B raw, render-blocking), `bundle.min.css` 12.2 KB (non-blocking `media=print` swap), `bootstrap.bundle.min.js` 22.9 KB (defer), `app.js` 2.3 KB (defer), one 41 KB `counter-bg-2.jpg` on some pages. Lab on live, Slow-4G + CPU x4, third parties blocked: FCP = LCP = 1.02-1.11 s, CLS 0, TTFB 0.41-0.50 s (4 pages). The LCP element is a text block on 36 of 44 pages and the 21-34 KB webp hero image on 8; on mobile that image is the 1000w file although 400w/800w variants exist.
- **Fonts:** `base-head.php` loads Inter from `fonts.googleapis.com` (preload + `onload` swap) under a comment that says "Self-hosted Fonts"; two extra origins (googleapis, gstatic) and a font swap after first paint.
- **Trust elements:** a trust bar (100,000+ students graduated, 60+ affiliated colleges, 15+ courses, Est. 1998, NAAC A++) appears on unified pages at ~390-470 px; legacy pages have none near the top. The homepage hero chips read "NAAC A++ Accredited / Government University / 60+ Colleges".
- **Messaging.** Titles/H1s still say 2026 (pages for the Feb-Jul 2027 season will need 2027 wording; see fix F18, class C).

Screenshots (mobile first screens, 390 px, header + hero + sticky bar): `ads-shots-mobile-1.png` (home, helpline, fees, management-quota hub, B.Tech management quota), `ads-shots-mobile-2.png` (colleges list, top colleges, VIPS, MAIT, B.Tech cutoff), `ads-shots-mobile-3.png` (counselling x2, B.Tech 2026, BBA cutoff, BPIT); desktop 1280: `ads-shots-desktop-1.png`.

---

## 3. Conversion tracking audit (from the code and the published container)

### 3.1 What the code does

| Surface | File | `data-cta-src` | dataLayer events pushed |
|---|---|---|---|
| Desktop header "Call now" (>=992 px) | `include/base-nav.php` | `header` | `phone_click` + `cta_call` |
| Mobile sticky "Call a counsellor" (<=768 px) | `base-nav.php` | `sticky` | `phone_click` + `cta_call` |
| Mobile sticky "Enquire" (`#enquiry-form`) | `base-nav.php` | `sticky-enquire` | `cta_enquire` only (not a tel: link) |
| Hero call button (page-hero, hero-banner, cta-strip) | `components/page-hero.php`, `hero-banner.php`, `cta-strip.php` | `hero` | `phone_click` + `cta_call` |
| Hero "Enquire" (opt-in) | `page-hero.php` | `hero-enquire` | `cta_enquire` |
| College-block call buttons (JS-injected on `.college-block`) | `base-nav.php` | `row` (spec says `row-<college>`; code does not do it) | `phone_click` + `cta_call` |
| Sidebar phone number, footer number, homepage hero button, in-body and FAQ links, thank-you page | `sidebar-enquiry.php`, `base-footer.php`, `index.php`, page bodies | none | `phone_click` only |

- `phone_click` is pushed by a bubbling `click` listener in `base-head.php` on `a[href^="tel:"]`; because it is delegated, JS-created tel: links (the `.cb-call-btn` buttons) are covered.
- `cta_call`/`cta_enquire` come from a capturing delegated listener in `base-nav.php`; `window.gtag` is not defined (GTM only), so the `dataLayer.push` branch runs.
- Counts on the 44 pages: 373 tel: links, 248 (66%) without `data-cta-src`; 99 plain-text occurrences of the number on 39 pages that are not wrapped in a tel: link (not tappable on Android; iOS Safari auto-links them, and that tap raises no DOM click so it is never tracked; there is no `format-detection` meta).
- GTM loader appears once per page (verified on all 44 pages plus `thank-you.php`, `blog.php`, `news/`, `course/`), and one `phone_click` listener per page: no double listeners. `thank-you.php` has two GTM `<noscript>` iframes (harmless) and a nested `<html><head>` (it includes `base-head.php` then declares its own).
- Form path: all forms POST to `/sendemail.php` (fields `name`, `phone`, `email`, `course`, `page_url`, `form_loaded_at`, honeypot `website`). `sendemail.php` emails two addresses, posts the lead to a Google Apps Script (Google Sheet), writes `include/.private/leads-fallback.jsonl` when email or Sheet delivery fails, stores hashed phone for 7-day dedup, and redirects to `/thank-you.php?src=submit` only for genuine submissions. Rejections (honeypot, <3 s, 5-minute cooldown, per-IP 5/10 min, same phone in session/cookie/7 days) redirect to `/thank-you.php` **without** `src=submit`; validation failures redirect to `/?error=fields|phone`.
- `thank-you.php` pushes `{event:'form_submission'}` only when `src=submit`, then (once per session) `{event:'enhanced_conversion', enhanced_conversion_user_data:{email, phone_number}, send_to:'AW-10900888879/IVcxCLiB87IbEK-6-c0o'}` with SHA-256 of the raw 10-digit phone (no `+91`).
- `page_url` (hidden field = server-side `REQUEST_URI` of the page holding the form) is included in the email `Source:` line and the Sheet `message` field. It carries `gclid`/UTMs only if the visitor submits on the same URL they landed on. No code anywhere stores `gclid`.
- `form_loaded_at` is stamped by `app.js` from the browser clock (`Date.now()/1000`), and `sendemail.php` prefers it over the session value.

### 3.2 What the published GTM container (version 22) actually does

Read from `https://www.googletagmanager.com/gtm.js?id=GTM-5GXCN7Z` (public; reflects the live published version, not any draft). Tags and the rules that fire them:

| Tag | Type | Fires on | Comment |
|---|---|---|---|
| Conversion Linker | `gclidw` (URL passthrough on) | All pages (`gtm.js`) | good; `_gcl_aw` cookie holds the gclid (usable for lead attribution, see fixes) |
| Google tag G-9VS3CTJ8SV | `googtag` | `gtm.init` | GA4 config |
| GA4 event `phone_click` | `gaawe` | custom event `phone_click` | one per tap |
| GA4 event `phone_clicks` (plural) | `gaawe` | any link click whose URL **contains the text "tel"** | duplicate per tap; also matches any URL containing "tel" |
| Ads conversion `10900888879 / rtNZCIPY8rIbEK-6-c0o` | `awct` | custom event `phone_click` | phone-tap conversion, every tap |
| Ads conversion `10900888879 / IVcxCLiB87IbEK-6-c0o` | `awct` | `gtm.dom` when **page path contains `/thank-you.php`** | form conversion; enhanced-conversions checkbox is **off** |
| Clarity (project vhojibqr0m) | custom template | All pages | session recording |
| 3 link-click listeners, 1 form-submit listener | `lcl`, `fsl` | All pages | only the `tel`-contains one feeds an active tag; form-submit feeds a paused tag |
| 6 paused tags | `awcc` x3 (Calls-from-website / forwarding number), `awct` x2, `awud` | none | **website call conversions are paused**, as is user-data |

Not present in the container: any trigger for `form_submission`, `enhanced_conversion`, `cta_call` or `cta_enquire`; any Meta Pixel tag (the code comment lists "Meta Pixel, Clarity, WCM"); any consent-mode setup. A consequence: `dataLayer` pushes for `form_submission`, `enhanced_conversion`, `cta_call`, `cta_enquire` currently do nothing.

### 3.3 Likely double counts and misses

| # | Finding | Effect | Confidence |
|---|---|---|---|
| T1 | Form conversion fires on `gtm.dom` + `/thank-you.php` path, ignoring `?src=submit` | Counts every redirect to `/thank-you.php`: honeypot hits, <3 s, cooldown, per-IP and 7-day-dedup redirects, reloads of the thank-you page and direct/bookmarked visits (the page is `noindex` and robots-disallowed, but reachable). Inflates "leads" and teaches smart bidding the wrong thing. Only visitors with a gclid cookie are attributed to Ads, so the damage is mostly duplicates and reloads | High (read from container) |
| T2 | Phone conversion = every tel: tap, no session or duration filter | A tap that never connects, a second tap in the same visit and an accidental tap are all conversions; no call-length data | High |
| T3 | GA4 gets `phone_click` **and** `phone_clicks` per tap | If both are marked key events or imported into Ads, taps are double counted | High (container); which are key events is unknown |
| T4 | `cta_call` / `cta_enquire` (placement) have no tag | No hero vs sticky vs row data; the CTA-system success metric ("lift in `cta_call`") cannot be read | High |
| T5 | 66% of tel: links lack `data-cta-src` | Even after T4 is fixed, placement coverage would be 34% | High |
| T6 | Plain-text numbers (99 on 39 pages) | Not tappable on Android; tapped via iOS auto-link = invisible to JS | High (count); iOS behaviour needs a device test |
| T7 | Enhanced conversions off in the tag; `enhanced_conversion` event has no listener; phone hashed without `+91` (E.164 required before hashing) | No EC match uplift; if later switched on as-is it would match poorly | High |
| T8 | Website call conversions (forwarding number) paused | Real answered calls from the website are not measured by Google | High |
| T9 | Hash anchors (`#enquiry-form`) | Fire `gtm.linkClick` but no tag; fine. The sticky Enquire is only measurable once T4 is wired | Medium |
| T10 | No gclid stored with the lead | Cannot do offline "qualified lead" imports or connect an Ads click to a lead in the Sheet/email | High |
| T11 | Form validation failures and silent drops invisible | The `?error=` redirects and silent thank-you paths are not tracked; the clock-skew drop (fix F4) looks like a success page view and is counted by T1 | High (code) |
| T12 | `PHPSESSID` `Set-Cookie` on every cacheable page (`cache-control: public, max-age=1800`) | Prevents shared/CDN public caching of landing pages; harmless for a browser-only audience | Medium |

Offline measurement without a CRM (respecting that site and CRM are deliberately disconnected): the lead email and Google Sheet row are the system of record. Add a `gclid` (from the `_gcl_aw` cookie that the Conversion Linker already writes) and the final-URL-suffix UTMs to the Sheet row, mark qualified/enrolled rows monthly, and upload them as offline conversions by file. The existing `leads-fallback.jsonl`, `phone_dedup.log` and mail logs give a server-side count of genuine submissions to reconcile against Ads (every `src=submit` redirect corresponds to one mail+Sheet attempt).

### 3.4 Owner checklist (GTM / GA4 / Google Ads; I could not access any of these)

**GTM (container GTM-5GXCN7Z, compare the workspace with published v22)**
1. Open Tags: confirm the Ads conversion `IVcxCLiB87IbEK-6-c0o` trigger is "Page View/DOM Ready, path contains /thank-you.php". Expected per my reading; if instead you see a `form_submission` custom-event trigger the published version differs from what I read.
2. Preview mode: visit `/thank-you.php` directly (no src) and watch whether that tag fires. If it does, fix as below.
3. Create Custom Event trigger `form_submission`; attach the form conversion to it (and the GA4 `generate_lead`/`form_submit` event); detach the path trigger. Add `once per page` and keep the one-time server guard (fix F4).
4. Pause or delete GA4 tag `phone_clicks` (plural) or at least confirm it is not a key event.
5. Create triggers on custom events `cta_call` and `cta_enquire`, Data Layer Variables `cta_src` and `page_path`, and GA4 event tags carrying them (analysis only, not conversions).
6. Decide whether to mark the phone-tap Ads conversion Secondary (observation) once a call-duration conversion exists (see Ads, below).
7. Check whether tag 'Meta Pixel' was intentionally removed (code comments still mention it); confirm WCM/call tags are meant to be paused.
8. Enhanced conversions: open the Ads conversion tag, enable "Include user-provided data", create a User-Provided Data variable (manual): email and phone in E.164 (`+91...`), fed from a new dataLayer key. Do not enable until the E.164 normalisation exists (fix F9).
9. Publish with a version note; keep v22 for rollback.

**GA4 (G-9VS3CTJ8SV)**
10. Admin > Events: which of `phone_click`, `phone_clicks`, `form_submission`, `generate_lead`, `cta_call` exist and which are key events. Only one of the two phone events should be a key event.
11. Compare 28 days of `phone_click` vs Ads phone conversions vs actual answered calls on the handset (call log). Expect Ads and GA4 to roughly agree and both to exceed real calls.
12. Check Reports > Pages: sessions and key events on `/thank-you.php` vs distinct `?src=submit`. Many `/thank-you.php` views without `src=submit` confirms T1.
13. Explorations: landing page + device for `phone_click` rate per session (useful even before T4 is fixed).
14. Check the data filter / referral exclusions for the spam referrals and any traffic with `?q=` or `?u=` landing pages.

**Google Ads**
15. Goals > Conversions: list every action, its source (Website/GA4 import/Calls from ads/Phone call clicks/Import), counting (One vs Every), "Include in conversions" (Primary vs Secondary), attribution model and window. Look for the same event counted by two actions (GTM tag + GA4 import) and for "Calls from ads" assets counted as a primary action.
16. Set **One** (not Every) for the form action; make Primary: form submit and call >= 30 s; make phone-tap Secondary.
17. Campaign settings: auto-tagging on; **final URL suffix** at account level, e.g. `utm_source=google&utm_medium=cpc&utm_campaign={campaignid}&utm_term={keyword}&utm_content={adgroupid}&matchtype={matchtype}`; no tracking template that breaks the redirect; use final URLs on the apex `https://ipu.co.in/` (www 301-redirects, GSC still shows 30,380 www impressions).
18. Assets: do call assets with call reporting exist? Which number do they dial; are calls from ads conversions enabled (minimum duration) and are they Primary?
19. Reports: search terms (all time), conversion by campaign/device/hour/day, geo (user location, not interest), landing page report (mobile speed score, mobile-friendly click rate), Quality Score columns (expected CTR, ad relevance, landing page experience) per keyword, auction insights.
20. Check ad copy and assets for any "official", "authorised", "guaranteed", "100% admission", "limited seats", "government" wording; check disapprovals/limited-status messages in Policy manager.
21. Locations: "Presence" only (not "presence or interest"); India only (GSC: India 99.1% of clicks; US 89 and UAE 71 clicks are noise).
22. Network: Search only; **Search Partners and Display expansion switched off** unless they show profitable data (owner rule: Search Network only).
23. Compare the Ads campaign list with the keyword-map doc: `Admission Helpline` (about Rs 4.98 lakh of Rs 10.4 lakh, 96% mobile), `Phone Call`, `Phone Call - Btech`, `Lead Search 2026 for MBA PDGM LAW`.

**Physical tests (10 minutes, do before and after each change)**
24. On an Android phone from a test ad click (or `?gclid=TEST`): tap sticky Call, hero Call, a body link; check GTM Preview for `phone_click`, `cta_call`, and exactly one Ads tag per tap.
25. Submit the form with `+919899991342`, with `9899991342`, with a number pasted with spaces; confirm the Sheet/email shows `9899991342`.
26. Set the phone clock 10 minutes ahead; submit; confirm the lead arrives (today it will not).
27. Submit the same number twice within 5 minutes and once with an invalid number; note what the visitor sees and whether a conversion fires.
28. iPhone Safari: tap an unlinked plain-text number; confirm whether GTM sees anything.

---

## 4. Demand and intent clusters (GSC as the proxy)

Source: `seo/baselines/2026-09-30-gsc-export/search/Queries.csv` (top 1,000 queries: 15,722 clicks and 1,848,219 impressions, i.e. about 23% of the ~67,300 clicks and 35% of the ~5.3M impressions; the rest is anonymised or long tail) and `Pages.csv`, `Devices.csv`, `Countries.csv`, `Chart.csv`. These are **organic** numbers: they show what people search and which pages answer, not Ads clicks, cost or conversion. Clusters were assigned by first matching rule in the order helpline, management quota, fees, cutoff/rank, counselling/registration, CUET/exam, generic college lists, named college, admission, news, with bare institution names under Brand. A query like "mait fees" therefore counts as Fees, not College-specific.

| Cluster | Queries | Impressions | Share of impr. | Clicks | Share of clicks | CTR | Avg position | Top queries |
|---|---|---|---|---|---|---|---|---|
| Brand / navigational (bare ipu, ggsipu, ip university...) | 12 | 1,036,218 | 56.1% | 1,993 | 12.7% | 0.19% | 8.9 | ipu, ggsipu, ip university, ipu university, guru gobind singh indraprastha university |
| Counselling / rounds / dates / registration | 174 | 265,334 | 14.4% | 2,524 | 16.1% | 0.95% | 7.0 | ipu counselling, ipu btech counselling 2026 date, ipu counselling 2026, ggsipu counselling |
| College-specific (named institution) | 73 | 136,298 | 7.4% | 736 | 4.7% | 0.54% | 7.6 | mait, usar, vips, fimt, usict |
| Generic college lists / top colleges | 287 | 126,169 | 6.8% | 4,576 | 29.1% | 3.63% | 5.4 | ipu college list, ipu colleges, ipu top colleges, colleges under ipu |
| Fees | 140 | 77,019 | 4.2% | 1,290 | 8.2% | 1.67% | 6.0 | usict mca fees, mait fees, vips bba fees, ipu fees structure 2026 |
| Helpline / contact | 83 | 57,720 | 3.1% | 2,566 | 16.3% | 4.45% | 5.9 | ipu helpline number, ggsipu helpline number, ipu contact number, ggsipu admission helpline number |
| Cutoff / rank | 99 | 57,398 | 3.1% | 1,066 | 6.8% | 1.86% | 6.4 | ipu rank list, gtb4cec cutoff, ipu college ranking, ipu cutoff for btech cse |
| Admission (course-specific / generic) | 56 | 49,802 | 2.7% | 400 | 2.5% | 0.80% | 7.7 | ipu admission, ipu admission portal 2026, ipu btech, ip university admission 2026 |
| CUET / CET / exam | 19 | 8,421 | 0.5% | 139 | 0.9% | 1.65% | 6.7 | ipu cuet, ipu admission through cuet 2026, ipu btech through cuet |
| Management quota / direct admission | 18 | 6,286 | 0.3% | 136 | 0.9% | 2.16% | 6.8 | ipu management quota 2026, ipu direct admission 2026, ipu management quota fees for btech, usict management quota |
| News / notices | 3 | 854 | 0.0% | 17 | 0.1% | 1.99% | 10.4 | ipu updates, ipu notices, ipu news |
| Other / unclassified | 36 | 26,700 | 1.4% | 279 | 1.8% | 1.04% | 7.1 | yes, ipuniversity, ipu.in, ipu university delhi |

Reading it:

- **Brand/navigational is 56% of impressions and 0.19% CTR.** "ipu" alone has 706,831 impressions and 1,149 clicks; these searchers want the university. Do not buy these; they would burn budget and invite the "is this official?" problem.
- **Helpline/contact** is the highest-CTR intent (4.45%, position 5.9) and 16% of clicks, but it is also the intent most exposed to "I wanted the official number" bounce. The page is honest only in an FAQ near the bottom ("Is this the official GGSIPU helpline? No.").
- **Generic college lists** carry 29% of clicks at 3.6% CTR, but this is research intent: good for organic, weak for paid unless the page converts research into a call.
- **Management quota/direct admission** is small in GSC (6,286 impressions) but is the wording the keyword-map doc records as the converting Ads terms ("direct admission", "management quota", "admission process"), so GSC under-states its commercial value. Per-college variants ("mait management quota cutoff", "usict management quota", "vips management quota fees", "msit management quota cutoff") sit inside the Fees and College clusters in the table.
- **Counselling/registration** is the biggest non-brand pool (265k impressions) but mostly portal seekers ("ipu counselling registration 2026" 3,854 impressions at position 1.9, "last date" 2,642). The keyword-map doc and negatives CSV show these did not convert.
- **Devices** (all queries): mobile 52,226 clicks / 3,636,942 impressions (77.6% of clicks, 69% of impressions, CTR 1.44%, position 6.6); desktop 13,947 (20.7%, CTR 0.86%); tablet 1,151. **Countries:** India 66,729 clicks (99.1%).
- **Seasonality** (GSC clicks per month): 2025-09 78, 2026-01 89, 02 257, 03 418, 04 1,071, 05 5,531, 06 16,419, **07 26,605**, 08 12,457, 09 3,046. February to March is the ramp, April-May the build, June-July the peak, August the taper. No hour-of-day or region data exists in the export.

Opportunities visible in the same export (position <= 7, impressions >= 3,000, CTR < 1.5%): `ipu rank list` (18,872 impr, 0.72%), `ipu counselling 2026` (13,369, 0.88%), `ggsipu counselling 2026` (6,686, 0.70%), `ggsipu counselling date 2026`, `vips college fees` (4,280, 0.58%), `vips bba fees`. These are organic snippet questions (class C territory), not Ads work, but they show where paid and organic overlap.

---

## 5. Proposed Search-only structure for Feb-Jul 2027

**Principles.** Search Network only; Search Partners and Display expansion off. One landing page per ad group. Few ad groups per campaign because the operator is solo and the volume is small. No bidding on bare `ipu`, `ggsipu`, `ip university`. No claim of official status anywhere. Everything here is a proposal to be validated against the account's own search-terms and conversion data (see checklist).

**Bidding.** Weeks 1-4 (February): Manual CPC or Maximise Clicks with a max-CPC cap, because today's conversion data is inflated (T1, T2). Once the corrected conversions have >= 30 per month per campaign (form submits + calls >= 30 s), switch to Maximise Conversions with a tCPA of about 1.2x the observed CPA, per campaign. Do not feed smart bidding the phone-tap action while it is Primary.

**Match types.** Core terms in exact and phrase; no broad match in the first 8 weeks except one controlled broad test in "Admission by course" after tCPA is on, with the shared negative lists attached. Keep exact-match copies of the best converting search terms in their own ad group so budget follows them. Add exact negatives for the bare navigational heads in every non-brand campaign.

| Campaign | Ad groups (keyword themes) | Landing page | Notes |
|---|---|---|---|
| C1 Management quota / direct admission (highest intent) | (a) ipu management quota / management seat; (b) B.Tech management quota; (c) BBA management quota; (d) MBA management quota; (e) BA LLB / BBA LLB management quota; (f) direct admission ipu; (g) per college: MAIT, MSIT, VIPS, USICT management quota + fees | (a)(f) `IP-University-management-quota-admission-eligibility-criteria.php`; (b) `btech-management-quota-ipu.php`; (c) `bba-management-quota-ipu.php`; (d) `mba-management-quota-ipu.php`; (e) `ballb-management-quota-ipu.php`; (g) the college page (`mait-admission.php`, `msit-admission.php`, `vips-admission.php`, `usict-admission.php`) | The four course pages already score 4.25-4.5 (unified hero, form at 1,230-1,601 px); the hub scores 3.0 (form 6,861 px down on mobile) and needs the form moved up (F8). Fix the header overlap and add the disclosure line first |
| C2 Helpline and contact | ipu helpline number; ggsipu helpline / contact number; admission helpdesk / counselling helpline | `ipu-helpline-contact-number.php` (after fix H1/H2 below) | Largest honesty exposure: ad copy must say independent, not official |
| C3 Admission by course | B.Tech (JEE + CUET) ; BBA; BCA; MCA; B.Com; MBA; Law (BA LLB, BBA LLB, 3-year); CUET pathway | `IPU-B-Tech-admission-2026.php`, `ipu-btech-via-cuet.php`, `comprehensive-guide-to-bba-colleges...php`, `bca-admission-ipu.php`, `mca-admission-ipu.php`, `bcom-admission-ipu.php`, `mba-admission-ip-university.php`, `IPU-Law-Admission.php`, `law-3-year-admission-ipu.php` | Year-free wording until 2027 pages exist |
| C4 College-specific | MAIT, MSIT, VIPS, USICT, MAIMS, BPIT, BVP, USAR: "<college> admission", "<college> fees", "<college> cutoff" | the college pages and `mait-delhi-fees-courses-placements.php`, `maims-delhi-fees-courses.php` | One ad group per college; keep USAR on the hold list |
| C5 Counselling and rounds (capped, in-season only) | choice filling help; seat allotment help; counselling support; "ipu btech counselling" (exact) | `GGSIPU-counselling-for-B-Tech-admission.php`, `ipu-counselling.php` | Aim at "help" intent; keep registration/last-date searchers out |
| C6 Fees and cutoff (capped, research intent) | ipu fees structure; <college> fees; ipu btech cutoff; ipu bba cutoff | `ipu-fees-structure.php`, `ipu-btech-cutoff-2025.php`, `ipu-bba-cutoff.php`, `ipu-ba-llb-cutoff.php` | Only exact/phrase; judge by call + form, not clicks |
| C7 Own brand (exact) | [ipu.co.in], [ipu admission guide] | `/` | Tiny budget; protects the name |

Skip as paid targets unless search-terms data proves otherwise: generic college lists (`ipu colleges`, `ipu college list`, 126k impressions, 3.6% organic CTR, research intent) and the bare brand heads.

**Ad copy angles (honest).** Responsive Search Ads, pin the disclosure to a description. Headline candidates (<=30 characters): "IPU Management Quota Help", "Independent Admission Guide", "Call a Counsellor Mon-Sat", "B.Tech Admission via JEE/CUET", "Compare IPU Colleges & Fees", "Free Initial Guidance", "IPU Helpline & Counselling". Description candidates (<=90): "Independent counsellors explain IPU seats, cutoffs and fees. Not affiliated with GGSIPU." (88) / "Free initial guidance by phone, Mon-Sat 09:00-19:00. We guide; we do not guarantee seats." (89) / "Management quota rules, eligible colleges and process explained. Request a free callback." (89). Avoid: official, authorised, guaranteed, 100% admission, confirmed seat, limited seats, government. Do not put a year in copy until the landing page text matches.

**Call assets and schedule.** The site states Mon-Sat 09:00-19:00. Show the call asset only during those hours (ad schedule on the asset); run the text ads 08:30-21:00 Mon-Sat with a bid reduction after 19:00 when the form is the only conversion route, and about -40% on Sunday. This is a starting hypothesis: hour-of-day data is not in the GSC export, so check the Ads hour/day report from last season.

**Geography.** GSC says 99.1% India, so target India only, "Presence" (people in the location), exclude all other countries. Delhi NCR (Delhi, Gurugram, Noida, Ghaziabad, Faridabad) is the primary campaign geography because the service is Delhi-specific and calls are answered by a Delhi counsellor. Whether outside-Delhi states deserve a lower-bid second geo cannot be seen in GSC (no region dimension); decide from last season's Ads location report.

**Devices.** 77.6% of organic clicks are mobile, and the sticky bar and tap-to-call are mobile features. Keep mobile at +0%, desktop at -10% to -20% to start but do not exclude it: desktop is where a parent compares fees and management-quota options and the form sits in the first screen. Re-set bids after four weeks from the corrected conversion data by device.

**Budget logic (proxy, not a forecast).** Index = GSC cluster impressions x an intent weight (0-1, my judgement), then adjusted for the conversion evidence in the keyword-map doc.

| Cluster | GSC impressions | Intent weight | Index | Index share | Proposed share of peak budget | Why the adjustment |
|---|---|---|---|---|---|---|
| Management quota (incl. per-college) | 6,286 | 1.0 | 6,286 | 2% | **25%** | Tiny organic volume, but the recorded converting wording; commercial intent is explicit |
| Helpline / contact | 57,720 | 0.8 | 46,176 | 16% | **20%** | Highest CTR, high intent, but "official number" risk; cap until real calls are measured |
| Admission by course | 49,802 | 0.7 | 34,861 | 12% | **18%** | Course + "admission" intent; best landing pages |
| College-specific | 136,298 | 0.4 | 54,519 | 19% | **15%** | Large and fee/management-quota heavy; split by college |
| Counselling / rounds | 265,334 | 0.25 | 66,334 | 23% | **8%** | Mostly portal seekers; budget cap, in-season weeks only |
| Fees | 77,019 | 0.35 | 26,957 | 9% | **6%** | Research; exact/phrase only |
| Generic college lists | 126,169 | 0.3 | 37,851 | 13% | **4%** | Pause unless search terms prove value |
| Cutoff / rank | 57,398 | 0.2 | 11,480 | 4% | **2%** | Research; low commercial intent |
| CUET / exam | 8,421 | 0.5 | 4,210 | 1% | **1%** | Folded into B.Tech groups |
| Own brand | n/a | n/a | n/a | n/a | **1%** | Exact only |

For comparison, the keyword-map doc records that the `Admission Helpline` campaign took about Rs 4.98 lakh of Rs 10.4 lakh total spend (about 48%, 96% mobile): the proposal cuts that share by more than half until call quality is proven. Phase the budget by month using the GSC curve: February 5%, March 8%, April 12%, May 20%, June 25%, July 22%, August 8% of the February-August total (a heuristic from the clicks above). Reallocate every two weeks by cost per qualified lead once tracking is fixed; until then judge by form submits (`src=submit`) and answered calls.

---

## 6. Negative keywords: review of `docs/ads/2026-10-negatives.csv`

The file has 8 rows (portal/registration/form seekers, ipu cet 2024), cumulative recorded waste about Rs 883 (204+109+85+141+88+105+82+69). The keyword-map doc lists a ninth candidate, `maharaja agrasen institute`, that is **not** in the CSV (a mismatch to reconcile).

**Existing rows: risk check.** All are narrow (exact or a long phrase) and none blocks wanted traffic:
- `ipu cet 2024` (phrase): safe. Old-year, but it does not stop other old years.
- `ipu ac nic`, `ipu ac in ipu admissions nic in` (phrase): safe; portal seekers.
- `indraprastha university registration 2026`, `ipu university registration 2026` (exact): safe but nearly useless, because "ipu registration 2026", "ipu counselling registration 2026" (3,854 impressions) and every word-order change still match.
- `guru gobind singh indraprastha university counselling form`, `ip university counselling form`, `ggsipu form` (exact): same limitation.
- **Do not add `maharaja agrasen institute` as phrase or broad**: it would block `mait management quota`, `mait direct admission` (the keyword-map doc records 15 conversions) and MAIMS fees queries. If specific MAIT/MAIMS queries wasted money, add them as exact.

**Gaps (proposed additions, grouped as three shared lists; evidence from GSC where available).**

| List | Negative (match) | Evidence / reasoning | Risk of blocking good traffic |
|---|---|---|---|
| Portal and self-serve | `ipu.ac.in`, `ipu ac in`, `ac in`, `official website`, `ipu website`, `login`, `portal`, `nic in` (phrase) | `ipu. ac. in` 2,929 impr, `ipu website` 1,605, `ipu ac in` 1,506, `ipu.ac.in` 1,351, `ipu admission portal 2026` 1,097, `ipu official website` 1,014: all wanting the university site | Low. Check that "portal" does not appear in a good converting term first |
| Portal and self-serve | `registration`, `last date`, `application form`, `counselling form`, `apply online`, `download` (phrase) | Counselling registration/last-date cluster: ~35,000 impressions in the top 1,000 (`ipu counselling registration 2026` 3,854; `ggsipu counselling last date 2026` 2,642); negatives CSV already shows zero conversions | **Medium.** Wait for a search-term check; attach to C1-C4, not to C5 where "registration help" might be a wanted theme |
| Results and academics | `result`, `admit card`, `date sheet`, `answer key`, `marksheet`, `revaluation`, `transcript`, `degree`, `convocation`, `syllabus`, `question paper`, `previous year`, `pdf` (phrase) | `ggsipu round 1 result 2026 btech` 435, `ipucet admit card` 214, `ipu colleges list pdf` 1,025; not products we sell | Low. `pdf` removes "colleges list pdf" shoppers, which is intended |
| Jobs and other | `jobs`, `recruitment`, `vacancy`, `faculty`, `phd`, `ph.d`, `research`, `news`, `notice`, `circular`, `hostel`, `nearest metro`, `campus area` (phrase) | Not lead intent (e.g. `mait campus area in acres` 1,928 impressions) | Low |
| Old years | `2022`, `2023`, `2024` (phrase) | The keyword-map doc notes converting terms with old years in the history; the season for the ad is 2027 | **Medium.** Do **not** add `2025` (cutoff searches for last year are research; keep) and do not add it to C6 |
| Other universities | `dtu`, `nsut`, `delhi university`, `jnu`, `jamia`, `amity` (phrase) | Owner rule: IPU and DTU are separate datasets and are not mixed | Low |
| Courses we do not serve | `mbbs`, `bds`, `nursing`, `paramedical`, `bpt`, `b pharma` (phrase) | `ipu bpt counselling 2026` 788, `ipu paramedical counselling 2026` 448; there is a `med-admission-ipu.php` page, so decide before adding | **Owner decision** |
| Bare navigational heads | `[ipu]`, `[ggsipu]`, `[ip university]`, `[ipu university]`, `[ipu admission]`? (exact) | 1.04M impressions at 0.19% CTR | Exact only; keep `[ipu admission]` positive in C3 if it converts |
| Do not add | `free`, `list`, `form`, `date`, `last`, `fees` as single broad words | They appear in wanted terms (`free counselling`, `colleges list`, `fees`, `counselling date 2026`) | High |

Method for the first four weeks: review the search-terms report twice a week; move any term with >= 2 clicks and no conversion in a month to the lists; review the lists monthly against the converting terms.

---

## 7. Junk parameter URLs, spam referrals and invalid traffic

- **GSC rows:** 35 query-string URLs in `Pages.csv`. 28 are `?q=`, `?u=`, `index.php?p=`, `?search_mode=` and `index.php?products/...` junk: 4 clicks and 574 impressions in total. 6 are `blog-detail.php?url=...` (202 clicks, 10,185 impressions, almost all the BJMC guide). 33 rows are on the `www` host (210 clicks, 30,380 impressions).
- **Live behaviour (checked today):** `/?q=<spam-title>`, `/?u=<spam-title>`, `/index.php?p=594877` return **200** with the homepage, `rel=canonical` to `https://ipu.co.in/`, no `noindex`. `www.ipu.co.in/...` returns 301 to the apex. `blog-detail.php?url=<BJMC guide>` now returns **404** (it had 198 clicks and ~9.9k impressions; a 301 to `guide-to-bjmc-colleges-under-ip-university.php` is an organic task already noted in the keyword-map diagnostics).
- **What it implies for Ads:** (1) These are organic spam URLs (car-listing/forum/product titles with random suffixes, the usual scraped-title pattern); they are not ad clicks. Google appends `gclid` only to final URLs you submit, so junk URLs cannot create invalid ad clicks unless one is used as a final URL. (2) The unknown-parameter handling means any `?gclid=`, `?utm_*`, `?_gl=` still renders the canonical page (live check: `/?gclid=TEST123&utm_source=google` and `/ipu-colleges-list.php?gclid=abc` return 200, canonical clean), so auto-tagging is safe. (3) Keep ad final URLs on the apex HTTPS, no extra query besides the suffix, so the `www` 301 hop and parameter edge cases never sit between click and page. (4) In GA4, check that spam referrals are not inflating sessions, which would make conversion-rate reads on the same landing pages unreliable. (5) Because junk URLs also serve the homepage including the live form, it is worth ensuring the form still carries `page_url` (it does) so spam submissions are easy to spot.
- **Invalid-traffic watch:** no sign of invalid Ads traffic can be assessed without the account. Check Ads "Invalid interactions" and Search-Partner results, and GA4 engagement for mobile sessions under 2 seconds on the Ads landing pages.

---

## 8. Page-level and tracking fixes ranked by expected Ads impact

Safety class: **A** safe any time (no title/meta/H1/canonical/URL), **B** safe but test first, **C** touches title/meta/H1/canonical/URL (owner approval). Effort: S (< 1 h), M (half day), L (days). IDs F1... are used in the 30/60/90 plan. "GTM" fixes need no site deploy.

| ID | Fix | Pages / files | Class | Effort | Expected impact | How to verify |
|---|---|---|---|---|---|---|
| F1 | Fire the form conversion on the `form_submission` dataLayer event (Custom Event trigger), remove the `/thank-you.php` path trigger, once per page | GTM | A (GTM) | S | Removes inflated lead counts; cleaner smart-bidding signal (T1) | GTM Preview: valid submit fires once; direct `/thank-you.php`, honeypot/dedup redirects and refresh fire nothing |
| F2 | One-time guard on the thank-you push: set `$_SESSION['ty_ok']` in `sendemail.php` on success, require and clear it in `thank-you.php` before pushing `form_submission` | `sendemail.php`, `thank-you.php` | A | S | Stops reload/bookmark double counts even if GTM changes | Reload `?src=submit` twice: no second event |
| F3 | Phone-field normalisation: accept `+91`, `91`, `0` prefixes (drop them when the remainder is 10 digits), allow longer paste, then trim; also strip spaces/dashes | `assets/js/app.js` | A | S | Stops wrong numbers being stored (today `+919899991342` becomes `9198999913`) and paste/autofill frustration | Unit-test the handler with the 5 inputs from checklist item 25 on Android Chrome autofill |
| F4 | Time-gate fix: ignore `form_loaded_at` when it is in the future or implausible (use the session value, which is server-stamped); redirect silent drops to a distinct `/thank-you.php?src=held` or log them to `leads-fallback.jsonl` so they are recoverable; validation errors redirect back to the referring page with a visible message and keep the typed values | `sendemail.php`, `components/sidebar-enquiry.php`, `index.php`, `app.js` (client-side block of invalid submit + inline error) | A (B for the redirect-back change) | M | Recovers real leads lost to clock skew, 5-minute cooldown (second student), invalid phone bounce to the homepage | Skew test (item 26); invalid phone shows an inline message on the same page |
| F5 | Wire `cta_call` / `cta_enquire` to GA4 events with `cta_src`; add a fallback so tel: links without `data-cta-src` still report a placement (`body`, `faq`, `footer`, `sidebar`) by adding it in the delegated listener; pause GA4 `phone_clicks` | GTM + `include/base-nav.php` | A | S-M | Makes hero vs sticky vs row measurable; removes duplicate GA4 events (T3-T5) | GTM Preview: one `cta_call` with the right `cta_src` per tap; GA4 DebugView |
| F6 | Disclosure and claims: add a one-line "Independent admission guidance, not the GGSIPU office. Official site: ipu.ac.in" under the hero (and in the sticky-bar-free first screen); relabel the trust bar as "About GGSIPU (university facts)" or source/replace the figures; remove "Seat Secured" and "within 2 hours" or condition it on opening hours; stale "IPU Admission 2026 open, limited seats" banner (homepage, hold list until 5 Jan 2027) | `include/components/page-hero.php`, `hero-banner.php`, `trust-bar.php`, `thank-you.php` | A | S | Landing Page Experience transparency, policy safety for "helpline" and "admission" terms, message match | Visual check at 390 px; disclosure within the first 844 px |
| F7 | Header overlap: at <= 767 px the header is fixed (56 px) while hero padding is 48 px; add `body{padding-top:56px}` for the fixed header or raise `.ipu-page-hero` mobile top padding to >= 72 px (match `hero-section` pages that sit at 72 px). Also stop `bundle.min.css` overriding `position:relative!important` unintentionally | `include/base-head.php` (CSS only) | A | S | Shows the full H1 on 10 high-value pages (management quota x5, counselling, B.Tech 2026, Law, MBA, admission guide); avoids header-jump CLS if the bundle is slow | Screenshot 390 px: H1 top >= header bottom on the 10 pages; CLS stays 0 |
| F8 | Finish Phase 4 of the template-unification plan for the high-volume legacy pages, starting with those that Ads will use: `ipu-colleges-list`, `top-*`, `ipu-counselling`, `ipu-fees-structure`, `vips`, `mait`, `msit`, `usict`, `maims`, `bca/bcom/mca-admission`, cutoff pages. Gives hero call button in the first screen, one H1 (second H1 is class C, already decided inside that plan), form after the intro (1,200-1,800 px instead of ~5,600 px) | 24 legacy pages | A/B per page (B: test on 2 pages first) | M-L | Largest landing-page gain: moves 24 pages from 2.5 to ~4.0-4.5 on the rubric; those pages hold 64% of candidate-page organic clicks | Re-run the measurement script: `mobile_hero_tel_ok`, `mobile_form_top_px <= 1700`, `h1_count == 1` |
| F9 | Enhanced conversions and offline lead measurement: normalise phone to E.164 before hashing (`+91...`), add a GTM User-Provided Data variable and enable it on the form tag; add hidden `gclid` (from `_gcl_aw` cookie) and UTM fields to the form and to the Sheet payload; monthly CSV upload of qualified leads | `thank-you.php`, form components, `sendemail.php`, GTM, Ads | B | M | Better match rate and the only route to optimise for qualified leads without a CRM | Ads > Conversions > diagnostics shows EC "recording"; Sheet rows carry a gclid |
| F10 | Helpline page: add the unified hero or at least a call button in the first screen (>= 48 px, number visible), disclosure line, fix the nested `<html><head>` and the 45% whitespace; H1/title unchanged | `ipu-helpline-contact-number.php` | A | S-M | Most honesty-exposed and a top-5 traffic page; currently 2.5/5 and only an 83-89x24 inline link in the first screen | 390 px screenshot; Search Console URL inspection unchanged |
| F11 | Linkify plain-text phone numbers at runtime (wrap digit patterns outside anchors in `<a href="tel:+919899991342" data-cta-src="text">`), one script in `base-nav.php`; add `<meta name="format-detection" content="telephone=no">` so iOS stops creating untracked links | `include/base-nav.php`, `base-head.php` | A | S | 99 more tappable, tracked call targets; iOS taps become measurable | Count `a[href^="tel:"]` before/after; GTM Preview on iOS |
| F12 | Call-quality conversion: re-enable Website call conversions (forwarding number) or use call assets with call reporting and a 30 s minimum; mark phone-tap Secondary | GTM (`awcc`), Google Ads | B | M | Turns "taps" into calls and gives call duration | Call details report matches test calls |
| F13 | Self-host Inter (latin subset woff2, 2-3 weights, `font-display:swap`, preload the regular weight) and delete the googleapis/gstatic links and preconnects | `base-head.php`, `assets/fonts/` | A | S-M | Removes two origins and a post-paint font swap; typically 150-400 ms on mobile 4G | PageSpeed Insights / Lighthouse: fewer requests, no font CLS |
| F14 | `bootstrap5.min.css` (232,803 B raw, render-blocking): purge to the used subset or inline the critical part and load the rest with the print-swap pattern | `assets/css/`, `base-head.php` | B | M-L | Lower parse/layout cost on low-end Android; ~28 KB over the wire already | Lab FCP/LCP before/after on 6 pages; visual diff |
| F15 | Hero images: emit `srcset` using the existing `-400w`/`-800w` webp files with `sizes`; `loading=lazy` where below the first screen | image helper, unified templates | A | S | -15 to -20 KB per page on mobile; LCP image on 8 pages | Network panel: 400w served at 390 px |
| F16 | Thank-you page: make it a clean document (it includes `base-head.php` then a second `<html><head>`), add the out-of-hours variant, keep call + WhatsApp, drop unverifiable claims | `thank-you.php` | A | S | Honesty; better follow-up conversion (call now) | View source; open the page at 21:00 IST in a test |
| F17 | WhatsApp click as a tracked secondary CTA (hero or sticky third action on mobile), with a `wa_click` event | `base-nav.php`, pages | B | M | Often the lowest-friction contact in India; needs someone to answer | Event in GTM Preview; A/B on 2 pages |
| F18 | 2027 wording: Feb-Jul 2027 season terms will include "2027"; pages say 2026 in title/H1. Ads copy stays year-free until the page text matches. Decide per page after the 1 Dec / 5 Jan decisions | titles, H1, meta | **C** | M | Message match and CTR/QS for year-specific searches | Owner approval; GSC stop-loss rechecks |
| F19 | Sticky bar: show the number ("Call 9899991342") and implement the out-of-hours state from the CTA spec Wave 2b (navy Enquire primary, "Opens 09:00" call) | `base-nav.php` | B | M | Higher tap confidence for helpline/mgmt-quota visitors; honest after hours | A/B on 3 pages with the corrected `cta_call` data |
| F20 | Homepage form inputs to 16 px and a clear hero line (iOS zoom on focus; stale 2026-open banner); homepage is on the 5 Jan 2027 hold, so do it after the recheck | `index.php` | A (after hold) | S | iOS form friction | Visual check on iPhone Safari |

---

## 9. 30 / 60 / 90-day plan (finishes before February 2027)

Dates assume work starts the week of 14 October 2026. Hold list: homepage, USAR and ADGIPS titles/changes follow the existing plan (deploy 1 December, rechecks 15 December, 29 December, 5 January).

**Days 0-30 (to 13 November): measurement and lead-loss fixes (no ranking risk)**
- Week 1: owner runs checklist items 1-3, 10-12, 15-16 (GTM Preview, GA4 events, Ads conversion list). Decide the primary conversions (form + call >= 30 s). Do F1 (GTM) and F5 (GTM part).
- Week 2: F2, F3, F4 on a branch, test locally (Android autofill, clock skew, invalid phone, dedup), deploy `sendemail.php`, `thank-you.php`, `app.js` with a full FTP sync after `git pull` (GitHub Actions commits). Add shared negative lists (section 6) in Ads.
- Week 3: F7 (header), F6 (disclosure line, claims), F16 (thank-you page), F11 (linkify), F13 (fonts). Local crosslink test, lint, visual check before deploy.
- Week 4: owner test-call script (checklist 24-28); record baseline: tap counts, form counts, `?src=submit` counts, Sheet rows for 4 weeks.

**Days 31-60 (to 13 December): landing pages for the ad groups**
- F8 Phase 4 conversions for the pages that map to C1-C4, in this order: management-quota pages (already unified), helpline (F10), counselling, fees, cutoffs, colleges. Test on 2 pages first (B), then batch.
- F9: add `gclid`/UTM hidden fields and E.164 hashing; set up the Sheet column and the monthly CSV upload routine (one worked example with 5 test rows).
- F14, F15: CSS purge test and `srcset`; re-measure with PageSpeed Insights/Lighthouse for 6 pages (install Lighthouse or use the web tool; not available locally today).
- Draft the 7 campaigns in Google Ads Editor (paused), RSAs with the honest copy, call assets with schedules, final URL suffix, shared negative lists, location/device settings. Nothing goes live.

**Days 61-90 (to 12 January): decisions and dry run**
- F18 title/H1 year decisions (class C) after the 1 December deploy and the 15 December / 29 December rechecks; homepage and USAR only after 5 January; F20 afterwards.
- F12 call-conversion set-up (forwarding number or call asset) and F17/F19 tests; clean end-to-end test of all conversions (checklist 24-28) and compare GTM, GA4, Ads and the Sheet counts for a week of test traffic.
- Freeze the structure; write the budget phasing sheet (section 5 percentages by month).

**Pre-season (13 January to 31 January):** low-budget live test on C1 (management quota) and C7 (brand) only, to validate tracking against real clicks; fix anything that appears; leave the rest paused.
**1 February:** switch on C2-C4 at the February share; ramp to C5/C6 in April-May per the budget table; first optimisation review mid-February from search terms and corrected conversions.

---

## 10. What I could not verify

- Anything inside GTM (workspace, unpublished changes), GA4 (key events, imports, filters) or Google Ads (conversion actions, campaigns, search terms, QS, call assets, locations, hour of day). The container reading is from the public `gtm.js` v22 on 2026-10-14; labels in the old `docs/status-archive/GTM_*.md` guides differ from the live ones, so those guides are out of date.
- Field Web Vitals (CrUX/PageSpeed field data) and Lighthouse scores: Lighthouse is not installed; lab numbers are Playwright/Chrome proxies. Lab numbers on the local copy used uncompressed bytes (median FCP 2.7 s) and are only comparable between pages; the four live runs (FCP/LCP about 1.0-1.1 s) had GTM, GA4, Clarity and Google Fonts blocked, so real-world numbers will be slower.
- Whether the live GTM tag-fire behaviour matches my reading of the rule set (item 2 of the checklist tests it in one visit).
- Real call outcomes, lead quality, and any effect of the +91 and clock-skew bugs on actual lead volume (reproduced in headless Chrome; frequency in the field unknown; Android autofill formatting and `maxlength` interaction need a device test).
- iOS Safari behaviour for plain-text numbers and 14 px form zoom (no iOS device here).
- The accuracy of the university facts in the trust bar (100,000+ graduates, 60+ colleges, 15+ courses, NAAC A++, Est. 1998); the repo has no source for them and I did not check them against official data.
- Whether unified-template CSS in the local `base-head.php` (not yet on live) changes any measurement for the 17 pages already using the unified hero; structure and H1/hero type matched live on all 44 pages, the CSS delta is the new unified blocks.
- Whether the Apps Script behind the Sheet accepts extra fields (needed for F9).
- Hour-of-day, city/state demand: absent from the GSC export.
- Competitive CPCs, Quality Scores, and budget sizes: not in the repo. The only spend figures are from the keyword-map doc (Rs 10.4 lakh total, Rs 4.98 lakh `Admission Helpline`, about Rs 3.25 lakh in Search with Rs 24k zero-conversion), taken as given.
- The local working tree was being modified by other processes while I audited (several `website_download/*.php` files have modification times after 13:00 on 2026-10-14, none made by this audit). My live comparison (44 pages) matched hero type and H1 count at that moment; re-run the measurement before relying on any single page's number once Phase 4 conversions land.
