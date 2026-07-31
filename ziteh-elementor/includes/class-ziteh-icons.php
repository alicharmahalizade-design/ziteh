<?php
/**
 * Self-contained inline SVG icon set.
 *
 * The product widgets used to print raw Font Awesome markup (`<i class="fas
 * fa-leaf">`). Nothing in the plugin ever enqueued a Font Awesome stylesheet,
 * so on any site where the theme or Elementor did not happen to load one the
 * icons collapsed into empty tofu boxes. The isolated product canvas makes that
 * worse, because it drops the WooCommerce stylesheet that carries the star
 * rating font.
 *
 * Every icon the reference UI needs therefore ships here as inline SVG: no
 * network request, no font, no dependency on the theme. Icons the site owner
 * picks in Elementor still go through Icons_Manager, and we enqueue Elementor's
 * Font Awesome styles so those render too.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Icons_Manager;

/**
 * Class Ziteh_Icons.
 */
class Ziteh_Icons {

	/**
	 * Stroke-drawn glyphs on a 24x24 grid.
	 *
	 * @var array<string, string>
	 */
	private static $stroke = array(
		'leaf'          => '<path d="M4.6 19.4C3 15.5 4.2 8.9 9 6.4c3.2-1.7 7-1.2 10.4-2.4.6 3.7.3 8.2-1.7 11.3-2.6 4-8 5-11.6 3.4"/><path d="M6.2 18.2c1.9-4 5-7.2 8.9-9.2"/>',
		'truck'         => '<path d="M2.8 6.6h9.9v9.2H2.8z"/><path d="M12.7 9.5h3.6l2.9 2.9v3.4h-6.5z"/><circle cx="7" cy="17.6" r="1.7"/><circle cx="16.3" cy="17.6" r="1.7"/><path d="M8.7 17.6h5.9"/>',
		'chevron-down'  => '<path d="M6.5 9.5 12 15l5.5-5.5"/>',
		'chevron-up'    => '<path d="M6.5 14.5 12 9l5.5 5.5"/>',
		'chevron-left'  => '<path d="M14.5 6.5 9 12l5.5 5.5"/>',
		'chevron-right' => '<path d="M9.5 6.5 15 12l-5.5 5.5"/>',
		'heart'         => '<path d="M12 19.6C8.9 17.4 4 14 4 9.9A3.9 3.9 0 0 1 12 8a3.9 3.9 0 0 1 8 1.9c0 4.1-4.9 7.5-8 9.7Z"/>',
		'shield'        => '<path d="M12 3.5 19 6v5.4c0 4-2.9 7.3-7 9.1-4.1-1.8-7-5.1-7-9.1V6Z"/><path d="m9.2 11.9 2 2 3.6-3.9"/>',
		'award'         => '<circle cx="12" cy="9.6" r="5.1"/><path d="m9.4 14 -1 6.1 3.6-2 3.6 2-1-6.1"/><path d="m10.2 9.5 1.3 1.4 2.3-2.6"/>',
		'rotate'        => '<path d="M19 12a7 7 0 1 1-2.4-5.3"/><path d="M19.2 4.4v3.9h-3.9"/>',
		'cart'          => '<path d="M3.4 4.4h2.3l2 9.7h8.8l1.9-6.9H6.4"/><circle cx="9.2" cy="18.4" r="1.5"/><circle cx="15.6" cy="18.4" r="1.5"/>',
		'check-circle'  => '<circle cx="12" cy="12" r="8.3"/><path d="m8.4 12.2 2.4 2.4 4.8-5.2"/>',
		'check'         => '<path d="m5.5 12.6 4 4 9-9.2"/>',
		'plus'          => '<path d="M12 6.4v11.2M6.4 12h11.2"/>',
		'minus'         => '<path d="M6.4 12h11.2"/>',
		'box'           => '<path d="m12 3.6 8 4v8.8l-8 4-8-4V7.6Z"/><path d="M4 7.6l8 4 8-4M12 11.6v8.8"/>',
		'headset'       => '<path d="M5 14.4v-2.6a7 7 0 0 1 14 0v2.6"/><path d="M5 13.1h1.8a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1Zm14 0h-1.8a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1H18a1 1 0 0 0 1-1Z"/><path d="M17.2 18.1v.6a2 2 0 0 1-2 2h-2.4"/>',
		'flask'         => '<path d="M9.7 3.9h4.6M10.6 3.9v5.4L6.2 17a2 2 0 0 0 1.7 3h8.2a2 2 0 0 0 1.7-3l-4.4-7.7V3.9"/><path d="M8.2 14.2h7.6"/>',
		'seedling'      => '<path d="M12 20.1v-6.4"/><path d="M12 13.7C12 10.6 9.6 8.2 6.5 8.2c-.6 0-1.2.1-1.7.3.4 2.8 2.8 5 5.7 5.2Z"/><path d="M12 13.7c0-3.1 2.4-5.5 5.5-5.5.6 0 1.2.1 1.7.3-.4 2.8-2.8 5-5.7 5.2Z"/>',
		'atom'          => '<circle cx="12" cy="12" r="2"/><ellipse cx="12" cy="12" rx="9" ry="3.9"/><ellipse cx="12" cy="12" rx="9" ry="3.9" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="9" ry="3.9" transform="rotate(120 12 12)"/>',
		'hair'          => '<path d="M4.7 19.6c0-5.4 1.4-9.6 3.6-12.2C9.7 5.6 11 4.7 12 4.7s2.3.9 3.7 2.7c2.2 2.6 3.6 6.8 3.6 12.2"/><path d="M8.7 19.6c0-4.7.7-8.2 2-10.4"/><path d="M15.3 19.6c0-4.7-.7-8.2-2-10.4"/><path d="M12 19.6V7"/>',
		'droplet'       => '<path d="M12 3.6c3 3.4 5.4 6.3 5.4 9.1A5.4 5.4 0 0 1 6.6 12.7c0-2.8 2.4-5.7 5.4-9.1Z"/>',
		'user'          => '<circle cx="12" cy="8.4" r="3.6"/><path d="M4.9 20a7.4 7.4 0 0 1 14.2 0"/>',
		'search'        => '<circle cx="10.9" cy="10.9" r="6.4"/><path d="m15.6 15.6 4 4"/>',
		'chat'          => '<path d="M20 12.3c0 3.8-3.6 6.8-8 6.8a9.4 9.4 0 0 1-2.6-.4L4.6 20l1.2-3.5A6.4 6.4 0 0 1 4 12.3c0-3.7 3.6-6.8 8-6.8s8 3.1 8 6.8Z"/>',
	);

