import re, subprocess, json
from pathlib import Path

WEB = Path(__file__).resolve().parents[1] / "website_download"
PAGE = WEB / "ipu-fees-structure.php"


def text():
    return PAGE.read_text()


def row(label_fragment):
    m = re.search(r"<tr[^>]*>(?:(?!</tr>).)*?" + re.escape(label_fragment) + r".*?</tr>", text(), re.S)
    assert m, label_fragment
    return re.sub(r"<[^>]+>", " | ", m.group(0))


def test_affiliated_btech_row_uses_2026_27_notices():
    r = row("affiliated colleges, e.g. BPIT / BVCOE")
    assert "1,65,770" in r and "2,20,640" in r and "7.69" in r and "Affiliated" in r
    assert "1,55,700" not in text()          # stale 2025-26 figure gone from the whole page


def test_four_year_total_matches_the_published_schedule():
    # BVCOE 2026-27 batch academic fees: years 1-4
    assert 165770 + 182347 + 200582 + 220640 == 769339   # about Rs. 7.69 lakh


def test_new_affiliated_rows_present_and_usms_row_keeps_anchor():
    assert "1,74,000 - 2,03,720" in row("MBA (affiliated colleges")
    assert "1,28,500" in row("BA LLB / BBA LLB (affiliated")
    assert re.search(r'<td id="mba-fees"[^>]*>MBA</td>', text())
    assert re.search(r'<td id="ballb-fees"[^>]*>BA LLB / BBA LLB</td>', text())


def test_sources_are_stated():
    t = text()
    src = t[t.index("<h2>Source of Data</h2>"):]
    assert "BPIT" in src and "BVCOE" in src and "23.07.2025" in src


def test_title_meta_h1_untouched_tier_c_needs_approval():
    t = text()
    assert "<title>IPU Fee Structure 2026 – B.Tech, BBA, Law, MBA, BCA, B.Com Fees</title>" in t
    assert 'content="IP University (GGSIPU) Fee Structure 2026 – B.Tech Rs.1.55L, BBA Rs.1.2L, BA LLB Rs.1.45L, MBA Rs.1.3L, BCA Rs.80K. Official brochure fees. Call 9899991342."' in t


def test_bvcoe_dataset_matches_published_last_admission_ranks():
    # Cross-check: Round 3 Delhi Max Rank in btech-cutoffs-2025.php == BVCOE AICTE disclosure 2025-26 last rank.
    code = f"""<?php $d = include '{WEB}/include/data/btech-cutoffs-2025.php'; $k = 'Bharati Vidyapeeths College of Engineering';
      $o = []; foreach ($d[$k] as $b => $r) {{ $o[$b] = $r['round_3']['delhi']['max']; }} echo json_encode($o);"""
    got = json.loads(subprocess.run(["php"], input=code, text=True, capture_output=True).stdout)
    expected = {"Computer Science & Engineering": 159959, "Electronics & Communication Engineering": 257480,
                "Information Technology": 188921, "Electrical & Electronics Engineering": 325165}
    for branch, rank in expected.items():
        assert got[branch] == rank, branch
    ice = [v for k, v in got.items() if k.startswith("Instrumentation")][0]
    assert ice == 377275          # disclosure prints 377275 for ICE
    facts = json.loads((WEB / "include/data/college-facts-2026.json").read_text())
    assert "159,959 (2025-26)" in facts["bvp"]["last_rank_cse"]["v"]
