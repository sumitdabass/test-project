#!/usr/bin/env python3
"""Inject the B.Tech cutoff-rounds-table component into existing college pages
and generate stub pages for institutes that have no page yet.

Run from project root or anywhere — paths are absolute.
"""
from __future__ import annotations

import json
import re
from pathlib import Path

REPO = Path("/Users/Sumit/test-project")
WEB = REPO / "website_download"
DATA_PHP = WEB / "include" / "data" / "btech-cutoffs-2025.php"
NEW_PAGES_DIR = WEB

# Map institute key (matches cutoffs.json) → page slug + edit strategy.
# Strategy: 'template' = pages using college-page-template.php (inject 'cutoff_institute' into $college_data array).
# Strategy: 'direct'   = pages with their own HTML; insert component-include before related-pages.php include.
PAGES = [
    # template-driven existing pages
    ("Delhi Technical Campus",                                    "dtc-admission.php",            "template"),
    ("Echelon Institute of Technology",                           "echelon-admission.php",        "template"),
    ("Fairfield Institute of Management & Technology",            "fairfield-admission.php",      "template"),
    ("Greater Noida Institute of Technology",                     "gnit-admission.php",           "template"),
    ("Guru Tegh Bahadur 4th Centenary Engineering College",       "gtb4cec-admission.php",        "template"),
    ("JIMS Engineering Management Technical Campus",              "jemtec-admission.php",         "template"),
    # direct-include existing pages
    ("Bhagwan Parshuram Institute of Technology",                 "BPIT.php",                     "direct"),
    ("Bharati Vidyapeeths College of Engineering",                "BVP.php",                      "direct"),
    ("Dr. Akhilesh Das Gupta Institute of Professional Studies",  "adgitm-admission.php",         "direct"),
    ("Guru Teg Bahadur Institute of Technology",                  "gtbit-admission.php",          "direct"),
    ("HMR Institute of Technology & Management",                  "hmr-admission.php",            "direct"),
    ("Maharaja Agrasen Institute of Technology",                  "mait-admission.php",           "direct"),
    ("Maharaja Surajmal Institute Technology",                    "msit-admission.php",           "direct"),
    ("University School of Automation & Robotics",                "usar-admission.php",           "direct"),
    ("University School of Information & Communication Technology", "usict-admission.php",        "direct"),
    ("Vivekananda Institute of Professional Studies",             "vips-pitampura-courses.php",   "direct"),
]

# New stub pages to create (institute → slug + short_name + address hint).
NEW_PAGES = [
    {
        "institute": "Delhi Institute of Sciences & Technology",
        "slug": "dist-admission",
        "short_name": "DIST",
        "address": "Delhi NCR (verify exact campus address with helpline)",
    },
    {
        "institute": "Shri Balwant Institute of Technology",
        "slug": "sbit-admission",
        "short_name": "SBIT",
        "address": "Sonepat / Delhi NCR (verify with helpline)",
    },
    {
        "institute": "Tribhuvan College",
        "slug": "tribhuvan-admission",
        "short_name": "Tribhuvan College",
        "address": "Delhi NCR (verify with helpline)",
    },
    {
        "institute": "Trinity Institute of Innovations in Professional Studies",
        "slug": "tiips-admission",
        "short_name": "TIIPS",
        "address": "Delhi NCR (verify with helpline)",
    },
    {
        "institute": "University School of Chemical Technology",
        "slug": "usct-admission",
        "short_name": "USCT",
        "address": "GGSIPU Dwarka Campus, Sector 16-C, New Delhi – 110078",
    },
]


def php_str(s: str) -> str:
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def inject_template_page(path: Path, institute: str) -> tuple[bool, str]:
    """Insert 'cutoff_institute' => '...' right after the 'slug' => '...' line."""
    if not path.exists():
        return False, "missing file"
    src = path.read_text(encoding="utf-8")
    if "'cutoff_institute'" in src:
        return False, "already injected"
    # Find: 'slug' => 'whatever',
    pattern = re.compile(r"('slug'\s*=>\s*'[^']*'\s*,)")
    m = pattern.search(src)
    if not m:
        return False, "no slug marker"
    inject = f"\n    'cutoff_institute' => {php_str(institute)},"
    new_src = src[:m.end()] + inject + src[m.end():]
    path.write_text(new_src, encoding="utf-8")
    return True, "ok"


