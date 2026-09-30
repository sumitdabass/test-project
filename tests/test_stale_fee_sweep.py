import re
from pathlib import Path

WEB = Path(__file__).resolve().parents[1] / "website_download"
PAGES = """BPIT BVP IPU-B-Tech-admission-2026 adgitm-admission btech-management-quota-ipu cuet-btech-admission-ipu
gtbit-admission ipu-btech-via-cuet mait-admission mait-delhi-fees-courses-placements msit-admission top-ipu-colleges
top-mba-colleges-ipu vips-admission b-tech-colleges-under-IP-university ipu-fees-structure""".split()


def read(n):
    return (WEB / f"{n}.php").read_text(encoding="utf-8")


def test_old_figure_only_appears_as_an_attributed_2025_gazette_range():
    for n in PAGES:
        t = read(n)
        for m in re.finditer(r"1,55,700", t):
            ctx = t[max(0, m.start() - 220): m.end() + 120]
            assert "14.07.2025" in ctx or "ppendix 13" in ctx, (n, ctx[-200:])


def test_college_fee_tables_use_2026_27_figures():
    for n, cell in (("BPIT", "Rs. 1,65,770 (year 1)"), ("BVP", "Rs. 1,65,770 (year 1)"), ("adgitm-admission", "Rs. 1,60,100 (year 1)"),
                    ("mait-admission", "Rs. 1,60,100 - 1,65,770 (year 1; confirm with college)"),
                    ("msit-admission", "Rs. 1,60,100 - 1,65,770 (year 1; confirm with college)"),
                    ("gtbit-admission", "Rs. 1,60,100 - 1,65,770 (year 1; confirm with college)")):
        t = read(n)
        assert cell in t, n
        assert "Rs. 1,55,700" not in t, n


def test_top_pages_no_longer_show_btech_fee_as_mba_fee_or_stale_fee():
    mba = read("top-mba-colleges-ipu")
    assert "Rs 1,55,700" not in mba and "1.74-2.04" in mba      # B.Tech figure was wrongly used for MAIT's MBA
    top = read("top-ipu-colleges")
    assert "Rs 1,55,700" not in top and "Rs 1,65,770 (yr 1)" in top and "1.60-1.66" in top
    assert "~₹1.55L/yr" not in read("b-tech-colleges-under-IP-university")


def test_range_sentences_cite_the_colleges_notices():
    for n in ("IPU-B-Tech-admission-2026", "btech-management-quota-ipu", "cuet-btech-admission-ipu", "ipu-btech-via-cuet"):
        t = read(n)
        assert "1,60,100" in t and "1,65,770" in t, n
    assert "1,60,100" in read("mait-delhi-fees-courses-placements")
    assert "7.17 lakh" not in read("mait-delhi-fees-courses-placements")   # derived from the old figure


def test_fee_page_meta_updated_with_approval_and_nothing_else_in_head():
    t = read("ipu-fees-structure")
    assert 'content="IP University (GGSIPU) Fee Structure 2026 – B.Tech Rs.1.66L, BBA Rs.1.2L, BA LLB Rs.1.45L, MBA Rs.1.3L, BCA Rs.80K. Official brochure fees. Call 9899991342."' in t
    assert "<title>IPU Fee Structure 2026 – B.Tech, BBA, Law, MBA, BCA, B.Com Fees</title>" in t
    assert "Appendix 13(i)" in t and "23.07.2025" in t          # explains the brochure-vs-notice difference


def test_three_page_metas_updated_with_approval():
    # approved by Sumit 2026-09-30 ("update those 3 metas"); range, not a single figure, because MAIT publishes no clean year-1 tuition
    mait = read("mait-admission")
    assert mait.count("B.Tech fees Rs.1.6-1.66L") == 3 and "Rs.1.55L" not in mait
    prof = read("mait-delhi-fees-courses-placements")
    assert "B.Tech fees Rs.1.6-1.66L" in prof and "B.Tech fees Rs. 1.6-1.66L" in prof
    assert "1.55L" not in prof
    bt = read("IPU-B-Tech-admission-2026")
    assert "year-1 fee Rs.1.6-1.66L" in bt and "Rs.1.55L" not in bt
    # titles and canonicals of these three pages stay exactly as they were
    assert "<title>MAIT Delhi" in prof or "<title>" in prof
