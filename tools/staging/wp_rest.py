"""Small cookie-authenticated REST client for the Guru STAGING site.

    WP_USER=... WP_PASS=... python3 -I wp_rest.py GET  /wp/v2/pages/14094?context=edit
    WP_USER=... WP_PASS=... python3 -I wp_rest.py POST /wp/v2/pages/14094 '{"status":"draft"}'

Refuses any site other than /staging. Prints the JSON response.
"""
import http.cookiejar
import json
import os
import re
import sys
import urllib.parse
import urllib.request

BASE = 'https://www.guruonline.com.hk/staging'
assert BASE.endswith('/staging')
method, route = sys.argv[1].upper(), sys.argv[2]
body = sys.argv[3] if len(sys.argv) > 3 else None
assert method in ('GET', 'POST')

jar = http.cookiejar.CookieJar()
op = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def raw(url, data=None, headers=None, m=None):
    h = {'User-Agent': 'guru-maint'}
    h.update(headers or {})
    req = urllib.request.Request(url, data=data, headers=h, method=m)
    try:
        with op.open(req, timeout=120) as r:
            return r.status, r.read().decode('utf-8', 'replace')
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8', 'replace')


raw(f'{BASE}/wp-login.php')
raw(f'{BASE}/wp-login.php', urllib.parse.urlencode({'log': os.environ['WP_USER'], 'pwd': os.environ['WP_PASS'], 'testcookie': '1'}).encode())
if not any(c.name.startswith('wordpress_logged_in') for c in jar):
    sys.exit('login failed')

# REST nonce from an admin screen that loads wp-api-fetch.
_, page = raw(f'{BASE}/wp-admin/edit.php?post_type=page')
m = re.search(r'wp\.apiFetch\.nonceMiddleware\s*=\s*wp\.apiFetch\.createNonceMiddleware\(\s*"([0-9a-f]+)"', page) or re.search(r'"nonce":"([0-9a-f]{10})"', page)
if not m:
    _, page = raw(f'{BASE}/wp-admin/admin-ajax.php?action=rest-nonce')
    nonce = page.strip()
else:
    nonce = m.group(1)

route_q = route.split('?', 1)
url = f'{BASE}/index.php?rest_route={urllib.parse.quote(route_q[0])}' + ('&' + route_q[1] if len(route_q) > 1 else '')
status, out = raw(url, data=body.encode() if body else None,
                  headers={'X-WP-Nonce': nonce, 'Content-Type': 'application/json'}, m=method)
print(status)
print(out)
