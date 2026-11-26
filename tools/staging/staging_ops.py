"""Guru STAGING maintenance helper (cookie login; refuses any site but /staging).

    WP_USER=.. WP_PASS=.. python3 -I staging_ops.py hero-get
    WP_USER=.. WP_PASS=.. python3 -I staging_ops.py upload <file.mp4>
    WP_USER=.. WP_PASS=.. python3 -I staging_ops.py hero-set <desktop_attachment_id> <mobile_attachment_id>
    WP_USER=.. WP_PASS=.. python3 -I staging_ops.py work-find <title substring>
    WP_USER=.. WP_PASS=.. python3 -I staging_ops.py work-video <post_id> <new video url> [--dry]
"""
import html
import http.cookiejar
import json
import os
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
import uuid

BASE = 'https://www.guruonline.com.hk/staging'
assert BASE.endswith('/staging')
jar = http.cookiejar.CookieJar()
op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def req(path, data=None, headers=None, method=None, timeout=300):
    url = path if path.startswith('http') else BASE + '/' + path.lstrip('/')
    h = {'User-Agent': 'guru-maint'}
    h.update(headers or {})
    r = urllib.request.Request(url, data=data, headers=h, method=method)
    try:
        with op.open(r, timeout=timeout) as x:
            return x.status, x.geturl(), x.read().decode('utf-8', 'replace')
    except urllib.error.HTTPError as e:
        return e.code, url, e.read().decode('utf-8', 'replace')


def login():
    req('wp-login.php')
    req('wp-login.php', urllib.parse.urlencode({'log': os.environ['WP_USER'], 'pwd': os.environ['WP_PASS'], 'testcookie': '1'}).encode())
    assert any(c.name.startswith('wordpress_logged_in') for c in jar), 'login failed'


def rest_nonce():
    _, _, s = req('wp-admin/admin-ajax.php?action=rest-nonce')
    return s.strip()


def customizer_settings():
    _, _, s = req('wp-admin/customize.php')
    m = re.search(r'var _wpCustomizeSettings = (\{.*?\});\s*\n', s, re.S)
    return json.loads(m.group(1))


cmd = sys.argv[1]
login()

if cmd == 'peek-customizer':
    _, _, s = req('wp-admin/customize.php')
    st = customizer_settings()
    print('top-level keys:', sorted(st.keys()))
    for m in re.finditer(r'guru_hero_video', s):
        print('...', s[max(0, m.start() - 120):m.start() + 160].replace('\n', ' '))
        break

elif cmd == 'inv-survey':
    # Taxonomy terms (admin list) and one document's edit screen fields — GET only.
    _, _, s = req('wp-admin/edit-tags.php?taxonomy=document_category&post_type=investor_document')
    for row in re.findall(r'<tr id="tag-(\d+)"(.*?)</tr>', s, re.S):
        name = re.search(r'class="row-title"[^>]*>([^<]+)', row[1])
        slug = re.search(r"class='slug column-slug'[^>]*>([^<]*)", row[1])
        cnt = re.search(r"class='posts column-posts num'[^>]*>\s*<a[^>]*>(\d+)", row[1])
        print('term', row[0], html.unescape(name.group(1)) if name else '?', '| slug', slug.group(1) if slug else '?', '| count', cnt.group(1) if cnt else '?')
    print('taxonomy page status hint:', 'edit-tags' in s, len(s))
    for pid in sys.argv[2:]:
        _, _, e = req('wp-admin/post.php?post=%s&action=edit' % pid)
        title = re.search(r'name="post_title"[^>]*value="([^"]*)"', e)
        acf = re.findall(r'data-name="([a-z_]+)"[^>]*data-type="([a-z_]+)"', e)
        dv = re.search(r'name="acf\[[^\]]+\]"[^>]*value="(\d{8})"', e)
        files = re.findall(r'href="([^"]+\.pdf)"', e)
        terms = re.findall(r'<input[^>]*name="tax_input\[document_category\]\[\]"[^>]*value="(\d+)"[^>]*checked', e) + re.findall(r'<input[^>]*value="(\d+)"[^>]*name="tax_input\[document_category\]\[\]"[^>]*checked', e)
        print('doc', pid, '|', html.unescape(title.group(1)) if title else '?', '| acf fields', acf[:6], '| date raw', dv.group(1) if dv else None, '| pdf', files[:2], '| terms', terms)

