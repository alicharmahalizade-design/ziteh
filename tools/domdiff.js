/*
 * Structural diff: compares the layout box of every design element (by class)
 * with its "zt-" counterpart in the WordPress render.
 *   node tools/domdiff.js <staticUrl> <wpUrl> [width=1440] [tolerance=1]
 */
const { chromium } = require('playwright');
const [a, b, wArg, tolArg] = process.argv.slice(2);
const width = +(wArg || 1440), tol = +(tolArg || 1);

async function boxes(browser, url, prefix, vocab) {
  const ctx = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce', isMobile: width <= 600, hasTouch: width <= 600 });
  const page = await ctx.newPage();
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.evaluate(() => document.fonts.ready);
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 700) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 40)); } window.scrollTo(0, 0); });
  await page.waitForTimeout(300);
  const res = await page.evaluate(([prefix, vocab]) => {
    const out = {};
    const seen = {};
    document.querySelectorAll('[class]').forEach(el => {
      const cls = [...el.classList].filter(c => prefix ? c.startsWith('zt-') : true).map(c => prefix ? c.slice(3) : c)
        .filter(c => !c.startsWith('is-') && c !== 'in' && c !== 'js-reveal' && (!vocab || vocab.includes(c)));
      if (!cls.length) return;
      const key = cls.sort().join('.');
      seen[key] = (seen[key] || 0) + 1;
      if (seen[key] > 3) return;
      const r = el.getBoundingClientRect();
      if (!r.width && !r.height) return;
      out[key + '#' + seen[key]] = [Math.round(r.left), Math.round(r.top + scrollY), Math.round(r.width), Math.round(r.height)];
    });
    return out;
  }, [prefix, vocab || null]);
  await ctx.close();
  return res;
}
(async () => {
  const browser = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const A = await boxes(browser, a, false);
  const vocab = [...new Set(Object.keys(A).flatMap(k => k.split('#')[0].split('.')))];
  const B = await boxes(browser, b, true, vocab);
  await browser.close();
  let n = 0;
  for (const k of Object.keys(A)) {
    if (!B[k]) { console.log('MISSING in wp:', k, A[k].join(',')); n++; continue; }
    const d = A[k].map((v, i) => B[k][i] - v);
    if (d.some(x => Math.abs(x) > tol)) { console.log(k.padEnd(40), 'design', A[k].join(','), ' wp', B[k].join(','), ' Δ', d.join(',')); n++; }
  }
  for (const k of Object.keys(B)) if (!A[k]) console.log('EXTRA in wp:', k);
  console.log('diffs:', n, 'of', Object.keys(A).length);
})();
