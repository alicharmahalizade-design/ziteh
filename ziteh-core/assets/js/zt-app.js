/* ==========================================================================
   Ziteh Core — front-end app layer
   Port of the design's main.js + app.js, wired to WooCommerce.
   Every widget-level behaviour is initialised per scope (idempotent), so it
   also works inside the Elementor editor when widgets re-render.
   ========================================================================== */
(function ($) {
  'use strict';

  var D = window.ZT || {};
  var I18N = D.i18n || {};
  var $1 = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
  var fa = function (n) { return D.fa === false ? String(n) : String(n).replace(/\d/g, function (d) { return FA[+d]; }); };
  var en = function (s) { return String(s).replace(/[۰-۹]/g, function (d) { return FA.indexOf(d); }).replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); }); };
  var money = function (n) { return fa(Math.round(Number(n) || 0).toLocaleString('en-US')); };
  var tpl = function (s, o) { return String(s || '').replace(/\{(\w+)\}/g, function (m, k) { return o[k] !== undefined ? o[k] : m; }); };
  var body = document.body;
  var reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isMobile = function () { return window.matchMedia && matchMedia('(max-width:' + (D.bp || 900) + 'px)').matches; };
  var once = function (el, key) { if (!el || el['_zt' + key]) return false; el['_zt' + key] = 1; return true; };

  /* ─────────────────────────── AJAX ─────────────────────────── */
  function ajax(action, data) {
    data = data || {};
    data.action = action;
    data.nonce = D.nonce;
    return $.ajax({ url: D.ajax, type: 'POST', data: data, dataType: 'json' }).then(function (r) {
      if (r && r.success === false) { return $.Deferred().reject(r.data || {}); }
      return r && r.data !== undefined ? r.data : r;
    });
  }
  window.ztAjax = ajax;

  /* ─────────────────────────── cart badges ─────────────────────────── */
  function paintBadges(v, bump) {
    $$('[data-zt-cart-badge]').forEach(function (el) {
      el.textContent = fa(v);
      if (!el.hasAttribute('data-zt-keep')) el.style.visibility = v > 0 ? '' : 'hidden';
      if (bump && !reduced) { el.classList.remove('zt-is-bumping'); void el.offsetWidth; el.classList.add('zt-is-bumping'); }
    });
    D.count = v;
  }
  function syncCountFromFragments() {
    var el = $1('.zt-cart-count-data');
    if (el && !D.demo) { var n = parseInt(el.getAttribute('data-count'), 10); if (!isNaN(n) && n !== D.count) paintBadges(n, true); }
  }
  $(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart', function () { setTimeout(syncCountFromFragments, 0); });

  /* ─────────────────────────── toast ─────────────────────────── */
  var toastHost = null;
  function toast(text, opts) {
    toastHost = toastHost || $1('[data-zt-toasts]');
    if (!toastHost) {
      toastHost = document.createElement('div');
      toastHost.className = 'zt-toasts zt-w';
      toastHost.setAttribute('data-zt-toasts', '');
      document.body.appendChild(toastHost);
    }
    opts = opts || {};
    var t = document.createElement('div');
    t.className = 'zt-toast' + (opts.error ? ' zt-toast--error' : '');
    t.innerHTML = '<span class="zt-toast__ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' +
      (opts.error ? '<path d="M18 6 6 18M6 6l12 12"/>' : '<path d="M20 6 9 17l-5-5"/>') + '</svg></span><span></span>';
    t.children[1].textContent = text;
    var close = function () { t.classList.add('zt-is-out'); setTimeout(function () { t.remove(); }, 300); };
    if (opts.actionText) {
      var a = document.createElement(opts.href ? 'a' : 'button');
      a.textContent = opts.actionText;
      if (opts.href) a.href = opts.href; else a.addEventListener('click', function () { if (opts.onAction) opts.onAction(); close(); });
      t.appendChild(a);
    }
    toastHost.appendChild(t);
    setTimeout(close, opts.duration || 3200);
    return t;
  }
  window.zitehToast = toast;

  /* ─────────────────────────── scrim / layers ─────────────────────────── */
  var scrim = $1('[data-zt-scrim]');
  var openLayers = 0;
  function lock() {
    openLayers++;
    body.classList.add('zt-no-scroll');
    if (scrim) { scrim.hidden = false; void scrim.offsetWidth; scrim.classList.add('zt-is-open'); }
  }
  function unlock() {
    openLayers = Math.max(0, openLayers - 1);
    if (openLayers === 0) {
      body.classList.remove('zt-no-scroll');
      if (scrim) { scrim.classList.remove('zt-is-open'); setTimeout(function () { if (openLayers === 0) scrim.hidden = true; }, 300); }
    }
  }

  /* ─────────────────────────── drawer ─────────────────────────── */
  var drawer = $1('[data-zt-drawer]');
  function openDrawer() {
    if (!drawer) return;
    drawer.hidden = false; lock(); void drawer.offsetWidth;
    drawer.classList.add('zt-is-open');
  }
  function closeDrawer() {
    if (!drawer || !drawer.classList.contains('zt-is-open')) return;
    drawer.classList.remove('zt-is-open'); unlock();
    setTimeout(function () { drawer.hidden = true; }, 380);
  }
  $$('[data-zt-drawer-acc]').forEach(function (acc) {
    var b = $1('button', acc);
    if (b) b.addEventListener('click', function () { acc.classList.toggle('zt-is-open'); });
  });

  /* ─────────────────────────── search pane ─────────────────────────── */
  var pane = $1('[data-zt-searchpane]');
  var sInput = $1('[data-zt-search-input]');
  var RECENT_KEY = 'zt.search.recent';
  function openSearch() {
    if (!pane) { var f = $1('.zt-search input'); if (f) f.focus(); return; }
    pane.hidden = false; lock(); void pane.offsetWidth;
    pane.classList.add('zt-is-open');
    paintRecent();
    setTimeout(function () { if (sInput) sInput.focus(); }, 120);
  }
  function closeSearch() {
    if (!pane || !pane.classList.contains('zt-is-open')) return;
    pane.classList.remove('zt-is-open'); unlock();
    setTimeout(function () { pane.hidden = true; }, 300);
  }
  function recentGet() { try { return JSON.parse(localStorage.getItem(RECENT_KEY) || 'null'); } catch (e) { return null; } }
  function recentPush(q) {
    q = String(q || '').trim(); if (!q) return;
    var list = recentGet() || [];
    list = [q].concat(list.filter(function (x) { return x !== q; })).slice(0, 6);
    try { localStorage.setItem(RECENT_KEY, JSON.stringify(list)); } catch (e) {}
  }
  function paintRecent() {
    var box = $1('[data-zt-search-recent]');
    var list = recentGet();
    if (!box || !list || !list.length) return;
    box.innerHTML = '';
    list.forEach(function (q) { var b = document.createElement('button'); b.className = 'zt-chipbtn'; b.textContent = q; box.appendChild(b); });
  }
  // desktop header search hands over to the overlay on small screens
  $$('.zt-search input').forEach(function (inp) {
    inp.addEventListener('focus', function () { if (isMobile() && pane) { inp.blur(); openSearch(); } });
  });
  if (sInput && pane) {
    var idle = $1('[data-zt-search-idle]'), res = $1('[data-zt-search-results]'),
      list = $1('[data-zt-search-list]'), cnt = $1('[data-zt-search-count]'),
      empty = $1('[data-zt-search-empty]'), clr = $1('[data-zt-search-clear]');
    var timer = null, seq = 0;
    var run = function (q) {
      q = q.trim();
      clr.hidden = !q;
      if (!q) { idle.hidden = false; res.hidden = true; return; }
      clearTimeout(timer);
      timer = setTimeout(function () {
        var my = ++seq;
        ajax('zt_search', { q: q }).then(function (r) {
          if (my !== seq) return;
          idle.hidden = true; res.hidden = false;
          cnt.textContent = r.count ? tpl(I18N.results, { n: fa(r.count), q: q }) : '';
          empty.hidden = r.count > 0;
          list.innerHTML = r.html || '';
          $$('.zt-sresult', list).forEach(function (a, k) { a.style.animationDelay = (k * 28) + 'ms'; a.addEventListener('click', function () { recentPush(q); }); });
        });
      }, 160);
    };
    sInput.addEventListener('input', function (e) { run(e.target.value); });
    clr.addEventListener('click', function () { sInput.value = ''; run(''); sInput.focus(); });
    pane.addEventListener('click', function (e) {
      var c = e.target.closest('.zt-chipbtn');
      if (!c) return;
      sInput.value = c.textContent; run(c.textContent);
    });
    var form = $1('[data-zt-search-form]');
    if (form) form.addEventListener('submit', function () { recentPush(sInput.value); });
  }

  /* ─────────────────────────── sheets ─────────────────────────── */
  function openSheet(el) {
    if (!el) return;
    el.hidden = false; lock(); void el.offsetWidth;
    el.classList.add('zt-is-open');
  }
  function closeSheet(el) {
    if (!el || !el.classList.contains('zt-is-open')) return;
    el.classList.remove('zt-is-open'); unlock();
    setTimeout(function () { el.hidden = true; var p = $1('.zt-sheet__panel', el); if (p) p.style.transform = ''; }, 440);
  }
  function bindSheet(sh) {
    if (!once(sh, 'sheet')) return;
    $$('[data-zt-sheet-close]', sh).forEach(function (b) { b.addEventListener('click', function () { closeSheet(sh); }); });
    var panel = $1('.zt-sheet__panel', sh), grab = $1('[data-zt-sheet-grab]', sh);
    if (!grab || !panel) return;
    var y0 = 0, dy = 0, on = false;
    var start = function (e) { on = true; y0 = (e.touches ? e.touches[0] : e).clientY; dy = 0; sh.classList.add('zt-is-dragging'); };
    var move = function (e) { if (!on) return; dy = Math.max(0, (e.touches ? e.touches[0] : e).clientY - y0); panel.style.transform = 'translateY(' + dy + 'px)'; };
    var end = function () {
      if (!on) return;
      on = false; sh.classList.remove('zt-is-dragging');
      panel.style.transform = '';
      if (dy > 90) closeSheet(sh);
    };
    grab.addEventListener('touchstart', start, { passive: true });
    grab.addEventListener('touchmove', move, { passive: true });
    grab.addEventListener('touchend', end);
    grab.addEventListener('mousedown', function (e) {
      start(e);
      var up = function () { end(); document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up); };
      document.addEventListener('mousemove', move); document.addEventListener('mouseup', up);
    });
  }
  $$('[data-zt-sheet]').forEach(bindSheet);

  /* ─────────────────────────── add to cart ─────────────────────────── */
  function addToCart(id, qty, extra) {
    if (!id) { return $.Deferred().reject({ message: '' }); }
    var data = $.extend({ product_id: id, quantity: qty || 1 }, extra || {});
    return ajax('zt_add_to_cart', data).then(function (r) {
      paintBadges(r.count, true);
      toast(tpl(I18N.added, { n: fa(qty || 1) }), { actionText: I18N.viewCart, href: (D.urls || {}).cart });
      $(document.body).trigger('added_to_cart', [r.fragments || {}, r.cart_hash || '', null]);
      $(document.body).trigger('wc_fragment_refresh');
      document.dispatchEvent(new CustomEvent('zt:cart', { detail: r }));
      return r;
    }, function (err) {
      toast((err && err.message) || I18N.error, { error: true, duration: 4200 });
      if (err && err.redirect) setTimeout(function () { location.href = err.redirect; }, 900);
      return $.Deferred().reject(err);
    });
  }
  window.ztAddToCart = addToCart;

  /* quick add sheet */
  var qaSheet = $1('#zt-sheet-quickadd');
  var qaData = null;
  function quickAdd(data) {
    if (!qaSheet) { return addToCart(data.id, 1); }
    qaData = data;
    $1('[data-zt-qa-img]', qaSheet).src = data.img || '';
    $1('[data-zt-qa-cat]', qaSheet).textContent = data.cat || '';
    $1('[data-zt-qa-title]', qaSheet).textContent = data.title || '';
    $1('[data-zt-qa-price]', qaSheet).textContent = money(data.price || 0);
    var q = $1('[data-zt-qa-qty]', qaSheet); setQty(q, 1, true);
    openSheet(qaSheet);
  }
  if (qaSheet) {
    $1('[data-zt-qa-add]', qaSheet).addEventListener('click', function () {
      var n = parseInt($1('[data-zt-qa-qty]', qaSheet).getAttribute('data-value') || '1', 10);
      if (!qaData) return;
      var btn = this; btn.disabled = true;
      addToCart(qaData.id, n).always(function () { btn.disabled = false; closeSheet(qaSheet); });
    });
  }

  /* ─────────────────────────── qty stepper ─────────────────────────── */
  function setQty(box, v, silent) {
    var max = parseInt(box.getAttribute('data-max') || '99', 10) || 99;
    var min = parseInt(box.getAttribute('data-min') || '1', 10);
    v = Math.max(isNaN(min) ? 1 : min, Math.min(max, v));
    var out = $1('span', box);
    if (out) out.textContent = fa(v);
    box.setAttribute('data-value', v);
    var inp = $1('input', box); if (inp) inp.value = v;
    if (!silent) box.dispatchEvent(new CustomEvent('qtychange', { bubbles: true, detail: { value: v } }));
  }

  /* ─────────────────────────── wishlist ─────────────────────────── */
  var WKEY = 'zt.wishlist';
  function wishLocal() { try { return JSON.parse(localStorage.getItem(WKEY) || '[]'); } catch (e) { return []; } }
  function wishToggle(id, btns) {
    id = parseInt(id, 10);
    var paint = function (on) {
      btns.forEach(function (b) {
        b.classList.toggle('zt-is-on', on);
        var u = $1('use', b); if (u) u.setAttribute('href', on ? '#i-heart-fill' : '#i-heart');
      });
      toast(on ? I18N.wishOn : I18N.wishOff);
    };
    if (!id) { var on0 = !btns[0].classList.contains('zt-is-on'); paint(on0); return; }
    ajax('zt_wishlist', { product_id: id }).then(function (r) {
      if (!D.loggedIn) {
        var l = wishLocal().filter(function (x) { return x !== id; });
        if (r.on) l.unshift(id);
        try { localStorage.setItem(WKEY, JSON.stringify(l.slice(0, 60))); } catch (e) {}
      }
      paint(!!r.on);
    }, function () { toast(I18N.error, { error: true }); });
  }

  /* ─────────────────────────── global delegation ─────────────────────────── */
  document.addEventListener('click', function (e) {
    var t = e.target;
    var el;
    if ((el = t.closest('[data-zt-drawer-open]'))) { e.preventDefault(); openDrawer(); return; }
    if ((el = t.closest('[data-zt-drawer-close]'))) { closeDrawer(); return; }
    if ((el = t.closest('[data-zt-search-open]'))) { e.preventDefault(); openSearch(); return; }
    if ((el = t.closest('[data-zt-search-close]'))) { closeSearch(); return; }
    if ((el = t.closest('[data-zt-sheet-open]'))) { e.preventDefault(); var sh = $1(el.getAttribute('data-zt-sheet-open')); if (sh) { bindSheet(sh); openSheet(sh); } return; }
    if ((el = t.closest('[data-zt-back]'))) { if (history.length > 1) history.back(); else location.href = (D.urls || {}).home || '/'; return; }

    // qty steppers
    if ((el = t.closest('[data-zt-qty] [data-zt-step]'))) {
      e.preventDefault();
      var box = el.closest('[data-zt-qty]');
      if (box.hasAttribute('data-busy')) return;
      var cur = parseInt(box.getAttribute('data-value') || en($1('span', box).textContent), 10) || 1;
      setQty(box, cur + (el.getAttribute('data-zt-step') === '-1' ? -1 : 1));
      return;
    }

    // product card quick add
    if ((el = t.closest('.zt-prod__add, .zt-relcard__foot button, [data-zt-quick]'))) {
      e.preventDefault(); e.stopPropagation();
      var card = el.closest('[data-zt-product]');
      if (!card) return;
      var data = {};
      try { data = JSON.parse(card.getAttribute('data-zt-product')); } catch (err) {}
      if (data.variable || data.buy === false) { if (data.url) location.href = data.url; return; }
      if (!data.id) { toast(tpl(I18N.added, { n: fa(1) }), { actionText: I18N.viewCart, href: (D.urls || {}).cart }); paintBadges((D.count || 0) + 1, true); return; }
      quickAdd(data);
      return;
    }

    // wishlist buttons
    if ((el = t.closest('[data-zt-wish]'))) {
      e.preventDefault();
      var id = el.getAttribute('data-zt-wish');
      wishToggle(id, $$('[data-zt-wish="' + id + '"]'));
      return;
    }
    if ((el = t.closest('[data-zt-wishlist-tab]'))) { return; }
  }, false);

  /* ─────────────────────────── Esc / scrim ─────────────────────────── */
  function closeTop() {
    var sh = $1('[data-zt-sheet].zt-is-open');
    if (sh) return closeSheet(sh);
    if (pane && pane.classList.contains('zt-is-open')) return closeSearch();
    if (drawer && drawer.classList.contains('zt-is-open')) return closeDrawer();
  }
  if (scrim) scrim.addEventListener('click', closeTop);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeTop(); });

  /* ─────────────────────────── app bar / scroll ─────────────────────────── */
  var appbar = $1('[data-zt-appbar]');
  var abTitle = $1('[data-zt-appbar-title]');
  var prog = $1('[data-zt-scrollprog]');
  var toTop = $1('[data-zt-totop]');
  var header = $1('.zt-header');
  var titleRef = $1('.zt-page-head h1, .zt-page-head .zt-ph-title, .zt-hero, .zt-pdp h1');
  var lastY = 0, ticking = false;
  function onScroll() {
    var y = window.scrollY || 0;
    var h = document.documentElement.scrollHeight - window.innerHeight;
    if (prog) prog.style.width = (h > 0 ? Math.min(100, (y / h) * 100) : 0) + '%';
    if (header) header.classList.toggle('zt-is-stuck', y > 8);
    if (appbar) {
      appbar.classList.toggle('zt-is-stuck', y > 8);
      if (D.fx && D.fx.hideBar !== false && !body.classList.contains('zt-no-scroll')) appbar.classList.toggle('zt-is-hidden', y > 220 && y > lastY + 6);
      if (y < lastY - 6) appbar.classList.remove('zt-is-hidden');
    }
    if (abTitle) {
      if (titleRef) { abTitle.classList.toggle('zt-is-on', titleRef.getBoundingClientRect().bottom < 66); }
      else abTitle.classList.add('zt-is-on');
    }
    if (toTop) toTop.classList.toggle('zt-is-on', y > 700);
    lastY = y;
    ticking = false;
  }
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
  onScroll();
  if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }); });

  /* ─────────────────────────── ripple ─────────────────────────── */
  if (!reduced && (!D.fx || D.fx.ripple !== false)) {
    document.addEventListener('pointerdown', function (e) {
      var el = e.target.closest('.zt-btn, .zt-prod__add, .zt-chipbtn, .zt-opt, .zt-gift, .zt-quick a, .zt-wide-btn');
      if (!el) return;
      var r = el.getBoundingClientRect();
      var d = Math.max(r.width, r.height);
      var s = document.createElement('span');
      s.className = 'zt-ripple';
      s.style.cssText = 'width:' + d + 'px;height:' + d + 'px;left:' + (e.clientX - r.left - d / 2) + 'px;top:' + (e.clientY - r.top - d / 2) + 'px';
      el.appendChild(s);
      setTimeout(function () { s.remove(); }, 600);
    });
  }

  /* ─────────────────────────── action bar ─────────────────────────── */
  $$('[data-zt-ab-add]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      var main = $1('[data-zt-buybox-add]');
      if (main) { main.click(); return; }
      var id = parseInt(b.getAttribute('data-zt-ab-add'), 10);
      addToCart(id, 1);
    });
  });
  $$('[data-zt-ab-pay]').forEach(function (b) {
    b.addEventListener('click', function (e) {
      e.preventDefault();
      var po = $1('#place_order');
      if (po) po.click(); else { var f = $1('form.checkout'); if (f) $(f).trigger('submit'); }
    });
  });

  /* ─────────────────────────── footer accordion (mobile) ─────────────────────────── */
  document.addEventListener('click', function (e) {
    var h = e.target.closest('.zt-footer--acc .zt-footer__col h4');
    if (!h || !isMobile()) return;
    var col = h.parentNode;
    if (col.classList.contains('zt-footer__col--static')) return;
    col.classList.toggle('zt-is-open');
  });

  /* copy-to-clipboard buttons (coupon codes …) + clickable table rows */
  document.addEventListener('click', function (e) {
    var c = e.target.closest('[data-zt-copy]');
    if (c) {
      e.preventDefault();
      var v = c.getAttribute('data-zt-copy');
      try { navigator.clipboard.writeText(v); } catch (err) {}
      c.classList.add('zt-is-done');
      toast(I18N.copied_code || I18N.copied);
      setTimeout(function () { c.classList.remove('zt-is-done'); }, 2200);
      return;
    }
    var r = e.target.closest('[data-zt-href]');
    if (r && !D.editor && (r.tagName === 'BUTTON' || !e.target.closest('a,button'))) location.href = r.getAttribute('data-zt-href');
  });

  /* ==========================================================================
     Per-scope initialisers (widgets)
     ========================================================================== */
  var inits = [];
  function def(fn) { inits.push(fn); }

  /* skeleton shimmer until images load */
  def(function (root) {
    if (D.fx && D.fx.skeleton === false) return;
    $$('img', root).forEach(function (img) {
      if (!once(img, 'sk') || img.closest('.zt-appbar, .zt-drawer, .zt-sheet')) return;
      var box = img.parentElement;
      var mark = function () { img.classList.remove('zt-is-loading'); img.classList.add('zt-is-loaded'); if (box) box.classList.remove('zt-sk'); };
      if (img.complete && img.naturalWidth) { img.classList.add('zt-is-loaded'); return; }
      img.classList.add('zt-is-loading'); if (box) box.classList.add('zt-sk');
      img.addEventListener('load', mark, { once: true });
      img.addEventListener('error', mark, { once: true });
    });
  });

  /* reveal on scroll */
  var revealIO = null;
  def(function (root) {
    var els = $$('.zt-reveal', root);
    if (!els.length) return;
    if (D.fx && D.fx.reveal === false) { els.forEach(function (e) { e.classList.add('zt-in'); }); return; }
    document.documentElement.classList.add('zt-js-reveal');
    var paint = function () {
      var h = window.innerHeight || 800;
      els.forEach(function (el) { if (!el.classList.contains('zt-in') && el.getBoundingClientRect().top < h - 40) el.classList.add('zt-in'); });
    };
    paint();
    window.addEventListener('scroll', paint, { passive: true });
    window.addEventListener('resize', paint);
    window.addEventListener('load', paint);
    setTimeout(function () { els.forEach(function (el) { el.classList.add('zt-in'); }); }, D.editor ? 50 : 4000);
    void revealIO;
  });

  /* FAQ: only one open at a time (optional) */
  def(function (root) {
    $$('[data-zt-faq-single]', root).forEach(function (w) {
      if (!once(w, 'faq')) return;
      $$('details', w).forEach(function (d) {
        d.addEventListener('toggle', function () {
          if (d.open) $$('details', w).forEach(function (x) { if (x !== d) x.open = false; });
        });
      });
    });
  });

  /* tabs (product) */
  def(function (root) {
    $$('[data-zt-tabs]', root).forEach(function (tabsEl) {
      if (!once(tabsEl, 'tabs')) return;
      var scope = tabsEl.parentNode;
      var tabs = $$('[data-zt-tab]', tabsEl);
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          var id = tab.getAttribute('data-zt-tab');
          tabs.forEach(function (x) { x.classList.toggle('zt-is-active', x === tab); });
          $$('[data-zt-panel]', scope).forEach(function (p) { p.hidden = p.getAttribute('data-zt-panel') !== id; });
        });
      });
      // mobile: tabs → accordion (as in the design)
      if (isMobile() && !D.editor) {
        var chev = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
        var wrap = document.createElement('div');
        wrap.className = 'zt-macc';
        tabs.forEach(function (btn, k) {
          var panel = $1('[data-zt-panel="' + btn.getAttribute('data-zt-tab') + '"]', scope);
          if (!panel) return;
          panel.hidden = false;
          var item = document.createElement('section');
          item.className = 'zt-macc__i' + (k === 0 ? ' zt-is-open' : '');
          var head = document.createElement('button');
          head.className = 'zt-macc__h';
          head.innerHTML = '<span></span>' + chev;
          head.firstChild.textContent = btn.textContent.trim();
          var bd = document.createElement('div'); bd.className = 'zt-macc__b';
          var inner = document.createElement('div'); inner.appendChild(panel); bd.appendChild(inner);
          item.appendChild(head); item.appendChild(bd);
          head.addEventListener('click', function () { item.classList.toggle('zt-is-open'); });
          wrap.appendChild(item);
        });
        if (wrap.children.length) { tabsEl.parentNode.insertBefore(wrap, tabsEl.nextSibling); tabsEl.remove(); }
      }
    });
  });

  /* simple accordion (shipping box) */
  def(function (root) {
    $$('[data-zt-accordion]', root).forEach(function (acc) {
      if (!once(acc, 'acc')) return;
      var head = $1('[data-zt-accordion-head]', acc);
      if (head) head.addEventListener('click', function () { acc.classList.toggle('zt-is-open'); });
    });
  });

  /* radio-style option cards */
  def(function (root) {
    $$('[data-zt-radio-group]', root).forEach(function (group) {
      if (!once(group, 'radio')) return;
      group.addEventListener('click', function (e) {
        var item = e.target.closest('[data-zt-radio]');
        if (!item || !group.contains(item) || item.classList.contains('zt-is-disabled')) return;
        var items = $$('[data-zt-radio]', group);
        var was = item.classList.contains('zt-is-selected');
        items.forEach(function (i) { i.classList.remove('zt-is-selected'); });
        item.classList.add('zt-is-selected');
        var input = $1('input', item);
        if (input && !input.checked) { input.checked = true; $(input).trigger('change'); }
        if (!was && D.fx && D.fx.toasts !== false && !group.hasAttribute('data-zt-silent')) {
          var head = group.previousElementSibling;
          var label = head && head.textContent ? head.textContent.trim() : '';
          var name = ($1('b', item) || {}).textContent;
          if (name) toast(tpl(I18N.selected, { label: label, name: name.trim() }).replace(/^: /, ''), { duration: 1800 });
        }
        group.dispatchEvent(new CustomEvent('zt:radio', { bubbles: true, detail: { item: item } }));
      });
    });
  });

  /* horizontal carousels (desktop arrows) */
  def(function (root) {
    $$('[data-zt-carousel]', root).forEach(function (c) {
      if (!once(c, 'car')) return;
      var track = $1('[data-zt-track]', c);
      if (!track) return;
      var step = function () {
        var first = track.firstElementChild;
        if (!first) return 320;
        var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 22;
        return first.getBoundingClientRect().width + gap;
      };
      $$('[data-zt-dir]', c).forEach(function (btn) {
        btn.addEventListener('click', function () {
          track.scrollBy({ left: (btn.getAttribute('data-zt-dir') === 'next' ? -1 : 1) * step(), behavior: reduced ? 'auto' : 'smooth' });
        });
      });
    });
    // generic scroller arrows (categories)
    $$('[data-zt-scroll]', root).forEach(function (btn) {
      if (!once(btn, 'scr')) return;
      btn.addEventListener('click', function () {
        var sec = btn.closest('.zt-section') || btn.parentNode.parentNode;
        var sc = $1('[data-zt-scroller]', sec);
        if (!sc) return;
        sc.scrollBy({ left: (btn.getAttribute('data-zt-scroll') === 'next' ? -1 : 1) * (sc.clientWidth * 0.8), behavior: reduced ? 'auto' : 'smooth' });
      });
    });
  });

  /* image sliders (hero desktop + mobile) */
  def(function (root) {
    $$('[data-zt-mslider]', root).forEach(function (sl) {
      if (!once(sl, 'ms')) return;
      var track = $1('[data-zt-mtrack]', sl);
      var dots = $$('[data-zt-mdots] button', sl);
      if (!track) return;
      var slides = function () { return Array.prototype.slice.call(track.children); };
      var idx = 0, timer = null, paused = false;
      var paint = function (i) { idx = i; dots.forEach(function (d, k) { d.classList.toggle('zt-is-active', k === i); }); };
      var goTo = function (i, smooth) {
        var s = slides()[i]; if (!s) return;
        var sr = s.getBoundingClientRect(), tr = track.getBoundingClientRect();
        var delta = (sr.left + sr.right) / 2 - (tr.left + tr.right) / 2;
        track.scrollBy({ left: delta, behavior: (smooth === false || reduced) ? 'instant' : 'smooth' });
        paint(i);
      };
      var nearest = function () {
        var tc = track.getBoundingClientRect(), mid = (tc.left + tc.right) / 2, best = 0, bd = Infinity;
        slides().forEach(function (s, k) { var r = s.getBoundingClientRect(); var d = Math.abs((r.left + r.right) / 2 - mid); if (d < bd) { bd = d; best = k; } });
        return best;
      };
      var tick = null;
      track.addEventListener('scroll', function () {
        clearTimeout(tick);
        tick = setTimeout(function () { var n = nearest(); if (n !== idx) paint(n); }, 70);
      }, { passive: true });
      dots.forEach(function (d, k) { d.addEventListener('click', function () { stop(); goTo(k); }); });
      $$('[data-zt-mdir]', sl).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var n = slides().length;
          var cur = nearest();
          goTo((cur + (btn.getAttribute('data-zt-mdir') === 'next' ? 1 : -1) + n) % n);
        });
      });
      var interval = parseInt(sl.getAttribute('data-zt-autoplay') || '5200', 10);
      var step = function () { if (!paused && slides().length > 1) goTo((idx + 1) % slides().length); };
      var start = function () { if (!reduced && interval > 0 && !timer && !D.editor) timer = setInterval(step, interval); };
      var stop = function () { clearInterval(timer); timer = null; };
      ['touchstart', 'pointerdown'].forEach(function (ev) { track.addEventListener(ev, function () { paused = true; }, { passive: true }); });
      ['touchend', 'pointerup', 'pointercancel'].forEach(function (ev) { track.addEventListener(ev, function () { setTimeout(function () { paused = false; }, 3500); }, { passive: true }); });
      document.addEventListener('visibilitychange', function () { if (document.hidden) stop(); else start(); });
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (e) { if (e[0].isIntersecting) start(); else stop(); }, { threshold: 0.35 }).observe(sl);
      } else start();
    });
  });

  /* countdown */
  def(function (root) {
    $$('[data-zt-countdown]', root).forEach(function (cd) {
      if (!once(cd, 'cd')) return;
      var cells = { d: $1('[data-zt-cd="d"]', cd), h: $1('[data-zt-cd="h"]', cd), m: $1('[data-zt-cd="m"]', cd), s: $1('[data-zt-cd="s"]', cd) };
      var sec = cd.closest('[data-zt-cd-end]');
      var endMode = sec ? sec.getAttribute('data-zt-cd-end') : 'zero';
      var end = parseInt(cd.getAttribute('data-zt-end') || '0', 10) * 1000;
      var ever = parseInt(cd.getAttribute('data-zt-evergreen') || '0', 10);
      if (ever) {
        var key = 'zt.cd.' + ever;
        var saved = 0;
        try { saved = parseInt(localStorage.getItem(key) || '0', 10); } catch (e) {}
        if (!saved || saved < Date.now()) { saved = Date.now() + ever * 1000; try { localStorage.setItem(key, saved); } catch (e) {} }
        end = saved;
      }
      if (!end) end = Date.now() + (parseInt(cd.getAttribute('data-zt-countdown'), 10) || 0) * 1000;
      var pad = function (n) { return fa(String(n).padStart(2, '0')); };
      var t = function () {
        var left = Math.max(0, Math.round((end - Date.now()) / 1000));
        if (left <= 0) {
          if (endMode === 'restart' && ever) { end = Date.now() + ever * 1000; try { localStorage.setItem('zt.cd.' + ever, end); } catch (e) {} left = ever; }
          else if (endMode === 'hide' && sec && !D.editor) { sec.style.display = 'none'; }
          if (cd.getAttribute('data-zt-daily')) { end += 86400000; }
        }
        cells.d.textContent = pad(Math.floor(left / 86400));
        cells.h.textContent = pad(Math.floor(left / 3600) % 24);
        cells.m.textContent = pad(Math.floor(left / 60) % 60);
        cells.s.textContent = pad(left % 60);
      };
      t(); setInterval(t, 1000);
    });
  });

  /* routine morning / night */
  def(function (root) {
    $$('[data-zt-routine]', root).forEach(function (seg) {
      if (!once(seg, 'rt')) return;
      var scope = seg.closest('.zt-section') || seg.parentNode.parentNode;
      var btns = $$('button', seg);
      var set = function (mode) {
        btns.forEach(function (x) { x.classList.toggle('zt-is-active', x.getAttribute('data-value') === mode); });
        $$('[data-zt-steps]', scope).forEach(function (g) { g.hidden = g.getAttribute('data-zt-steps') !== mode; });
      };
      btns.forEach(function (b) { b.addEventListener('click', function () { set(b.getAttribute('data-value')); }); });
      if (seg.getAttribute('data-zt-auto')) { var h = new Date().getHours(); if (h >= 18 || h < 5) set('pm'); }
    });
  });

  /* product gallery */
  def(function (root) {
    $$('[data-zt-gallery]', root).forEach(function (g) {
      if (!once(g, 'gal')) return;
      var main = $1('[data-zt-gallery-main]', g);
      var thumbs = $$('[data-zt-gallery-thumb]', g);
      var mainBox = $1('.zt-gallery__main', g);
      var idx = 0;
      var counter = null;
      var show = function (n) {
        if (!thumbs.length) return;
        idx = (n + thumbs.length) % thumbs.length;
        thumbs.forEach(function (t, k) { t.classList.toggle('zt-is-active', k === idx); });
        var full = thumbs[idx].getAttribute('data-full') || ($1('img', thumbs[idx]) || {}).src;
        if (full && main) { main.src = full; main.removeAttribute('srcset'); }
        if (counter) counter.textContent = fa(idx + 1) + ' / ' + fa(thumbs.length);
      };
      thumbs.forEach(function (t, k) { t.addEventListener('click', function () { show(k); }); });
      $$('[data-zt-gallery-dir]', g).forEach(function (b) { b.addEventListener('click', function () { show(idx + (b.getAttribute('data-zt-gallery-dir') === 'next' ? 1 : -1)); }); });
      document.addEventListener('zt:variation', function (e) {
        if (!main || !e.detail || !e.detail.image) return;
        main.src = e.detail.image; main.removeAttribute('srcset');
      });
      if (isMobile() && mainBox && thumbs.length) {
        counter = document.createElement('span');
        counter.className = 'zt-gcount';
        mainBox.appendChild(counter);
        counter.textContent = fa(1) + ' / ' + fa(thumbs.length);
      }
      if (mainBox) {
        var gx = 0, gy = 0, moved = false;
        mainBox.addEventListener('touchstart', function (e) { gx = e.touches[0].clientX; gy = e.touches[0].clientY; moved = false; }, { passive: true });
        mainBox.addEventListener('touchmove', function (e) { if (Math.abs(e.touches[0].clientX - gx) > 12) moved = true; }, { passive: true });
        mainBox.addEventListener('touchend', function (e) {
          if (!moved) return;
          var dx = e.changedTouches[0].clientX - gx, dy = e.changedTouches[0].clientY - gy;
          if (Math.abs(dx) < 45 || Math.abs(dx) < Math.abs(dy)) return;
          show(idx + (dx < 0 ? 1 : -1));
          if (main && !reduced) { main.style.animation = 'none'; void main.offsetWidth; main.style.animation = 'zt-fadeimg .35s var(--zt-ease-out)'; }
        });
      }
    });
  });

  /* buy box: add to cart (+ variations) */
  def(function (root) {
    $$('[data-zt-buybox]', root).forEach(function (bb) {
      if (!once(bb, 'bb')) return;
      var add = $1('[data-zt-buybox-add]', bb);
      var qty = $1('[data-zt-qty]', bb);
      var form = $1('form.variations_form', bb) || $1('[data-zt-variations]', bb);
      var priceEl = $1('[data-zt-bb-price]', bb);
      var oldEl = $1('[data-zt-bb-old]', bb);
      var variations = [];
      try { variations = JSON.parse(bb.getAttribute('data-zt-variations') || '[]'); } catch (e) {}
      var current = null;
      var match = function () {
        var sel = {};
        var all = true;
        $$('select[data-zt-attr]', bb).forEach(function (s) { sel[s.name] = s.value; if (!s.value) all = false; });
        if (!all) return null;
        for (var i = 0; i < variations.length; i++) {
          var v = variations[i], ok = true;
          for (var k in v.attributes) { if (v.attributes[k] && v.attributes[k] !== sel[k]) { ok = false; break; } }
          if (ok) return v;
        }
        return null;
      };
      $$('select[data-zt-attr]', bb).forEach(function (s) {
        s.addEventListener('change', function () {
          current = match();
          if (current) {
            if (priceEl) priceEl.innerHTML = money(current.price) + ' <small>' + (D.currency || '') + '</small>';
            if (oldEl) { oldEl.hidden = !(current.regular > current.price); var d = $1('del', oldEl); if (d) d.textContent = money(current.regular) + ' ' + (D.currency || ''); }
            if (current.image) document.dispatchEvent(new CustomEvent('zt:variation', { detail: current }));
            if (qty && current.max) qty.setAttribute('data-max', current.max);
          }
          if (add) add.classList.toggle('zt-is-disabled', !current && variations.length > 0);
        });
      });
      void form;
      if (add) {
        add.addEventListener('click', function (e) {
          e.preventDefault();
          var id = parseInt(add.getAttribute('data-zt-buybox-add'), 10);
          var n = qty ? parseInt(qty.getAttribute('data-value') || '1', 10) : 1;
          var extra = {};
          if (variations.length) {
            if (!current) { toast(add.getAttribute('data-zt-choose') || 'لطفاً گزینه‌های محصول را انتخاب کنید', { error: true }); return; }
            extra.variation_id = current.id;
            $$('select[data-zt-attr]', bb).forEach(function (s) { extra[s.name] = s.value; });
          }
          add.classList.add('zt-is-loading');
          addToCart(id, n, extra).always(function () { add.classList.remove('zt-is-loading'); });
        });
      }
    });
  });

  /* product reviews: show all */
  def(function (root) {
    $$('[data-zt-reviews-more]', root).forEach(function (b) {
      if (!once(b, 'rv')) return;
      b.addEventListener('click', function (e) {
        var list = b.closest('.zt-reviews');
        var hidden = list ? $$('.zt-rev[hidden]', list) : [];
        if (hidden.length) { e.preventDefault(); hidden.forEach(function (r) { r.hidden = false; }); b.remove(); }
      });
    });
    $$('.zt-revform [data-zt-rate]', root).forEach(function (wrap) {
      if (!once(wrap, 'rate')) return;
      var input = $1('input', wrap.parentNode);
      $$('button', wrap).forEach(function (b, k) {
        b.addEventListener('click', function (e) {
          e.preventDefault();
          var v = k + 1;
          if (input) input.value = v;
          $$('button', wrap).forEach(function (x, j) { x.classList.toggle('zt-is-on', j < v); });
        });
      });
    });
  });

  /* newsletter + contact forms */
  def(function (root) {
    $$('form[data-zt-newsletter]', root).forEach(function (f) {
      if (!once(f, 'nl')) return;
      f.addEventListener('submit', function (e) {
        e.preventDefault();
        var inp = $1('input[type=email]', f), btn = $1('button', f);
        btn.disabled = true;
        ajax('zt_newsletter', { email: inp.value }).then(function (r) { toast(r.message); inp.value = ''; },
          function (err) { toast((err && err.message) || I18N.error, { error: true }); }).always(function () { btn.disabled = false; });
      });
    });
    $$('form[data-zt-contact]', root).forEach(function (f) {
      if (!once(f, 'ct')) return;
      f.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = $1('button[type=submit]', f); btn.disabled = true;
        var data = {}; $(f).serializeArray().forEach(function (x) { data[x.name] = x.value; });
        ajax('zt_contact', data).then(function (r) { toast(r.message); f.reset(); },
          function (err) { toast((err && err.message) || I18N.error, { error: true }); }).always(function () { btn.disabled = false; });
      });
    });
  });

  /* tracking: copy order number (mobile) */
  def(function (root) {
    $$('.zt-ohead__num', root).forEach(function (num) {
      if (!once(num, 'cp') || !isMobile()) return;
      var code = num.textContent.trim();
      var b = document.createElement('button');
      b.className = 'zt-copybtn';
      b.setAttribute('aria-label', 'کپی شماره سفارش');
      b.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>';
      num.appendChild(b);
      b.addEventListener('click', function () {
        try { navigator.clipboard.writeText(en(code).replace(/\D/g, '')); } catch (e) {}
        b.classList.add('zt-is-done');
        toast(I18N.copied);
        setTimeout(function () { b.classList.remove('zt-is-done'); }, 2200);
      });
    });
  });

  /* product grid (archive): sort select */
  def(function (root) {
    $$('[data-zt-orderby]', root).forEach(function (sel) {
      if (!once(sel, 'ob')) return;
      sel.addEventListener('change', function () {
        var u = new URL(location.href);
        u.searchParams.set('orderby', sel.value);
        u.searchParams.delete('paged');
        location.href = u.toString().replace(/\/page\/\d+\/?/, '/');
      });
    });
  });

  /* ==========================================================================
     CART page
     ========================================================================== */
  function applyCartState(st) {
    if (!st) return;
    paintBadges(st.count, false);
    $$('[data-zt-sum-subtotal]').forEach(function (el) { el.textContent = money(st.subtotal); });
    $$('[data-zt-sum-total]').forEach(function (el) { el.textContent = money(st.total); });
    $$('[data-zt-ab-total]').forEach(function (el) { if (body.classList.contains('zt-page-cart')) el.textContent = money(st.total); });
    $$('[data-zt-sum-discount]').forEach(function (el) {
      if (st.discount > 0) { el.textContent = '− ' + money(st.discount) + ' ' + (D.currency || ''); el.classList.remove('zt-dash'); }
      else { el.textContent = el.getAttribute('data-empty') || '- - - -'; el.classList.add('zt-dash'); }
    });
    $$('[data-zt-sum-ship]').forEach(function (el) {
      if (st.free_shipping) { el.textContent = el.getAttribute('data-free') || 'رایگان'; el.classList.remove('zt-dash'); }
      else { el.textContent = el.getAttribute('data-next') || ''; el.classList.add('zt-dash'); }
    });
    Object.keys(st.lines || {}).forEach(function (k) {
      var row = $1('[data-zt-line="' + k + '"]');
      if (!row) return;
      var lt = $1('[data-zt-line-total]', row);
      if (lt) lt.textContent = money(st.lines[k].total);
      var q = $1('[data-zt-qty]', row);
      if (q && parseInt(q.getAttribute('data-value'), 10) !== st.lines[k].qty) setQty(q, st.lines[k].qty, true);
    });
    // tiers
    var t = st.tiers;
    $$('[data-zt-tiers]').forEach(function (box) {
      if (!t) return;
      $$('[data-zt-tier]', box).forEach(function (el) { el.classList.toggle('zt-is-done', st.subtotal_tier >= parseFloat(el.getAttribute('data-zt-tier'))); });
      var fill = $1('[data-zt-tier-fill]', box);
      if (fill && t.fill >= 0) { fill.classList.add('zt-fill-l'); fill.style.width = 'calc((100% - 2 * var(--zt-tin, 100px)) * ' + (t.fill / 100) + ')'; }
    });
    $$('[data-zt-tier-msg]').forEach(function (el) { if (t) el.textContent = t.msg; });
    $$('[data-zt-tier-bar]').forEach(function (el) { if (t) el.style.width = t.bar + '%'; });
    // samples
    $$('[data-zt-samples]').forEach(function (box) {
      box.classList.toggle('zt-is-locked', !st.samples_unlocked);
      var lock = $1('[data-zt-samples-lock]', box.parentNode);
      if (lock) lock.hidden = !!st.samples_unlocked;
      if (!st.samples_unlocked) $$('[data-zt-radio]', box).forEach(function (g) { g.classList.remove('zt-is-selected'); var i = $1('input', g); if (i) i.checked = false; });
    });
    // empty
    $$('[data-zt-cart-items]').forEach(function (card) {
      var table = $1('.zt-ctable', card), empty = $1('.zt-cart-empty', card), foot = $1('.zt-ctable-foot', card);
      var isEmpty = st.count === 0;
      if (table) table.hidden = isEmpty;
      if (foot) foot.hidden = isEmpty;
      if (empty) empty.hidden = !isEmpty;
    });
    $$('.zt-actionbar [data-zt-ab-checkout], [data-zt-checkout-btn]').forEach(function (b) {
      b.style.opacity = st.count ? '' : '.5'; b.style.pointerEvents = st.count ? '' : 'none';
    });
    document.dispatchEvent(new CustomEvent('zt:cartstate', { detail: st }));
  }
  window.ztApplyCartState = applyCartState;

  def(function (root) {
    $$('[data-zt-cart-items]', root).forEach(function (card) {
      if (!once(card, 'cart')) return;
      var pending = {};
      card.addEventListener('qtychange', function (e) {
        var row = e.target.closest('[data-zt-line]');
        if (!row) return;
        var key = row.getAttribute('data-zt-line');
        var price = parseFloat(row.getAttribute('data-price') || '0');
        var lt = $1('[data-zt-line-total]', row);
        if (lt && price) lt.textContent = money(price * e.detail.value);
        clearTimeout(pending[key]);
        pending[key] = setTimeout(function () {
          if (D.editor || !key || key.indexOf('demo') === 0) return;
          ajax('zt_cart_qty', { key: key, qty: e.detail.value }).then(applyCartState, function (err) { toast((err && err.message) || I18N.error, { error: true }); });
        }, 350);
      });
      var removeRow = function (row) {
        var key = row.getAttribute('data-zt-line');
        row.style.opacity = '0';
        setTimeout(function () { row.remove(); }, 200);
        if (D.editor || key.indexOf('demo') === 0) return $.Deferred().resolve();
        return ajax('zt_cart_remove', { key: key }).then(applyCartState);
      };
      card.addEventListener('click', function (e) {
        var rm = e.target.closest('[data-zt-remove]');
        if (rm) { e.preventDefault(); removeRow(rm.closest('[data-zt-line]')); return; }
        var clr = e.target.closest('[data-zt-cart-clear]');
        if (clr) {
          e.preventDefault();
          $$('[data-zt-line]', card).forEach(function (r) { r.style.opacity = '0'; setTimeout(function () { r.remove(); }, 200); });
          if (!D.editor) ajax('zt_cart_clear', {}).then(applyCartState);
          toast(I18N.cleared);
          return;
        }
        var upd = e.target.closest('[data-zt-cart-refresh]');
        if (upd) { e.preventDefault(); ajax('zt_cart_state', {}).then(function (st) { applyCartState(st); toast(I18N.updated); }); }
      });
      // swipe to delete (mobile)
      $$('[data-zt-line]', card).forEach(function (row) {
        var x0 = 0, dx = 0, on = false;
        row.addEventListener('touchstart', function (e) { if (e.target.closest('.zt-qty, button, a')) return; on = true; x0 = e.touches[0].clientX; dx = 0; row.style.transition = 'none'; }, { passive: true });
        row.addEventListener('touchmove', function (e) { if (!on) return; dx = Math.max(-140, Math.min(140, e.touches[0].clientX - x0)); row.style.transform = 'translateX(' + dx + 'px)'; }, { passive: true });
        row.addEventListener('touchend', function () {
          if (!on) return; on = false;
          if (Math.abs(dx) > 110) {
            row.style.transition = 'transform .28s ease, opacity .28s ease';
            row.style.transform = 'translateX(' + (dx < 0 ? -420 : 420) + 'px)';
            row.style.opacity = '0';
            setTimeout(function () { removeRow(row); }, 240);
          } else { row.style.transition = 'transform .3s cubic-bezier(.22,1,.36,1)'; row.style.transform = ''; }
        });
      });
    });
    // gift samples
    $$('[data-zt-samples]', root).forEach(function (box) {
      if (!once(box, 'smp')) return;
      box.addEventListener('zt:radio', function (e) {
        if (box.classList.contains('zt-is-locked')) return;
        var i = e.detail.item.getAttribute('data-index');
        if (!D.editor) ajax('zt_sample', { index: i }).then(function (r) { if (r && r.state) applyCartState(r.state); });
      });
      box.addEventListener('click', function (e) {
        if (!box.classList.contains('zt-is-locked')) return;
        if (e.target.closest('[data-zt-radio]')) { e.stopImmediatePropagation(); var m = box.getAttribute('data-locked-msg'); if (m) toast(m, { error: true }); }
      }, true);
    });
  });

  // cart breakdown sheet from the sticky bar (mobile)
  (function () {
    if (!body.classList.contains('zt-page-cart') || !isMobile()) return;
    var price = $1('.zt-actionbar__price');
    var sumCard = $1('.zt-sum');
    if (!price || !sumCard) return;
    price.classList.add('zt-is-tappable');
    price.setAttribute('role', 'button');
    price.insertAdjacentHTML('beforeend', '<svg class="zt-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg>');
    var sheet = document.createElement('div');
    sheet.className = 'zt-sheet'; sheet.id = 'zt-sheet-total'; sheet.hidden = true;
    sheet.setAttribute('data-zt-sheet', '');
    sheet.innerHTML = '<div class="zt-sheet__panel"><div class="zt-sheet__grab" data-zt-sheet-grab></div><div class="zt-sheet__body"><h3 class="zt-sheetttl"></h3><div class="zt-brk"></div></div>' +
      '<div class="zt-sheet__foot"><button class="zt-btn zt-btn--ghost" data-zt-sheet-close></button><a class="zt-btn zt-btn--primary"></a></div></div>';
    $1('.zt-sheetttl', sheet).textContent = I18N.payDetails;
    $1('[data-zt-sheet-close]', sheet).textContent = I18N.close;
    var go = $1('a.zt-btn', sheet); go.textContent = I18N.order; go.href = (D.urls || {}).checkout;
    var host = $1('.zt-shell-root') || body; host.appendChild(sheet);
    bindSheet(sheet);
    var brk = $1('.zt-brk', sheet);
    price.addEventListener('click', function () {
      var rows = $$('.zt-sum__row', sumCard).map(function (r) { return '<div><span>' + r.children[0].textContent.trim() + '</span><span>' + r.children[1].textContent.trim() + '</span></div>'; });
      var tot = $1('.zt-sum__total', sumCard);
      if (tot) rows.push('<div class="zt-grand"><span>' + tot.children[0].textContent.trim() + '</span><span>' + tot.children[1].textContent.trim() + '</span></div>');
      brk.innerHTML = rows.join('');
      openSheet(sheet);
    });
  })();

  /* ==========================================================================
     CHECKOUT page
     ========================================================================== */
  def(function (root) {
    var form = $1('form.zt-co-form', root);
    if (!form || !once(form, 'co')) return;
    var provSel = $1('[data-zt-province]', form), citySel = $1('[data-zt-city]', form);
    var CITIES = {};
    try { CITIES = JSON.parse(form.getAttribute('data-zt-cities') || '{}'); } catch (e) {}
    var cityPh = citySel ? (citySel.getAttribute('data-placeholder') || I18N.city) : '';
    var setCities = function (list, keep) {
      if (!citySel) return;
      var cur = keep || '';
      citySel.innerHTML = '<option value="" disabled' + (cur ? '' : ' selected') + '>' + (list && list.length ? I18N.city : (citySel.getAttribute('data-empty') || I18N.emptyCity)) + '</option>' +
        (list || []).map(function (c) { return '<option' + (c === cur ? ' selected' : '') + '>' + c + '</option>'; }).join('');
      citySel.disabled = !(list && list.length);
    };
    void cityPh;
    if (provSel && citySel) {
      var initial = citySel.getAttribute('data-value') || '';
      if (provSel.value) setCities(CITIES[provSel.value] || [], initial); else citySel.disabled = true;
      provSel.addEventListener('change', function () { setCities(CITIES[provSel.value] || []); $(citySel).trigger('change'); });
      citySel.addEventListener('change', function () {
        var city = citySel.value.trim();
        var auto = $1('[data-zt-ship-auto]', form);
        $$('[data-zt-ship-auto]', form).forEach(function (opt) {
          var list = (opt.getAttribute('data-zt-ship-auto') || '').split('|');
          if (city && list.indexOf(city) > -1 && !opt.classList.contains('zt-is-selected')) {
            var name = ($1('b', opt) || {}).textContent || '';
            setTimeout(function () { var inp = $1('input', opt); if (inp) { $$('[data-zt-radio]', opt.parentNode).forEach(function (o) { o.classList.remove('zt-is-selected'); }); opt.classList.add('zt-is-selected'); inp.checked = true; $(inp).trigger('change'); toast(tpl(I18N.peyk, { name: name.trim() })); } }, 600);
          }
        });
        void auto;
      });
    }
    // normalise Persian digits in numeric fields
    $$('input[inputmode=numeric], input[type=tel]', form).forEach(function (i) {
      i.addEventListener('change', function () { i.value = en(i.value); });
    });
    // remember info for guests (save info checkbox)
    var SAVE = 'zt.co.info';
    var save = $1('[name=zt_save_info]', form);
    try {
      var saved = JSON.parse(localStorage.getItem(SAVE) || 'null');
      if (saved && !D.loggedIn) {
        Object.keys(saved).forEach(function (k) {
          var f = form.elements[k];
          if (f && !f.value && f.tagName !== 'SELECT') f.value = saved[k];
        });
        if (saved.billing_state && provSel && !provSel.value) { provSel.value = saved.billing_state; setCities(CITIES[provSel.value] || [], saved.billing_city); }
      }
    } catch (e) {}
    $(document.body).on('checkout_place_order', function () {
      try {
        if (save && save.checked) {
          var o = {};
          $$('[name^=billing_]', form).forEach(function (f) { o[f.name] = f.value; });
          localStorage.setItem(SAVE, JSON.stringify(o));
        } else if (save) localStorage.removeItem(SAVE);
      } catch (e) {}
      return true;
    });
    // coupon
    $$('[data-zt-coupon]').forEach(function (box) {
      if (!once(box, 'cpn')) return;
      var btn = $1('button', box), inp = $1('input', box);
      var apply = function () {
        if (!inp.value.trim()) return;
        btn.disabled = true;
        ajax('zt_coupon', { code: inp.value.trim() }).then(function (r) { toast(r.message); inp.value = ''; $(document.body).trigger('update_checkout'); },
          function (err) { toast((err && err.message) || I18N.error, { error: true }); }).always(function () { btn.disabled = false; });
      };
      btn.addEventListener('click', apply);
      inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); apply(); } });
    });
    // remove item from the summary
    document.addEventListener('click', function (e) {
      var rm = e.target.closest('[data-zt-co-remove]');
      if (!rm) return;
      e.preventDefault();
      var it = rm.closest('.zt-oitem'); if (it) it.style.opacity = '.4';
      ajax('zt_cart_remove', { key: rm.getAttribute('data-zt-co-remove') }).then(function (st) {
        if (st && st.count === 0) { location.href = (D.urls || {}).cart; return; }
        $(document.body).trigger('update_checkout');
      });
    });
    // mobile: collapsible order summary
    if (isMobile()) {
      var osum = $1('.zt-osum');
      if (osum && once(osum, 'osum')) {
        var toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'zt-osum__toggle';
        var ttl = $1('.zt-osum__title', osum);
        if (ttl) ttl.appendChild(toggle);
        var paint = function () {
          var n = $$('.zt-oitem', osum).length;
          toggle.innerHTML = '<span></span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
          toggle.firstChild.textContent = tpl(I18N.items, { n: fa(n) });
        };
        paint();
        toggle.addEventListener('click', function () { osum.classList.toggle('zt-is-open'); });
        $(document.body).on('updated_checkout', paint);
      }
    }
  });

  /* ==========================================================================
     boot
     ========================================================================== */
  function init(root) {
    root = root || document;
    inits.forEach(function (fn) { try { fn(root); } catch (e) { if (window.console) console.error('[ziteh]', e); } });
  }
  window.ztInit = init;
  paintBadges(D.count || 0, false);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { init(document); });
  else init(document);

  $(window).on('elementor/frontend/init', function () {
    if (window.elementorFrontend && elementorFrontend.hooks) {
      elementorFrontend.hooks.addAction('frontend/element_ready/global', function ($scope) { init($scope[0]); });
    }
  });
})(jQuery);
