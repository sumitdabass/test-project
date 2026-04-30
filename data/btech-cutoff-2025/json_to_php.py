#!/usr/bin/env python3
"""Convert cutoffs.json into website_download/include/data/btech-cutoffs-2025.php
(a return-array PHP file consumed by the cutoff-rounds-table component)."""
import json
from pathlib import Path

HERE = Path(__file__).resolve().parent
SRC = HERE / "cutoffs.json"
OUT = HERE.parent.parent / "website_download" / "include" / "data" / "btech-cutoffs-2025.php"


def php_str(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def render_rank(d):
    if not d:
        return "null"
    return f"['min' => {d['min']}, 'max' => {d['max']}]"


def main() -> None:
    data = json.loads(SRC.read_text(encoding="utf-8"))
    OUT.parent.mkdir(parents=True, exist_ok=True)
    lines = ["<?php",
             "// Auto-generated from data/btech-cutoff-2025/cutoffs.json by json_to_php.py",
             "// Source: GGSIPU 2025-26 B.Tech counselling — Rounds 1, 2, 3",
             "// Schema: $cutoffs[institute][branch][round_N] = ['delhi'=>['min','max'], 'outside'=>['min','max']]",
             "return ["]
    for inst in sorted(data.keys()):
        lines.append(f"  {php_str(inst)} => [")
        for branch in data[inst]:
            lines.append(f"    {php_str(branch)} => [")
            for rkey in sorted(data[inst][branch].keys()):
                rd = data[inst][branch][rkey]
                lines.append(f"      {php_str(rkey)} => ["
                             f"'delhi' => {render_rank(rd.get('delhi'))}, "
                             f"'outside' => {render_rank(rd.get('outside'))}"
                             f"],")
            lines.append("    ],")
        lines.append("  ],")
    lines.append("];")
    OUT.write_text("\n".join(lines) + "\n", encoding="utf-8")
    print(f"wrote {OUT} — {len(data)} institutes")


if __name__ == "__main__":
    main()
