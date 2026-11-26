"""Read-only check of the Guru staging admin after a theme update.

    WP_USER=... WP_PASS=... python3 -I tools/staging-admin-check.py [base-url]

base-url defaults to https://www.guruonline.com.hk/staging and must end in /staging:
this script refuses to touch any other site. It only GETs admin screens (the
"new showcase item" screen makes WordPress create a hidden auto-draft, which it
deletes by itself after 7 days). It never saves, publishes, or changes settings.
Credentials come from the environment only; never put them in a file.
"""
import html
import http.cookiejar
import json
import os
import re
import sys
import urllib.parse
import urllib.request

base = (sys.argv[1] if len(sys.argv) > 1 else 'https://www.guruonline.com.hk/staging').rstrip('/')
if not base.endswith('/staging'):
    sys.exit('REFUSED: only the /staging site may be checked: ' + base)
UA = 'Mozilla/5.0 guru-qa'
jar = http.cookiejar.CookieJar()
op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def get(path, data=None):
    url = path if path.startswith('http') else f'{base}/{path.lstrip("/")}'
    with op.open(urllib.request.Request(url, data=data, headers={'User-Agent': UA}), timeout=120) as r:
        return r.status, r.read().decode('utf-8', 'replace')


def report(check, **data):
    print(json.dumps({'check': check, **data}, ensure_ascii=False))


def text(s):
    return re.sub(r'\s+', ' ', html.unescape(re.sub('<[^>]+>', ' ', s))).strip()


def php_errors(s):
    return re.findall(r'(?:Fatal error|Parse error|Warning|Notice|Deprecated)</b>:[^<]{0,160}', s)[:5]


# Log in.
get('wp-login.php')
get('wp-login.php', urllib.parse.urlencode({'log': os.environ['WP_USER'], 'pwd': os.environ['WP_PASS'], 'testcookie': '1', 'redirect_to': f'{base}/wp-admin/'}).encode())
if not any(c.name.startswith('wordpress_logged_in') for c in jar):
    report('login', ok=False)
    sys.exit(2)
report('login', ok=True)

# Active theme and its version.
# themes.php carries the theme list as JSON; the active theme is the last "id" before "active":true.
_, s = get('wp-admin/themes.php')
pos = s.find('"active":true')
ids = [(m.start(), m.group(1)) for m in re.finditer(r'"id":"([^"]+)"', s) if m.start() < pos] if pos >= 0 else []
slug = ids[-1][1] if ids else None
seg = s[ids[-1][0]:pos] if ids else ''
ver = re.search(r'"version":"([^"]+)"', seg)
report('active-theme', slug=slug, version=ver.group(1) if ver else None, php_errors=php_errors(s))

# Showcase list: homepage slots, missing client / cover.
_, s = get('wp-admin/edit.php?post_type=work&posts_per_page=100')
rows = []
for pid, body in re.findall(r'<tr id="post-(\d+)"(.*?)</tr>', s, re.S):
    cell = lambda k: (lambda m: text(m.group(1)) if m else '')(re.search(r"class='" + k + r"[^']*'[^>]*>(.*?)</td>", body, re.S))
    title = re.search(r'class="row-title"[^>]*>([^<]+)', body)
    rows.append({'id': int(pid), 'title': html.unescape(title.group(1)) if title else '?', 'status': 'publish' if 'status-publish' in body else re.search(r'status-(\w+)', body).group(1),
                 'client': cell('guru_client'), 'media': cell('guru_media'), 'home': cell('guru_home'), 'cover': 'has-post-thumbnail' in body})
slots = sorted((r['home'], r['title']) for r in rows if r['home'].startswith('#'))
report('showcase', total=len(rows), homepage_slots=slots,
       duplicate_slots=sorted({h for h, _ in slots if [x for x, _ in slots].count(h) > 1}),
       no_client=[r['title'] for r in rows if r['status'] == 'publish' and not r['client']],
       no_media=[r['title'] for r in rows if r['status'] == 'publish' and r['media'] in ('', 'None') and not r['cover']],
       not_published=[f"{r['title']} ({r['status']})" for r in rows if r['status'] != 'publish'])

# New showcase screen: Guru panel open, fields present.
_, s = get('wp-admin/post-new.php?post_type=work')
box = re.search(r'<div id="guru_work_details" class="([^"]*)"', s)
report('showcase-editor', panel_present=bool(box), panel_collapsed=bool(box and 'closed' in box.group(1).split()),
       panel_above_editor=0 <= s.find('id="guru-top-boxes"') < s.find('id="postdivrich"'),
       fields={f: f'id="{f}"' in s for f in ('guru_home_order', 'guru_client', 'guru_tags', 'guru_video_url')},
       cover_box='id="postimagediv"' in s, php_errors=php_errors(s))

# Solution editor panel.
_, s = get('wp-admin/post-new.php?post_type=solution')
box = re.search(r'<div id="guru_solution_media" class="([^"]*)"', s)
report('solution-editor', panel_present=bool(box), panel_collapsed=bool(box and 'closed' in box.group(1).split()), php_errors=php_errors(s))

# Customizer sections.
_, s = get('wp-admin/customize.php')
report('customizer', guru_sections=sorted(set(re.findall(r'"(guru_[a-z_]+)":\{"id":"guru_[a-z_]+"', s))),
       menu_colour_controls=sorted(set(re.findall(r'customize-control-(guru_menu_[a-z]+)', s))))

# Search-engine setting must stay on for staging.
_, s = get('wp-admin/options-reading.php')
m = re.search(r'<input[^>]*id="blog_public"[^>]*>', s)
report('discourage-search-engines', on=bool(m and 'checked' in m.group(0)))

# Users and roles (who will see what).
_, s = get('wp-admin/users.php?number=100')
users = []
for uid, body in re.findall(r'<tr id=[\'"]user-(\d+)[\'"](.*?)</tr>', s, re.S):
    login = re.search(r'<strong><a[^>]*>([^<]+)</a>', body)
    role = re.search(r'data-colname="使用者角色">(.*?)</td>|data-colname="Role">(.*?)</td>', body, re.S)
    users.append({'login': login.group(1) if login else '?', 'roles': text((role.group(1) or role.group(2)) if role else '')})
report('users', users=users)

# Enquiries screen loads.
status, s = get('wp-admin/admin.php?page=guru-enquiries')
report('enquiries-screen', status=status, has_table='wp-list-table' in s or 'guru-enquiries' in s, php_errors=php_errors(s))
