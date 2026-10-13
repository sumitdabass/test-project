import json, re, subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
HEAD = (WEB / "include" / "base-head.php").read_text(encoding="utf-8")


def render(component: str, **local_vars) -> str:
    """Render include/components/<component>.php with PHP locals set."""
    assigns = "".join(f"${k}=json_decode({json.dumps(json.dumps(v))},true);" for k, v in local_vars.items())
    code = f"{assigns} include {json.dumps(str(WEB / 'include' / 'components' / (component + '.php')))};"
    return subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB).stdout


def test_box_sizing_covers_pseudo_elements():
    assert "*,*::before,*::after{box-sizing:border-box}" in HEAD.replace(" ", "")


def test_body_grid_css_present():
    css = HEAD.replace(" ", "")
    assert ".ipu-body{" in css
    assert 'grid-template-areas:"intro""aside""rest"' in css
    assert 'grid-template-areas:"introaside""restaside"' in css
    assert ".ipu-body__aside{position:sticky;top:84px}" in css


def test_pulse_animation_removed_from_online_dot():
    assert "ipuPulse" not in HEAD


def test_legacy_mobile_call_gradients_removed_but_layout_kept():
    # base-nav.php owns the sticky bar's look; base-head must keep only layout.
    assert "linear-gradient(135deg,#f59e0b 0%,#FFD700 100%)" not in HEAD
    assert ".mobile-call-cta{position:fixed" in HEAD
    assert "body{padding-bottom:calc(68px" in HEAD


def test_hero_call_button_is_action_orange():
    assert ".ipu-page-hero a.ipu-page-hero__call{background:#ff7a1a;color:#0d1b6e".replace(" ", "") in HEAD.replace(" ", "")


HERO = dict(hero_h1="Test H1", hero_show_form=False, show_trust_bar=False,
            hero_breadcrumbs=[["Home", "/"], ["Colleges", "/ipu-colleges-list.php"], ["BPIT College", ""]])


def test_hero_default_has_no_enquire_button_and_one_h1():
    html = render("page-hero", **HERO)
    assert html.count("<h1") == 1
    assert "#enquiry-form" not in html
    assert 'href="tel:+919899991342"' in html
    assert 'data-cta-src="hero"' in html


def test_hero_enquire_button_opt_in():
    html = render("page-hero", hero_show_enquire=True, **HERO)
    assert 'href="#enquiry-form"' in html and "ipu-page-hero__enquire" in html
    assert html.count('href="#enquiry-form"') == 1


def test_hero_meta_slot_renders_when_set():
    html = render("page-hero", hero_meta="Last Updated: 6 April 2026", **HERO)
    assert '<p class="ipu-page-hero__meta">Last Updated: 6 April 2026</p>' in html
    assert "ipu-page-hero__meta" not in render("page-hero", **HERO)


def test_hero_breadcrumb_unchanged_markup():
    html = render("page-hero", **HERO)
    assert '<a href="/ipu-colleges-list.php">Colleges</a>' in html
    assert '<span aria-current="page">BPIT College</span>' in html


def test_cta_strip_button_is_orange_pill_not_gold_gradient():
    html = render("cta-strip")
    assert "FFD700" not in html and "f59e0b,#FFD700" not in html
    assert 'class="ipu-cta-call"' in html
    assert 'href="tel:+919899991342"' in html and 'data-cta-src="hero"' in html
    assert "Call: 9899991342" in html


def test_cta_strip_card_variant():
    html = render("cta-strip", cta_card=True, cta_heading="Need Admission Guidance?", cta_subtext="x")
    assert 'class="ipu-cta-card"' in html and "Need Admission Guidance?" in html
    assert "<section" not in html


def test_table_wrapper_keeps_header_cell_visible_and_stripes():
    css = HEAD.replace(" ", "")
    for rule in (".ipu-tablewrap thead th:first-child{background:#0d1b6e}",
                 ".ipu-tablewrap tbody td:first-child{background:inherit}"):
        assert rule.replace(" ", "") in css, rule
    # the old rule painted every first cell white, hiding the navy header
    old = ".ipu-tablewrap td:first-child,.ipu-tablewrap th:first-child{position:sticky;left:0;background:#fff}"
    assert old.replace(" ", "") not in css


def test_cta_strip_card_flag_does_not_leak_into_next_include():
    code = (
        '$cta_card=true; include "include/components/cta-strip.php"; '
        'echo "\\n@@SPLIT@@\\n"; include "include/components/cta-strip.php";'
    )
    out = subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB).stdout
    first, second = out.split("@@SPLIT@@")
    assert "ipu-cta-card" in first
    assert "ipu-cta-card" not in second and "<section" in second


def test_cta_card_has_bottom_margin_inside_grid():
    assert ".ipu-body .ipu-cta-card{margin-bottom:28px}" in HEAD.replace("\n", "")


def test_hero_pill_slot():
    html = render("page-hero", hero_pill="Counselling", **HERO)
    assert '<span class="ipu-page-hero__pill">Counselling</span>' in html
    assert "ipu-page-hero__pill" not in render("page-hero", **HERO)


def test_faq_details_schema_can_be_suppressed():
    faqs = [{"question": "Q?", "answer": "A"}]
    assert "FAQPage" in render("faq-details", faqs=faqs)
    html = render("faq-details", faqs=faqs, faq_schema=False)
    assert "FAQPage" not in html and "<details" in html
