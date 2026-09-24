<?php
/**
 * Plugin Name:       Ziteh Core — هسته زیته
 * Plugin URI:        https://ziteh.com
 * Description:       افزونه هسته فروشگاه زیته: تبدیل پیکسل‌به‌پیکسل تمام صفحات طرح به ویجت‌های اختصاصی المنتور (دسته‌بندی‌شده بر اساس صفحه)، ساخت خودکار صفحات و قالب‌ها با یک کلیک، قالب داینامیک تک‌محصول، سبد خرید با تخفیف پلکانی و سمپل هدیه، صورت‌حساب سفارشی، پیگیری سفارش، پنل کاربری، علاقه‌مندی، امتیاز باشگاه و صفحه مدیریت کامل تنظیمات.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Ziteh
 * Text Domain:       ziteh-core
 * Domain Path:       /languages
 * Requires Plugins:  elementor
 * Elementor tested up to: 3.31
 * WC requires at least: 8.0
 * WC tested up to:   9.8
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

define( 'ZT_VERSION', '1.0.0' );
define( 'ZT_FILE', __FILE__ );
define( 'ZT_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZT_URL', plugin_dir_url( __FILE__ ) );
define( 'ZT_MIN_ELEMENTOR', '3.16.0' );

require_once ZT_PATH . 'includes/helpers.php';
require_once ZT_PATH . 'includes/class-zt-jalali.php';
require_once ZT_PATH . 'includes/class-zt-settings.php';
require_once ZT_PATH . 'includes/class-zt-plugin.php';

ZT_Plugin::instance();

register_activation_hook( __FILE__, array( 'ZT_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ZT_Plugin', 'deactivate' ) );

// Declare HPOS + cart/checkout blocks compatibility for WooCommerce.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
