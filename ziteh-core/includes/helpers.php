<?php
/**
 * Global helper functions.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Read a plugin setting by dot path, e.g. zt_opt( 'cart.tiers' ).
 *
 * @param string $path    Dot separated key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function zt_opt( $path, $default = null ) {
	return ZT_Settings::get( $path, $default );
}

/**
 * Convert Latin digits to Persian digits (when enabled in settings).
 *
 * @param string|int|float $value Value.
 * @param bool             $force Convert even if disabled in settings.
 * @return string
 */
function zt_fa( $value, $force = false ) {
	$value = (string) $value;
	if ( ! $force && ! zt_opt( 'general.persian_digits', true ) ) {
		return $value;
	}
	return strtr( $value, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

/**
 * Convert Persian / Arabic digits to Latin digits.
 *
 * @param string $value Value.
 * @return string
 */
function zt_en( $value ) {
	return strtr(
		(string) $value,
		array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
		)
	);
}

/**
 * Store amount -> displayed amount (Rial stores can be shown in Toman).
 *
 * @param float $amount Store amount.
 * @return float
 */
function zt_amount( $amount ) {
	$amount = (float) $amount;
	$div    = (float) zt_opt( 'general.price_divisor', 1 );
	if ( $div > 0 && 1.0 !== $div ) {
		$amount = $amount / $div;
	}
	return $amount;
}

/**
 * Format a number as "۳۸۵,۰۰۰" (no currency).
 *
 * @param float $amount   Amount (already in display unit).
 * @param bool  $convert  Run through zt_amount() first.
 * @return string
 */
function zt_money( $amount, $convert = false ) {
	if ( $convert ) {
		$amount = zt_amount( $amount );
	}
	return zt_fa( number_format( round( (float) $amount ), 0, '.', ',' ) );
}

/**
 * Currency label (تومان).
 *
 * @return string
 */
function zt_currency() {
	return (string) zt_opt( 'general.currency_label', 'تومان' );
}

/**
 * Price html used across the design: "۳۸۵,۰۰۰ <small>تومان</small>".
 *
 * @param float  $amount Store amount.
 * @param string $wrap   Tag for the currency label (small|i|span|'' for plain text).
 * @return string
 */
function zt_price( $amount, $wrap = 'small' ) {
	$num = zt_money( $amount, true );
	if ( '' === $wrap ) {
		return $num . ' ' . esc_html( zt_currency() );
	}
	return $num . ' <' . $wrap . '>' . esc_html( zt_currency() ) . '</' . $wrap . '>';
}

/**
 * Is WooCommerce active?
 *
 * @return bool
 */
function zt_is_woo() {
	return class_exists( 'WooCommerce' ) && function_exists( 'WC' );
}

/**
 * Are we inside the Elementor editor / preview?
 *
 * @return bool
 */
function zt_is_editor() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	$el = \Elementor\Plugin::$instance;
	return ( isset( $el->editor ) && $el->editor->is_edit_mode() ) || ( isset( $el->preview ) && $el->preview->is_preview_mode() ) || ( isset( $_REQUEST['action'] ) && 'elementor_ajax' === $_REQUEST['action'] ); // phpcs:ignore
}

/**
 * URL for a site "role" (home, shop, cart, checkout, account, tracking...).
 *
 * @param string $role Role key.
 * @return string
 */
