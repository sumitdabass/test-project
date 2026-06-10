#!/usr/bin/env python3
"""Generate a weekly Search Console report from seo/data/seo.db.

Usage:
    python weekly_report.py                          # latest snapshot
    python weekly_report.py --week-end 2026-05-20    # specific week (matches week_end)
    python weekly_report.py --list                   # list all snapshots in the DB

Writes the report to seo/reports/<week_end>-week-N.md (N = ordinal of this snapshot in DB).
If a previous snapshot exists, the report includes a WoW diff section.
"""

import argparse
import sqlite3
import sys
from datetime import date
from pathlib import Path

REPO_ROOT  = Path(__file__).resolve().parents[2]
DB_PATH    = REPO_ROOT / "seo" / "data" / "seo.db"
REPORT_DIR = REPO_ROOT / "seo" / "reports"

TOP_N = 20


def fmt_pct(x: float) -> str:
    return f"{x*100:.2f}%"


def fmt_delta(x: float, unit: str = "") -> str:
    if x == 0: return f"  0{unit}"
    sign = "+" if x > 0 else ""
    return f"{sign}{x:.2f}{unit}" if isinstance(x, float) else f"{sign}{x}{unit}"


def fetch_snapshot(cur, week_end: str | None):
    if week_end is None:
        row = cur.execute("SELECT id, week_start, week_end, source_file FROM snapshots ORDER BY week_end DESC LIMIT 1").fetchone()
    else:
        row = cur.execute("SELECT id, week_start, week_end, source_file FROM snapshots WHERE week_end=?", (week_end,)).fetchone()
    return row


def fetch_prior(cur, week_end: str):
    return cur.execute(
        "SELECT id, week_start, week_end FROM snapshots WHERE week_end < ? ORDER BY week_end DESC LIMIT 1",
        (week_end,),
    ).fetchone()


def snapshot_ordinal(cur, snapshot_id: int) -> int:
    n = cur.execute("SELECT COUNT(*) FROM snapshots WHERE id<=?", (snapshot_id,)).fetchone()[0]
    return n


def totals(cur, snapshot_id: int) -> dict:
    # Site-wide totals: sc_daily is the only source that includes anonymized queries.
    # SUM(sc_queries) under-counts because Google drops low-volume queries for privacy.
    row = cur.execute(
        "SELECT COALESCE(SUM(clicks),0), COALESCE(SUM(impressions),0) FROM sc_daily WHERE snapshot_id=?",
        (snapshot_id,),
    ).fetchone()
    clicks, impr = row
    ctr = (clicks / impr) if impr else 0.0
    # Daily-weighted avg position from sc_daily (impression-weighted, single value per day).
    pos_row = cur.execute(
        "SELECT COALESCE(SUM(position * impressions) / NULLIF(SUM(impressions),0), 0) FROM sc_daily WHERE snapshot_id=?",
        (snapshot_id,),
    ).fetchone()
    # Coverage: how much of site-wide impressions the visible (non-anonymized) queries account for.
    visible_row = cur.execute(
        "SELECT COALESCE(SUM(clicks),0), COALESCE(SUM(impressions),0) FROM sc_queries WHERE snapshot_id=?",
        (snapshot_id,),
    ).fetchone()
    visible_clicks, visible_impr = visible_row
    coverage_impr = (visible_impr / impr) if impr else 0.0
    return {
        "clicks": clicks,
        "impressions": impr,
        "ctr": ctr,
        "avg_pos": pos_row[0],
        "visible_clicks": visible_clicks,
        "visible_impr": visible_impr,
        "coverage_impr": coverage_impr,
    }


def top_rows(cur, table: str, key_col: str, snapshot_id: int, n: int = TOP_N):
    return cur.execute(
        f"SELECT {key_col}, clicks, impressions, ctr, position FROM {table} "
        f"WHERE snapshot_id=? ORDER BY impressions DESC LIMIT ?",
        (snapshot_id, n),
    ).fetchall()


