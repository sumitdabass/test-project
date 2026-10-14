#!/usr/bin/env python3
"""Convert a legacy blog-wrapper page to the unified .ipu-body shell. Deterministic, idempotent, strict.

Usage: python3 scripts/unify_legacy_page.py [--apply] website_download/page.php ...
Dry run by default: prints OK / SKIP per page.
"""
import re, sys
from pathlib import Path

VOID = {"br", "hr", "img", "input", "meta", "link", "source", "col", "area", "base", "wbr"}
EMOJI = "\U0001F449"
PHP = re.compile(r"<\?(?:php|=).*?\?>", re.S)
TAG = re.compile(r"<!--.*?-->|<script\b.*?</script>|<style\b.*?</style>|<(/?)([a-zA-Z][a-zA-Z0-9]*)\b[^>]*?(/?)>|[^<]+|<", re.S)


class ConvertError(Exception):
    pass


def _mask(html):
    blocks = []
    def sub(m):
        blocks.append(m.group(0)); return f"\x00{len(blocks) - 1}\x00"
    return PHP.sub(sub, html), blocks


def _unmask(text, blocks):
    return re.sub(r"\x00(\d+)\x00", lambda m: blocks[int(m.group(1))], text)


def top_level_nodes(html):
    """[(kind, raw)] with kind in {'el','text','comment'}; elements balanced; PHP blocks stay inside their node."""
    masked, blocks = _mask(html)
    nodes, cur, depth = [], [], 0
    for m in TAG.finditer(masked):
        raw = m.group(0)
        if m.group(2) is None:                       # comment / script / style / text / stray '<'
            kind = "comment" if raw.startswith("<!--") else ("el" if raw.startswith(("<script", "<style")) else "text")
            if depth == 0: nodes.append((kind, raw))
            else: cur.append(raw)
            continue
        closing, tag, selfc = m.group(1), m.group(2).lower(), m.group(3)
        if not closing:
            if tag in VOID or selfc:
                if depth == 0: nodes.append(("el", raw))
                else: cur.append(raw)
            else:
                cur.append(raw); depth += 1
        else:
            cur.append(raw); depth -= 1
            if depth < 0: raise ConvertError("unbalanced closing tag in content")
            if depth == 0:
                nodes.append(("el", "".join(cur))); cur = []
    if depth != 0: raise ConvertError("unbalanced content (unclosed element)")
    return [(k, _unmask(r, blocks)) for k, r in nodes]


def _is_boundary(kind, raw):
    return kind == "el" and bool(re.match(r"<h2\b", raw) or re.match(r"<(?:div|section)\b[^>]*>\s*<h2\b", raw))


def split_sections(nodes):
    seen = 0
    for i, (k, r) in enumerate(nodes):
        if _is_boundary(k, r):
            seen += 1
            if seen == 2:
                return nodes[:i], nodes[i:]
    return nodes, []


def _find_balanced(s, start, tag):
    """Index just past the closing tag matching the <tag ...> that begins at s[start]."""
    masked, blocks = _mask(s)
    # map start to masked offset
    off = len(_mask(s[:start])[0])
    depth = 0
    for m in re.finditer(rf"<(/?){tag}\b[^>]*?>", masked[off:]):
        depth += -1 if m.group(1) else 1
        if depth == 0:
            masked_end = off + m.end()
            # translate masked offset back to original offset
            return len(_unmask(masked[:masked_end], blocks))
    raise ConvertError(f"unbalanced <{tag}>")


