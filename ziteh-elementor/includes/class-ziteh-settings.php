<?php
/**
 * Central design settings.
 *
 * Adds a "زیته" top-level admin menu where the whole palette / radius / motion
 * can be tuned from one place, then prints matching CSS custom-property
 * overrides in the <head> so every widget updates at once — no per-widget
 * editing needed.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Settings
 */
class Ziteh_Settings {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Settings|null
	 */
	private static $instance = null;

	/**
	 * Option key.
	 */
	const OPTION = 'ziteh_settings';

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Settings
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Default values (mirror the CSS defaults).
	 *
	 * @return array<string,string>
	 */
	public static function defaults() {
		$defaults = array(
			'green'      => '#7c8a5c',
			'green_dark' => '#55603a',
			'cream'      => '#f6f2ea',
			'ink'        => '#47473f',
			'sale'       => '#e0483d',
			'radius'     => '20',
			'animations' => 'on',
			'font'       => 'Vazirmatn',
			'shell'      => 'on',
			'quickview'  => 'on',
			'live_search' => 'on',
			'cart_drawer' => 'on',
			'wishlist'    => 'on',
			'quiz_products' => 'on',
			'sticky_header' => 'on',
			'external_font' => 'on',
			'asset_mode'    => 'smart',
			'auto_cache_purge' => 'on',
			'product_isolation' => 'on',
			'product_app'       => 'on',

			// Global content. Widgets fall back to these when their own field is
			// left empty, so a phone number or shipping promise is edited once.
			'c_brand_name'       => '',
			'c_brand_tagline'    => '',
			'c_phone'            => '',
			'c_email'            => '',
			'c_address'          => '',
			'c_hours'            => '',
			'c_map'              => '',
			'c_instagram'        => '',
			'c_telegram'         => '',
			'c_whatsapp'         => '',
			'c_shipping_title'   => '',
			'c_shipping_text'    => '',
			'c_shipping_details' => '',
			'c_copyright'        => '',

			// Technical SEO. Both schema switches default to off because an SEO
			// plugin almost certainly emits the same markup already, and two
			// copies of Product or BreadcrumbList schema on one page is worse
			// than none.
			'seo_product_schema'    => 'off',
			'seo_breadcrumb_schema' => 'off',
			'seo_preload_lcp'       => 'on',

			// Design scale.
			'container'   => '1360',
			'base_size'   => '16',
		);

		/**
		 * Extend default core settings without modifying this class.
		 *
		 * @param array<string,string> $defaults Default settings.
		 */
		return apply_filters( 'ziteh_core_default_settings', $defaults );
	}

	/**
	 * Whether the interactive shell (cart drawer / quick view / live search) is on.
	 *
	 * @return bool
	 */
	public static function shell_enabled() {
		$s = self::get();
		return 'off' !== $s['shell'];
	}

	/**
	 * Check whether a core feature is enabled.
	 *
	 * @param string $feature Setting key.
	 * @param bool   $default Fallback when a future key is not stored yet.
	 * @return bool
	 */
	public static function feature_enabled( $feature, $default = true ) {
		$s = self::get();
		if ( ! array_key_exists( $feature, $s ) ) {
			return (bool) $default;
		}
		return 'off' !== $s[ $feature ];
	}

	/**
	 * A global content value, or an empty string.
	 *
	 * @param string $key     Key without the `c_` prefix.
	 * @param string $default Value to use when nothing is stored.
	 * @return string
	 */
	public static function content( $key, $default = '' ) {
		$s   = self::get();
		$key = 'c_' . $key;
		$val = isset( $s[ $key ] ) ? trim( (string) $s[ $key ] ) : '';
		return '' !== $val ? $val : $default;
	}

	/**
	 * Detect an SEO plugin that owns titles, meta and structured data.
	 *
	 * Ziteh has no business emitting a second canonical tag or a second Product
	 * schema block. Where one of these is active, the plugin steps back and says
	 * so in the panel rather than quietly competing.
	 *
	 * @return string Human-readable plugin name, or an empty string.
	 */
	public static function seo_plugin() {
		$known = array(
			'RankMath\\Helper'                  => 'Rank Math SEO',
			'WPSEO_Options'                      => 'Yoast SEO',
			'SEOPress'                           => 'SEOPress',
			'AIOSEO\\Plugin\\AIOSEO'            => 'All in One SEO',
			'The_SEO_Framework\\Load'            => 'The SEO Framework',
		);

		foreach ( $known as $marker => $label ) {
			if ( class_exists( $marker ) ) {
				return $label;
			}
		}

		// Rank Math also exposes a plain constant, checked separately because the
		// namespaced class is not loaded on every request.
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			return 'Rank Math SEO';
		}

		if ( defined( 'SEOPRESS_VERSION' ) ) {
			return 'SEOPress';
		}

