/*
 * Admin screen test: every tab renders, a repeater value (first tier amount)
 * is saved and used by the cart, reset restores the default.
 *   node tools/e2e-admin.js <siteUrl> <user> <pass>
 */
const { chromium } = require('playwright');
const [site, user, pass] = process.argv.slice(2);
const results = [];
const check = (name, ok, extra) => { results.push([name, ok]); console.log((ok ? 'PASS ' : 'FAIL ') + name + (extra ? '  — ' + extra : '')); };

(async () => {
  const b = await chromium.launch({ executablePath: '/opt/pw-browsers/chromium' });
  const p = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  const errors = [];
  p.on('pageerror', e => errors.push(e.message));
  await p.goto(site + '/wp-login.php', { waitUntil: 'networkidle' });
  await p.fill('#user_login', user);
  await p.fill('#user_pass', pass);
  await Promise.all([p.waitForNavigation(), p.click('#wp-submit')]);
  const tabs = ['general', 'pages', 'shell', 'cart', 'checkout', 'tracking', 'meta', 'newsletter', 'tools', 'subscribers', 'help'];
  for (const t of tabs) {
    const r = await p.goto(site + '/wp-admin/admin.php?page=ziteh-core&tab=' + t, { waitUntil: 'domcontentloaded' });
    const html = await p.content();
    const ok = r.status() === 200 && /zt-admin__body/.test(html) && !/Fatal error|Warning:|Notice:/.test(html);
    const fields = await p.$$eval('.zt-field', els => els.length);
    check('tab «' + t + '» renders', ok, 'fields=' + fields);
  }
  // change first tier (lowest: 2,500,000 → 3,000,000)
  await p.goto(site + '/wp-admin/admin.php?page=ziteh-core&tab=cart', { waitUntil: 'domcontentloaded' });
  const amountInputs = await p.$$('input[name^="zt[tiers]"][name$="[amount]"]');
  check('tier repeater has 3 rows', amountInputs.length === 3, 'rows=' + amountInputs.length);
  let target = null;
  for (const i of amountInputs) if ((await i.inputValue()) === '2500000') target = i;
  if (target) {
    await target.evaluate(el => el.closest('[data-zt-rep-row]').classList.add('is-open'));
    await target.fill('3000000');
  }
  check('found the 2,500,000 tier', !!target);
  // add a new repeater row through the UI, then delete it again
  const before = (await p.$$('[data-zt-rep-rows] > [data-zt-rep-row]')).length;
  await p.click('.zt-field--repeater [data-zt-rep-add] >> nth=0');
  const after = (await p.$$('[data-zt-rep-rows] > [data-zt-rep-row]')).length;
  check('repeater «add row» works', after === before + 1);
  p.once('dialog', d => d.accept());
  await p.click('.zt-field--repeater >> nth=0 >> [data-zt-rep-rows] > [data-zt-rep-row]:last-child [data-zt-rep-del]');
  check('repeater «delete row» works', (await p.$$('[data-zt-rep-rows] > [data-zt-rep-row]')).length === before);
  await Promise.all([p.waitForNavigation(), p.click('.zt-admin__save button[type=submit]')]);
  check('settings saved notice', /تنظیمات ذخیره شد/.test(await p.content()));
  const vals = await p.$$eval('input[name^="zt[tiers]"][name$="[amount]"]', els => els.map(e => e.value));
  check('tier amount persisted', vals.includes('3000000'), vals.join(','));
  // cart widget reflects it
  const c = await p.goto(site + '/cart/?zt_demo=1', { waitUntil: 'networkidle' });
  const tierAmounts = await p.$$eval('[data-zt-tier]', els => els.map(e => e.getAttribute('data-zt-tier')));
  check('cart tiers use the new amount', tierAmounts.includes('3000000'), tierAmounts.join(','));
  // reset tab
  await p.goto(site + '/wp-admin/admin.php?page=ziteh-core&tab=cart', { waitUntil: 'domcontentloaded' });
  p.once('dialog', d => d.accept());
  await Promise.all([p.waitForNavigation(), p.click('.zt-reset')]);
  const vals2 = await p.$$eval('input[name^="zt[tiers]"][name$="[amount]"]', els => els.map(e => e.value));
  check('reset restores defaults', vals2.includes('2500000') && !vals2.includes('3000000'), vals2.join(','));
  // export
  const [dl] = await Promise.all([p.waitForEvent('download'), p.goto(site + '/wp-admin/admin.php?page=ziteh-core&tab=tools').then(() => p.click('a[href*="action=zt_export"]'))]);
  const path = await dl.path();
  const json = JSON.parse(require('fs').readFileSync(path, 'utf8'));
  check('settings export downloads JSON', !!json.settings, Object.keys(json.settings || {}).join(','));
  check('no JS errors in admin', errors.length === 0, errors.slice(0, 2).join(' | '));
  await b.close();
  const failed = results.filter(r => !r[1]).length;
  console.log(failed ? failed + ' FAILED' : 'ALL PASSED (' + results.length + ')');
  process.exit(failed ? 1 : 0);
})();