	/**
	 * Solid glyphs on a 24x24 grid.
	 *
	 * @var array<string, string>
	 */
	private static $solid = array(
		'star'      => '<path d="m12 2.9 2.8 5.7 6.3.9-4.6 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.5l6.3-.9z"/>',
		'leaf-fill' => '<path d="M20.2 3.1c-3.6 1.2-7.5.7-10.8 2.5C4.2 8.4 2.9 15.6 4.8 19.7a1 1 0 0 0 1.8-.9c-.4-.8-.6-1.8-.7-2.9 2-4 5.2-7.2 9.2-9.2a1 1 0 0 1 .9 1.8c-3.3 1.7-6 4.2-7.8 7.4 3.4.7 8-.4 10.4-4.1 2.1-3.3 2.3-8.1 1.6-11.9Z"/>',
	);

	/**
	 * Font Awesome class fragments mapped to our own glyph names, so widgets
	 * that already have Font Awesome defaults saved in the database still render
	 * the reference artwork instead of a missing-font box.
	 *
	 * @var array<string, string>
	 */
	private static $fa_map = array(
		'leaf'          => 'leaf',
		'truck-fast'    => 'truck',
		'shipping-fast' => 'truck',
		'truck'         => 'truck',
		'chevron-down'  => 'chevron-down',
		'chevron-up'    => 'chevron-up',
		'chevron-left'  => 'chevron-left',
		'chevron-right' => 'chevron-right',
		'angle-down'    => 'chevron-down',
		'angle-left'    => 'chevron-left',
		'angle-right'   => 'chevron-right',
		'heart'         => 'heart',
		'shield-alt'    => 'shield',
		'shield-halved' => 'shield',
		'shield'        => 'shield',
		'award'         => 'award',
		'medal'         => 'award',
		'certificate'   => 'award',
		'redo-alt'      => 'rotate',
		'rotate-right'  => 'rotate',
		'rotate-left'   => 'rotate',
		'undo'          => 'rotate',
		'sync'          => 'rotate',
		'shopping-cart' => 'cart',
		'cart-shopping' => 'cart',
		'cart-plus'     => 'cart',
		'check-circle'  => 'check-circle',
		'circle-check'  => 'check-circle',
		'check'         => 'check',
		'plus'          => 'plus',
		'minus'         => 'minus',
		'box'           => 'box',
		'box-open'      => 'box',
		'cube'          => 'box',
		'headset'       => 'headset',
		'headphones'    => 'headset',
		'flask'         => 'flask',
		'vial'          => 'flask',
		'seedling'      => 'seedling',
		'spa'           => 'seedling',
		'atom'          => 'atom',
		'glasses'       => 'hair',
		'mask'          => 'hair',
		'tint'          => 'droplet',
		'droplet'       => 'droplet',
		'user'          => 'user',
		'search'        => 'search',
		'comment'       => 'chat',
		'comments'      => 'chat',
		'star'          => 'star',
	);

