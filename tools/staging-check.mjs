// Read-only front-end check of a deployed Guru site (normally the staging site).
// Needs headless Chrome with DevTools on :9333 — start it with tools/chrome-cdp.sh.
//
//   node tools/staging-check.mjs [base-url] [out-dir]
//   base-url defaults to https://www.guruonline.com.hk/staging/
//
// Prints one JSON line per check and saves screenshots (desktop 1440×900 and
// mobile 390×844) for every page in the navigation. Never logs in, never submits.
import { writeFileSync, mkdirSync } from 'node:fs';

const BASE = (process.argv[2] || 'https://www.guruonline.com.hk/staging/').replace(/\/?$/, '/');
const OUT = process.argv[3] || '/tmp/guru-staging-check';
mkdirSync(OUT, { recursive: true });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const report = (check, data) => console.log(JSON.stringify({ check, ...data }));

// ---- minimal CDP client --------------------------------------------------------
const version = await (await fetch('http://127.0.0.1:9333/json/version')).json();
const ws = new WebSocket(version.webSocketDebuggerUrl);
await new Promise((r) => (ws.onopen = r));
let seq = 0;
const pending = new Map();
let pageErrors = [];
ws.onmessage = (ev) => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) { const { res, rej } = pending.get(m.id); pending.delete(m.id); m.error ? rej(new Error(m.error.message)) : res(m.result); return; }
  if (m.method === 'Runtime.exceptionThrown') pageErrors.push('JS: ' + (m.params.exceptionDetails.exception?.description || m.params.exceptionDetails.text).split('\n')[0]);
  if (m.method === 'Log.entryAdded' && m.params.entry.level === 'error') pageErrors.push(`${m.params.entry.source}: ${m.params.entry.text} ${m.params.entry.url || ''}`.trim());
};
const send = (method, params = {}, sessionId) => new Promise((res, rej) => { const id = ++seq; pending.set(id, { res, rej }); ws.send(JSON.stringify({ id, method, params, sessionId })); });
const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId: S } = await send('Target.attachToTarget', { targetId, flatten: true });
for (const d of ['Page', 'Runtime', 'Log', 'Network']) await send(`${d}.enable`, {}, S);
await send('Network.setCacheDisabled', { cacheDisabled: true }, S);
const js = async (e) => (await send('Runtime.evaluate', { expression: e, awaitPromise: true, returnByValue: true }, S)).result.value;
const shot = async (name) => { const { data } = await send('Page.captureScreenshot', { format: 'png' }, S); writeFileSync(`${OUT}/${name}.png`, Buffer.from(data, 'base64')); };
const viewport = (mobile) => send('Emulation.setDeviceMetricsOverride', mobile ? { width: 390, height: 844, deviceScaleFactor: 2, mobile: true } : { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false }, S);
const open = async (url, wait = 5000) => { pageErrors = []; await send('Page.navigate', { url: url + (url.includes('?') ? '&' : '?') + 'qa=' + Date.now() }, S); await sleep(wait); };

// ---- 1. theme version + raw HTML of every nav page -------------------------------
const css = await (await fetch(BASE + 'wp-content/themes/guru-theme/style.css?qa=' + Date.now())).text();
report('theme-version', { version: (css.match(/^Version:\s*(\S+)/m) || [])[1], released: (css.match(/^Released:\s*(\S+)/m) || [])[1] });

