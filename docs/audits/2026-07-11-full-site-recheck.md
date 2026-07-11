# ipu.co.in — Full-site recheck (speed · results · code)

**Date:** 2026-07-11 · **Method:** 3 parallel read-only audits + live production checks + self-verification of top findings. **No code changed, nothing deployed.**

Live prod baseline (curl): LiteSpeed, gzip on, HTML ~15–16 KB/page over the wire, `.htaccess` already has gzip + Expires + immutable static caching. `x-powered-by: PHP/8.5.7` still leaks (the held `a7627b8` suppresses it — confirms Phase 2 not yet deployed).

Legend: 🔴 Critical 🟠 High 🟡 Med ⚪ Low · **[HELD]** = already covered by held commit `a7627b8` (deploy after 15-Aug).

---

## 🔴 CRITICAL — act first

### C1. Live FTP password committed to git (security) — *needs Sumit, I can't do it*
`FTP_PASS = "Sumit@8022"` (same live cPanel password as `.env`) is hardcoded in **41 tracked files** (`deploy_ai_agents.py`, all `deploy/archive/upload_*.py`, 2 plan `.md`s) and sits in **16 commits of history**. `.env` itself is correctly untracked. Anyone with repo read access → full FTP write to `public_html` → site takeover / lead-data theft.
**Fix:** (1) **Rotate the cPanel FTP password now** — this is the only real remedy; the history is burned. (2) Then I de-hardcode `deploy_ai_agents.py` + `deploy/archive/` to read `os.environ` (as `deploy.py` already does) and scrub the 2 MD files. Optional: `git filter-repo` history purge. *Effort: rotation S (you) · code S–M (me).*

---

## 🟠 HIGH — quick, high-value, mostly S-effort

### H1. Full-screen preloader blocks first paint on every page (SPEED)
`include/base-nav.php:10` + `include/base-head.php:108-110` render a white `position:fixed z-index:99999` overlay dismissed only on `window.load` **+300ms +500ms fade** (`assets/js/app.js:11-22`). LCP is gated on *total* page load, and it **cancels the held commit's hero `fetchpriority` work** (hero paints, then hides behind the overlay). **Fix:** remove the preloader (site renders fine without it). *S. Not in held.*

### H2. Bootstrap CSS render-blocking, 232 KB mostly unused (SPEED)
`include/base-head.php:216` loads `bootstrap5.min.css` with no defer, while `bundle.min.css` right below it *is* deferred. Critical nav/hero CSS is already inlined. **Fix:** defer it with the same `media="print" onload` trick (short-term); PurgeCSS later (→ ~30 KB). *S / L. Held commit explicitly defers this to an unbuilt "Batch C".*

### H3. Sitemap feeds a 404 (SEO)
`sitemap.xml:420` = `ipu-bba-cutoff-2025.php` (does not exist; real page `ipu-bba-cutoff.php` is already at :808). **Fix:** delete line 420. *S. SEO-safe (dead URL was never live).*

### H4. Two enquiry forms on one page — rule + analytics violation (CONVERSION)
`ipu-bba-cutoff.php:119`+`:1403` and `ipu-ba-llb-cutoff.php:119`+`:743` each include `sidebar-enquiry.php` twice → duplicate forms, double-fired form events, per the one-form-per-page rule. **Fix:** remove the second include. *S.*

### H5. ~33 internal links point at 301 redirects, incl. site-wide footer (SEO)
`include/base-footer.php:45` links to `IPU-Law-Admission-2026.php` (301 → `IPU-Law-Admission.php`); 20 inbound links total on that stub, plus a 2-hop chain via `law-admission-ip-university.php → …-2025.php → …`. **Fix:** repoint internal hrefs to final targets (301s stay for external equity). *S. SEO-safe.*

### H6. 10 orphan pages + 4 pages missing from sitemap (SEO)
Zero inbound internal links to: `ipu-fees-structure.php` (high-intent!), `barch-admission-ipu.php`, `med-admission-ipu.php`, 4 cutoff pages, `sbit/tiips/tribhuvan-admission.php`. Also `sbit/tiips/tribhuvan/usct-admission.php` are absent from `sitemap.xml`. **Fix:** add contextual links from relevant hubs (via existing `related-pages.php`) + 4 sitemap entries. *S–M. Additive/safe.*