def diff_queries(cur, current_id: int, prior_id: int, n: int = TOP_N):
    """Return movers: queries present in both snapshots, joined by query name."""
    sql = """
    SELECT c.query,
           c.clicks       AS now_clicks,
           p.clicks       AS prev_clicks,
           c.impressions  AS now_impr,
           p.impressions  AS prev_impr,
           c.position     AS now_pos,
           p.position     AS prev_pos
    FROM sc_queries c
    JOIN sc_queries p
      ON c.query = p.query
     AND p.snapshot_id = ?
    WHERE c.snapshot_id = ?
    """
    return cur.execute(sql, (prior_id, current_id)).fetchall()


def render_table(rows, headers) -> str:
    out = ["| " + " | ".join(headers) + " |", "|" + "|".join(["---"] * len(headers)) + "|"]
    for r in rows:
        out.append("| " + " | ".join(str(c) for c in r) + " |")
    return "\n".join(out)


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--week-end", help="Generate report for the snapshot with this week_end (YYYY-MM-DD)")
    ap.add_argument("--list", action="store_true", help="List all snapshots and exit")
    args = ap.parse_args()

    conn = sqlite3.connect(DB_PATH)
    cur = conn.cursor()

    if args.list:
        for row in cur.execute("SELECT id, week_start, week_end, imported_at FROM snapshots ORDER BY week_end").fetchall():
            print(f"id={row[0]:>3}  {row[1]} -> {row[2]}   (imported {row[3]})")
        return 0

    snap = fetch_snapshot(cur, args.week_end)
    if not snap:
        print(f"ERROR: no snapshot found for week_end={args.week_end}", file=sys.stderr)
        return 2
    snap_id, week_start, week_end, source = snap
    week_n = snapshot_ordinal(cur, snap_id)
    prior  = fetch_prior(cur, week_end)

    cur_totals = totals(cur, snap_id)
    lines = []
    lines.append(f"# ipu.co.in SEO Weekly Report — Week {week_n}")
    lines.append("")
    lines.append(f"**Window:** {week_start} → {week_end} (7 days)")
    lines.append(f"**Source:** `{source}`")
    lines.append(f"**Generated:** {date.today().isoformat()}")
    lines.append("")

    lines.append("## Weekly totals (site-wide, including anonymized queries)")
    lines.append("")
    lines.append(f"- **Clicks:** {cur_totals['clicks']:,}")
    lines.append(f"- **Impressions:** {cur_totals['impressions']:,}")
    lines.append(f"- **Site-wide CTR:** {fmt_pct(cur_totals['ctr'])}")
    lines.append(f"- **Avg position (daily impression-weighted):** {cur_totals['avg_pos']:.2f}")
    lines.append(f"- **Visible-query coverage:** {fmt_pct(cur_totals['coverage_impr'])} of impressions  ({cur_totals['visible_impr']:,} of {cur_totals['impressions']:,})")
    lines.append("")
    lines.append("> Google anonymizes rare queries — the visible query list below covers only the non-anonymized portion. Site totals above are the true picture.")
    lines.append("")

    if prior:
        prior_id, prior_start, prior_end = prior
        prior_totals = totals(cur, prior_id)
        d_clicks = cur_totals["clicks"]      - prior_totals["clicks"]
        d_impr   = cur_totals["impressions"] - prior_totals["impressions"]
        d_ctr    = cur_totals["ctr"]         - prior_totals["ctr"]
        d_pos    = cur_totals["avg_pos"]     - prior_totals["avg_pos"]
        lines.append(f"### Week-over-week vs {prior_start} → {prior_end}")
        lines.append("")
        lines.append(f"- **Clicks delta:** {d_clicks:+,}  (prev {prior_totals['clicks']:,})")
        lines.append(f"- **Impressions delta:** {d_impr:+,}  (prev {prior_totals['impressions']:,})")
        lines.append(f"- **CTR delta:** {d_ctr*100:+.2f}pp  (prev {fmt_pct(prior_totals['ctr'])})")
        lines.append(f"- **Avg-pos delta:** {d_pos:+.2f}  (negative = better)  (prev {prior_totals['avg_pos']:.2f})")
        lines.append("")

    # Top queries
    lines.append(f"## Top {TOP_N} queries by impressions")
    lines.append("")
    rows = top_rows(cur, "sc_queries", "query", snap_id)
    table_rows = [
        (q, f"{c:,}", f"{i:,}", fmt_pct(ctr), f"{p:.2f}")
        for q, c, i, ctr, p in rows
    ]
    lines.append(render_table(table_rows, ["Query", "Clicks", "Impr", "CTR", "Pos"]))
    lines.append("")

    # Top pages
    lines.append(f"## Top {TOP_N} pages by impressions")
    lines.append("")
    rows = top_rows(cur, "sc_pages", "page", snap_id)
    table_rows = [
        (p.replace("https://ipu.co.in", ""), f"{c:,}", f"{i:,}", fmt_pct(ctr), f"{pos:.2f}")
        for p, c, i, ctr, pos in rows
    ]
    lines.append(render_table(table_rows, ["Page", "Clicks", "Impr", "CTR", "Pos"]))
    lines.append("")

    # Country + device split
    lines.append("## Country split")
    lines.append("")
    rows = cur.execute(
        "SELECT country, clicks, impressions, ctr, position FROM sc_countries "
        "WHERE snapshot_id=? ORDER BY impressions DESC LIMIT 5",
        (snap_id,),
    ).fetchall()
    table_rows = [(c, f"{cl:,}", f"{i:,}", fmt_pct(ctr), f"{p:.2f}") for c, cl, i, ctr, p in rows]
    lines.append(render_table(table_rows, ["Country", "Clicks", "Impr", "CTR", "Pos"]))
    lines.append("")

    lines.append("## Device split")
    lines.append("")
    rows = cur.execute(
        "SELECT device, clicks, impressions, ctr, position FROM sc_devices WHERE snapshot_id=? ORDER BY impressions DESC",
        (snap_id,),
    ).fetchall()
    table_rows = [(d, f"{cl:,}", f"{i:,}", fmt_pct(ctr), f"{p:.2f}") for d, cl, i, ctr, p in rows]
    lines.append(render_table(table_rows, ["Device", "Clicks", "Impr", "CTR", "Pos"]))
    lines.append("")

    # WoW movers
    if prior:
        movers = diff_queries(cur, snap_id, prior[0])
        # Sort by abs impression delta desc; cap at top N
        impr_movers = sorted(
            movers, key=lambda r: abs(r[3] - r[4]), reverse=True
        )[:TOP_N]
        lines.append(f"## Top {TOP_N} WoW impression movers (queries in both weeks)")
        lines.append("")
        table_rows = []
        for q, nc, pc, ni, pi, npos, ppos in impr_movers:
            d_impr = ni - pi
            d_pos  = npos - ppos
            table_rows.append((q, f"{pi:,}", f"{ni:,}", f"{d_impr:+,}", f"{ppos:.2f}", f"{npos:.2f}", f"{d_pos:+.2f}"))
        lines.append(render_table(table_rows, ["Query", "Prev Impr", "Now Impr", "ΔImpr", "Prev Pos", "Now Pos", "ΔPos"]))
        lines.append("")

        # Position movers — biggest position drops (rank getting worse)
        pos_movers = sorted(
            movers, key=lambda r: (r[5] - r[6]), reverse=True
        )[:TOP_N]  # +ve delta = worse rank
        lines.append(f"## Top {TOP_N} WoW position LOSERS (rank dropped)")
        lines.append("")
        table_rows = []
        for q, nc, pc, ni, pi, npos, ppos in pos_movers:
            d_pos = npos - ppos
            if d_pos <= 0: continue
            table_rows.append((q, f"{ppos:.2f}", f"{npos:.2f}", f"{d_pos:+.2f}", f"{pi:,}", f"{ni:,}"))
        lines.append(render_table(table_rows, ["Query", "Prev Pos", "Now Pos", "ΔPos", "Prev Impr", "Now Impr"]))
        lines.append("")

    REPORT_DIR.mkdir(parents=True, exist_ok=True)
    out_path = REPORT_DIR / f"{week_end}-week-{week_n}.md"
    out_path.write_text("\n".join(lines))
    print(f"Wrote {out_path}")
    conn.close()
    return 0


if __name__ == "__main__":
    sys.exit(main())
