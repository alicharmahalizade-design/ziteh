/* ==========================================================================
   زیته — App layer
   اسکلت اپلیکیشنی: دراور، جستجو، باتم‌شیت، توست، سبد پایدار، ژست‌ها
   ========================================================================== */
(function () {
  'use strict';

  const $  = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.prototype.slice.call((r || document).querySelectorAll(s));
  const FA = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
  const fa = n => String(n).replace(/\d/g, d => FA[+d]);
  const en = s => String(s).replace(/[۰-۹]/g, d => FA.indexOf(d));
  const money = n => fa(Number(n).toLocaleString('en-US'));
  const body = document.body;
  const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ═══════════ 1 · سبد خرید (localStorage) ═══════════════════════════════ */
  const CART_KEY = 'ziteh.cart.count';
  const cart = {
    get() {
      try { const v = parseInt(localStorage.getItem(CART_KEY), 10); if (!isNaN(v)) return v; } catch (e) {}
      return parseInt(body.dataset.cartCount || '0', 10) || 0;
    },
    set(v) {
      v = Math.max(0, v);
      try { localStorage.setItem(CART_KEY, v); } catch (e) {}
      paintBadges(v, true);
      return v;
    },
    add(n) { return this.set(this.get() + (n || 1)); }
  };
  function paintBadges(v, bump) {
    $$('[data-cart-badge]').forEach(el => {
      el.textContent = fa(v);
      el.style.visibility = v > 0 ? '' : 'hidden';
      if (bump) { el.classList.remove('is-bumping'); void el.offsetWidth; el.classList.add('is-bumping'); }
    });
    const hdr = $('.cart-btn .count');
    if (hdr) { hdr.textContent = fa(v); if (bump) { hdr.classList.remove('is-bumping'); void hdr.offsetWidth; hdr.classList.add('is-bumping'); } }
  }
  paintBadges(cart.get(), false);

  /* ═══════════ 2 · توست ═══════════════════════════════════════════════════ */
  const toastHost = $('[data-toasts]');
  function toast(text, opts) {
    if (!toastHost) return;
    opts = opts || {};
    const t = document.createElement('div');
    t.className = 'toast';
    t.innerHTML =
      '<span class="toast__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" ' +
      'stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span><span></span>';
    $('span:nth-child(2)', t).textContent = text;
    if (opts.actionText) {
      const a = document.createElement(opts.href ? 'a' : 'button');
      a.textContent = opts.actionText;
      if (opts.href) a.href = opts.href; else a.addEventListener('click', () => { opts.onAction && opts.onAction(); close(); });
      t.appendChild(a);
    }
    toastHost.appendChild(t);
    const close = () => { t.classList.add('is-out'); setTimeout(() => t.remove(), 300); };
    setTimeout(close, opts.duration || 3200);
    return t;
  }
  window.zitehToast = toast;

  /* ═══════════ 3 · اسکریم مشترک ═══════════════════════════════════════════ */
  const scrim = $('[data-scrim]');
  let openLayers = 0;
  function lock() { openLayers++; body.classList.add('no-scroll'); if (scrim) { scrim.hidden = false; void scrim.offsetWidth; scrim.classList.add('is-open'); } }
  function unlock() {
    openLayers = Math.max(0, openLayers - 1);
    if (openLayers === 0) {
      body.classList.remove('no-scroll');
      if (scrim) { scrim.classList.remove('is-open'); setTimeout(() => { if (openLayers === 0) scrim.hidden = true; }, 300); }
    }
  }

  /* ═══════════ 4 · دراور ═══════════════════════════════════════════════════ */
  const drawer = $('[data-drawer]');
  function openDrawer() {
    if (!drawer) return;
    drawer.hidden = false; lock(); void drawer.offsetWidth;
    drawer.classList.add('is-open');
  }
  function closeDrawer() {
    if (!drawer || !drawer.classList.contains('is-open')) return;
    drawer.classList.remove('is-open'); unlock();
    setTimeout(() => { drawer.hidden = true; }, 380);
  }
  $$('[data-drawer-open]').forEach(b => b.addEventListener('click', openDrawer));
  $$('[data-drawer-close]').forEach(b => b.addEventListener('click', closeDrawer));
  $$('[data-drawer-acc]').forEach(acc => {
    $('button', acc).addEventListener('click', () => acc.classList.toggle('is-open'));
  });

  /* ═══════════ 5 · جستجوی تمام‌صفحه ═══════════════════════════════════════ */
  const CATALOG = [
    { t: 'شامپو تقویت‌کننده و ضد ریزش موی زیته', c: 'مراقبت مو', p: 385000, i: 'assets/img/p-thumb-1.jpg' },
    { t: 'سرم ضد ریزش مو زیته', c: 'مراقبت مو', p: 395000, i: 'assets/img/rel-5.jpg' },
    { t: 'ماسک مو تقویت‌کننده زیته', c: 'مراقبت مو', p: 475000, i: 'assets/img/rel-4.jpg' },
    { t: 'لوسیون تقویت مو زیته', c: 'مراقبت مو', p: 375000, i: 'assets/img/rel-3.jpg' },
    { t: 'شامپو روزانه ملایم زیته', c: 'مراقبت مو', p: 285000, i: 'assets/img/rel-2.jpg' },
    { t: 'روغن تقویت ریشه مو زیته', c: 'مراقبت مو', p: 395000, i: 'assets/img/rel-1.jpg' },
    { t: 'تونر آبرسان گیاهی آلوئه ورا و رز', c: 'مراقبت پوست', p: 785000, i: 'assets/img/c-item-2.jpg' },
    { t: 'مام صابون‌کننده و ضد عرق خاکستر کله', c: 'بهداشت فردی', p: 785000, i: 'assets/img/c-item-1.jpg' },
    { t: 'ماسک مو تغذیه‌کننده زیته با کره شی', c: 'مراقبت مو', p: 340000, i: 'assets/img/c-item-3.jpg' },
    { t: 'شامپو ضد شوره ملایم D1 پرایم', c: 'شامپو', p: 690000, i: 'assets/img/sel-1.jpg' },
    { t: 'ماسک لب آبرسان و حجم دهنده آردن اکسپرتیج', c: 'مراقبت از لب', p: 495000, i: 'assets/img/sel-2.jpg' },
    { t: 'استیک ضد آفتاب سولار شیلد SPF50', c: 'ضد آفتاب', p: 1099000, i: 'assets/img/sel-3.jpg' },
    { t: 'کرم ژل مرطوب کننده پوست خشک و حساس', c: 'کرم مرطوب کننده', p: 1548000, i: 'assets/img/sel-4.jpg' },
    { t: 'عطر خانه ارکید اواسیس ویت یو', c: 'خوشبو کننده محیط', p: 900000, i: 'assets/img/offer-1.jpg' },
    { t: 'عطر خانه پلیس گالا ویت یو', c: 'خوشبو کننده محیط', p: 900000, i: 'assets/img/offer-2.jpg' },
    { t: 'عطر خانه اربیتال سوک ویت یو', c: 'خوشبو کننده محیط', p: 900000, i: 'assets/img/offer-3.jpg' },
    { t: 'عطر خانه فارست کاتیج ویت یو', c: 'خوشبو کننده محیط', p: 900000, i: 'assets/img/offer-4.jpg' },
    { t: 'سرم ویتامین C', c: 'سرم', p: 640000, i: 'assets/img/gift-4.jpg' },
    { t: 'ژل شستشوی صورت', c: 'مراقبت پوست', p: 320000, i: 'assets/img/gift-2.jpg' },
    { t: 'کرم مرطوب‌کننده آلوئه ورا', c: 'مراقبت پوست', p: 285000, i: 'assets/img/gift-1.jpg' }
  ];
  const pane = $('[data-searchpane]');
  const sInput = $('[data-search-input]');
  function openSearch() {
    if (!pane) return;
    pane.hidden = false; lock(); void pane.offsetWidth;
    pane.classList.add('is-open');
    setTimeout(() => sInput && sInput.focus(), 120);
  }
  function closeSearch() {
    if (!pane || !pane.classList.contains('is-open')) return;
    pane.classList.remove('is-open'); unlock();
    setTimeout(() => { pane.hidden = true; }, 300);
  }
  $$('[data-search-open]').forEach(b => b.addEventListener('click', openSearch));
  $$('[data-search-close]').forEach(b => b.addEventListener('click', closeSearch));
  // desktop search fields hand over to the same overlay on small screens
  $$('.search input').forEach(inp => inp.addEventListener('focus', () => {
    if (matchMedia('(max-width:900px)').matches) { inp.blur(); openSearch(); }
  }));

  if (sInput) {
    const idle = $('[data-search-idle]'), res = $('[data-search-results]'),
          list = $('[data-search-list]'), cnt = $('[data-search-count]'),
          empty = $('[data-search-empty]'), clr = $('[data-search-clear]');
    const norm = s => en(String(s)).replace(/‌/g, '').replace(/\s+/g, ' ').trim()
      .replace(/ی/g, 'ي').replace(/ک/g, 'ك').replace(/أ|إ|آ/g, 'ا');
    const run = q => {
      q = q.trim();
      clr.hidden = !q;
      if (!q) { idle.hidden = false; res.hidden = true; return; }
      idle.hidden = true; res.hidden = false;
      const nq = norm(q);
      const hits = CATALOG.filter(p => norm(p.t).indexOf(nq) > -1 || norm(p.c).indexOf(nq) > -1);
      cnt.textContent = hits.length ? fa(hits.length) + ' نتیجه برای «' + q + '»' : '';
      empty.hidden = hits.length > 0;
      list.innerHTML = hits.slice(0, 12).map((p, k) =>
        '<a class="sresult" href="product.html" style="animation-delay:' + (k * 28) + 'ms">' +
        '<img src="' + p.i + '" alt=""><div><b>' + p.t + '</b><span>' + money(p.p) + ' تومان</span></div>' +
        '<span class="go"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
        'stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></span></a>').join('');
    };
    sInput.addEventListener('input', e => run(e.target.value));
    clr.addEventListener('click', () => { sInput.value = ''; run(''); sInput.focus(); });
    $$('.chipbtn').forEach(c => c.addEventListener('click', () => { sInput.value = c.textContent; run(c.textContent); }));
  }

  /* ═══════════ 6 · باتم‌شیت ════════════════════════════════════════════════ */
  function openSheet(el) {
    if (!el) return;
    el.hidden = false; lock(); void el.offsetWidth;
    el.classList.add('is-open');
  }
  function closeSheet(el) {
    if (!el || !el.classList.contains('is-open')) return;
    el.classList.remove('is-open'); unlock();
    setTimeout(() => { el.hidden = true; el.querySelector('.sheet__panel').style.transform = ''; }, 440);
  }
  $$('[data-sheet-close]').forEach(b => b.addEventListener('click', () => closeSheet(b.closest('[data-sheet]'))));
  // swipe the grab handle down to dismiss — reusable so sheets built at runtime work too
  function bindSheet(sh) {
    const panel = $('.sheet__panel', sh), grab = $('[data-sheet-grab]', sh);
    if (!grab) return;
    let y0 = 0, dy = 0, on = false;
    const start = e => { on = true; y0 = (e.touches ? e.touches[0] : e).clientY; dy = 0; sh.classList.add('is-dragging'); };
    const move = e => {
      if (!on) return;
      dy = Math.max(0, (e.touches ? e.touches[0] : e).clientY - y0);
      panel.style.transform = 'translateY(' + dy + 'px)';
    };
    const end = () => {
      if (!on) return;
      on = false; sh.classList.remove('is-dragging');
      if (dy > 90) { panel.style.transform = ''; closeSheet(sh); } else panel.style.transform = '';
    };
    grab.addEventListener('touchstart', start, { passive: true });
    grab.addEventListener('touchmove', move, { passive: true });
    grab.addEventListener('touchend', end);
    grab.addEventListener('mousedown', e => { start(e); document.addEventListener('mousemove', move); document.addEventListener('mouseup', function up() { end(); document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); }); });
  }
  $$('[data-sheet]').forEach(bindSheet);

  /* ═══════════ 7 · افزودن سریع به سبد ══════════════════════════════════════ */
  const qaSheet = $('#sheet-quickadd');
  let qaData = null;
  function quickAdd(data) {
    qaData = data;
    $('[data-qa-img]').src = data.img || '';
    $('[data-qa-cat]').textContent = data.cat || '';
    $('[data-qa-title]').textContent = data.title || '';
    $('[data-qa-price]').textContent = money(data.price || 0);
    const q = $('[data-qa-qty]'); $('span', q).textContent = '۱'; q.dataset.value = 1;
    openSheet(qaSheet);
  }
  if (qaSheet) {
    $('[data-qa-add]').addEventListener('click', () => {
      const n = parseInt($('[data-qa-qty]').dataset.value || '1', 10);
      cart.add(n); closeSheet(qaSheet);
      toast(fa(n) + ' عدد به سبد خرید اضافه شد', { actionText: 'مشاهده سبد', href: 'cart.html' });
    });
  }

  // wire every product card's cart button
  $$('.prod__add, .relcard__foot button').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      const card = btn.closest('.prod, .relcard');
      if (!card) return;
      const img = $('img', card);
      const title = $('.prod__title, h4', card);
      const priceEl = $('.prod__price b, .relcard__foot b', card);
      quickAdd({
        img: img ? img.getAttribute('src') : '',
        cat: (($('.prod__cat', card) || {}).textContent || '').trim(),
        title: title ? title.textContent.trim() : '',
        price: parseInt(en((priceEl ? priceEl.textContent : '0').replace(/[^\d۰-۹]/g, '')), 10) || 0
      });
    });
  });

  /* ═══════════ 8 · اکشن‌بار (افزودن به سبد از صفحه محصول) ═════════════════ */
  $$('[data-ab-add]').forEach(b => b.addEventListener('click', e => {
    e.preventDefault();
    const q = $('.buybox .qty');
    const n = q ? (parseInt(q.dataset.value || en($('span', q).textContent), 10) || 1) : 1;
    cart.add(n);
    toast(fa(n) + ' عدد به سبد خرید اضافه شد', { actionText: 'مشاهده سبد', href: 'cart.html' });
  }));
  $$('[data-ab-wish]').forEach(b => b.addEventListener('click', () => {
    b.classList.toggle('is-on');
    toast(b.classList.contains('is-on') ? 'به علاقه‌مندی‌ها اضافه شد' : 'از علاقه‌مندی‌ها حذف شد');
  }));
  // main "add to cart" button on the product page
  const buyBtn = $('.buybox .btn--primary');
  if (buyBtn) buyBtn.addEventListener('click', e => {
    e.preventDefault();
    const q = $('.buybox .qty');
    const n = q ? (parseInt(q.dataset.value || en($('span', q).textContent), 10) || 1) : 1;
    cart.add(n);
    toast(fa(n) + ' عدد به سبد خرید اضافه شد', { actionText: 'مشاهده سبد', href: 'cart.html' });
  });
  $$('.buybox__wish').forEach(w => w.addEventListener('click', e => {
    e.preventDefault(); w.classList.toggle('is-on');
    const svg = $('svg use', w);
    if (svg) svg.setAttribute('href', w.classList.contains('is-on') ? '#i-heart-fill' : '#i-heart');
    w.style.color = w.classList.contains('is-on') ? '#D9534F' : '';
    toast(w.classList.contains('is-on') ? 'به لیست علاقه‌مندی اضافه شد' : 'از لیست علاقه‌مندی حذف شد');
  }));
  $$('[data-wishlist]').forEach(a => a.addEventListener('click', e => {
    e.preventDefault(); toast('لیست علاقه‌مندی‌های شما به‌زودی اینجا نمایش داده می‌شود');
  }));

  /* ═══════════ 9 · کشیدن برای حذف (سبد خرید) ══════════════════════════════ */
  $$('[data-cart] [data-line]').forEach(row => {
    let x0 = 0, dx = 0, on = false;
    const cells = row;
    const reset = () => { cells.style.transition = 'transform .3s cubic-bezier(.22,1,.36,1)'; cells.style.transform = ''; };
    row.addEventListener('touchstart', e => {
      if (e.target.closest('.qty, button')) return;
      on = true; x0 = e.touches[0].clientX; dx = 0; cells.style.transition = 'none';
    }, { passive: true });
    row.addEventListener('touchmove', e => {
      if (!on) return;
      dx = e.touches[0].clientX - x0;
      if (dx < 0) dx = Math.max(dx, -140);
      else dx = Math.min(dx, 140);
      cells.style.transform = 'translateX(' + dx + 'px)';
    }, { passive: true });
    row.addEventListener('touchend', () => {
      if (!on) return; on = false;
      if (Math.abs(dx) > 110) {
        cells.style.transition = 'transform .28s ease, opacity .28s ease';
        cells.style.transform = 'translateX(' + (dx < 0 ? -420 : 420) + 'px)';
        cells.style.opacity = '0';
        const rm = $('[data-remove]', row);
        setTimeout(() => rm && rm.click(), 240);
      } else reset();
    });
  });

  /* ═══════════ 10 · اپ‌بار: عنوان و پنهان‌شدن هنگام اسکرول ════════════════ */
  const appbar = $('[data-appbar]');
  const abTitle = $('[data-appbar-title]');
  if (abTitle) abTitle.textContent = body.dataset.title || document.title.split('|')[0].trim();
  $$('[data-back]').forEach(b => b.addEventListener('click', () => {
    if (history.length > 1) history.back(); else location.href = 'index.html';
  }));

  const hero = $('.page-head h1, .hero');
  const header = $('.header');
  const prog = $('[data-scrollprog]');
  const toTop = $('[data-totop]');
  let lastY = 0, ticking = false;

  function onScroll() {
    const y = window.scrollY || 0;
    const h = document.documentElement.scrollHeight - window.innerHeight;
    if (prog) prog.style.width = (h > 0 ? Math.min(100, (y / h) * 100) : 0) + '%';
    if (header) header.classList.toggle('is-stuck', y > 8);
    if (appbar) {
      appbar.classList.toggle('is-stuck', y > 8);
      if (!$('.no-scroll')) appbar.classList.toggle('is-hidden', y > 220 && y > lastY + 6);
      if (y < lastY - 6) appbar.classList.remove('is-hidden');
    }
    if (abTitle && hero) {
      const r = hero.getBoundingClientRect();
      abTitle.classList.toggle('is-on', r.bottom < 66);
    }
    if (toTop) toTop.classList.toggle('is-on', y > 700);
    lastY = y;
    ticking = false;
  }
  addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', () =>
    window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }));

  /* ═══════════ 11 · تب فعال ═══════════════════════════════════════════════ */
  const tabKey = body.dataset.tab;
  if (tabKey) { const t = $('.tabbar [data-tab="' + tabKey + '"]'); if (t) t.classList.add('is-active'); }

  /* ═══════════ 12 · فوتر آکاردئونی (موبایل) ═══════════════════════════════ */
  $$('.footer__col h4').forEach(h => h.addEventListener('click', () => {
    if (!matchMedia('(max-width:900px)').matches) return;
    const col = h.parentNode;
    if (col.classList.contains('footer__col--static')) return;
    col.classList.toggle('is-open');
  }));

  /* ═══════════ 13 · تصاویر: اسکلتون تا لود شدن ════════════════════════════ */
  $$('img').forEach(img => {
    if (img.closest('.appbar, .drawer')) return;
    const box = img.parentElement;
    const mark = () => { img.classList.remove('is-loading'); img.classList.add('is-loaded'); box && box.classList.remove('sk'); };
    if (img.complete && img.naturalWidth) { img.classList.add('is-loaded'); return; }
    img.classList.add('is-loading'); box && box.classList.add('sk');
    img.addEventListener('load', mark, { once: true });
    img.addEventListener('error', mark, { once: true });
  });

  /* ═══════════ 14 · ریپل ══════════════════════════════════════════════════ */
  if (!reduced) {
    document.addEventListener('pointerdown', e => {
      const el = e.target.closest('.btn, .prod__add, .chipbtn, .opt, .gift, .quick a, .wide-btn');
      if (!el) return;
      const r = el.getBoundingClientRect();
      const d = Math.max(r.width, r.height);
      const s = document.createElement('span');
      s.className = 'ripple';
      s.style.cssText = 'width:' + d + 'px;height:' + d + 'px;left:' + (e.clientX - r.left - d / 2) +
        'px;top:' + (e.clientY - r.top - d / 2) + 'px';
      el.appendChild(s);
      setTimeout(() => s.remove(), 600);
    });
  }

  /* ═══════════ 15 · بستن با Escape / کلیک روی اسکریم ═════════════════════ */
  function closeTop() {
    const sh = $('[data-sheet].is-open');
    if (sh) return closeSheet(sh);
    if (pane && pane.classList.contains('is-open')) return closeSearch();
    if (drawer && drawer.classList.contains('is-open')) return closeDrawer();
  }
  if (scrim) scrim.addEventListener('click', closeTop);
  addEventListener('keydown', e => { if (e.key === 'Escape') closeTop(); });

  /* ═══════════ 17 · سوایپ گالری محصول ═════════════════════════════════════ */
  const gal = document.querySelector('[data-gallery] .gallery__main');
  if (gal) {
    let gx = 0, gy = 0, moved = false;
    gal.addEventListener('touchstart', e => {
      gx = e.touches[0].clientX; gy = e.touches[0].clientY; moved = false;
    }, { passive: true });
    gal.addEventListener('touchmove', e => {
      if (Math.abs(e.touches[0].clientX - gx) > 12) moved = true;
    }, { passive: true });
    gal.addEventListener('touchend', e => {
      if (!moved) return;
      const dx = e.changedTouches[0].clientX - gx;
      const dy = e.changedTouches[0].clientY - gy;
      if (Math.abs(dx) < 45 || Math.abs(dx) < Math.abs(dy)) return;
      // RTL: swipe start(right→left) = next
      const dir = dx < 0 ? 'next' : 'prev';
      const btn = document.querySelector('[data-gallery-dir="' + dir + '"]');
      if (btn) btn.click();
      const img = document.querySelector('[data-gallery-main]');
      if (img) { img.style.animation = 'none'; void img.offsetWidth; img.style.animation = 'fadeimg .35s var(--ease-out)'; }
    });
  }

  /* ═══════════ 18 · همگام‌سازی نوار پایین سبد ═════════════════════════════ */
  const cartRoot = document.querySelector('[data-cart]');
  const abSum = document.querySelector('.actionbar [data-sum]');
  if (cartRoot && abSum) {
    const sync = () => {
      const first = document.querySelector('.sum [data-sum]');
      if (first) abSum.textContent = first.textContent;
      const rows = document.querySelectorAll('[data-line]').length;
      const bar = document.querySelector('.actionbar .btn');
      if (bar) { bar.style.opacity = rows ? '' : '.5'; bar.style.pointerEvents = rows ? '' : 'none'; }
    };
    cartRoot.addEventListener('qtychange', () => setTimeout(sync, 0));
    cartRoot.addEventListener('click', e => { if (e.target.closest('[data-remove]')) setTimeout(sync, 260); });
    sync();
  }

  /* ═══════════ 19 · انتخاب گزینه‌ها → بازخورد ═════════════════════════════ */
  document.querySelectorAll('[data-radio-group]').forEach(g => {
    const label = g.previousElementSibling && g.previousElementSibling.textContent
      ? g.previousElementSibling.textContent.trim() : '';
    g.addEventListener('click', e => {
      const item = e.target.closest('[data-radio]');
      if (!item) return;
      const name = (item.querySelector('b') || {}).textContent;
      if (name) toast((label ? label + ': ' : '') + name.trim() + ' انتخاب شد', { duration: 1800 });
    });
  });

  /* ═══════════ 16 · شمارنده‌ی نرم برای مبالغ ══════════════════════════════ */
  window.zitehCountTo = function (el, to) {
    if (reduced) { el.textContent = money(to); return; }
    const from = parseInt(en(el.textContent).replace(/\D/g, ''), 10) || 0;
    if (from === to) return;
    const t0 = performance.now(), dur = 420;
    (function step(t) {
      const k = Math.min(1, (t - t0) / dur);
      const e = 1 - Math.pow(1 - k, 3);
      el.textContent = money(Math.round(from + (to - from) * e));
      if (k < 1) requestAnimationFrame(step);
    })(t0);
  };

  /* ═══════════ 20 · سبد خالی + تخفیف پلکانی زنده ══════════════════════════ */
  (function () {
    const table = document.querySelector('[data-cart] .ctable');
    if (!table) return;

    const TIERS = [
      { at: 2500000, label: 'ارسال رایگان' },
      { at: 5000000, label: '۵٪ تخفیف' },
      { at: 7000000, label: '۱۰٪ تخفیف' }
    ];
    const bar  = document.querySelector('.tier-progress .bar i');
    const note = document.querySelector('.tier-progress p');
    const dots = document.querySelectorAll('.tier');          // ۱۰٪ , ۵٪ , ارسال رایگان (RTL order)
    const fill = document.querySelector('.tiers__fill');

    function total() {
      let sum = 0;
      document.querySelectorAll('[data-line]').forEach(l => {
        const q = parseInt((l.querySelector('.qty') || {}).dataset?.value || '1', 10) || 1;
        sum += (+l.dataset.price) * q;
      });
      return sum;
    }

    function paintTiers() {
      const sum = total();
      const next = TIERS.find(t => sum < t.at);
      if (note) {
        note.textContent = next
          ? 'شما تا ' + money(next.at - sum) + ' تومان دیگر تا دریافت ' + next.label + ' فاصله دارید'
          : 'تبریک! بیشترین تخفیف پلکانی برای سفارش شما فعال شد 🎉';
      }
      const cap = TIERS[TIERS.length - 1].at;
      if (bar) bar.style.width = Math.min(100, (sum / cap) * 100) + '%';
      // dots are laid out ۱۰٪ , ۵٪ , ارسال رایگان → reverse to compare with thresholds
      [...dots].reverse().forEach((d, i) => d.classList.toggle('is-done', sum >= TIERS[i].at));
      if (fill) {
        const reached = TIERS.filter(t => sum >= t.at).length;
        fill.style.width = 'calc(' + (reached / TIERS.length) * 100 + '% - 60px)';
      }
    }

    function paintEmpty() {
      const rows = document.querySelectorAll('[data-line]').length;
      const card = table.closest('.pcard');
      let empty = card.querySelector('.cart-empty');
      if (rows === 0 && !empty) {
        empty = document.createElement('div');
        empty.className = 'cart-empty';
        empty.innerHTML =
          '<span class="cart-empty__ic"><svg viewBox="0 0 24 24" width="34" height="34"><use href="#i-basket"/></svg></span>' +
          '<b>سبد خرید شما خالی است</b>' +
          '<p>محصولات مورد علاقه‌تان را انتخاب کنید تا اینجا نمایش داده شوند.</p>' +
          '<a class="btn btn--primary" href="product.html">شروع خرید</a>';
        table.after(empty);
        table.hidden = true;
        const foot = card.querySelector('.ctable-foot');
        if (foot) foot.hidden = true;
      } else if (rows > 0 && empty) {
        empty.remove(); table.hidden = false;
        const foot = card.querySelector('.ctable-foot');
        if (foot) foot.hidden = false;
      }
    }

    const refresh = () => { paintTiers(); paintEmpty(); };
    document.querySelector('[data-cart]').addEventListener('qtychange', () => setTimeout(refresh, 0));
    document.querySelector('[data-cart]').addEventListener('click', e => {
      if (e.target.closest('[data-remove]')) setTimeout(refresh, 280);
    });
    const clearBtn = [...document.querySelectorAll('.ctable-foot .btn')]
      .find(b => b.textContent.indexOf('پاک‌سازی') > -1);
    if (clearBtn) clearBtn.addEventListener('click', () => {
      document.querySelectorAll('[data-line] [data-remove]').forEach(b => b.click());
      setTimeout(refresh, 320);
      toast('سبد خرید خالی شد');
    });
    const syncBtn = [...document.querySelectorAll('.ctable-foot .btn')]
      .find(b => b.textContent.indexOf('به‌روزرسانی') > -1);
    if (syncBtn) syncBtn.addEventListener('click', () => { refresh(); toast('سبد خرید به‌روزرسانی شد'); });
    refresh();
  })();


  /* ═══════════ 21 · اسلایدر موبایل صفحه‌ی اصلی ════════════════════════════ */
  document.querySelectorAll('[data-mslider]').forEach(root => {
    const track = root.querySelector('[data-mtrack]');
    const dots  = [...root.querySelectorAll('[data-mdots] button')];
    if (!track || !dots.length) return;

    const slides = () => [...track.children];
    let idx = 0, timer = null, paused = false;

    const paint = i => {
      idx = i;
      dots.forEach((d, k) => d.classList.toggle('is-active', k === i));
    };

    // direction-agnostic: scroll by the measured delta, never by scrollLeft
    const goTo = (i, smooth) => {
      const s = slides()[i];
      if (!s) return;
      const sr = s.getBoundingClientRect(), tr = track.getBoundingClientRect();
      const delta = (sr.left + sr.right) / 2 - (tr.left + tr.right) / 2;   // centre-based
      track.scrollBy({ left: delta, behavior: (smooth === false || reduced) ? 'instant' : 'smooth' });
      paint(i);
    };

    // keep the dots in sync with a manual swipe (measured, not scrollLeft)
    const nearest = () => {
      const tc = track.getBoundingClientRect();
      const mid = (tc.left + tc.right) / 2;
      let best = 0, bd = Infinity;
      slides().forEach((s, k) => {
        const r = s.getBoundingClientRect();
        const d = Math.abs((r.left + r.right) / 2 - mid);
        if (d < bd) { bd = d; best = k; }
      });
      return best;
    };
    let tick = null;
    track.addEventListener('scroll', () => {
      clearTimeout(tick);
      tick = setTimeout(() => { const n = nearest(); if (n !== idx) paint(n); }, 70);
    }, { passive: true });
    paint(nearest());

    dots.forEach((d, k) => d.addEventListener('click', () => { stop(); goTo(k); }));

    // autoplay ------------------------------------------------------------
    const step = () => { if (!paused) goTo((idx + 1) % slides().length); };
    const start = () => { if (!reduced && !timer) timer = setInterval(step, 5200); };
    const stop  = () => { clearInterval(timer); timer = null; };

    ['touchstart', 'pointerdown'].forEach(ev =>
      track.addEventListener(ev, () => { paused = true; }, { passive: true }));
    ['touchend', 'pointerup', 'pointercancel'].forEach(ev =>
      track.addEventListener(ev, () => { setTimeout(() => { paused = false; }, 3500); }, { passive: true }));

    document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(e => e[0].isIntersecting ? start() : stop(), { threshold: .35 }).observe(root);
    } else start();
  });


  /* ═══════════ 22 · صفحه‌های موبایل: بازچینش بومی ═══════════════════════════ */
  const isMobile = () => matchMedia('(max-width:900px)').matches;
  const chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
               'stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';

  if (isMobile()) {

    /* ---------- PRODUCT ---------------------------------------------------- */
    (function () {
      const main = $('.gallery__main');
      const thumbs = $$('[data-gallery-thumb]');

      // 1 · counter pill over the gallery
      if (main && thumbs.length) {
        const c = document.createElement('span');
        c.className = 'gcount';
        main.appendChild(c);
        const upd = () => {
          let i = thumbs.findIndex(t => t.classList.contains('is-active'));
          if (i < 0) i = 0;
          c.textContent = fa(i + 1) + ' / ' + fa(thumbs.length);
        };
        upd();
        thumbs.forEach(t => t.addEventListener('click', () => setTimeout(upd, 0)));
        $$('[data-gallery-dir]').forEach(b => b.addEventListener('click', () => setTimeout(upd, 0)));
      }

      // 2 · tabs → accordion
      const tabsEl = $('.tabs');
      if (tabsEl) {
        const wrap = document.createElement('div');
        wrap.className = 'macc';
        $$('[data-tab]', tabsEl).forEach((btn, k) => {
          const panel = $('[data-panel="' + btn.dataset.tab + '"]');
          if (!panel) return;
          panel.hidden = false;
          const item = document.createElement('section');
          item.className = 'macc__i' + (k === 0 ? ' is-open' : '');
          const head = document.createElement('button');
          head.className = 'macc__h';
          head.innerHTML = '<span>' + btn.textContent.trim() + '</span>' + chev;
          const body = document.createElement('div');
          body.className = 'macc__b';
          const inner = document.createElement('div');
          inner.appendChild(panel);
          body.appendChild(inner);
          item.append(head, body);
          head.addEventListener('click', () => item.classList.toggle('is-open'));
          wrap.appendChild(item);
        });
        if (wrap.children.length) { tabsEl.after(wrap); tabsEl.remove(); }
      }
    })();

    /* ---------- CHECKOUT ---------------------------------------------------- */
    (function () {
      const osum = $('.osum');
      if (!osum) return;
      const items = $$('.oitem', osum);
      if (!items.length) return;

      const box = document.createElement('div');
      box.className = 'osum__items';
      const inner = document.createElement('div');
      items.forEach(i => inner.appendChild(i));
      box.appendChild(inner);
      const coupon = $('.coupon', osum);
      (coupon || osum).before ? (coupon ? coupon.before(box) : osum.appendChild(box)) : osum.appendChild(box);

      const btn = document.createElement('button');
      btn.className = 'osum__toggle';
      btn.innerHTML = '<span>' + fa(items.length) + ' کالا</span>' + chev;
      $('.osum__title', osum).appendChild(btn);
      btn.addEventListener('click', () => osum.classList.toggle('is-open'));
    })();

    /* ---------- CART: breakdown sheet from the sticky bar --------------------- */
    (function () {
      if (body.dataset.page !== 'cart') return;
      const price = $('.actionbar__price');
      const sumCard = $('.sum');
      if (!price || !sumCard) return;

      price.classList.add('is-tappable');
      price.setAttribute('role', 'button');
      price.insertAdjacentHTML('beforeend',
        '<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
        'stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>');

      const sheet = document.createElement('div');
      sheet.className = 'sheet';
      sheet.id = 'sheet-total';
      sheet.hidden = true;
      sheet.setAttribute('data-sheet', '');
      sheet.setAttribute('aria-label', 'جزئیات پرداخت');
      sheet.innerHTML =
        '<div class="sheet__panel"><div class="sheet__grab" data-sheet-grab></div>' +
        '<div class="sheet__body"><h3 class="sheetttl">جزئیات پرداخت</h3><div class="brk"></div></div>' +
        '<div class="sheet__foot"><button class="btn btn--ghost" data-sheet-close>بستن</button>' +
        '<a class="btn btn--primary" href="checkout.html">ثبت سفارش</a></div></div>';
      body.appendChild(sheet);
      bindSheet(sheet);
      $('[data-sheet-close]', sheet).addEventListener('click', () => closeSheet(sheet));

      const brk = $('.brk', sheet);
      const fill = () => {
        const rows = $$('.sum__row', sumCard).map(r => {
          const k = r.children[0].textContent.trim();
          const v = r.children[1].textContent.trim();
          return '<div><span>' + k + '</span><span>' + v + '</span></div>';
        });
        const tot = $('.sum__total', sumCard);
        if (tot) rows.push('<div class="grand"><span>' + tot.children[0].textContent.trim() +
                           '</span><span>' + tot.children[1].textContent.trim() + '</span></div>');
        brk.innerHTML = rows.join('');
      };
      price.addEventListener('click', () => { fill(); openSheet(sheet); });
    })();

    /* ---------- TRACKING: copy the order number ------------------------------- */
    (function () {
      const num = $('.ohead__num');
      if (!num) return;
      const code = num.textContent.trim();
      const b = document.createElement('button');
      b.className = 'copybtn';
      b.setAttribute('aria-label', 'کپی شماره سفارش');
      b.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" ' +
        'stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/>' +
        '<path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>';
      num.appendChild(b);
      b.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(code); } catch (e) {}
        b.classList.add('is-done');
        b.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" ' +
          'stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
        toast('شماره سفارش کپی شد');
        setTimeout(() => b.classList.remove('is-done'), 2200);
      });
    })();
  }


  /* ═══════════ 23 · فلش‌های اسلایدر (دسکتاپ) ══════════════════════════════ */
  $$('[data-mslider]').forEach(root => {
    const track = $('[data-mtrack]', root);
    if (!track) return;
    $$('[data-mdir]', root).forEach(btn => btn.addEventListener('click', () => {
      const kids = [...track.children];
      const tc = track.getBoundingClientRect(), mid = (tc.left + tc.right) / 2;
      let cur = 0, bd = Infinity;
      kids.forEach((s, k) => {
        const r = s.getBoundingClientRect();
        const d = Math.abs((r.left + r.right) / 2 - mid);
        if (d < bd) { bd = d; cur = k; }
      });
      const next = (cur + (btn.dataset.mdir === 'next' ? 1 : -1) + kids.length) % kids.length;
      const r = kids[next].getBoundingClientRect();
      track.scrollBy({ left: (r.left + r.right) / 2 - mid, behavior: reduced ? 'instant' : 'smooth' });
      const dots = $$('[data-mdots] button', root);
      dots.forEach((d, k) => d.classList.toggle('is-active', k === next));
    }));
  });

  /* ═══════════ 24 · روتین صبح / شب ════════════════════════════════════════ */
  const routineSeg = $('[data-routine]');
  if (routineSeg) {
    const btns = $$('button', routineSeg);
    btns.forEach(b => b.addEventListener('click', () => {
      const mode = b.dataset.value;
      btns.forEach(x => x.classList.toggle('is-active', x === b));
      $$('[data-steps]').forEach(g => { g.hidden = g.dataset.steps !== mode; });
    }));
  }

  /* ═══════════ 25 · پله‌های تخفیف سبد خرید ════════════════════════════════ */
  const tiersBox = $('[data-tiers]');
  if (tiersBox) {
    const tiers = $$('[data-tier]', tiersBox)
      .map(el => ({ el: el, min: +el.dataset.tier }))
      .sort((a, b) => a.min - b.min);                   // صعودی
    const fill = $('[data-tier-fill]', tiersBox);
    const msg  = $('[data-tier-msg]');
    const bar  = $('[data-tier-bar]');
    const disc = $('[data-sum-discount]');
    const ship = $('[data-sum-ship]');

    // درصد تخفیف هر پله (ارسال رایگان تخفیف ندارد)
    const RATE = { 2500000: 3, 4000000: 5 };
    const FREE_SHIP = 5000000;

    window.zitehTiers = function (sum) {
      tiers.forEach(t => t.el.classList.toggle('is-done', sum >= t.min));

      // پله‌ی بعدی
      const next = tiers.find(t => sum < t.min);
      const top  = tiers[tiers.length - 1].min;
      const pct  = Math.max(0, Math.min(100, (sum / top) * 100));
      if (bar) bar.style.width = pct + '%';
      if (fill) fill.style.width = 'calc(' + pct + '% * .62 + 8%)';

      if (msg) {
        if (!next) msg.textContent = 'تبریک! بیشترین تخفیف و ارسال رایگان برای شما فعال شد.';
        else {
          const label = RATE[next.min] ? fa(RATE[next.min]) + '٪ تخفیف' : 'ارسال رایگان';
          msg.textContent = 'شما تا ' + money(next.min - sum) + ' تومان دیگر تا دریافت ' + label + ' فاصله دارید';
        }
      }

      // بیشترین درصد تخفیفی که فعال شده
      let rate = 0;
      Object.keys(RATE).forEach(k => { if (sum >= +k) rate = Math.max(rate, RATE[k]); });
      if (disc) {
        if (rate) { disc.textContent = '− ' + money(Math.round(sum * rate / 100)) + ' تومان'; disc.classList.remove('dash'); }
        else { disc.textContent = '- - - -'; disc.classList.add('dash'); }
      }
      if (ship) {
        if (sum >= FREE_SHIP) { ship.textContent = 'رایگان'; ship.classList.remove('dash'); }
        else { ship.textContent = 'در مرحله بعد'; ship.classList.add('dash'); }
      }
      return rate;
    };
  }

  /* ═══════════ 26 · شهر بر اساس استان + پیک لاهیجان ══════════════════════ */
  const provSel = $('[data-province]'), citySel = $('[data-city]');
  if (provSel && citySel) {
    let CITIES = {};
    const raw = $('#city-data');
    if (raw) { try { CITIES = JSON.parse(raw.textContent); } catch (e) {} }

    const setCities = list => {
      citySel.innerHTML = '<option value="" disabled selected>انتخاب شهر</option>' +
        (list || []).map(c => '<option>' + c + '</option>').join('');
      citySel.disabled = !(list && list.length);
    };
    citySel.disabled = true;

    provSel.addEventListener('change', () => {
      setCities(CITIES[provSel.value] || ['مرکز استان']);
    });

    // انتخاب لاهیجان → روش ارسال با پیک
    citySel.addEventListener('change', () => {
      const isLahijan = citySel.value.trim() === 'لاهیجان';
      const peyk = $('[data-ship="peyk"]');
      if (!peyk) return;
      if (isLahijan) {
        $$('[data-ship-group] [data-radio]').forEach(o => o.classList.remove('is-selected'));
        peyk.classList.add('is-selected');
        const r = $('input', peyk); if (r) r.checked = true;
        toast('ارسال با پیک در لاهیجان برای شما انتخاب شد');
      }
    });
  }

  /* ═══════════ 27 · شیت مشاوره (واتساپ / تلگرام / بله) ════════════════════ */
  $$('[data-sheet-open]').forEach(b => b.addEventListener('click', () => {
    const el = $(b.dataset.sheetOpen);
    if (el) openSheet(el);
  }));

})();
