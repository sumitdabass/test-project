"""AI-facing files (llms.txt, AI_AGENT_README.md, api/agent-data.php) must not carry unsourced numbers,
and the MSIT cutoff answer must agree with the site's own dataset."""
import json, re, subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
WEB = ROOT / "website_download"
FILES = ["llms.txt", "AI_AGENT_README.md", "api/agent-data.php", ".well-known/ai.json"]
UNSOURCED = [r"success[ _]rate", r"students[ _]guided", r"5,?000\+", r"\b500\+\s*students", r"\b9[0-9]%\+?\s*placement", r"placement rate", r"Success rates"]


def test_no_unsourced_claims_in_ai_facing_files():
    for name in FILES:
        text = (WEB / name).read_text(encoding="utf-8")
        for pat in UNSOURCED:
            assert not re.search(pat, text, re.I), f"{name}: unsourced claim matches /{pat}/"


def _msit_cse_shift1():
    code = ('$d = include "include/data/btech-cutoffs-2025.php";'
            '$r = $d["Maharaja Surajmal Institute Technology"]["Computer Science & Engineering (Shift I)"];'
            'echo json_encode(["r1" => $r["round_1"]["delhi"], "r3" => $r["round_3"]["delhi"]]);')
    return json.loads(subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True, cwd=WEB).stdout)


def test_msit_cutoff_answer_matches_dataset():
    text = (WEB / "api" / "agent-data.php").read_text(encoding="utf-8")
    m = re.search(r"'question' => 'What is the cutoff for MSIT B\.Tech 2026\?',\s*'answer' => '((?:[^'\\]|\\.)*)'", text)
    assert m, "MSIT cutoff FAQ not found"
    answer = m.group(1)
    d = _msit_cse_shift1()
    for n in (d["r1"]["min"], d["r1"]["max"], d["r3"]["min"], d["r3"]["max"]):
        assert f"{n:,}" in answer, f"{n:,} missing from answer"
    assert "5,000-8,000" not in answer
    assert "9899991342" in answer                      # the call-to-action is kept
    assert "msit-cutoff.php" in answer                  # points to the page with the full round-wise table


def test_ai_json_is_still_valid_json_and_keeps_its_other_signals():
    data = json.loads((WEB / ".well-known" / "ai.json").read_text(encoding="utf-8"))
    sig = data["expertise_signals"]
    assert sig["free_service"] is True and sig["colleges_covered"] == "60+" and sig["years_active"] == 10
    assert "students_guided" not in sig and "admission_success_rate" not in sig
    stats = subprocess.run(["php", "-r", '$_GET["action"]="stats"; ob_start(); include "api/agent-data.php"; echo ob_get_clean();'],
                           capture_output=True, text=True, cwd=WEB).stdout
    assert "admission_success_rate" not in stats and "total_students_guided" not in stats


def _served(action):
    code = ('chdir("' + str(WEB) + '"); $_SERVER["REQUEST_METHOD"]="GET"; $_GET["action"]="' + action + '"; '
            'ob_start(); include "api/agent-data.php"; echo ob_get_clean();')
    out = subprocess.run(["php", "-r", code], capture_output=True, text=True, check=True).stdout
    return json.loads(out[out.index("{"):])


def _find_faq(node):
    if isinstance(node, dict):
        if "question" in node and "answer" in node:
            yield node
        for v in node.values():
            yield from _find_faq(v)
    elif isinstance(node, list):
        for v in node:
            yield from _find_faq(v)


def test_served_faq_is_well_formed_and_msit_answer_is_the_corrected_one():
    served = list(_find_faq(_served("faq")))
    src = (WEB / "api" / "agent-data.php").read_text(encoding="utf-8")
    faq_block = src[src.index("'faq' => ["):]
    faq_block = faq_block[:faq_block.index("\n    ],\n")]
    assert len(served) == faq_block.count("'question' =>") >= 6          # no entry swallowed or merged
    assert faq_block.count("'answer' =>") == faq_block.count("'question' =>")   # exactly one answer per question
    by_q = {e["question"]: e["answer"] for e in served}
    assert len(by_q) == len(served)                                     # unique questions
    d = _msit_cse_shift1()
    msit = by_q["What is the cutoff for MSIT B.Tech 2026?"]
    for n in (d["r1"]["min"], d["r1"]["max"], d["r3"]["min"], d["r3"]["max"]):
        assert f"{n:,}" in msit
    assert "5,000-8,000" not in msit and "msit-cutoff.php" in msit
    assert by_q["What is management quota admission in IPU?"].startswith("Management quota allows")
