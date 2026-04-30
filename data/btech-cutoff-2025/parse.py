#!/usr/bin/env python3
"""Parse round-1/2/3 raw paste files into a single cutoffs.json keyed by
institute → branch → {round_n: {delhi: {min,max}, outside: {min,max}}}."""
from __future__ import annotations

import json
import re
from collections import defaultdict
from pathlib import Path

HERE = Path(__file__).resolve().parent
ROUND_FILES = {1: HERE / "round-1.txt", 2: HERE / "round-2.txt", 3: HERE / "round-3.txt"}
RANK_RE = re.compile(r"Min\s*Rank\s*-?\s*(\d+)\s*Max\s*Rank\s*-?\s*(\d+)", re.I)


def parse_rank_cell(cell: str) -> dict | None:
    """'Min Rank - 99288 Max Rank - 160820' → {'min': 99288, 'max': 160820}."""
    cell = cell.strip().strip('"').replace("\n", " ")
    if not cell:
        return None
    m = RANK_RE.search(cell)
    if not m:
        return None
    return {"min": int(m.group(1)), "max": int(m.group(2))}


def parse_round(path: Path) -> dict:
    """institute → list of {branch, delhi, outside}."""
    out: dict[str, list[dict]] = defaultdict(list)
    raw = path.read_text(encoding="utf-8")
    lines = raw.splitlines()
    for ln in lines[1:]:  # skip header
        if not ln.strip():
            continue
        parts = ln.split("\t")
        if len(parts) < 4:
            continue
        institute, branch, delhi_cell, outside_cell = parts[0].strip(), parts[1].strip(), parts[2], parts[3]
        if not institute or not branch:
            continue
        delhi = parse_rank_cell(delhi_cell)
        outside = parse_rank_cell(outside_cell)
        if not (delhi or outside):
            continue
        out[institute].append({"branch": branch, "delhi": delhi, "outside": outside})
    return out


def main() -> None:
    # institute → branch → {round_n: {delhi, outside}}
    merged: dict[str, dict[str, dict]] = defaultdict(lambda: defaultdict(dict))
    counts: dict[int, int] = {}
    for rnd, path in ROUND_FILES.items():
        if not path.exists():
            print(f"warn: {path} missing")
            counts[rnd] = 0
            continue
        round_data = parse_round(path)
        rows = 0
        for institute, branches in round_data.items():
            for entry in branches:
                merged[institute][entry["branch"]][f"round_{rnd}"] = {
                    "delhi": entry["delhi"],
                    "outside": entry["outside"],
                }
                rows += 1
        counts[rnd] = rows

    # convert defaultdicts to plain dicts for JSON
    final = {inst: {br: dict(rounds) for br, rounds in branches.items()} for inst, branches in merged.items()}

    out_path = HERE / "cutoffs.json"
    out_path.write_text(json.dumps(final, indent=2, ensure_ascii=False), encoding="utf-8")
    print(f"wrote {out_path} — {len(final)} institutes")
    for rnd, n in counts.items():
        print(f"  round {rnd}: {n} branch rows parsed")
    print("\nInstitutes:")
    for inst in sorted(final.keys()):
        n_branches = len(final[inst])
        print(f"  {inst}: {n_branches} branches")


if __name__ == "__main__":
    main()