	/**
	 * Build one inline SVG.
	 *
	 * @param string $name    Glyph name from the stroke or solid set.
	 * @param array  $attrs   Extra attributes for the <svg> element.
	 * @return string SVG markup, or an empty string for an unknown glyph.
	 */
	public static function get( $name, $attrs = array() ) {
		$name   = str_replace( '_', '-', (string) $name );
		$solid  = isset( self::$solid[ $name ] );
		$body   = $solid ? self::$solid[ $name ] : ( isset( self::$stroke[ $name ] ) ? self::$stroke[ $name ] : '' );

		if ( '' === $body ) {
			return '';
		}

		$defaults = array(
			'class'       => 'ziteh-i ziteh-i--' . $name,
			'viewBox'     => '0 0 24 24',
			'xmlns'       => 'http://www.w3.org/2000/svg',
			'aria-hidden' => 'true',
			'focusable'   => 'false',
		);

		if ( $solid ) {
			$defaults['fill'] = 'currentColor';
		} else {
			$defaults['fill']            = 'none';
			$defaults['stroke']          = 'currentColor';
			$defaults['stroke-width']    = '1.5';
			$defaults['stroke-linecap']  = 'round';
			$defaults['stroke-linejoin'] = 'round';
		}

		$attrs = array_merge( $defaults, $attrs );
		$html  = '';
		foreach ( $attrs as $key => $value ) {
			if ( null === $value || false === $value ) {
				continue;
			}
			$html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return '<svg' . $html . '>' . $body . '</svg>';
	}

	/**
	 * Echo one inline SVG.
	 *
	 * @param string $name  Glyph name.
	 * @param array  $attrs Extra attributes.
	 */
	public static function render( $name, $attrs = array() ) {
		echo self::get( $name, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Render an Elementor icon control, preferring our own artwork.
	 *
	 * When the stored icon is one of the Font Awesome glyphs the reference UI
	 * uses, the matching inline SVG is printed instead — that keeps the design
	 * intact even when no icon font is available. Anything else falls through to
	 * Elementor, with the Font Awesome stylesheets enqueued so it can render.
	 *
	 * @param array  $icon     Elementor ICONS control value.
	 * @param string $fallback Glyph name to use when $icon is empty.
	 * @param array  $attrs    Extra attributes for the <svg> element.
	 */
	public static function render_control( $icon, $fallback = '', $attrs = array() ) {
		$value   = is_array( $icon ) && isset( $icon['value'] ) ? $icon['value'] : '';
		$library = is_array( $icon ) && isset( $icon['library'] ) ? $icon['library'] : '';

		// Elementor stores uploaded SVGs as an array with a url — let Elementor handle those.
		if ( is_array( $value ) || 'svg' === $library ) {
			self::enqueue_icon_fonts();
			Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
			return;
		}

		$mapped = self::map_class( (string) $value );

		if ( '' === $mapped && '' === $value && $fallback ) {
			$mapped = $fallback;
		}

		if ( $mapped ) {
			self::render( $mapped, $attrs );
			return;
		}

		if ( '' !== $value ) {
			self::enqueue_icon_fonts();
			Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) );
			return;
		}

		if ( $fallback ) {
			self::render( $fallback, $attrs );
		}
	}

	/**
	 * Resolve a Font Awesome class string to one of our glyph names.
	 *
	 * @param string $classes Class string such as "fas fa-truck-fast".
	 * @return string Glyph name, or an empty string when unmapped.
	 */
	private static function map_class( $classes ) {
		if ( '' === $classes ) {
			return '';
		}

		foreach ( preg_split( '/\s+/', $classes ) as $class ) {
			if ( 0 !== strpos( $class, 'fa-' ) ) {
				continue;
			}
			$key = substr( $class, 3 );
			if ( isset( self::$fa_map[ $key ] ) ) {
				return self::$fa_map[ $key ];
			}
		}

		return '';
	}

	/**
	 * Enqueue Elementor's Font Awesome styles for site-owner-chosen icons.
	 *
	 * Elementor registers these handles but only enqueues them for its own
	 * widgets, so third-party widgets that call Icons_Manager have to ask.
	 */
	private static function enqueue_icon_fonts() {
		foreach ( array( 'elementor-icons-fa-solid', 'elementor-icons-fa-regular', 'elementor-icons-fa-brands' ) as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) && ! wp_style_is( $handle, 'enqueued' ) ) {
				wp_enqueue_style( $handle );
			}
		}
	}

	/**
	 * Star rating drawn as inline SVG.
	 *
	 * The isolated product canvas drops woocommerce-general, which is where the
	 * WooCommerce icon font behind `.star-rating` lives, so `wc_get_rating_html()`
	 * renders as literal glyph boxes. This draws the same information without a
	 * font dependency, with a clip path for the fractional star.
	 *
	 * @param float  $rating Average rating, 0–5.
	 * @param int    $count  Rating count, used for the accessible label.
	 * @param string $class  Extra class for the wrapper.
	 * @return string
	 */
	public static function stars( $rating, $count = 0, $class = '' ) {
		$rating = max( 0, min( 5, (float) $rating ) );
		$label  = $count > 0
			/* translators: 1: average rating, 2: number of ratings */
			? sprintf( __( 'امتیاز %1$s از ۵ بر اساس %2$s رأی', 'ziteh' ), number_format_i18n( $rating, 1 ), number_format_i18n( $count ) )
			/* translators: %s: average rating */
			: sprintf( __( 'امتیاز %s از ۵', 'ziteh' ), number_format_i18n( $rating, 1 ) );

		$html = '<span class="ziteh-stars' . ( $class ? ' ' . esc_attr( $class ) : '' ) . '" role="img" aria-label="' . esc_attr( $label ) . '">';

		for ( $i = 1; $i <= 5; $i++ ) {
			$fill = max( 0, min( 1, $rating - ( $i - 1 ) ) );

			if ( $fill >= 1 ) {
				$html .= '<span class="ziteh-stars__star is-full">' . self::get( 'star' ) . '</span>';
				continue;
			}

			if ( $fill <= 0 ) {
				$html .= '<span class="ziteh-stars__star is-empty">' . self::get( 'star' ) . '</span>';
				continue;
			}

			// Partial star: one empty glyph with a clipped full glyph on top.
			// The clip is inset from the inline-end so it fills right-to-left in
			// LTR terms, which is what a left-to-right star row expects.
			$percent = round( $fill * 100, 2 );
			$html   .= '<span class="ziteh-stars__star is-empty">' . self::get( 'star' )
				. '<span class="ziteh-stars__fill" style="width:' . esc_attr( $percent ) . '%">'
				. self::get( 'star' ) . '</span></span>';
		}

		return $html . '</span>';
	}
}
