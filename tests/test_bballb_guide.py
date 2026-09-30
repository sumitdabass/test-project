from pathlib import Path

PAGE = Path(__file__).resolve().parents[1] / "website_download" / "comprehensive-guide-to-bballb-admission-in-ip-university.php"


def test_fees_section_from_fee_table():
    t = PAGE.read_text()
    assert "BBALLB Fees at IP University" in t
    assert "Rs. 1,45,200 - 2,12,587" in t
    assert "/ipu-fees-structure.php#ballb-fees" in t


def test_title_fixed_single_description_canonical_unchanged():
    # tier-C fix approved by Sumit 2026-09-30 (see seo/rechecks/2026-12-01/decision.md)
    t = PAGE.read_text()
    assert "<title>IPU BBA LLB Admission 2026 – Fees, CLAT Eligibility &amp; Top Colleges</title>" in t
    assert t.count('<meta name="description"') == 1
    assert "BBA LLB IPU Admission 2026: Complete guide to BBA LL.B admission" in t
    assert 'rel="canonical" href="https://ipu.co.in/comprehensive-guide-to-bballb-admission-in-ip-university.php"' in t
