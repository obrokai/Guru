// Drive headless Chrome over the DevTools protocol: log in to the LOCAL test site,
// open the admin screens the client guide describes, and save annotated screenshots.
// Usage: node shoot-admin.mjs <chrome-devtools-json-url> <out-dir>
import { writeFileSync, mkdirSync } from 'node:fs';

const [, , base = 'http://127.0.0.1:9333', OUT = '/tmp/guru-admin-shots'] = process.argv;
const SITE = 'http://localhost:8899';
mkdirSync(OUT, { recursive: true });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

// ---- minimal CDP client -------------------------------------------------------
const version = await (await fetch(`${base}/json/version`)).json();
const browser = new WebSocket(version.webSocketDebuggerUrl);
await new Promise((r) => (browser.onopen = r));
let seq = 0;
const pending = new Map();
const listeners = [];
browser.onmessage = (ev) => {
  const msg = JSON.parse(ev.data);
  if (msg.id && pending.has(msg.id)) { const { res, rej } = pending.get(msg.id); pending.delete(msg.id); msg.error ? rej(new Error(msg.error.message)) : res(msg.result); }
  else listeners.forEach((l) => l(msg));
};
const send = (method, params = {}, sessionId) => new Promise((res, rej) => { const id = ++seq; pending.set(id, { res, rej }); browser.send(JSON.stringify({ id, method, params, sessionId })); });
const waitEvent = (name, sessionId, timeout = 20000) => new Promise((res, rej) => {
  const t = setTimeout(() => { listeners.splice(listeners.indexOf(l), 1); rej(new Error('timeout ' + name)); }, timeout);
  const l = (m) => { if (m.method === name && m.sessionId === sessionId) { clearTimeout(t); listeners.splice(listeners.indexOf(l), 1); res(m.params); } };
  listeners.push(l);
});

const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId: S } = await send('Target.attachToTarget', { targetId, flatten: true });
await send('Page.enable', {}, S);
await send('Runtime.enable', {}, S);
await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false }, S);

const evalJs = async (expr) => (await send('Runtime.evaluate', { expression: expr, awaitPromise: true, returnByValue: true }, S)).result.value;
const goto = async (url, settle = 1200) => {
  const loaded = waitEvent('Page.loadEventFired', S, 45000);
  await send('Page.navigate', { url }, S);
  await loaded; await sleep(settle);
  await evalJs(`(() => { document.querySelectorAll('.update-nag, #update-nag, .notice-warning, .wp-pointer, #wp-admin-bar-updates').forEach(e => e.remove()); return true; })()`);
};
const fillShowcase = () => evalJs(`(() => {
  const set = (sel, v) => { const el = document.querySelector(sel); if (el) { el.value = v; el.dispatchEvent(new Event('input', { bubbles: true })); } };
  set('#title', 'Pandora × Influencers'); const ph = document.querySelector('#title-prompt-text'); if (ph) ph.style.display = 'none';
  set('#guru_client', 'Pandora'); set('#guru_tags', 'Influencer Marketing, Social Media'); set('#guru_home_order', '2'); return true; })()`);
const shot = async (name, { full = false, clip = null } = {}) => {
  if (full) {
    const h = await evalJs('Math.min(document.documentElement.scrollHeight, 4200)');
    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: h, deviceScaleFactor: 1, mobile: false }, S);
    await sleep(300);
  }
  const params = { format: 'png', captureBeyondViewport: !!full };
  if (clip) params.clip = { ...clip, scale: 1 };
  const { data } = await send('Page.captureScreenshot', params, S);
  writeFileSync(`${OUT}/${name}.png`, Buffer.from(data, 'base64'));
  if (full) await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false }, S);
  console.log('saved', name);
};
// Bounding box of an element, in page pixels (after scrolling it into view).
const box = async (selector, pad = 12) => {
  const r = await evalJs(`(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return null; el.scrollIntoView({block:'center'}); const b = el.getBoundingClientRect(); return { x: b.left + window.scrollX, y: b.top + window.scrollY, width: b.width, height: b.height }; })()`);
  if (!r) throw new Error('no element ' + selector);
  return { x: Math.max(0, r.x - pad), y: Math.max(0, r.y - pad), width: r.width + pad * 2, height: r.height + pad * 2 };
};
// A red ring around one element, and optional callout labels, for the client's eyes.
const highlight = async (selector, label = '', pos = 'above') => evalJs(`(() => {
  const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return false;
  el.style.outline = '3px solid #e5484d'; el.style.outlineOffset = '4px'; el.style.borderRadius = '6px';
  if (${JSON.stringify(label)}) { const t = document.createElement('div'); t.textContent = ${JSON.stringify(label)};
    // z-index above the media modal (160000) and the Customizer pane.
    t.style.cssText = 'position:absolute;z-index:2147483000;background:#e5484d;color:#fff;font:700 15px/1 -apple-system,PingFang TC,sans-serif;padding:6px 10px;border-radius:999px;box-shadow:0 2px 8px rgba(0,0,0,.25);white-space:nowrap';
    const b = el.getBoundingClientRect();
    // Inside a fixed overlay (media modal, Customizer pane) position the label in viewport space.
    const fixed = !!el.closest('.media-modal, #customize-controls, .wp-full-overlay-sidebar');
    const sx = fixed ? 0 : window.scrollX, sy = fixed ? 0 : window.scrollY; if (fixed) t.style.position = 'fixed';
    const p = ${JSON.stringify(pos)};
    if (p === 'right') { t.style.left = (b.right + sx + 14) + 'px'; t.style.top = (b.top + sy + b.height/2 - 14) + 'px'; }
    else if (p === 'below') { t.style.left = (b.left + sx - 4) + 'px'; t.style.top = (b.bottom + sy + 8) + 'px'; }
    else if (p === 'left') { t.style.right = (window.innerWidth - b.left - sx + 14) + 'px'; t.style.top = (b.top + sy + b.height/2 - 14) + 'px'; }
    else { t.style.left = (b.left + sx - 4) + 'px'; t.style.top = (b.top + sy - 34) + 'px'; }
    document.body.appendChild(t); }
  return true; })()`);

