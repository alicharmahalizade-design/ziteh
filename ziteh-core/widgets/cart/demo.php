<?php
/**
 * Sample cart used in the editor / design-preview mode.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ZT_Cart_Demo' ) ) {
	/**
	 * Class ZT_Cart_Demo
	 */
	class ZT_Cart_Demo {

		/**
		 * Use the sample cart?
		 *
		 * @return bool
		 */
		public static function on() {
			if ( ZT_Context::demo() ) {
				return true;
			}
			return zt_is_editor() && ( ! zt_is_woo() || ! WC()->cart || WC()->cart->is_empty() );
		}

		/**
		 * Lines [key, name, sub, img, price(display), qty].
		 *
		 * @return array
		 */
		public static function lines() {
			return array(
				array( 'demo1', 'مام صابون‌کننده و ضد عرق خاکستر کله', '۵۰ میل', zt_asset_img( 'c-item-1.jpg' ), 785000, 1 ),
				array( 'demo2', 'تونر آبرسان گیاهی آلوئه ورا و رز', '۱۲۰ میل', zt_asset_img( 'c-item-2.jpg' ), 785000, 1 ),
				array( 'demo3', 'ماسک مو تغذیه‌کننده زیته با کره شی', '۲۰۰ میل', zt_asset_img( 'c-item-3.jpg' ), 340000, 1 ),
			);
		}

		/**
		 * Sample state.
		 *
		 * @return array
		 */
		public static function state() {
			return array(
				'count'            => 3,
				'subtotal'         => 1910000,
				'discount'         => 0,
				'total'            => 1910000,
				'subtotal_tier'    => 1910000,
				'free_shipping'    => false,
				'samples_unlocked' => true,
				'sample'           => -1,
				'tiers'            => array(
					'msg'  => 'شما تا ۵۹۰,۰۰۰ تومان دیگر تا دریافت ۳٪ تخفیف فاصله دارید',
					'bar'  => 76,
					'fill' => -1,
					'done' => array(),
				),
			);
		}
	}
}
