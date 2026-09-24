<?php
/**
 * Pages: contact form (AJAX; stored in «پیام‌های تماس» and emailed).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Contact_Form
 */
class ZT_W_Contact_Form extends ZT_Widget_Base {

	protected $zt_group = 'pages';
	protected $zt_icon  = 'eicon-form-horizontal';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-contact-form';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'فرم تماس';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'فرم' );
		$this->ctl( 'notice', 'notice', '', 'پیام‌ها در «زیته ← پیام‌های تماس» ذخیره و به ایمیل تنظیم‌شده ارسال می‌شوند.' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'send' );
		$this->ctl( 'title', 'text', 'عنوان', 'ارسال پیام' );
		$this->ctl( 'l_name', 'text', 'برچسب نام', 'نام و نام خانوادگی' );
		$this->ctl( 'l_phone', 'text', 'برچسب موبایل', 'شماره موبایل' );
		$this->ctl( 'l_email', 'text', 'برچسب ایمیل', 'ایمیل (اختیاری)' );
		$this->ctl( 'subject', 'switch', 'فیلد موضوع', true );
		$this->ctl( 'l_subject', 'text', 'برچسب موضوع', 'موضوع' );
		$this->ctl( 'subjects', 'textarea', 'گزینه‌های موضوع (هر خط یکی)', "پیگیری سفارش\nمشاوره پوستی\nهمکاری\nپیشنهاد و انتقاد\nسایر" );
		$this->ctl( 'l_msg', 'text', 'برچسب پیام', 'متن پیام' );
		$this->ctl( 'btn', 'text', 'متن دکمه', 'ارسال پیام' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'فرم',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'label', 'برچسب', '.zt-label', array( 'typo', 'color' ) ),
				array( 'input', 'فیلد', '.zt-input, .zt-select, .zt-textarea', array( 'typo', 'color', 'bg', 'border_color', 'radius' ) ),
				array( 'btn', 'دکمه', '.zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<section class="zt-dcard zt-cform-card"><div class="zt-dcard__head"><h3>' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h3></div>'; // phpcs:ignore
		echo '<form class="zt-form-grid zt-cform" data-zt-contact novalidate>';
		echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_name'] ) . ' <span class="zt-req">*</span></span><input class="zt-input" name="name" required autocomplete="name"></label>';
		echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_phone'] ) . ' <span class="zt-req">*</span></span><input class="zt-input" name="phone" inputmode="tel" autocomplete="tel" dir="ltr" placeholder="۰۹۱۲۱۲۳۴۵۶۷"></label>';
		echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_email'] ) . '</span><input class="zt-input" type="email" name="email" autocomplete="email" dir="ltr"></label>';
		if ( 'yes' === $s['subject'] ) {
			echo '<label class="zt-field"><span class="zt-label">' . esc_html( $s['l_subject'] ) . '</span><select class="zt-select" name="subject">';
			foreach ( zt_lines( $s['subjects'] ) as $o ) {
				echo '<option>' . esc_html( $o ) . '</option>';
			}
			echo '</select></label>';
		}
		echo '<label class="zt-field zt-full"><span class="zt-label">' . esc_html( $s['l_msg'] ) . ' <span class="zt-req">*</span></span><textarea class="zt-textarea" name="message" required rows="5"></textarea></label>';
		echo '<input type="text" name="website" tabindex="-1" autocomplete="off" class="zt-hp" aria-hidden="true">';
		echo '<div class="zt-full"><button type="submit" class="zt-btn zt-btn--primary">' . zt_icon( 'send' ) . ' ' . esc_html( $s['btn'] ) . '</button></div>'; // phpcs:ignore
		echo '</form></section>';
	}
}
