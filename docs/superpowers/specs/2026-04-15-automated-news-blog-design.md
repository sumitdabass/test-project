# Automated News & Announcements Blog — Design Spec

**Date:** 2026-04-15
**Status:** Approved (pending final review)
**Project:** ipu.co.in website overhaul
**Scope:** News & Announcements section (first module of broader automated blogging system)

---

## 1. Purpose

Build a dedicated **News & Announcements** section at `ipu.co.in/news/` that is populated daily by an automated pipeline. The section covers official IPU notifications (counselling rounds, CET updates, result dates, new programmes, seat additions, etc.) and is designed to be discoverable by both search engines and generative-AI systems.

Later specs will extend this system with long-tail SEO content and freshness updates to existing evergreen pages. **Those are explicitly out of scope for this spec.**

## 2. Goals

- **Daily freshness** — a new post on `ipu.co.in/news/` within 24 hours of news breaking on official IPU sources
- **Zero-manual-effort steady state** — fully automated scrape → rewrite → publish; human involvement limited to reviewing the daily Slack digest and occasionally suggesting edits
- **SEO-optimized** — each post is an indexable static page with correct schema markup, internal links, and freshness signals
- **AI-citation-friendly** — posts follow a structure (tl;dr, FAQ, semantic headings) that maximizes likelihood of citation by GPT/Claude/Perplexity
- **Copyright-safe** — scrape only official IPU sources + Google News RSS; no paraphrase of third-party editorial
- **Fast rollback** — git-tracked MD source of truth; one-click unpublish from Slack; global template edits propagate to all posts

## 3. Non-Goals / Out of Scope

- Long-tail SEO content generation (separate spec)
- Auto-refresh of existing evergreen pages (separate spec)
- Chat agent / auto-answer in the chat section (separate future spec; the IPU n8n instance built here will host it later)
- User comments, reactions, or social features
- Multi-author support or editorial roles
- A full admin UI (a lightweight `/admin/news.php` is a later enhancement, not required for v1)

