# Click-to-call tracking — 2026-04-17

**Parent audit:** `2026-04-17-ipu-website-audit.md` § P0.3

**Goal:** Fire a GA4 `phone_click` event every time a visitor taps any `tel:` link on `ipu.co.in`, so we can attribute phone-call intent to specific pages, campaigns, and sources — and then import that event as a conversion in Google Ads to enable call-focused Smart Bidding.

**Scope:** Website-side instrumentation only. Does NOT cover Google Ads call extensions (P0.4), call-conversion action creation in Ads (P0.4), or IVR-side missed-call automation (P0.5).

**Non-goals:**
- No CTA-location metadata (header vs hero vs sticky) — audit chose simplest tracking (option "a")
- No phone-number payload — only one number exists site-wide
- No WhatsApp click tracking (future: same pattern, separate event)

---

## Architecture

Single delegated event listener lives in `website_download/include/base-head.php` (included by every page, so the listener is installed site-wide with one file change). On any click on `a[href^="tel:"]`, it pushes `{event: 'phone_click'}` to the existing GTM `dataLayer` (GTM container ID `GTM-5GXCN7Z`, already loaded via base-head).

GTM routes the event:
1. **GTM → GA4 event tag** — fires a GA4 event named `phone_click`. GA4 auto-enriches with `page_path`, `page_referrer`, `session_source`, device, geo, `session_id`.
2. **GTM → Google Ads conversion tag** (configured after P0.4 creates the conversion action) — counts the same event as a conversion in Google Ads for Smart Bidding.

GA4 marks `phone_click` as a "Key event" so it appears in standard conversion reports.

### Component boundaries

| Unit | Purpose | Depends on | Observable via |
|---|---|---|---|
| Inline listener in `base-head.php` | Turn DOM click into a `dataLayer` event | None (vanilla JS, `dataLayer` is already there via GTM) | `window.dataLayer` array, browser devtools |
| GTM "Phone Click" trigger | Match `phone_click` custom event | Listener | GTM Preview mode |
| GTM "GA4 — phone_click" tag | Forward to GA4 as an event | Trigger + existing GA4 config tag | GA4 DebugView / Realtime |
| GTM "Google Ads — Phone Click" tag (deferred) | Count as Google Ads conversion | Trigger + Google Ads conversion action (P0.4) | Google Ads Conversions report |

Each unit is testable in isolation: devtools for the listener, GTM Preview for the trigger, GA4 Realtime for the event tag, Google Ads for the conversion tag.

---

## Implementation detail

### Code change — one file

**File:** `website_download/include/base-head.php`

**Insertion point:** immediately before `</head>` (line 145, after the GTM snippet and tracking comment).

**Code (6 lines including `<script>` tags):**

```html
<script>
document.addEventListener('click', function(e) {
  var a = e.target.closest('a[href^="tel:"]');
  if (!a) return;
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({ event: 'phone_click' });
});
</script>
```

Notes:
- `e.target.closest(…)` handles clicks on children of the link (icon spans, text spans).
- Does NOT call `e.preventDefault()` — the browser still invokes the `tel:` handler normally.
- `window.dataLayer` guard in case GTM somehow failed to load (defensive but probably unnecessary).

### GTM config — done in UI at tagmanager.google.com

**Trigger:**
- Name: `CE — phone_click`
- Type: Custom Event
- Event name: `phone_click`
- This trigger fires on: All Custom Events

**Tag 1 — GA4 event:**
- Name: `GA4 — phone_click`
- Type: Google Analytics: GA4 Event
- Configuration tag: existing GA4 config tag
- Event name: `phone_click`
- Parameters: (none — leave default, GA4 auto-attaches page info)
- Trigger: `CE — phone_click`

**Tag 2 — Google Ads conversion (deferred, post-P0.4):**
- Name: `Ads — Phone Click Conversion`
- Type: Google Ads Conversion Tracking
- Conversion ID: (from P0.4)
- Conversion Label: (from P0.4)
- Trigger: `CE — phone_click`

### GA4 config — Admin UI

After the first `phone_click` event fires and appears in GA4 (takes up to 24h, usually 5 min):
- Admin → Events → find `phone_click` in the list → toggle **Mark as key event**
- This surfaces it in Reports → Engagement → Conversions.

---

## Test plan

**Before deploy:**
- Note current state in GA4 Realtime — no events named `phone_click`.

**Deploy:**
- Upload `website_download/include/base-head.php` to `/public_html/include/base-head.php` on cPanel via FTP. (Same mechanism used for the font-unification commit; see existing `upload_news.py` for the Python/ftplib pattern.)

**Post-deploy smoke test (within 10 minutes of upload):**
1. Open `https://ipu.co.in/` on a phone or in desktop dev tools with mobile emulation.
2. Hard-refresh (⌘⇧R / Ctrl-F5).
3. Open devtools → Console → type `window.dataLayer` → verify it's an array.
4. Tap any `CALL: 9899991342` link.
5. Re-inspect `window.dataLayer` — last pushed object should include `{event: 'phone_click'}`.
6. (Optional) Turn on GTM Preview mode → open the site in the preview-connected tab → click a `tel:` → the `CE — phone_click` trigger should fire and the `GA4 — phone_click` tag should fire.
7. GA4 → Reports → Realtime → within 30 seconds the `phone_click` event appears in "Event count by Event name".

**Acceptance:**
- `phone_click` events are visible in GA4 Realtime when triggered from at least two different pages (homepage and one inner page).
- The `tel:` action itself still works — the dialer opens on mobile.
- No console errors on page load.
- Lighthouse performance score ±0 vs pre-deploy (verify once, e.g., via PageSpeed Insights before/after).

**Rollback:**
- Remove the `<script>` block from `base-head.php`.
- Re-upload to cPanel.
- No data cleanup needed: events already captured remain in GA (which is what you want — the data was real).

---

## SEO / performance / Ads impact

| Concern | Impact |
|---|---|
| SEO rankings | None — no HTML structure change, no URL change, no content/meta/canonical/schema change. |
| Page speed | ~250 bytes of inline JS. No HTTP request added. Parse time <1ms. CWV unaffected. |
| Ads Quality Score | Unaffected — ad relevance, landing page experience, and CTR inputs unchanged. |
| Ad landing UX | Unchanged — visitors see no difference. |
| `tel:` link behavior | Unchanged — no `preventDefault()`, dialer opens as before. |
| Worst-case broken JS | Browser skips the block; rest of page renders normally. |

---

## Open questions / assumptions

- **Assumption:** Sumit has either admin access to the GTM container (`GTM-5GXCN7Z`) or can request the admin to add the two GTM items. If not, a fallback path is to call `gtag('event','phone_click')` directly instead of pushing to `dataLayer` — but this bypasses GTM and complicates P0.4 downstream. Worth confirming GTM access before execution.
- **Deferred:** Tag 2 (Google Ads conversion) cannot be fully configured until P0.4 creates the Conversion Action in Google Ads. The spec deliberately keeps them separate so the GA4 half can ship immediately; Ads side follows in a few hours or days.

---

## Success criteria

**Week 1:** `phone_click` events flow into GA4 for all pages. Dashboard or ad-hoc report shows per-page call-click counts.

**Week 2–4:** With the data, produce a single list: "pages ranked by `phone_click` count per session" — this drives P1 work (where to add more prominent CTAs).

**Week 4+:** After P0.4 wires the Ads conversion, Google Ads Smart Bidding can start optimizing toward phone clicks for eligible campaigns. Measure: CPA on `phone_click` conversions vs form-submit conversions.
