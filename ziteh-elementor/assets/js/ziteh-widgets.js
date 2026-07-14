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

	/**
	 * (Re)initialise every widget within a scope.
	 *
	 * @param {ParentNode} [scope=document] Where to look.
	 */
	function initAll(scope) {
		scope = scope || document;
		scope.querySelectorAll('[data-ziteh-hero]').forEach(initHero);
		scope.querySelectorAll('[data-ziteh-slider]').forEach(initSlider);
		scope.querySelectorAll('[data-ziteh-countdown]').forEach(initCountdown);
		scope.querySelectorAll('[data-ziteh-routine]').forEach(initRoutine);
		scope.querySelectorAll('[data-ziteh-burger]').forEach(initBurger);
	}

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
			var slugs = ['ziteh-hero', 'ziteh-categories', 'ziteh-products', 'ziteh-offers', 'ziteh-routine', 'ziteh-header'];
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
