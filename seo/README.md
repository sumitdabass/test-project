# seo/ — ipu.co.in SEO data pipeline

Longitudinal tracking of Google Search Console performance during the mid-session observation window. **No site changes will ship until next admission cycle** — this directory is read-only data collection plus weekly reports for planning.

## Directory layout

```
seo/
├── README.md                         # this file
├── requirements.txt                  # Python deps (openpyxl + Google API client)
├── .venv/                            # gitignored; create via `python3 -m venv seo/.venv`
├── .credentials/                     # gitignored; holds the GCP service account JSON
├── data/
│   └── seo.db                        # SQLite — gitignored; weekly snapshots accumulate here
├── scripts/
│   ├── schema.sql                    # DB schema
│   ├── fetch_sc.py                   # PRIMARY: pulls direct from Google Search Console API
│   ├── ingest_sc.py                  # FALLBACK: ingests a manually-exported .xlsx
│   ├── weekly_report.py              # generates markdown report from DB
│   └── README-gsc-setup.md           # one-time GCP service-account setup (8 minutes)
├── reports/
│   └── YYYY-MM-DD-week-N.md          # one per ingested week
└── baselines/                        # Phase B baseline files (Day-0 + Day-7 stop-loss artifacts)
```

## Weekly cadence (after GCP setup is done)

```bash
cd /Users/Sumit/test-project

# Pull last week's data direct from Google
seo/.venv/bin/python seo/scripts/fetch_sc.py

# Generate the report
seo/.venv/bin/python seo/scripts/weekly_report.py
```

The reports include site-wide totals, top 20 queries/pages, country/device split, and WoW deltas vs the prior snapshot. Once 4 weeks of data exist, we can pull a 4-week trend report and plan next-session changes.

## Until GCP is configured

Manual fallback (export from SC → Excel → ingest):

```bash
seo/.venv/bin/python seo/scripts/ingest_sc.py /path/to/sc-export.xlsx
seo/.venv/bin/python seo/scripts/weekly_report.py
```

## Why SQLite

- Zero infra (no server, no auth, no network)
- File-based, easy to back up / inspect with `sqlite3`
- Schema is small enough that 4 weeks × 1000+ queries × 5 dimensions ≈ < 10 MB
- Trivial to query with `sqlite3 seo/data/seo.db "SELECT ... FROM sc_queries WHERE query LIKE 'ipu%' ORDER BY impressions DESC"`
- Can migrate to MySQL/Postgres later if multi-user access is needed; the schema is portable

## Status — 2026-05-21

- Schema built, DB created
- Week 1 ingested (2026-05-14 → 2026-05-20) via xlsx fallback
- API fetcher built; **awaiting one-time GCP service-account setup** (see `scripts/README-gsc-setup.md`)
- Week 1 report at `reports/2026-05-20-week-1.md`
- Site changes paused until next session (planning mode only)
