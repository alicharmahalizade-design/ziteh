<?php
/**
 * Product: gallery (main image, thumbnails, discount badge, expiry).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Gallery
 */
class ZT_W_Product_Gallery extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-images';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-gallery';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'گالری محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'گالری' );
		$this->ctl( 'badge', 'switch', 'نشان تخفیف', true );
		$this->ctl( 'badge_text', 'text', 'متن نشان ({pct} = درصد)', '{pct}٪ تخفیف' );
		$this->ctl( 'exp', 'switch', 'نمایش تاریخ انقضا', true );
		$this->ctl( 'exp_label', 'text', 'برچسب انقضا', 'انقضا' );
		$this->ctl( 'arrows', 'switch', 'فلش‌ها', true );
		$this->ctl( 'thumbs', 'switch', 'تصاویر کوچک', true );
		$this->ctl( 'max_thumbs', 'number', 'حداکثر تصاویر کوچک', 5 );
		$this->ctl( 'demo_note', 'notice', '', 'اطلاعات به‌صورت خودکار از محصول فعلی خوانده می‌شود (تصویر شاخص + گالری ووکامرس). در ویرایشگر قالب، محصول پیش‌نمایش نمایش داده می‌شود.' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'گالری',
			array(
				array( 'main', 'تصویر اصلی', '.zt-gallery__main', array( 'radius', 'ratio', 'bg', 'shadow' ) ),
				array( 'img', 'نحوه نمایش تصویر', '.zt-gallery__main img', array( 'fit' ) ),
				array( 'badge', 'نشان تخفیف', '.zt-gallery__main .zt-badge-off', self::fx( 'badge' ) ),
				array( 'exp', 'نشان انقضا', '.zt-gallery__main .zt-exp', array( 'typo', 'bg', 'display' ) ),
				array( 'nav', 'فلش‌ها', '.zt-gallery__nav', array( 'size', 'color', 'bg', 'display' ) ),
				array( 'thumbs', 'تصاویر کوچک', '.zt-gallery__thumbs', array( 'columns', 'gap', 'margin', 'display' ) ),
				array( 'thumb', 'هر تصویر کوچک', '.zt-gallery__thumbs button', array( 'radius', 'border_color', 'bg' ) ),
				array( 'thumba', 'تصویر کوچک فعال', '.zt-gallery__thumbs button.zt-is-active', array( 'border_color' ) ),
			)
		);
	}

	/**
	 * Data from product or demo.
	 *
	 * @return array [main, thumbs[[thumb, full]], pct, exp, alt]
	 */
	private function data() {
		$p = ZT_Context::product();
		if ( $p ) {
			$ids   = array_filter( array_merge( array( $p->get_image_id() ), $p->get_gallery_image_ids() ) );
			$thumb = array();
			foreach ( $ids as $id ) {
				$thumb[] = array( wp_get_attachment_image_url( $id, 'woocommerce_thumbnail' ), wp_get_attachment_image_url( $id, 'large' ) );
			}
			$main = $ids ? wp_get_attachment_image_url( reset( $ids ), 'large' ) : wc_placeholder_img_src( 'large' );
			$it   = ZT_Parts::product_item( $p );
			return array( $main, $thumb, $it['pct'], $it['exp'], $p->get_name() );
		}
		$thumbs = array( array( zt_asset_img( 'p-thumb-1.jpg' ), zt_asset_img( 'p-main.jpg' ) ) );
		for ( $i = 2; $i <= 5; $i++ ) {
			$thumbs[] = array( zt_asset_img( 'p-thumb-' . $i . '.jpg' ), zt_asset_img( 'p-thumb-' . $i . '.jpg' ) );
		}
		return array( zt_asset_img( 'p-main.jpg' ), $thumbs, 18, '۱۴۰۸/۰۵', 'شامپو تقویت‌کننده و ضد ریزش موی زیته' );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		list( $main, $thumbs, $pct, $exp, $alt ) = $this->data();
		$thumbs = array_slice( $thumbs, 0, max( 1, (int) $s['max_thumbs'] ) );
		echo '<div class="zt-gallery" data-zt-gallery><div class="zt-gallery__main">';
		if ( 'yes' === $s['badge'] && $pct ) {
			echo '<span class="zt-badge-off">' . esc_html( zt_fa( str_replace( '{pct}', $pct, $s['badge_text'] ) ) ) . '</span>';
		}
		echo '<img src="' . esc_url( $main ) . '" alt="' . esc_attr( $alt ) . '" data-zt-gallery-main fetchpriority="high">';
		if ( 'yes' === $s['exp'] ) {
			echo ZT_Parts::exp( $exp, $s['exp_label'] ); // phpcs:ignore
		}
		if ( 'yes' === $s['arrows'] && count( $thumbs ) > 1 ) {
			echo '<button class="zt-iconbtn zt-gallery__nav zt-gallery__nav--prev" data-zt-gallery-dir="prev" aria-label="قبلی">' . zt_icon( 'chev-left' ) . '</button>'; // phpcs:ignore
			echo '<button class="zt-iconbtn zt-gallery__nav zt-gallery__nav--next" data-zt-gallery-dir="next" aria-label="بعدی">' . zt_icon( 'chev-right' ) . '</button>'; // phpcs:ignore
		}
		echo '</div>';
		if ( 'yes' === $s['thumbs'] && count( $thumbs ) > 1 ) {
			echo '<div class="zt-gallery__thumbs">';
			foreach ( $thumbs as $i => $t ) {
				echo '<button' . ( 0 === $i ? ' class="zt-is-active"' : '' ) . ' data-zt-gallery-thumb data-full="' . esc_url( $t[1] ) . '"><img src="' . esc_url( $t[0] ) . '" alt=""></button>';
			}
			echo '</div>';
		}
		echo '</div>';
	}
}
