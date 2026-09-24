<?php
/**
 * Cart: items table (quantity, remove, clear, swipe to delete).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Cart_Items
 */
class ZT_W_Cart_Items extends ZT_Widget_Base {

	protected $zt_group = 'cart';
	protected $zt_icon  = 'eicon-cart';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-cart-items';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'محصولات سبد خرید';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'cart' );
		$this->ctl( 'title', 'text', 'عنوان', 'محصولات سبد خرید' );
		$this->ctl( 'cols', 'text', 'عنوان ستون‌ها (با کاما)', 'محصول,قیمت,تعداد,مبلغ کل' );
		$this->ctl( 'refresh', 'text', 'دکمه به‌روزرسانی', 'به‌روزرسانی سبد خرید' );
		$this->ctl( 'clear', 'text', 'دکمه پاک‌سازی', 'پاک‌سازی سبد' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'جدول سبد',
			array(
				array( 'card', 'کارت', '.zt-pcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-pcard__title', self::fx( 'text' ) ),
				array( 'th', 'سرستون‌ها', '.zt-ctable th', array( 'typo', 'color' ) ),
				array( 'img', 'تصویر', '.zt-citem img', array( 'size', 'radius' ) ),
				array( 'name', 'نام محصول', '.zt-citem b', array( 'typo', 'color' ) ),
				array( 'sub', 'حجم', '.zt-citem span', array( 'typo', 'color' ) ),
				array( 'price', 'مبالغ', '.zt-cprice', array( 'typo', 'color' ) ),
				array( 'rm', 'دکمه حذف', '.zt-crm', array( 'color', 'hover_color', 'bg_hover', 'size' ) ),
				array( 'btn', 'دکمه‌های پایین', '.zt-ctable-foot .zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$lines = array();
		if ( ZT_Cart_Demo::on() ) {
			$lines = ZT_Cart_Demo::lines();
		} elseif ( zt_is_woo() && WC()->cart ) {
			$cart = WC()->cart;
			foreach ( $cart->get_cart() as $key => $item ) {
				$p = $item['data'];
				if ( ! $p || ! $p->exists() ) {
					continue;
				}
				$parent  = $p->get_parent_id() ? $p->get_parent_id() : $p->get_id();
				$vol     = get_post_meta( $parent, '_zt_volume', true );
				$sub     = $p->is_type( 'variation' ) ? wc_get_formatted_variation( $p, true, false, false ) : $vol;
				$unit    = (float) ( $cart->display_prices_including_tax() ? wc_get_price_including_tax( $p ) : wc_get_price_excluding_tax( $p ) );
				$img     = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ) : wc_placeholder_img_src();
				$lines[] = array( $key, $p->get_name(), $sub, $img, zt_amount( $unit ), (int) $item['quantity'], $p->get_permalink(), $p->managing_stock() && ! $p->backorders_allowed() ? max( 1, (int) $p->get_stock_quantity() ) : 99 );
			}
		}
		$cols = array_pad( array_map( 'trim', explode( ',', $s['cols'] ) ), 4, '' );
		echo '<section class="zt-pcard" data-zt-cart-items><h2 class="zt-pcard__title" style="margin-bottom:24px">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		echo '<table class="zt-ctable"' . ( $lines ? '' : ' hidden' ) . '><thead><tr><th>' . esc_html( $cols[0] ) . '</th><th>' . esc_html( $cols[1] ) . '</th><th>' . esc_html( $cols[2] ) . '</th><th>' . esc_html( $cols[3] ) . '</th><th></th></tr></thead><tbody>';
		foreach ( $lines as $l ) {
			$name = isset( $l[6] ) ? '<a href="' . esc_url( $l[6] ) . '">' . esc_html( $l[1] ) . '</a>' : esc_html( $l[1] );
			echo '<tr data-zt-line="' . esc_attr( $l[0] ) . '" data-price="' . esc_attr( $l[4] ) . '">';
			echo '<td><div class="zt-citem"><img src="' . esc_url( $l[3] ) . '" alt=""><div><b>' . $name . '</b>' . ( $l[2] ? '<span>' . esc_html( zt_fa( $l[2] ) ) . '</span>' : '' ) . '</div></div></td>'; // phpcs:ignore
			echo '<td><div class="zt-cprice">' . esc_html( zt_money( $l[4] ) ) . ' <small>' . esc_html( zt_currency() ) . '</small></div></td>';
			echo '<td>' . ZT_Parts::qty( $l[5], array(), '', isset( $l[7] ) ? $l[7] : 99 ) . '</td>'; // phpcs:ignore
			echo '<td><div class="zt-cprice"><span data-zt-line-total>' . esc_html( zt_money( $l[4] * $l[5] ) ) . '</span> <small>' . esc_html( zt_currency() ) . '</small></div></td>';
			echo '<td><button class="zt-crm" data-zt-remove aria-label="حذف">' . zt_icon( 'x' ) . '</button></td></tr>'; // phpcs:ignore
		}
		echo '</tbody></table>';
		echo '<div class="zt-cart-empty"' . ( $lines ? ' hidden' : '' ) . '><span class="zt-cart-empty__ic">' . zt_icon( 'basket' ) . '</span><b>' . esc_html( zt_opt( 'cart.empty_title' ) ) . '</b><p>' . esc_html( zt_opt( 'cart.empty_text' ) ) . '</p><a class="zt-btn zt-btn--primary" href="' . esc_url( zt_page_url( 'shop' ) ) . '">' . esc_html( zt_opt( 'cart.empty_button' ) ) . '</a></div>'; // phpcs:ignore
		echo '<div class="zt-ctable-foot"' . ( $lines ? '' : ' hidden' ) . '><button class="zt-btn zt-btn--ghost zt-btn--sm" data-zt-cart-refresh>' . zt_icon( 'refresh' ) . ' ' . esc_html( $s['refresh'] ) . '</button><button class="zt-btn zt-btn--ghost zt-btn--sm" data-zt-cart-clear>' . zt_icon( 'trash' ) . ' ' . esc_html( $s['clear'] ) . '</button></div>'; // phpcs:ignore
		echo '</section>';
	}
}
