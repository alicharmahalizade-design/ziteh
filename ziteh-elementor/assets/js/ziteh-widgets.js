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

		var index = 0;

		function items() {
			return track.children.length;
		}

		function perView() {
			var v = parseInt(getComputedStyle(track).getPropertyValue('--ziteh-per-view'), 10);
			return isNaN(v) || v < 1 ? 1 : v;
		}

		function step() {
			var first = track.children[0];
			if (!first) {
				return 0;
			}
			var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
			return first.getBoundingClientRect().width + gap;
		}

		function maxIndex() {
			return Math.max(0, items() - perView());
		}

		function apply() {
			index = Math.max(0, Math.min(index, maxIndex()));
			var dir = getComputedStyle(track).direction === 'rtl' ? 1 : -1;
			track.style.transform = 'translateX(' + (dir * index * step()) + 'px)';
			if (prev) {
				prev.disabled = index <= 0;
			}
			if (next) {
				next.disabled = index >= maxIndex();
			}
		}

		if (prev) {
			prev.addEventListener('click', function () {
				index -= 1;
				apply();
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				index += 1;
				apply();
			});
		}

		// Re-clamp on resize (per-view is responsive).
		var t;
		window.addEventListener('resize', function () {
			clearTimeout(t);
			t = setTimeout(apply, 150);
		});

		apply();
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
			var slugs = ['ziteh-hero', 'ziteh-categories', 'ziteh-products', 'ziteh-routine', 'ziteh-header'];
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
