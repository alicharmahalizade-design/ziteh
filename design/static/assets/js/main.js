/* ==========================================================================
   زیته | Ziteh — front-end behaviours
   ========================================================================== */
(function () {
  'use strict';

  const $  = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.prototype.slice.call((r || document).querySelectorAll(s));

  /* -- Persian digits ---------------------------------------------------- */
  const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
  const fa = n => String(n).replace(/\d/g, d => FA[+d]);
  const en = s => String(s).replace(/[۰-۹]/g, d => FA.indexOf(d));
  const money = n => fa(n.toLocaleString('en-US'));

  /* -- quantity steppers ------------------------------------------------- */
  $$('.qty').forEach(box => {
    const out = $('span', box);
    const btns = $$('button', box);
    const sync = v => {
      out.textContent = fa(v);
      box.dataset.value = v;
      box.dispatchEvent(new CustomEvent('qtychange', { bubbles: true, detail: { value: v } }));
    };
    btns.forEach(b => b.addEventListener('click', () => {
      let v = parseInt(en(out.textContent), 10) || 1;
      v += b.dataset.step === '-1' ? -1 : 1;
      if (v < 1) v = 1;
      if (v > 99) v = 99;
      sync(v);
    }));
  });

  /* -- radio-style option cards (shipping / payment / gift sample) ------- */
  $$('[data-radio-group]').forEach(group => {
    const items = $$('[data-radio]', group);
    items.forEach(item => item.addEventListener('click', () => {
      items.forEach(i => i.classList.remove('is-selected'));
      item.classList.add('is-selected');
      const input = $('input', item);
      if (input) input.checked = true;
    }));
  });

  /* -- tabs -------------------------------------------------------------- */
  $$('[data-tabs]').forEach(root => {
    const tabs = $$('[data-tab]', root);
    tabs.forEach(tab => tab.addEventListener('click', () => {
      const id = tab.dataset.tab;
      tabs.forEach(t => t.classList.toggle('is-active', t === tab));
      $$('[data-panel]', root.parentNode).forEach(p =>
        p.hidden = p.dataset.panel !== id);
    }));
  });

  /* -- segmented toggle (صبح / شب) --------------------------------------- */
  $$('[data-toggle]').forEach(root => {
    const opts = $$('button', root);
    opts.forEach(o => o.addEventListener('click', () => {
      opts.forEach(x => x.classList.toggle('is-active', x === o));
      const target = $('#' + root.dataset.toggle);
      if (target) target.dataset.mode = o.dataset.value;
    }));
  });

  /* -- horizontal carousels ---------------------------------------------- */
  $$('[data-carousel]').forEach(root => {
    const track = $('[data-track]', root);
    if (!track) return;
    const step = () => {
      const first = track.firstElementChild;
      if (!first) return 320;
      const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 22;
      return first.getBoundingClientRect().width + gap;
    };
    $$('[data-dir]', root).forEach(btn => btn.addEventListener('click', () => {
      track.scrollBy({ left: (btn.dataset.dir === 'next' ? -1 : 1) * step(), behavior: 'smooth' });
    }));
  });

  /* -- hero slider -------------------------------------------------------- */
  const hero = $('[data-hero]');
  if (hero) {
    const dots = $$('[data-dot]', hero);
    let i = 0;
    const go = n => {
      i = (n + dots.length) % dots.length;
      dots.forEach((d, k) => d.classList.toggle('is-active', k === i));
    };
    $$('[data-hero-dir]', hero).forEach(b =>
      b.addEventListener('click', () => go(i + (b.dataset.heroDir === 'next' ? 1 : -1))));
    dots.forEach((d, k) => d.addEventListener('click', () => go(k)));
  }

  /* -- countdown ---------------------------------------------------------- */
  const cd = $('[data-countdown]');
  if (cd) {
    const cells = {
      d: $('[data-cd="d"]', cd), h: $('[data-cd="h"]', cd),
      m: $('[data-cd="m"]', cd), s: $('[data-cd="s"]', cd)
    };
    let left = (+cd.dataset.countdown) || (2 * 3600 + 39 * 60 + 41);
    const pad = n => fa(String(n).padStart(2, '0'));
    const tick = () => {
      if (left < 0) left = 0;
      cells.d.textContent = pad(Math.floor(left / 86400));
      cells.h.textContent = pad(Math.floor(left / 3600) % 24);
      cells.m.textContent = pad(Math.floor(left / 60) % 60);
      cells.s.textContent = pad(left % 60);
      if (left > 0) left--;
    };
    tick();
    setInterval(tick, 1000);
  }

  /* -- product gallery ----------------------------------------------------- */
  const gallery = $('[data-gallery]');
  if (gallery) {
    const main = $('[data-gallery-main]', gallery);
    const thumbs = $$('[data-gallery-thumb]', gallery);
    let idx = 0;
    const show = n => {
      idx = (n + thumbs.length) % thumbs.length;
      thumbs.forEach((t, k) => t.classList.toggle('is-active', k === idx));
      main.src = thumbs[idx].dataset.full || $('img', thumbs[idx]).src;
    };
    thumbs.forEach((t, k) => t.addEventListener('click', () => show(k)));
    $$('[data-gallery-dir]', gallery).forEach(b =>
      b.addEventListener('click', () => show(idx + (b.dataset.galleryDir === 'next' ? 1 : -1))));
  }

  /* -- accordion ----------------------------------------------------------- */
  $$('[data-accordion]').forEach(acc => {
    const head = $('[data-accordion-head]', acc);
    head && head.addEventListener('click', () => acc.classList.toggle('is-open'));
  });

  /* -- cart line totals ---------------------------------------------------- */
  const cartTable = $('[data-cart]');
  if (cartTable) {
    const recalc = () => {
      let sum = 0;
      $$('[data-line]', cartTable).forEach(line => {
        const price = +line.dataset.price;
        const q = +($('.qty', line) || { dataset: { value: 1 } }).dataset.value || 1;
        const cell = $('[data-line-total]', line);
        if (cell) cell.textContent = money(price * q);
        sum += price * q;
      });
      $$('[data-sum]').forEach(el => el.textContent = money(sum));
      if (window.zitehTiers) window.zitehTiers(sum);
    };
    cartTable.addEventListener('qtychange', recalc);
    $$('[data-remove]', cartTable).forEach(b => b.addEventListener('click', () => {
      const line = b.closest('[data-line]');
      line.style.opacity = '0';
      setTimeout(() => { line.remove(); recalc(); }, 200);
    }));
    $$('.qty', cartTable).forEach(q => q.dataset.value = en($('span', q).textContent));
  }

  /* -- panel dropdown ------------------------------------------------------ */
  $$('[data-menu]').forEach(btn => {
    const menu = btn.nextElementSibling;
    btn.addEventListener('click', e => { e.stopPropagation(); menu.classList.toggle('is-open'); });
  });
  document.addEventListener('click', () => $$('.dropdown.is-open').forEach(m => m.classList.remove('is-open')));

  /* -- reveal on scroll ---------------------------------------------------- */
  const reveals = $$('.reveal');
  if (reveals.length) {
    document.documentElement.classList.add('js-reveal');
    const paint = () => {
      const h = window.innerHeight || 800;
      reveals.forEach(el => {
        if (el.classList.contains('in')) return;
        if (el.getBoundingClientRect().top < h - 40) el.classList.add('in');
      });
    };
    paint();
    window.addEventListener('scroll', paint, { passive: true });
    window.addEventListener('resize', paint);
    window.addEventListener('load', paint);
    setTimeout(() => reveals.forEach(el => el.classList.add('in')), 4000);
  }
})();