def inject_direct_page(path: Path, institute: str) -> tuple[bool, str]:
    """Insert the cutoff-rounds-table include block immediately before the
    include 'include/components/related-pages.php'; line."""
    if not path.exists():
        return False, "missing file"
    src = path.read_text(encoding="utf-8")
    if "btech-cutoff-rounds-table" in src:
        return False, "already injected"
    # find related-pages include line
    rel_re = re.compile(r"(\s*)include\s+'include/components/related-pages\.php';", re.M)
    m = rel_re.search(src)
    if not m:
        return False, "no related-pages anchor"
    indent = m.group(1)
    block = (
        f"{indent}// B.Tech round-wise cutoff table (2025-26 GGSIPU counselling)\n"
        f"{indent}$cutoff_institute = {php_str(institute)};\n"
        f"{indent}include 'include/components/btech-cutoff-rounds-table.php';\n"
    )
    new_src = src[:m.start()] + block + src[m.start():]
    path.write_text(new_src, encoding="utf-8")
    return True, "ok"


def render_new_page(spec: dict, branches: list) -> str:
    """Build a minimal but credible $college_data page for the template.
    Honest about unknowns — fees/placements explicitly say verify."""
    inst   = spec["institute"]
    slug   = spec["slug"]
    short  = spec["short_name"]
    addr   = spec["address"]
    title  = f"{short} Admission 2026 | B.Tech Courses, Cutoff & IPU Counselling"
    md     = f"{inst} ({short}) – IPU affiliated B.Tech college. 2025 round-wise JEE Main cutoffs, courses, admission process. Free counselling: 9899991342."
    courses = "[\n" + ",\n".join(
        f"        ['name' => {php_str('B.Tech ' + b)}, 'duration' => '4 Years', 'seats' => '']"
        for b in branches
    ) + "\n    ]"

    faqs = "[\n" + ",\n".join([
        f"        ['question' => 'How to take admission in {short} under IPU 2026?', 'answer' => 'Admission is via the GGSIPU centralised counselling on JEE Main rank. Register on the IPU admission portal, fill {short} as a preference during choice filling, and report to the college upon allotment. Call 9899991342 for step-by-step guidance.']",
        f"        ['question' => 'What B.Tech branches does {short} offer?', 'answer' => '{short} offers " + ", ".join(branches[:6]) + " under IPU. Seat intake varies per branch — call 9899991342 for the latest seat matrix.']",
        f"        ['question' => 'What was the {short} B.Tech cutoff for 2025?', 'answer' => 'The 2025 round-wise JEE Main rank cutoffs (Delhi & Outside Delhi) for {short} are tabulated below. Use them as guidance for the 2026 cycle. For personalised choice-filling help, call 9899991342.']",
        f"        ['question' => 'How can I get the latest fee and placement info for {short}?', 'answer' => 'Fee structure follows the GGSIPU Delhi Gazette SFRC notification. For verified current fees, placements and cut-off support, call 9899991342 (Mon–Sat, 9 AM – 7 PM).']",
    ]) + "\n    ]"

    related = (
        "[\n"
        "        ['title' => 'IPU B.Tech Admission 2026', 'url' => '/IPU-B-Tech-admission-2026.php', 'desc' => 'JEE Main eligibility, top colleges, cutoffs and admission process'],\n"
        "        ['title' => 'IPU B.Tech Cutoff 2025', 'url' => '/ipu-btech-cutoff-2025.php', 'desc' => 'Round-wise B.Tech cutoffs across all GGSIPU colleges'],\n"
        "        ['title' => 'IPU Helpline – Call 9899991342', 'url' => '/ipu-helpline-contact-number.php', 'desc' => 'Free admission counselling Mon-Sat 9 AM – 7 PM'],\n"
        "        ['title' => 'B.Tech Management Quota in IPU', 'url' => '/btech-management-quota-ipu.php', 'desc' => 'Direct B.Tech admission process and management seat eligibility'],\n"
        "        ['title' => 'IPU Counselling Process', 'url' => '/GGSIPU-counselling-for-B-Tech-admission.php', 'desc' => 'Step-by-step GGSIPU B.Tech counselling guide'],\n"
        "    ]"
    )

    return f"""<?php
$college_data = [
    'slug' => {php_str(slug)},
    'cutoff_institute' => {php_str(inst)},
    'name' => {php_str(inst)},
    'short_name' => {php_str(short)},
    'title' => {php_str(title)},
    'meta_desc' => {php_str(md)},
    'og_title' => {php_str(title)},
    'og_desc' => {php_str(md)},
    'address' => {php_str(addr)},
    'last_updated' => '2026-04-30',
    'ai_summary' => {php_str(f"{inst} ({short}) is an IPU-affiliated college offering B.Tech programmes under GGSIPU Delhi. The college takes admissions through the centralised IPU counselling on JEE Main rank. The 2025 round-wise cut-offs (Delhi and Outside Delhi quotas) are documented on this page. For verified fees, placements and current admission help, call 9899991342.")},
    'about_text' => '<h2>About ' + str(short) + ' &ndash; IPU Affiliated B.Tech College</h2>\\n<p>' + str(inst) + ' is an IPU-affiliated engineering college operating under Guru Gobind Singh Indraprastha University (GGSIPU). Admissions are conducted through the centralised IPU counselling process based on JEE Main All India Rank.</p>\\n<p>The B.Tech round-wise cutoffs published below cover the 2025-26 GGSIPU counselling cycle. They show the JEE Main rank range (Min &ndash; Max) at which seats closed for Delhi Region and Outside Delhi Region candidates across each branch in Rounds 1, 2 and 3. Use these as guidance when planning your 2026 choice list. Call <a href="tel:+919899991342">9899991342</a> for free choice-filling support.</p>',
    'admission_text' => '<p>Admission is conducted via the IPU centralised counselling process. Candidates with a valid JEE Main score should follow these steps:</p>\\n<ol>\\n<li>Register on the GGSIPU online admission portal (ipu.admissions.nic.in) within the specified window.</li>\\n<li>Choose ' + str(short) + ' branches as preferences while filling the choice list.</li>\\n<li>Track allotment results round-by-round (Rounds 1 to 3 + spot/sliding).</li>\\n<li>Report for document verification and pay the fee on allotment.</li>\\n</ol>\\n<p>Management quota seats may also be available. For step-by-step help with eligibility, choice filling and management quota, call <a href="tel:+919899991342">9899991342</a> (Mon&ndash;Sat 9 AM &ndash; 7 PM).</p>',
    'courses' => {courses},
    'placements' => [
        ['label' => 'Placement Information', 'value' => 'Verified data on request &mdash; call 9899991342'],
        ['label' => 'Recruiters', 'value' => 'Across IT, banking, electronics, manufacturing &mdash; verify with helpline'],
    ],
    'campus_life' => [
        'B.Tech academic blocks and laboratories per AICTE norms',
        'Library and digital learning resources',
        'Sports and student activity facilities',
        'Placement and career-services support',
        'Hostel availability subject to confirmation &mdash; call 9899991342',
    ],
    'fees' => [],
    'faqs' => {faqs},
    'related_pages' => {related},
];
include 'include/templates/college-page-template.php';
""".replace("' + str(short) + '", short).replace("' + str(inst) + '", inst)


def main() -> None:
    cutoffs = json.loads((Path(__file__).resolve().parent / "cutoffs.json").read_text(encoding="utf-8"))
    print("=== Existing-page injection ===")
    for institute, slug, strategy in PAGES:
        path = WEB / slug
        fn = inject_template_page if strategy == "template" else inject_direct_page
        ok, msg = fn(path, institute)
        flag = "INJ" if ok else "skip"
        print(f"  [{flag:4}] {slug:62} → {msg}")

    print("\n=== New page generation ===")
    for spec in NEW_PAGES:
        inst = spec["institute"]
        if inst not in cutoffs:
            print(f"  [skip] {spec['slug']:30} → not in cutoffs.json")
            continue
        branches = list(cutoffs[inst].keys())
        out = WEB / f"{spec['slug']}.php"
        if out.exists():
            print(f"  [skip] {spec['slug']:30} → already exists ({out})")
            continue
        out.write_text(render_new_page(spec, branches), encoding="utf-8")
        print(f"  [NEW ] {spec['slug']:30} → {len(branches)} branches")


if __name__ == "__main__":
    main()
