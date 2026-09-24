<?php
/**
 * Account: wishlist preview.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Favs
 */
class ZT_W_Account_Favs extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-heart-o';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-favs';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'علاقه‌مندی‌ها (پیشخوان)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'heart' );
		$this->ctl( 'title', 'text', 'عنوان', 'علاقه‌مندی‌ها' );
		$this->ctl( 'head_link_text', 'text', 'لینک سربرگ', 'مشاهده همه' );
		$this->ctl( 'wide_text', 'text', 'دکمه پایین', 'مشاهده همه علاقه‌مندی‌ها' );
		$this->ctl( 'link', 'url', 'لینک', '{{wishlist}}' );
		$this->ctl( 'limit', 'number', 'تعداد', 4 );
		$this->ctl( 'empty', 'text', 'متن لیست خالی', 'هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'grid', 'چیدمان', '.zt-favs', array( 'columns', 'gap' ) ),
				array( 'img', 'تصویر', '.zt-fav img', array( 'radius', 'ratio', 'bg' ) ),
				array( 'name', 'نام محصول', '.zt-fav b', array( 'typo', 'color' ) ),
				array( 'price', 'قیمت', '.zt-fav span', array( 'typo', 'color' ) ),
				array( 'wide', 'دکمه پایین', '.zt-wide-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ! ZT_Acc::dashboard() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$link  = zt_url( $s['link'] );
		$items = array();
		if ( 'sample' === ZT_Acc::mode() ) {
			$m     = max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			$items = array(
				array( 'سرم ضد ریزش مو زیته', 395000 * $m, zt_asset_img( 'fav-1.jpg' ), '' ),
				array( 'کرم آبرسان فیسبک', 285000 * $m, zt_asset_img( 'fav-2.jpg' ), '' ),
				array( 'ماسک مو تغذیه‌کننده', 475000 * $m, zt_asset_img( 'fav-3.jpg' ), '' ),
				array( 'رایحه آرامش', 385000 * $m, zt_asset_img( 'fav-4.jpg' ), '' ),
			);
		} else {
			foreach ( array_slice( ZT_Wishlist::get(), 0, max( 1, (int) $s['limit'] ) ) as $id ) {
				$it = ZT_Parts::product_item( $id, 'medium' );
				if ( $it ) {
					$items[] = array( $it['short'] ? $it['short'] : $it['title'], $it['price'], $it['img'], $it['url'] );
				}
			}
		}
		echo '<section class="zt-dcard">' . ZT_Acc::head( $s, $link ); // phpcs:ignore
		if ( ! $items ) {
			echo '<p class="zt-dcard__empty">' . esc_html( $s['empty'] ) . '</p>';
		} else {
			echo '<div class="zt-favs">';
			foreach ( $items as $it ) {
				$tag = $it[3] ? 'a href="' . esc_url( $it[3] ) . '"' : 'div';
				echo '<' . $tag . ' class="zt-fav"><img src="' . esc_url( $it[2] ) . '" alt=""><b>' . esc_html( $it[0] ) . '</b><span>' . esc_html( zt_money( $it[1], true ) . ' ' . zt_currency() ) . '</span></' . ( $it[3] ? 'a' : 'div' ) . '>'; // phpcs:ignore
			}
			echo '</div>';
		}
		echo ZT_Acc::wide( $s, $link ) . '</section>'; // phpcs:ignore
	}
}
