<?php
/**
 * Settings: schema, defaults, storage and sanitisation.
 *
 * Everything that is a "feature" of the store (tiered discounts, samples,
 * checkout fields, shipping options, tracking stages, mobile shell…) lives in
 * one option ("zt_settings") described by the schema below. The admin screen,
 * defaults and sanitisation are all generated from this single schema.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Settings
 */
class ZT_Settings {

	const OPTION = 'zt_settings';

	/**
	 * Cached merged settings.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Cached schema.
	 *
	 * @var array|null
	 */
	private static $schema = null;

	/**
	 * Get a value by dot path.
	 *
	 * @param string $path    Path.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	public static function get( $path, $default = null ) {
		$all = self::all();
		foreach ( explode( '.', $path ) as $seg ) {
			if ( is_array( $all ) && array_key_exists( $seg, $all ) ) {
				$all = $all[ $seg ];
			} else {
				return $default;
			}
		}
		return $all;
	}

	/**
	 * All settings merged over defaults.
	 *
	 * @return array
	 */
	public static function all() {
		if ( null === self::$cache ) {
			$saved = get_option( self::OPTION, array() );
			$saved = is_array( $saved ) ? $saved : array();
			$defs  = self::defaults();
			$out   = array();
			foreach ( $defs as $tab => $fields ) {
				$out[ $tab ] = isset( $saved[ $tab ] ) && is_array( $saved[ $tab ] ) ? array_merge( $fields, $saved[ $tab ] ) : $fields;
			}
			self::$cache = $out;
		}
		return self::$cache;
	}

	/**
	 * Save a whole tab (already sanitised) or a single value.
	 *
	 * @param string $tab    Tab key.
	 * @param array  $values Values.
	 */
	public static function update_tab( $tab, $values ) {
		$saved         = get_option( self::OPTION, array() );
		$saved         = is_array( $saved ) ? $saved : array();
		$saved[ $tab ] = $values;
		update_option( self::OPTION, $saved, true );
		self::$cache = null;
		do_action( 'zt_settings_saved', $tab );
	}

	/**
	 * Set one value by path (tab.key).
	 *
	 * @param string $path  Path.
	 * @param mixed  $value Value.
	 */
	public static function set( $path, $value ) {
		list( $tab, $key ) = array_pad( explode( '.', $path, 2 ), 2, '' );
		$saved = get_option( self::OPTION, array() );
		$saved = is_array( $saved ) ? $saved : array();
		if ( ! isset( $saved[ $tab ] ) || ! is_array( $saved[ $tab ] ) ) {
			$saved[ $tab ] = array();
		}
		$saved[ $tab ][ $key ] = $value;
		update_option( self::OPTION, $saved, true );
		self::$cache = null;
	}

	/**
	 * Flush cache.
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Default values generated from the schema.
	 *
	 * @return array
	 */
	public static function defaults() {
		$out = array();
		foreach ( self::schema() as $tab_key => $tab ) {
			$out[ $tab_key ] = array();
			foreach ( $tab['sections'] as $section ) {
				foreach ( $section['fields'] as $f ) {
					if ( isset( $f['key'] ) ) {
						$out[ $tab_key ][ $f['key'] ] = isset( $f['default'] ) ? $f['default'] : '';
					}
				}
			}
		}
		return $out;
	}

	/**
	 * Sanitise the posted values of a tab according to the schema.
	 *
	 * @param string $tab_key Tab.
	 * @param array  $input   Raw posted values.
	 * @return array
	 */
	public static function sanitize_tab( $tab_key, $input ) {
		$schema = self::schema();
		if ( ! isset( $schema[ $tab_key ] ) ) {
			return array();
		}
		$out = array();
		foreach ( $schema[ $tab_key ]['sections'] as $section ) {
			foreach ( $section['fields'] as $f ) {
				if ( empty( $f['key'] ) ) {
					continue;
				}
				$raw                = isset( $input[ $f['key'] ] ) ? $input[ $f['key'] ] : null;
				$out[ $f['key'] ] = self::sanitize_field( $f, $raw );
			}
		}
		return $out;
	}

	/**
	 * Sanitise a single field value.
	 *
	 * @param array $f   Field schema.
	 * @param mixed $raw Raw.
	 * @return mixed
	 */
	public static function sanitize_field( $f, $raw ) {
		$type = isset( $f['type'] ) ? $f['type'] : 'text';
		switch ( $type ) {
			case 'toggle':
				return ! empty( $raw ) ? 1 : 0;
			case 'number':
				return is_numeric( zt_en( (string) $raw ) ) ? 0 + zt_en( (string) $raw ) : ( isset( $f['default'] ) ? $f['default'] : 0 );
			case 'color':
				$c = sanitize_hex_color( (string) $raw );
				if ( ! $c && preg_match( '/^rgba?\([\d\s.,%]+\)$/', (string) $raw ) ) {
					$c = (string) $raw;
				}
				return $c ? $c : '';
			case 'page':
			case 'template':
				return absint( $raw );
			case 'select':
				$opts = isset( $f['options'] ) ? array_keys( $f['options'] ) : array();
				return in_array( (string) $raw, array_map( 'strval', $opts ), true ) ? (string) $raw : ( isset( $f['default'] ) ? $f['default'] : '' );
			case 'textarea':
			case 'lines':
				return sanitize_textarea_field( (string) $raw );
			case 'html':
				return wp_kses_post( (string) $raw );
			case 'code':
				return trim( wp_unslash( (string) $raw ) ) === '' ? '' : (string) $raw;
			case 'media':
			case 'url':
				return esc_url_raw( (string) $raw );
			case 'repeater':
				$rows = array();
				if ( is_array( $raw ) ) {
					foreach ( $raw as $row ) {
						if ( ! is_array( $row ) || isset( $row['__tpl'] ) ) {
							continue;
						}
						$clean = array();
						foreach ( $f['fields'] as $sub ) {
							$clean[ $sub['key'] ] = self::sanitize_field( $sub, isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : null );
						}
						$rows[] = $clean;
					}
				}
				return $rows;
			case 'icon':
				return sanitize_key( (string) $raw );
			default:
				return sanitize_text_field( (string) $raw );
		}
	}

	/**
	 * Icon choices for selects.
	 *
	 * @return array
	 */
	public static function icon_choices() {
		static $c = null;
		if ( null === $c ) {
			$c    = array( '' => '—' );
			$json = json_decode( (string) file_get_contents( ZT_PATH . 'assets/icons/icons.json' ), true );
			foreach ( (array) ( isset( $json['icons'] ) ? $json['icons'] : array() ) as $n ) {
				$c[ $n ] = $n;
			}
		}
		return $c;
	}

