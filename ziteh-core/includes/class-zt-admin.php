<?php
/**
 * Admin: «زیته» settings screen (schema driven), tools, newsletter list, help.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Admin
 */
class ZT_Admin {

	const SLUG = 'ziteh-core';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_zt_save_settings', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_zt_reset_tab', array( __CLASS__, 'reset_tab' ) );
		add_action( 'admin_post_zt_build', array( __CLASS__, 'build' ) );
		add_action( 'admin_post_zt_demo_import', array( __CLASS__, 'demo_import' ) );
		add_action( 'admin_post_zt_export', array( __CLASS__, 'export' ) );
		add_action( 'admin_post_zt_import', array( __CLASS__, 'import' ) );
		add_action( 'admin_post_zt_clear_cache', array( __CLASS__, 'clear_cache' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>' ); // phpcs:ignore
		add_menu_page( 'زیته', 'زیته', 'manage_options', self::SLUG, array( __CLASS__, 'page' ), $icon, 58 );
		add_submenu_page( self::SLUG, 'تنظیمات زیته', 'تنظیمات', 'manage_options', self::SLUG, array( __CLASS__, 'page' ) );
		add_submenu_page( self::SLUG, 'ابزارها و ساخت صفحات', 'ساخت صفحات و ابزارها', 'manage_options', self::SLUG . '&tab=tools', '__return_null' );
		add_submenu_page( self::SLUG, 'مشترکین خبرنامه', 'مشترکین خبرنامه', 'manage_options', self::SLUG . '&tab=subscribers', '__return_null' );
		add_submenu_page( self::SLUG, 'راهنما', 'راهنما', 'manage_options', self::SLUG . '&tab=help', '__return_null' );
	}

	/**
	 * Is our screen?
	 *
	 * @return bool
	 */
	private static function is_screen() {
		return isset( $_GET['page'] ) && self::SLUG === sanitize_key( $_GET['page'] ); // phpcs:ignore
	}

	/**
	 * Admin assets.
	 */
	public static function assets() {
		if ( ! self::is_screen() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'zt-admin', ZT_URL . 'assets/css/zt-admin.css', array(), ZT_VERSION );
		wp_enqueue_style( 'zt-icons', ZT_URL . 'assets/css/zt-icons.css', array(), ZT_VERSION );
		wp_enqueue_script( 'zt-admin', ZT_URL . 'assets/js/zt-admin.js', array( 'jquery', 'jquery-ui-sortable' ), ZT_VERSION, true );
	}

	/**
	 * Tabs (schema + extra).
	 *
	 * @return array key => [title, dashicon]
	 */
	private static function tabs() {
		$tabs = array();
		foreach ( ZT_Settings::schema() as $k => $t ) {
			$tabs[ $k ] = array( $t['title'], isset( $t['icon'] ) ? $t['icon'] : 'dashicons-admin-generic' );
		}
		$tabs['tools']       = array( 'ساخت صفحات و ابزارها', 'dashicons-admin-tools' );
		$tabs['subscribers'] = array( 'مشترکین خبرنامه', 'dashicons-email-alt' );
		$tabs['help']        = array( 'راهنما', 'dashicons-editor-help' );
		return $tabs;
	}

	/**
	 * Current tab.
	 *
	 * @return string
	 */
	private static function tab() {
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore
		$tabs = self::tabs();
		return isset( $tabs[ $tab ] ) ? $tab : 'general';
	}

	/**
	 * Url of a tab.
	 *
	 * @param string $tab  Tab.
	 * @param array  $args Extra args.
	 * @return string
	 */
	public static function url( $tab = 'general', $args = array() ) {
		return add_query_arg( array_merge( array( 'page' => self::SLUG, 'tab' => $tab ), $args ), admin_url( 'admin.php' ) );
	}

	/**
	 * Screen.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$tab = self::tab();
		echo '<div class="wrap zt-admin" dir="rtl">';
		echo '<div class="zt-admin__head"><div class="zt-admin__brand"><span class="zt-admin__logo">زیته</span><span>هسته زیته <small>نسخه ' . esc_html( ZT_VERSION ) . '</small></span></div>';
		echo '<div class="zt-admin__head-links"><a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank">مشاهده سایت</a> <a class="button" href="' . esc_url( add_query_arg( 'zt_demo', 1, home_url( '/' ) ) ) . '" target="_blank">پیش‌نمایش طرح</a></div></div>';
		echo '<h1 class="screen-reader-text">تنظیمات زیته</h1>';
		self::notices();
		echo '<nav class="zt-admin__tabs">';
		foreach ( self::tabs() as $k => $t ) {
			echo '<a class="zt-admin__tab' . ( $k === $tab ? ' is-active' : '' ) . '" href="' . esc_url( self::url( $k ) ) . '"><span class="dashicons ' . esc_attr( $t[1] ) . '"></span>' . esc_html( $t[0] ) . '</a>';
		}
		echo '</nav><div class="zt-admin__body">';
		switch ( $tab ) {
			case 'tools':
				self::tools();
				break;
			case 'subscribers':
				self::subscribers();
				break;
			case 'help':
				self::help();
				break;
			default:
				self::settings_form( $tab );
		}
		echo '</div></div>';
	}

	/**
	 * Result notices after redirects.
	 */
	private static function notices() {
		$msg = isset( $_GET['zt_msg'] ) ? sanitize_key( $_GET['zt_msg'] ) : ''; // phpcs:ignore
		$map = array(
			'saved'    => array( 'success', 'تنظیمات ذخیره شد.' ),
			'reset'    => array( 'success', 'تنظیمات این بخش به پیش‌فرض برگشت.' ),
			'built'    => array( 'success', 'صفحات و قالب‌ها ساخته شدند.' ),
			'demo'     => array( 'success', 'محتوای نمونه درون‌ریزی شد.' ),
			'imported' => array( 'success', 'تنظیمات درون‌ریزی شد.' ),
			'badjson'  => array( 'error', 'فایل/متن تنظیمات معتبر نیست.' ),
			'cache'    => array( 'success', 'کش CSS المنتور و قوانین پیوندها بازسازی شد.' ),
			'nowoo'    => array( 'error', 'برای این کار ووکامرس باید فعال باشد.' ),
		);
		if ( isset( $map[ $msg ] ) ) {
			echo '<div class="notice notice-' . esc_attr( $map[ $msg ][0] ) . ' is-dismissible"><p>' . esc_html( $map[ $msg ][1] ) . '</p></div>';
		}
		$log = get_transient( 'zt_admin_log_' . get_current_user_id() );
		if ( $log ) {
			delete_transient( 'zt_admin_log_' . get_current_user_id() );
			echo '<div class="notice notice-info zt-admin__log"><ul><li>' . implode( '</li><li>', array_map( 'esc_html', (array) $log ) ) . '</li></ul></div>';
		}
	}

	/* ---------------------------------------------------------------- settings */

	/**
	 * Settings form of a schema tab.
	 *
	 * @param string $tab Tab.
	 */
	private static function settings_form( $tab ) {
		$schema = ZT_Settings::schema();
		$vals   = ZT_Settings::get( $tab, array() );
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="zt-admin__form">';
		wp_nonce_field( 'zt_save_' . $tab );
		echo '<input type="hidden" name="action" value="zt_save_settings"><input type="hidden" name="tab" value="' . esc_attr( $tab ) . '">';
		foreach ( $schema[ $tab ]['sections'] as $i => $sec ) {
			echo '<section class="zt-card" id="zt-sec-' . (int) $i . '"><h2 class="zt-card__title">' . esc_html( $sec['title'] ) . '</h2>';
			if ( ! empty( $sec['desc'] ) ) {
				echo '<p class="zt-card__desc">' . esc_html( $sec['desc'] ) . '</p>';
			}
			echo '<div class="zt-fields">';
			foreach ( $sec['fields'] as $f ) {
				if ( empty( $f['key'] ) ) {
					continue;
				}
				$val = isset( $vals[ $f['key'] ] ) ? $vals[ $f['key'] ] : ( isset( $f['default'] ) ? $f['default'] : '' );
				self::row( $f, 'zt[' . $f['key'] . ']', $val );
			}
			echo '</div></section>';
		}
		echo '<div class="zt-admin__save"><button type="submit" class="button button-primary button-hero">ذخیره تنظیمات</button>';
		echo '<a class="button zt-reset" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=zt_reset_tab&tab=' . $tab ), 'zt_reset_' . $tab ) ) . '" onclick="return confirm(\'همه تنظیمات این بخش به پیش‌فرض برگردد؟\')">بازگشت به پیش‌فرض این بخش</a></div>';
		echo '</form>';
	}

	/**
	 * A labelled field row.
	 *
	 * @param array  $f    Field.
	 * @param string $name Input name.
	 * @param mixed  $val  Value.
	 */
	private static function row( $f, $name, $val ) {
		$type = isset( $f['type'] ) ? $f['type'] : 'text';
		$wide = in_array( $type, array( 'repeater', 'textarea', 'lines', 'code' ), true ) ? ' zt-field--wide' : '';
		echo '<div class="zt-field zt-field--' . esc_attr( $type ) . $wide . '">';
		if ( 'toggle' !== $type ) {
			echo '<label class="zt-field__label">' . esc_html( $f['label'] ) . '</label>';
		}
		self::input( $f, $name, $val );
		if ( ! empty( $f['help'] ) ) {
			echo '<p class="zt-field__help">' . esc_html( $f['help'] ) . '</p>';
		}
		echo '</div>';
	}

	/**
	 * Pages dropdown options.
	 *
	 * @param string $pt Post type.
	 * @return array
	 */
	private static function post_options( $pt ) {
		static $cache = array();
		if ( ! isset( $cache[ $pt ] ) ) {
			$cache[ $pt ] = array( 0 => '— انتخاب —' );
			foreach ( get_posts( array( 'post_type' => $pt, 'numberposts' => 300, 'post_status' => array( 'publish', 'draft', 'private' ), 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
				$cache[ $pt ][ $p->ID ] = $p->post_title . ( 'zt_template' === $pt ? ' (' . get_post_meta( $p->ID, '_zt_tpl_type', true ) . ')' : '' );
			}
		}
		return $cache[ $pt ];
	}

	/**
	 * Input control.
	 *
	 * @param array  $f    Field.
	 * @param string $name Name.
	 * @param mixed  $val  Value.
	 */
	private static function input( $f, $name, $val ) {
		$type = isset( $f['type'] ) ? $f['type'] : 'text';
		$ph   = isset( $f['placeholder'] ) ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '';
		switch ( $type ) {
			case 'toggle':
				echo '<label class="zt-toggle"><input type="hidden" name="' . esc_attr( $name ) . '" value="0"><input type="checkbox" name="' . esc_attr( $name ) . '" value="1"' . checked( ! empty( $val ), true, false ) . '><span class="zt-toggle__ui"></span><span class="zt-toggle__label">' . esc_html( $f['label'] ) . '</span></label>';
				break;
			case 'number':
				echo '<input type="number" step="any" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '"' . $ph . '>'; // phpcs:ignore
				break;
			case 'color':
				$hex = preg_match( '/^#[0-9a-f]{6}$/i', (string) $val ) ? $val : '#000000';
				echo '<span class="zt-color"><input type="color" value="' . esc_attr( $hex ) . '" data-zt-color-pick><input type="text" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" dir="ltr" data-zt-color-text>';
				if ( isset( $f['default'] ) ) {
					echo '<button type="button" class="button-link" data-zt-color-reset="' . esc_attr( $f['default'] ) . '">پیش‌فرض</button>';
				}
				echo '</span>';
				break;
			case 'select':
				echo '<select name="' . esc_attr( $name ) . '">';
				foreach ( (array) $f['options'] as $k => $l ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( (string) $val, (string) $k, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</select>';
				break;
			case 'page':
			case 'template':
				$pt   = 'page' === $type ? 'page' : 'zt_template';
				$opts = self::post_options( $pt );
				echo '<span class="zt-postsel"><select name="' . esc_attr( $name ) . '">';
				foreach ( $opts as $k => $l ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( (int) $val, (int) $k, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</select>';
				if ( $val && get_post( (int) $val ) ) {
					echo ' <a href="' . esc_url( get_permalink( (int) $val ) ) . '" target="_blank" class="button-link">مشاهده</a>';
					if ( class_exists( '\Elementor\Plugin' ) ) {
						echo ' | <a href="' . esc_url( admin_url( 'post.php?post=' . (int) $val . '&action=elementor' ) ) . '" target="_blank" class="button-link">ویرایش با المنتور</a>';
					}
				}
				echo '</span>';
				break;
			case 'media':
				echo '<span class="zt-media"><input type="text" class="regular-text" dir="ltr" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" data-zt-media-input><button type="button" class="button" data-zt-media>انتخاب تصویر</button><button type="button" class="button-link" data-zt-media-clear>حذف</button>';
				echo '<img class="zt-media__prev" src="' . esc_url( $val ) . '" alt=""' . ( $val ? '' : ' hidden' ) . '></span>';
				break;
			case 'icon':
				echo '<span class="zt-iconsel"><span class="zt-iconsel__box"><i class="zti zti-' . esc_attr( $val ) . '" data-zt-icon-prev></i></span><select name="' . esc_attr( $name ) . '" data-zt-icon>';
				foreach ( ZT_Settings::icon_choices() as $k => $l ) {
					echo '<option value="' . esc_attr( $k ) . '"' . selected( (string) $val, (string) $k, false ) . '>' . esc_html( $l ) . '</option>';
				}
				echo '</select></span>';
				break;
			case 'textarea':
			case 'lines':
				echo '<textarea class="large-text" rows="' . ( 'lines' === $type ? 5 : 3 ) . '" name="' . esc_attr( $name ) . '"' . $ph . '>' . esc_textarea( is_array( $val ) ? implode( "\n", $val ) : $val ) . '</textarea>'; // phpcs:ignore
				break;
			case 'code':
				echo '<textarea class="large-text code" dir="ltr" rows="6" name="' . esc_attr( $name ) . '">' . esc_textarea( $val ) . '</textarea>';
				break;
			case 'repeater':
				self::repeater( $f, $name, is_array( $val ) ? $val : array() );
				break;
			case 'email':
			case 'url':
				echo '<input type="' . esc_attr( $type ) . '" class="regular-text" dir="ltr" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '"' . $ph . '>'; // phpcs:ignore
				break;
			default:
				echo '<input type="text" class="regular-text" name="' . esc_attr( $name ) . '" value="' . esc_attr( is_array( $val ) ? '' : $val ) . '"' . $ph . '>'; // phpcs:ignore
		}
	}

	/**
	 * Repeater.
	 *
	 * @param array  $f    Field.
	 * @param string $name Name.
	 * @param array  $rows Rows.
	 */
	private static function repeater( $f, $name, $rows ) {
		$title = isset( $f['title'] ) ? $f['title'] : '';
		echo '<div class="zt-rep" data-zt-rep data-name="' . esc_attr( $name ) . '">';
		echo '<div class="zt-rep__rows" data-zt-rep-rows>';
		foreach ( array_values( $rows ) as $i => $row ) {
			self::rep_row( $f, $name . '[' . $i . ']', $row, $title, false );
		}
		echo '</div>';
		echo '<script type="text/template" data-zt-rep-tpl>';
		ob_start();
		self::rep_row( $f, $name . '[__i__]', array(), $title, true );
		echo str_replace( '</script>', '<\/script>', ob_get_clean() ); // phpcs:ignore
		echo '</script>';
		echo '<button type="button" class="button zt-rep__add" data-zt-rep-add><span class="dashicons dashicons-plus-alt2"></span> افزودن ردیف</button></div>';
	}

	/**
	 * Repeater row.
	 *
	 * @param array  $f     Field.
	 * @param string $name  Row name.
	 * @param array  $row   Values.
	 * @param string $title Title key.
	 * @param bool   $open  Open.
	 */
	private static function rep_row( $f, $name, $row, $title, $open ) {
		$label = ( $title && isset( $row[ $title ] ) && '' !== (string) $row[ $title ] ) ? $row[ $title ] : 'ردیف جدید';
		echo '<div class="zt-rep__row' . ( $open ? ' is-open' : '' ) . '" data-zt-rep-row>';
		echo '<div class="zt-rep__head"><span class="zt-rep__drag dashicons dashicons-move" title="جابه‌جایی"></span><button type="button" class="zt-rep__toggle" data-zt-rep-toggle><span data-zt-rep-title data-key="' . esc_attr( $title ) . '">' . esc_html( is_array( $label ) ? '' : $label ) . '</span><span class="dashicons dashicons-arrow-down-alt2"></span></button>';
		echo '<button type="button" class="zt-rep__dup" data-zt-rep-dup title="کپی"><span class="dashicons dashicons-admin-page"></span></button><button type="button" class="zt-rep__del" data-zt-rep-del title="حذف"><span class="dashicons dashicons-trash"></span></button></div>';
		echo '<div class="zt-rep__body"><div class="zt-fields">';
		foreach ( $f['fields'] as $sub ) {
			$v = isset( $row[ $sub['key'] ] ) ? $row[ $sub['key'] ] : ( isset( $sub['default'] ) ? $sub['default'] : '' );
			self::row( $sub, $name . '[' . $sub['key'] . ']', $v );
		}
		echo '</div></div></div>';
	}

	/**
	 * Save handler.
	 */
	public static function save() {
		$tab = isset( $_POST['tab'] ) ? sanitize_key( $_POST['tab'] ) : '';
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_save_' . $tab ) ) {
			wp_die( 'forbidden' );
		}
		$raw = isset( $_POST['zt'] ) ? wp_unslash( $_POST['zt'] ) : array(); // phpcs:ignore
		ZT_Settings::update_tab( $tab, ZT_Settings::sanitize_tab( $tab, is_array( $raw ) ? $raw : array() ) );
		if ( 'meta' === $tab ) {
			flush_rewrite_rules( false );
		}
		self::clear_elementor_css();
		wp_safe_redirect( self::url( $tab, array( 'zt_msg' => 'saved' ) ) );
		exit;
	}

	/**
	 * Reset a tab.
	 */
	public static function reset_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_reset_' . $tab ) ) {
			wp_die( 'forbidden' );
		}
		$saved = get_option( ZT_Settings::OPTION, array() );
		if ( is_array( $saved ) && isset( $saved[ $tab ] ) ) {
			$keep = array();
			if ( 'pages' === $tab ) {
				$keep = $saved[ $tab ]; // page ids are never reset.
			}
			$saved[ $tab ] = $keep;
			update_option( ZT_Settings::OPTION, $saved, true );
			ZT_Settings::flush();
		}
		wp_safe_redirect( self::url( $tab, array( 'zt_msg' => 'reset' ) ) );
		exit;
	}

	/* ------------------------------------------------------------------- tools */

	/**
	 * Tools tab.
	 */
	private static function tools() {
		$post = admin_url( 'admin-post.php' );
		$has  = (bool) zt_opt( 'pages.home' );
		echo '<section class="zt-card zt-card--hero"><h2 class="zt-card__title">ساخت خودکار همه صفحات با یک کلیک</h2>';
		echo '<p class="zt-card__desc">صفحات خانه، فروشگاه، سبد خرید، صورت‌حساب، پنل کاربری، پیگیری سفارش، راهنمای روتین، مجله، درباره ما، تماس با ما و قوانین، به‌همراه قالب‌های هدر، فوتر، تک‌محصول، آرشیو محصولات، تک‌نوشته، آرشیو مقالات، ۴۰۴ و نتایج جستجو، دقیقاً مطابق طرح با ویجت‌های زیته ساخته می‌شوند. صفحات ووکامرس موجود استفاده می‌شوند و صفحه تکراری ساخته نمی‌شود.</p>';
		echo '<form method="post" action="' . esc_url( $post ) . '" class="zt-tools-form">';
		wp_nonce_field( 'zt_build' );
		echo '<input type="hidden" name="action" value="zt_build">';
		echo '<label class="zt-toggle"><input type="checkbox" name="overwrite" value="1"' . checked( ! $has, true, false ) . '><span class="zt-toggle__ui"></span><span class="zt-toggle__label">بازسازی صفحاتی که قبلاً ساخته شده‌اند (محتوای فعلی آن‌ها جایگزین می‌شود)</span></label>';
		echo '<label class="zt-toggle"><input type="checkbox" name="front" value="1" checked><span class="zt-toggle__ui"></span><span class="zt-toggle__label">تنظیم صفحه «خانه» به‌عنوان صفحه اصلی سایت</span></label>';
		echo '<label class="zt-toggle"><input type="checkbox" name="woo" value="1" checked><span class="zt-toggle__ui"></span><span class="zt-toggle__label">تنظیم ووکامرس (صفحات، واحد پول تومان، منطقه حمل‌ونقل ایران با «ارسال زیته»)</span></label>';
		echo '<p><button type="submit" class="button button-primary button-hero"><span class="dashicons dashicons-admin-page"></span> ساخت همه صفحات</button></p></form>';
		$rows = array();
		foreach ( array( 'home' => 'خانه', 'shop' => 'فروشگاه', 'cart' => 'سبد خرید', 'checkout' => 'صورت‌حساب', 'account' => 'پنل کاربری', 'tracking' => 'پیگیری سفارش', 'routine' => 'راهنمای روتین', 'blog' => 'مجله', 'about' => 'درباره ما', 'contact' => 'تماس با ما', 'terms' => 'قوانین' ) as $k => $l ) {
			$id     = (int) zt_opt( 'pages.' . $k );
			$rows[] = '<tr><td>' . esc_html( $l ) . '</td><td>' . ( $id && get_post( $id ) ? '<a href="' . esc_url( get_permalink( $id ) ) . '" target="_blank">' . esc_html( get_the_title( $id ) ) . '</a> — <a href="' . esc_url( admin_url( 'post.php?post=' . $id . '&action=elementor' ) ) . '">ویرایش با المنتور</a>' : '<span class="zt-muted">ساخته نشده</span>' ) . '</td></tr>';
		}
		echo '<table class="widefat striped zt-pages-table"><thead><tr><th>صفحه</th><th>وضعیت</th></tr></thead><tbody>' . implode( '', $rows ) . '</tbody></table></section>'; // phpcs:ignore

		echo '<section class="zt-card"><h2 class="zt-card__title">درون‌ریزی محتوای نمونه</h2><p class="zt-card__desc">دسته‌بندی‌ها، محصولات (با تصاویر، قیمت، تخفیف، ویژگی‌ها، ترکیبات، نحوه استفاده، تاریخ انقضا و نظرات)، مقالات مجله و برند نمونه‌ی طرح ساخته می‌شوند تا همه‌چیز فوراً داینامیک دیده شود. محتوای تکراری ساخته نمی‌شود.</p>';
		echo '<form method="post" action="' . esc_url( $post ) . '">';
		wp_nonce_field( 'zt_demo_import' );
		echo '<input type="hidden" name="action" value="zt_demo_import"><button type="submit" class="button button-secondary button-large"' . ( zt_is_woo() ? '' : ' disabled' ) . '><span class="dashicons dashicons-download"></span> درون‌ریزی محتوای نمونه</button>' . ( zt_is_woo() ? '' : ' <span class="zt-muted">(ووکامرس فعال نیست)</span>' ) . '</form></section>';

		echo '<section class="zt-card"><h2 class="zt-card__title">پشتیبان‌گیری تنظیمات</h2><p class="zt-card__desc">همه تنظیمات زیته را به‌صورت فایل JSON دریافت کنید یا از یک فایل برگردانید.</p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( $post . '?action=zt_export', 'zt_export' ) ) . '"><span class="dashicons dashicons-upload"></span> دریافت فایل تنظیمات</a></p>';
		echo '<form method="post" action="' . esc_url( $post ) . '" enctype="multipart/form-data" class="zt-import">';
		wp_nonce_field( 'zt_import' );
		echo '<input type="hidden" name="action" value="zt_import"><input type="file" name="file" accept=".json,application/json"> <button type="submit" class="button">درون‌ریزی تنظیمات</button></form></section>';

		echo '<section class="zt-card"><h2 class="zt-card__title">کش و پیوندها</h2><p class="zt-card__desc">اگر تغییری در ظاهر دیده نمی‌شود یا آدرس بخش‌های حساب کاربری ۴۰۴ می‌دهد، این دکمه را بزنید.</p>';
		echo '<p><a class="button" href="' . esc_url( wp_nonce_url( $post . '?action=zt_clear_cache', 'zt_clear_cache' ) ) . '"><span class="dashicons dashicons-update"></span> پاک‌سازی کش CSS المنتور و بازسازی پیوندها</a></p></section>';

		self::status();
	}

	/**
	 * System status.
	 */
	private static function status() {
		$theme = wp_get_theme();
		$items = array(
			array( 'قالب فعال', $theme->get( 'Name' ), 'hello-elementor' === $theme->get_template() ),
			array( 'المنتور', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : 'غیرفعال', defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, ZT_MIN_ELEMENTOR, '>=' ) ),
			array( 'ووکامرس', defined( 'WC_VERSION' ) ? WC_VERSION : 'غیرفعال', defined( 'WC_VERSION' ) ),
			array( 'واحد پول ووکامرس', function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '—', function_exists( 'get_woocommerce_currency' ) && in_array( get_woocommerce_currency(), array( 'IRT', 'IRR', 'IRHT' ), true ) ),
			array( 'پیوندهای یکتا', get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'ساده', (bool) get_option( 'permalink_structure' ) ),
			array( 'نسخه PHP', PHP_VERSION, version_compare( PHP_VERSION, '7.4', '>=' ) ),
		);
		echo '<section class="zt-card"><h2 class="zt-card__title">وضعیت سیستم</h2><table class="widefat striped"><tbody>';
		foreach ( $items as $it ) {
			echo '<tr><td>' . esc_html( $it[0] ) . '</td><td dir="auto">' . esc_html( $it[1] ) . '</td><td><span class="dashicons ' . ( $it[2] ? 'dashicons-yes-alt zt-ok' : 'dashicons-warning zt-warn' ) . '"></span></td></tr>';
		}
		echo '</tbody></table></section>';
	}

	/**
	 * Store a log for the next screen.
	 *
	 * @param array $log Lines.
	 */
	private static function log( $log ) {
		set_transient( 'zt_admin_log_' . get_current_user_id(), (array) $log, 120 );
	}

	/**
	 * Build pages.
	 */
	public static function build() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_build' ) ) {
			wp_die( 'forbidden' );
		}
		@set_time_limit( 300 ); // phpcs:ignore
		$log = ZT_Builder::build(
			array(
				'overwrite' => ! empty( $_POST['overwrite'] ),
				'front'     => ! empty( $_POST['front'] ),
				'woo'       => ! empty( $_POST['woo'] ),
			)
		);
		flush_rewrite_rules( false );
		self::clear_elementor_css();
		self::log( $log );
		wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'built' ) ) );
		exit;
	}

	/**
	 * Demo import.
	 */
	public static function demo_import() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_demo_import' ) ) {
			wp_die( 'forbidden' );
		}
		if ( ! zt_is_woo() ) {
			wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'nowoo' ) ) );
			exit;
		}
		@set_time_limit( 600 ); // phpcs:ignore
		self::log( ZT_Demo::import() );
		self::clear_elementor_css();
		wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'demo' ) ) );
		exit;
	}

	/**
	 * Export settings.
	 */
	public static function export() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_export' ) ) {
			wp_die( 'forbidden' );
		}
		$data = get_option( ZT_Settings::OPTION, array() );
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=ziteh-settings-' . gmdate( 'Y-m-d' ) . '.json' );
		echo wp_json_encode( array( 'ziteh' => ZT_VERSION, 'settings' => $data ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore
		exit;
	}

	/**
	 * Import settings.
	 */
	public static function import() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_import' ) ) {
			wp_die( 'forbidden' );
		}
		$json = '';
		if ( ! empty( $_FILES['file']['tmp_name'] ) && is_uploaded_file( $_FILES['file']['tmp_name'] ) ) { // phpcs:ignore
			$json = (string) file_get_contents( $_FILES['file']['tmp_name'] ); // phpcs:ignore
		}
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) || ! isset( $data['settings'] ) || ! is_array( $data['settings'] ) ) {
			wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'badjson' ) ) );
			exit;
		}
		$schema = ZT_Settings::schema();
		foreach ( $data['settings'] as $tab => $vals ) {
			if ( isset( $schema[ $tab ] ) && is_array( $vals ) ) {
				ZT_Settings::update_tab( $tab, ZT_Settings::sanitize_tab( $tab, $vals ) );
			}
		}
		self::clear_elementor_css();
		wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'imported' ) ) );
		exit;
	}

	/**
	 * Clear caches.
	 */
	public static function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_clear_cache' ) ) {
			wp_die( 'forbidden' );
		}
		self::clear_elementor_css();
		flush_rewrite_rules( false );
		wp_safe_redirect( self::url( 'tools', array( 'zt_msg' => 'cache' ) ) );
		exit;
	}

	/**
	 * Elementor CSS cache.
	 */
	public static function clear_elementor_css() {
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/* ------------------------------------------------------------- subscribers */

	/**
	 * Newsletter subscribers.
	 */
	private static function subscribers() {
		$rows = ZT_Newsletter::rows( 500 );
		echo '<section class="zt-card"><h2 class="zt-card__title">مشترکین خبرنامه <small>(' . esc_html( zt_fa( count( $rows ) ) ) . ')</small></h2>';
		echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=zt_newsletter_export' ), 'zt_newsletter' ) ) . '"><span class="dashicons dashicons-media-spreadsheet"></span> دریافت فایل CSV</a></p>';
		if ( ! $rows ) {
			echo '<p class="zt-muted">هنوز کسی عضو خبرنامه نشده است.</p></section>';
			return;
		}
		echo '<table class="widefat striped"><thead><tr><th>ایمیل</th><th>تاریخ عضویت</th><th></th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$del = wp_nonce_url( admin_url( 'admin-post.php?action=zt_newsletter_delete&id=' . (int) $r->id ), 'zt_newsletter' );
			echo '<tr><td dir="ltr" style="text-align:right">' . esc_html( $r->email ) . '</td><td>' . esc_html( zt_jdate( 'j F Y — H:i', strtotime( $r->created ) ) ) . '</td><td><a class="zt-danger" href="' . esc_url( $del ) . '" onclick="return confirm(\'حذف شود؟\')">حذف</a></td></tr>';
		}
		echo '</tbody></table></section>';
	}

	/* -------------------------------------------------------------------- help */

	/**
	 * Help tab.
	 */
	private static function help() {
		$tokens = array(
			'{{home}}'         => 'صفحه اصلی',
			'{{shop}}'         => 'فروشگاه',
			'{{cart}}'         => 'سبد خرید',
			'{{checkout}}'     => 'صورت‌حساب',
			'{{account}}'      => 'پنل کاربری',
			'{{orders}}'       => 'سفارش‌های من',
			'{{wishlist}}'     => 'علاقه‌مندی‌ها',
			'{{addresses}}'    => 'آدرس‌ها',
			'{{edit-account}}' => 'اطلاعات حساب',
			'{{reviews}}'      => 'نظرات من',
			'{{coupons}}'      => 'کدهای تخفیف',
			'{{tracking}}'     => 'پیگیری سفارش',
			'{{routine}}'      => 'راهنمای روتین',
			'{{blog}}'         => 'مجله',
			'{{about}}'        => 'درباره ما',
			'{{contact}}'      => 'تماس با ما',
			'{{terms}}'        => 'قوانین',
			'{{logout}}'       => 'خروج',
		);
		echo '<section class="zt-card"><h2 class="zt-card__title">شروع سریع</h2><ol class="zt-steps">';
		echo '<li>قالب <b>Hello Elementor</b>، افزونه <b>المنتور</b> و <b>ووکامرس</b> را نصب و فعال کنید (استایل‌های پیش‌فرض Hello خودکار غیرفعال می‌شوند).</li>';
		echo '<li>از تب «ساخت صفحات و ابزارها» دکمه <b>ساخت همه صفحات</b> را بزنید؛ اگر فروشگاه خالی است «درون‌ریزی محتوای نمونه» را هم اجرا کنید.</li>';
		echo '<li>هر صفحه را با المنتور باز کنید؛ ویجت‌ها در دسته‌های «زیته | …» (خانه، محصول، سبد خرید، صورت‌حساب، پیگیری، حساب کاربری، راهنما، مجله، فروشگاه، صفحات) قرار دارند و همه متن‌ها، لینک‌ها، آیکون‌ها، تصاویر و استایل‌ها قابل تغییرند.</li>';
		echo '<li>قالب‌های داینامیک (هدر، فوتر، تک‌محصول، آرشیو، تک‌نوشته، ۴۰۴، جستجو) در منوی «زیته ← قالب‌ها» هستند و از تب «صفحات» به‌عنوان پیش‌فرض انتخاب می‌شوند. برای یک محصول خاص هم می‌توانید قالب جداگانه انتخاب کنید.</li>';
		echo '<li>تخفیف پلکانی، سمپل‌های هدیه، فیلدهای صورت‌حساب، روش‌های ارسال، کارت به کارت، مراحل پیگیری و متافیلدها از همین صفحه تنظیم می‌شوند.</li>';
		echo '<li>برای دیدن صفحات دقیقاً با محتوای نمونه طرح، <code>?zt_demo=1</code> را به آدرس هر صفحه اضافه کنید (فقط برای مدیران).</li></ol></section>';
		echo '<section class="zt-card"><h2 class="zt-card__title">لینک‌های هوشمند</h2><p class="zt-card__desc">در هر فیلد لینک (ویجت‌ها و تنظیمات) می‌توانید از این کدها استفاده کنید تا لینک همیشه به صفحه درست اشاره کند:</p><table class="widefat striped"><tbody>';
		foreach ( $tokens as $k => $l ) {
			echo '<tr><td dir="ltr" style="text-align:right"><code>' . esc_html( $k ) . '</code></td><td>' . esc_html( $l ) . '</td></tr>';
		}
		echo '</tbody></table></section>';
		echo '<section class="zt-card"><h2 class="zt-card__title">فیلدهای اختصاصی محصول</h2><p class="zt-card__desc">در صفحه ویرایش هر محصول، کادر «اطلاعات زیته» شامل نام انگلیسی، عنوان کوتاه، عنوان مسیر راهنما، دسته نمایشی، تاریخ انقضا (مثلا ۱۴۰۸/۰۵)، حجم، برند، یادداشت، برچسب، ویژگی‌ها، ترکیبات، نحوه استفاده، مشخصات و «نمایش در بخش‌ها» است. همه این‌ها در قالب تک‌محصول و کارت‌ها به‌صورت داینامیک نمایش داده می‌شوند.</p></section>';
	}
}
