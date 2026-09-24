<?php
/**
 * Product: buy box (price, quantity, variations, add to cart, wishlist).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Buybox
 */
class ZT_W_Product_Buybox extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-add-to-cart';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-buybox';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'باکس خرید محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'باکس خرید' );
		$this->ctl( 'flag', 'text', 'برچسب بالا (خالی = از فیلد محصول)', '' );
		$this->ctl( 'flag_default', 'text', 'برچسب پیش‌فرض محصولات تخفیف‌دار', 'پیشنهاد ویژه' );
		$this->ctl( 'brand_icon', 'icon', 'آیکون برند', 'leaf' );
		$this->ctl( 'note_default', 'text', 'توضیح زیر برند (پیش‌فرض)', 'محصولی از طبیعت برای مراقبت از شما' );
		$this->ctl( 'off_text', 'text', 'متن درصد تخفیف', '{pct}٪ تخفیف' );
		$this->ctl( 'btn', 'text', 'متن دکمه', 'افزودن به سبد خرید' );
		$this->ctl( 'btn_icon', 'icon', 'آیکون دکمه', 'bag' );
		$this->ctl( 'oos', 'text', 'متن ناموجود', 'ناموجود' );
		$this->ctl( 'choose', 'text', 'پیام انتخاب گزینه (محصول متغیر)', 'لطفاً گزینه‌های محصول را انتخاب کنید' );
		$this->ctl( 'wish', 'text', 'متن علاقه‌مندی', 'افزودن به لیست علاقه‌مندی' );
		$this->repeater(
			'mini',
			'مزیت‌های پایین',
			array(
				array( 'icon', 'icon', 'آیکون', 'truck-fast' ),
				array( 'text', 'text', 'متن', '' ),
			),
			array(
				array( 'icon' => 'truck-fast', 'text' => 'ارسال سریع' ),
				array( 'icon' => 'check-circle', 'text' => 'ضمانت اصالت' ),
				array( 'icon' => 'shield', 'text' => 'پرداخت امن' ),
			),
			'{{{ text }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'باکس خرید',
			array(
				array( 'box', 'کادر', '.zt-buybox', array( 'bg', 'radius', 'padding', 'shadow', 'border', 'align' ) ),
				array( 'flag', 'برچسب', '.zt-buybox__flag', self::fx( 'badge' ) ),
				array( 'brand', 'برند', '.zt-buybox__brand', array( 'typo', 'color' ) ),
				array( 'note', 'توضیح', '.zt-buybox__note', self::fx( 'text' ) ),
				array( 'hr', 'خط جداکننده', '.zt-buybox hr', array( 'bg', 'margin', 'display' ) ),
				array( 'old', 'قیمت قبلی', '.zt-buybox__old', array( 'typo', 'color' ) ),
				array( 'off', 'درصد تخفیف', '.zt-buybox__off', self::fx( 'badge' ) ),
				array( 'price', 'قیمت', '.zt-buybox__price', array( 'typo', 'color', 'margin' ) ),
				array( 'qty', 'تعداد', '.zt-buybox .zt-qty', array( 'height', 'bg', 'border', 'radius' ) ),
				array( 'btn', 'دکمه', '.zt-buybox .zt-btn--primary', self::fx( 'button' ) ),
				array( 'wish', 'علاقه‌مندی', '.zt-buybox__wish', self::fx( 'link' ) ),
				array( 'mini', 'مزیت‌ها', '.zt-buybox__mini span', array( 'typo', 'color' ) ),
				array( 'minii', 'آیکون مزیت‌ها', '.zt-buybox__mini svg', array( 'size', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p = ZT_Context::product();
		if ( $p ) {
			$it       = ZT_Parts::product_item( $p );
			$pid      = $p->get_id();
			$flag     = '' !== $s['flag'] ? $s['flag'] : get_post_meta( $pid, '_zt_flag', true );
			$flag     = $flag ? $flag : ( $it['pct'] ? $s['flag_default'] : '' );
			$brand    = get_post_meta( $pid, '_zt_brand', true );
			$brand    = $brand ? $brand : zt_opt( 'general.brand_name' );
			$note     = get_post_meta( $pid, '_zt_note', true );
			$note     = $note ? $note : $s['note_default'];
			$price    = $it['price'];
			$regular  = $it['regular'];
			$pct      = $it['pct'];
			$buyable  = $p->is_purchasable() && $p->is_in_stock();
			$max      = $p->managing_stock() && ! $p->backorders_allowed() ? max( 1, (int) $p->get_stock_quantity() ) : 99;
			$vars     = array();
			$attrs_ui = '';
			if ( $p->is_type( 'variable' ) ) {
				foreach ( $p->get_available_variations( 'objects' ) as $v ) {
					$vars[] = array(
						'id'         => $v->get_id(),
						'attributes' => $v->get_variation_attributes(),
						'price'      => zt_amount( $v->get_price() ),
						'regular'    => zt_amount( $v->get_regular_price() ),
						'image'      => $v->get_image_id() ? wp_get_attachment_image_url( $v->get_image_id(), 'large' ) : '',
						'max'        => $v->managing_stock() ? (int) $v->get_stock_quantity() : 99,
					);
				}
				foreach ( $p->get_variation_attributes() as $name => $options ) {
					$field     = 'attribute_' . sanitize_title( $name );
					$attrs_ui .= '<label class="zt-field zt-bb-attr"><span class="zt-label">' . esc_html( wc_attribute_label( $name, $p ) ) . '</span><select class="zt-select" name="' . esc_attr( $field ) . '" data-zt-attr><option value="">انتخاب کنید</option>';
					$terms     = taxonomy_exists( $name ) ? wc_get_product_terms( $pid, $name, array( 'fields' => 'all' ) ) : array();
					if ( $terms ) {
						foreach ( $terms as $t ) {
							if ( in_array( $t->slug, $options, true ) ) {
								$attrs_ui .= '<option value="' . esc_attr( $t->slug ) . '">' . esc_html( $t->name ) . '</option>';
							}
						}
					} else {
						foreach ( $options as $o ) {
							$attrs_ui .= '<option value="' . esc_attr( $o ) . '">' . esc_html( $o ) . '</option>';
						}
					}
					$attrs_ui .= '</select></label>';
				}
			}
		} else {
			$pid      = 0;
			$flag     = '' !== $s['flag'] ? $s['flag'] : $s['flag_default'];
			$brand    = zt_opt( 'general.brand_name' );
			$note     = $s['note_default'];
			$price    = 385000 * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			$regular  = 450000 * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			$pct      = 18;
			$buyable  = true;
			$max      = 99;
			$vars     = array();
			$attrs_ui = '';
		}
		$on = $pid && class_exists( 'ZT_Wishlist' ) && ZT_Wishlist::has( $pid );
		echo '<aside class="zt-buybox" data-zt-buybox data-zt-variations="' . esc_attr( wp_json_encode( $vars ) ) . '">';
		if ( $flag ) {
			echo '<span class="zt-buybox__flag">' . esc_html( $flag ) . '</span>';
		}
		echo '<div class="zt-buybox__brand">' . zt_icon( $s['brand_icon'] ) . ' ' . esc_html( $brand ) . '</div>'; // phpcs:ignore
		echo '<p class="zt-buybox__note">' . esc_html( $note ) . '</p><hr>';
		echo '<div class="zt-buybox__old" data-zt-bb-old' . ( $pct && $regular > $price ? '' : ' hidden' ) . '><del>' . esc_html( zt_money( $regular, true ) . ' ' . zt_currency() ) . '</del><span class="zt-buybox__off">' . esc_html( zt_fa( str_replace( '{pct}', $pct, $s['off_text'] ) ) ) . '</span></div>';
		echo '<div class="zt-buybox__price" data-zt-bb-price>' . zt_price( $price ) . '</div>'; // phpcs:ignore
		echo $attrs_ui; // phpcs:ignore
		if ( $buyable ) {
			echo ZT_Parts::qty( 1, array(), '', $max ); // phpcs:ignore
			echo '<a href="' . esc_url( $pid ? '?add-to-cart=' . $pid : zt_page_url( 'cart' ) ) . '" class="zt-btn zt-btn--primary zt-btn--block" data-zt-buybox-add="' . esc_attr( $pid ) . '" data-zt-choose="' . esc_attr( $s['choose'] ) . '">' . zt_icon( $s['btn_icon'] ) . ' ' . esc_html( $s['btn'] ) . '</a>'; // phpcs:ignore
		} else {
			echo '<span class="zt-btn zt-btn--ghost zt-btn--block zt-is-disabled">' . esc_html( $s['oos'] ) . '</span>';
		}
		echo '<a href="#" class="zt-buybox__wish' . ( $on ? ' zt-is-on' : '' ) . '" data-zt-wish="' . esc_attr( $pid ) . '">' . zt_icon( $on ? 'heart-fill' : 'heart' ) . ' ' . esc_html( $s['wish'] ) . '</a><hr>'; // phpcs:ignore
		echo '<div class="zt-buybox__mini">';
		foreach ( (array) $s['mini'] as $m ) {
			echo '<div>' . zt_icon( $m['icon'] ) . '<span>' . esc_html( $m['text'] ) . '</span></div>'; // phpcs:ignore
		}
		echo '</div></aside>';
	}
}
