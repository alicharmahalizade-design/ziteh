<?php
/**
 * Home: skin routine (morning / night steps).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Routine
 */
class ZT_W_Routine extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-toggle';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-routine';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'روتین مراقبت (صبح / شب)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'روتین مراقبت از پوست' );
		$this->ctl( 'sub', 'text', 'زیرعنوان', 'پوستی سالم، با روتینی ساده و مداوم' );
		$this->ctl( 'am_label', 'text', 'برچسب صبح', 'صبح' );
		$this->ctl( 'am_icon', 'icon', 'آیکون صبح', 'sun' );
		$this->ctl( 'pm_label', 'text', 'برچسب شب', 'شب' );
		$this->ctl( 'pm_icon', 'icon', 'آیکون شب', 'moon' );
		$this->ctl( 'auto_time', 'switch', 'انتخاب خودکار صبح/شب بر اساس ساعت کاربر', false );
		$this->end_controls_section();
		$fields = array(
			array( 'icon', 'icon', 'آیکون', 'droplet' ),
			array( 'title', 'text', 'عنوان', '' ),
			array( 'sub', 'text', 'توضیح', '' ),
			array( 'url', 'url', 'لینک', '{{shop}}' ),
		);
		$this->section( 'c_am', 'مراحل صبح' );
		$this->repeater(
			'am',
			'مراحل',
			$fields,
			array(
				array( 'icon' => 'droplet', 'title' => 'شست‌وشو', 'sub' => 'آماده سازی پوست', 'url' => '{{shop}}' ),
				array( 'icon' => 'leaf', 'title' => 'سرم', 'sub' => 'مراقبت تخصصی', 'url' => '{{shop}}' ),
				array( 'icon' => 'pen', 'title' => 'مرطوب‌کننده و آبرسان', 'sub' => 'آبرسانی و حفظ رطوبت', 'url' => '{{shop}}' ),
				array( 'icon' => 'shield-sun', 'title' => 'ضد آفتاب', 'sub' => 'محافظت روزانه', 'url' => '{{shop}}' ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();
		$this->section( 'c_pm', 'مراحل شب' );
		$this->repeater(
			'pm',
			'مراحل',
			$fields,
			array(
				array( 'icon' => 'refresh', 'title' => 'پاکسازی', 'sub' => 'پاک کردن آرایش و ضدآفتاب', 'url' => '{{shop}}' ),
				array( 'icon' => 'droplet', 'title' => 'شست‌وشو', 'sub' => 'شوینده ملایم صورت', 'url' => '{{shop}}' ),
				array( 'icon' => 'flask', 'title' => 'محصولات درمانی', 'sub' => 'رتینوئید، ضدجوش، ضدلک', 'url' => '{{shop}}' ),
				array( 'icon' => 'pen', 'title' => 'مرطوب‌کننده و آبرسان', 'sub' => 'حفظ رطوبت در شب', 'url' => '{{shop}}' ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();
		$this->section( 'c_foot', 'لینک پایین' );
		$this->ctl( 'more', 'text', 'متن', 'راهنمای کامل روتین' );
		$this->ctl( 'more_link', 'url', 'لینک', '{{routine}}' );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'روتین',
			array(
				array( 'title', 'عنوان', '.zt-sec-title', self::fx( 'text' ) ),
				array( 'sub', 'زیرعنوان', '.zt-routine-head .zt-sec-sub', self::fx( 'text' ) ),
				array( 'seg', 'سوییچ صبح/شب', '.zt-seg', array( 'bg', 'radius', 'padding' ) ),
				array( 'segb', 'دکمه‌های سوییچ', '.zt-seg button', array( 'typo', 'color', 'height', 'padding' ) ),
				array( 'sega', 'دکمه فعال', '.zt-seg button.zt-is-active', array( 'bg', 'color', 'shadow' ) ),
				array( 'grid', 'شبکه مراحل', '.zt-steps', self::fx( 'grid' ) ),
				array( 'step', 'کارت مرحله', '.zt-step', self::fx( 'card' ) ),
				array( 'num', 'شماره', '.zt-step__n', array( 'typo', 'color' ) ),
				array( 'ic', 'دایره آیکون', '.zt-step__ic', array( 'size', 'bg', 'color', 'radius', 'shadow' ) ),
				array( 'st', 'عنوان مرحله', '.zt-step b', self::fx( 'text' ) ),
				array( 'ss', 'توضیح مرحله', '.zt-step span:not(.zt-step__n)', self::fx( 'text' ) ),
				array( 'more', 'لینک پایین', '.zt-routine-foot .zt-link-more', self::fx( 'link' ) ),
			)
		);
	}

	/**
	 * Steps.
	 *
	 * @param array  $rows   Rows.
	 * @param string $mode   am|pm.
	 * @param bool   $hidden Hidden.
	 * @param string $rev    Reveal class.
	 */
	private function steps( $rows, $mode, $hidden, $rev ) {
		echo '<div class="zt-steps' . esc_attr( $rev ) . '" data-zt-steps="' . esc_attr( $mode ) . '"' . ( $hidden ? ' hidden' : '' ) . '>';
		foreach ( array_values( (array) $rows ) as $i => $r ) {
			echo '<a class="zt-step"' . zt_link_attrs( $r['url'] ) . '><span class="zt-step__n">' . esc_html( zt_fa( $i + 1 ) ) . '</span><div class="zt-step__ic">' . zt_icon( $r['icon'] ) . '</div><b>' . esc_html( $r['title'] ) . '</b><span>' . esc_html( $r['sub'] ) . '</span></a>'; // phpcs:ignore
		}
		echo '</div>';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$rev = $this->reveal( $s );
		$this->section_open( $s );
		echo '<div class="zt-routine-head' . esc_attr( $rev ) . '"><div><h2 class="zt-sec-title">' . zt_icon( $s['icon'], array( 'class' => 'zt-leaf', 'width' => '22', 'height' => '22' ) ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		if ( '' !== $s['sub'] ) {
			echo '<p class="zt-sec-sub" style="margin-top:8px">' . esc_html( $s['sub'] ) . '</p>';
		}
		echo '</div><div class="zt-seg" data-zt-routine' . ( 'yes' === $s['auto_time'] ? ' data-zt-auto="1"' : '' ) . '><button class="zt-is-active" data-value="am">' . zt_icon( $s['am_icon'] ) . ' ' . esc_html( $s['am_label'] ) . '</button><button data-value="pm">' . zt_icon( $s['pm_icon'] ) . ' ' . esc_html( $s['pm_label'] ) . '</button></div></div>'; // phpcs:ignore
		$this->steps( $s['am'], 'am', false, $rev );
		$this->steps( $s['pm'], 'pm', true, $rev );
		if ( '' !== $s['more'] ) {
			echo '<div class="zt-routine-foot"><a' . zt_link_attrs( $s['more_link'] ) . ' class="zt-link-more">' . esc_html( $s['more'] ) . ' ' . zt_icon( 'chev-left' ) . '</a></div>'; // phpcs:ignore
		}
		$this->section_close( $s );
	}
}
