#!/bin/zsh
# Start headless Chrome with the DevTools port open (for shoot-admin.mjs).
pkill -f 'Google Chrome.*--headless' 2>/dev/null
rm -rf /tmp/guru-chrome-cdp
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" --headless=new --disable-gpu --hide-scrollbars \
  --no-first-run --no-default-browser-check --remote-debugging-port=9333 \
  --user-data-dir=/tmp/guru-chrome-cdp --window-size=1440,1000 about:blank >/dev/null 2>&1 &
for i in $(seq 1 30); do curl -s -o /dev/null http://127.0.0.1:9333/json/version && break; sleep 1; done
curl -s http://127.0.0.1:9333/json/version | python3 -c "import sys,json; d=json.load(sys.stdin); print('devtools ready:', d.get('Browser'))"
