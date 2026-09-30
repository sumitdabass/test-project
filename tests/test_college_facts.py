import json, subprocess, textwrap
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
FACTS = ROOT / "website_download" / "include" / "data" / "college-facts-2026.json"
COMPONENT = ROOT / "website_download" / "include" / "components" / "college-facts-faq.php"


def test_every_field_has_source_and_as_of():
    data = json.loads(FACTS.read_text())
    for key, college in data.items():
        for field, val in college.items():
            if field in ("short", "name"):
                continue
            items = val if isinstance(val, list) else [val]
            for item in items:
                assert item.get("source"), f"{key}.{field} missing source"
                assert item.get("as_of"), f"{key}.{field} missing as_of"


def _run(facts, key):
    code = textwrap.dedent(f"""\
      <?php $faqs = []; $facts_key = '{key}'; $facts_file_override = '{facts}';
      include '{COMPONENT}'; echo json_encode($faqs);""")
    return json.loads(subprocess.run(["php"], input=code, text=True, capture_output=True).stdout)


def test_component_skips_unsourced_fields(tmp_path):
    f = tmp_path / "f.json"
    f.write_text(json.dumps({"x": {"short": "X", "name": "X College",
        "campus_area_acres": {"v": 5},                      # no source -> must be skipped
        "highest_package_lpa": {"v": 30, "source": "https://example.test", "as_of": "2026-09-01"}}}))
    out = _run(f, "x")
    assert len(out) == 1 and "30" in out[0]["answer"] and "9899991342" not in out[0]["answer"]


def test_component_empty_when_key_missing(tmp_path):
    f = tmp_path / "f.json"; f.write_text("{}")
    assert _run(f, "nope") == []


def test_note_avg_package_and_accreditation_render(tmp_path):
    f = tmp_path / "f.json"
    f.write_text(json.dumps({"x": {"short": "X", "name": "X College",
        "avg_package_lpa": {"v": 7.1, "note": "2025 batch, all branches", "source": "https://example.test/p", "as_of": "2026-09-30"},
        "accreditation": {"v": "NAAC 'A' grade; NBA for CSE and IT", "source": "https://example.test/a", "as_of": "2026-09-30"}}}))
    out = _run(f, "x")
    qs = {o["question"]: o["answer"] for o in out}
    assert "7.1 LPA" in qs["What is the average package at X?"]
    assert "2025 batch, all branches" in qs["What is the average package at X?"]
    assert "NAAC 'A' grade" in qs["What accreditation does X have?"]
    assert all("9899991342" not in a for a in qs.values())


def test_intake_and_fee_note_render(tmp_path):
    f = tmp_path / "f.json"
    f.write_text(json.dumps({"x": {"short": "X", "name": "X College",
        "intake": {"v": [{"programme": "B.Tech CSE", "seats": "300"}, {"programme": "B.Tech IT", "seats": "240"}],
                   "source": "https://example.test/about", "as_of": "2026-09-30"},
        "fees": [{"course": "B.Tech", "per_year_inr": 160100, "note": "first-year fee, 2026-27",
                  "source": "https://example.test/fees", "as_of": "2026-09-30"}]}}))
    qs = {o["question"]: o["answer"] for o in _run(f, "x")}
    assert "B.Tech CSE 300" in qs["What is the intake at X?"] and "B.Tech IT 240" in qs["What is the intake at X?"]
    fee = qs["What are the B.Tech fees at X?"]
    assert "1,60,100" in fee and "first-year fee, 2026-27" in fee
    assert all("9899991342" not in a for a in qs.values())
