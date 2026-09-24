<?php
/**
 * Tracking: lookup form (shown when no order is resolved).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Lookup
 */
class ZT_W_Order_Lookup extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-search';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-lookup';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'جستجوی سفارش';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'فرم' );
		$this->ctl( 'notice', 'notice', '', 'این فرم فقط وقتی سفارشی برای نمایش پیدا نشود (کاربر مهمان یا بدون سفارش) نمایش داده می‌شود.' );
		$this->ctl( 'title', 'text', 'عنوان', 'پیگیری سفارش' );
		$this->ctl( 'text', 'textarea', 'توضیح', 'شماره سفارش و شماره موبایل (یا ایمیل) ثبت‌شده در سفارش را وارد کنید.' );
		$this->ctl( 'l_order', 'text', 'برچسب شماره سفارش', 'شماره سفارش' );
		$this->ctl( 'l_id', 'text', 'برچسب موبایل/ایمیل', 'شماره موبایل یا ایمیل' );
		$this->ctl( 'btn', 'text', 'متن دکمه', 'پیگیری' );
		$this->ctl( 'fail', 'text', 'پیام پیدا نشدن', 'سفارشی با این مشخصات پیدا نشد. لطفاً اطلاعات را بررسی کنید.' );
		$this->ctl( 'login', 'text', 'متن ورود', 'برای مشاهده همه سفارش‌ها وارد حساب کاربری شوید' );
		$this->end_controls_section();
		$this->style_section( 'st', 'فرم', array( array( 'box', 'کادر', '.zt-dbox', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ), array( 'btn', 'دکمه', '.zt-btn', self::fx( 'button' ) ) ) );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ZT_Order_View::get() && ! $this->is_editor() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		if ( $this->is_editor() && ZT_Order_View::get() ) {
			echo '<div class="zt-editor-note">فرم جستجوی سفارش — فقط وقتی سفارشی برای نمایش نباشد دیده می‌شود.</div>';
			return;
		}
		$req = wp_unslash( $_REQUEST ); // phpcs:ignore
		echo '<section class="zt-dbox zt-lookup"><h2 class="zt-dbox__title">' . zt_icon( 'search' ) . ' ' . esc_html( $s['title'] ) . '</h2><p class="zt-lookup__text">' . esc_html( $s['text'] ) . '</p>'; // phpcs:ignore
		if ( ZT_Context::lookup_failed() ) {
			echo '<p class="zt-notice zt-notice--error">' . esc_html( $s['fail'] ) . '</p>';
		}
		echo '<form method="post" class="zt-form-grid">';
		wp_nonce_field( 'zt_track', 'zt_track_nonce' );
		echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_order'] ) . '</span><input class="zt-input" name="zt_track" inputmode="numeric" required value="' . esc_attr( isset( $req['zt_track'] ) ? sanitize_text_field( $req['zt_track'] ) : '' ) . '" placeholder="۱۵۵۸۷"></label>';
		echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_id'] ) . '</span><input class="zt-input" name="zt_track_id" required value="' . esc_attr( isset( $req['zt_track_id'] ) ? sanitize_text_field( $req['zt_track_id'] ) : '' ) . '" placeholder="۰۹۱۲۱۲۳۴۵۶۷"></label>';
		echo '<div class="zt-full zt-lookup__foot"><button class="zt-btn zt-btn--primary" type="submit">' . zt_icon( 'search' ) . ' ' . esc_html( $s['btn'] ) . '</button>'; // phpcs:ignore
		if ( ! is_user_logged_in() ) {
			echo '<a class="zt-link-more" href="' . esc_url( zt_page_url( 'account' ) ) . '">' . esc_html( $s['login'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>'; // phpcs:ignore
		}
		echo '</div></form></section>';
	}
}
