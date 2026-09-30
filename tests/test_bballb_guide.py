from pathlib import Path

PAGE = Path(__file__).resolve().parents[1] / "website_download" / "comprehensive-guide-to-bballb-admission-in-ip-university.php"


def test_fees_section_from_fee_table():
    t = PAGE.read_text()
    assert "BBALLB Fees at IP University" in t
    assert "Rs. 1,45,200 - 2,12,587" in t
    assert "/ipu-fees-structure.php#ballb-fees" in t


def test_protected_head_elements_unchanged():
    t = PAGE.read_text()
    assert "<title>Comprehensive Guide to BBALLB Admission in IP University (IPU) : Eligibility, Counselling, Top Colleges, and CLAT Process  Meta</title>" in t
    assert 'rel="canonical" href="https://ipu.co.in/comprehensive-guide-to-bballb-admission-in-ip-university.php"' in t
