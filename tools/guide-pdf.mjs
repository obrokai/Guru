// Print the client guide (docs/客戶教學-上架作品與調整字體.html) to PDF through headless
// Chrome's DevTools protocol. Chrome's own --print-to-pdf flag stalls on this
// machine, the protocol does not.
//
//   tools/chrome-cdp.sh                 # start headless Chrome on port 9333
//   node tools/guide-pdf.mjs            # writes docs/客戶教學-上架作品與調整字體.pdf
//   pkill -f 'Google Chrome.*--headless'
//
// The HTML is the artifact-style single file (no <html>/<head> wrapper, dark-mode
// tokens, screenshots inlined as data URIs). For paper we wrap it, force the
// light palette, drop shadows, and keep each step / figure / table on one page.
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const REPO = dirname(dirname(fileURLToPath(import.meta.url)));
const NAME = '客戶教學-上架作品與調整字體';
const SRC = join(REPO, 'docs', `${NAME}.html`);
const OUT = process.argv[2] || join(REPO, 'docs', `${NAME}.pdf`);
const TMP = join(process.env.CLAUDE_JOB_DIR || '/tmp', 'tmp');
mkdirSync(TMP, { recursive: true });
const PRINT_HTML = join(TMP, 'guide-print.html');

const printCss = `
<style>
  @page { size: A4; margin: 14mm 13mm 16mm; }
  html { color-scheme: light only; }
  :root, :root[data-theme="dark"] {
    --bg: #ffffff; --surface: #ffffff; --ink: #0e0e10; --ink-2: #3a3a3f; --ink-3: #6d6d72;
    --line: rgba(14,14,16,0.16); --green: #3f8f31; --green-ink: #3f8f31; --blue: #1b23e0;
    --blue-soft: rgba(27,35,224,0.08); --red: #e5484d; --warn-bg: #fff6e5; --warn-line: #e6a23c;
    --warn-ink: #7a4a00; --chip-bg: #ffffff; --shadow: none;
  }
  body { background: #fff; font-size: 12.5px; line-height: 1.6; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .page { max-width: none; padding: 0; }
  h1 { font-size: 30px; }
  h2 { font-size: 20px; margin: 24px 0 10px; break-after: avoid; }
  h3 { break-after: avoid; margin: 24px 0 10px; }
  .step { margin: 18px 0 8px; break-after: avoid; break-inside: avoid; }
  .step .n { width: 32px; height: 32px; line-height: 32px; font-size: 15px; }
  .step h4 { font-size: 16px; padding-top: 5px; }
  .body { margin-left: 44px; }
  figure { break-inside: avoid; margin: 8px 0 4px; }
  figure img { width: 90%; border-radius: 6px; box-shadow: none; border: 1px solid var(--line); }
  figure.narrow { max-width: 300px; }
  .two { grid-template-columns: 1fr 260px; break-inside: avoid; }
  .tbl-wrap { overflow: visible; break-inside: avoid; }
  table { font-size: 11.5px; break-inside: avoid; }
  th, td { padding: 6px 9px; }
  .note, .tip { break-inside: avoid; font-size: 12px; margin: 12px 0; }
  .doc-head { margin-bottom: 18px; padding-bottom: 14px; }
  .doc-lead { font-size: 13px; }
  .foot { margin-top: 30px; }
  a { color: inherit; text-decoration: none; }
</style>`;

const body = readFileSync(SRC, 'utf8');
const html = `<!doctype html><html lang="zh-Hant"><head><meta charset="utf-8">${body.replace(/<\/style>/, '</style>' + printCss)}</head></html>`;
writeFileSync(PRINT_HTML, html);

const base = 'http://127.0.0.1:9333';
const version = await (await fetch(`${base}/json/version`)).json();
const ws = new WebSocket(version.webSocketDebuggerUrl);
await new Promise((r) => (ws.onopen = r));
let seq = 0; const pending = new Map(); const listeners = [];
ws.onmessage = (ev) => { const m = JSON.parse(ev.data); if (m.id && pending.has(m.id)) { const { res, rej } = pending.get(m.id); pending.delete(m.id); m.error ? rej(new Error(m.error.message)) : res(m.result); } else listeners.forEach((l) => l(m)); };
const send = (method, params = {}, sessionId) => new Promise((res, rej) => { const id = ++seq; pending.set(id, { res, rej }); ws.send(JSON.stringify({ id, method, params, sessionId })); });
const waitEvent = (name, sessionId, timeout = 45000) => new Promise((res, rej) => { const t = setTimeout(() => rej(new Error('timeout ' + name)), timeout); const l = (m) => { if (m.method === name && m.sessionId === sessionId) { clearTimeout(t); listeners.splice(listeners.indexOf(l), 1); res(m.params); } }; listeners.push(l); });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId: S } = await send('Target.attachToTarget', { targetId, flatten: true });
await send('Page.enable', {}, S); await send('Runtime.enable', {}, S);
await send('Emulation.setEmulatedMedia', { media: 'print' }, S);
const loaded = waitEvent('Page.loadEventFired', S);
await send('Page.navigate', { url: pathToFileURL(PRINT_HTML).href }, S);
try { await loaded; } catch (e) { console.log('load timeout, printing anyway'); }
// Web fonts (DM Sans / Noto Sans TC) arrive after load; give them a moment.
await send('Runtime.evaluate', { expression: 'document.fonts ? document.fonts.ready.then(() => true) : true', awaitPromise: true }, S);
await sleep(800);
const { data } = await send('Page.printToPDF', {
  printBackground: true,
  preferCSSPageSize: true,
  displayHeaderFooter: true,
  headerTemplate: '<span></span>',
  footerTemplate: '<div style="width:100%;font-size:8px;color:#8a8a90;text-align:center;font-family:-apple-system,Helvetica,sans-serif;">Guru 後台操作指南 · <span class="pageNumber"></span> / <span class="totalPages"></span></div>',
  marginTop: 0.55, marginBottom: 0.63, marginLeft: 0.51, marginRight: 0.51, // inches ≈ the @page margins
}, S);
writeFileSync(OUT, Buffer.from(data, 'base64'));
console.log('saved', OUT, Math.round(Buffer.byteLength(data, 'base64') / 1024), 'KB');
await send('Target.closeTarget', { targetId }); ws.close();
