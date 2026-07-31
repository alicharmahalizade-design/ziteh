<?php
/**
 * Shared base class for all Ziteh widgets.
 *
 * Centralises the Elementor category, dependent style/script handles and a few
 * small helpers used across widgets (icon markup, safe URLs, etc).
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Widget_Base;

/**
 * Class Ziteh_Widget_Base
 */
abstract class Ziteh_Widget_Base extends Widget_Base {

	/**
	 * Place product-page widgets in their own Elementor category while keeping
	 * every public widget ID unchanged for backward compatibility.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		$product_widgets = apply_filters(
			'ziteh_core_single_product_widgets',
			array(
				'ziteh-single-product',
				'ziteh-product-details',
				'ziteh-related-products',
				'ziteh-product-reviews',
				'ziteh-store-services',
			)
		);

		$category = in_array( $this->get_name(), $product_widgets, true ) ? 'ziteh-product-page' : 'ziteh';

		return array( apply_filters( 'ziteh_core_widget_category', $category, $this->get_name() ) );
	}

	/**
	 * Shared stylesheet for every widget.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		$handles = array( 'ziteh-fonts', 'ziteh-widgets' );

		// Product-page widgets additionally need the isolated layer, including
		// inside Theme Builder templates where the smart asset mode has not
		// already enqueued it.
		if ( in_array( 'ziteh-product-page', $this->get_categories(), true ) ) {
			$handles[] = 'ziteh-single-product';
		}

		return $handles;
	}

	/**
	 * Shared script for interactive widgets (sliders, toggles, tabs).
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'ziteh-widgets' );
	}

	/**
	 * Keywords help users find the widget in the panel search.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'ziteh', 'زیته', ' zite' );
	}

	/**
	 * Render an inline SVG icon by name. Keeps markup consistent and avoids an
	 * icon-font dependency for the small UI glyphs used in the design.
	 *
	 * @param string $name  Icon key.
	 * @param string $class Optional extra CSS class.
	 * @return string SVG markup (already safe/escaped).
	 */
	protected function get_icon_svg( $name, $class = '' ) {
		$class = $class ? ' ' . $class : '';
		$icons = array(
			'cart'      => '<path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/><path d="M6 6 5 3H2"/>',
			'search'    => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
			'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
			'heart'     => '<path d="M12 21s-7-4.6-9.3-9C1 9 2.5 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.5 0 5 3.5 3.3 6.5C19 16.4 12 21 12 21z"/>',
			'arrow-l'   => '<path d="M15 6l-6 6 6 6"/>',
			'arrow-r'   => '<path d="M9 6l6 6-6 6"/>',
			'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l1.5 1.5M17.5 17.5 19 19M19 5l-1.5 1.5M6.5 17.5 5 19"/>',
			'moon'      => '<path d="M20 14.5A8 8 0 1 1 9.5 4 6.5 6.5 0 0 0 20 14.5z"/>',
			'star'      => '<path d="M12 3l2.6 5.6 6 .6-4.5 4 1.3 6L12 16.9 6.6 19.2l1.3-6-4.5-4 6-.6z"/>',
			'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1"/>',
			'phone'     => '<path d="M5 4h4l2 5-2.5 1.5a12 12 0 0 0 5 5L16 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
			'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
			'leaf'      => '<path d="M4 20C4 11 11 4 20 4c0 9-7 16-16 16z"/><path d="M4 20c4-6 8-9 12-11"/>',
			'droplet'   => '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/>',
			'shield'    => '<path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6z"/>',
			'spray'     => '<rect x="7" y="9" width="8" height="12" rx="2"/><path d="M9 9V6h4v3M17 5h1M19 7h1M17 9h1"/>',
			'brush'     => '<path d="M14 4l6 6-8 8H6v-6z"/><path d="M6 16l-2 4 4-2"/>',
		);

		$path = isset( $icons[ $name ] ) ? $icons[ $name ] : '';

		return sprintf(
			'<svg class="ziteh-icon ziteh-icon-%1$s%2$s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%3$s</svg>',
			esc_attr( $name ),
			esc_attr( $class ),
			$path
		);
	}
}