// ---- log in -------------------------------------------------------------------
await goto(`${SITE}/wp-login.php`, 600);
await evalJs(`(() => { document.querySelector('#user_login').value = 'admin'; document.querySelector('#user_pass').value = 'localpass'; document.querySelector('#loginform').submit(); return true; })()`);
await waitEvent('Page.loadEventFired', S, 45000); await sleep(800);
console.log('logged in:', await evalJs('document.body.classList.contains("wp-admin")'));
// Dismiss the pointer/welcome nags so they do not sit on the screenshots.
await evalJs(`(() => { document.querySelectorAll('.wp-pointer, #wp-admin-bar-updates, .notice-warning, .update-nag').forEach(e => e.remove()); return true; })()`);

// ---- 1. Showcase list with "Add new" ------------------------------------------
await goto(`${SITE}/wp-admin/edit.php?post_type=work`);
await highlight('#menu-posts-work', '① 左側選單 Showcase', 'right');
await highlight('.page-title-action', '② 按「Add new showcase item」', 'right');
await shot('01-showcase-list', { full: false });

// ---- 2. New showcase: whole edit screen ---------------------------------------
await goto(`${SITE}/wp-admin/post-new.php?post_type=work`, 1500);
await fillShowcase();
await highlight('#guru_work_details', 'A  Showcase details — 填這裡');
await highlight('#postimagediv', 'B  Cover image — 封面圖放這裡');
await highlight('#work_categorydiv', 'B  勾分類');
await highlight('#publishing-action', '最後按「發佈」');
await shot('02-showcase-edit-full', { full: true });

// ---- 3. Close-up: Showcase details box -----------------------------------------
await goto(`${SITE}/wp-admin/post-new.php?post_type=work`, 1500);
await fillShowcase();
await shot('03-showcase-details', { clip: await box('#guru_work_details', 16) });

// ---- 4. Close-up: right column (publish + cover image + categories) -----------
await shot('04-right-column', { clip: await box('#side-sortables', 16) });

// ---- 5. Media modal after "Set cover image" -----------------------------------
await evalJs(`(() => { const a = document.querySelector('#set-post-thumbnail'); if (a) a.click(); return !!a; })()`);
await sleep(1800);
await evalJs(`(() => { const tab = document.querySelector('#menu-item-upload'); if (tab) tab.click(); return true; })()`);
await sleep(1200);
await highlight('#menu-item-upload', '① 上傳檔案（或到「媒體庫」選已有的圖）');
await highlight('.upload-ui', '② 把圖拖進來，或按「選取檔案」');
await highlight('.media-frame-toolbar .media-button-select', '③ 選好後按「Set cover image」', 'left');
await shot('05-cover-image-modal', { full: false });

// ---- 6. Customizer → Typography ----------------------------------------------
await goto(`${SITE}/wp-admin/customize.php?autofocus[section]=guru_typography&url=${encodeURIComponent(SITE + '/about/')}`, 4500);
await evalJs(`(() => { document.querySelectorAll('#customize-controls .notice, .customize-pane-parent .notice').forEach(e => e.remove()); return true; })()`);
await sleep(600);
await highlight('#customize-control-guru_fs_body', 'Body text  內文');
await highlight('#customize-control-guru_fs_heading', 'Section headings  區塊標題');
await highlight('#customize-control-guru_fs_hero', 'Page titles  頁面大標題');
await highlight('#customize-control-guru_fs_nav', 'Navigation links  導覽列');
await highlight('#save', '改完按「發佈」', 'below');
await shot('06-typography', { full: false });

// ---- 0b. Sanity: at 100% the hero title must keep its designed size ----------
await goto(`${SITE}/about/`, 800);
const heroPx = await evalJs("parseFloat(getComputedStyle(document.querySelector('.abt-hero-title')).fontSize)");
const bodyPx = await evalJs("parseFloat(getComputedStyle(document.querySelector('.abt-story-p')).fontSize)");
console.log(`computed at 100%: .abt-hero-title=${heroPx}px .abt-story-p=${bodyPx}px`);
if (heroPx < 50) throw new Error('typography regression: hero title collapsed to ' + heroPx + 'px');

// ---- 7. Customizer entry point: 外觀 → 自訂 ---------------------------------------
await goto(`${SITE}/wp-admin/themes.php`);
await highlight('#menu-appearance', '① 外觀', 'right');
await highlight('#menu-appearance .wp-submenu a[href*="customize"]', '② 自訂', 'right');
await shot('07-appearance-customize', { full: false });

await send('Target.closeTarget', { targetId });
browser.close();
console.log('done');