function zt_page_url( $role ) {
	switch ( $role ) {
		case 'home':
			return home_url( '/' );
		case 'shop':
			if ( zt_is_woo() ) {
				$u = wc_get_page_permalink( 'shop' );
				if ( $u && home_url( '/' ) !== $u ) {
					return $u;
				}
			}
			break;
		case 'cart':
			if ( zt_is_woo() ) {
				return wc_get_cart_url();
			}
			break;
		case 'checkout':
			if ( zt_is_woo() ) {
				return wc_get_checkout_url();
			}
			break;
		case 'account':
			if ( zt_is_woo() ) {
				return wc_get_page_permalink( 'myaccount' );
			}
			break;
		case 'wishlist':
			if ( zt_is_woo() ) {
				return wc_get_account_endpoint_url( 'zt-wishlist' );
			}
			break;
		case 'orders':
			if ( zt_is_woo() ) {
				return wc_get_account_endpoint_url( 'orders' );
			}
			break;
		case 'logout':
			return zt_is_woo() ? wc_logout_url() : wp_logout_url( home_url( '/' ) );
	}
	$id = (int) zt_opt( 'pages.' . $role, 0 );
	if ( $id && get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	if ( 'blog' === $role && get_option( 'page_for_posts' ) ) {
		return get_permalink( get_option( 'page_for_posts' ) );
	}
	return home_url( '/' );
}

/**
 * Resolve an Elementor URL control (or string) and its {{tokens}}.
 *
 * Tokens: {{home}} {{shop}} {{cart}} {{checkout}} {{account}} {{tracking}}
 * {{routine}} {{blog}} {{wishlist}} {{about}} {{contact}} {{orders}} {{logout}}
 *
 * @param array|string $link Elementor URL control value or plain string.
 * @return string
 */
function zt_url( $link ) {
	$url = is_array( $link ) ? ( isset( $link['url'] ) ? $link['url'] : '' ) : (string) $link;
	if ( '' === $url ) {
		return '#';
	}
	if ( false !== strpos( $url, '{{' ) ) {
		$url = preg_replace_callback(
			'/\{\{\s*([a-z_-]+)\s*\}\}/',
			function ( $m ) {
				return zt_page_url( $m[1] );
			},
			$url
		);
	}
	return $url;
}

/**
 * Attributes string for a link control: href + target + rel.
 *
 * @param array|string $link Link control.
 * @return string Escaped attribute string (leading space).
 */
function zt_link_attrs( $link ) {
	$out = ' href="' . esc_url( zt_url( $link ) ) . '"';
	if ( is_array( $link ) ) {
		if ( ! empty( $link['is_external'] ) ) {
			$out .= ' target="_blank"';
		}
		$rel = array();
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( ! empty( $link['is_external'] ) ) {
			$rel[] = 'noopener';
		}
		if ( $rel ) {
			$out .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}
		if ( ! empty( $link['custom_attributes'] ) && class_exists( '\Elementor\Utils' ) ) {
			foreach ( \Elementor\Utils::parse_custom_attributes( $link['custom_attributes'] ) as $k => $v ) {
				$out .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
			}
		}
	}
	return $out;
}

/**
 * Elementor ICONS control default for a Ziteh icon.
 *
 * @param string $name Icon name (without i-).
 * @return array
 */
function zt_icon_default( $name ) {
	return array(
		'value'   => 'zti zti-' . $name,
		'library' => 'zt-icons',
	);
}

/**
 * Render an icon. Accepts an icon name ("leaf") or an Elementor ICONS value.
 * Ziteh icons render as <svg><use href="#i-NAME"/></svg> — exactly the markup
 * of the original design — so all size rules of the design apply unchanged.
 *
 * @param string|array $icon  Name or Elementor icon value.
 * @param array        $attrs Extra attributes for the <svg> (class, width, height, style).
 * @return string
 */
function zt_icon( $icon, $attrs = array() ) {
	if ( is_array( $icon ) ) {
		if ( empty( $icon['value'] ) ) {
			return '';
		}
		if ( 'zt-icons' === ( isset( $icon['library'] ) ? $icon['library'] : '' ) || ( is_string( $icon['value'] ) && 0 === strpos( $icon['value'], 'zti ' ) ) ) {
			$icon = preg_replace( '/^zti\s+zti-/', '', $icon['value'] );
		} elseif ( class_exists( '\Elementor\Icons_Manager' ) ) {
			ob_start();
			\Elementor\Icons_Manager::render_icon( $icon, array_merge( array( 'aria-hidden' => 'true' ), $attrs ) );
			return '<span class="zt-ext-ico">' . ob_get_clean() . '</span>';
		} else {
			return '';
		}
	}
	$icon = sanitize_key( $icon );
	if ( '' === $icon ) {
		return '';
	}
	$a = '';
	foreach ( $attrs as $k => $v ) {
		$a .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	ZT_Shell::need_sprite();
	return '<svg' . $a . '><use href="#i-' . esc_attr( $icon ) . '"/></svg>';
}

/**
 * Image URL from an Elementor MEDIA control value (or string).
 *
 * @param array|string $media Media control.
 * @param string       $size  Image size for attachments.
 * @return string
 */
function zt_img_url( $media, $size = 'full' ) {
	if ( is_array( $media ) ) {
		if ( ! empty( $media['id'] ) ) {
			$src = wp_get_attachment_image_url( (int) $media['id'], $size );
			if ( $src ) {
				return $src;
			}
		}
		return isset( $media['url'] ) ? $media['url'] : '';
	}
	return (string) $media;
}

/**
 * <img> tag from a media control.
 *
 * @param array|string $media Media control.
 * @param string       $alt   Alt text.
 * @param array        $attrs Extra attributes.
 * @param string       $size  Size.
 * @return string
 */
function zt_img( $media, $alt = '', $attrs = array(), $size = 'full' ) {
	$src = zt_img_url( $media, $size );
	if ( ! $src ) {
		return '';
	}
	if ( '' === $alt && is_array( $media ) && ! empty( $media['id'] ) ) {
		$alt = (string) get_post_meta( (int) $media['id'], '_wp_attachment_image_alt', true );
	}
	$a = '';
	foreach ( $attrs as $k => $v ) {
		$a .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
	}
	if ( ! isset( $attrs['loading'] ) ) {
		$a .= ' loading="lazy"';
	}
	return '<img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '"' . $a . '>';
}

/**
 * URL of a bundled design image.
 *
 * @param string $file File name in assets/img.
 * @return string
 */
function zt_asset_img( $file ) {
	return ZT_URL . 'assets/img/' . $file;
}

/**
 * Media control default for a bundled image.
 *
 * @param string $file File.
 * @return array
 */
function zt_media_default( $file ) {
	return array(
		'url' => zt_asset_img( $file ),
		'id'  => '',
	);
}

/**
 * Jalali date formatter (wrapper).
 *
 * @param string   $format    Format (Y m d F H i ...).
 * @param int|null $timestamp Unix timestamp (site timezone applied).
 * @return string
 */
function zt_jdate( $format, $timestamp = null ) {
	return ZT_Jalali::date( $format, null === $timestamp ? time() : (int) $timestamp );
}

/**
 * Allowed inline HTML for rich text controls (br, b, a, span, small...).
 *
 * @param string $html Html.
 * @return string
 */
function zt_kses( $html ) {
	return wp_kses(
		(string) $html,
		array(
			'br'     => array(),
			'b'      => array( 'class' => array() ),
			'strong' => array( 'class' => array() ),
			'em'     => array(),
			'i'      => array( 'class' => array() ),
			'small'  => array( 'class' => array() ),
			'span'   => array(
				'class' => array(),
				'style' => array(),
			),
			'a'      => array(
				'href'   => array(),
				'target' => array(),
				'rel'    => array(),
				'class'  => array(),
			),
			'p'      => array( 'class' => array() ),
			'ul'     => array( 'class' => array() ),
			'ol'     => array( 'class' => array() ),
			'li'     => array( 'class' => array() ),
			'del'    => array(),
			'mark'   => array(),
			'h3'     => array( 'class' => array() ),
			'h4'     => array( 'class' => array() ),
			'img'    => array(
				'src'   => array(),
				'alt'   => array(),
				'class' => array(),
			),
		)
	);
}

/**
 * Normalise a Persian search string (ی/ک, zero-width, digits).
 *
 * @param string $s Input.
 * @return string
 */
function zt_normalize_fa( $s ) {
	$s = zt_en( $s );
	$s = str_replace( array( "\xE2\x80\x8C", 'ي', 'ك', 'أ', 'إ', 'آ' ), array( ' ', 'ی', 'ک', 'ا', 'ا', 'ا' ), $s );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

/**
 * Split a textarea into non-empty trimmed lines.
 *
 * @param string $text Text.
 * @return string[]
 */
function zt_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ), 'strlen' ) );
}

/**
 * Per-page Ziteh setting (Elementor page settings "zt_{key}", or post meta "_zt_{key}").
 *
 * @param string   $key Key without prefix (page_type, bottom_bar, mobile_title, header_layout, footer_style, footer_width).
 * @param int|null $id  Post id (default: queried object).
 * @return string
 */
function zt_page_setting( $key, $id = null ) {
	$ids = array();
	if ( null !== $id ) {
		$ids[] = (int) $id;
	} else {
		$ids[] = (int) get_queried_object_id();
		if ( class_exists( 'ZT_Templates' ) && ZT_Templates::$current ) {
			$ids[] = (int) ZT_Templates::$current;
		}
	}
	foreach ( array_filter( $ids ) as $pid ) {
		$ps = get_post_meta( $pid, '_elementor_page_settings', true );
		if ( is_array( $ps ) && isset( $ps[ 'zt_' . $key ] ) && '' !== $ps[ 'zt_' . $key ] ) {
			return (string) $ps[ 'zt_' . $key ];
		}
		$v = get_post_meta( $pid, '_zt_' . $key, true );
		if ( '' !== (string) $v ) {
			return (string) $v;
		}
	}
	return '';
}
