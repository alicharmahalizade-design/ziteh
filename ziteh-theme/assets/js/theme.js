/**
 * Ziteh theme shell behaviour.
 *
 * Only the fallback header needs script, and only for the mobile menu toggle.
 * Everything else the theme renders works without JavaScript, which is the
 * point: a theme should not be able to break a page by failing to load.
 *
 * @package Ziteh_Theme
 */

(function () {
	'use strict';

	function initNavToggle() {
		var button = document.querySelector('[data-ziteh-site-nav-toggle]');
		var nav = document.getElementById('ziteh-site-nav');

		if (!button || !nav) { return; }

		button.addEventListener('click', function () {
			var open = button.getAttribute('aria-expanded') === 'true';
			button.setAttribute('aria-expanded', open ? 'false' : 'true');
			nav.classList.toggle('is-open', !open);
		});

		// Collapse when the layout grows past the breakpoint, otherwise the menu
		// would keep the mobile `is-open` state on a desktop where it is
		// always visible anyway.
		var wide = window.matchMedia('(min-width: 901px)');
		var reset = function () {
			if (wide.matches) {
				button.setAttribute('aria-expanded', 'false');
				nav.classList.remove('is-open');
			}
		};

		if (wide.addEventListener) { wide.addEventListener('change', reset); }
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initNavToggle);
	} else {
		initNavToggle();
	}
})();
