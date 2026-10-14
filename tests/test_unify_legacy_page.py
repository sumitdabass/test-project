import sys
from pathlib import Path
import pytest
sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "scripts"))
from unify_legacy_page import ConvertError, convert, split_sections, top_level_nodes

PAGE = """<?php
include_once("include/base-head.php");
?>
<title>T</title>
</head>
<body>
<?php include_once("include/base-nav.php") ?>
<?php
$hero_h1 = 'Course Guide';
$hero_show_form = false;
include __DIR__ . '/include/components/page-hero.php';
?>
<section class="blog-wrapper pt-130 pb-130">
<div class="container">
<nav aria-label="breadcrumb" class="mb-4">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="https://ipu.co.in/">Home</a></li>
<li class="breadcrumb-item"><a href="ipu-admission-guide.php">IPU Admission Guide</a></li>
<li class="breadcrumb-item active">Course Guide</li>
</ol>
</nav>
<div class="row">
<div class="col-lg-8">
<div class="blog-details">
<?php $last_updated = '2026-06-24'; include 'include/components/last-updated.php'; ?>
<h2>First</h2>
<p>
&#128073; intro <a href="a.php">A</a>
</p>
<p>\U0001F449 <a href="b.php">Link B</a></p>
<hr>
<h2>Second</h2>
<p>second para</p>
<h2>Third</h2>
<p>third</p>
</div>
</div>
<div class="col-lg-4">
<?php include __DIR__ . '/include/components/sidebar-enquiry.php'; ?>
</div>
</div>
</div>
</section>
<?php include_once("include/base-footer.php"); ?>
</body>
</html>
"""


def test_top_level_nodes_balanced_and_php_opaque():
    nodes = top_level_nodes('<p>a<?php if ($x > 1): ?>b<?php endif; ?></p><hr><div><h2>x</h2></div>text')
    assert [k for k, _ in nodes] == ["el", "el", "el", "text"]
    assert nodes[0][1] == '<p>a<?php if ($x > 1): ?>b<?php endif; ?></p>'


def test_split_sections_before_second_boundary():
    nodes = top_level_nodes("<h2>A</h2><p>x</p><hr><h2>B</h2><p>y</p><h2>C</h2>")
    intro, rest = split_sections(nodes)
    assert "".join(r for _, r in intro) == "<h2>A</h2><p>x</p><hr>"
    assert "".join(r for _, r in rest) == "<h2>B</h2><p>y</p><h2>C</h2>"


def test_split_sections_section_block_wrappers():
    nodes = top_level_nodes('<div class="section-block"><h2>A</h2><p>x</p></div><div class="section-block"><h2>B</h2></div>')
    intro, rest = split_sections(nodes)
    assert "".join(r for _, r in intro).count("<h2>A</h2>") == 1 and "<h2>B</h2>" in "".join(r for _, r in rest)


def test_split_sections_single_section_stays_whole():
    nodes = top_level_nodes("<h2>A</h2><p>x</p>")
    intro, rest = split_sections(nodes)
    assert rest == [] and "".join(r for _, r in intro) == "<h2>A</h2><p>x</p>"


def test_convert_full_page():
    out = convert(PAGE)
    assert "blog-wrapper" not in out and "breadcrumb-item" not in out and "blog-details" not in out
    assert "$hero_breadcrumbs = [['Home', 'https://ipu.co.in/'], ['IPU Admission Guide', 'ipu-admission-guide.php'], ['Course Guide', '']];" in out
    assert "$hero_show_enquire = true;" in out
    assert "$last_updated = '2026-06-24';" in out.split("page-hero.php")[0]          # lifted into the hero block
    assert out.count("last-updated.php") == 0                                         # hero renders it
    assert "\U0001F449" not in out and "&#128073;" not in out
    assert out.count("sidebar-enquiry.php") == 1
    ib = out[out.index('<div class="ipu-body">'):]
    assert ib.index("<h2>First</h2>") < ib.index("sidebar-enquiry.php") < ib.index("<h2>Second</h2>") < ib.index("<h2>Third</h2>")
    assert "cta-strip.php" in ib.split("<h2>Second</h2>")[0].split("ipu-body__rest")[1]   # CTA card opens the rest column
    assert '<a href="a.php">A</a>' in out and '<a href="b.php">Link B</a>' in out    # links untouched


def test_convert_is_idempotent_and_refuses_unknown_shapes():
    out = convert(PAGE)
    with pytest.raises(ConvertError, match="already converted"):
        convert(out)
    with pytest.raises(ConvertError, match="sidebar"):
        convert(PAGE.replace("sidebar-enquiry.php", "sidebar-cta.php"))
    with pytest.raises(ConvertError, match="blog-wrapper"):
        convert("<html><body><p>no wrapper</p></body></html>")
