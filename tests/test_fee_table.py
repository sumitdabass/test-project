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
    assert "Rs. 1,55,700" not in r                                   # stale figure gone from the table row itself


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


def test_title_unchanged_and_meta_is_the_approved_one():
    t = text()
    assert "<title>IPU Fee Structure 2026 – B.Tech, BBA, Law, MBA, BCA, B.Com Fees</title>" in t
    assert 'content="IP University (GGSIPU) Fee Structure 2026 – B.Tech Rs.1.66L, BBA Rs.1.2L, BA LLB Rs.1.45L, MBA Rs.1.94L, BCA Rs.80K. Official brochure fees. Call 9899991342."' in t   # approved by Sumit 2026-09-30


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


def test_usms_mba_fee_matches_pg_brochure():
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    assert "Rs. 1,93,600 (2026-27)" in t and "Rs. 2,12,960" in t
    assert "is Rs. 1,30,000 per year. The total 2-year MBA cost" not in t


def test_usicts_mca_and_usllss_llb_fees_match_pg_brochure():
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    assert "Rs. 1,45,200 (2026-27)" in t                      # MCA at USICT
    assert "Rs. 1,45,200 (yr 1); Rs. 1,93,220 (yr 2); Rs. 2,09,192 (yr 3)" in t
    assert "Rs. 1,30,000</td><td style=\"padding:10px 14px\">~Rs. 3.9 L" not in t
    assert "LLM Rs. 1,30,000" not in t


def test_ug_programme_durations_are_four_years_per_nep():
    import re
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    for name in ("BBA", "BCA", "B.Com (Hons)", "BJMC"):
        assert re.search(re.escape(name) + r"</td><td[^>]*>4 Yrs</td>", t), name
    assert "3-year BBA" not in t and "Rs. 4.3-5.2 lakh" not in t


def test_law_admission_page_has_no_unsourced_llm_fee():
    t = open("website_download/IPU-Law-Admission.php", encoding="utf-8").read()
    assert "Rs. 1,30,000 &ndash; Rs. 1,50,000 per year" not in t
    assert "/llm-admission-ipu.php" in t


def test_barch_and_mtech_totals_match_the_sourced_schedules():
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    # B.Arch 5-year tuition per barch-admission-ipu.php: 1,69,400 + 1,86,340 + 2,04,974 + 2,25,471 + 2,48,019
    assert 1_69_400 + 1_86_340 + 2_04_974 + 2_25_471 + 2_48_019 == 10_34_204
    assert "~Rs. 10.3 L" in t and "~Rs. 9.5 L" not in t
    assert "~Rs. 3.55 L" in t and "~Rs. 3.4 L" not in t


def test_mbbs_row_has_no_unsourced_lakh_range():
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    assert "Rs. 15 - 30 L" not in t and "Rs. 15-30 lakh" not in t and "75 L - 1.5 Cr" not in t
    assert "Rs. 25,000 university charges per year" in t


def test_mbbs_matches_the_sfrc_gazette_and_ug_ranges_kept_at_sumits_direction():
    # Sumit 2026-09-30: keep the BBA/BCA/B.Com/BJMC ranges as they were. Note: brochure Appendix 13(i)
    # (SFRC gazette 14.07.2025) lists lower 2025-26 fees (BBA 69,400-1,15,300 etc.); ranges kept by owner decision.
    t = open("website_download/ipu-fees-structure.php", encoding="utf-8").read()
    assert "Rs. 1,20,000 - 1,50,000" in t and "Rs. 80,000 - 1,50,000" in t
    assert "Army College of Medical Sciences: Rs. 5,55,700" in t
    assert "service bond" in t
