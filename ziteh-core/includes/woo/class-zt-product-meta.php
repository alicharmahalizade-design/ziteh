<?php
/**
 * Built-in product fields used by the product template (English name,
 * expiry, volume, brand, features, ingredients, usage, property strip…).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Product_Meta
 */
class ZT_Product_Meta {

	/**
	 * Field definitions: key => [label, type, help].
	 *
	 * @return array
	 */
	public static function fields() {
		return array(
			'_zt_en_name'     => array( 'نام انگلیسی', 'text', 'مثلا: Anti Hair Fall Shock Shampoo' ),
			'_zt_short_title' => array( 'عنوان کوتاه (اپ‌بار موبایل)', 'text', 'خالی = عنوان محصول' ),
			'_zt_crumb_title' => array( 'عنوان در مسیر راهنما', 'text', 'خالی = عنوان محصول' ),
			'_zt_card_cat'    => array( 'برچسب دسته روی کارت محصول', 'text', 'خالی = اولین دسته محصول' ),
			'_zt_expiry'      => array( 'تاریخ انقضا (شمسی)', 'text', 'مثلا ۱۴۰۸/۰۵ — روی تصویر محصول نمایش داده می‌شود' ),
			'_zt_volume'      => array( 'حجم / وزن', 'text', 'مثلا ۵۰۰ میلی‌لیتر' ),
			'_zt_brand'       => array( 'برند (باکس خرید)', 'text', 'خالی = نام برند سایت' ),
			'_zt_note'        => array( 'توضیح زیر برند', 'text', 'مثلا: محصولی از طبیعت برای مراقبت از شما' ),
			'_zt_flag'        => array( 'برچسب باکس خرید', 'text', 'مثلا: پیشنهاد ویژه — خالی = پنهان' ),
			'_zt_features'    => array( 'ویژگی‌ها (هر خط: آیکون|متن)', 'lines', 'مثلا: droplet|تقویت فولیکول‌ها' ),
			'_zt_desc_title'  => array( 'عنوان تب توضیحات', 'text', '' ),
			'_zt_desc_image'  => array( 'تصویر تب توضیحات', 'image', '' ),
			'_zt_props'       => array( 'نوار ویژگی‌ها زیر توضیحات (هر خط: آیکون|متن)', 'lines', 'مثلا: flask|بدون پارابن' ),
			'_zt_ingredients' => array( 'ترکیبات (هر خط یک مورد)', 'lines', '' ),
			'_zt_usage'       => array( 'نحوه استفاده (هر خط یک مورد)', 'lines', '' ),
			'_zt_sections'    => array( 'نمایش در بخش‌ها (کلیدها با کاما)', 'text', 'مثلا: selected — برای ویجت‌هایی که منبعشان «علامت‌خورده» است' ),
		);
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'box' ) );
		add_action( 'save_post_product', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Register box.
	 */
	public static function box() {
		add_meta_box( 'zt-product', 'اطلاعات اختصاصی زیته', array( __CLASS__, 'render' ), 'product', 'normal', 'high' );
	}

	/**
	 * Render.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'zt_product', 'zt_product_nonce' );
		wp_enqueue_media();
		echo '<table class="form-table zt-meta-table"><tbody>';
		foreach ( self::fields() as $key => $f ) {
			$v = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
			if ( 'lines' === $f[1] ) {
				echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="4" class="large-text">' . esc_textarea( $v ) . '</textarea>';
			} elseif ( 'image' === $f[1] ) {
				$url = $v ? wp_get_attachment_image_url( (int) $v, 'thumbnail' ) : '';
				echo '<input type="hidden" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $v ) . '"><img class="zt-media-prev" src="' . esc_url( $url ) . '" style="max-width:90px;border-radius:8px;display:' . ( $url ? 'block' : 'none' ) . '"> ';
				echo '<button type="button" class="button zt-media-pick" data-target="' . esc_attr( $key ) . '">انتخاب تصویر</button> <button type="button" class="button-link zt-media-clear" data-target="' . esc_attr( $key ) . '">حذف</button>';
			} else {
				echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $v ) . '" class="regular-text">';
			}
			if ( $f[2] ) {
				echo '<p class="description">' . esc_html( $f[2] ) . '</p>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
		echo '<p class="description">نام آیکون‌ها: ' . esc_html( implode( '، ', array_slice( array_keys( ZT_Settings::icon_choices() ), 1 ) ) ) . '</p>';
		?>
		<script>
		jQuery(function($){
			$(document).on('click','.zt-media-pick',function(e){e.preventDefault();var t=$(this).data('target'),b=$(this);
				var f=wp.media({title:'انتخاب تصویر',multiple:false,library:{type:'image'}});
				f.on('select',function(){var a=f.state().get('selection').first().toJSON();$('#'+t).val(a.id);b.siblings('.zt-media-prev').attr('src',(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url)).show();});f.open();});
			$(document).on('click','.zt-media-clear',function(e){e.preventDefault();var t=$(this).data('target');$('#'+t).val('');$(this).siblings('.zt-media-prev').hide();});
		});
		</script>
		<?php
	}

	/**
	 * Save.
	 *
	 * @param int     $post_id Id.
	 * @param WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['zt_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zt_product_nonce'] ), 'zt_product' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( self::fields() as $key => $f ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore
			$val = 'lines' === $f[1] ? sanitize_textarea_field( $raw ) : ( 'image' === $f[1] ? absint( $raw ) : sanitize_text_field( $raw ) );
			if ( '' === $val || 0 === $val ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $val );
			}
		}
	}

	/**
	 * "icon|text" lines → [ [icon, text], ... ].
	 *
	 * @param string $text Lines.
	 * @param string $icon Default icon.
	 * @return array
	 */
	public static function pairs( $text, $icon = 'check-circle' ) {
		$out = array();
		foreach ( zt_lines( $text ) as $l ) {
			$p     = array_map( 'trim', explode( '|', $l, 2 ) );
			$out[] = isset( $p[1] ) ? array( sanitize_key( $p[0] ), $p[1] ) : array( $icon, $p[0] );
		}
		return $out;
	}
}
