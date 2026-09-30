#!/usr/bin/env python3
"""Fetch Search Console data for ipu.co.in directly from the Google API and store it in seo/data/seo.db.

Usage:
    python fetch_sc.py                    # last 7 days ending 3 days ago (default lag)
    python fetch_sc.py --week-end 2026-05-20
    python fetch_sc.py --start 2026-05-14 --end 2026-05-20
    python fetch_sc.py --force            # replace an existing snapshot for the same window

Requires:
    - seo/.venv with google-api-python-client, google-auth (`pip install -r seo/requirements.txt`)
    - GCP service account JSON key at seo/.credentials/gsc-service-account.json
    - The service account email added as a user on the ipu.co.in property in Search Console
      (one-time setup — see seo/scripts/README-gsc-setup.md)
"""

import argparse
import sqlite3
import sys
from datetime import date, timedelta
from pathlib import Path

from google.oauth2 import service_account
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError

REPO_ROOT = Path(__file__).resolve().parents[2]
DB_PATH   = REPO_ROOT / "seo" / "data" / "seo.db"
CRED_PATH = REPO_ROOT / "seo" / ".credentials" / "gsc-service-account.json"
SITE_URL  = "https://ipu.co.in/"

SCOPES = ["https://www.googleapis.com/auth/webmasters.readonly"]

DIMENSION_TABLE_MAP = {
    "query":   ("sc_queries",   "query"),
    "page":    ("sc_pages",     "page"),
    "country": ("sc_countries", "country"),
    "device":  ("sc_devices",   "device"),
    "date":    ("sc_daily",     "date"),
}


def get_client():
    if not CRED_PATH.exists():
        print(f"ERROR: missing credentials file: {CRED_PATH}", file=sys.stderr)
        print("See seo/scripts/README-gsc-setup.md for one-time setup steps.", file=sys.stderr)
        sys.exit(2)
    creds = service_account.Credentials.from_service_account_file(str(CRED_PATH), scopes=SCOPES)
    return build("searchconsole", "v1", credentials=creds, cache_discovery=False)


def query_dim(svc, dimension: str, start: str, end: str, row_limit: int = 25000):
    """Pull rows for one dimension across the full date range, paginating until exhausted."""
    rows = []
    start_row = 0
    while True:
        body = {
            "startDate": start,
            "endDate":   end,
            "dimensions": [dimension],
            "rowLimit":  row_limit,
            "startRow":  start_row,
            "dataState": "all",
        }
        resp = svc.searchanalytics().query(siteUrl=SITE_URL, body=body).execute()
        chunk = resp.get("rows", [])
        rows.extend(chunk)
        if len(chunk) < row_limit:
            break
        start_row += row_limit
    return rows


def normalize_rows(rows):
    """Convert SC API rows → (key, clicks, impressions, ctr, position) tuples."""
    for r in rows:
        key = r["keys"][0]
        yield (
            str(key).strip(),
            int(r.get("clicks", 0)),
            int(r.get("impressions", 0)),
            float(r.get("ctr", 0.0)),
            float(r.get("position", 0.0)),
        )


def default_window():
    """Last 7 days ending 3 days ago (SC has ~2-3 day data lag)."""
    end   = date.today() - timedelta(days=3)
    start = end - timedelta(days=6)
    return start.isoformat(), end.isoformat()


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--start", help="Start date YYYY-MM-DD (inclusive)")
    ap.add_argument("--end",   help="End date YYYY-MM-DD (inclusive)")
    ap.add_argument("--week-end", help="Pull 7 days ending on this date (YYYY-MM-DD)")
    ap.add_argument("--force", action="store_true", help="Replace an existing snapshot for the same window")
    args = ap.parse_args()

    if args.week_end:
        end = date.fromisoformat(args.week_end)
        start = end - timedelta(days=6)
        start, end = start.isoformat(), end.isoformat()
    elif args.start and args.end:
        start, end = args.start, args.end
    elif args.start or args.end:
        print("ERROR: --start and --end must be used together", file=sys.stderr)
        return 2
    else:
        start, end = default_window()

    print(f"Window: {start} -> {end}")
    print(f"Site:   {SITE_URL}")
    print(f"DB:     {DB_PATH}")

    conn = sqlite3.connect(DB_PATH)
    conn.execute("PRAGMA foreign_keys = ON")
    cur = conn.cursor()

    existing = cur.execute(
        "SELECT id FROM snapshots WHERE week_start=? AND week_end=?",
        (start, end),
    ).fetchone()

    if existing:
        if not args.force:
            print(f"ERROR: snapshot for {start}..{end} already exists (id={existing[0]}). Use --force to replace.", file=sys.stderr)
            return 3
        cur.execute("DELETE FROM snapshots WHERE id=?", (existing[0],))
        print(f"Removed existing snapshot id={existing[0]}")

    svc = get_client()

    try:
        cur.execute(
            "INSERT INTO snapshots (week_start, week_end, source_file) VALUES (?, ?, ?)",
            (start, end, f"gsc-api://{SITE_URL}"),
        )
        snapshot_id = cur.lastrowid
        print(f"Created snapshot id={snapshot_id}")

        total = 0
        for dim, (table, key_col) in DIMENSION_TABLE_MAP.items():
            rows = query_dim(svc, dim, start, end)
            normalized = list(normalize_rows(rows))
            cur.executemany(
                f"INSERT INTO {table} (snapshot_id, {key_col}, clicks, impressions, ctr, position) "
                f"VALUES (?, ?, ?, ?, ?, ?)",
                [(snapshot_id, *r) for r in normalized],
            )
            print(f"  {table:<14} {len(normalized):>6} rows")
            total += len(normalized)

        conn.commit()
        print(f"Done. {total} rows inserted across all tables.")
        return 0

    except HttpError as e:
        conn.rollback()
        print(f"ERROR: Google API call failed: {e}", file=sys.stderr)
        if e.resp.status == 403:
            print("HINT: the service account email might not be added to the ipu.co.in property in Search Console.", file=sys.stderr)
            print("      See seo/scripts/README-gsc-setup.md step 5.", file=sys.stderr)
        return 4
    finally:
        conn.close()


if __name__ == "__main__":
    sys.exit(main())
