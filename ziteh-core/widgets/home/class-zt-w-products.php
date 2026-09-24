<?php
/**
 * Home: product carousel section (e.g. "selected products").
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-zt-w-offers.php';

/**
 * Class ZT_W_Products
 */
class ZT_W_Products extends ZT_W_Offers {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-products';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-products';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'اسلایدر محصولات (محصولات منتخب)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'محصولات منتخب زیته', array( 'link_text' => 'مشاهده همه', 'link_url' => '{{shop}}' ) );
		$this->end_controls_section();
		$this->section( 'c2', 'محصولات' );
		$this->product_source_controls(
			array(
				array( 'title' => 'شامپو ضد شوره ملایم D1 پرایم', 'cat' => 'شامپو', 'img' => 'sel-1.jpg', 'price' => 690000, 'regular' => 0, 'exp' => '۱۴۰۷/۰۹', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'ماسک لب آبرسان و حجم دهنده آردن اکسپرتیج', 'cat' => 'مراقبت از لب', 'img' => 'sel-2.jpg', 'price' => 495000, 'regular' => 0, 'exp' => '۱۴۰۷/۰۶', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'استیک ضد آفتاب سولار شیلد SPF50 بی رنگ پوست خشک سان...', 'cat' => 'ضد آفتاب', 'img' => 'sel-3.jpg', 'price' => 1099000, 'regular' => 0, 'exp' => '۱۴۰۸/۰۳', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'کرم ژل مرطوب کننده پوست خشک، حساس و مستعد قرمزی...', 'cat' => 'کرم مرطوب کننده پوست حساس', 'img' => 'sel-4.jpg', 'price' => 1548000, 'regular' => 0, 'exp' => '۱۴۰۷/۱۲', 'url' => '{{shop}}', 'pid' => 0 ),
			),
			array(
				'query' => 'meta',
				'limit' => 8,
			)
		);
		$this->ctl( 'badge', 'switch', 'نشان درصد تخفیف روی تصویر', true );
		$this->ctl( 'exp', 'switch', 'نمایش تاریخ انقضا', true );
		$this->ctl( 'exp_label', 'text', 'برچسب انقضا', 'انقضا' );
		$this->ctl( 'stock', 'switch', 'هشدار «فقط X عدد باقی مانده»', false );
		$this->ctl( 'arrows', 'switch', 'فلش‌ها', true );
		$this->end_controls_section();
		$this->section_wrap_controls( 'cream' );
		$this->style_section( 'sth', 'عنوان بخش', array( array( 'title', 'عنوان', '.zt-sec-title', self::fx( 'text' ) ), array( 'more', 'لینک', '.zt-link-more', self::fx( 'link' ) ), array( 'head', 'کادر عنوان', '.zt-sec-head', array( 'margin' ) ) ) );
		$this->product_card_styles();
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$items = $this->get_products( $s );
		$this->section_open( $s );
		$this->heading_render( $s );
		echo '<div class="zt-prods' . esc_attr( $this->reveal( $s ) ) . '" data-zt-carousel><div class="zt-prod-track" data-zt-track>';
		foreach ( $items as $it ) {
			echo ZT_Parts::product_card( $it, array( 'badge' => 'yes' === $s['badge'], 'exp' => 'yes' === $s['exp'], 'exp_label' => $s['exp_label'], 'stock' => 'yes' === $s['stock'] ) ); // phpcs:ignore
		}
		echo '</div>';
		if ( 'yes' === $s['arrows'] ) {
			$this->carousel_arrows( '42%' );
		}
		echo '</div>';
		$this->section_close( $s );
	}
}
