/*
 * End-to-end purchase flow against a WordPress + Ziteh Core site.
 *   node tools/e2e.js <siteUrl> <productUrl>
 * product → add to cart → cart (qty +) → checkout (Gilan / Lahijan) →
 * place order → tracking page. Prints a PASS/FAIL line per step.
 */
const { chromium } = require('playwright');
const [site, productUrl] = process.argv.slice(2);
const results = [];
const check = (name, ok, extra) => { results.push([name, ok]); console.log((ok ? 'PASS ' : 'FAIL ') + name + (extra ? '  — ' + extra : '')); };
const en = s => String(s || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[^\d]/g, '');

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const errors = [];
  p.on('pageerror', e => errors.push(e.message));

  // 1 · product page → add to cart
  await p.goto(productUrl, { waitUntil: 'networkidle' });
  check('product page renders title', (await p.textContent('h1')).trim().length > 5);
  await p.click('.zt-buybox [data-zt-qty] [data-zt-step="1"]');
  await p.click('[data-zt-buybox-add]');
  await p.waitForFunction(() => { const b = document.querySelector('[data-zt-cart-badge]'); return b && b.textContent.trim() !== '۰'; }, null, { timeout: 15000 }).catch(() => {});
  const badge = await p.textContent('[data-zt-cart-badge]');
  check('add to cart updates header badge (qty 2)', en(badge) === '2', 'badge=' + badge);

  // 2 · cart page
  await p.goto(site + '/cart/', { waitUntil: 'networkidle' });
  const rows = await p.$$('[data-zt-line]');
  check('cart shows the line', rows.length === 1, 'rows=' + rows.length);
  const sub1 = en(await p.textContent('[data-zt-sum-subtotal]'));
  await p.click('[data-zt-line] [data-zt-step="1"]');
  await p.waitForFunction((s) => { const e = document.querySelector('[data-zt-sum-subtotal]'); return e && e.textContent.replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)).replace(/[^\d]/g, '') !== s; }, sub1, { timeout: 15000 }).catch(() => {});
  const sub2 = en(await p.textContent('[data-zt-sum-subtotal]'));
  check('qty + recalculates subtotal via AJAX', +sub2 === +sub1 / 2 * 3, sub1 + ' → ' + sub2);
  const msg = await p.textContent('[data-zt-tier-msg]');
  check('tier message is live', /فاصله دارید|فعال شد|تبریک/.test(msg), msg.trim());

  // 3 · checkout
  await p.goto(site + '/checkout/', { waitUntil: 'networkidle' });
  await p.fill('#billing_first_name', 'نرگس محمدی');
  await p.fill('#billing_phone', '09121234567');
  await p.fill('#billing_email', 'test@example.com');
  await p.fill('#billing_postcode', '4471234567');
  await p.selectOption('#billing_state', 'GIL');
  await p.waitForTimeout(400);
  const cityTag = await p.evaluate(() => document.querySelector('#billing_city').tagName);
  if (cityTag === 'SELECT') await p.selectOption('#billing_city', { label: 'لاهیجان' }); else await p.fill('#billing_city', 'لاهیجان');
  await p.fill('#billing_address_1', 'خیابان شهید بهشتی، کوچه ۵، پلاک ۱۲');
  await p.waitForResponse(r => r.url().includes('update_order_review'), { timeout: 15000 }).catch(() => {});
  await p.waitForTimeout(1500);
  const ship = await p.$$eval('input[name^="shipping_method"]', els => els.map(e => ({ v: e.value, c: e.checked })));
  check('shipping options for Lahijan include courier', ship.some(s => /peyk/.test(s.v)), JSON.stringify(ship));
  const peykChecked = ship.some(s => /peyk/.test(s.v) && s.c);
  check('courier auto-selected for Lahijan', peykChecked);
  const pay = await p.$$eval('input[name="payment_method"]', els => els.map(e => e.value));
  check('payment methods rendered', pay.length > 0, pay.join(','));
  const terms = await p.$('#terms'); if (terms) await p.check('#terms', { force: true });
  await Promise.all([
    p.waitForURL(u => /zt_order=/.test(u.toString()) || /order-received/.test(u.toString()), { timeout: 30000 }).catch(() => {}),
    p.click('#place_order'),
  ]);
  const url = p.url();
  check('order placed → redirected to tracking', /zt_order=/.test(url), url);
  await p.waitForLoadState('networkidle');
  const num = await p.textContent('.zt-ohead__num').catch(() => '');
  check('tracking shows order number', en(num).length > 0, num);
  const items = await p.$$('.zt-itable tbody tr');
  check('tracking lists the ordered item', items.length >= 1, 'rows=' + items.length);
  const status = await p.textContent('.zt-ohead .zt-ostatus').catch(() => '');
  check('tracking shows status', status.trim().length > 0, status.trim());
  const notice = await p.$('.woocommerce-error');
  if (notice) console.log('   notice:', (await notice.textContent()).trim());

  check('no JS errors', errors.length === 0, errors.join(' | '));
  await b.close();
  const failed = results.filter(r => !r[1]).length;
  console.log(failed ? failed + ' FAILED' : 'ALL PASSED (' + results.length + ')');
  process.exit(failed ? 1 : 0);
})();