		return '';
	}

	/**
	 * Whether Ziteh may emit its own structured data.
	 *
	 * @param string $type Either `product` or `breadcrumb`.
	 * @return bool
	 */
	public static function may_emit_schema( $type ) {
		if ( self::seo_plugin() ) {
			return false;
		}
		return self::feature_enabled( 'seo_' . $type . '_schema', false );
	}

	/**
	 * Allowed font-family choices (label => CSS stack).
	 *
	 * @return array<string,string>
	 */
	public static function fonts() {
		return array(
			'Vazirmatn' => "'Vazirmatn', 'Tahoma', sans-serif",
			'Estedad'   => "'Estedad', 'Vazirmatn', 'Tahoma', sans-serif",
			'Sahel'     => "'Sahel', 'Vazirmatn', 'Tahoma', sans-serif",
			'IRANSansX' => "'IRANSansX', 'IRANSans', 'Vazirmatn', 'Tahoma', sans-serif",
			'IRANYekan' => "'IRANYekan', 'Vazirmatn', 'Tahoma', sans-serif",
			'System'    => "'Tahoma', 'Segoe UI', sans-serif",
		);
	}

	/**
	 * Get merged settings (defaults + saved).
	 *
	 * @return array<string,string>
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Print the variable overrides on the front-end and the Elementor editor.
		add_action( 'wp_head', array( $this, 'print_vars' ), 20 );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'print_vars' ), 20 );
	}

	/**
	 * Top-level "زیته" menu with the settings screen.
	 */
	public function register_menu() {
		add_menu_page(
			esc_html__( 'تنظیمات هسته زیته', 'ziteh' ),
			esc_html__( 'هسته زیته', 'ziteh' ),
			'manage_options',
			'ziteh-settings',
			array( $this, 'render_page' ),
			'dashicons-leaf',
			58
		);
	}

	/**
	 * Register the setting + sanitiser.
	 */
	public function register_settings() {
		register_setting(
			'ziteh_settings_group',
			self::OPTION,
			array( $this, 'sanitize' )
		);
	}

	/**
	 * Sanitise the submitted settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string,string>
	 */
	public function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$out      = array();
		$defaults = self::defaults();
		foreach ( array( 'green', 'green_dark', 'cream', 'ink', 'sale' ) as $color ) {
			$val           = isset( $input[ $color ] ) ? sanitize_hex_color( $input[ $color ] ) : '';
			$out[ $color ] = $val ? $val : $defaults[ $color ];
		}
		$out['radius']     = isset( $input['radius'] ) ? (string) max( 0, min( 40, (int) $input['radius'] ) ) : $defaults['radius'];
		foreach ( array( 'animations', 'shell', 'quickview', 'live_search', 'cart_drawer', 'wishlist', 'quiz_products', 'sticky_header', 'external_font', 'auto_cache_purge', 'product_isolation', 'product_app' ) as $flag ) {
			$out[ $flag ] = ( isset( $input[ $flag ] ) && 'on' === $input[ $flag ] ) ? 'on' : 'off';
		}
		foreach ( array( 'seo_preload_lcp', 'seo_product_schema', 'seo_breadcrumb_schema' ) as $flag ) {
			$out[ $flag ] = ( isset( $input[ $flag ] ) && 'on' === $input[ $flag ] ) ? 'on' : 'off';
		}

		// An active SEO plugin owns structured data. Storing "on" here would let
		// a later plugin deactivation silently start emitting duplicates, so the
		// value is forced down at save time rather than only hidden in the UI.
		if ( self::seo_plugin() ) {
			$out['seo_product_schema']    = 'off';
			$out['seo_breadcrumb_schema'] = 'off';
		}

		foreach ( array( 'c_brand_name', 'c_brand_tagline', 'c_phone', 'c_hours', 'c_shipping_title', 'c_shipping_text', 'c_copyright' ) as $key ) {
			$out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		}
		foreach ( array( 'c_address', 'c_shipping_details' ) as $key ) {
			$out[ $key ] = isset( $input[ $key ] ) ? sanitize_textarea_field( $input[ $key ] ) : '';
		}
		$out['c_email'] = isset( $input['c_email'] ) ? sanitize_email( $input['c_email'] ) : '';
		foreach ( array( 'c_map', 'c_instagram', 'c_telegram', 'c_whatsapp' ) as $key ) {
			$out[ $key ] = isset( $input[ $key ] ) ? esc_url_raw( trim( $input[ $key ] ) ) : '';
		}

		$out['container'] = isset( $input['container'] ) ? (string) max( 960, min( 1680, (int) $input['container'] ) ) : $defaults['container'];
		$out['base_size'] = isset( $input['base_size'] ) ? (string) max( 14, min( 20, (int) $input['base_size'] ) ) : $defaults['base_size'];

		$out['asset_mode'] = ( isset( $input['asset_mode'] ) && in_array( $input['asset_mode'], array( 'smart', 'global' ), true ) ) ? $input['asset_mode'] : 'smart';
		$fonts             = self::fonts();
		$out['font']       = ( isset( $input['font'] ) && isset( $fonts[ $input['font'] ] ) ) ? $input['font'] : $defaults['font'];
		/**
		 * Let future Ziteh modules validate and persist their own settings.
		 *
		 * @param array<string,mixed> $out      Sanitized core settings.
		 * @param array<string,mixed> $input    Submitted settings.
		 * @param array<string,mixed> $defaults Default settings.
		 */
		return apply_filters( 'ziteh_core_sanitize_settings', $out, $input, $defaults );
	}

	/**
	 * Render the settings screen.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s      = self::get();
		$fields = array(
			'green'      => esc_html__( 'رنگ اصلی', 'ziteh' ),
			'green_dark' => esc_html__( 'رنگ تیترها', 'ziteh' ),
			'cream'      => esc_html__( 'پس‌زمینه روشن', 'ziteh' ),
			'ink'        => esc_html__( 'رنگ متن', 'ziteh' ),
			'sale'       => esc_html__( 'رنگ تخفیف و هشدار', 'ziteh' ),
		);
		$feature_count = count( array_filter( array( $s['quickview'], $s['live_search'], $s['cart_drawer'], $s['wishlist'], $s['quiz_products'], $s['sticky_header'], $s['animations'], $s['product_isolation'] ), static function ( $value ) { return 'on' === $value; } ) );
		?>
		<style>
			.ziteh-core{--zc-primary:#64714a;--zc-primary-dark:#465033;--zc-bg:#f6f7f4;--zc-card:#fff;--zc-text:#20251b;--zc-muted:#68705f;--zc-border:#dfe3da;max-width:1180px;margin:24px 0 40px;font-family:Vazirmatn,Tahoma,sans-serif;color:var(--zc-text)}
			.ziteh-core *{box-sizing:border-box}.ziteh-core__hero{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:26px 30px;border-radius:18px;background:linear-gradient(135deg,#465033,#718055);color:#fff;box-shadow:0 12px 32px rgba(48,58,35,.18)}
			.ziteh-core__brand{display:flex;align-items:center;gap:16px}.ziteh-core__mark{display:grid;place-items:center;width:54px;height:54px;border-radius:16px;background:rgba(255,255,255,.14);font-size:30px}.ziteh-core h1{margin:0 0 4px;color:#fff;font-size:25px}.ziteh-core__hero p{margin:0;color:rgba(255,255,255,.78)}.ziteh-core__version{padding:8px 12px;border:1px solid rgba(255,255,255,.25);border-radius:999px;white-space:nowrap}
			.ziteh-core__stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:18px 0}.ziteh-stat{display:flex;align-items:center;justify-content:space-between;padding:17px 20px;background:var(--zc-card);border:1px solid var(--zc-border);border-radius:14px}.ziteh-stat strong{display:block;font-size:22px}.ziteh-stat span{color:var(--zc-muted)}.ziteh-stat__dot{width:10px;height:10px;border-radius:50%;background:#3fa55b;box-shadow:0 0 0 5px #e5f5e9}
			.ziteh-core__layout{display:grid;grid-template-columns:220px minmax(0,1fr);gap:18px}.ziteh-core__nav{align-self:start;position:sticky;top:46px;padding:10px;background:#fff;border:1px solid var(--zc-border);border-radius:16px}.ziteh-core__tab{display:flex;align-items:center;width:100%;min-height:44px;margin:2px 0;padding:10px 13px;border:0;border-radius:10px;background:transparent;color:#394033;text-align:right;cursor:pointer;font:inherit;font-weight:600}.ziteh-core__tab:hover{background:#f2f4ef}.ziteh-core__tab.is-active{background:#e8ece3;color:var(--zc-primary-dark)}.ziteh-core__tab:focus-visible{outline:3px solid rgba(100,113,74,.3);outline-offset:2px}
			.ziteh-panel{display:none}.ziteh-panel.is-active{display:block}.ziteh-card{margin-bottom:16px;padding:24px;background:#fff;border:1px solid var(--zc-border);border-radius:16px;box-shadow:0 3px 12px rgba(34,41,27,.035)}.ziteh-card h2{margin:0 0 6px;font-size:18px}.ziteh-card__lead{margin:0 0 22px;color:var(--zc-muted)}.ziteh-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.ziteh-field{padding:16px;border:1px solid #e8ebe4;border-radius:12px;background:#fbfcfa}.ziteh-field>label:first-child{display:block;margin-bottom:8px;font-weight:700}.ziteh-field p{margin:7px 0 0;color:var(--zc-muted);font-size:12px;line-height:1.7}.ziteh-field input[type=color]{width:48px;height:38px;padding:2px;border-radius:8px;vertical-align:middle}.ziteh-field input[type=number],.ziteh-field select{width:100%;min-height:40px;border-color:#cbd1c4;border-radius:8px}.ziteh-color-value{display:inline-block;margin-right:8px;direction:ltr;color:#596151}
			.ziteh-switch{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;cursor:pointer}.ziteh-switch input{position:absolute;opacity:0;pointer-events:none}.ziteh-switch__rail{position:relative;flex:0 0 44px;width:44px;height:24px;margin-top:1px;border-radius:999px;background:#b8beb1;transition:.2s}.ziteh-switch__rail:after{content:"";position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.22);transition:.2s}.ziteh-switch input:checked+.ziteh-switch__rail{background:var(--zc-primary)}.ziteh-switch input:checked+.ziteh-switch__rail:after{transform:translateX(-20px)}.ziteh-switch input:focus-visible+.ziteh-switch__rail{outline:3px solid rgba(100,113,74,.28);outline-offset:2px}.ziteh-switch__copy{flex:1}.ziteh-switch__copy strong{display:block;margin-bottom:3px}.ziteh-switch__copy small{display:block;color:var(--zc-muted);line-height:1.65}
			.ziteh-core__save{position:sticky;bottom:0;z-index:2;display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding:14px 18px;background:rgba(255,255,255,.94);border:1px solid var(--zc-border);border-radius:14px;backdrop-filter:blur(10px)}.ziteh-core__save p{margin:0;color:var(--zc-muted)}.ziteh-core__save .button-primary{min-height:42px;padding:0 22px;border-color:var(--zc-primary-dark);background:var(--zc-primary-dark)}.ziteh-system{width:100%;border-collapse:collapse}.ziteh-system td{padding:11px 5px;border-bottom:1px solid #edf0e9}.ziteh-system td:last-child{text-align:left;direction:ltr}.ziteh-core__tools{margin:18px 0 0;padding:18px 24px;background:#fff;border:1px solid var(--zc-border);border-radius:16px}.ziteh-core__tools hr,.ziteh-core__tools h2{display:none}.ziteh-core__tools form{margin-top:10px}
			@media(max-width:782px){.ziteh-core{margin-left:10px}.ziteh-core__hero{align-items:flex-start;padding:22px}.ziteh-core__version{display:none}.ziteh-core__stats{grid-template-columns:1fr}.ziteh-core__layout{grid-template-columns:1fr}.ziteh-core__nav{position:static;display:flex;overflow:auto}.ziteh-core__tab{width:auto;white-space:nowrap}.ziteh-grid{grid-template-columns:1fr}.ziteh-core__save{position:static;align-items:flex-start;gap:10px;flex-direction:column}.ziteh-core__save .button{width:100%}}
			.ziteh-switch__copy{min-width:0}.ziteh-switch__copy strong{overflow-wrap:anywhere}
			.ziteh-core__layout>main{padding-bottom:74px}
			.ziteh-field textarea,.ziteh-field input[type=text],.ziteh-field input[type=email],.ziteh-field input[type=url]{width:100%;min-height:40px;padding:8px 11px;border:1px solid #cbd1c4;border-radius:8px;font:inherit;background:#fff}.ziteh-field textarea{min-height:78px;line-height:1.8;resize:vertical}
			.ziteh-field.is-locked{opacity:.55}.ziteh-field.is-locked .ziteh-switch{cursor:not-allowed}
			.ziteh-notice{margin:0 0 18px;padding:14px 16px;border:1px solid #e0d9c2;border-radius:12px;background:#fdfbf3}.ziteh-notice--ok{border-color:#cfdcc6;background:#f2f7ee}.ziteh-notice strong{display:block;margin-bottom:5px}.ziteh-notice p{margin:0;color:var(--zc-muted);line-height:1.8}
			@media(prefers-reduced-motion:reduce){.ziteh-core *{transition:none!important}}
		</style>
		<div class="wrap ziteh-core" dir="rtl">
			<header class="ziteh-core__hero">
				<div class="ziteh-core__brand"><span class="ziteh-core__mark" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20C4 11 11 4 20 4c0 9-7 16-16 16z"/><path d="M4 20c4-6 8-9 12-11"/></svg></span><div><h1><?php esc_html_e( 'هسته زیته', 'ziteh' ); ?></h1><p><?php esc_html_e( 'مرکز فرمان طراحی، فروشگاه و قابلیت‌های زیته', 'ziteh' ); ?></p></div></div>
				<span class="ziteh-core__version"><?php echo esc_html( 'نسخه ' . ZITEH_EL_VERSION ); ?></span>
			</header>
			<?php if ( isset( $_GET['ziteh_purged'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'کش با موفقیت پاک شد.', 'ziteh' ); ?></p></div>
			<?php endif; ?>
			<div class="ziteh-core__stats">
				<div class="ziteh-stat"><div><strong>۱۸</strong><span><?php esc_html_e( 'ویجت فعال', 'ziteh' ); ?></span></div><i class="ziteh-stat__dot"></i></div>
				<div class="ziteh-stat"><div><strong><?php echo esc_html( $feature_count ); ?>/۸</strong><span><?php esc_html_e( 'قابلیت روشن', 'ziteh' ); ?></span></div><i class="ziteh-stat__dot"></i></div>
				<div class="ziteh-stat"><div><strong><?php echo class_exists( 'WooCommerce' ) ? esc_html__( 'متصل', 'ziteh' ) : esc_html__( 'خاموش', 'ziteh' ); ?></strong><span>WooCommerce</span></div><i class="ziteh-stat__dot"></i></div>
			</div>
			<form method="post" action="options.php">
				<?php settings_fields( 'ziteh_settings_group' ); ?>
				<div class="ziteh-core__layout">
					<nav class="ziteh-core__nav" aria-label="<?php esc_attr_e( 'بخش‌های تنظیمات', 'ziteh' ); ?>">
						<button type="button" class="ziteh-core__tab is-active" data-ziteh-tab="general"><?php esc_html_e( 'نمای کلی', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="design"><?php esc_html_e( 'طراحی و هویت', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="content"><?php esc_html_e( 'محتوای سراسری', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="features"><?php esc_html_e( 'امکانات', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="seo"><?php esc_html_e( 'سئو و ساختار', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="performance"><?php esc_html_e( 'کارایی و سازگاری', 'ziteh' ); ?></button>
						<button type="button" class="ziteh-core__tab" data-ziteh-tab="system"><?php esc_html_e( 'اطلاعات سیستم', 'ziteh' ); ?></button>
					</nav>
					<main>
						<section class="ziteh-panel is-active" data-ziteh-panel="general"><div class="ziteh-card"><h2><?php esc_html_e( 'هسته مرکزی زیته', 'ziteh' ); ?></h2><p class="ziteh-card__lead"><?php esc_html_e( 'این پنل نقطه واحد مدیریت ماژول‌های فعلی و توسعه‌های آینده زیته است. تنظیمات جدید از طریق API داخلی هسته قابل افزودن هستند.', 'ziteh' ); ?></p><div class="ziteh-grid"><?php $this->render_toggle( 'shell', $s, __( 'پوسته تعاملی', 'ziteh' ), __( 'زیرساخت مشترک مودال، جستجو و سبد کشویی را فعال می‌کند.', 'ziteh' ) ); ?><div class="ziteh-field"><strong><?php esc_html_e( 'معماری توسعه‌پذیر', 'ziteh' ); ?></strong><p><?php esc_html_e( 'ماژول‌های آینده می‌توانند تنظیمات، اعتبارسنجی و اطلاعات وضعیت خود را با هوک‌های هسته ثبت کنند.', 'ziteh' ); ?></p></div></div></div></section>
						<section class="ziteh-panel" data-ziteh-panel="design"><div class="ziteh-card"><h2><?php esc_html_e( 'سیستم طراحی', 'ziteh' ); ?></h2><p class="ziteh-card__lead"><?php esc_html_e( 'توکن‌های ظاهری تمام ویجت‌ها را یکجا تنظیم کنید.', 'ziteh' ); ?></p><div class="ziteh-grid"><?php foreach ( $fields as $key => $label ) : ?><div class="ziteh-field"><label for="ziteh-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label><input type="color" id="ziteh-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $s[ $key ] ); ?>"><code class="ziteh-color-value"><?php echo esc_html( $s[ $key ] ); ?></code></div><?php endforeach; ?><div class="ziteh-field"><label for="ziteh-font"><?php esc_html_e( 'خانواده فونت', 'ziteh' ); ?></label><select id="ziteh-font" name="<?php echo esc_attr( self::OPTION ); ?>[font]"><?php foreach ( array_keys( self::fonts() ) as $font ) : ?><option value="<?php echo esc_attr( $font ); ?>" <?php selected( $font, $s['font'] ); ?>><?php echo esc_html( $font ); ?></option><?php endforeach; ?></select><p><?php esc_html_e( 'فونت‌های غیر از وزیرمتن باید توسط قالب یا سایت بارگذاری شوند.', 'ziteh' ); ?></p></div><div class="ziteh-field"><label for="ziteh-radius"><?php esc_html_e( 'گردی کارت‌ها', 'ziteh' ); ?></label><input type="number" id="ziteh-radius" min="0" max="40" name="<?php echo esc_attr( self::OPTION ); ?>[radius]" value="<?php echo esc_attr( $s['radius'] ); ?>"><p><?php esc_html_e( 'مقدار بین ۰ تا ۴۰ پیکسل', 'ziteh' ); ?></p></div><div class="ziteh-field"><label for="ziteh-container"><?php esc_html_e( 'عرض محتوای سایت', 'ziteh' ); ?></label><input type="number" id="ziteh-container" min="960" max="1680" step="20" name="<?php echo esc_attr( self::OPTION ); ?>[container]" value="<?php echo esc_attr( $s['container'] ); ?>"><p><?php esc_html_e( 'بین ۹۶۰ تا ۱۶۸۰ پیکسل. ویجت‌های صفحه محصول و صفحات از همین مقدار استفاده می‌کنند.', 'ziteh' ); ?></p></div><div class="ziteh-field"><label for="ziteh-base-size"><?php esc_html_e( 'اندازه پایه متن', 'ziteh' ); ?></label><input type="number" id="ziteh-base-size" min="14" max="20" name="<?php echo esc_attr( self::OPTION ); ?>[base_size]" value="<?php echo esc_attr( $s['base_size'] ); ?>"><p><?php esc_html_e( 'بین ۱۴ تا ۲۰ پیکسل.', 'ziteh' ); ?></p></div><?php $this->render_toggle( 'external_font', $s, __( 'بارگذاری وزیرمتن از CDN', 'ziteh' ), __( 'در صورت میزبانی محلی فونت یا محدودیت حریم خصوصی، خاموش کنید.', 'ziteh' ) ); ?></div></div></section>
						<section class="ziteh-panel" data-ziteh-panel="features"><div class="ziteh-card"><h2><?php esc_html_e( 'مدیریت امکانات', 'ziteh' ); ?></h2><p class="ziteh-card__lead"><?php esc_html_e( 'هر قابلیت را مستقل از بقیه فعال یا غیرفعال کنید.', 'ziteh' ); ?></p><div class="ziteh-grid"><?php $this->render_toggle( 'quickview', $s, __( 'مشاهده سریع محصول', 'ziteh' ), __( 'نمایش جزئیات محصول در مودال AJAX.', 'ziteh' ) ); ?><?php $this->render_toggle( 'live_search', $s, __( 'جستجوی زنده', 'ziteh' ), __( 'نمایش نتایج محصولات یا نوشته‌ها هنگام تایپ.', 'ziteh' ) ); ?><?php $this->render_toggle( 'cart_drawer', $s, __( 'سبد خرید کشویی', 'ziteh' ), __( 'نمایش و بروزرسانی سبد WooCommerce بدون ترک صفحه.', 'ziteh' ) ); ?><?php $this->render_toggle( 'wishlist', $s, __( 'علاقه‌مندی مرورگر', 'ziteh' ), __( 'ذخیره محصولات منتخب کاربر در localStorage.', 'ziteh' ) ); ?><?php $this->render_toggle( 'quiz_products', $s, __( 'پیشنهاد محصول در کوییز', 'ziteh' ), __( 'پس از پایان کوییز، محصولات مرتبط را با AJAX پیشنهاد می‌دهد.', 'ziteh' ) ); ?><?php $this->render_toggle( 'sticky_header', $s, __( 'هدر چسبان', 'ziteh' ), __( 'هدر زیته هنگام اسکرول جمع و ثابت شود.', 'ziteh' ) ); ?><?php $this->render_toggle( 'animations', $s, __( 'انیمیشن ورود سکشن‌ها', 'ziteh' ), __( 'نمایش تدریجی سکشن‌ها با رعایت prefers-reduced-motion.', 'ziteh' ) ); ?></div></div></section>
						<section class="ziteh-panel" data-ziteh-panel="performance"><div class="ziteh-card"><h2><?php esc_html_e( 'بارگذاری و کارایی', 'ziteh' ); ?></h2><p class="ziteh-card__lead"><?php esc_html_e( 'رفتار assets و کش را متناسب با زیرساخت سایت انتخاب کنید.', 'ziteh' ); ?></p><div class="ziteh-grid"><div class="ziteh-field"><label for="ziteh-asset-mode"><?php esc_html_e( 'شیوه بارگذاری فایل‌ها', 'ziteh' ); ?></label><select id="ziteh-asset-mode" name="<?php echo esc_attr( self::OPTION ); ?>[asset_mode]"><option value="smart" <?php selected( 'smart', $s['asset_mode'] ); ?>><?php esc_html_e( 'هوشمند — فقط در صفحات موردنیاز', 'ziteh' ); ?></option><option value="global" <?php selected( 'global', $s['asset_mode'] ); ?>><?php esc_html_e( 'سراسری — بیشترین سازگاری', 'ziteh' ); ?></option></select><p><?php esc_html_e( 'حالت سراسری برای قالب‌های سفارشی یا Theme Builderهای پیچیده مناسب‌تر است.', 'ziteh' ); ?></p></div><?php $this->render_toggle( 'auto_cache_purge', $s, __( 'پاک‌سازی خودکار کش', 'ziteh' ), __( 'با ذخیره تنظیمات و تغییر نسخه اجرا می‌شود.', 'ziteh' ) ); ?><?php $this->render_toggle( 'product_isolation', $s, __( 'حالت ایزوله صفحه محصول', 'ziteh' ), __( 'استایل‌های پیش‌فرض WooCommerce و کانتینر قالب را فقط در صفحه محصول غیرفعال می‌کند تا UI کاملاً توسط زیته کنترل شود.', 'ziteh' ) ); ?><?php $this->render_toggle( 'product_app', $s, __( 'تجربه اپلیکیشنی صفحه محصول', 'ziteh' ), __( 'در موبایل گالری را قابل کشیدن می‌کند و نوار خرید چسبان، نمایشگر تمام‌صفحه تصاویر و تب‌های سگمنتی را فعال می‌کند.', 'ziteh' ) ); ?></div></div></section>
						<section class="ziteh-panel" data-ziteh-panel="system"><div class="ziteh-card"><h2><?php esc_html_e( 'وضعیت سیستم', 'ziteh' ); ?></h2><p class="ziteh-card__lead"><?php esc_html_e( 'اطلاعات پایه برای عیب‌یابی و پشتیبانی.', 'ziteh' ); ?></p><table class="ziteh-system"><tr><td>WordPress</td><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr><tr><td>PHP</td><td><?php echo esc_html( PHP_VERSION ); ?></td></tr><tr><td>Elementor</td><td><?php echo defined( 'ELEMENTOR_VERSION' ) ? esc_html( ELEMENTOR_VERSION ) : esc_html__( 'در دسترس نیست', 'ziteh' ); ?></td></tr><tr><td>WooCommerce</td><td><?php echo defined( 'WC_VERSION' ) ? esc_html( WC_VERSION ) : esc_html__( 'غیرفعال', 'ziteh' ); ?></td></tr><tr><td><?php esc_html_e( 'نسخه هسته زیته', 'ziteh' ); ?></td><td><?php echo esc_html( ZITEH_EL_VERSION ); ?></td></tr><tr><td><?php esc_html_e( 'حالت بارگذاری', 'ziteh' ); ?></td><td><?php echo esc_html( $s['asset_mode'] ); ?></td></tr></table><?php do_action( 'ziteh_core_settings_system', $s ); ?></div></section>
						<?php $this->render_content_panel( $s ); ?>
						<?php $this->render_seo_panel( $s ); ?>
						<?php do_action( 'ziteh_core_settings_panels', $s ); ?>
					</main>
				</div>
				<div class="ziteh-core__save"><p><?php esc_html_e( 'تغییرات پس از ذخیره روی تمام ویجت‌های زیته اعمال می‌شوند.', 'ziteh' ); ?></p><?php submit_button( __( 'ذخیره تنظیمات هسته', 'ziteh' ), 'primary', 'submit', false ); ?></div>
			</form>
			<?php if ( class_exists( 'Ziteh_Cache' ) ) : ?><div class="ziteh-core__tools"><strong><?php esc_html_e( 'ابزار نگه‌داری', 'ziteh' ); ?></strong><?php Ziteh_Cache::render_button(); ?></div><?php endif; ?>
		</div>
		<script>
		(function(){var tabs=document.querySelectorAll('[data-ziteh-tab]');var panels=document.querySelectorAll('[data-ziteh-panel]');tabs.forEach(function(tab){tab.addEventListener('click',function(){tabs.forEach(function(item){item.classList.remove('is-active');});panels.forEach(function(panel){panel.classList.remove('is-active');});tab.classList.add('is-active');var panel=document.querySelector('[data-ziteh-panel="'+tab.dataset.zitehTab+'"]');if(panel){panel.classList.add('is-active');history.replaceState(null,'','#'+tab.dataset.zitehTab);}});});var initial=location.hash.slice(1);if(initial){var target=document.querySelector('[data-ziteh-tab="'+initial+'"]');if(target){target.click();}}document.querySelectorAll('input[type="color"]').forEach(function(input){input.addEventListener('input',function(){var code=input.parentNode.querySelector('.ziteh-color-value');if(code){code.textContent=input.value;}});});})();
		</script>
		<?php
	}

	/**
	 * Text or textarea field bound to a settings key.
	 *
	 * @param string              $key         Setting key.
	 * @param array<string,mixed> $settings    Current settings.
	 * @param string              $label       Field label.
	 * @param string              $description Helper text.
	 * @param string              $type        text|email|url|textarea.
	 * @param string              $placeholder Placeholder text.
	 */
	private function render_text( $key, $settings, $label, $description = '', $type = 'text', $placeholder = '' ) {
		$value = isset( $settings[ $key ] ) ? $settings[ $key ] : '';
		$name  = self::OPTION . '[' . $key . ']';
		?>
		<div class="ziteh-field">
			<label for="ziteh-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php if ( 'textarea' === $type ) : ?>
				<textarea id="ziteh-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" id="ziteh-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>">
			<?php endif; ?>
			<?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Global content panel.
	 *
	 * One place for the details that otherwise get retyped into every widget:
	 * phone number, address, shipping promise. Widgets treat these as fallbacks,
	 * so anything already filled in on a widget keeps winning.
	 *
	 * @param array<string,mixed> $s Current settings.
	 */
	private function render_content_panel( $s ) {
		?>
		<section class="ziteh-panel" data-ziteh-panel="content">
			<div class="ziteh-card">
				<h2><?php esc_html_e( 'هویت و تماس', 'ziteh' ); ?></h2>
				<p class="ziteh-card__lead"><?php esc_html_e( 'این مقادیر یک‌بار اینجا نوشته می‌شوند و ویجت‌ها وقتی فیلد خودشان خالی باشد از همین‌ها استفاده می‌کنند. هرچه در خود ویجت نوشته باشید، اولویت دارد.', 'ziteh' ); ?></p>
				<div class="ziteh-grid">
					<?php
					$this->render_text( 'c_brand_name', $s, __( 'نام برند', 'ziteh' ), __( 'خالی بماند از نام سایت استفاده می‌شود.', 'ziteh' ), 'text', get_bloginfo( 'name' ) );
					$this->render_text( 'c_brand_tagline', $s, __( 'شعار کوتاه', 'ziteh' ), '', 'text', __( 'مراقبت از پوست و مو، با انتخاب درست', 'ziteh' ) );
					$this->render_text( 'c_phone', $s, __( 'تلفن پشتیبانی', 'ziteh' ), '', 'text', '۰۲۱-۱۲۳۴۵۶۷۸' );
					$this->render_text( 'c_email', $s, __( 'ایمیل', 'ziteh' ), __( 'فرم تماس مستقل از این مقدار به ایمیل مدیر سایت ارسال می‌کند.', 'ziteh' ), 'email', 'info@example.com' );
					$this->render_text( 'c_hours', $s, __( 'ساعات پاسخگویی', 'ziteh' ), '', 'text', __( 'شنبه تا چهارشنبه، ۹ تا ۱۷', 'ziteh' ) );
					$this->render_text( 'c_address', $s, __( 'نشانی', 'ziteh' ), '', 'textarea' );
					$this->render_text( 'c_map', $s, __( 'نشانی embed نقشه', 'ziteh' ), __( 'فقط لینک embed؛ بدون اسکریپت خارجی درج می‌شود.', 'ziteh' ), 'url', 'https://www.google.com/maps/embed?...' );
					$this->render_text( 'c_copyright', $s, __( 'متن کپی‌رایت', 'ziteh' ), '', 'text' );
					?>
				</div>
			</div>

			<div class="ziteh-card">
				<h2><?php esc_html_e( 'شبکه‌های اجتماعی', 'ziteh' ); ?></h2>
				<p class="ziteh-card__lead"><?php esc_html_e( 'نشانی کامل صفحه را وارد کنید. موارد خالی نمایش داده نمی‌شوند.', 'ziteh' ); ?></p>
				<div class="ziteh-grid">
					<?php
					$this->render_text( 'c_instagram', $s, __( 'اینستاگرام', 'ziteh' ), '', 'url', 'https://instagram.com/…' );
					$this->render_text( 'c_telegram', $s, __( 'تلگرام', 'ziteh' ), '', 'url', 'https://t.me/…' );
					$this->render_text( 'c_whatsapp', $s, __( 'واتساپ', 'ziteh' ), '', 'url', 'https://wa.me/…' );
					?>
				</div>
			</div>

			<div class="ziteh-card">
				<h2><?php esc_html_e( 'قول ارسال', 'ziteh' ); ?></h2>
				<p class="ziteh-card__lead"><?php esc_html_e( 'ردیف ارسال در صفحه محصول وقتی فیلدهای خودش را خالی بگذارید از این متن‌ها استفاده می‌کند — یعنی شرایط ارسال را یک‌بار برای کل فروشگاه می‌نویسید.', 'ziteh' ); ?></p>
				<div class="ziteh-grid">
					<?php
					$this->render_text( 'c_shipping_title', $s, __( 'عنوان', 'ziteh' ), '', 'text', __( 'ارسال سریع و مطمئن', 'ziteh' ) );
					$this->render_text( 'c_shipping_text', $s, __( 'توضیح کوتاه', 'ziteh' ), '', 'text', __( 'ارسال به سراسر ایران در ۲ تا ۳ روز کاری', 'ziteh' ) );
					$this->render_text( 'c_shipping_details', $s, __( 'جزئیات بازشونده', 'ziteh' ), '', 'textarea' );
					?>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Technical SEO panel.
	 *
	 * @param array<string,mixed> $s Current settings.
	 */
	private function render_seo_panel( $s ) {
		$plugin = self::seo_plugin();
		?>
		<section class="ziteh-panel" data-ziteh-panel="seo">
			<div class="ziteh-card">
				<h2><?php esc_html_e( 'داده‌های ساختاریافته', 'ziteh' ); ?></h2>

				<?php if ( $plugin ) : ?>
					<div class="ziteh-notice ziteh-notice--ok">
						<strong><?php echo esc_html( sprintf( /* translators: %s: plugin name */ __( '%s روی سایت فعال است.', 'ziteh' ), $plugin ) ); ?></strong>
						<p><?php esc_html_e( 'عنوان، توضیح متا، کنونیکال، نقشه سایت و داده‌های ساختاریافته کار همان افزونه است و زیته دستی به آن نمی‌زند. دو کلید زیر قفل شده‌اند چون خروجی دومِ Product یا BreadcrumbList روی یک صفحه، از نبودشان بدتر است.', 'ziteh' ); ?></p>
					</div>
				<?php else : ?>
					<div class="ziteh-notice">
						<strong><?php esc_html_e( 'هیچ افزونه سئویی شناسایی نشد.', 'ziteh' ); ?></strong>
						<p><?php esc_html_e( 'زیته یک افزونه سئو نیست و عنوان و متا تولید نمی‌کند. اگر افزونه‌ای مثل Rank Math یا Yoast نصب کنید، همین بخش خودش کنار می‌رود. تا آن زمان می‌توانید داده ساختاریافته زیته را روشن کنید.', 'ziteh' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="ziteh-grid">
					<?php
					$this->render_toggle( 'seo_product_schema', $s, __( 'Product schema زیته', 'ziteh' ), __( 'فقط زمانی روشن کنید که هیچ افزونه سئویی روی سایت نیست.', 'ziteh' ), (bool) $plugin );
					$this->render_toggle( 'seo_breadcrumb_schema', $s, __( 'BreadcrumbList schema زیته', 'ziteh' ), __( 'مسیر راهنمای ویجت‌های زیته را به‌صورت داده ساختاریافته منتشر می‌کند.', 'ziteh' ), (bool) $plugin );
					?>
				</div>
			</div>

			<div class="ziteh-card">
				<h2><?php esc_html_e( 'سرعت و تجربه', 'ziteh' ); ?></h2>
				<p class="ziteh-card__lead"><?php esc_html_e( 'این موارد با افزونه سئو تداخل ندارند؛ کاری است که افزونه‌های سئو انجام نمی‌دهند.', 'ziteh' ); ?></p>
				<div class="ziteh-grid">
					<?php $this->render_toggle( 'seo_preload_lcp', $s, __( 'پیش‌بارگذاری تصویر اصلی محصول', 'ziteh' ), __( 'تصویر شاخص محصول معمولاً بزرگ‌ترین عنصر قابل رنگ صفحه است. اعلام زودهنگام آن به مرورگر، معیار LCP را بهتر می‌کند.', 'ziteh' ) ); ?>
					<div class="ziteh-field">
						<strong><?php esc_html_e( 'چیزی که زیته انجام نمی‌دهد', 'ziteh' ); ?></strong>
						<p><?php esc_html_e( 'تولید عنوان و توضیح متا، ریدایرکت، نقشه سایت و تحلیل کلمه کلیدی. این‌ها کار افزونه سئوی شماست و دوباره‌کاری در آن‌ها به رتبه سایت آسیب می‌زند.', 'ziteh' ); ?></p>
					</div>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Render an accessible switch field used across settings panels.
	 *
	 * @param string              $key         Setting key.
	 * @param array<string,mixed> $settings    Current settings.
	 * @param string              $title       Field title.
	 * @param string              $description Helper text.
	 */
	private function render_toggle( $key, $settings, $title, $description, $locked = false ) {
		?>
		<div class="ziteh-field<?php echo $locked ? ' is-locked' : ''; ?>"><label class="ziteh-switch"><input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="on" <?php checked( 'on', isset( $settings[ $key ] ) ? $settings[ $key ] : 'off' ); ?> <?php disabled( true, $locked ); ?>><span class="ziteh-switch__rail" aria-hidden="true"></span><span class="ziteh-switch__copy"><strong><?php echo esc_html( $title ); ?></strong><small><?php echo esc_html( $description ); ?></small></span></label></div>
		<?php
	}

	/**
	 * Print CSS custom-property overrides so saved colours win over the defaults.
	 */
	public function print_vars() {
		$s = self::get();

		$css  = ':root{';
		$css .= '--ziteh-green:' . $s['green'] . ';';
		$css .= '--ziteh-green-dark:' . $s['green_dark'] . ';';
		$css .= '--ziteh-green-hover:' . $this->shade( $s['green'], -12 ) . ';';
		$css .= '--ziteh-green-soft:' . $this->tint( $s['green'], 82 ) . ';';
		$css .= '--ziteh-cream:' . $s['cream'] . ';';
		$css .= '--ziteh-ink:' . $s['ink'] . ';';
		$css .= '--ziteh-sale:' . $s['sale'] . ';';
		$css .= '--ziteh-radius:' . (int) $s['radius'] . 'px;';
		$css .= '--ziteh-page:' . (int) $s['container'] . 'px;';
		$css .= '--ziteh-base-size:' . (int) $s['base_size'] . 'px;';
		$fonts = self::fonts();
		if ( isset( $fonts[ $s['font'] ] ) ) {
			$css .= '--ziteh-font:' . $fonts[ $s['font'] ] . ';';
		}
		$css .= '}';

		if ( 'off' === $s['animations'] ) {
			$css .= '.ziteh-reveal{opacity:1!important;transform:none!important;}';
		}

		printf( "<style id='ziteh-vars'>%s</style>\n", $css ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Darken/lighten a hex colour by a percentage (-100..100).
	 *
	 * @param string $hex     Hex colour.
	 * @param int    $percent Negative = darker, positive = lighter.
	 * @return string
	 */
	private function shade( $hex, $percent ) {
		$rgb = $this->hex_to_rgb( $hex );
		if ( ! $rgb ) {
			return $hex;
		}
		foreach ( $rgb as &$c ) {
			$c = (int) max( 0, min( 255, $c + ( $c * $percent / 100 ) ) );
		}
		return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
	}

	/**
	 * Mix a colour towards white by a percentage (0..100 = amount of white).
	 *
	 * @param string $hex     Hex colour.
	 * @param int    $percent Amount of white mixed in.
	 * @return string
	 */
	private function tint( $hex, $percent ) {
		$rgb = $this->hex_to_rgb( $hex );
		if ( ! $rgb ) {
			return $hex;
		}
		foreach ( $rgb as &$c ) {
			$c = (int) round( $c + ( 255 - $c ) * ( $percent / 100 ) );
		}
		return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
	}

	/**
	 * Parse a #rrggbb string to [r,g,b].
	 *
	 * @param string $hex Hex colour.
	 * @return int[]|null
	 */
	private function hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return null;
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}
