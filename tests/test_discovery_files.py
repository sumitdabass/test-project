import re
import xml.etree.ElementTree as ET
from pathlib import Path

WEB = Path(__file__).resolve().parents[1] / "website_download"
NEW = ["mait-cutoff.php", "msit-cutoff.php", "mbs-college-admission.php", "ipu-colleges-in-dwarka.php"]


def test_sitemap_is_well_formed_unique_and_has_new_pages():
    tree = ET.parse(WEB / "sitemap.xml")
    locs = [e.text.strip() for e in tree.iter() if e.tag.endswith("loc")]
    assert len(locs) == len(set(locs)), "duplicate sitemap URLs"
    for p in NEW:
        assert f"https://ipu.co.in/{p}" in locs, p
    for loc in locs:
        assert loc.startswith("https://ipu.co.in/"), loc


def test_llms_txt_lists_new_pages():
    text = (WEB / "llms.txt").read_text()
    for p in NEW:
        assert f"https://ipu.co.in/{p}" in text or f"/{p}" in text, p


def test_llms_txt_does_not_place_usar_in_dwarka():
    text = (WEB / "llms.txt").read_text()
    m = re.search(r"### USAR[^\n]*\nURL:[^\n]*\nLocation: ([^\n]*)", text)
    assert m and "Surajmal Vihar" in m.group(1), m and m.group(1)
    row = re.search(r"\| \d+ \| USAR \|[^\n]*", text).group(0)
    assert "Dwarka" not in row and "Surajmal Vihar" in row, row


def test_every_new_page_has_at_least_three_inbound_links():
    import re as _re
    pages = list(WEB.glob("*.php"))
    for p in NEW:
        hits = [q.name for q in pages if q.name != p and _re.search(rf"""(href=["']|'url' => ')/?{_re.escape(p)}""", q.read_text())]
        assert len(hits) >= 3, (p, hits)
