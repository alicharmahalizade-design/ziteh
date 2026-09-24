/*
 * Feature tests: tiers + free shipping + gift sample in the order, guest order
 * lookup, registration, account sections, wishlist, mobile login, forms.
 *   node tools/e2e-features.js <siteUrl> <productUrl>
 */
const { chromium } = require('playwright');
const [site, productUrl] = process.argv.slice(2);
const results = [];
const check = (name, ok, extra) => { results.push([name, ok]); console.log((ok ? 'PASS ' : 'FAIL ') + name + (extra ? '  — ' + extra : '')); };
const en = s => String(s || '').replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d));
const digits = s => en(s).replace(/[^\d]/g, '');
const stamp = Date.now().toString().slice(-6);
const phone = '0912' + stamp.padStart(7, '5');

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const errors = [];
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  p.on('pageerror', e => errors.push(e.message));
  const settle = () => p.waitForLoadState('networkidle');

  // ---- register a customer
  await p.goto(site + '/my-account/', { waitUntil: 'networkidle' });
  check('guest sees login + register forms', !!(await p.$('form.woocommerce-form-login')) && !!(await p.$('form.woocommerce-form-register')));
  check('guest does not see account menu', !(await p.$('.zt-sidemenu')));
  const email = 'u' + stamp + '@example.com';
  await p.fill('form.woocommerce-form-register #email', email);
  const pw = await p.$('form.woocommerce-form-register #password');
  if (pw) await pw.fill('Zt-pass-' + stamp + '!');
  await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle' }), p.click('form.woocommerce-form-register button[name=register]')]);
  check('registration logs in → dashboard with menu + profile', !!(await p.$('.zt-sidemenu')) && !!(await p.$('.zt-profile')), p.url());

  // ---- big cart: 13 × 385,000 = 5,005,000 → 5% + free shipping; choose sample
  await p.goto(productUrl, { waitUntil: 'networkidle' });
  await p.fill('.zt-buybox [data-zt-qty] input', '13').catch(() => {});
  const q = await p.$eval('.zt-buybox [data-zt-qty]', el => el.getAttribute('data-value'));
  if (q !== '13') { for (let i = +q; i < 13; i++) await p.click('.zt-buybox [data-zt-qty] [data-zt-step="1"]'); }
  await p.click('.zt-buybox__wish');
  await p.waitForTimeout(800);
  await p.click('[data-zt-buybox-add]');
  await p.waitForFunction(() => /۱۳/.test((document.querySelector('[data-zt-cart-badge]') || {}).textContent || ''), null, { timeout: 15000 }).catch(() => {});
  await p.goto(site + '/cart/', { waitUntil: 'networkidle' });
  const disc = await p.textContent('[data-zt-sum-discount]');
  check('5% tier discount applied (250,250)', digits(disc) === '250250', disc.trim());
  const shipTxt = await p.textContent('[data-zt-sum-ship]');
  check('free-shipping tier reached', /رایگان/.test(shipTxt), shipTxt.trim());
  const done = await p.$$eval('[data-zt-tier].zt-is-done', els => els.length);
  check('all three tiers marked done', done === 3, 'done=' + done);
  check('gift samples unlocked', !(await p.$('[data-zt-samples].zt-is-locked')));
  await p.click('[data-zt-samples] .zt-gift[data-index="1"]');
  await p.waitForResponse(r => r.url().includes('admin-ajax') && r.request().postData() && r.request().postData().includes('zt_sample'), { timeout: 10000 }).catch(() => {});
  await p.waitForTimeout(500);

  // ---- checkout
  await p.goto(site + '/checkout/', { waitUntil: 'networkidle' });
  await p.fill('#billing_first_name', 'نرگس محمدی');
  await p.fill('#billing_phone', phone);
  await p.fill('#billing_postcode', '1983756311');
  await p.selectOption('#billing_state', 'THR');
  await p.waitForTimeout(300);
  if ((await p.evaluate(() => document.querySelector('#billing_city').tagName)) === 'SELECT') await p.selectOption('#billing_city', { label: 'تهران' }); else await p.fill('#billing_city', 'تهران');
  await p.fill('#billing_address_1', 'خیابان پاسداران، پلاک ۱۲');
  await p.waitForResponse(r => r.url().includes('update_order_review'), { timeout: 15000 }).catch(() => {});
  await p.waitForTimeout(1500);
  const costs = await p.$$eval('.zt-co-ship .zt-opt__foot span:first-child', els => els.map(e => e.textContent.trim()));
  check('shipping shows free with the tier', costs.some(c => /رایگان/.test(c)), costs.join(' | '));
  const osum = await p.textContent('.zt-osum-body').catch(() => '');
  check('order summary contains the gift sample line', /سمپل|هدیه/.test(osum));
  const terms = await p.$('#terms'); if (terms) await p.check('#terms', { force: true });
  await Promise.all([p.waitForURL(u => /zt_order=/.test(u.toString()), { timeout: 30000 }).catch(() => {}), p.click('#place_order')]);
  const orderUrl = p.url();
  check('logged-in order placed', /zt_order=/.test(orderUrl), orderUrl);
  await settle();
  const orderId = new URL(orderUrl).searchParams.get('zt_order');
  const rowsTxt = await p.$$eval('.zt-itable tbody tr', els => els.map(e => e.textContent.replace(/\s+/g, ' ').trim()));
  check('order contains product + free sample line', rowsTxt.length === 2 && rowsTxt.some(t => /سمپل|هدیه/.test(t)), rowsTxt.join(' || '));
  const tot = await p.$$eval('.zt-itotals div', els => els.map(e => e.textContent.replace(/\s+/g, ' ').trim()));
  check('order totals: 5,005,000 − 250,250 = 4,754,750', tot.some(t => digits(t) === '4754750'), tot.join(' | '));

  // ---- account sections
  await p.goto(site + '/my-account/', { waitUntil: 'networkidle' });
  const ordersRows = await p.$$('.zt-orders tr');
  check('dashboard lists the order', ordersRows.length >= 1, 'rows=' + ordersRows.length);
  const favs = await p.$$('.zt-favs .zt-fav');
  check('dashboard wishlist shows the product', favs.length >= 1, 'favs=' + favs.length);
  const addr = await p.textContent('.zt-addr__body').catch(() => '');
  check('dashboard shows the saved address', /پاسداران/.test(addr), addr.replace(/\s+/g, ' ').trim());
  for (const [ep, sel] of [['orders', '.zt-wc table, .woocommerce-orders-table'], ['edit-address', '.zt-wc .woocommerce-Address, .zt-wc address'], ['edit-account', '.zt-wc form'], ['zt-wishlist', '.zt-favs--page .zt-fav'], ['zt-reviews', '.zt-dcard'], ['zt-coupons', '.zt-dcard']]) {
    await p.goto(site + '/my-account/' + ep + '/', { waitUntil: 'networkidle' });
    const ok = !!(await p.$(sel));
    const active = await p.$eval('.zt-sidemenu a.zt-is-active', a => a.textContent.trim()).catch(() => '');
    check('account section «' + ep + '» renders', ok && !(await p.$('.zt-profile')) , 'active menu: ' + active);
  }
  // ---- logout, then login with the mobile number
  await p.goto(site + '/my-account/', { waitUntil: 'networkidle' });
  const logout = await p.$('.zt-sidemenu a.zt-danger');
  await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle' }), logout.click()]);
  if (await p.$('a:has-text("Confirm and log out")')) await Promise.all([p.waitForNavigation(), p.click('a:has-text("Confirm and log out")')]);
  check('logged out', !!(await p.$('form.woocommerce-form-login')));
  await p.fill('form.woocommerce-form-login #username', phone.replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]));
  await p.fill('form.woocommerce-form-login #password', 'Zt-pass-' + stamp + '!');
  await Promise.all([p.waitForNavigation({ waitUntil: 'networkidle' }), p.click('form.woocommerce-form-login button[name=login]')]);
  check('login with Persian-digit mobile number', !!(await p.$('.zt-profile')), p.url());

  // ---- guest order lookup
  const g = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  await g.goto(site + '/tracking/', { waitUntil: 'networkidle' });
  check('guest tracking shows lookup form', !!(await g.$('input[name=zt_track]')));
  await g.fill('input[name=zt_track]', orderId);
  await g.fill('input[name=zt_track_id]', '0098' + phone.slice(1));
  await Promise.all([g.waitForNavigation({ waitUntil: 'networkidle' }), g.click('.zt-lookup button[type=submit]')]);
  const gnum = await g.textContent('.zt-ohead__num').catch(() => '');
  check('lookup by order number + phone finds the order', digits(gnum) === orderId, gnum);
  await g.goto(site + '/tracking/', { waitUntil: 'networkidle' });
  await g.fill('input[name=zt_track]', orderId);
  await g.fill('input[name=zt_track_id]', '09999999999');
  await Promise.all([g.waitForNavigation({ waitUntil: 'networkidle' }), g.click('.zt-lookup button[type=submit]')]);
  check('lookup with wrong phone is refused', !(await g.$('.zt-ohead')) && !!(await g.$('.zt-notice--error')));

  // ---- newsletter + contact
  await g.goto(site + '/contact/', { waitUntil: 'networkidle' });
  await g.fill('[data-zt-contact] [name=name]', 'تست');
  await g.fill('[data-zt-contact] [name=phone]', '09120000000');
  await g.fill('[data-zt-contact] [name=message]', 'سلام، این یک پیام آزمایشی است.');
  const [cr] = await Promise.all([g.waitForResponse(r => r.url().includes('admin-ajax')), g.click('[data-zt-contact] button[type=submit]')]);
  const cj = await cr.json().catch(() => ({}));
  check('contact form submits', cj.success === true, JSON.stringify(cj).slice(0, 120));
  const nl = await g.$('[data-zt-newsletter]');
  if (nl) {
    await g.fill('[data-zt-newsletter] [name=email]', 'nl' + stamp + '@example.com');
    const [nr] = await Promise.all([g.waitForResponse(r => r.url().includes('admin-ajax')), g.click('[data-zt-newsletter] button')]);
    const nj = await nr.json().catch(() => ({}));
    check('newsletter subscribes', nj.success === true, JSON.stringify(nj).slice(0, 120));
  }

  check('no JS errors', errors.length === 0, errors.slice(0, 3).join(' | '));
  await b.close();
  const failed = results.filter(r => !r[1]).length;
  console.log(failed ? failed + ' FAILED' : 'ALL PASSED (' + results.length + ')');
  process.exit(failed ? 1 : 0);
})();
