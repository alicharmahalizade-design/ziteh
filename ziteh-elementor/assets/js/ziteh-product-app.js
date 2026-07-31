/**
 * Ziteh product page — app-like behaviours.
 *
 * A self-contained enhancement layer for the five product widgets. It never
 * owns state that the page needs to work: the gallery, tabs and cart form are
 * all functional without it, and every interaction here either scrolls
 * something or forwards a click to a control that already existed. That keeps
 * the WooCommerce add-to-cart path (variations, stock, third-party filters)
 * completely untouched.
 *
 * Everything is scoped to the touch layout via one media query, so the desktop
 * composition keeps the behaviour it already had.
 *
 * @package Ziteh_Elementor
 */

(function () {
	'use strict';

	// The plugin normally adds this class through body_class(). A few themes
	// and page builders print their own <body> tag without calling it, so this
	// script — which only loads when the feature is on — makes sure the class
	// is there. The base layout never depends on it; only the enhancements do.
	if (document.body && !document.body.classList.contains('ziteh-app')) {
		document.body.classList.add('ziteh-app');
	}

	var TOUCH = window.matchMedia('(max-width: 767px)');
	var REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');

	function on(el, type, fn, opts) {
		if (el) { el.addEventListener(type, fn, opts); }
	}

	function scrollBehavior() {
		return REDUCED.matches ? 'auto' : 'smooth';
	}

	/** Index of the slide currently filling a horizontal snap track. */
	function activeIndex(track) {
		var slides = track.children;
		if (!slides.length) { return 0; }
		// abs() because RTL scrollLeft is negative in every current engine.
		var pos = Math.abs(track.scrollLeft);
		var width = slides[0].getBoundingClientRect().width;
		return width ? Math.min(slides.length - 1, Math.round(pos / width)) : 0;
	}

	function scrollToIndex(track, index) {
		var slide = track.children[index];
		if (!slide) { return; }
		var width = slide.getBoundingClientRect().width;
		var dir = getComputedStyle(track).direction === 'rtl' ? -1 : 1;
		track.scrollTo({ left: dir * width * index, behavior: scrollBehavior() });
	}

	/** Run fn once per animation frame at most. */
	function raf(fn) {
		var queued = false;
		return function () {
			if (queued) { return; }
			queued = true;
			requestAnimationFrame(function () { queued = false; fn(); });
		};
	}

	/* ---------------------------------------------------------------------
	 * Swipeable gallery
	 *
	 * The CSS turns .ziteh-sp__track into a snap container below 768px. This
	 * only keeps the dots, thumbnails and slide flags in sync with wherever
	 * the finger left it, and makes the existing arrow/thumb buttons scroll
	 * the track instead of cross-fading.
	 * ------------------------------------------------------------------- */
	function initGallery(root) {
		var track = root.querySelector('[data-ziteh-sp-track]');
		if (!track || track.dataset.zitehAppReady === '1') { return; }
		track.dataset.zitehAppReady = '1';

		var dots = Array.prototype.slice.call(root.querySelectorAll('[data-ziteh-sp-dot]'));
		var thumbs = Array.prototype.slice.call(root.querySelectorAll('[data-ziteh-sp-thumb]'));
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-ziteh-sp-slide]'));

		function sync() {
			if (!TOUCH.matches) { return; }
			var index = activeIndex(track);
			dots.forEach(function (dot, i) { dot.classList.toggle('is-active', i === index); });
			thumbs.forEach(function (thumb, i) {
				thumb.classList.toggle('is-active', i === index);
				thumb.setAttribute('aria-selected', i === index ? 'true' : 'false');
			});
			slides.forEach(function (slide, i) {
				slide.classList.toggle('is-active', i === index);
				slide.setAttribute('aria-hidden', i === index ? 'false' : 'true');
			});
			root.dataset.zitehSpIndex = String(index);
		}

		on(track, 'scroll', raf(sync), { passive: true });

		// Capture phase: scroll the track, then let the existing desktop
		// handler run too. On touch its class toggling is a no-op because the
		// CSS shows every slide, and on desktop this branch does not fire.
		root.addEventListener('click', function (event) {
			if (!TOUCH.matches) { return; }

			var dot = event.target.closest('[data-ziteh-sp-dot]');
			if (dot) {
				scrollToIndex(track, parseInt(dot.getAttribute('data-ziteh-sp-dot'), 10) || 0);
				return;
			}

			var thumb = event.target.closest('[data-ziteh-sp-thumb]');
			if (thumb) {
				scrollToIndex(track, parseInt(thumb.getAttribute('data-ziteh-sp-thumb'), 10) || 0);
				return;
			}

			var back = event.target.closest('[data-ziteh-sp-prev]');
			var fwd = event.target.closest('[data-ziteh-sp-next]');
			if (back || fwd) {
				var count = track.children.length;
				var next = (activeIndex(track) + (fwd ? 1 : -1) + count) % count;
				scrollToIndex(track, next);
			}
		}, true);

		sync();
	}

	/* ---------------------------------------------------------------------
	 * Fullscreen image viewer
	 * ------------------------------------------------------------------- */
	function initLightbox(root) {
		var box = root.querySelector('[data-ziteh-sp-lightbox]');
		if (!box || box.dataset.zitehAppReady === '1') { return; }
		box.dataset.zitehAppReady = '1';

		var track = box.querySelector('[data-ziteh-sp-lightbox-track]');
		var label = box.querySelector('[data-ziteh-sp-lightbox-index]');
		var closeBtn = box.querySelector('[data-ziteh-sp-lightbox-close]');
		var galleryTrack = root.querySelector('[data-ziteh-sp-track]');
		var lastFocus = null;

		function setLabel() {
			if (!label) { return; }
			// Match the digits PHP rendered for the total, which went through
			// number_format_i18n and so follows the site locale.
			var locale = document.documentElement.lang || undefined;
			label.textContent = (activeIndex(track) + 1).toLocaleString(locale);
		}

		function open(index) {
			lastFocus = document.activeElement;
			box.hidden = false;
			// Force a layout pass so the opening transition actually runs.
			void box.offsetWidth;
			box.classList.add('is-open');
			document.documentElement.classList.add('ziteh-sp-lock');

			// Jump without animating: the viewer should appear already on the
			// image the shopper was looking at.
			var slide = track.children[index];
			if (slide) {
				var dir = getComputedStyle(track).direction === 'rtl' ? -1 : 1;
				track.scrollTo({ left: dir * slide.getBoundingClientRect().width * index, behavior: 'auto' });
			}
			setLabel();
			if (closeBtn) { closeBtn.focus(); }
		}

		function close() {
			box.classList.remove('is-open');
			document.documentElement.classList.remove('ziteh-sp-lock');
			var done = function () { box.hidden = true; };
			if (REDUCED.matches) { done(); } else { setTimeout(done, 200); }
			if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
		}

		on(track, 'scroll', raf(setLabel), { passive: true });
		on(closeBtn, 'click', close);

		// Tapping the backdrop (not an image) closes, as it would in an app.
		on(box, 'click', function (event) {
			if (event.target === box || event.target === track || event.target.closest('.ziteh-sp__lightbox-slide') === event.target) {
				close();
			}
		});

		on(root, 'click', function (event) {
			var trigger = event.target.closest('[data-ziteh-sp-zoom]');
			if (trigger) {
				event.preventDefault();
				open(galleryTrack ? activeIndex(galleryTrack) : 0);
				return;
			}
			// On touch, tapping the image itself opens the viewer.
			if (TOUCH.matches && event.target.closest('[data-ziteh-sp-slide]')) {
				open(galleryTrack ? activeIndex(galleryTrack) : 0);
			}
		});

		on(document, 'keydown', function (event) {
			if (box.hidden) { return; }
			if (event.key === 'Escape') {
				close();
				return;
			}
			if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
				event.preventDefault();
				var rtl = getComputedStyle(track).direction === 'rtl';
				var forward = rtl ? event.key === 'ArrowLeft' : event.key === 'ArrowRight';
				var count = track.children.length;
				scrollToIndex(track, (activeIndex(track) + (forward ? 1 : -1) + count) % count);
				return;
			}
			// Minimal focus trap: the viewer only has one focusable control.
			if (event.key === 'Tab' && closeBtn) {
				event.preventDefault();
				closeBtn.focus();
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Sticky action bar
	 * ------------------------------------------------------------------- */
	function initAppBar(root) {
		var bar = root.querySelector('[data-ziteh-sp-appbar]');
		var realButton = root.querySelector('.ziteh-sp__buy .single_add_to_cart_button');
		if (!bar || bar.dataset.zitehAppReady === '1') { return; }
		bar.dataset.zitehAppReady = '1';

		var cta = bar.querySelector('[data-ziteh-sp-appbar-cta]');
		var anchor = root.querySelector('.ziteh-sp__buy') || realButton;

		// Forward to the genuine submit button so nothing about the cart flow
		// changes; if the theme replaced it, fall back to submitting the form.
		on(cta, 'click', function () {
			var button = root.querySelector('.ziteh-sp__buy .single_add_to_cart_button');
			if (button) {
				button.click();
				return;
			}
			var form = root.querySelector('.ziteh-sp__buy form.cart');
			if (form) { form.requestSubmit ? form.requestSubmit() : form.submit(); }
		});

		function show(visible) {
			if (visible === (bar.dataset.visible === '1')) { return; }
			bar.dataset.visible = visible ? '1' : '0';
			if (visible) {
				bar.hidden = false;
				void bar.offsetWidth;
				bar.classList.add('is-visible');
				document.body.classList.add('ziteh-sp-has-appbar');
			} else {
				bar.classList.remove('is-visible');
				document.body.classList.remove('ziteh-sp-has-appbar');
				var done = function () { if (bar.dataset.visible === '0') { bar.hidden = true; } };
				if (REDUCED.matches) { done(); } else { setTimeout(done, 240); }
			}
		}

		if (!anchor || !('IntersectionObserver' in window)) { return; }

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				// Only ever on the touch layout, and only once the shopper has
				// actually scrolled past the inline buy control.
				show(TOUCH.matches && !entry.isIntersecting && entry.boundingClientRect.top < 0);
			});
		}, { threshold: 0 });

		observer.observe(anchor);

		on(TOUCH, 'change', function () {
			if (!TOUCH.matches) { show(false); }
		});
	}

	/* ---------------------------------------------------------------------
	 * Disclosure (shipping row)
	 * ------------------------------------------------------------------- */
	function initDisclosure(root) {
		root.querySelectorAll('[data-ziteh-sp-disclosure]').forEach(function (button) {
			if (button.dataset.zitehAppReady === '1') { return; }
			button.dataset.zitehAppReady = '1';

			var panel = document.getElementById(button.getAttribute('aria-controls'));
			if (!panel) { return; }

			on(button, 'click', function () {
				var open = button.getAttribute('aria-expanded') === 'true';
				button.setAttribute('aria-expanded', open ? 'false' : 'true');
				button.closest('.ziteh-sp__shipping').classList.toggle('is-open', !open);
				panel.hidden = open;
			});
		});
	}

	/* ---------------------------------------------------------------------
	 * Segmented tab indicator
	 *
	 * The pill that slides under the active tab is positioned from JS because
	 * the tabs are content-sized; CSS alone cannot know their widths.
	 * ------------------------------------------------------------------- */
	function initSegmented(root) {
		var tabs = root.querySelector('.ziteh-pd__tabs');
		if (!tabs || tabs.dataset.zitehAppReady === '1') { return; }
		tabs.dataset.zitehAppReady = '1';

		function place() {
			var active = tabs.querySelector('.ziteh-pd__tab.is-active');
			if (!active) { return; }
			var box = tabs.getBoundingClientRect();
			var target = active.getBoundingClientRect();
			tabs.style.setProperty('--ziteh-pd-pill-w', target.width + 'px');
			// Offset from the inline start, which is the right edge in RTL.
			var start = getComputedStyle(tabs).direction === 'rtl'
				? box.right - target.right
				: target.left - box.left;
			tabs.style.setProperty('--ziteh-pd-pill-x', start + 'px');

			if (TOUCH.matches) {
				active.scrollIntoView({ inline: 'center', block: 'nearest', behavior: scrollBehavior() });
			}
		}

		on(tabs, 'click', function (event) {
			if (event.target.closest('.ziteh-pd__tab')) { requestAnimationFrame(place); }
		});
		on(window, 'resize', raf(place));
		if (document.fonts && document.fonts.ready) { document.fonts.ready.then(place); }
		place();
	}

	/* ---------------------------------------------------------------------
	 * Entrance reveals
	 *
	 * Browsers with scroll-driven animations get them from CSS alone. This is
	 * the fallback for everything else, and it is skipped entirely when the
	 * visitor asked for reduced motion.
	 * ------------------------------------------------------------------- */
	var REVEAL_SELECTOR = '.ziteh-pd__body, .ziteh-rp__shell, .ziteh-pr__layout, .ziteh-ss__inner';

	function initReveal(scope) {
		if (REDUCED.matches || !('IntersectionObserver' in window)) { return; }
		if (window.CSS && CSS.supports && CSS.supports('animation-timeline: view()')) { return; }

		var targets = scope.querySelectorAll(REVEAL_SELECTOR);
		if (!targets.length) { return; }

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) { return; }
				entry.target.classList.add('is-revealed');
				observer.unobserve(entry.target);
			});
		}, { rootMargin: '0px 0px -8% 0px' });

		targets.forEach(function (el) {
			if (el.dataset.zitehRevealReady === '1') { return; }
			el.dataset.zitehRevealReady = '1';
			el.classList.add('ziteh-reveal-js');
			observer.observe(el);
		});
	}

	function init(scope) {
		var context = scope || document;

		context.querySelectorAll('[data-ziteh-single-product]').forEach(function (root) {
			try {
				initGallery(root);
				initLightbox(root);
				initAppBar(root);
				initDisclosure(root);
			} catch (error) {
				// One broken widget must never take the rest of the page down.
				if (window.console) { window.console.warn('ziteh product app:', error); }
			}
		});

		context.querySelectorAll('[data-ziteh-product-details]').forEach(function (root) {
			try {
				initSegmented(root);
			} catch (error) {
				if (window.console) { window.console.warn('ziteh product app:', error); }
			}
		});

		try {
			initReveal(context === document ? document.body : context);
		} catch (error) {
			if (window.console) { window.console.warn('ziteh product app:', error); }
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(); });
	} else {
		init();
	}

	// Elementor editor: re-run when a widget is re-rendered in the preview.
	if (window.jQuery) {
		window.jQuery(window).on('elementor/frontend/init', function () {
			if (!window.elementorFrontend || !window.elementorFrontend.hooks) { return; }
			['ziteh-single-product', 'ziteh-product-details'].forEach(function (slug) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/' + slug + '.default',
					function ($scope) { init($scope[0]); }
				);
			});
		});
	}
})();
