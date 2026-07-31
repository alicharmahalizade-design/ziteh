<?php
/**
 * Ziteh theme bootstrap.
 *
 * The theme deliberately does very little. Its job is to provide a correct
 * document shell, register Elementor's Theme Builder locations, and stay out of
 * the way of the widgets that do the actual design work. Anything that styles
 * bare `button`, `input` or `p` belongs to a page builder widget, not here —
 * that kind of rule is exactly what leaked into the product page under the
 * previous theme.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ZITEH_THEME_VERSION', '1.0.0' );
define( 'ZITEH_THEME_DIR', get_template_directory() );
define( 'ZITEH_THEME_URI', get_template_directory_uri() );

require_once ZITEH_THEME_DIR . '/inc/class-ziteh-theme.php';
require_once ZITEH_THEME_DIR . '/inc/template-tags.php';

Ziteh_Theme::instance();