## 4. Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                     IPU n8n (new instance)                   │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐          │
│  │ Daily   │→ │ Scrape  │→ │ Claude  │→ │ Build   │          │
│  │ cron    │  │ sources │  │ rewrite │  │ MD+PHP  │          │
│  └─────────┘  └─────────┘  └─────────┘  └────┬────┘          │
│                                              │               │
│              ┌───────────────────────────────┤               │
│              ▼                               ▼               │
│         ┌─────────┐                    ┌─────────┐           │
│         │ GitHub  │                    │  FTP →  │           │
│         │ commit  │                    │  cPanel │           │
│         └─────────┘                    └────┬────┘           │
│                                              │               │
│                                              ▼               │
│                                        ┌─────────┐           │
│                                        │ Slack   │           │
│                                        │ digest  │           │
│                                        └─────────┘           │
└──────────────────────────────────────────────────────────────┘
```

### 4.1 Runtime

- **Host:** new dedicated IPU n8n instance (separate from the existing KYNE instance). Same future instance will later host the chat-agent workflow.
- **Schedule:** once per day (UTC time to be confirmed with user; sensible default: 08:00 IST = 02:30 UTC).
- **Model:** Claude Sonnet 4.6 via Anthropic API (balances quality + cost for daily cadence).

### 4.2 Sources

1. **ipu.ac.in** — official university notifications page (HTML scrape)
2. **ipuadmissions.nic.in** — admission portal announcements (HTML scrape)
3. **Google News RSS** — queries: `"IPU admission"`, `"GGSIPU counselling"` (RSS feed parse)

Third-party editorial sites (Shiksha, Careers360, CollegeDunia) are explicitly excluded to eliminate copyright risk.

### 4.3 Failure Modes

| Failure | Handling |
|---|---|
| A single source scrape fails (timeout, 5xx) | Log, skip that source, continue others |
| AI rewrite fails (API error, rate limit) | Retry once with exponential backoff; on second fail, skip item and include in digest as "skipped due to AI error" |
| FTP upload fails | Commit to git still succeeds. n8n sends a Slack alert with instructions to run `upload_ftp.py` manually. |
| GitHub commit fails | FTP still succeeds (live site is updated). Slack alert flags the missing commit so history can be repaired manually. |
| Dedup state file missing/corrupt | Regenerate from filesystem (scan `content/news/*.md` frontmatter). |

## 5. File & Folder Structure

### 5.1 In the repo (git-tracked)

```
content/
  news/
    2026-04-15-round-2-counselling-schedule.md
    2026-04-14-cet-result-date-announced.md
    _state.json                  # dedup state

website_download/
  news/
    index.php                    # list/archive with category filter
    <slug>.php                   # one per post (thin wrapper)

  assets/images/news/
    counselling.jpg
    cet.jpg
    admissions.jpg
    results.jpg
    general.jpg
    urgent-banner.jpg

  include/
    news-template.php            # shared renderer included by every post
    news-card.php                # list-item partial
    news-jsonld.php              # NewsArticle + FAQPage schema builder
```

### 5.2 On the live cPanel server (via FTP)

Only the following are uploaded:
- `website_download/news/*.php`
- `website_download/include/news-*.php` (on template change only)
- `website_download/assets/images/news/*.jpg` (one-time)
- `website_download/sitemap.xml` (on every run)
- `website_download/llms.txt` (on every run)

MD source files and `_state.json` are **never** uploaded — they stay in the repo.

## 6. Post Schema (MD Frontmatter)

```yaml
---
title: "Round 2 Counselling Schedule Announced for IPU 2026–27"
slug: "round-2-counselling-schedule"
date: 2026-04-15
date_modified: 2026-04-15
category: Counselling          # one of: Counselling | CET | Admissions | Results | General
tags: [B.Tech, MBA, 2026]
read_time: 6                   # auto-calculated from body
featured: false
is_urgent: false
image: assets/images/news/counselling.jpg   # category default unless overridden
tldr: "IPU has announced Round 2 counselling dates for B.Tech and MBA programmes, running April 22–28, 2026."
faq:
  - q: "When does Round 2 counselling start?"
    a: "Round 2 counselling begins April 22, 2026."
  - q: "Which programmes are covered?"
    a: "B.Tech, MBA, and select PG courses. Check the official list at ipuadmissions.nic.in."
---

<!-- body in markdown follows -->
```

Explicitly removed fields: `author`, `source_name`, `source_url`.

## 7. Rendering

### 7.1 Thin-wrapper pattern

Each generated `<slug>.php` is ~5 lines:

```php
<?php
$post = [ /* all frontmatter + body, as PHP array */ ];
include('../include/news-template.php');
```

`news-template.php` reads `$post`, renders the full page (head, meta, JSON-LD, article body, related posts, footer). **All design and SEO logic lives in the template** — editing the template instantly updates every post without regeneration.

### 7.2 Index page (`/news/index.php`)

- Hero: most recent or featured post
- Category filter tabs (Counselling / CET / Admissions / Results / General)
- Chronological list of posts, paginated (20 per page)
- Each card: image, title, tldr, date, category badge, read time
- Regenerated on every cron run (scans `content/news/*.md`)

### 7.3 SEO + AI-citation layer (auto-injected by `news-template.php`)

| Element | Purpose |
|---|---|
| `<title>`: `{title} — IPU News` | SERP title |
| `<meta description>`: `tldr` field | SERP snippet |
| `<link rel="canonical">` | avoid dup content |
| OG + Twitter card meta | social sharing |
| NewsArticle JSON-LD (`headline`, `datePublished`, `dateModified`, `image`, `publisher`) | Google News eligibility + rich results |
| FAQPage JSON-LD (when `faq` present) | featured snippet + AI citation |
| BreadcrumbList JSON-LD (Home → News → Category → Post) | breadcrumbs in SERP |
| `<h1>` matches title; `<h2>/<h3>` from body | semantic hierarchy |
| Visible tl;dr box at top | humans + AI models |
| 2–4 internal links to existing `ipu.co.in` pages (inserted by AI prompt) | authority flow |
| Auto-append to `sitemap.xml` | Google discovery |
| Auto-append to `llms.txt` under "IPU News" heading | LLM discoverability |
| `lastmod` in sitemap updated on every regeneration | freshness signal |

## 8. AI Rewrite Prompt Strategy

System prompt pins:
- IPU context (what GGSIPU is, key programmes, target audience = prospective students and parents)
- Required output structure: tldr (1 sentence), FAQ (2–4 Q/A), body with semantic `<h2>/<h3>`
- Factual-only content: "Do not infer, speculate, or paraphrase editorial phrasing. Use only facts present in the source."
- Internal-link requirement: 2–4 links to existing `ipu.co.in` pages (template provides a whitelist of URL patterns)
- Tone: informative, direct, student-focused, no promotional language
- Output JSON matching the MD frontmatter schema

The prompt is iterated in the n8n UI — cheap to tune after observing outputs.

## 9. Dedup

- `content/news/_state.json` stores SHA-256 hash of each source URL already processed
- Scraper skips any source URL whose hash is in the state file
- On each successful post publication, hash is appended to the state file
- Monthly archival: hashes older than 90 days moved to `_state-archive-YYYY-MM.json` to keep the active file small

## 10. Daily Slack Digest

**Delivery:** Slack DM to user (channel: user's personal DM with the IPU-News bot).

**Sent after the daily cron completes, regardless of outcome.**

**Contents:**
- Date header
- Count of posts published
- For each post: title, category, read time, live URL, and four inline buttons — **Read**, **Edit**, **Unpublish**, **Suggest edit**
- Sources-hit summary
- Skipped-duplicates count
- Any errors (scrape fails, AI fails, FTP fails) with remediation hint

**Button behaviors:**
| Button | Action |
|---|---|
| Read | Opens the live URL in the browser |
| Edit | Opens the MD file in GitHub's web editor; on commit, a GitHub webhook into n8n re-triggers the build-and-deploy flow for that single post |
| Unpublish | Confirmation prompt → calls `unpublish-by-slug` n8n workflow → deletes MD + PHP + sitemap entry + `llms.txt` entry + rebuilds index |
| Suggest edit | Opens a Slack modal with a text area → user types the change in plain English → n8n passes (current MD + user instruction) to Claude → Claude returns an updated MD → auto-regenerates + FTPs → bot replies in thread with ✅ and a diff preview |

**Weekly summary (Sundays):** total posts, top categories, most-edited post, unpublished count. Single Slack message, no buttons.

## 11. Editing Workflow Scope

| Action | Path | Typical effort |
|---|---|---|
| Fix typo / wrong fact | Slack "Edit" → GitHub web edit → auto-regenerate | ~30 sec |
| Unpublish bad post | Slack "Unpublish" button | ~10 sec |
| Suggest change in plain English | Slack "Suggest edit" button → modal | ~30 sec |
| Change slug / URL | Edit MD frontmatter + rename file → auto-regenerate with 301 redirect from old slug | ~1 min |
| Feature a post | Set `featured: true` in MD → regenerate index | ~15 sec |
| Update evolving story | Edit body + bump `date_modified` → regenerate | ~1 min |
| Global design / layout change | Edit `include/news-template.php` once + FTP → propagates to all posts, no regeneration | ~2 min |
| Bulk category rename | sed across `content/news/` + run `rebuild-all` n8n workflow | ~5 min |

## 12. Rollback

- Every change is a git commit → `git revert` for any change in history
- Slack Unpublish button for single-post immediate removal
- If the site breaks globally: revert `include/news-template.php` to previous commit and FTP the old version — all existing post pages pick it up instantly because of the thin-wrapper pattern

## 13. Testing Strategy

### 13.1 Template / rendering (local)

- Unit tests for `news-jsonld.php` schema builder: feed sample `$post` arrays, assert JSON-LD output validates against schema.org NewsArticle and FAQPage
- Template snapshot tests: render known `$post` fixtures through `news-template.php`, diff HTML output against committed snapshots
- `index.php` test: seed 3–5 fixture MD files in a temp `content/news/`, render index, assert correct order + category filtering

### 13.2 Build pipeline (n8n)

- Dry-run mode for the full n8n workflow: scrape + AI rewrite + build MD+PHP, but skip GitHub commit and FTP. Output shown in a test Slack channel.
- Regression fixtures: a handful of committed sample scrape outputs (HTML from ipu.ac.in, an RSS feed payload) — run the rewrite prompt against them and assert the output JSON matches the schema and includes required fields

### 13.3 End-to-end (manual smoke)

Before going live:
1. Run the n8n workflow in dry-run against yesterday's actual news
2. Inspect generated MD + PHP files, review AI rewrites for quality and factuality
3. Run full pipeline to a staging `/news-staging/` folder on cPanel
4. Verify JSON-LD with Google Rich Results Test tool
5. Verify Slack digest arrives correctly with working buttons

### 13.4 Post-launch monitoring

- Weekly sanity: spot-check 3 random posts for factual accuracy against source
- Track: posts published/day, AI failure rate, FTP failure rate, time from scrape to live, unpublished count, edited-via-suggest count

## 14. Risks & Mitigations

| Risk | Mitigation |
|---|---|
| AI publishes hallucinated dates/cutoffs | Sources 1+2 are official IPU — low hallucination risk at source. Prompt enforces factual-only. Slack digest review within hours. One-click unpublish. |
| Scraper breaks when IPU site HTML changes | Each source node isolated; a single broken source doesn't block others. Slack alert on consecutive failures. Manual scrape adjustments in n8n UI. |
| Google News RSS returns tangential items | Prompt includes a relevance check: "If the item is not about IPU/GGSIPU admissions, return `skip: true`". Skipped items logged. |
| Internal links point to pages that no longer exist | Whitelist provided to AI is maintained manually; a quarterly check (script scans for 404s in post bodies) flags stale links |
| n8n instance downtime | Daily cadence is forgiving — a missed day is recoverable. For a prolonged outage, the `scripts/build-news.php` build step still works standalone (against manually-provided MD files), so emergency-authored news can still be published via `upload_ftp.py`. |
| Cost runaway on Claude API | Daily cadence caps usage; typical day = ~5 items × ~3K tokens output = modest. Hard daily cap enforced in n8n (skip additional items past 20/day). |
| Template bug breaks all posts at once | Thin-wrapper pattern means template rollback via FTP restores every page instantly. Staging folder for template changes before prod FTP. |

## 15. Open Questions (to resolve during planning)

- Exact cron time (default suggestion: 08:00 IST)
- Slack workspace + user ID for the DM target
- Confirm FTP credentials are available to n8n (or whether n8n reuses an SSH tunnel)
- Confirm IPU n8n instance hosting decision (self-hosted on same infra as KYNE, or fresh hosted instance)
- Internal-link whitelist: which existing ipu.co.in pages should AI be allowed to link to?

## 16. Implementation Outline (handed to writing-plans skill)

Deliverables, in roughly dependency order:

1. `include/news-template.php`, `include/news-card.php`, `include/news-jsonld.php` — the template layer
2. `assets/images/news/*.jpg` — five category stock images
3. `website_download/news/index.php` — list page (takes MD input at build time, but written as a concrete file)
4. Local build script (`scripts/build-news.php` or similar) — reads `content/news/*.md`, writes `website_download/news/*.php` + updated `sitemap.xml` + `llms.txt`. This is the logic n8n will later invoke; having it runnable locally makes testing and manual recovery trivial.
5. Unit/snapshot tests for the template + build script
6. IPU n8n instance provisioning
7. n8n workflow #1: daily scraper + AI rewrite + build + GitHub commit + FTP + Slack digest
8. n8n workflow #2: `unpublish-by-slug` webhook
9. n8n workflow #3: `rebuild-single` webhook (triggered by GitHub push on edit)
10. n8n workflow #4: `suggest-edit` flow (Slack modal → Claude → regenerate)
11. n8n workflow #5: weekly summary
12. End-to-end smoke test against a staging folder
13. Cutover to prod + first-week monitoring
