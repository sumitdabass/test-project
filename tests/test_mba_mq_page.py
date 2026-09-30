import re
from pathlib import Path

WEB = Path(__file__).resolve().parents[1] / "website_download"
PAGE = WEB / "mba-management-quota-ipu.php"


def test_sourced_sections_present_and_match_the_page_schema():
    text = PAGE.read_text()
    for h in ("How Many MBA Management Quota Seats Are There at IPU?",
              "Is an Entrance Score Mandatory for MBA Management Quota?",
              "MBA Fees at IPU"):
        assert f"<h2>{h}</h2>" in text, h
    # visible copy must state the same sourced facts the FAQ schema states
    assert "10% of the total seats" in text and "Section 12(1)(a)" in text
    assert "CAT, CMAT or GGSIPU CET" in text
    assert "Rs. 1,30,000" in text


def test_page_has_at_least_five_inbound_links():
    hits = [p.name for p in WEB.glob("*.php")
            if p.name != PAGE.name and re.search(r"mba-management-quota-ipu\.php", p.read_text())]
    assert len(hits) >= 5, hits


def test_protected_head_elements_unchanged():
    text = PAGE.read_text()
    assert "<title>MBA Management Quota Admission in IP University (GGSIPU) 2026 | USMS, MAIT, MSIT MBA Guide</title>" in text
    assert 'rel="canonical" href="https://ipu.co.in/mba-management-quota-ipu.php"' in text
    assert "$hero_h1 = 'MBA Management Quota Admission in IP University (GGSIPU)';" in text
