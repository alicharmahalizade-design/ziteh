<?php
/**
 * Home: brand story (text + framed image).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Story
 */
class ZT_W_Story extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-image-box';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-story';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'داستان زیته';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'داستان زیته' );
		$this->ctl( 'paras', 'textarea', 'پاراگراف‌ها (هر خط یک پاراگراف — آخرین پاراگراف سبز و پررنگ است)', "زیته در زبان گیلکی یعنی روییدن و آغاز یک جوانه.\nما این نام را انتخاب کردیم چون باور داریم مراقبت از خود هم شبیه روییدن است؛ با قدم‌های کوچک آغاز می‌شود، با عشق ادامه پیدا می‌کند و آرام‌آرام به بخشی از زندگی تبدیل می‌شود.\nدر زیته، تلاش می‌کنیم با انتخاب محصولاتی اصیل و باکیفیت، این مسیر را برایت ساده‌تر و مطمئن‌تر کنیم.\nزیته: جوانه‌ای برای مراقبت از خودت", array( 'rows' => 10 ) );
		$this->ctl( 'link_text', 'text', 'متن لینک (اختیاری)', '' );
		$this->ctl( 'link', 'url', 'لینک', '{{about}}' );
		$this->ctl( 'img', 'media', 'تصویر', 'story.jpg' );
		$this->ctl( 'img_alt', 'text', 'متن جایگزین تصویر', 'داستان زیته' );
		$this->ctl( 'mobile_short', 'switch', 'در موبایل فقط پاراگراف اول و آخر', true );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'داستان',
			array(
				array( 'grid', 'چیدمان', '.zt-story', array( 'gap' ) ),
				array( 'title', 'عنوان', '.zt-story .zt-sec-title', self::fx( 'text' ) ),
				array( 'p', 'پاراگراف‌ها', '.zt-story p', self::fx( 'text' ) ),
				array( 'plast', 'پاراگراف آخر', '.zt-story p:last-of-type', array( 'typo', 'color' ) ),
				array( 'frame', 'قاب تصویر', '.zt-story__frame::before', array( 'border_color', 'radius', 'display' ) ),
				array( 'img', 'تصویر', '.zt-story__frame img', self::fx( 'image' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s, 'yes' === $s['mobile_short'] ? 'zt-story-sec zt-story-sec--short' : 'zt-story-sec' );
		echo '<div class="zt-story' . esc_attr( $this->reveal( $s ) ) . '"><div>';
		echo '<h2 class="zt-sec-title" style="margin-bottom:22px">' . zt_icon( $s['icon'], array( 'class' => 'zt-leaf', 'width' => '22', 'height' => '22' ) ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		foreach ( zt_lines( $s['paras'] ) as $p ) {
			echo '<p>' . zt_kses( $p ) . '</p>'; // phpcs:ignore
		}
		if ( '' !== $s['link_text'] ) {
			echo '<a' . zt_link_attrs( $s['link'] ) . ' class="zt-link-more">' . esc_html( $s['link_text'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>'; // phpcs:ignore
		}
		echo '</div><figure class="zt-story__frame">' . zt_img( $s['img'], $s['img_alt'] ) . '</figure></div>'; // phpcs:ignore
		$this->section_close( $s );
	}
}
