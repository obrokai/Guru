#!/usr/bin/env python3
"""Parity check: the static site's pages vs the WordPress theme rendered on a local site.

For each page, the set of CSS classes the static page actually uses (defined in
its <style> AND present in its markup) must (a) have a rule in the theme CSS and
(b) appear in the rendered WordPress markup. Anything missing is a parity gap.

Usage:
  python3 tools/parity-check.py --static-dir /path/to/vercel/html --local http://localhost:8899

The static HTML can be fetched with:
  for p in index about services work blog investor; do curl -s -o $p.html https://guru-ebon-eight.vercel.app/$p.html; done
"""
import argparse, glob, os, re, sys, urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
THEME = os.path.join(os.path.dirname(HERE), "guru-theme")
PAGES = [("index", "/"), ("about", "/about/"), ("services", "/solutions/"), ("work", "/work/"), ("blog", "/blog/"), ("investor", "/investor/")]

# Classes the theme generates dynamically (JS state, per-item colour variants) — not expected verbatim in server HTML.
IGNORE = re.compile(r"^(c\d+|active|open|hidden|show|is-hidden|nav-hidden|form-open|vmodal-tag)$|^(cat|grad)-")

# Structural expectations per page: (needle, minimum count). Needles ending in a space are matched as class="…".
CHECKS = {
    "index": [("abt-stat-num", 4), ("hw-vmodal", 1), ("svc-acc-item", 9), ("wrk-card ", 4), ("logo-ticker", 1), ("hw-info-card", 2), ("hw-video-card", 2)],
    "about": [("abt-world-map", 1), ("abt-office-card", 6), ("abt-client-cell", 67), ("abt-story-p", 4)],
    "services": [('id="panel-', 9), ("srv-phone-wrap", 3), ("srv-carousel-slide", 4), ("srv-video-wrap", 1), ('srv-highlight"', 24), ("srv-media-label", 3), ("spf-form", 9)],
    "work": [("prj-card", 13), ("filter-btn", 7), ('id="vmodal"', 1), ('class="prj-card large"', 1), ("prj-img", 13), ("btn-primary-lg", 1)],
    "blog": [("blg-filter-btn", 8), ("btn-primary-lg", 1)],
    "investor": [("inv-doc-item", 8), ("inv-sidebar-card", 3), ("inv-contact-row", 5), ("inv-footer", 1), ("inv-more-btn", 1)],
}


def get(url):
    req = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0"})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.status, r.read().decode("utf-8", "replace")


def css_classes(css):
    css = re.sub(r"/\*.*?\*/", "", css, flags=re.S)
    return set(re.findall(r"\.(-?[_a-zA-Z][\w-]*)", css))


def html_classes(html):
    s = set()
    for m in re.findall(r'class="([^"]+)"', html):
        s.update(m.split())
    return s


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--static-dir", required=True, help="folder holding index.html, about.html, …")
    ap.add_argument("--local", default="http://localhost:8899", help="base URL of the local WordPress site")
    ap.add_argument("--theme", default=THEME)
    args = ap.parse_args()

    theme_css = ""
    for f in [os.path.join(args.theme, "style.css")] + sorted(glob.glob(os.path.join(args.theme, "assets/css/*.css"))):
        theme_css += open(f, encoding="utf-8", errors="replace").read() + "\n"
    theme_cls = css_classes(theme_css)

    gaps = 0
    for name, path in PAGES:
        static = open(os.path.join(args.static_dir, name + ".html"), encoding="utf-8", errors="replace").read()
        s_css = "\n".join(re.findall(r"<style[^>]*>(.*?)</style>", static, re.S))
        used = {c for c in css_classes(s_css) & html_classes(static) if not IGNORE.match(c)}
        try:
            status, rendered = get(args.local.rstrip("/") + path)
        except Exception as e:  # noqa: BLE001
            print(f"[{name:9s}] {path:12s} FETCH FAILED: {e}")
            gaps += 1
            continue
        r_cls = html_classes(rendered)
        no_css = sorted(used - theme_cls)
        no_html = sorted(used - r_cls)
        errors = len(re.findall(r"(Fatal error|Warning:|Notice:|Deprecated:)", rendered))
        print(f"[{name:9s}] {path:12s} HTTP {status} | static classes {len(used):3d} | missing in theme CSS {len(no_css):2d} | missing in rendered HTML {len(no_html):2d} | PHP error strings {errors}")
        if no_css:
            print("    CSS:", ", ".join(no_css))
        if no_html:
            print("    HTML:", ", ".join(no_html))
        gaps += len(no_css) + len(no_html) + errors
        for needle, expect in CHECKS.get(name, []):
            got = rendered.count('class="' + needle) if needle.endswith(" ") else rendered.count(needle)
            ok = got >= expect
            gaps += 0 if ok else 1
            print(f"    {'✓' if ok else '✗'} {needle.strip():24s} expected ≥{expect:<3d} got {got}")

    print("\nTOTAL GAPS:", gaps)
    sys.exit(1 if gaps else 0)


if __name__ == "__main__":
    main()
