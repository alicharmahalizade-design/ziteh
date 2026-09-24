<?php
/**
 * Pages: contact information card.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Contact_Info
 */
class ZT_W_Contact_Info extends ZT_Widget_Base {

	protected $zt_group = 'pages';
	protected $zt_icon  = 'eicon-call-to-action';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-contact-info';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'اطلاعات تماس';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'headset' );
		$this->ctl( 'title', 'text', 'عنوان', 'راه‌های ارتباط با زیته' );
		$this->ctl( 'text', 'textarea', 'توضیح', 'تیم پشتیبانی زیته در ساعات کاری پاسخگوی سؤالات شما درباره سفارش‌ها، محصولات و مشاوره پوستی است.' );
		$this->repeater(
			'items',
			'راه‌های ارتباطی',
			array(
				array( 'icon', 'icon', 'آیکون', 'phone' ),
				array( 'label', 'text', 'عنوان', 'تلفن' ),
				array( 'value', 'text', 'مقدار', '' ),
				array( 'link', 'url', 'لینک', '' ),
			),
			array(
				array( 'icon' => 'phone', 'label' => 'تلفن پشتیبانی', 'value' => '۰۱۳-۴۲۳۴۵۶۷۸', 'link' => 'tel:01342345678' ),
				array( 'icon' => 'mail', 'label' => 'ایمیل', 'value' => 'info@ziteh.ir', 'link' => 'mailto:info@ziteh.ir' ),
				array( 'icon' => 'clock', 'label' => 'ساعات پاسخگویی', 'value' => 'شنبه تا پنجشنبه، ۹ تا ۱۸', 'link' => '' ),
				array( 'icon' => 'pin', 'label' => 'آدرس', 'value' => 'گیلان، لاهیجان', 'link' => '' ),
			),
			'label'
		);
		$this->repeater(
			'social',
			'شبکه‌های اجتماعی',
			array(
				array( 'icon', 'icon', 'آیکون', 'instagram' ),
				array( 'label', 'text', 'عنوان', 'اینستاگرام' ),
				array( 'link', 'url', 'لینک', '#' ),
			),
			array(
				array( 'icon' => 'instagram', 'label' => 'اینستاگرام', 'link' => '#' ),
				array( 'icon' => 'telegram', 'label' => 'تلگرام', 'link' => '#' ),
				array( 'icon' => 'whatsapp', 'label' => 'واتساپ', 'link' => '#' ),
				array( 'icon' => 'bale', 'label' => 'بله', 'link' => '#' ),
			),
			'label'
		);
		$this->ctl( 'map', 'textarea', 'آدرس iframe نقشه (اختیاری)', '' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'text', 'توضیح', '.zt-cinfo__text', array( 'typo', 'color' ) ),
				array( 'ic', 'آیکون‌ها', '.zt-cinfo__ic', array( 'size', 'bg', 'color', 'radius' ) ),
				array( 'lbl', 'عنوان آیتم', '.zt-cinfo__row span', array( 'typo', 'color' ) ),
				array( 'val', 'مقدار', '.zt-cinfo__row b', array( 'typo', 'color' ) ),
				array( 'soc', 'شبکه‌های اجتماعی', '.zt-cinfo__social .zt-iconbtn', array( 'size', 'color', 'bg', 'radius' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<section class="zt-dcard zt-cinfo"><div class="zt-dcard__head"><h3>' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h3></div>'; // phpcs:ignore
		if ( $s['text'] ) {
			echo '<p class="zt-cinfo__text">' . esc_html( $s['text'] ) . '</p>';
		}
		echo '<div class="zt-cinfo__list">';
		foreach ( (array) $s['items'] as $it ) {
			$has = ! empty( $it['link']['url'] );
			$tag = $has ? 'a' . zt_link_attrs( $it['link'] ) : 'div';
			echo '<' . $tag . ' class="zt-cinfo__row"><span class="zt-cinfo__ic">' . zt_icon( $it['icon'] ) . '</span><div><span>' . esc_html( $it['label'] ) . '</span><b>' . esc_html( $it['value'] ) . '</b></div></' . ( $has ? 'a' : 'div' ) . '>'; // phpcs:ignore
		}
		echo '</div>';
		if ( $s['social'] ) {
			echo '<div class="zt-cinfo__social">';
			foreach ( $s['social'] as $it ) {
				echo '<a class="zt-iconbtn"' . zt_link_attrs( $it['link'] ) . ' aria-label="' . esc_attr( $it['label'] ) . '">' . zt_icon( $it['icon'] ) . '</a>'; // phpcs:ignore
			}
			echo '</div>';
		}
		if ( ! empty( $s['map'] ) ) {
			$src = $s['map'];
			if ( preg_match( '/src=["\']([^"\']+)/', $src, $m ) ) {
				$src = $m[1];
			}
			echo '<div class="zt-cinfo__map"><iframe src="' . esc_url( $src ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="نقشه"></iframe></div>';
		}
		echo '</section>';
	}
}
