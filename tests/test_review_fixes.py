import json, re
from pathlib import Path

WEB = Path(__file__).resolve().parents[1] / "website_download"


def read(name):
    return (WEB / name).read_text()


def test_bpit_campus_sentence_has_no_doubled_word():
    facts = json.loads(read("include/data/college-facts-2026.json"))
    assert facts["bpit"]["campus_area_acres"]["v"] == 6
    import subprocess, textwrap
    code = textwrap.dedent(f"""\
      <?php $faqs = []; $facts_key = 'bpit'; include '{WEB}/include/components/college-facts-faq.php';
      echo json_encode($faqs);""")
    out = json.loads(subprocess.run(["php"], input=code, text=True, capture_output=True).stdout)
    answers = " ".join(o["answer"] for o in out)
    assert "BPIT's campus covers about 6 acres." in answers
    assert "about about" not in answers


def test_fee_answers_carry_the_table_scope_qualifier():
    # ipu-fees-structure.php Type column: law + LLM rows are USLLS (university school); affiliated colleges differ.
    pattern = re.compile(r"At USLLS[^<]{0,200}?Rs\. 1,(?:45,200|30,000).{0,700}?[Aa]ffiliated", re.S)
    for page in ("IPU-Law-Admission.php", "top-law-colleges-ipu.php", "law-3-year-admission-ipu.php",
                 "llm-admission-ipu.php", "comprehensive-guide-to-bballb-admission-in-ip-university.php"):
        assert pattern.search(read(page)), page


def test_mba_mq_page_does_not_present_usms_fee_as_quota_fee():
    t = read("mba-management-quota-ipu.php")
    sec = t[t.index("<h2>MBA Fees at IPU</h2>"): t.index("<h2>Need Help with MBA Admission?</h2>")]
    assert "USMS" in sec and "affiliated" in sec.lower()


def test_conflicting_fee_faqs_not_added_to_bba_and_bcom_pages():
    assert "What are IPU BBA fees?" not in read("comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php")
    assert "What are IPU B.Com Hons fees?" not in read("bcom-admission-ipu.php")


def test_bba_guide_has_single_faqpage_block():
    t = read("comprehensive-guide-to-bba-colleges-under-ip-university-top-10-institutions.php")
    assert t.count('"@type": "FAQPage"') + t.count('"@type":"FAQPage"') == 0   # only the component emits it now


def test_usar_is_not_labelled_dwarka_anywhere_public():
    for page in ("ipu-btech-cutoff-2025.php", "best-btech-colleges-ipu.php", "ipu-colleges-list.php"):
        t = read(page)
        assert "USAR Dwarka" not in t and "USAR, Dwarka" not in t, page
    lst = read("ipu-colleges-list.php")
    m = re.search(r'usar-admission\.php[^\n]*</a></td>\s*<td[^>]*>([^<]*)</td>', lst)
    assert m and "Dwarka" not in m.group(1), m and m.group(1)


def test_dwarka_hub_lists_tips():
    assert "/tips-admission.php" in read("ipu-colleges-in-dwarka.php")


def test_mbs_title_and_h1_do_not_promise_fees_or_seats():
    t = read("mbs-college-admission.php")
    title = re.search(r"<title>([^<]*)</title>", t).group(1)
    h1 = re.search(r'\$hero_title = "([^"]*)"', t).group(1)
    facts = json.loads(read("include/data/college-facts-2026.json"))
    has_fees = bool(facts.get("mbs", {}).get("fees"))
    if not has_fees:
        assert "Fees" not in title and "Seats" not in title
        assert "Fees" not in h1 and "Seats" not in h1


def test_cutoff_template_does_not_duplicate_admission_page_table():
    t = read("include/components/college-cutoff-page.php")
    assert "btech-cutoff-rounds-table.php" not in t
    assert "Round 1 to Round 3" in t


def test_adgitm_page_uses_current_name_and_sourced_facts():
    facts = json.loads(read("include/data/college-facts-2026.json"))
    a = facts["adgitm"]
    assert "ADGIPS" in a["short"] and "formerly ADGITM" in a["short"]
    assert a["campus_area_acres"]["v"] == 8.08
    assert {f["course"] for f in a["fees"]} >= {"B.Tech", "BBA", "BA LLB / BBA LLB", "MBA"}
    assert "facts_key = 'adgitm'" in read("adgitm-admission.php")


def test_mait_and_msit_intake_rows_are_sourced():
    facts = json.loads(read("include/data/college-facts-2026.json"))
    mait = facts["mait"]["intake"]; msit = facts["msit"]["intake"]
    assert mait["source"].startswith("https://mait.ac.in/") and msit["source"].startswith("https://msit.in/")
    assert {"programme": "B.Tech Information Technology", "seats": "300"} in mait["v"]
    assert {"programme": "B.Tech Computer Science & Engineering", "seats": "300"} in msit["v"]
    assert "facts_key = 'mait'" in read("mait-admission.php")


def test_mbs_page_states_eligibility_from_college_admission_page():
    t = read("mbs-college-admission.php")
    for phrase in ("NATA", "45% aggregate", "JEE / CUET", "GGSIPU CET / CUET", "50% aggregate"):
        assert phrase in t, phrase
    assert "mbscollege.org/web/mbsclg/admission-v8.html" in t
