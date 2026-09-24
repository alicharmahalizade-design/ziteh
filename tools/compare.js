/*
 * Pixel comparison: static design vs. WordPress + Ziteh Core.
 *
 *   node tools/compare.js <name> <staticUrl> <wpUrl> [widths=1440,1024,430] [outDir]
 *
 * Takes full-page screenshots of both (reduced motion, fonts loaded) and
 * writes a diff image + mismatch percentage for every width.
 * Requires: playwright, pixelmatch, pngjs (NODE_PATH may point to them).
 */
const { chromium } = require('playwright');
const pixelmatch = require('pixelmatch');
const { PNG } = require('pngjs');
const fs = require('fs');
const path = require('path');

const [name, a, b, widthsArg, outArg] = process.argv.slice(2);
const widths = (widthsArg || '1440,1024,430').split(',').map(Number);
const out = outArg || path.join(process.cwd(), 'shots');
fs.mkdirSync(out, { recursive: true });

async function shot(browser, url, width, file) {
  const ctx = await browser.newContext({
    viewport: { width, height: 900 },
    deviceScaleFactor: 1,
    reducedMotion: 'reduce',
    isMobile: width <= 600,
    hasTouch: width <= 600,
  });
  const page = await ctx.newPage();
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.evaluate(() => document.fonts.ready);
  // freeze dynamic bits
  await page.addStyleTag({ content: '*{caret-color:transparent!important} .zt-scrollprog,.scrollprog{display:none!important}' });
  await page.evaluate(async () => {
    // scroll through to trigger reveal/lazy images, then back to top
    for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 30)); }
    window.scrollTo(0, 0);
    await new Promise(r => setTimeout(r, 300));
    document.querySelectorAll('img').forEach(i => { i.loading = 'eager'; });
  });
  await page.waitForTimeout(400);
  await page.screenshot({ path: file, fullPage: true });
  await ctx.close();
}

(async () => {
  const browser = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const results = [];
  for (const w of widths) {
    const fa = path.join(out, `${name}-${w}-design.png`);
    const fb = path.join(out, `${name}-${w}-wp.png`);
    await shot(browser, a, w, fa);
    await shot(browser, b, w, fb);
    const A = PNG.sync.read(fs.readFileSync(fa));
    const B = PNG.sync.read(fs.readFileSync(fb));
    const W = Math.max(A.width, B.width), H = Math.max(A.height, B.height);
    const pad = img => { const p = new PNG({ width: W, height: H }); p.data.fill(255); PNG.bitblt(img, p, 0, 0, img.width, img.height, 0, 0); return p; };
    const PA = pad(A), PB = pad(B), D = new PNG({ width: W, height: H });
    const n = pixelmatch(PA.data, PB.data, D.data, W, H, { threshold: 0.1 });
    fs.writeFileSync(path.join(out, `${name}-${w}-diff.png`), PNG.sync.write(D));
    results.push({ width: w, design: `${A.width}x${A.height}`, wp: `${B.width}x${B.height}`, diffPct: +(100 * n / (W * H)).toFixed(3) });
  }
  await browser.close();
  console.log(JSON.stringify({ name, results }, null, 1));
})().catch(e => { console.error(e); process.exit(1); });
