/**
 * Ziteh widgets — front-end interactivity.
 *
 * Three small, dependency-free behaviours:
 *   1. Sliders (categories & products): step the track by one item, RTL-aware.
 *   2. Routine صبح/شب toggle: switch the visible step pane.
 *   3. Mobile header burger: open/close the nav.
 *
 * Everything is delegated / re-initialisable so it also works inside the
 * Elementor editor preview where widgets mount dynamically.
 */
(function () {
	'use strict';

	/**
	 * Wire up a single slider instance.
	 *
	 * @param {HTMLElement} root Element carrying [data-ziteh-slider].
	 */
	function initSlider(root) {
		if (root.dataset.zitehSliderReady === '1') {
			return;
		}
		root.dataset.zitehSliderReady = '1';

		var track = root.querySelector('[data-ziteh-track]');
		var prev = root.querySelector('[data-ziteh-prev]');
		var next = root.querySelector('[data-ziteh-next]');

		if (!track) {
			return;
		}

		// The scroll container is the track's parent (viewport).
		var viewport = track.parentElement;

		function step() {
			var first = track.children[0];
			if (!first) {
				return viewport.clientWidth;
			}
			var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
			return first.getBoundingClientRect().width + gap;
		}

		function isRtl() {
			return getComputedStyle(track).direction === 'rtl';
		}

		// Scroll one item towards later (next) or earlier (prev) items. RTL-safe
		// via native scrollLeft sign handling in modern browsers.
		function scrollByDir(forward) {
			var amount = step() * (forward ? 1 : -1) * (isRtl() ? -1 : 1);
			viewport.scrollBy({ left: amount, behavior: 'smooth' });
		}

		function update() {
			var max = track.scrollWidth - viewport.clientWidth - 1;
			var pos = Math.abs(viewport.scrollLeft);
			if (prev) {
				prev.disabled = pos <= 1;
			}
			if (next) {
				next.disabled = pos >= max;
			}
		}

		if (prev) {
			prev.addEventListener('click', function () {
				scrollByDir(false);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				scrollByDir(true);
			});
		}

		viewport.addEventListener('scroll', function () {
			window.requestAnimationFrame(update);
		});

		var t;
		window.addEventListener('resize', function () {
			clearTimeout(t);
			t = setTimeout(update, 150);
		});

		update();
	}

	/**
	 * Wire up a hero image slider (fade slides, autoplay, arrows, dots).
	 *
	 * @param {HTMLElement} root Element carrying [data-ziteh-hero].
	 */
	function initHero(root) {
		if (root.dataset.zitehHeroReady === '1') {
			return;
		}
		root.dataset.zitehHeroReady = '1';

		var slides = Array.prototype.slice.call(root.querySelectorAll('.ziteh-hero-slide'));
		var dots = Array.prototype.slice.call(root.querySelectorAll('[data-ziteh-hero-dot]'));
		var prev = root.querySelector('[data-ziteh-hero-prev]');
		var next = root.querySelector('[data-ziteh-hero-next]');
		var delay = parseInt(root.getAttribute('data-autoplay'), 10) || 0;

		if (slides.length <= 1) {
			return;
		}

		var current = Math.max(0, slides.findIndex(function (s) {
			return s.classList.contains('is-active');
		}));
		var timer = null;

		function show(n) {
			current = (n + slides.length) % slides.length;
			slides.forEach(function (s, i) {
				s.classList.toggle('is-active', i === current);
			});
			dots.forEach(function (d, i) {
				d.classList.toggle('is-active', i === current);
			});
		}

		function nextSlide() {
			show(current + 1);
		}

		function prevSlide() {
			show(current - 1);
		}

		function start() {
			if (delay > 0) {
				stop();
				timer = setInterval(nextSlide, delay);
			}
		}

		function stop() {
			if (timer) {
				clearInterval(timer);
				timer = null;
			}
		}

		if (next) {
			next.addEventListener('click', function () {
				nextSlide();
				start();
			});
		}
		if (prev) {
			prev.addEventListener('click', function () {
				prevSlide();
				start();
			});
		}
		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				show(parseInt(dot.getAttribute('data-ziteh-hero-dot'), 10) || 0);
				start();
			});
		});

		// Pause on hover for usability.
		root.addEventListener('mouseenter', stop);
		root.addEventListener('mouseleave', start);

		show(current);
		start();
	}

	/**
	 * Wire up a routine day/night toggle.
	 *
	 * @param {HTMLElement} toggle Element carrying [data-ziteh-routine].
	 */
	function initRoutine(toggle) {
		if (toggle.dataset.zitehRoutineReady === '1') {
			return;
		}
		toggle.dataset.zitehRoutineReady = '1';

		var section = toggle.closest('.ziteh-routine');
		if (!section) {
			return;
		}
		var buttons = toggle.querySelectorAll('[data-ziteh-tab]');
		var panes = section.querySelectorAll('[data-ziteh-pane]');

		buttons.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var mode = btn.getAttribute('data-ziteh-tab');

				buttons.forEach(function (b) {
					b.classList.toggle('is-active', b === btn);
				});
				panes.forEach(function (pane) {
					pane.classList.toggle('is-active', pane.getAttribute('data-ziteh-pane') === mode);
				});
			});
		});
	}

	/**
	 * Wire up a countdown timer.
	 *
	 * @param {HTMLElement} el Element carrying [data-ziteh-countdown] with a
	 *                        data-end ISO datetime (empty → end of today).
	 */
	function initCountdown(el) {
		if (el.dataset.zitehCountdownReady === '1') {
			return;
		}
		el.dataset.zitehCountdownReady = '1';

		var nums = {
			d: el.querySelector('[data-cd="d"]'),
			h: el.querySelector('[data-cd="h"]'),
			m: el.querySelector('[data-cd="m"]'),
			s: el.querySelector('[data-cd="s"]')
		};
		var section = el.closest('.ziteh-offers');

		function endTime() {
			var raw = (el.getAttribute('data-end') || '').trim();
			if (raw) {
				var t = new Date(raw.replace(' ', 'T')).getTime();
				if (!isNaN(t)) {
					return t;
				}
			}
			// Fallback: next local midnight.
			var d = new Date();
			d.setHours(24, 0, 0, 0);
			return d.getTime();
		}

		var target = endTime();

		// Persian digits, zero-padded to 2.
		function fa(n) {
			var s = (n < 10 ? '0' : '') + n;
			return s.replace(/[0-9]/g, function (ch) {
				return '۰۱۲۳۴۵۶۷۸۹'.charAt(+ch);
			});
		}

		function set(el2, n) {
			if (el2) {
				el2.textContent = fa(n);
			}
		}

		var timer;

		function tick() {
			var diff = target - Date.now();
			if (diff <= 0) {
				set(nums.d, 0); set(nums.h, 0); set(nums.m, 0); set(nums.s, 0);
				if (section) {
					section.classList.add('is-ended');
				}
				clearInterval(timer);
				return;
			}
			var totalSec = Math.floor(diff / 1000);
			set(nums.d, Math.floor(totalSec / 86400));
			set(nums.h, Math.floor((totalSec % 86400) / 3600));
			set(nums.m, Math.floor((totalSec % 3600) / 60));
			set(nums.s, totalSec % 60);
		}

		tick();
		timer = setInterval(tick, 1000);
	}

	/**
	 * Wire up the mobile header burger.
	 *
	 * @param {HTMLElement} burger Element carrying [data-ziteh-burger].
	 */
	function initBurger(burger) {
		if (burger.dataset.zitehBurgerReady === '1') {
			return;
		}
		burger.dataset.zitehBurgerReady = '1';

		var header = burger.closest('.ziteh-header');
		if (!header) {
			return;
		}
		var menu = header.querySelector('.ziteh-header__menu');
		if (!menu) {
			return;
		}
		burger.addEventListener('click', function () {
			menu.classList.toggle('is-open');
		});
	}

	/* ===================== v2: shell (drawer / modal / search) ============== */

	var DATA = window.zitehData || {};

	/** Show the shared dimming overlay. */
	function showOverlay() {
		var o = document.querySelector('[data-ziteh-overlay]');
		if (o) {
			o.hidden = false;
			requestAnimationFrame(function () { o.classList.add('is-visible'); });
		}
		document.body.classList.add('ziteh-noscroll');
	}

	/** Hide the overlay and every shell panel. */
	function closeShell() {
		var o = document.querySelector('[data-ziteh-overlay]');
		if (o) {
			o.classList.remove('is-visible');
			setTimeout(function () { o.hidden = true; }, 300);
		}
		document.querySelectorAll('[data-ziteh-drawer],[data-ziteh-modal],[data-ziteh-search-overlay]').forEach(function (el) {
			el.classList.remove('is-open');
			el.setAttribute('aria-hidden', 'true');
		});
		document.body.classList.remove('ziteh-noscroll');
	}

	function openPanel(sel) {
		var el = document.querySelector(sel);
		if (!el) { return null; }
		showOverlay();
		el.classList.add('is-open');
		el.setAttribute('aria-hidden', 'false');
		return el;
	}

	/** Cart drawer: open on the cart link + on WooCommerce's added_to_cart. */
	function initDrawer() {
		if (document.body.dataset.zitehDrawer === '1') { return; }
		document.body.dataset.zitehDrawer = '1';

		document.addEventListener('click', function (e) {
			var open = e.target.closest('[data-ziteh-cart-open]');
			if (open && document.querySelector('[data-ziteh-drawer]')) {
				e.preventDefault();
				openPanel('[data-ziteh-drawer]');
			}
			if (e.target.closest('[data-ziteh-drawer-close]')) {
				closeShell();
			}
		});

		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', function () {
				openPanel('[data-ziteh-drawer]');
			});
		}
	}

	/** Quick View modal. */
	function initQuickView() {
		if (document.body.dataset.zitehQv === '1') { return; }
		document.body.dataset.zitehQv = '1';

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-ziteh-quickview]');
			if (!btn) {
				if (e.target.closest('[data-ziteh-modal-close]')) { closeShell(); }
				return;
			}
			e.preventDefault();
			var id = btn.getAttribute('data-ziteh-quickview');
			var modal = openPanel('[data-ziteh-modal]');
			if (!modal || !DATA.ajaxUrl) { return; }

			var body = modal.querySelector('[data-ziteh-modal-body]');
			var loader = modal.querySelector('[data-ziteh-modal-loader]');
			if (loader) { loader.hidden = true; }
			if (body) { body.innerHTML = quickViewSkeleton(); }

			var url = DATA.ajaxUrl + '?action=ziteh_quickview&id=' + encodeURIComponent(id) +
				'&nonce=' + encodeURIComponent(DATA.nonce || '');
			fetch(url, { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res && res.success && body) {
						body.innerHTML = res.data.html;
						if (window.jQuery && window.jQuery.fn) {
							window.jQuery(document.body).trigger('wc_fragment_refresh');
						}
					} else if (body) {
						body.innerHTML = '<p class="ziteh-modal__error">' + (DATA.i18n && DATA.i18n.error || 'Error') + '</p>';
					}
				})
				.catch(function () {
					if (body) { body.innerHTML = '<p class="ziteh-modal__error">' + (DATA.i18n && DATA.i18n.error || 'Error') + '</p>'; }
				});
		});
	}

	/** Live AJAX search overlay. */
	function initSearch() {
		if (document.body.dataset.zitehSearch === '1') { return; }
		document.body.dataset.zitehSearch = '1';

		var timer;

		document.addEventListener('click', function (e) {
			if (e.target.closest('[data-ziteh-search-open]')) {
				e.preventDefault();
				var ov = openPanel('[data-ziteh-search-overlay]');
				if (ov) {
					var input = ov.querySelector('[data-ziteh-search-input]');
					if (input) { setTimeout(function () { input.focus(); }, 60); }
				}
			}
			if (e.target.closest('[data-ziteh-search-close]')) { closeShell(); }
		});

		document.addEventListener('input', function (e) {
			var input = e.target.closest('[data-ziteh-search-input]');
			if (!input) { return; }
			var results = document.querySelector('[data-ziteh-search-results]');
			var q = input.value.trim();
			clearTimeout(timer);
			if (q.length < 2) { if (results) { results.innerHTML = ''; } return; }
			if (results) { results.innerHTML = searchSkeleton(); }
			timer = setTimeout(function () {
				if (!DATA.ajaxUrl) { return; }
				var url = DATA.ajaxUrl + '?action=ziteh_search&q=' + encodeURIComponent(q) +
					'&nonce=' + encodeURIComponent(DATA.nonce || '');
				fetch(url, { credentials: 'same-origin' })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						if (results) { results.innerHTML = (res && res.success) ? res.data.html : ''; }
					})
					.catch(function () { if (results) { results.innerHTML = ''; } });
			}, 280);
		});
	}

	/** Wishlist toggle backed by localStorage. */
	function initWishlist() {
		if (document.body.dataset.zitehWish === '1') { return; }
		document.body.dataset.zitehWish = '1';

		var KEY = 'ziteh_wishlist';
		function read() {
			try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
		}
		function write(list) {
			try { localStorage.setItem(KEY, JSON.stringify(list)); } catch (e) {}
		}
		function mark() {
			var list = read();
			document.querySelectorAll('[data-ziteh-wish]').forEach(function (b) {
				b.classList.toggle('is-active', list.indexOf(b.getAttribute('data-ziteh-wish')) !== -1);
			});
		}
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-ziteh-wish]');
			if (!btn) { return; }
			e.preventDefault();
			var id = btn.getAttribute('data-ziteh-wish');
			var list = read();
			var i = list.indexOf(id);
			if (i === -1) { list.push(id); } else { list.splice(i, 1); }
			write(list);
			mark();
			btn.classList.add('is-pulse');
			setTimeout(function () { btn.classList.remove('is-pulse'); }, 300);
		});
		mark();
	}

	/** Sticky, shrinking header on scroll. */
	function initStickyHeader() {
		var header = document.querySelector('.ziteh-header');
		if (!header || header.dataset.zitehSticky === '1') { return; }
		header.dataset.zitehSticky = '1';
		header.classList.add('ziteh-header--sticky');

		var last = 0;
		function onScroll() {
			var y = window.pageYOffset || document.documentElement.scrollTop;
			header.classList.toggle('is-scrolled', y > 40);
			last = y;
		}
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/** Scroll-reveal animations across the main sections. */
	function initReveal() {
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var selectors = [
			'.ziteh-features', '.ziteh-story', '.ziteh-cats', '.ziteh-routine',
			'.ziteh-products', '.ziteh-offers', '.ziteh-quiz', '.ziteh-brands',
			'.ziteh-blog', '.ziteh-cta', '.ziteh-testimonials', '.ziteh-insta',
			'.ziteh-newsletter'
		];
		var nodes = document.querySelectorAll(selectors.join(','));
		nodes.forEach(function (n) { n.classList.add('ziteh-reveal'); });

		if (reduce || !('IntersectionObserver' in window)) {
			nodes.forEach(function (n) { n.classList.add('is-visible'); });
			return;
		}
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (en) {
				if (en.isIntersecting) {
					en.target.classList.add('is-visible');
					io.unobserve(en.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
		nodes.forEach(function (n) {
			if (!n.classList.contains('is-visible')) { io.observe(n); }
		});
	}

	/** Multi-step skin quiz. */
	function initQuiz(root) {
		if (root.dataset.zitehQuizReady === '1') { return; }
		root.dataset.zitehQuizReady = '1';

		var screens = {};
		root.querySelectorAll('[data-quiz-screen]').forEach(function (s) {
			screens[s.getAttribute('data-quiz-screen')] = s;
		});
		var steps = Array.prototype.slice.call(root.querySelectorAll('[data-quiz-step]'));
		var total = steps.length;
		var bar = root.querySelector('[data-quiz-progress]');
		var current = root.querySelector('[data-quiz-current]');
		var summary = root.querySelector('[data-quiz-summary]');
		var idx = 0;
		var answers = [];
		var cats = [];

		function show(name) {
			Object.keys(screens).forEach(function (k) {
				screens[k].classList.toggle('is-active', k === name);
			});
		}
		function showStep(n) {
			idx = n;
			steps.forEach(function (s, i) { s.classList.toggle('is-active', i === n); });
			if (bar) { bar.style.width = ((n) / total * 100) + '%'; }
			if (current) { current.textContent = toFa(n + 1); }
			steps.forEach(function (s) {
				var back = s.querySelector('[data-quiz-back]');
				if (back) { back.hidden = (n === 0); }
			});
		}
		function toFa(n) {
			return String(n).replace(/[0-9]/g, function (c) { return '۰۱۲۳۴۵۶۷۸۹'.charAt(+c); });
		}
		function loadProducts() {
			var wrap = root.querySelector('[data-quiz-products-wrap]');
			var grid = root.querySelector('[data-quiz-products]');
			if (!wrap || !grid || !DATA.ajaxUrl || DATA.hasWc === false) { return; }

			var chosen = cats.filter(function (c) { return c; });
			var count = root.getAttribute('data-quiz-count') || '4';
			var fallback = root.getAttribute('data-quiz-fallback') || '';

			wrap.hidden = false;
			grid.innerHTML = skeletonCards(parseInt(count, 10) || 4);

			var url = DATA.ajaxUrl + '?action=ziteh_quiz_products' +
				'&cats=' + encodeURIComponent(chosen.join(',')) +
				'&fallback=' + encodeURIComponent(fallback) +
				'&count=' + encodeURIComponent(count) +
				'&nonce=' + encodeURIComponent(DATA.nonce || '');
			fetch(url, { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res && res.success && res.data.html.trim()) {
						grid.innerHTML = res.data.html;
						initWishlist();
					} else {
						wrap.hidden = true;
					}
				})
				.catch(function () { wrap.hidden = true; });
		}

		function finish() {
			if (bar) { bar.style.width = '100%'; }
			if (summary) {
				summary.innerHTML = '';
				answers.forEach(function (a) {
					var li = document.createElement('li');
					li.textContent = a;
					summary.appendChild(li);
				});
			}
			show('result');
			loadProducts();
		}

		root.addEventListener('click', function (e) {
			if (e.target.closest('[data-quiz-start]')) {
				answers = []; cats = []; showStep(0); show('questions'); return;
			}
			var ans = e.target.closest('[data-quiz-answer]');
			if (ans) {
				answers[idx] = ans.textContent.trim();
				cats[idx] = ans.getAttribute('data-cat') || '';
				var stepEl = ans.closest('[data-quiz-step]');
				stepEl.querySelectorAll('[data-quiz-answer]').forEach(function (b) { b.classList.remove('is-picked'); });
				ans.classList.add('is-picked');
				setTimeout(function () {
					if (idx + 1 < total) { showStep(idx + 1); } else { finish(); }
				}, 220);
				return;
			}
			if (e.target.closest('[data-quiz-back]')) {
				if (idx > 0) { showStep(idx - 1); }
				return;
			}
			if (e.target.closest('[data-quiz-restart]')) {
				answers = []; cats = [];
				var wrap = root.querySelector('[data-quiz-products-wrap]');
				if (wrap) { wrap.hidden = true; }
				show('intro'); return;
			}
		});
	}

	/** Quick View skeleton (image block + text lines). */
	function quickViewSkeleton() {
		return '<div class="ziteh-qv ziteh-qv--skel">' +
			'<div class="ziteh-skel ziteh-skel--qv-media"></div>' +
			'<div class="ziteh-qv__info">' +
			'<div class="ziteh-skel ziteh-skel--line ziteh-skel--title"></div>' +
			'<div class="ziteh-skel ziteh-skel--line ziteh-skel--short"></div>' +
			'<div class="ziteh-skel ziteh-skel--block"></div>' +
			'<div class="ziteh-skel ziteh-skel--line"></div>' +
			'<div class="ziteh-skel ziteh-skel--line ziteh-skel--short"></div>' +
			'</div></div>';
	}

	/** Search results skeleton rows. */
	function searchSkeleton() {
		var row = '<div class="ziteh-search-skel">' +
			'<span class="ziteh-skel ziteh-skel--sq"></span>' +
			'<span class="ziteh-skel ziteh-skel--line"></span>' +
			'</div>';
		return row + row + row;
	}

	/** Build N skeleton product-card placeholders. */
	function skeletonCards(n) {
		var one = '<div class="ziteh-skel-card">' +
			'<div class="ziteh-skel ziteh-skel--thumb"></div>' +
			'<div class="ziteh-skel ziteh-skel--line"></div>' +
			'<div class="ziteh-skel ziteh-skel--line ziteh-skel--short"></div>' +
			'</div>';
		var out = '';
		for (var i = 0; i < n; i++) { out += one; }
		return out;
	}

	/** Quick View gallery thumbnails + quantity stepper (delegated). */
	function initQvControls() {
		if (document.body.dataset.zitehQv2 === '1') { return; }
		document.body.dataset.zitehQv2 = '1';

		document.addEventListener('click', function (e) {
			var thumb = e.target.closest('[data-qv-thumb]');
			if (thumb) {
				var gallery = thumb.closest('[data-ziteh-qv-gallery]');
				var n = thumb.getAttribute('data-qv-thumb');
				gallery.querySelectorAll('[data-qv-slide]').forEach(function (s) {
					s.classList.toggle('is-active', s.getAttribute('data-qv-slide') === n);
				});
				gallery.querySelectorAll('[data-qv-thumb]').forEach(function (t) {
					t.classList.toggle('is-active', t === thumb);
				});
				return;
			}
			var minus = e.target.closest('[data-qty-minus]');
			var plus = e.target.closest('[data-qty-plus]');
			if (minus || plus) {
				var box = (minus || plus).closest('[data-ziteh-qty]');
				var input = box.querySelector('[data-qty-input]');
				var val = parseInt(input.value, 10) || 1;
				val = plus ? val + 1 : Math.max(1, val - 1);
				input.value = val;
				syncQty(box, val);
			}
		});

		document.addEventListener('input', function (e) {
			var input = e.target.closest('[data-qty-input]');
			if (!input) { return; }
			var box = input.closest('[data-ziteh-qty]');
			var val = Math.max(1, parseInt(input.value, 10) || 1);
			syncQty(box, val);
		});

		function syncQty(box, val) {
			var actions = box.closest('.ziteh-qv__actions');
			if (!actions) { return; }
			var addBtn = actions.querySelector('.add_to_cart_button, [data-product_id]');
			if (addBtn) { addBtn.setAttribute('data-quantity', val); }
		}
	}

	/**
	 * (Re)initialise every widget within a scope.
	 *
	 * @param {ParentNode} [scope=document] Where to look.
	 */
	/**
	 * Run a function without ever letting it throw out — a failure in one Ziteh
	 * feature must never break the page or a host theme's own scripts.
	 *
	 * @param {Function} fn Callback.
	 * @param {*}        [arg] Optional argument.
	 */
	function safe(fn, arg) {
		try { fn(arg); } catch (e) { if (window.console) { console.warn('[ziteh]', e); } }
	}

	function initAll(scope) {
		scope = scope || document;
		safe(function () { scope.querySelectorAll('[data-ziteh-hero]').forEach(function (n) { safe(initHero, n); }); });
		safe(function () { scope.querySelectorAll('[data-ziteh-slider]').forEach(function (n) { safe(initSlider, n); }); });
		safe(function () { scope.querySelectorAll('[data-ziteh-countdown]').forEach(function (n) { safe(initCountdown, n); }); });
		safe(function () { scope.querySelectorAll('[data-ziteh-routine]').forEach(function (n) { safe(initRoutine, n); }); });
		safe(function () { scope.querySelectorAll('[data-ziteh-burger]').forEach(function (n) { safe(initBurger, n); }); });
		safe(function () { scope.querySelectorAll('[data-ziteh-quiz]').forEach(function (n) { safe(initQuiz, n); }); });

		// Document-level features (run once).
		safe(initDrawer);
		safe(initQuickView);
		safe(initQvControls);
		safe(initSearch);
		safe(initWishlist);
		safe(initStickyHeader);
		safe(initReveal);
	}

	// Global: overlay click + Esc close the shell.
	document.addEventListener('click', function (e) {
		if (e.target.closest('[data-ziteh-overlay]')) { closeShell(); }
	});
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') { closeShell(); }
	});

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			initAll();
		});
	} else {
		initAll();
	}

	// Elementor editor: re-init when a widget is (re)rendered in the preview.
	if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', function () {
			if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
				return;
			}
			var slugs = ['ziteh-hero', 'ziteh-categories', 'ziteh-products', 'ziteh-offers', 'ziteh-routine', 'ziteh-header', 'ziteh-quiz', 'ziteh-features'];
			slugs.forEach(function (slug) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/' + slug + '.default',
					function ($scope) {
						initAll($scope[0]);
					}
				);
			});
		});
	}
})();
