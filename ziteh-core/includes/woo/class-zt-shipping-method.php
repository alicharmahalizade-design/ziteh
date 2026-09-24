<?php
/**
 * "Ziteh shipping" — one WooCommerce shipping method that produces the rates
 * defined in Ziteh → Checkout → shipping options (with city rules).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Shipping_Method
 */
class ZT_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'zt_shipping';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = 'ارسال زیته';
		$this->method_description = 'گزینه‌های ارسال (پیک، پست پیشتاز، پس‌کرایه و…) از «زیته ← صورت‌حساب ← روش‌های ارسال» خوانده می‌شوند.';
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
		$this->instance_form_fields = array(
			'title' => array(
				'title'   => 'عنوان',
				'type'    => 'text',
				'default' => 'ارسال زیته',
			),
		);
		$this->title   = $this->get_option( 'title', 'ارسال زیته' );
		$this->enabled = 'yes';
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * City list normaliser.
	 *
	 * @param string $text Lines.
	 * @return string[]
	 */
	private static function cities( $text ) {
		return array_map( 'zt_normalize_fa', zt_lines( $text ) );
	}

	/**
	 * Calculate rates.
	 *
	 * @param array $package Package.
	 */
	public function calculate_shipping( $package = array() ) {
		$city = zt_normalize_fa( isset( $package['destination']['city'] ) ? $package['destination']['city'] : '' );
		$mul  = max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
		foreach ( (array) zt_opt( 'checkout.shipping', array() ) as $opt ) {
			if ( empty( $opt['enabled'] ) || empty( $opt['id'] ) ) {
				continue;
			}
			$only = self::cities( $opt['cities'] );
			$not  = self::cities( $opt['exclude_cities'] );
			if ( $city && $only && ! in_array( $city, $only, true ) ) {
				continue;
			}
			if ( $city && $not && in_array( $city, $not, true ) ) {
				continue;
			}
			$this->add_rate(
				array(
					'id'        => $this->id . ':' . sanitize_key( $opt['id'] ),
					'label'     => $opt['title'],
					'cost'      => (float) $opt['cost'] * $mul,
					'package'   => $package,
					'meta_data' => array(
						'zt_opt'  => sanitize_key( $opt['id'] ),
						'zt_free' => ! empty( $opt['free_by_tier'] ) ? 1 : 0,
					),
				)
			);
		}
	}
}
