import csv, sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "seo" / "scripts"))
import watch_terms as wt

def _q(path, rows):
    with open(path, "w", newline="") as f:
        w = csv.writer(f)
        w.writerow(["Top queries", "Clicks", "Impressions", "CTR", "Position"])
        for r in rows:
            w.writerow(r)

def test_build_and_compare_flags_only_drops_over_two(tmp_path):
    base_q, new_q, base = tmp_path / "b.csv", tmp_path / "n.csv", tmp_path / "base.csv"
    _q(base_q, [["mait cutoff", 11, 3741, "0.3%", 8.87], ["usar", 48, 9384, "0.5%", 9.13]])
    _q(new_q, [["mait cutoff", 20, 3000, "0.7%", 12.0], ["usar", 50, 9000, "0.6%", 8.0]])
    wt.WATCH[:] = ["mait cutoff", "usar", "not in export"]
    wt.build_baseline(base_q, base)
    bad = wt.compare(base, new_q)
    assert [b["term"] for b in bad] == ["mait cutoff"]
    assert round(bad[0]["drop"], 2) == 3.13

def test_missing_term_is_reported_as_missing(tmp_path):
    # A term that falls out of the (1,000-row) GSC export is the worst stop-loss case: it must be reported, not skipped.
    base_q, new_q, base = tmp_path / "b.csv", tmp_path / "n.csv", tmp_path / "base.csv"
    _q(base_q, [["usar", 48, 9384, "0.5%", 9.13]])
    _q(new_q, [])
    wt.WATCH[:] = ["usar"]
    wt.build_baseline(base_q, base)
    out = wt.compare(base, new_q)
    assert len(out) == 1 and out[0]["term"] == "usar" and out[0].get("missing") is True


def test_watch_list_covers_adgips_test_terms():
    import importlib; importlib.reload(wt)
    for term in ("adgitm", "dr akhilesh das gupta institute of technology and management"):
        assert term in wt.WATCH
