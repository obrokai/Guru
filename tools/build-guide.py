#!/usr/bin/env python3
"""Assemble the client guide: HTML with the admin screenshots embedded as data URIs
(the Artifact CSP blocks external images) and Markdown referencing docs/images/."""
import base64, os, re, shutil

TMP = "/Users/mike/.claude/jobs/65a2bd7c/tmp"
SHOTS = f"{TMP}/admin-shots"
REPO = "/Users/mike/Guru/.claude/worktrees/theme-parity"
DOCS = f"{REPO}/docs"
IMG_DIR = f"{DOCS}/images"
os.makedirs(IMG_DIR, exist_ok=True)

tpl = open(f"{TMP}/guide-template.html", encoding="utf-8").read()

def data_uri(name):
    p = f"{SHOTS}/{name}.png"
    return "data:image/png;base64," + base64.b64encode(open(p, "rb").read()).decode()

html = re.sub(r"\{\{IMG:([\w-]+)\}\}", lambda m: data_uri(m.group(1)), tpl)
out_html = f"{DOCS}/客戶教學-上架作品與調整字體.html"
open(out_html, "w", encoding="utf-8").write(html)
open(f"{TMP}/guru-admin-guide.html", "w", encoding="utf-8").write(html)

for f in sorted(os.listdir(SHOTS)):
    if f.endswith(".png"):
        shutil.copy(f"{SHOTS}/{f}", f"{IMG_DIR}/{f}")

# Markdown: same placeholders → relative image paths
md_tpl = open(f"{TMP}/guide-template.md", encoding="utf-8").read()
md = re.sub(r"\{\{IMG:([\w-]+)\}\}", lambda m: f"![](images/{m.group(1)}.png)", md_tpl)
open(f"{DOCS}/客戶教學-上架作品與調整字體.md", "w", encoding="utf-8").write(md)

print(f"html {os.path.getsize(out_html)/1048576:.1f} MB · images copied: {len([f for f in os.listdir(IMG_DIR) if f.endswith('.png')])}")
