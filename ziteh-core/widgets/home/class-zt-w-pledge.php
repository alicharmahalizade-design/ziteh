<?php
/**
 * Home: "our pledge" centred statement.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Pledge
 */
class ZT_W_Pledge extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-blockquote';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-pledge';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'تعهد ما';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'تعهد ما' );
		$this->ctl( 'text', 'textarea', 'متن', 'هر انتخاب کوچک می‌تواند آغاز یک حس خوب باشد. ما در زیتِه متعهدیم محصولاتی اصیل و باکیفیت را با دقت انتخاب کنیم، سفارش‌ها را با عشق آماده کنیم و تجربه‌ای بسازیم که مراقبت از خود، برای شما به عادتی لذت‌بخش و ماندگار تبدیل شود.', array( 'rows' => 6 ) );
		$this->end_controls_section();
		$this->section_wrap_controls( 'cream' );
		$this->style_section(
			'st',
			'تعهد ما',
			array(
				array( 'box', 'کادر', '.zt-pledge', array( 'max_width', 'align' ) ),
				array( 'ic', 'دایره آیکون', '.zt-pledge__ic', array( 'size', 'bg', 'color', 'radius', 'shadow', 'margin' ) ),
				array( 'svg', 'آیکون', '.zt-pledge__ic svg', array( 'size' ) ),
				array( 'title', 'عنوان', '.zt-pledge h2', self::fx( 'text' ) ),
				array( 'text', 'متن', '.zt-pledge p', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s );
		echo '<div class="zt-pledge' . esc_attr( $this->reveal( $s ) ) . '"><span class="zt-pledge__ic">' . zt_icon( $s['icon'] ) . '</span><h2>' . esc_html( $s['title'] ) . '</h2><p>' . zt_kses( $s['text'] ) . '</p></div>'; // phpcs:ignore
		$this->section_close( $s );
	}
}
