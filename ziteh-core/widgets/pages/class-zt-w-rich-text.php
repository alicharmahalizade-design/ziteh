<?php
/**
 * Pages: rich text (terms, about …) with the design typography.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Rich_Text
 */
class ZT_W_Rich_Text extends ZT_Widget_Base {

	protected $zt_group = 'pages';
	protected $zt_icon  = 'eicon-text-area';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-rich-text';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'متن غنی (قوانین / درباره ما)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'متن' );
		$this->ctl(
			'text',
			'wysiwyg',
			'متن',
			'<h2>شرایط ثبت سفارش</h2><p>ثبت سفارش در فروشگاه زیته به معنای پذیرش قوانین و شرایط زیر است. لطفاً پیش از خرید، این موارد را با دقت مطالعه کنید.</p><ul><li>قیمت‌ها به تومان و شامل مالیات بر ارزش افزوده است.</li><li>سفارش پس از تایید پرداخت پردازش و ارسال می‌شود.</li><li>مسئولیت صحت اطلاعات ارسال با خریدار است.</li></ul><h2>ارسال و تحویل</h2><p>سفارش‌ها معمولاً ظرف ۲ تا ۳ روز کاری ارسال می‌شوند. زمان تحویل بسته به روش ارسال و مقصد متفاوت است.</p><h2>بازگشت کالا</h2><p>در صورت وجود ایراد یا مغایرت، تا ۷ روز پس از تحویل و با پلمب سالم، امکان بازگشت کالا وجود دارد. محصولات باز شده به دلیل رعایت بهداشت قابل بازگشت نیستند.</p><h2>حریم خصوصی</h2><p>اطلاعات شخصی شما صرفاً برای پردازش سفارش استفاده شده و در اختیار شخص ثالث قرار نمی‌گیرد.</p>'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'تایپوگرافی',
			array(
				array( 'prose', 'متن', '.zt-prose', array( 'typo', 'color', 'max_width', 'bg', 'radius', 'padding' ) ),
				array( 'h2', 'تیتر ۲', '.zt-prose h2', array( 'typo', 'color', 'margin' ) ),
				array( 'h3', 'تیتر ۳', '.zt-prose h3', array( 'typo', 'color', 'margin' ) ),
				array( 'a', 'لینک', '.zt-prose a', array( 'color', 'hover_color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$html = $s['text'];
		echo '<div class="zt-prose">' . wp_kses_post( $html ) . '</div>';
	}
}