	/**
	 * The schema.
	 *
	 * @return array
	 */
	public static function schema() {
		if ( null !== self::$schema ) {
			return self::$schema;
		}
		$icon = array( 'type' => 'icon' );

		$s = array();

		/* ------------------------------------------------------------ general */
		$s['general'] = array(
			'title'    => 'عمومی و ظاهر',
			'icon'     => 'dashicons-admin-appearance',
			'sections' => array(
				array(
					'title'  => 'هماهنگی با قالب',
					'desc'   => 'این افزونه برای قالب Hello Elementor طراحی شده است و استایل‌های پیش‌فرض آن را غیرفعال می‌کند تا ظاهر سایت دقیقاً مطابق طرح باشد.',
					'fields' => array(
						array( 'key' => 'enable_design', 'type' => 'toggle', 'label' => 'فعال‌سازی سیستم طراحی زیته روی کل سایت', 'default' => 1, 'help' => 'فونت دانا، رنگ پس‌زمینه و استایل پایه روی تمام صفحات اعمال می‌شود.' ),
						array( 'key' => 'disable_hello_styles', 'type' => 'toggle', 'label' => 'غیرفعال کردن تمام استایل‌های پیش‌فرض Hello Elementor', 'default' => 1 ),
						array( 'key' => 'disable_hello_header_footer', 'type' => 'toggle', 'label' => 'غیرفعال کردن هدر، فوتر و عنوان صفحه‌ی Hello', 'default' => 1 ),
						array( 'key' => 'disable_wc_styles', 'type' => 'toggle', 'label' => 'غیرفعال کردن استایل‌های پیش‌فرض ووکامرس (استایل زیته جایگزین می‌شود)', 'default' => 1 ),
						array( 'key' => 'disable_elementor_defaults', 'type' => 'toggle', 'label' => 'غیرفعال کردن رنگ‌ها و فونت‌های پیش‌فرض المنتور', 'default' => 1 ),
						array( 'key' => 'sitewide_header_footer', 'type' => 'toggle', 'label' => 'نمایش هدر و فوتر زیته در تمام صفحات سایت', 'default' => 1 ),
					),
				),
				array(
					'title'  => 'برند و واحد پول',
					'fields' => array(
						array( 'key' => 'brand_name', 'type' => 'text', 'label' => 'نام برند (لوگوی متنی)', 'default' => 'زیته' ),
						array( 'key' => 'logo_image', 'type' => 'media', 'label' => 'لوگوی تصویری (اختیاری — جایگزین لوگوی متنی)', 'default' => '' ),
						array( 'key' => 'currency_label', 'type' => 'text', 'label' => 'برچسب واحد پول', 'default' => 'تومان' ),
						array( 'key' => 'price_divisor', 'type' => 'number', 'label' => 'تقسیم مبالغ فروشگاه بر', 'default' => 1, 'help' => 'اگر واحد پول ووکامرس «ریال» است و می‌خواهید «تومان» نمایش داده شود، ۱۰ وارد کنید.' ),
						array( 'key' => 'persian_digits', 'type' => 'toggle', 'label' => 'نمایش اعداد به فارسی', 'default' => 1 ),
					),
				),
				array(
					'title'  => 'رنگ‌ها (توکن‌های طراحی)',
					'desc'   => 'مقادیر پیش‌فرض دقیقاً از فایل‌های طرح استخراج شده‌اند.',
					'fields' => array(
						array( 'key' => 'c_green', 'type' => 'color', 'label' => 'رنگ اصلی برند', 'default' => '#6B7457', 'var' => 'green' ),
						array( 'key' => 'c_green_600', 'type' => 'color', 'label' => 'رنگ اصلی — هاور', 'default' => '#5D6749', 'var' => 'green-600' ),
						array( 'key' => 'c_green_700', 'type' => 'color', 'label' => 'رنگ اصلی — تیره', 'default' => '#4E5740', 'var' => 'green-700' ),
						array( 'key' => 'c_green_050', 'type' => 'color', 'label' => 'سبز خیلی روشن', 'default' => '#F1F3EB', 'var' => 'green-050' ),
						array( 'key' => 'c_green_100', 'type' => 'color', 'label' => 'سبز روشن', 'default' => '#E5E9DA', 'var' => 'green-100' ),
						array( 'key' => 'c_green_200', 'type' => 'color', 'label' => 'سبز ملایم', 'default' => '#D3D9C4', 'var' => 'green-200' ),
						array( 'key' => 'c_paper', 'type' => 'color', 'label' => 'پس‌زمینه صفحه', 'default' => '#FAF8F5', 'var' => 'paper' ),
						array( 'key' => 'c_cream', 'type' => 'color', 'label' => 'کرم', 'default' => '#F6F2EA', 'var' => 'cream' ),
						array( 'key' => 'c_cream_2', 'type' => 'color', 'label' => 'کرم ۲', 'default' => '#F1EADF', 'var' => 'cream-2' ),
						array( 'key' => 'c_cream_3', 'type' => 'color', 'label' => 'کرم ۳', 'default' => '#EBE2D4', 'var' => 'cream-3' ),
						array( 'key' => 'c_line', 'type' => 'color', 'label' => 'خطوط', 'default' => '#EEE8DE', 'var' => 'line' ),
						array( 'key' => 'c_line_2', 'type' => 'color', 'label' => 'خطوط ۲', 'default' => '#E3DACC', 'var' => 'line-2' ),
						array( 'key' => 'c_ink', 'type' => 'color', 'label' => 'متن اصلی', 'default' => '#2E2E29', 'var' => 'ink' ),
						array( 'key' => 'c_ink_2', 'type' => 'color', 'label' => 'متن ثانویه', 'default' => '#55544C', 'var' => 'ink-2' ),
						array( 'key' => 'c_muted', 'type' => 'color', 'label' => 'متن کم‌رنگ', 'default' => '#9C968A', 'var' => 'muted' ),
						array( 'key' => 'c_muted_2', 'type' => 'color', 'label' => 'متن خیلی کم‌رنگ', 'default' => '#B3ACA0', 'var' => 'muted-2' ),
						array( 'key' => 'c_star', 'type' => 'color', 'label' => 'ستاره‌ها', 'default' => '#EFA92E', 'var' => 'star' ),
						array( 'key' => 'c_red', 'type' => 'color', 'label' => 'قرمز هشدار', 'default' => '#C4552A', 'var' => 'red' ),
					),
				),
				array(
					'title'  => 'ابعاد و تایپوگرافی',
					'fields' => array(
						array( 'key' => 'font', 'type' => 'select', 'label' => 'فونت', 'default' => 'dana', 'options' => array( 'dana' => 'دانا (همراه افزونه)', 'theme' => 'فونت قالب / المنتور' ) ),
						array( 'key' => 'container', 'type' => 'number', 'label' => 'عرض کانتینر (px)', 'default' => 1440 ),
						array( 'key' => 'container_narrow', 'type' => 'number', 'label' => 'عرض کانتینر باریک (px)', 'default' => 1240 ),
						array( 'key' => 'gutter', 'type' => 'number', 'label' => 'فاصله کناری کانتینر (px)', 'default' => 24 ),
					),
				),
				array(
					'title'  => 'حرکت و جلوه‌ها',
					'fields' => array(
						array( 'key' => 'fx_reveal', 'type' => 'toggle', 'label' => 'ظاهر شدن نرم بخش‌ها هنگام اسکرول', 'default' => 1 ),
						array( 'key' => 'fx_ripple', 'type' => 'toggle', 'label' => 'افکت ریپل روی دکمه‌ها', 'default' => 1 ),
						array( 'key' => 'fx_skeleton', 'type' => 'toggle', 'label' => 'اسکلتون شیمر تا لود تصاویر', 'default' => 1 ),
						array( 'key' => 'fx_progress', 'type' => 'toggle', 'label' => 'نوار پیشرفت اسکرول', 'default' => 1 ),
						array( 'key' => 'fx_totop', 'type' => 'toggle', 'label' => 'دکمه‌ی بازگشت به بالا', 'default' => 1 ),
						array( 'key' => 'fx_transitions', 'type' => 'toggle', 'label' => 'ترنزیشن بین صفحات (View Transitions)', 'default' => 1 ),
						array( 'key' => 'fx_option_toasts', 'type' => 'toggle', 'label' => 'پیام کوتاه هنگام انتخاب گزینه‌ها (ارسال/پرداخت/سمپل)', 'default' => 1 ),
					),
				),
			),
		);

		/* -------------------------------------------------------------- pages */
		$page = array( 'type' => 'page' );
		$tpl  = array( 'type' => 'template' );
		$s['pages'] = array(
			'title'    => 'صفحات و قالب‌ها',
			'icon'     => 'dashicons-admin-page',
			'sections' => array(
				array(
					'title'  => 'صفحات سایت',
					'desc'   => 'با دکمه‌ی «ساخت خودکار» در تب ابزارها همه‌ی این صفحات ساخته و اینجا تنظیم می‌شوند. می‌توانید هر صفحه را دستی هم انتخاب کنید.',
					'fields' => array(
						array( 'key' => 'home', 'label' => 'صفحه اصلی' ) + $page,
						array( 'key' => 'shop', 'label' => 'فروشگاه' ) + $page,
						array( 'key' => 'cart', 'label' => 'سبد خرید' ) + $page,
						array( 'key' => 'checkout', 'label' => 'صورت‌حساب' ) + $page,
						array( 'key' => 'account', 'label' => 'پنل کاربری' ) + $page,
						array( 'key' => 'tracking', 'label' => 'پیگیری سفارش' ) + $page,
						array( 'key' => 'routine', 'label' => 'راهنمای روتین' ) + $page,
						array( 'key' => 'blog', 'label' => 'مجله (وبلاگ)' ) + $page,
						array( 'key' => 'about', 'label' => 'درباره ما' ) + $page,
						array( 'key' => 'contact', 'label' => 'تماس با ما' ) + $page,
						array( 'key' => 'terms', 'label' => 'قوانین و شرایط' ) + $page,
					),
				),
				array(
					'title'  => 'قالب‌های پیش‌فرض (سیستم قالب زیته)',
					'desc'   => 'قالب‌ها در منوی «زیته ← قالب‌ها» با المنتور ویرایش می‌شوند و به‌صورت داینامیک برای همه‌ی محصولات/نوشته‌ها استفاده می‌شوند.',
					'fields' => array(
						array( 'key' => 'tpl_header', 'label' => 'هدر سراسری' ) + $tpl,
						array( 'key' => 'tpl_footer', 'label' => 'فوتر سراسری' ) + $tpl,
						array( 'key' => 'tpl_single_product', 'label' => 'قالب تک‌محصول (پیش‌فرض)' ) + $tpl,
						array( 'key' => 'tpl_product_archive', 'label' => 'قالب آرشیو محصولات / دسته‌ها' ) + $tpl,
						array( 'key' => 'tpl_single_post', 'label' => 'قالب تک‌نوشته (مقاله)' ) + $tpl,
						array( 'key' => 'tpl_post_archive', 'label' => 'قالب آرشیو نوشته‌ها / دسته‌ها' ) + $tpl,
						array( 'key' => 'tpl_404', 'label' => 'قالب صفحه ۴۰۴' ) + $tpl,
						array( 'key' => 'tpl_search', 'label' => 'قالب نتایج جستجو' ) + $tpl,
					),
				),
			),
		);

		/* -------------------------------------------------------------- shell */
		$s['shell'] = array(
			'title'    => 'اپلیکیشن موبایل',
			'icon'     => 'dashicons-smartphone',
			'sections' => array(
				array(
					'title'  => 'اسکلت اپلیکیشنی (زیر ۹۰۰ پیکسل)',
					'fields' => array(
						array( 'key' => 'enabled', 'type' => 'toggle', 'label' => 'فعال‌سازی اپ‌بار، تب‌بار، دراور و جستجوی تمام‌صفحه روی موبایل', 'default' => 1 ),
						array( 'key' => 'hide_appbar_on_scroll', 'type' => 'toggle', 'label' => 'پنهان شدن اپ‌بار هنگام اسکرول به پایین', 'default' => 1 ),
						array( 'key' => 'actionbar', 'type' => 'toggle', 'label' => 'نوار خرید چسبان پایین در محصول / سبد / صورت‌حساب', 'default' => 1 ),
					),
				),
				array(
					'title'  => 'تب‌بار پایین',
					'fields' => array(
						array(
							'key'     => 'tabs',
							'type'    => 'repeater',
							'label'   => 'تب‌ها',
							'title'   => 'label',
							'fields'  => array(
								array( 'key' => 'key', 'type' => 'select', 'label' => 'نوع', 'options' => array( 'home' => 'خانه', 'shop' => 'فروشگاه', 'cart' => 'سبد (دکمه برجسته)', 'wish' => 'علاقه‌مندی', 'account' => 'حساب', 'custom' => 'دلخواه' ) ),
								array( 'key' => 'label', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'url', 'type' => 'text', 'label' => 'لینک (توکن مجاز)' ),
							),
							'default' => array(
								array( 'key' => 'home', 'label' => 'خانه', 'icon' => 'home', 'url' => '{{home}}' ),
								array( 'key' => 'shop', 'label' => 'فروشگاه', 'icon' => 'store', 'url' => '{{shop}}' ),
								array( 'key' => 'cart', 'label' => 'سبد', 'icon' => 'bag', 'url' => '{{cart}}' ),
								array( 'key' => 'wish', 'label' => 'علاقه‌مندی', 'icon' => 'heart', 'url' => '{{wishlist}}' ),
								array( 'key' => 'account', 'label' => 'حساب', 'icon' => 'user', 'url' => '{{account}}' ),
							),
						),
					),
				),
				array(
					'title'  => 'دراور کشویی (منوی موبایل)',
					'fields' => array(
						array( 'key' => 'drawer_title', 'type' => 'text', 'label' => 'عنوان کاربر مهمان', 'default' => 'خوش آمدید' ),
						array( 'key' => 'drawer_sub', 'type' => 'text', 'label' => 'زیرعنوان کاربر مهمان', 'default' => 'وارد شوید یا ثبت‌نام کنید' ),
						array(
							'key'     => 'drawer_menu',
							'type'    => 'repeater',
							'label'   => 'آیتم‌های منو',
							'title'   => 'label',
							'fields'  => array(
								array( 'key' => 'label', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'url', 'type' => 'text', 'label' => 'لینک' ),
								array( 'key' => 'children', 'type' => 'lines', 'label' => 'زیرمنو (هر خط: عنوان|لینک) — خالی = بدون زیرمنو. «auto» = دسته‌بندی‌های محصول' ),
							),
							'default' => array(
								array( 'label' => 'خانه', 'icon' => 'home', 'url' => '{{home}}', 'children' => '' ),
								array( 'label' => 'فروشگاه', 'icon' => 'store', 'url' => '{{shop}}', 'children' => '' ),
								array( 'label' => 'دسته‌بندی‌ها', 'icon' => 'grid', 'url' => '', 'children' => "مراقبت پوست|{{shop}}\nمراقبت مو|{{shop}}\nمراقبت کودک|{{shop}}\nبهداشت فردی|{{shop}}\nسلامت دندان|{{shop}}\nمراقبت دور چشم|{{shop}}" ),
								array( 'label' => 'پیگیری سفارش', 'icon' => 'truck-fast', 'url' => '{{tracking}}', 'children' => '' ),
								array( 'label' => 'تخفیف‌های ویژه', 'icon' => 'tag', 'url' => '{{shop}}?on_sale=1', 'children' => '' ),
								array( 'label' => 'مجله زیته', 'icon' => 'leaf', 'url' => '{{blog}}', 'children' => '' ),
								array( 'label' => 'درباره ما', 'icon' => 'info', 'url' => '{{about}}', 'children' => '' ),
								array( 'label' => 'تماس با ما', 'icon' => 'headset', 'url' => '{{contact}}', 'children' => '' ),
							),
						),
						array(
							'key'     => 'drawer_socials',
							'type'    => 'repeater',
							'label'   => 'شبکه‌های اجتماعی دراور',
							'title'   => 'label',
							'fields'  => array(
								array( 'key' => 'label', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'url', 'type' => 'text', 'label' => 'لینک' ),
							),
							'default' => array(
								array( 'label' => 'اینستاگرام', 'icon' => 'instagram', 'url' => '#' ),
								array( 'label' => 'تلگرام', 'icon' => 'telegram', 'url' => '#' ),
								array( 'label' => 'واتساپ', 'icon' => 'whatsapp', 'url' => '#' ),
							),
						),
						array( 'key' => 'drawer_footer', 'type' => 'text', 'label' => 'متن پایین دراور', 'default' => 'زیته — جوانه‌ای برای مراقبت از خودت' ),
					),
				),
				array(
					'title'  => 'جستجوی تمام‌صفحه',
					'fields' => array(
						array( 'key' => 'search_placeholder', 'type' => 'text', 'label' => 'متن راهنما', 'default' => 'دنبال چه محصولی می‌گردی؟' ),
						array( 'key' => 'search_recent_title', 'type' => 'text', 'label' => 'عنوان جستجوهای اخیر', 'default' => 'جستجوهای اخیر' ),
						array( 'key' => 'search_recent_default', 'type' => 'lines', 'label' => 'جستجوهای اخیر پیش‌فرض (برای کاربر جدید)', 'default' => "شامپو ضد ریزش\nسرم ویتامین C\nضد آفتاب\nماسک مو" ),
						array( 'key' => 'search_popular_title', 'type' => 'text', 'label' => 'عنوان پرجستجوترین‌ها', 'default' => 'پرجستجوترین‌ها' ),
						array( 'key' => 'search_popular', 'type' => 'lines', 'label' => 'پرجستجوترین‌ها (هر خط یک مورد)', 'default' => "تونر آبرسان\nکرم مرطوب‌کننده\nمام ضد عرق\nعطر خانه\nپک هدیه" ),
						array( 'key' => 'search_empty', 'type' => 'text', 'label' => 'پیام نبود نتیجه', 'default' => 'چیزی پیدا نشد. یک عبارت دیگر امتحان کنید.' ),
						array( 'key' => 'search_limit', 'type' => 'number', 'label' => 'حداکثر نتایج', 'default' => 12 ),
					),
				),
				array(
					'title'  => 'شیت مشاوره (واتساپ / تلگرام / بله)',
					'fields' => array(
						array( 'key' => 'advice_title', 'type' => 'text', 'label' => 'عنوان', 'default' => 'مشاوره پوستی رایگان' ),
						array( 'key' => 'advice_text', 'type' => 'text', 'label' => 'توضیح', 'default' => 'سؤالت را از هر کدام از راه‌های زیر بپرس؛ در ساعات کاری پاسخ می‌دهیم.' ),
						array(
							'key'     => 'advice_items',
							'type'    => 'repeater',
							'label'   => 'راه‌های ارتباطی',
							'title'   => 'title',
							'fields'  => array(
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'sub', 'type' => 'text', 'label' => 'زیرعنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'url', 'type' => 'text', 'label' => 'لینک' ),
							),
							'default' => array(
								array( 'title' => 'واتساپ', 'sub' => '۰۹۱۲ ۱۲۳ ۴۵۶۷', 'icon' => 'whatsapp', 'url' => 'https://wa.me/989121234567' ),
								array( 'title' => 'تلگرام', 'sub' => 'ziteh@', 'icon' => 'telegram', 'url' => 'https://t.me/ziteh' ),
								array( 'title' => 'بله', 'sub' => 'ziteh@', 'icon' => 'headset', 'url' => 'https://ble.ir/ziteh' ),
							),
						),
					),
				),
				array(
					'title'  => 'متن‌های تعاملی',
					'fields' => array(
						array( 'key' => 'qa_note', 'type' => 'text', 'label' => 'یادداشت شیت افزودن سریع', 'default' => 'ارسال سریع، تحویل ۱ تا ۳ روز کاری' ),
						array( 'key' => 'toast_added', 'type' => 'text', 'label' => 'پیام افزودن به سبد ({n} = تعداد)', 'default' => '{n} عدد به سبد خرید اضافه شد' ),
						array( 'key' => 'toast_view_cart', 'type' => 'text', 'label' => 'دکمه‌ی پیام', 'default' => 'مشاهده سبد' ),
						array( 'key' => 'ab_product', 'type' => 'text', 'label' => 'نوار پایین محصول: دکمه', 'default' => 'افزودن به سبد' ),
						array( 'key' => 'ab_cart', 'type' => 'text', 'label' => 'نوار پایین سبد: دکمه', 'default' => 'ثبت سفارش' ),
						array( 'key' => 'ab_checkout', 'type' => 'text', 'label' => 'نوار پایین صورت‌حساب: دکمه', 'default' => 'پرداخت' ),
						array( 'key' => 'ab_total', 'type' => 'text', 'label' => 'برچسب مبلغ', 'default' => 'مبلغ قابل پرداخت' ),
					),
				),
			),
		);

		/* --------------------------------------------------------------- cart */
		$s['cart'] = array(
			'title'    => 'سبد خرید',
			'icon'     => 'dashicons-cart',
			'sections' => array(
				array(
					'title'  => 'تخفیف پلکانی',
					'desc'   => 'هر پله با رسیدن جمع سبد به مبلغ تعیین‌شده فعال می‌شود. «ارسال رایگان» هزینه‌ی روش‌های ارسال را صفر می‌کند. بالاترین درصد/مبلغ فعال اعمال می‌شود.',
					'fields' => array(
						array( 'key' => 'tiers_enabled', 'type' => 'toggle', 'label' => 'فعال', 'default' => 1 ),
						array(
							'key'     => 'tiers',
							'type'    => 'repeater',
							'label'   => 'پله‌ها',
							'title'   => 'title',
							'fields'  => array(
								array( 'key' => 'amount', 'type' => 'number', 'label' => 'حداقل مبلغ سبد (تومان)' ),
								array( 'key' => 'type', 'type' => 'select', 'label' => 'نوع', 'options' => array( 'percent' => 'درصد تخفیف', 'fixed' => 'مبلغ ثابت تخفیف', 'free_shipping' => 'ارسال رایگان' ) ),
								array( 'key' => 'value', 'type' => 'number', 'label' => 'مقدار (درصد یا مبلغ)' ),
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان (مثلا ۵٪ تخفیف)' ),
								array( 'key' => 'sub', 'type' => 'text', 'label' => 'زیرعنوان (خالی = «با خرید X تومان»)' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
							),
							'default' => array(
								array( 'amount' => 5000000, 'type' => 'free_shipping', 'value' => 0, 'title' => 'ارسال رایگان', 'sub' => '', 'icon' => 'truck-fast' ),
								array( 'amount' => 4000000, 'type' => 'percent', 'value' => 5, 'title' => '۵٪ تخفیف', 'sub' => '', 'icon' => 'percent' ),
								array( 'amount' => 2500000, 'type' => 'percent', 'value' => 3, 'title' => '۳٪ تخفیف', 'sub' => '', 'icon' => 'tag' ),
							),
						),
						array( 'key' => 'tier_fee_label', 'type' => 'text', 'label' => 'عنوان ردیف تخفیف در فاکتور', 'default' => 'تخفیف پلکانی' ),
						array( 'key' => 'tier_msg_next', 'type' => 'text', 'label' => 'پیام پله بعد ({amount} و {label})', 'default' => 'شما تا {amount} تومان دیگر تا دریافت {label} فاصله دارید' ),
						array( 'key' => 'tier_msg_done', 'type' => 'text', 'label' => 'پیام تکمیل همه پله‌ها', 'default' => 'تبریک! بیشترین تخفیف و ارسال رایگان برای شما فعال شد.' ),
						array( 'key' => 'tier_exclude_sale', 'type' => 'toggle', 'label' => 'محاسبه تخفیف پلکانی فقط روی محصولات بدون حراج', 'default' => 0 ),
					),
				),
				array(
					'title'  => 'سمپل هدیه',
					'fields' => array(
						array( 'key' => 'samples_enabled', 'type' => 'toggle', 'label' => 'فعال', 'default' => 1 ),
						array( 'key' => 'samples_min', 'type' => 'number', 'label' => 'حداقل مبلغ سبد برای انتخاب سمپل (تومان)', 'default' => 1000000 ),
						array( 'key' => 'samples_note', 'type' => 'text', 'label' => 'متن کارت راهنما ({amount})', 'default' => 'با خرید بیش از {amount} تومان یک سمپل هدیه انتخاب کنید.' ),
						array( 'key' => 'samples_locked', 'type' => 'text', 'label' => 'پیام وقتی مبلغ کافی نیست', 'default' => 'برای انتخاب سمپل هدیه، جمع سبد باید حداقل {amount} تومان باشد.' ),
						array( 'key' => 'samples_line_name', 'type' => 'text', 'label' => 'عنوان ردیف سمپل در سفارش', 'default' => 'نمونه هدیه (سمپل)' ),
						array(
							'key'     => 'samples',
							'type'    => 'repeater',
							'label'   => 'سمپل‌ها',
							'title'   => 'title',
							'fields'  => array(
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'size', 'type' => 'text', 'label' => 'حجم' ),
								array( 'key' => 'image', 'type' => 'media', 'label' => 'تصویر' ),
								array( 'key' => 'product_id', 'type' => 'number', 'label' => 'شناسه محصول مرتبط (اختیاری — برای کسر موجودی)' ),
							),
							'default' => array(
								array( 'title' => 'کرم مرطوب‌کننده آلوئه ورا', 'size' => '۱۵ میل', 'image' => zt_asset_img( 'gift-1.jpg' ), 'product_id' => 0 ),
								array( 'title' => 'ژل شستشوی صورت', 'size' => '۳۰ میل', 'image' => zt_asset_img( 'gift-2.jpg' ), 'product_id' => 0 ),
								array( 'title' => 'شامپو گیاهی رزماری', 'size' => '۳۰ میل', 'image' => zt_asset_img( 'gift-3.jpg' ), 'product_id' => 0 ),
								array( 'title' => 'سرم ویتامین C', 'size' => '۱۰ میل', 'image' => zt_asset_img( 'gift-4.jpg' ), 'product_id' => 0 ),
								array( 'title' => 'کرم دست گل یاس', 'size' => '۳۰ میل', 'image' => zt_asset_img( 'gift-5.jpg' ), 'product_id' => 0 ),
							),
						),
					),
				),
				array(
					'title'  => 'متن‌ها',
					'fields' => array(
						array( 'key' => 'empty_title', 'type' => 'text', 'label' => 'عنوان سبد خالی', 'default' => 'سبد خرید شما خالی است' ),
						array( 'key' => 'empty_text', 'type' => 'text', 'label' => 'متن سبد خالی', 'default' => 'محصولات مورد علاقه‌تان را انتخاب کنید تا اینجا نمایش داده شوند.' ),
						array( 'key' => 'empty_button', 'type' => 'text', 'label' => 'دکمه سبد خالی', 'default' => 'شروع خرید' ),
						array( 'key' => 'ship_next_step', 'type' => 'text', 'label' => 'متن هزینه ارسال در سبد', 'default' => 'در مرحله بعد' ),
						array( 'key' => 'toast_cleared', 'type' => 'text', 'label' => 'پیام پاک‌سازی سبد', 'default' => 'سبد خرید خالی شد' ),
						array( 'key' => 'toast_updated', 'type' => 'text', 'label' => 'پیام به‌روزرسانی سبد', 'default' => 'سبد خرید به‌روزرسانی شد' ),
					),
				),
			),
		);

		/* ----------------------------------------------------------- checkout */
		$s['checkout'] = array(
			'title'    => 'صورت‌حساب',
			'icon'     => 'dashicons-feedback',
			'sections' => array(
				array(
					'title'  => 'فیلدهای اطلاعات ارسال',
					'desc'   => 'ترتیب، عنوان، متن راهنما، اجباری بودن و عرض هر فیلد قابل تغییر است. کلیدهای billing_* به فیلدهای استاندارد ووکامرس متصل‌اند؛ کلیدهای دیگر به‌عنوان فیلد سفارشی در سفارش ذخیره می‌شوند.',
					'fields' => array(
						array(
							'key'     => 'fields',
							'type'    => 'repeater',
							'label'   => 'فیلدها',
							'title'   => 'label',
							'fields'  => array(
								array( 'key' => 'key', 'type' => 'text', 'label' => 'کلید (مثلا billing_phone یا zt_national_code)' ),
								array( 'key' => 'label', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'placeholder', 'type' => 'text', 'label' => 'متن راهنما' ),
								array( 'key' => 'type', 'type' => 'select', 'label' => 'نوع', 'options' => array( 'text' => 'متن', 'tel' => 'موبایل/تلفن', 'email' => 'ایمیل', 'number' => 'عدد', 'postcode' => 'کد پستی', 'state' => 'استان', 'city' => 'شهر (وابسته به استان)', 'select' => 'لیست کشویی', 'textarea' => 'متن چندخطی' ) ),
								array( 'key' => 'options', 'type' => 'lines', 'label' => 'گزینه‌ها (برای لیست کشویی، هر خط یک گزینه)' ),
								array( 'key' => 'required', 'type' => 'toggle', 'label' => 'اجباری' ),
								array( 'key' => 'width', 'type' => 'select', 'label' => 'عرض', 'options' => array( 'half' => 'نصف', 'full' => 'تمام عرض' ) ),
								array( 'key' => 'enabled', 'type' => 'toggle', 'label' => 'فعال' ),
							),
							'default' => array(
								array( 'key' => 'billing_first_name', 'label' => 'نام و نام خانوادگی', 'placeholder' => 'مثلا: نرگس محمدی', 'type' => 'text', 'options' => '', 'required' => 1, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_phone', 'label' => 'شماره موبایل', 'placeholder' => '۰۹۱۲ ۱۲۳ ۴۵۶۷', 'type' => 'tel', 'options' => '', 'required' => 1, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_email', 'label' => 'ایمیل (اختیاری)', 'placeholder' => 'name@email.com', 'type' => 'email', 'options' => '', 'required' => 0, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_postcode', 'label' => 'کد پستی', 'placeholder' => '۱۰ رقم', 'type' => 'postcode', 'options' => '', 'required' => 1, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_state', 'label' => 'استان', 'placeholder' => 'انتخاب استان', 'type' => 'state', 'options' => '', 'required' => 1, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_city', 'label' => 'شهر', 'placeholder' => 'ابتدا استان را انتخاب کنید', 'type' => 'city', 'options' => '', 'required' => 1, 'width' => 'half', 'enabled' => 1 ),
								array( 'key' => 'billing_address_1', 'label' => 'آدرس کامل', 'placeholder' => 'خیابان، کوچه، پلاک، واحد، نشانی دقیق ...', 'type' => 'text', 'options' => '', 'required' => 1, 'width' => 'full', 'enabled' => 1 ),
							),
						),
						array( 'key' => 'save_info', 'type' => 'toggle', 'label' => 'نمایش گزینه «ذخیره اطلاعات برای خریدهای بعدی»', 'default' => 1 ),
						array( 'key' => 'save_info_label', 'type' => 'text', 'label' => 'متن گزینه ذخیره اطلاعات', 'default' => 'ذخیره اطلاعات برای خریدهای بعدی' ),
						array( 'key' => 'validate_mobile', 'type' => 'toggle', 'label' => 'اعتبارسنجی شماره موبایل ایران (۰۹xxxxxxxxx)', 'default' => 1 ),
						array( 'key' => 'validate_postcode', 'type' => 'toggle', 'label' => 'اعتبارسنجی کد پستی ۱۰ رقمی', 'default' => 1 ),
						array( 'key' => 'cities_json', 'type' => 'code', 'label' => 'فهرست شهرها (JSON اختیاری: {"کد استان": ["شهر", ...]}) — خالی = فهرست کامل داخلی', 'default' => '' ),
					),
				),
				array(
					'title'  => 'روش‌های ارسال',
					'desc'   => 'این گزینه‌ها از طریق روش حمل‌ونقل «ارسال زیته» در ووکامرس محاسبه می‌شوند (در ساخت خودکار، این روش به منطقه‌ی «ایران» اضافه می‌شود).',
					'fields' => array(
						array(
							'key'     => 'shipping',
							'type'    => 'repeater',
							'label'   => 'گزینه‌های ارسال',
							'title'   => 'title',
							'fields'  => array(
								array( 'key' => 'id', 'type' => 'text', 'label' => 'شناسه (لاتین، یکتا)' ),
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'desc', 'type' => 'textarea', 'label' => 'توضیح' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'cost', 'type' => 'number', 'label' => 'هزینه (تومان)' ),
								array( 'key' => 'cities', 'type' => 'lines', 'label' => 'فقط برای این شهرها (هر خط یک شهر — خالی = همه)' ),
								array( 'key' => 'exclude_cities', 'type' => 'lines', 'label' => 'به جز این شهرها' ),
								array( 'key' => 'auto_select', 'type' => 'toggle', 'label' => 'انتخاب خودکار وقتی شهر مشتری در فهرست بالا باشد' ),
								array( 'key' => 'free_by_tier', 'type' => 'toggle', 'label' => 'مشمول ارسال رایگان پلکانی' ),
								array( 'key' => 'enabled', 'type' => 'toggle', 'label' => 'فعال' ),
							),
							'default' => array(
								array( 'id' => 'peyk', 'title' => 'ارسال با پیک در لاهیجان', 'desc' => 'تحویل در روزهای کاری، ۹ صبح تا ۸ غروب', 'icon' => 'bike', 'cost' => 65000, 'cities' => 'لاهیجان', 'exclude_cities' => '', 'auto_select' => 1, 'free_by_tier' => 1, 'enabled' => 1 ),
								array( 'id' => 'post', 'title' => 'ارسال با پست پیشتاز', 'desc' => 'ارسال از زیته ۲ تا ۳ روز کاری', 'icon' => 'package', 'cost' => 45000, 'cities' => '', 'exclude_cities' => '', 'auto_select' => 0, 'free_by_tier' => 1, 'enabled' => 1 ),
								array( 'id' => 'pas', 'title' => 'ارسال با پست پیشتاز به صورت پس‌کرایه', 'desc' => 'هزینه پرداختی شما، فقط برای بسته‌بندی می‌باشد. پرداخت هزینه پست، درب منزل شماست. ارسال از زیته ۲ تا ۳ روز کاری', 'icon' => 'truck', 'cost' => 25000, 'cities' => '', 'exclude_cities' => '', 'auto_select' => 0, 'free_by_tier' => 0, 'enabled' => 1 ),
							),
						),
						array( 'key' => 'shipping_free_text', 'type' => 'text', 'label' => 'متن هزینه وقتی رایگان است', 'default' => 'رایگان' ),
						array( 'key' => 'shipping_default', 'type' => 'text', 'label' => 'شناسه روش ارسال پیش‌فرض', 'default' => 'post' ),
					),
				),
				array(
					'title'  => 'روش‌های پرداخت',
					'fields' => array(
						array(
							'key'     => 'gateways',
							'type'    => 'repeater',
							'label'   => 'آیکون و توضیح درگاه‌ها',
							'desc'    => 'شناسه درگاه‌های فعال ووکامرس را وارد کنید (مثلا bacs، cod، zt_card2card، zarinpal). درگاه‌هایی که اینجا نیستند با آیکون پیش‌فرض نمایش داده می‌شوند.',
							'title'   => 'id',
							'fields'  => array(
								array( 'key' => 'id', 'type' => 'text', 'label' => 'شناسه درگاه' ),
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان (خالی = عنوان درگاه)' ),
								array( 'key' => 'sub', 'type' => 'text', 'label' => 'زیرعنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
							),
							'default' => array(
								array( 'id' => 'zt_card2card', 'title' => 'کارت به کارت', 'sub' => 'پرداخت کارت به کارت و ارسال فیش', 'icon' => 'card2' ),
								array( 'id' => '*', 'title' => '', 'sub' => 'پرداخت امن از طریق درگاه بانکی', 'icon' => 'card' ),
							),
						),
						array( 'key' => 'card_enabled', 'type' => 'toggle', 'label' => 'فعال‌سازی درگاه «کارت به کارت» زیته', 'default' => 1 ),
						array( 'key' => 'card_number', 'type' => 'text', 'label' => 'شماره کارت', 'default' => '6037-9900-0000-0000' ),
						array( 'key' => 'card_holder', 'type' => 'text', 'label' => 'به نام', 'default' => 'فروشگاه زیته' ),
						array( 'key' => 'card_bank', 'type' => 'text', 'label' => 'بانک', 'default' => 'ملی' ),
						array( 'key' => 'card_instructions', 'type' => 'textarea', 'label' => 'توضیحات پس از ثبت سفارش', 'default' => 'لطفاً مبلغ سفارش را به کارت بالا واریز کرده و تصویر فیش یا شماره پیگیری را در صفحه پیگیری سفارش ثبت کنید.' ),
					),
				),
				array(
					'title'  => 'سایر',
					'fields' => array(
						array( 'key' => 'steps', 'type' => 'lines', 'label' => 'مراحل نوار بالا (هر خط یک مرحله)', 'default' => "اطلاعات ارسال\nروش ارسال\nروش پرداخت\nتایید سفارش" ),
						array( 'key' => 'note_enabled', 'type' => 'toggle', 'label' => 'نمایش یادداشت سفارش', 'default' => 1 ),
						array( 'key' => 'submit_note', 'type' => 'text', 'label' => 'متن کنار دکمه ثبت', 'default' => 'با ثبت سفارش، با شرایط و قوانین سایت موافقت می‌کنید.' ),
						array( 'key' => 'points_enabled', 'type' => 'toggle', 'label' => 'امتیاز باشگاه مشتریان', 'default' => 1 ),
						array( 'key' => 'points_per', 'type' => 'number', 'label' => 'هر چند تومان = ۱ امتیاز', 'default' => 10000 ),
						array( 'key' => 'points_text', 'type' => 'text', 'label' => 'متن امتیاز سفارش ({points})', 'default' => 'امتیاز این سفارش: {points} امتیاز' ),
						array( 'key' => 'thankyou_tracking', 'type' => 'toggle', 'label' => 'پس از ثبت سفارش به صفحه پیگیری همان سفارش برود', 'default' => 1 ),
					),
				),
			),
		);

		/* ---------------------------------------------------------- tracking */
		$s['tracking'] = array(
			'title'    => 'پیگیری و حساب کاربری',
			'icon'     => 'dashicons-location',
			'sections' => array(
				array(
					'title'  => 'مراحل وضعیت سفارش',
					'desc'   => 'برای هر مرحله، وضعیت‌های ووکامرس که به آن مرحله تعلق دارند را با کاما جدا کنید. زمان رسیدن به هر مرحله خودکار ثبت می‌شود. وضعیت‌های «در حال ارسال» (zt-shipped) و «در راه» (zt-transit) توسط افزونه اضافه شده‌اند.',
					'fields' => array(
						array(
							'key'     => 'stages',
							'type'    => 'repeater',
							'label'   => 'مراحل',
							'title'   => 'title',
							'fields'  => array(
								array( 'key' => 'title', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'icon', 'label' => 'آیکون' ) + $icon,
								array( 'key' => 'statuses', 'type' => 'text', 'label' => 'وضعیت‌ها' ),
							),
							'default' => array(
								array( 'title' => 'ثبت سفارش', 'icon' => 'clipboard', 'statuses' => 'pending,on-hold' ),
								array( 'title' => 'در حال آماده‌سازی', 'icon' => 'box', 'statuses' => 'processing' ),
								array( 'title' => 'در حال ارسال', 'icon' => 'truck-fast', 'statuses' => 'zt-shipped' ),
								array( 'title' => 'در راه', 'icon' => 'pin', 'statuses' => 'zt-transit' ),
								array( 'title' => 'تحویل شده', 'icon' => 'check', 'statuses' => 'completed' ),
							),
						),
						array( 'key' => 'eta_note', 'type' => 'text', 'label' => 'توضیح زمان تحویل', 'default' => 'این زمان تقریبی بوده و ممکن است به دلایل غیرمنتظره تغییر کند.' ),
						array( 'key' => 'map_url', 'type' => 'text', 'label' => 'آدرس نقشه ({address})', 'default' => 'https://neshan.org/maps/search/{address}' ),
					),
				),
				array(
					'title'  => 'پشتیبانی سفارش',
					'fields' => array(
						array( 'key' => 'support_title', 'type' => 'text', 'label' => 'عنوان', 'default' => 'پشتیبانی سفارش' ),
						array( 'key' => 'support_text', 'type' => 'textarea', 'label' => 'متن', 'default' => "در صورت نیاز به راهنمایی\nبا تیم پشتیبانی ما در تماس باشید." ),
						array( 'key' => 'support_button', 'type' => 'text', 'label' => 'دکمه', 'default' => 'تماس با پشتیبانی' ),
						array( 'key' => 'support_url', 'type' => 'text', 'label' => 'لینک دکمه', 'default' => '{{contact}}' ),
					),
				),
				array(
					'title'  => 'پنل کاربری',
					'fields' => array(
						array( 'key' => 'badge', 'type' => 'text', 'label' => 'نشان عضویت', 'default' => 'عضو ویژه زیته' ),
						array( 'key' => 'greeting', 'type' => 'text', 'label' => 'خوشامد هدر ({name})', 'default' => 'سلام {name} عزیز' ),
						array( 'key' => 'wishlist_guest', 'type' => 'toggle', 'label' => 'علاقه‌مندی برای کاربران مهمان (ذخیره در مرورگر)', 'default' => 1 ),
					),
				),
			),
		);

		/* -------------------------------------------------------- meta fields */
		$s['meta'] = array(
			'title'    => 'متافیلدها',
			'icon'     => 'dashicons-database',
			'sections' => array(
				array(
					'title'  => 'متافیلدهای اختصاصی',
					'desc'   => 'فیلدهای دلخواه برای محصولات، نوشته‌ها و برگه‌ها بسازید. هر فیلد در صفحه ویرایش نمایش داده می‌شود و با ویجت «متافیلد زیته» یا تگ داینامیک المنتور در قالب‌ها قابل نمایش است. فیلدهای اختصاصی محصول (نام انگلیسی، تاریخ انقضا، ویژگی‌ها، ترکیبات، نحوه استفاده و…) به‌صورت داخلی وجود دارند.',
					'fields' => array(
						array(
							'key'     => 'fields',
							'type'    => 'repeater',
							'label'   => 'فیلدها',
							'title'   => 'label',
							'fields'  => array(
								array( 'key' => 'key', 'type' => 'text', 'label' => 'کلید (لاتین، مثلا skin_type)' ),
								array( 'key' => 'label', 'type' => 'text', 'label' => 'عنوان' ),
								array( 'key' => 'type', 'type' => 'select', 'label' => 'نوع', 'options' => array( 'text' => 'متن', 'textarea' => 'متن چندخطی', 'number' => 'عدد', 'url' => 'لینک', 'image' => 'تصویر', 'date' => 'تاریخ (شمسی)', 'select' => 'لیست کشویی', 'lines' => 'فهرست (هر خط یک مورد)', 'toggle' => 'بله/خیر' ) ),
								array( 'key' => 'options', 'type' => 'lines', 'label' => 'گزینه‌ها (برای لیست کشویی)' ),
								array( 'key' => 'post_type', 'type' => 'select', 'label' => 'نوع محتوا', 'options' => array( 'product' => 'محصول', 'post' => 'نوشته', 'page' => 'برگه' ) ),
								array( 'key' => 'help', 'type' => 'text', 'label' => 'راهنما' ),
							),
							'default' => array(
								array( 'key' => 'skin_type', 'label' => 'مناسب نوع پوست', 'type' => 'text', 'options' => '', 'post_type' => 'product', 'help' => 'مثلا: پوست خشک و حساس' ),
							),
						),
					),
				),
			),
		);

		/* --------------------------------------------------------- newsletter */
		$s['newsletter'] = array(
			'title'    => 'خبرنامه و فرم‌ها',
			'icon'     => 'dashicons-email-alt',
			'sections' => array(
				array(
					'title'  => 'خبرنامه',
					'fields' => array(
						array( 'key' => 'success', 'type' => 'text', 'label' => 'پیام موفقیت', 'default' => 'عضویت شما در خبرنامه زیته با موفقیت ثبت شد.' ),
						array( 'key' => 'exists', 'type' => 'text', 'label' => 'پیام تکراری', 'default' => 'این ایمیل قبلاً در خبرنامه ثبت شده است.' ),
						array( 'key' => 'invalid', 'type' => 'text', 'label' => 'پیام ایمیل نامعتبر', 'default' => 'لطفاً یک ایمیل معتبر وارد کنید.' ),
						array( 'key' => 'notify_admin', 'type' => 'toggle', 'label' => 'ارسال ایمیل به مدیر برای هر عضویت', 'default' => 0 ),
					),
				),
				array(
					'title'  => 'فرم تماس',
					'fields' => array(
						array( 'key' => 'contact_to', 'type' => 'text', 'label' => 'ایمیل دریافت پیام‌ها (خالی = ایمیل مدیر)', 'default' => '' ),
						array( 'key' => 'contact_success', 'type' => 'text', 'label' => 'پیام موفقیت', 'default' => 'پیام شما ارسال شد؛ به‌زودی پاسخ می‌دهیم.' ),
					),
				),
			),
		);

		self::$schema = apply_filters( 'zt_settings_schema', $s );
		return self::$schema;
	}
}