def convert(src):
    if "ipu-body" in src:
        raise ConvertError("already converted")
    m = re.search(r'<section class="blog-wrapper[^"]*">', src)
    if not m:
        raise ConvertError("no blog-wrapper section")
    sec_start = m.start()
    sec_end = _find_balanced(src, sec_start, "section")
    section = src[sec_start:sec_end]
    inner = section[m.end() - sec_start:-len("</section>")]

    # container
    cm = re.match(r'\s*<div class="container">', inner)
    if not cm:
        raise ConvertError("wrapper has no .container")
    c_start = inner.index('<div class="container">')
    c_end = _find_balanced(inner, c_start, "div")
    container = inner[c_start + len('<div class="container">'):c_end - len("</div>")]

    # optional visible breadcrumb
    crumbs = []
    nm = re.match(r'\s*<nav aria-label="breadcrumb"[^>]*>.*?</nav>', container, re.S)
    if nm:
        for li in re.findall(r'<li class="breadcrumb-item[^"]*"[^>]*>(.*?)</li>', nm.group(0), re.S):
            a = re.match(r'\s*<a href="([^"]*)"[^>]*>(.*?)</a>\s*$', li, re.S)
            if a: crumbs.append((re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", a.group(2))).strip(), a.group(1)))
            else: crumbs.append((re.sub(r"\s+", " ", re.sub(r"<[^>]+>", "", li)).strip(), ""))
        container = container[nm.end():]
    if nm and (len(crumbs) < 2 or crumbs[-1][1] != ""):
        raise ConvertError("breadcrumb shape not understood")

    # row > col-lg-8 + col-lg-4
    rm = re.match(r'\s*<div class="row[^"]*">', container)
    if not rm:
        raise ConvertError("no .row after breadcrumb")
    row_start = rm.start()
    row_end = _find_balanced(container, container.index("<div", row_start), "div")
    row_inner = container[rm.end():row_end - len("</div>")]
    c8 = re.match(r'\s*<div class="col-lg-8">', row_inner)
    if not c8:
        raise ConvertError("no col-lg-8 content column")
    c8_end = _find_balanced(row_inner, row_inner.index("<div", c8.start()), "div")
    content = row_inner[c8.end():c8_end - len("</div>")]
    side = row_inner[c8_end:]
    sm = re.fullmatch(r'\s*<div class="col-lg-4">\s*(<\?php include __DIR__ \. \'/include/components/sidebar-enquiry\.php\'; \?>)\s*</div>\s*', side)
    if not sm:
        raise ConvertError("sidebar column is not exactly the sidebar-enquiry include")
    sidebar_include = sm.group(1)
    if container[row_end:].strip():
        raise ConvertError("unexpected content after the row inside the container")

    # unwrap a sole .blog-details wrapper
    for wtag in ("div", "article"):
        bd = re.fullmatch(rf'\s*<{wtag} class="blog-details">(.*)</{wtag}>\s*', content, re.S)
        if bd and _find_balanced(content, content.index(f"<{wtag}"), wtag) >= len(content.rstrip()):
            content = bd.group(1)
            break

    # lift the Last Updated include into the hero
    lu = re.findall(r"<\?php\s*\$last_updated\s*=\s*'([0-9-]+)';\s*include\s*'include/components/last-updated\.php';\s*\?>", content)
    if len(lu) > 1:
        raise ConvertError("more than one last-updated include")
    last_updated = None
    if lu:
        last_updated = lu[0]
        content = re.sub(r"<\?php\s*\$last_updated\s*=\s*'[0-9-]+';\s*include\s*'include/components/last-updated\.php';\s*\?>\s*", "", content, count=1)

    # emoji removal in text (links keep their text)
    content = content.replace("&#128073;", "").replace(EMOJI, "")
    content = re.sub(r"(<p>)[ \t]*", r"\1", content)           # '<p>👉 <a' -> '<p><a'

    nodes = top_level_nodes(content)
    intro, rest = split_sections(nodes)
    join = lambda ns: "".join(r for _, r in ns).strip("\n")

    # hero block: the PHP block that includes page-hero.php
    hm = None
    for cand in re.finditer(r"<\?php(?:(?!\?>).)*?components/page-hero\.php';(?:(?!\?>).)*?\?>", src, re.S):
        hm = cand
    if not hm or hm.start() > sec_start:
        raise ConvertError("no hero include before the wrapper")
    hero = hm.group(0)
    add = []
    if crumbs:
        items = ", ".join("['" + l.replace("\\", "\\\\").replace("'", "\\'") + "', '" + u.replace("'", "\\'") + "']" for l, u in crumbs)
        add.append(f"$hero_breadcrumbs = [{items}];")
    add.append("$hero_show_enquire = true;")
    if last_updated:
        add.append(f"$last_updated = '{last_updated}';")
    new_hero = hero.replace("include __DIR__ . '/include/components/page-hero.php';", "\n".join(add) + "\ninclude __DIR__ . '/include/components/page-hero.php';", 1)
    if new_hero == hero:
        raise ConvertError("hero include statement not in the expected form")

    cta = "<?php $cta_card = true; include 'include/components/cta-strip.php'; ?>"
    rest_html = (cta + "\n" + join(rest)) if rest else ""
    grid = f"""<div class="ipu-body">

  <div class="ipu-body__intro">
{join(intro)}
  </div>

  <div class="ipu-body__aside">
    {sidebar_include}
  </div>

  <div class="ipu-body__rest">
{rest_html}
  </div>

</div>"""
    out = src[:hm.start()] + new_hero + src[hm.end():sec_start] + grid + src[sec_end:]
    return _retarget_blog_details_css(out)


def _retarget_blog_details_css(html):
    """Inline <style> rules keyed to .blog-details keep applying to the same content under the new containers."""
    def fix_block(m):
        css = m.group(2)
        def fix_selector_list(sm):
            sels = [x.strip() for x in sm.group(1).split(",")]
            out = []
            for sel in sels:
                if sel.startswith(".blog-details"):
                    rest = sel[len(".blog-details"):]
                    out += [".ipu-body__intro" + rest, ".ipu-body__rest" + rest]
                else:
                    out.append(sel)
            return ", ".join(out) + " {"
        css2 = re.sub(r"(?m)^([^{}@/\n][^{}]*?)\s*\{", lambda sm: fix_selector_list(sm) if ".blog-details" in sm.group(1) else sm.group(0), css)
        return m.group(1) + css2 + m.group(3)
    return re.sub(r"(<style[^>]*>)(.*?)(</style>)", fix_block, html, flags=re.S)


def main(argv):
    apply = "--apply" in argv
    rc = 0
    for name in [a for a in argv if not a.startswith("--")]:
        p = Path(name)
        try:
            out = convert(p.read_text(encoding="utf-8"))
        except ConvertError as e:
            print(f"SKIP {p.name}: {e}"); rc = 1; continue
        if apply: p.write_text(out, encoding="utf-8")
        print(f"OK   {p.name}")
    return rc


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