### H7. Email header injection via Subject (security)
`sendemail.php:103` + `include/form-handler.php:88`: `$subject = "New Enquiry: $name - $course"` — `htmlspecialchars` doesn't strip CR/LF, so `name` with `\r\nBcc:` injects mail headers → spam relay. (`sendemail.php` already CRLF-guards Reply-To but missed subject.) **Fix:** `str_replace(["\r","\n"]," ",$name/$course)`. *S.*

---

## 🟡 MEDIUM

- **M1. Double BreadcrumbList JSON-LD — true scope is 73 pages, not ~29.** Root cause: both `include/components/hero-banner.php:74` and `include/components/breadcrumb-schema.php` emit a BreadcrumbList. **One-file fix** (remove it from `hero-banner.php`) resolves all 73. *S. Additive/safe.*
- **M2. CSS `background-image` LCP heroes still load `.jpg` though `.webp` exists** — `blog.php:106`, `news/index.php:19` (`banner-bg-2`, 171 KB), `counter-bg-2.jpg` across 14 pages. Held commit only rewrote `<img>`, not CSS backgrounds. **Fix:** `image-set()` + `rel=preload as=image` (0 preloads on site today). *M.*
- **M3. News section (37 pages) unoptimized** — `include/news-template.php:215` + `news-card.php:9`: no width/height (CLS on LCP), no lazy, no WebP (`assets/images/news/` has 0 webp). *M. Held commit defers news webp.*
- **M4. 4 real pages missing self-canonical** — `BPIT.php`, `economics-admission-ip-university.php`, `guide-to-bjmc-colleges-under-ip-university.php`, `maharaja-agrasen-business-school…php` (last also has no meta description). Adding a *self*-canonical = additive, safe. *S.*
- **M5. Content gaps** — no cutoff page for BCA / MCA / BA-English though admission + top-college pages exist. New additive evergreen-URL pages. *M.*
- **M6. Two divergent form handlers** — `include/form-handler.php` vs `sendemail.php` have drifted (rate-limit + CRLF guard only in the latter); fixing one misses the other. **Fix:** collapse to one shared handler. *M.*

---

## ⚪ LOW / verify

- **L1.** `thank-you.php` may fire `gtag`/`fbq` conversion IDs also carried by GTM → possible double-count. Verify. *S.*
- **L2.** Malformed title on `comprehensive-guide-to-bballb-admission-in-ip-university.php:15` (127 chars, stray "…CLAT Process  Meta"). **SEO-frozen** — CTR-only, needs GSC baseline + stop-loss.
- **L3.** `tel:` inconsistency — 53 bare `tel:9899991342` vs 255 `+91`. Normalize. *S.*
- **L4.** No CSRF on forms (mitigated by honeypot + time-gate + 6-layer dedup; low). Footer `defer` missing on `bootstrap.bundle.min.js`+`app.js`. Brotli not enabled (gzip only). "Self-hosted fonts" actually fetch Google Fonts. Sitemap `lastmod` frozen 2026-02-15 on 28 hubs. News-build writes `$slug` into a path unsanitized (author/AI-controlled). Apps Script webhook URL hardcoded server-side.

---

## Verified NON-issues (checked, clean)
Single GTM container (no duplicate/hardcoded tags on content pages); no jQuery; no PHP 8.2 deprecations; `php -l` clean on handlers; no reflected XSS from `$_GET`/`REQUEST_URI`/`PHP_SELF` (session email/phone validated + escaped in `thank-you.php`); `.htaccess` solid (blocks `include/`, `.env`-class, denies listing, X-Frame/nosniff/HSTS); `deploy.py`/`upload_news.py`/`news-scraper.py` use env creds + timeouts + try/except; FAQ schema present on all 65 FAQ pages; form validation solid (honeypot + 6-layer dedup + server regex); homepage has EducationalOrganization + WebSite/SearchAction schema; single-locale so no hreflang; `.env` NOT tracked in git.

---

## Recommended order
1. 🔴 **C1** — you rotate cPanel FTP pw; I de-hardcode the scripts.
2. 🟠 One additive+safe batch (no title/meta/H1/URL touches): **H1** preloader, **H2** defer Bootstrap, **H3** sitemap, **H4** dup forms, **H5** internal-link repoint, **H6** orphans/sitemap, **H7** subject CRLF, **M1** breadcrumb dedup. All S-effort, all reversible.
3. 🟡 M2–M6 as a follow-up. Speed items M2/M3 could ride the held Phase 2 deploy after 15-Aug.
