<?php
/**
 * Home: special offers — countdown banner + product carousel.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Offers
 */
class ZT_W_Offers extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-countdown';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-offers';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'پیشنهادهای ویژه (شمارش معکوس)';
	}

	/**
	 * Demo rows.
	 *
	 * @return array
	 */
	public static function demo() {
		return array(
			array( 'title' => 'عطر خانه ارکید اواسیس ویت یو', 'cat' => 'خوشبو کننده محیط', 'img' => 'offer-1.jpg', 'price' => 900000, 'regular' => 980000, 'exp' => '۱۴۰۸/۰۲', 'url' => '{{shop}}', 'pid' => 0 ),
			array( 'title' => 'عطر خانه پلیس گالا ویت یو', 'cat' => 'خوشبو کننده محیط', 'img' => 'offer-2.jpg', 'price' => 900000, 'regular' => 980000, 'exp' => '۱۴۰۸/۰۴', 'url' => '{{shop}}', 'pid' => 0 ),
			array( 'title' => 'عطر خانه اربیتال سوک ویت یو', 'cat' => 'خوشبو کننده محیط', 'img' => 'offer-3.jpg', 'price' => 900000, 'regular' => 980000, 'exp' => '۱۴۰۸/۰۱', 'url' => '{{shop}}', 'pid' => 0 ),
			array( 'title' => 'عطر خانه فارست کاتیج ویت یو', 'cat' => 'خوشبو کننده محیط', 'img' => 'offer-4.jpg', 'price' => 900000, 'regular' => 980000, 'exp' => '۱۴۰۷/۱۱', 'url' => '{{shop}}', 'pid' => 0 ),
		);
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'بنر و شمارش معکوس' );
		$this->ctl( 'show_banner', 'switch', 'نمایش بنر', true );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'فرصت های ویژه در زیته' );
		$this->ctl( 'text', 'text', 'متن', 'تخفیف‌های شگفت‌انگیز، تا پایان شمارش معکوس!' );
		$this->ctl(
			'cd_mode',
			'select',
			'حالت شمارش معکوس',
			'evergreen',
			array(
				'options' => array(
					'evergreen' => 'مدت ثابت برای هر بازدیدکننده (تکرارشونده)',
					'date'      => 'تا تاریخ و ساعت مشخص',
					'sale'      => 'تا پایان نزدیک‌ترین حراج محصولات',
					'daily'     => 'تا پایان امروز',
				),
			)
		);
		$this->ctl( 'cd_seconds', 'number', 'مدت (ثانیه)', 9581, array( 'condition' => array( 'cd_mode' => 'evergreen' ) ) );
		$this->ctl( 'cd_date', 'date', 'تاریخ پایان', '', array( 'condition' => array( 'cd_mode' => 'date' ) ) );
		$this->ctl( 'cd_labels', 'text', 'برچسب‌ها (ثانیه,دقیقه,ساعت,روز)', 'ثانیه,دقیقه,ساعت,روز' );
		$this->ctl( 'cd_end', 'select', 'پس از پایان', 'zero', array( 'options' => array( 'zero' => 'نمایش صفر', 'hide' => 'پنهان کردن بخش', 'restart' => 'شروع مجدد' ) ) );
		$this->ctl( 'btn', 'text', 'متن دکمه', 'مشاهده همه' );
		$this->ctl( 'btn_link', 'url', 'لینک دکمه', '{{shop}}?on_sale=1' );
		$this->end_controls_section();

		$this->section( 'c2', 'محصولات' );
		$this->ctl( 'show_products', 'switch', 'نمایش محصولات', true );
		$this->product_source_controls( self::demo(), array( 'query' => 'on_sale', 'limit' => 8 ) );
		$this->ctl( 'badge', 'switch', 'نشان درصد تخفیف روی تصویر', true );
		$this->ctl( 'exp', 'switch', 'نمایش تاریخ انقضا', true );
		$this->ctl( 'exp_label', 'text', 'برچسب انقضا', 'انقضا' );
		$this->ctl( 'arrows', 'switch', 'فلش‌ها', true );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white', 'wide', '#F4F3EB' );

		$this->style_section(
			'st',
			'بنر',
			array(
				array( 'banner', 'بنر', '.zt-offers-banner', array( 'bg', 'radius', 'padding', 'margin', 'shadow' ) ),
				array( 'title', 'عنوان', '.zt-offers-banner h2', self::fx( 'text' ) ),
				array( 'text', 'متن', '.zt-offers-banner p', self::fx( 'text' ) ),
				array( 'cell', 'خانه‌های شمارنده', '.zt-cd-cell', array( 'bg', 'radius', 'width', 'padding', 'shadow' ) ),
				array( 'num', 'اعداد', '.zt-cd-cell b', array( 'typo', 'color' ) ),
				array( 'lbl', 'برچسب‌ها', '.zt-cd-cell span', array( 'typo', 'color' ) ),
				array( 'btn', 'دکمه', '.zt-offers-banner .zt-btn', self::fx( 'button' ) ),
			)
		);
		$this->product_card_styles();
	}

	/**
	 * Shared product card style controls.
	 */
	protected function product_card_styles() {
		$this->style_section(
			'stp',
			'کارت محصول',
			array(
				array( 'track', 'شبکه', '.zt-prod-track', self::fx( 'grid' ) ),
				array( 'card', 'کارت', '.zt-prod', array( 'bg', 'radius', 'shadow' ) ),
				array( 'pimg', 'تصویر', '.zt-prod__img', array( 'ratio', 'bg' ) ),
				array( 'pbody', 'بدنه', '.zt-prod__body', array( 'padding' ) ),
				array( 'pcat', 'دسته', '.zt-prod__cat', self::fx( 'text' ) ),
				array( 'ptitle', 'عنوان', '.zt-prod__title', array( 'typo', 'color', 'hover_color', 'min_height' ) ),
				array( 'pprice', 'قیمت', '.zt-prod__price b', array( 'typo', 'color' ) ),
				array( 'pdel', 'قیمت قبلی', '.zt-prod__price del', array( 'typo', 'color', 'display' ) ),
				array( 'padd', 'دکمه سبد', '.zt-prod__add', array( 'size', 'bg', 'bg_hover', 'color', 'radius' ) ),
				array( 'pbadge', 'نشان تخفیف', '.zt-badge-off', self::fx( 'badge' ) ),
				array( 'pexp', 'نشان انقضا', '.zt-exp', array( 'typo', 'bg', 'display' ) ),
				array( 'parrow', 'فلش‌ها', '[data-zt-dir]', array( 'size', 'color', 'bg', 'display' ) ),
			)
		);
	}

	/**
	 * Countdown seconds left + attributes.
	 *
	 * @param array $s     Settings.
	 * @param array $items Items.
	 * @return array [seconds, attrs]
	 */
	private function countdown( $s, $items ) {
		$now = time();
		switch ( $s['cd_mode'] ) {
			case 'date':
				$end = $s['cd_date'] ? strtotime( get_gmt_from_date( $s['cd_date'] ) . ' UTC' ) : $now;
				return array( max( 0, $end - $now ), ' data-zt-end="' . esc_attr( $end ) . '"' );
			case 'daily':
				$tz  = wp_timezone();
				$end = ( new DateTimeImmutable( 'tomorrow', $tz ) )->getTimestamp();
				return array( max( 0, $end - $now ), ' data-zt-end="' . esc_attr( $end ) . '" data-zt-daily="1"' );
			case 'sale':
				$end = 0;
				foreach ( $items as $it ) {
					if ( ! empty( $it['id'] ) ) {
						$p = wc_get_product( $it['id'] );
						$d = $p ? $p->get_date_on_sale_to() : null;
						if ( $d && ( ! $end || $d->getTimestamp() < $end ) && $d->getTimestamp() > $now ) {
							$end = $d->getTimestamp();
						}
					}
				}
				if ( $end ) {
					return array( $end - $now, ' data-zt-end="' . esc_attr( $end ) . '"' );
				}
				return array( (int) $s['cd_seconds'], ' data-zt-evergreen="' . esc_attr( (int) $s['cd_seconds'] ) . '"' );
			default:
				return array( (int) $s['cd_seconds'], ' data-zt-evergreen="' . esc_attr( (int) $s['cd_seconds'] ) . '"' );
		}
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$items = 'yes' === $s['show_products'] ? $this->get_products( $s ) : array();
		list( $left, $attr ) = $this->countdown( $s, $items );
		$this->section_open( $s, 'zt-offers-sec', ' data-zt-cd-end="' . esc_attr( $s['cd_end'] ) . '"' );
		if ( 'yes' === $s['show_banner'] ) {
			$l   = array_pad( array_map( 'trim', explode( ',', $s['cd_labels'] ) ), 4, '' );
			$pad = function ( $n ) {
				return zt_fa( str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) );
			};
			echo '<div class="zt-offers-banner' . esc_attr( $this->reveal( $s ) ) . '" data-zt-countdown="' . esc_attr( $left ) . '"' . $attr . '>'; // phpcs:ignore
			echo '<div><h2>' . zt_icon( $s['icon'], array( 'class' => 'zt-leaf', 'width' => '21', 'height' => '21' ) ) . ' ' . esc_html( $s['title'] ) . '</h2><p>' . esc_html( $s['text'] ) . '</p></div>'; // phpcs:ignore
			echo '<div class="zt-countdown">';
			echo '<div class="zt-cd-cell"><b data-zt-cd="s">' . esc_html( $pad( $left % 60 ) ) . '</b><span>' . esc_html( $l[0] ) . '</span></div><span class="zt-sep">:</span>';
			echo '<div class="zt-cd-cell"><b data-zt-cd="m">' . esc_html( $pad( floor( $left / 60 ) % 60 ) ) . '</b><span>' . esc_html( $l[1] ) . '</span></div><span class="zt-sep">:</span>';
			echo '<div class="zt-cd-cell"><b data-zt-cd="h">' . esc_html( $pad( floor( $left / 3600 ) % 24 ) ) . '</b><span>' . esc_html( $l[2] ) . '</span></div><span class="zt-sep">:</span>';
			echo '<div class="zt-cd-cell"><b data-zt-cd="d">' . esc_html( $pad( floor( $left / 86400 ) ) ) . '</b><span>' . esc_html( $l[3] ) . '</span></div>';
			echo '</div>';
			if ( '' !== $s['btn'] ) {
				echo '<a' . zt_link_attrs( $s['btn_link'] ) . ' class="zt-btn">' . esc_html( $s['btn'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>'; // phpcs:ignore
			}
			echo '</div>';
		}
		if ( $items ) {
			echo '<div class="zt-prods' . esc_attr( $this->reveal( $s ) ) . '" data-zt-carousel><div class="zt-prod-track" data-zt-track>';
			foreach ( $items as $it ) {
				echo ZT_Parts::product_card( $it, array( 'badge' => 'yes' === $s['badge'], 'exp' => 'yes' === $s['exp'], 'exp_label' => $s['exp_label'] ) ); // phpcs:ignore
			}
			echo '</div>';
			if ( 'yes' === $s['arrows'] ) {
				$this->carousel_arrows( '50%' );
			}
			echo '</div>';
		}
		$this->section_close( $s );
	}
}
