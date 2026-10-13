from pathlib import Path
import xml.etree.ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
SM = ROOT / "website_download" / "sitemap.xml"
NEW = ["economics-admission-ip-university", "cuet-admission-ipu", "cuet-btech-admission-ipu",
       "cuet-bba-admission-ipu", "cuet-bcom-admission-ipu", "cuet-law-admission-ipu",
       "law-3-year-admission-ipu", "mca-admission-ipu"]


def locs():
    ns = {"s": "http://www.sitemaps.org/schemas/sitemap/0.9"}
    return [e.text for e in ET.parse(SM).getroot().findall("s:url/s:loc", ns)]


def test_new_pages_listed_once():
    L = locs()
    for slug in NEW:
        assert L.count(f"https://ipu.co.in/{slug}.php") == 1, slug


def test_noindex_privacy_policy_excluded():
    assert not any("privacy-policy" in u for u in locs())


def test_valid_and_unique():
    L = locs()
    assert len(L) == len(set(L))
    assert all(u.startswith("https://ipu.co.in/") for u in L)
