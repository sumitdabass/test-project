# Google Search Console API — one-time setup

This is the **one-time** setup so `fetch_sc.py` can pull Search Console data autonomously without you exporting xlsx files every week. Estimated time: 8 minutes.

After this is done, weekly fetches are a single command:
```bash
seo/.venv/bin/python seo/scripts/fetch_sc.py
```

## 1. Create a Google Cloud project (or reuse one)

Go to https://console.cloud.google.com/ → top bar → project dropdown → **New Project**.
- Name: `ipu-seo-fetch` (or reuse `ipu-news-scraper` if you already have a GCP project for the news pipeline).
- Note the **Project ID**.

## 2. Enable the Search Console API

In the same project: https://console.cloud.google.com/apis/library/searchconsole.googleapis.com → click **Enable**.

## 3. Create a service account

https://console.cloud.google.com/iam-admin/serviceaccounts → **+ Create service account**.
- Name: `ipu-seo-reader`
- Description: `Reads Search Console data for ipu.co.in`
- No roles needed at the project level (SC access is granted in step 5).
- **Done** (skip the "grant users access" step).

## 4. Download the service account JSON key

On the service account you just created → **Keys** tab → **Add key → Create new key → JSON → Create**. Browser downloads a file like `ipu-seo-fetch-abc123.json`.

Save it to this exact path (the script looks for it here):
```
/Users/Sumit/test-project/seo/.credentials/gsc-service-account.json
```

```bash
mkdir -p seo/.credentials
mv ~/Downloads/ipu-seo-fetch-*.json seo/.credentials/gsc-service-account.json
chmod 600 seo/.credentials/gsc-service-account.json
```

`.credentials/` is gitignored — the key never leaves your laptop.

## 5. Grant the service account read access in Search Console

Copy the service account **email** (looks like `ipu-seo-reader@ipu-seo-fetch.iam.gserviceaccount.com`).

Open Search Console: https://search.google.com/search-console → property **https://ipu.co.in/** → **Settings (left nav) → Users and permissions → Add user**.
- Email: paste the service account email
- Permission: **Restricted** (read-only, enough for fetching)
- Confirm.

## 6. Verify

```bash
seo/.venv/bin/python seo/scripts/fetch_sc.py --start 2026-05-14 --end 2026-05-20 --force
```

Expected output:
```
Window: 2026-05-14 -> 2026-05-20
Site:   https://ipu.co.in/
Created snapshot id=2
  sc_queries     ~1000 rows
  sc_pages       ~55 rows
  ...
Done. ~1200 rows inserted.
```

If you get a **403**: the service account email isn't on the SC property yet — go back to step 5.
If you get a **404 on site**: the property URL might be the domain property (`sc-domain:ipu.co.in`) instead of `https://ipu.co.in/` — update `SITE_URL` in `fetch_sc.py`.

## Weekly cadence

After setup, every Monday morning (or any day after the previous Sunday's data lands):
```bash
cd /Users/Sumit/test-project
seo/.venv/bin/python seo/scripts/fetch_sc.py        # default: last 7 days ending 3 days ago
seo/.venv/bin/python seo/scripts/weekly_report.py   # generates markdown report with WoW diff
```

Or wire it to a cron / launchd / n8n schedule.
