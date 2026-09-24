<?php
/**
 * "کارت به کارت" payment gateway (order goes on-hold until the transfer is verified).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Gateway_Card
 */
class ZT_Gateway_Card extends WC_Payment_Gateway {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'zt_card2card';
		$this->method_title       = 'کارت به کارت (زیته)';
		$this->method_description = 'مشتری مبلغ را کارت به کارت می‌کند و شماره پیگیری/فیش را در صفحه پیگیری سفارش ثبت می‌کند. شماره کارت در «زیته ← صورت‌حساب» تنظیم می‌شود.';
		$this->has_fields         = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->title       = $this->get_option( 'title', 'کارت به کارت' );
		$this->description = $this->get_option( 'description', 'پرداخت کارت به کارت و ارسال فیش' );
		$this->enabled     = $this->get_option( 'enabled', 'yes' );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou' ) );
	}

	/**
	 * Settings.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => 'فعال',
				'type'    => 'checkbox',
				'label'   => 'فعال‌سازی کارت به کارت',
				'default' => 'yes',
			),
			'title'       => array(
				'title'   => 'عنوان',
				'type'    => 'text',
				'default' => 'کارت به کارت',
			),
			'description' => array(
				'title'   => 'توضیح',
				'type'    => 'textarea',
				'default' => 'پرداخت کارت به کارت و ارسال فیش',
			),
		);
	}

	/**
	 * Card details under the option.
	 */
	public function payment_fields() {
		echo '<div class="zt-cardinfo"><div><span>شماره کارت</span><b dir="ltr">' . esc_html( zt_fa( zt_opt( 'checkout.card_number' ) ) ) . '</b></div>';
		echo '<div><span>به نام</span><b>' . esc_html( zt_opt( 'checkout.card_holder' ) ) . '</b></div>';
		echo '<div><span>بانک</span><b>' . esc_html( zt_opt( 'checkout.card_bank' ) ) . '</b></div>';
		echo '<p>' . esc_html( zt_opt( 'checkout.card_instructions' ) ) . '</p></div>';
	}

	/**
	 * Process.
	 *
	 * @param int $order_id Order.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'on-hold', 'در انتظار تایید پرداخت کارت به کارت.' );
		wc_reduce_stock_levels( $order_id );
		WC()->cart->empty_cart();
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Thank-you note.
	 */
	public function thankyou() {
		echo '<p>' . esc_html( zt_opt( 'checkout.card_instructions' ) ) . '</p>';
	}
}
