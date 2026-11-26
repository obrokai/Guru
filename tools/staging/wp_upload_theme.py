"""Upload a theme zip to a WordPress site through wp-admin and replace the installed copy.

Usage: WP_USER=... WP_PASS=... python3 -I wp_upload_theme.py <site-base-url> <zip-path> [--dry-run]
--dry-run logs in and fetches the upload form only.
"""
import html
import http.cookiejar
import os
import re
import sys
import urllib.parse
import urllib.request
import uuid

base, zip_path = sys.argv[1].rstrip('/'), sys.argv[2]
if not base.endswith('/staging'):
    print('REFUSED: only the /staging site may be changed:', base)
    sys.exit(1)
dry = '--dry-run' in sys.argv
UA = 'Mozilla/5.0 (Macintosh) Chrome/130'

jar = http.cookiejar.CookieJar()
opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))


def req(url, data=None, headers=None, timeout=300):
    h = {'User-Agent': UA}
    h.update(headers or {})
    r = urllib.request.Request(url, data=data, headers=h)
    with opener.open(r, timeout=timeout) as resp:
        return resp.geturl(), resp.read().decode('utf-8', 'replace')


# 1. Log in.
req(f'{base}/wp-login.php')
form = urllib.parse.urlencode({
    'log': os.environ['WP_USER'], 'pwd': os.environ['WP_PASS'], 'testcookie': '1',
    'redirect_to': f'{base}/wp-admin/', 'wp-submit': 'Log In',
}).encode()
url, body = req(f'{base}/wp-login.php', form)
if not any(c.name.startswith('wordpress_logged_in') for c in jar):
    err = re.search(r'id="login_error"[^>]*>(.*?)</div>', body, re.S)
    print('LOGIN FAILED:', re.sub('<[^>]+>', ' ', err.group(1)).strip() if err else url)
    sys.exit(2)
print('login ok')

# 2. Upload form + nonce.
url, body = req(f'{base}/wp-admin/theme-install.php?upload')
m = re.search(r'<form[^>]*action="([^"]*update\.php\?action=upload-theme[^"]*)"(.*?)</form>', body, re.S)
if not m:
    print('upload form not found (account may lack install_themes)')
    sys.exit(3)
action = html.unescape(m.group(1))
fields = dict(re.findall(r'<input[^>]*type="hidden"[^>]*name="([^"]+)"[^>]*value="([^"]*)"', m.group(2)))
print('upload form ok, hidden fields:', sorted(fields))
if dry:
    sys.exit(0)

# 3. Multipart upload.
boundary = uuid.uuid4().hex
parts = []
for k, v in fields.items():
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{k}"\r\n\r\n{html.unescape(v)}\r\n'.encode())
parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="install-theme-submit"\r\n\r\nInstall Now\r\n'.encode())
fname = os.path.basename(zip_path)
parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="themezip"; filename="{fname}"\r\nContent-Type: application/zip\r\n\r\n'.encode())
parts.append(open(zip_path, 'rb').read())
parts.append(f'\r\n--{boundary}--\r\n'.encode())
data = b''.join(parts)
action_url = urllib.parse.urljoin(f'{base}/wp-admin/', action)
url, body = req(action_url, data, {'Content-Type': f'multipart/form-data; boundary={boundary}'}, timeout=600)
text = re.sub(r'\s+', ' ', re.sub('<[^>]+>', ' ', html.unescape(body)))
print('upload response:', text[text.find('wrap') if 'wrap' in text else 0:][:0] or '')

# 4. "Theme already installed" → follow the replace link.
m = re.search(r'href="([^"]*overwrite=update-theme[^"]*)"', body)
if m:
    rep = urllib.parse.urljoin(f'{base}/wp-admin/', html.unescape(m.group(1)))
    print('same theme installed; replacing with uploaded version')
    url, body = req(rep, timeout=600)
    text = re.sub(r'\s+', ' ', re.sub('<[^>]+>', ' ', html.unescape(body)))

for key in ('佈景主題更新成功', '已成功', 'successfully', '安裝失敗', 'failed', 'Error', '錯誤'):
    i = text.find(key)
    if i >= 0:
        print('result:', text[max(0, i - 200):i + 200])
        break
else:
    i = text.find('wpbody-content')
    print('result (unrecognised):', text[-1500:])
