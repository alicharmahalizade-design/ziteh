<?php
/**
 * Tracking: order header (number, status, amount).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Header
 */
class ZT_W_Order_Header extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-info-box';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-header';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'سربرگ سفارش';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'h1', 'text', 'عنوان ستون ۱', 'شماره سفارش' );
		$this->ctl( 'h2', 'text', 'عنوان ستون ۲', 'وضعیت سفارش' );
		$this->ctl( 'h3', 'text', 'عنوان ستون ۳', 'مبلغ سفارش' );
		$this->ctl( 'created_tpl', 'text', 'متن تاریخ ثبت', 'ثبت شده در {date}' );
		$this->ctl( 'ship_tpl', 'text', 'متن روش ارسال', 'روش ارسال: {method}' );
		$this->ctl( 'orn', 'media', 'تصویر تزئینی', 'branch-soft.png' );
		$this->ctl( 'receipt', 'switch', 'فرم ثبت اطلاعات واریز برای سفارش‌های کارت به کارت', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'سربرگ',
			array(
				array( 'box', 'کادر', '.zt-ohead', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'h4', 'عناوین', '.zt-ohead__col h4', array( 'typo', 'color' ) ),
				array( 'num', 'شماره سفارش', '.zt-ohead__num', array( 'typo', 'color' ) ),
				array( 'status', 'وضعیت', '.zt-ohead .zt-ostatus', array( 'typo', 'color', 'bg', 'height', 'radius' ) ),
				array( 'amount', 'مبلغ', '.zt-ohead__amount', array( 'typo', 'color' ) ),
				array( 'p', 'توضیحات', '.zt-ohead__col p', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$v = ZT_Order_View::get();
		if ( ! $v ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$date = ! empty( $v['created_s'] ) ? $v['created_s'] : zt_jdate( 'j F Y — H:i', $v['created'] );
		echo '<section class="zt-ohead">';
		if ( ! empty( $s['orn']['url'] ) ) {
			echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['orn'] ) ) . '" alt="">';
		}
		echo '<div class="zt-ohead__col"><h4>' . esc_html( $s['h1'] ) . '</h4><div class="zt-ohead__num">' . esc_html( zt_fa( $v['id'] ) ) . '#</div><p>' . esc_html( str_replace( '{date}', $date, $s['created_tpl'] ) ) . '</p></div>';
		echo '<div class="zt-ohead__col"><h4>' . esc_html( $s['h2'] ) . '</h4><span class="zt-ostatus" style="background:#EAEEE0;color:var(--zt-green-700);height:38px;padding-inline:20px;font-size:13.5px;font-weight:700">' . zt_icon( $v['status_ic'], array( 'style' => 'width:18px;height:18px' ) ) . ' ' . esc_html( $v['status'] ) . '</span>'; // phpcs:ignore
		if ( $v['ship'] ) {
			echo '<p>' . esc_html( str_replace( '{method}', $v['ship'], $s['ship_tpl'] ) ) . '</p>';
		}
		echo '</div><div class="zt-ohead__col"><h4>' . esc_html( $s['h3'] ) . '</h4><div class="zt-ohead__amount">' . zt_price( $v['total'] ) . '</div><p>' . esc_html( $v['pay'] ) . '</p></div>'; // phpcs:ignore
		echo '</section>';
		$o = $v['order'];
		if ( 'yes' === $s['receipt'] && $o && 'zt_card2card' === $o->get_payment_method() && $o->has_status( 'on-hold' ) ) {
			echo '<section class="zt-dbox zt-receipt"><h2 class="zt-dbox__title">' . zt_icon( 'card2' ) . ' ثبت اطلاعات واریز</h2>'; // phpcs:ignore
			echo '<div class="zt-cardinfo"><div><span>شماره کارت</span><b dir="ltr">' . esc_html( zt_fa( zt_opt( 'checkout.card_number' ) ) ) . '</b></div><div><span>به نام</span><b>' . esc_html( zt_opt( 'checkout.card_holder' ) ) . '</b></div><div><span>مبلغ</span><b>' . wp_kses_post( zt_price( $v['total'], '' ) ) . '</b></div></div>';
			if ( $o->get_meta( '_zt_receipt' ) ) {
				echo '<p class="zt-notice">اطلاعات واریز شما ثبت شد و در حال بررسی است: ' . esc_html( $o->get_meta( '_zt_receipt' ) ) . '</p>';
			} else {
				echo '<form method="post" class="zt-receipt__form">';
				wp_nonce_field( 'zt_receipt_' . $o->get_id(), 'zt_receipt_nonce' );
				echo '<input type="hidden" name="zt_receipt_order" value="' . esc_attr( $o->get_id() ) . '"><input type="hidden" name="zt_receipt_key" value="' . esc_attr( $o->get_order_key() ) . '"><input class="zt-input" name="zt_receipt" required placeholder="شماره پیگیری یا ۴ رقم آخر کارت و زمان واریز"><button class="zt-btn zt-btn--primary zt-btn--sm" type="submit">ثبت</button></form>';
			}
			echo '</section>';
		}
	}
}
