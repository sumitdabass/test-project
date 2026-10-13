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
