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


ARTICLE_PAGE = PAGE.replace('<div class="blog-details">', '<article class="blog-details">', 1).replace(
    "<h2>Third</h2>\n<p>third</p>\n</div>", "<h2>Third</h2>\n<p>third</p>\n</article>", 1).replace(
    "<title>T</title>", "<title>T</title>\n<style>\n.college-card { color: red; }\n.blog-details h2 {\n  border-left: 4px solid #3a5bd9;\n}\n.blog-details p, .blog-details li { margin: 0; }\n.section-block { margin-bottom: 2rem; }\n</style>")


def test_article_blog_details_wrapper_is_unwrapped_and_inline_css_retargeted():
    out = convert(ARTICLE_PAGE)
    assert "blog-details" not in out
    assert "<article" not in out
    assert ".ipu-body__intro h2, .ipu-body__rest h2 {" in out
    assert ".ipu-body__intro p, .ipu-body__rest p, .ipu-body__intro li, .ipu-body__rest li {" in out
    assert ".college-card { color: red; }" in out and ".section-block { margin-bottom: 2rem; }" in out   # unrelated rules untouched


def test_html_comments_around_breadcrumb_and_row_are_tolerated():
    page = PAGE.replace('<div class="container">\n<nav', '<div class="container">\n\n<!-- ===== BREADCRUMBS ===== -->\n\n<nav', 1).replace(
        "</nav>\n<div class=\"row\">", "</nav>\n\n<!-- content -->\n<div class=\"row\">", 1)
    out = convert(page)
    assert "$hero_breadcrumbs = [['Home'" in out and "BREADCRUMBS" not in out and "breadcrumb-item" not in out


def test_html_comment_before_sidebar_column_is_tolerated():
    page = PAGE.replace('<div class="col-lg-4">', '<!-- Sidebar -->\n\n<div class="col-lg-4">', 1)
    out = convert(page)
    assert out.count("sidebar-enquiry.php") == 1 and "Sidebar -->" not in out


def test_top_level_nodes_accepts_html_implied_end_tags():
    # a forgotten </p> before </div> is valid-by-recovery HTML; the node text must stay byte-identical
    nodes = top_level_nodes('<div class="x"><p>unclosed text</div><h2>Next</h2>')
    assert [k for k, _ in nodes] == ["el", "el"]
    assert nodes[0][1] == '<div class="x"><p>unclosed text</p></div>'
    with pytest.raises(ConvertError):
        top_level_nodes('<div class="x"><span>never closed</div>')   # non-optional end tags still fail loudly


def test_top_level_nodes_accepts_optional_end_tag_open_at_end_of_content():
    nodes = top_level_nodes('<h2>A</h2><p>last paragraph never closed')
    assert [k for k, _ in nodes] == ["el", "el"] and nodes[1][1] == '<p>last paragraph never closed</p>'
    with pytest.raises(ConvertError, match="unclosed <div>"):
        top_level_nodes('<h2>A</h2><div>never closed')


def test_blog_details_wrapper_that_does_not_span_all_content_is_unwrapped_in_place():
    page = PAGE.replace("<h2>Third</h2>\n<p>third</p>\n</div>",
                        "<h2>Third</h2>\n<p>third</p>\n</div>\n<!-- extra -->\n<section class=\"cta-x\"><h3>Extra</h3></section>", 1)
    out = convert(page)
    assert "blog-details" not in out
    ib = out[out.index('<div class="ipu-body">'):]
    assert '<section class="cta-x"><h3>Extra</h3></section>' in ib.split("ipu-body__rest")[1]
    assert ib.count("<h2>") == 3


def test_breadcrumb_labels_with_entities_are_unescaped_before_the_hero_escapes_them():
    page = PAGE.replace('Course Guide</li>', 'Seat &amp; Quota &#8211; Guide</li>', 1)
    out = convert(page)
    assert "['Seat & Quota – Guide', '']" in out


def test_implied_end_tags_are_normalised_with_explicit_closing_tags():
    nodes = top_level_nodes('<div class="x"><p>unclosed text</div><h2>Next</h2>')
    assert nodes[0][1] == '<div class="x"><p>unclosed text</p></div>'
    nodes = top_level_nodes('<h2>A</h2><p>last paragraph never closed')
    assert nodes[1][1] == '<p>last paragraph never closed</p>'


def test_block_start_tag_closes_an_open_paragraph_at_the_right_place():
    nodes = top_level_nodes("<p>a<p>b")
    assert [r for _, r in nodes] == ["<p>a</p>", "<p>b</p>"]
    assert [r for _, r in top_level_nodes('<div><p>a<p>b</div>')] == ['<div><p>a</p><p>b</p></div>']
    nodes = top_level_nodes("<p>a<h2>b</h2><p>c")
    assert [r for _, r in nodes] == ["<p>a</p>", "<h2>b</h2>", "<p>c</p>"]


def test_forgotten_close_paragraph_does_not_hide_later_sections_from_the_splitter():
    nodes = top_level_nodes("<h2>One</h2><p>text without close<h2>Two</h2><p>two</p><h2>Three</h2>")
    intro, rest = split_sections(nodes)
    assert "".join(r for _, r in intro) == "<h2>One</h2><p>text without close</p>"
    assert "".join(r for _, r in rest).startswith("<h2>Two</h2>")


def test_cta_card_is_emitted_even_when_there_is_only_one_section():
    page = PAGE.replace("<h2>Second</h2>\n<p>second para</p>\n<h2>Third</h2>\n<p>third</p>\n", "")
    out = convert(page)
    ib = out[out.index('<div class="ipu-body">'):]
    assert ib.count("cta-strip.php") == 1 and ib.index("sidebar-enquiry.php") < ib.index("cta-strip.php")