const homeHtml = await (await fetch(BASE, { headers: { 'User-Agent': 'Mozilla/5.0 guru-qa' } })).text();
const navUrls = [...new Set([...homeHtml.matchAll(/class="gn-mobile-link"[^>]*href="([^"]+)"|href="([^"]+)"[^>]*class="gn-mobile-link"/g)].map((m) => (m[1] || m[2]).replace(/&#038;|&amp;/g, '&')))];
const pages = [['home', BASE], ...navUrls.map((u) => [u.replace(BASE, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'page', u])];
for (const [name, url] of pages) {
  const r = await fetch(url, { headers: { 'User-Agent': 'Mozilla/5.0 guru-qa' } });
  const html = await r.text();
  report('page-http', {
    page: name, url, status: r.status,
    phpErrors: (html.match(/(Fatal error|Parse error|Warning|Notice|Deprecated)<\/b>:[^<]{0,160}/g) || []).slice(0, 5),
    robots: (html.match(/<meta name='robots' content='([^']+)'/) || [])[1] || null,
  });
}

// ---- 2. homepage behaviour, both sizes ------------------------------------------------
for (const mobile of [false, true]) {
  const size = mobile ? 'mob' : 'desk';
  await viewport(mobile);
  await open(BASE, 6000);
  report('home-structure', { size, ...(await js(`(() => {
    const ids = [...document.querySelectorAll('main section, body > section, section[id], section.gh')].map(s => s.id || s.className.split(' ')[0]);
    const a = document.querySelector('#about'), t = document.querySelector('.abt-title');
    return { sections: [...new Set(ids)], aboutHeight: a ? a.offsetHeight : null, aboutTitlePx: t ? getComputedStyle(t).fontSize : null,
      workCards: document.querySelectorAll('#work .wrk-card').length, solutions: document.querySelectorAll('.svc-acc-item').length,
      brokenImages: [...document.images].filter(i => i.complete && i.naturalWidth === 0 && i.src).map(i => i.src.split('/').pop()).slice(0, 10),
      footerCopy: [...document.querySelectorAll('.ftr-copy')].map(p => p.textContent.trim()) };
  })()`)) });
  report('hero-video', { size, ...(await js(`(async () => {
    const v = document.getElementById('heroVideo');
    if (!v) return { found: false };
    for (let i = 0; i < 40 && !(v.duration > 0); i++) await new Promise(r => setTimeout(r, 250));
    const o = { found: true, loop: v.loop, duration: Math.round(v.duration * 10) / 10, src: (v.currentSrc || '').split('/').pop(), playing: !v.paused };
    try { v.currentTime = Math.max(0, v.duration - 0.6); await v.play(); } catch (e) {}
    await new Promise(r => setTimeout(r, 2500));
    o.stopsAtEnd = v.ended && v.paused;
    return o;
  })()`)) });
  await shot(`home-${size}-top`);
  await js(`document.querySelector('#about') && window.scrollTo(0, document.querySelector('#about').getBoundingClientRect().top + scrollY - 70)`);
  await sleep(1500);
  await shot(`home-${size}-about`);
  await js('window.scrollTo(0, document.body.scrollHeight)');
  await sleep(1200);
  await shot(`home-${size}-footer`);
  if (mobile) {
    await js(`window.scrollTo(0, 0); document.getElementById('gnToggle') && document.getElementById('gnToggle').click()`);
    await sleep(800);
    report('mobile-menu', await js(`(() => { const m = document.getElementById('gnMobileMenu'); if (!m) return { found: false };
      return { open: m.classList.contains('open'), links: [...m.querySelectorAll('.gn-mobile-link')].map(a => a.textContent.trim()),
        bg: getComputedStyle(m).backgroundColor, contact: getComputedStyle(m.querySelector('.gn-link-cta')).backgroundColor,
        whatsapp: getComputedStyle(m.querySelector('.gn-link-whatsapp')).backgroundColor }; })()`));
    await shot('home-mob-menu');
  }
  report('console-errors', { page: 'home', size, errors: pageErrors.slice(0, 10) });
}

// ---- 3. every other nav page: screenshot + console errors -------------------------------
for (const [name, url] of pages.slice(1)) {
  for (const mobile of [false, true]) {
    await viewport(mobile);
    await open(url, 4500);
    await shot(`${name}-${mobile ? 'mob' : 'desk'}`);
    report('console-errors', { page: name, size: mobile ? 'mob' : 'desk', errors: pageErrors.slice(0, 10),
      brokenImages: await js(`[...document.images].filter(i => i.complete && i.naturalWidth === 0 && i.src).map(i => i.src.split('/').pop()).slice(0, 10)`) });
  }
}

report('done', { screenshots: OUT });
ws.close();
process.exit(0);
