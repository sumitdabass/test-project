import sys
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from seo_verify import check_html

GOOD = """<html><head><title>MAIT Cutoff – JEE Main Closing Ranks by Branch</title>
<link rel="canonical" href="https://ipu.co.in/mait-cutoff.php">
<meta name="description" content="MAIT cutoff by branch and round.">
<script type="application/ld+json">{"@type":"BreadcrumbList","itemListElement":[]}</script>
<script type="application/ld+json">{"@type":"FAQPage","mainEntity":[
{"@type":"Question","name":"Q1","acceptedAnswer":{"@type":"Answer","text":"A1"}},
{"@type":"Question","name":"Q2","acceptedAnswer":{"@type":"Answer","text":"A2"}},
{"@type":"Question","name":"Q3","acceptedAnswer":{"@type":"Answer","text":"A3"}}]}</script>
</head><body><h1>MAIT Cutoff</h1><div id="enquiry-form"></div>
<a href="tel:+919899991342">Call</a></body></html>"""

def test_good_page_passes():
    assert check_html(GOOD) == []

def test_two_h1_fails():
    assert any("h1" in p for p in check_html(GOOD.replace("</body>", "<h1>x</h1></body>")))

def test_missing_canonical_fails():
    bad = GOOD.replace('<link rel="canonical" href="https://ipu.co.in/mait-cutoff.php">', "")
    assert any("canonical" in p for p in check_html(bad))

def test_two_descriptions_fail():
    bad = GOOD.replace("</head>", '<meta name="description" content="dup"></head>')
    assert any("meta description" in p for p in check_html(bad))

def test_two_forms_fail():
    assert any("enquiry" in p for p in check_html(GOOD.replace("</body>", '<div id="enquiry-form"></div></body>')))

def test_two_breadcrumbs_fail():
    bc = '<script type="application/ld+json">{"@type":"BreadcrumbList"}</script>'
    assert any("BreadcrumbList" in p for p in check_html(GOOD.replace("</head>", bc + "</head>")))

def test_bad_json_ld_fails():
    bad = GOOD.replace("</head>", '<script type="application/ld+json">{oops}</script></head>')
    assert any("JSON-LD" in p for p in check_html(bad))

def test_bare_tel_fails():
    assert any("tel:" in p for p in check_html(GOOD.replace("tel:+919899991342", "tel:9899991342")))

def test_phone_in_faq_only_fails_when_strict():
    bad = GOOD.replace('"text":"A1"', '"text":"Call 9899991342"')
    assert check_html(bad) == []
    assert any("FAQ" in p for p in check_html(bad, strict_faq_phone=True))

def test_short_faq_fails():
    bad = GOOD.replace('{"@type":"Question","name":"Q3","acceptedAnswer":{"@type":"Answer","text":"A3"}}',
                       '{"@type":"Question","name":"Q2b","acceptedAnswer":{"@type":"Answer","text":"A2b"}}').replace(
                       ',\n{"@type":"Question","name":"Q2","acceptedAnswer":{"@type":"Answer","text":"A2"}}', "")
    assert any("FAQPage" in p for p in check_html(bad))

def test_tel_in_css_or_js_selector_is_not_a_bare_link():
    sel = GOOD.replace("</body>", '<style>a[href^="tel:"]{color:red}</style><script>e.closest(\'a[href^="tel:"]\')</script></body>')
    assert check_html(sel) == []


def test_two_faqpage_blocks_fail():
    second = ('<script type="application/ld+json">{"@type":"FAQPage","mainEntity":['
              '{"@type":"Question","name":"Z1","acceptedAnswer":{"@type":"Answer","text":"a"}},'
              '{"@type":"Question","name":"Z2","acceptedAnswer":{"@type":"Answer","text":"a"}},'
              '{"@type":"Question","name":"Z3","acceptedAnswer":{"@type":"Answer","text":"a"}}]}</script>')
    assert any("FAQPage blocks" in p for p in check_html(GOOD.replace("</head>", second + "</head>")))
