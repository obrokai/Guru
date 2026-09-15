#!/usr/bin/env python3
"""Generate the font-size override stylesheets that power Customize → Typography.

For every rule in the theme's stylesheets that sets a font-size on a selector we
classify as body / heading / hero / nav, emit the same rule (same @media wrapper,
same order) with the value multiplied by a CSS variable:

    .abt-p { font-size: 18px; }   →   .abt-p { font-size: calc(18px * var(--guru-fs-body)); }

At 100% every variable is 1, so the cascade is identical to the original. Each
source stylesheet gets its own override file (assets/css/type/<name>.css), which
functions.php enqueues right after the stylesheet it was derived from — that keeps
the original override order between style.css and the per-page stylesheets intact.

Re-run after editing any theme CSS:  python3 tools/build-typography.py
"""
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.join(os.path.dirname(HERE), "guru-theme")
OUT_DIR = os.path.join(THEME, "assets", "css", "type")
SOURCES = ["style.css", "assets/css/about.css", "assets/css/services.css", "assets/css/work.css",
           "assets/css/blog.css", "assets/css/investor.css"]

# Never scaled: eyebrow labels, pills, buttons, form controls, counters.
EXCLUDE = re.compile(
    r"-label\b|-tag\b|\btag\b|btn|-submit\b|\binput\b|\bselect\b|\btextarea\b|\boption\b|"
    r"filter|-count\b|\.ftr-nav-heading|\.ftr-copy|-badge\b|-dot\b|-hint|-pill|"
    r"\.gn-link-cta|\.gn-link-whatsapp|\.hw-cta|\.vmodal-cta|\.srv-panel-cta|-cta-label|-cta-block|"
    r"\.gn-hamburger|\.wp-|\.screen-reader|\.cf-hp|\.hwf-|\.vmf-|\.spf-(input|select|textarea)|"
    r"\.cf-(input|select|textarea|cc|phone)|\.srv-nav-num|\.abt-office-num|\.hw-cta|\.vmodal-btn|"
    r"\.srv-carousel|\.srv-media-label|\.logo-|\.inv-doc-link\b|\.inv-hero-label|\.srv-sidebar-label|"
    r"\.blg-hero-tag|\.abt-hero-tag|\.wrk-page-meta|\.srv-panel-tag|\.abt-clients-label|\.abt-offices-label|"
    r"\.abt-story-label|\.svc-label|\.abt-stats-label|\.spf-label|\.inv-section-title|\.inv-card-title|"
    r"\.inv-sidebar-card-title|\.hw-vmodal-sub|\.vmodal-client|\.wrk-card-meta|\.prj-tags"
)
NAV = re.compile(r"\.gn-(link|mobile-link)\b")
HERO = re.compile(r"\.(abt-hero-title|inv-hero-title|blg-hero-title|wrk-page-title|blg-single-title|"
                  r"srv-panel-title|gh-h1|gh-title)\b")
HEADING = re.compile(r"-(title|heading)\b|\.abt-stat-num\b|\.svc-acc-name\b|\.abt-office-city\b|\.svc-name\b|"
                     r"\.inf-heading\b|\.hw-vmodal-title\b|\.vmodal-title\b|(^|[\s>+~,])h[1-3]\b")
BODY = re.compile(r"-(p|desc|lead|excerpt|body|text|region|tel|addr|cap|link|sub|note|date|name|key|val|"
                  r"city|empty|intro)\b|\.guru-editor|\.inv-contact-|\.prj-client\b|\.art-meta\b|"
                  r"\.inv-info-|\.blg-empty\b|\.wrk-empty\b|(^|[\s>+~,])(p|li|blockquote|td|th)\b")

SKIP_VALUES = re.compile(r"inherit|initial|unset|revert|smaller|larger|var\(")


def strip_comments(css):
    return re.sub(r"/\*.*?\*/", "", css, flags=re.S)


def parse(css):
    """Yield (wrappers, selector_text, declarations) for every style rule."""
    i, n = 0, len(css)
    stack = []
    out = []
    while i < n:
        j = css.find("{", i)
        if j < 0:
            break
        prelude = css[i:j].strip()
        close_before = css.find("}", i)
        if 0 <= close_before < j:
            # end of an at-rule block
            if stack:
                stack.pop()
            i = close_before + 1
            continue
        if prelude.startswith("@"):
            if prelude.startswith(("@media", "@supports", "@container", "@layer")):
                stack.append(prelude)
                i = j + 1
                continue
            # @font-face, @keyframes … skip the whole block
            depth, k = 1, j + 1
            while k < n and depth:
                depth += {"{": 1, "}": -1}.get(css[k], 0)
                k += 1
            i = k
            continue
        k = css.find("}", j)
        out.append((list(stack), prelude, css[j + 1:k]))
        i = k + 1
    return out


def font_size(decls):
    value = None
    for d in decls.split(";"):
        if ":" not in d:
            continue
        prop, val = d.split(":", 1)
        if prop.strip().lower() == "font-size":
            value = val.strip()
    return value


def smallest_px(value):
    nums = [float(x) for x in re.findall(r"(\d+(?:\.\d+)?)px", value)]
    return min(nums) if nums else None


def classify(selector, value):
    s = selector.strip()
    if EXCLUDE.search(s):
        return None
    if NAV.search(s):
        return "nav"
    if HERO.search(s):
        return "hero"
    if HEADING.search(s):
        px = smallest_px(value)
        if px is not None and px <= 14:   # an eyebrow styled as a "title"
            return None
        return "heading"
    if BODY.search(s):
        return "body"
    return None


def build(src_rel):
    css = strip_comments(open(os.path.join(THEME, src_rel), encoding="utf-8").read())
    lines = [f"/* Generated by tools/build-typography.py from {src_rel} — do not edit; re-run the script instead. */"]
    counts = {"body": 0, "heading": 0, "hero": 0, "nav": 0}
    seen = {"body": set(), "heading": set(), "hero": set(), "nav": set()}
    for wrappers, selectors, decls in parse(css):
        value = font_size(decls)
        if not value or SKIP_VALUES.search(value):
            continue
        important = "!important" in value
        clean = value.replace("!important", "").strip()
        groups = {}
        for sel in selectors.split(","):
            g = classify(sel, clean)
            if g:
                groups.setdefault(g, []).append(sel.strip())
        for g, sels in groups.items():
            rule = f"{', '.join(sels)} {{ font-size: calc({clean} * var(--guru-fs-{g})){' !important' if important else ''}; }}"
            indent = ""
            for w in wrappers:
                lines.append(f"{indent}{w} {{")
                indent += "  "
            lines.append(indent + rule)
            for _ in wrappers:
                indent = indent[:-2]
                lines.append(indent + "}")
            counts[g] += 1
            seen[g].update(sels)
    return "\n".join(lines) + "\n", counts, seen


def main():
    os.makedirs(OUT_DIR, exist_ok=True)
    total = {"body": set(), "heading": set(), "hero": set(), "nav": set()}
    for src in SOURCES:
        text, counts, seen = build(src)
        out = os.path.join(OUT_DIR, os.path.basename(src))
        open(out, "w", encoding="utf-8").write(text)
        for g in total:
            total[g] |= seen[g]
        print(f"{src:26s} → type/{os.path.basename(src):14s} body={counts['body']:3d} heading={counts['heading']:3d} hero={counts['hero']:3d} nav={counts['nav']:3d}")
    if "--list" in sys.argv:
        for g, sels in total.items():
            print(f"\n[{g}] {len(sels)} selectors")
            for s in sorted(sels):
                print("   ", s)


if __name__ == "__main__":
    main()