elif cmd == 'hero-get':
    _, _, s = req('wp-admin/customize.php')
    for k in ('guru_hero_video', 'guru_hero_video_mobile'):
        m = re.search(r'"%s":\{"value":([^,}]*)' % k, s)
        print(k, '=', m.group(1) if m else None)

elif cmd == 'upload':
    path = sys.argv[2]
    name = os.path.basename(path)
    st, _, out = req('index.php?rest_route=/wp/v2/media', data=open(path, 'rb').read(),
                     headers={'X-WP-Nonce': rest_nonce(), 'Content-Type': 'video/mp4',
                              'Content-Disposition': 'attachment; filename="%s"' % name}, method='POST', timeout=900)
    d = json.loads(out)
    print(st, d.get('id'), d.get('source_url'), d.get('code', ''), d.get('message', ''))

elif cmd == 'hero-set':
    desk, mob = int(sys.argv[2]), int(sys.argv[3])
    st = customizer_settings()
    data = {
        'action': 'customize_save', 'wp_customize': 'on', 'customize_theme': st['theme']['stylesheet'],
        'customize_changeset_uuid': st['changeset']['uuid'], 'nonce': st['nonce']['save'],
        'customize_changeset_status': 'publish',
        'customize_changeset_data': json.dumps({'guru_hero_video': {'value': desk}, 'guru_hero_video_mobile': {'value': mob}}),
    }
    s, _, out = req('wp-admin/admin-ajax.php', urllib.parse.urlencode(data).encode())
    print(s, out[:400])

elif cmd == 'work-find':
    _, _, s = req('wp-admin/edit.php?post_type=work&posts_per_page=100&post_status=all')
    for pid, body in re.findall(r'<tr id="post-(\d+)"(.*?)</tr>', s, re.S):
        t = re.search(r'class="row-title"[^>]*>([^<]+)', body)
        if t and sys.argv[2].lower() in html.unescape(t.group(1)).lower():
            print(pid, html.unescape(t.group(1)))

elif cmd == 'work-video':
    pid, new = sys.argv[2], sys.argv[3]
    _, _, s = req('wp-admin/post.php?post=%s&action=edit' % pid)
    form = re.search(r'<form name="post" action="post.php" method="post" id="post"[^>]*>(.*?)</form>', s, re.S).group(1)
    def attr(tag, key):
        m = re.search(r'\b%s\s*=\s*(["\'])(.*?)\1' % key, tag, re.S)
        return html.unescape(m.group(2)) if m else None

    fields = []
    for tag in re.findall(r'<input\b[^>]*>', form):
        name = attr(tag, 'name')
        if not name:
            continue
        typ = (attr(tag, 'type') or 'text').lower()
        if typ in ('checkbox', 'radio') and not re.search(r'\bchecked\b', tag):
            continue
        if typ in ('submit', 'button', 'image', 'file'):
            continue
        fields.append([name, attr(tag, 'value') or ''])
    for m in re.finditer(r'(<textarea\b[^>]*>)(.*?)</textarea>', form, re.S):
        name = attr(m.group(1), 'name')
        if name:
            fields.append([name, html.unescape(m.group(2))])
    for m in re.finditer(r'(<select\b[^>]*>)(.*?)</select>', form, re.S):
        name = attr(m.group(1), 'name')
        opts = re.findall(r'<option\b[^>]*>', m.group(2))
        chosen = next((o for o in opts if re.search(r'\bselected\b', o)), opts[0] if opts else None)
        if name and chosen is not None:
            fields.append([name, attr(chosen, 'value') or ''])
    old = [v for k, v in fields if k == 'guru_video_url']
    print('current video:', old)
    for f in fields:
        if f[0] == 'guru_video_url':
            f[1] = new
    fields.append(['save', 'Update'])
    summary = {k: (v if len(v) < 80 else v[:77] + '...') for k, v in fields if k in ('post_title', 'post_status', 'original_post_status', 'post_type', 'user_ID', 'post_author', 'guru_client', 'guru_home_order', 'guru_tags', 'guru_video_url', '_thumbnail_id', 'action', 'post_ID', 'hidden_post_status', 'visibility', 'menu_order')}
    print('fields:', len(fields), json.dumps(summary, ensure_ascii=False))
    print('content chars:', len(next((v for k, v in fields if k == 'content'), '')),
          '| categories:', [v for k, v in fields if k.startswith('tax_input[work_category]')])
    if '--dry' in sys.argv:
        sys.exit(0)
    s2, url, out = req('wp-admin/post.php', urllib.parse.urlencode([tuple(f) for f in fields]).encode())
    print('saved:', s2, url)
