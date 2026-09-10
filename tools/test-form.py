#!/usr/bin/env python3
"""Submit the footer contact form to the local site five ways and check the enquiries table.

  python3 tools/test-form.py [http://localhost:8899] [~/wptest]
"""
import http.cookiejar, os, re, subprocess, sys, urllib.parse, urllib.request

BASE = sys.argv[1] if len(sys.argv) > 1 else "http://localhost:8899"
WP = os.path.expanduser(sys.argv[2] if len(sys.argv) > 2 else "~/wptest")

jar = http.cookiejar.CookieJar()


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar), NoRedirect())


def php(code):
    out = subprocess.run(["php", "-r", f'require "{WP}/wp-load.php"; {code}'], capture_output=True, text=True)
    return out.stdout.strip()


def count():
    return int(php("echo guru_enquiries_count();") or 0)


def post(fields, label, expect_delta, expect_status):
    before = count()
    data = urllib.parse.urlencode(fields).encode()
    req = urllib.request.Request(BASE + "/wp-admin/admin-post.php", data=data, headers={"User-Agent": "Mozilla/5.0"})
    try:
        r = opener.open(req)
        loc = r.headers.get("Location", "")
    except urllib.error.HTTPError as e:
        loc = e.headers.get("Location", "")
    delta = count() - before
    ok = delta == expect_delta and f"contact={expect_status}" in loc and loc.startswith(BASE)
    print(f"{'✓' if ok else '✗'} {label:26s} stored +{delta} (want +{expect_delta}) -> {loc}")
    return ok


home = urllib.request.urlopen(BASE + "/").read().decode()
nonce = re.search(r'name="guru_contact_nonce" value="([^"]+)"', home).group(1)
base = {"action": "guru_contact", "guru_contact_nonce": nonce, "guru_redirect": BASE + "/",
        "name": "測試 Tester", "company": "Guru QA", "cc": "+852", "phone": "39521100",
        "email": "qa@example.com", "service": "Influencer Marketing", "message": "Line one\nLine two — 中文測試"}

results = [
    post(base, "valid submission", 1, "sent"),
    post({**base, "guru_website": "http://spam.example"}, "honeypot filled (bot)", 0, "sent"),
    post({**base, "email": "not-an-email"}, "invalid email", 0, "error"),
    post({**base, "guru_contact_nonce": "bad"}, "bad nonce", 0, "error"),
    post({**base, "guru_redirect": "https://evil.example/"}, "off-site redirect", 1, "sent"),
]
print("stored row:", php('global $wpdb; echo json_encode($wpdb->get_row("SELECT name, company, email, phone, service, message FROM " . guru_enquiries_table() . " ORDER BY id ASC LIMIT 1", ARRAY_A), JSON_UNESCAPED_UNICODE);'))
sys.exit(0 if all(results) else 1)
