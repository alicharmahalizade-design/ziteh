<?php
/**
 * Global: design button.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Button
 */
class ZT_W_Button extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-button';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-button';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'دکمه زیته';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'دکمه' );
		$this->ctl( 'text', 'text', 'متن', 'مشاهده محصولات مراقبت پوست' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'bag' );
		$this->ctl( 'icon_pos', 'select', 'جای آیکون', 'before', array( 'options' => array( 'before' => 'قبل از متن', 'after' => 'بعد از متن' ) ) );
		$this->ctl( 'action', 'select', 'عملکرد', 'link', array( 'options' => array( 'link' => 'لینک', 'advice' => 'باز کردن شیت مشاوره', 'search' => 'باز کردن جستجو', 'drawer' => 'باز کردن منوی موبایل' ) ) );
		$this->ctl( 'link', 'url', 'لینک', '{{shop}}', array( 'condition' => array( 'action' => 'link' ) ) );
		$this->ctl( 'variant', 'select', 'سبک', 'primary', array( 'options' => array( 'primary' => 'اصلی (سبز)', 'ghost' => 'سفید با حاشیه', 'outline' => 'حاشیه سبز', 'soft' => 'سبز ملایم' ) ) );
		$this->ctl( 'size', 'select', 'اندازه', '', array( 'options' => array( 'sm' => 'کوچک', '' => 'معمولی', 'lg' => 'بزرگ' ) ) );
		$this->ctl( 'block', 'switch', 'تمام عرض', false );
		$this->add_responsive_control(
			'align',
			array(
				'label'     => 'چینش',
				'type'      => \Elementor\Controls_Manager::CHOOSE,
				'default'   => 'center',
				'options'   => array(
					'flex-start' => array( 'title' => 'راست', 'icon' => 'eicon-h-align-right' ),
					'center'     => array( 'title' => 'وسط', 'icon' => 'eicon-h-align-center' ),
					'flex-end'   => array( 'title' => 'چپ', 'icon' => 'eicon-h-align-left' ),
				),
				'selectors' => array( '{{WRAPPER}} .zt-btn-wrap' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'دکمه',
			array(
				array( 'wrap', 'کادر بیرونی', '.zt-btn-wrap', array( 'margin' ) ),
				array( 'btn', 'دکمه', '.zt-btn', self::fx( 'button' ) ),
				array( 'icon', 'آیکون', '.zt-btn svg', array( 'size' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$cls  = 'zt-btn zt-btn--' . $s['variant'] . ( $s['size'] ? ' zt-btn--' . $s['size'] : '' ) . ( 'yes' === $s['block'] ? ' zt-btn--block' : '' );
		$ic   = zt_icon( $s['icon'] );
		$in   = 'after' === $s['icon_pos'] ? esc_html( $s['text'] ) . ' ' . $ic : $ic . ' ' . esc_html( $s['text'] );
		echo '<div class="zt-btn-wrap" style="display:flex">';
		if ( 'link' === $s['action'] ) {
			echo '<a' . zt_link_attrs( $s['link'] ) . ' class="' . esc_attr( $cls ) . '">' . $in . '</a>'; // phpcs:ignore
		} else {
			$map = array(
				'advice' => 'data-zt-sheet-open="#zt-sheet-advice"',
				'search' => 'data-zt-search-open',
				'drawer' => 'data-zt-drawer-open',
			);
			echo '<button type="button" class="' . esc_attr( $cls ) . '" ' . $map[ $s['action'] ] . '>' . $in . '</button>'; // phpcs:ignore
		}
		echo '</div>';
	}
}
