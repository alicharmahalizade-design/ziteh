<?php
/**
 * Guide: FAQ (details/summary) + optional FAQPage schema.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Guide_Faq
 */
class ZT_W_Guide_Faq extends ZT_Widget_Base {

	protected $zt_group = 'guide';
	protected $zt_icon  = 'eicon-accordion';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-guide-faq';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'سؤالات متداول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'سؤالات' );
		$this->ctl( 'title', 'text', 'عنوان', 'سؤالات متداول' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'info' );
		$this->repeater(
			'items',
			'سؤالات',
			array(
				array( 'q', 'text', 'سؤال', 'سؤال' ),
				array( 'a', 'textarea', 'پاسخ', '' ),
			),
			array(
				array( 'q' => 'آیا تونر ضروری است؟', 'a' => "خیر. تونر برای همه افراد ضروری نیست. در صورت تمایل یا نیاز پوست، می‌توان آن را بعد از\n          شستشو و قبل از سرم استفاده کرد." ),
				array( 'q' => 'آیا میسلار واتر جای شوینده را می‌گیرد؟', 'a' => "خیر. اگر از میسلار واتر برای پاک کردن آرایش یا ضدآفتاب استفاده می‌کنید، معمولاً توصیه\n          می‌شود پس از آن صورت را با شوینده مناسب بشویید." ),
				array( 'q' => 'آیا همه افراد به سرم نیاز دارند؟', 'a' => 'خیر. سرم‌ها بر اساس نیاز پوست انتخاب می‌شوند و استفاده از آن‌ها برای همه ضروری نیست.' ),
				array( 'q' => 'آیا مرطوب‌کننده برای پوست چرب هم لازم است؟', 'a' => "بله، بسیاری از افراد با پوست چرب نیز می‌توانند از مرطوب‌کننده‌های سبک و فاقد چربی\n          استفاده کنند." ),
				array( 'q' => 'آیا ضدآفتاب فقط در روزهای آفتابی لازم است؟', 'a' => "قرار گرفتن در معرض اشعه فرابنفش تنها به روزهای آفتابی محدود نیست. در صورت قرار گرفتن\n          در فضای باز، استفاده از ضدآفتاب توصیه می‌شود." ),
			),
			'q'
		);
		$this->ctl( 'first_open', 'switch', 'اولین سؤال باز باشد', true );
		$this->ctl( 'single', 'switch', 'فقط یک سؤال همزمان باز باشد', false );
		$this->ctl( 'schema', 'switch', 'اسکیمای FAQ برای گوگل', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'سؤالات',
			array(
				array( 'h', 'عنوان', '.zt-guide__h', array( 'typo', 'color', 'margin' ) ),
				array( 'item', 'آیتم', '.zt-guide__faq details', array( 'bg', 'border', 'radius' ) ),
				array( 'q', 'سؤال', '.zt-guide__faq summary', array( 'typo', 'color', 'padding' ) ),
				array( 'qo', 'سؤال باز', '.zt-guide__faq details[open] summary', array( 'color' ) ),
				array( 'a', 'پاسخ', '.zt-guide__faq p', array( 'typo', 'color', 'padding' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( '' !== $s['title'] ) {
			echo '<h2 class="zt-guide__h">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		}
		echo '<div class="zt-guide__faq"' . ( 'yes' === $s['single'] ? ' data-zt-faq-single' : '' ) . '>';
		$ld = array();
		foreach ( (array) $s['items'] as $i => $it ) {
			echo '<details' . ( 0 === $i && 'yes' === $s['first_open'] ? ' open' : '' ) . '><summary>' . esc_html( $it['q'] ) . '</summary><p>' . esc_html( $it['a'] ) . '</p></details>';
			$ld[] = array(
				'@type'          => 'Question',
				'name'           => $it['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => preg_replace( '/\s+/u', ' ', $it['a'] ),
				),
			);
		}
		echo '</div>';
		if ( 'yes' === $s['schema'] && $ld && ! $this->is_editor() ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $ld ), JSON_UNESCAPED_UNICODE ) . '</script>'; // phpcs:ignore
		}
	}
}
