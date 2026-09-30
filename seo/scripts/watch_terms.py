#!/usr/bin/env python3
"""Watch-terms baseline and stop-loss comparer for the keyword-map programme.

  python3 seo/scripts/watch_terms.py build  <queries.csv> <out.csv>
  python3 seo/scripts/watch_terms.py compare <baseline.csv> <new-queries.csv>
A term is flagged when its average position worsens by more than 2.0 versus baseline,
or when it is absent from the new export (reported as MISSING).
"""
from __future__ import annotations
import csv, sys

WATCH = [
    "ipu", "ggsipu", "ip university", "ipu counselling", "ggsipu counselling",
    "mait", "mait cutoff", "mait cutoff 2026", "mait fees", "msit", "msit cutoff",
    "msit cutoff 2026", "msit janakpuri", "usict", "usict cutoff", "usict placement 2026",
    "usict mca fees", "usar", "usar delhi", "usar cutoff 2025", "usms ipu", "usms mba fees",
    "uslls ba llb fees", "adgitm", "bpit", "bpit fees", "bvp college", "vips", "vips college fees",
    "vips bba fees", "ipu btech", "ipu btech fees", "ipu colleges for btech", "ipu bba fees",
    "ggsipu bba fees", "ipu bcom hons fees", "ipu mba", "ipu mba fees", "ipu ba llb fees",
    "ipu ba llb counselling 2026", "ggsipu bba llb fees", "vips 3 year llb fees",
    "ipu law colleges", "ipu law college", "ipu colleges list", "ipu helpline number",
]


def _read(path):
    with open(path, newline="", encoding="utf-8") as f:
        return {r["Top queries"].strip().lower(): r for r in csv.DictReader(f)}


def build_baseline(queries_csv, out_csv) -> None:
    rows = _read(queries_csv)
    with open(out_csv, "w", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        w.writerow(["term", "impressions", "clicks", "position"])
        for t in WATCH:
            r = rows.get(t)
            if r:
                w.writerow([t, r["Impressions"], r["Clicks"], r["Position"]])


def compare(baseline_csv, new_queries_csv, max_drop: float = 2.0) -> list[dict]:
    new = _read(new_queries_csv)
    out = []
    with open(baseline_csv, newline="", encoding="utf-8") as f:
        for r in csv.DictReader(f):
            n = new.get(r["term"])
            if not n:
                # A term that fell out of the (1,000-row) export is the worst case: report it, never skip it.
                out.append({"term": r["term"], "base_pos": float(r["position"]),
                            "new_pos": None, "drop": None, "missing": True})
                continue
            drop = float(n["Position"]) - float(r["position"])
            if drop > max_drop:
                out.append({"term": r["term"], "base_pos": float(r["position"]),
                            "new_pos": float(n["Position"]), "drop": drop})
    return out


if __name__ == "__main__":
    if len(sys.argv) == 4 and sys.argv[1] == "build":
        build_baseline(sys.argv[2], sys.argv[3])
    elif len(sys.argv) == 4 and sys.argv[1] == "compare":
        bad = compare(sys.argv[2], sys.argv[3])
        for b in bad:
            if b.get("missing"):
                print(f"MISSING {b['term']}: was {b['base_pos']:.2f}, absent from the new export")
            else:
                print(f"REVERT? {b['term']}: {b['base_pos']:.2f} -> {b['new_pos']:.2f} (+{b['drop']:.2f})")
        sys.exit(1 if bad else 0)
    else:
        print(__doc__)
        sys.exit(2)
