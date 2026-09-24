<?php
/**
 * Account: login + register forms (guests).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Login
 */
class ZT_W_Account_Login extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-lock-user';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-login';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'ورود و ثبت‌نام';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'ورود' );
		$this->ctl( 'notice', 'notice', '', 'این ویجت فقط برای کاربران وارد نشده نمایش داده می‌شود. ثبت‌نام از «ووکامرس ← تنظیمات ← حساب‌ها» فعال/غیرفعال می‌شود.' );
		$this->ctl( 'l_icon', 'icon', 'آیکون', 'login' );
		$this->ctl( 'l_title', 'text', 'عنوان ورود', 'ورود به حساب کاربری' );
		$this->ctl( 'l_text', 'textarea', 'توضیح ورود', 'با شماره موبایل، ایمیل یا نام کاربری وارد شوید.' );
		$this->ctl( 'l_user', 'text', 'برچسب نام کاربری', 'شماره موبایل، ایمیل یا نام کاربری' );
		$this->ctl( 'l_pass', 'text', 'برچسب رمز عبور', 'رمز عبور' );
		$this->ctl( 'l_remember', 'text', 'مرا به خاطر بسپار', 'مرا به خاطر بسپار' );
		$this->ctl( 'l_lost', 'text', 'فراموشی رمز', 'رمز عبور را فراموش کرده‌اید؟' );
		$this->ctl( 'l_btn', 'text', 'دکمه ورود', 'ورود' );
		$this->ctl( 'redirect', 'url', 'انتقال پس از ورود (خالی = پنل)', '' );
		$this->end_controls_section();
		$this->section( 'c2', 'ثبت‌نام' );
		$this->ctl( 'r_icon', 'icon', 'آیکون', 'user' );
		$this->ctl( 'r_title', 'text', 'عنوان ثبت‌نام', 'ثبت‌نام در زیته' );
		$this->ctl( 'r_text', 'textarea', 'توضیح ثبت‌نام', 'با ساخت حساب کاربری، سفارش‌ها را پیگیری کنید و از امتیاز باشگاه مشتریان بهره‌مند شوید.' );
		$this->ctl( 'r_user', 'text', 'برچسب نام کاربری', 'نام کاربری' );
		$this->ctl( 'r_email', 'text', 'برچسب ایمیل', 'ایمیل' );
		$this->ctl( 'r_pass', 'text', 'برچسب رمز عبور', 'رمز عبور' );
		$this->ctl( 'r_note', 'text', 'متن ارسال رمز', 'لینک تعیین رمز عبور به ایمیل شما ارسال می‌شود.' );
		$this->ctl( 'r_btn', 'text', 'دکمه ثبت‌نام', 'ثبت‌نام' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'فرم‌ها',
			array(
				array( 'grid', 'چیدمان', '.zt-auth', array( 'columns', 'gap' ) ),
				array( 'box', 'کادر', '.zt-auth .zt-dcard', array( 'bg', 'border', 'radius', 'padding', 'shadow' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'text', 'توضیح', '.zt-auth__text', array( 'typo', 'color' ) ),
				array( 'label', 'برچسب', '.zt-label', array( 'typo', 'color' ) ),
				array( 'input', 'فیلد', '.zt-input', array( 'typo', 'color', 'bg', 'border_color', 'radius', 'height' ) ),
				array( 'btn', 'دکمه', '.zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/**
	 * Input row.
	 *
	 * @param string $label Label.
	 * @param string $name  Name.
	 * @param string $type  Type.
	 * @param string $auto  Autocomplete.
	 * @return string
	 */
	private function field( $label, $name, $type = 'text', $auto = '' ) {
		$val = ( 'password' !== $type && isset( $_POST[ $name ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : ''; // phpcs:ignore
		return '<label class="zt-field"><span class="zt-label">' . esc_html( $label ) . ' <span class="zt-req">*</span></span><input class="zt-input" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" autocomplete="' . esc_attr( $auto ) . '" value="' . esc_attr( $val ) . '" required' . ( 'password' === $type ? ' dir="ltr"' : '' ) . '></label>';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$mode = ZT_Acc::mode();
		if ( 'user' === $mode || ( 'guest' === $mode && 'lost-password' === ZT_Account::endpoint() ) ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		if ( 'sample' === $mode && ! $this->is_editor() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		if ( 'sample' === $mode ) {
			echo '<div class="zt-editor-note">فرم ورود و ثبت‌نام — فقط برای کاربران وارد نشده نمایش داده می‌شود.</div>';
			return;
		}
		$reg = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
		echo '<div class="zt-auth' . ( $reg ? '' : ' zt-auth--single' ) . '">';
		echo '<div class="zt-auth__notices">';
		if ( function_exists( 'wc_print_notices' ) ) {
			wc_print_notices();
		}
		echo '</div>';
		echo '<section class="zt-dcard"><div class="zt-dcard__head"><h3>' . zt_icon( $s['l_icon'] ) . ' ' . esc_html( $s['l_title'] ) . '</h3></div>'; // phpcs:ignore
		if ( $s['l_text'] ) {
			echo '<p class="zt-auth__text">' . esc_html( $s['l_text'] ) . '</p>';
		}
		echo '<form class="woocommerce-form woocommerce-form-login login zt-auth__form" method="post">';
		do_action( 'woocommerce_login_form_start' );
		echo $this->field( $s['l_user'], 'username', 'text', 'username' ); // phpcs:ignore
		echo $this->field( $s['l_pass'], 'password', 'password', 'current-password' ); // phpcs:ignore
		do_action( 'woocommerce_login_form' );
		echo '<div class="zt-auth__row"><label class="zt-check"><input type="checkbox" name="rememberme" value="forever"><span class="zt-box">' . zt_icon( 'check' ) . '</span>' . esc_html( $s['l_remember'] ) . '</label>'; // phpcs:ignore
		echo '<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html( $s['l_lost'] ) . '</a></div>';
		wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' );
		$redirect = ! empty( $s['redirect']['url'] ) ? zt_url( $s['redirect'] ) : ( isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : wc_get_page_permalink( 'myaccount' ) ); // phpcs:ignore
		echo '<input type="hidden" name="redirect" value="' . esc_url( $redirect ) . '">';
		echo '<button type="submit" class="zt-btn zt-btn--primary zt-btn--block" name="login" value="' . esc_attr( $s['l_btn'] ) . '">' . esc_html( $s['l_btn'] ) . '</button>';
		do_action( 'woocommerce_login_form_end' );
		echo '</form></section>';

		if ( $reg ) {
			echo '<section class="zt-dcard"><div class="zt-dcard__head"><h3>' . zt_icon( $s['r_icon'] ) . ' ' . esc_html( $s['r_title'] ) . '</h3></div>'; // phpcs:ignore
			if ( $s['r_text'] ) {
				echo '<p class="zt-auth__text">' . esc_html( $s['r_text'] ) . '</p>';
			}
			echo '<form method="post" class="woocommerce-form woocommerce-form-register register zt-auth__form">';
			do_action( 'woocommerce_register_form_start' );
			if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
				echo $this->field( $s['r_user'], 'username', 'text', 'username' ); // phpcs:ignore
			}
			echo $this->field( $s['r_email'], 'email', 'email', 'email' ); // phpcs:ignore
			if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) {
				echo $this->field( $s['r_pass'], 'password', 'password', 'new-password' ); // phpcs:ignore
			} elseif ( $s['r_note'] ) {
				echo '<p class="zt-auth__note">' . esc_html( $s['r_note'] ) . '</p>';
			}
			do_action( 'woocommerce_register_form' );
			wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' );
			echo '<button type="submit" class="zt-btn zt-btn--primary zt-btn--block" name="register" value="' . esc_attr( $s['r_btn'] ) . '">' . esc_html( $s['r_btn'] ) . '</button>';
			do_action( 'woocommerce_register_form_end' );
			echo '</form></section>';
		}
		echo '</div>';
	}
}
