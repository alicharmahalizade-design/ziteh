<?php
/**
 * Global: footer.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Footer
 */
class ZT_W_Footer extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-footer';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-footer';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'فوتر زیته';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c_style', 'سبک' );
		$this->ctl(
			'style',
			'select',
			'سبک فوتر',
			'white',
			array(
				'options'     => array(
					'white' => 'سفید تمام‌عرض (با خط بالا)',
					'cream' => 'کرم جعبه‌ای با گوشه گرد',
				),
				'description' => 'در تنظیمات هر صفحه (زیته) قابل تغییر است.',
			)
		);
		$this->ctl( 'width', 'select', 'عرض محتوا', 'wide', array( 'options' => array( 'wide' => 'کانتینر اصلی (۱۴۴۰)', 'narrow' => 'کانتینر باریک (۱۲۴۰)' ) ) );
		$this->ctl( 'box_width', 'number', 'حداکثر عرض جعبه کرم (px)', 1240, array( 'condition' => array( 'style' => 'cream' ) ) );
		$this->ctl( 'accordion', 'switch', 'ستون‌ها در موبایل آکاردئونی (بسته‌شونده) باشند', false );
		$this->end_controls_section();

		$this->section( 'c_brand', 'برند' );
		$this->ctl( 'logo_type', 'select', 'لوگو', 'text', array( 'options' => array( 'text' => 'متنی', 'image' => 'تصویر' ) ) );
		$this->ctl( 'logo_text', 'text', 'متن لوگو (خالی = نام برند)', '', array( 'condition' => array( 'logo_type' => 'text' ) ) );
		$this->ctl( 'logo_img', 'media', 'تصویر لوگو', '', array( 'condition' => array( 'logo_type' => 'image' ) ) );
		$this->ctl( 'tagline', 'textarea', 'شعار', 'جوانه‌ای برای مراقبت از تو' );
		$this->repeater(
			'socials',
			'شبکه‌های اجتماعی',
			array(
				array( 'label', 'text', 'عنوان', 'اینستاگرام' ),
				array( 'icon', 'icon', 'آیکون', 'instagram' ),
				array( 'url', 'url', 'لینک', '#' ),
			),
			array(
				array( 'label' => 'اینستاگرام', 'icon' => 'instagram', 'url' => '#' ),
				array( 'label' => 'واتساپ', 'icon' => 'whatsapp', 'url' => '#' ),
				array( 'label' => 'تلگرام', 'icon' => 'telegram', 'url' => '#' ),
				array( 'label' => 'بله', 'icon' => 'bale', 'url' => '#' ),
			),
			'{{{ label }}}'
		);
		$this->end_controls_section();

		$this->section( 'c_cols', 'ستون‌های لینک' );
		$menus = array( '' => '— لینک‌های دستی —' );
		foreach ( wp_get_nav_menus() as $m ) {
			$menus[ $m->term_id ] = $m->name;
		}
		$this->repeater(
			'cols',
			'ستون‌ها',
			array(
				array( 'title', 'text', 'عنوان', 'عنوان ستون' ),
				array( 'menu', 'select', 'منوی وردپرس', '', array( 'options' => $menus ) ),
				array( 'links', 'textarea', 'لینک‌ها (هر خط: عنوان|لینک)', '' ),
			),
			array(
				array( 'title' => 'خدمات مشتریان', 'menu' => '', 'links' => "پیگیری سفارش|{{tracking}}\nمرسولات برگشتی|#\nشرایط مرجوعی کالا|#\nشرایط انصراف از خرید|#" ),
				array( 'title' => 'دسترسی سریع', 'menu' => '', 'links' => "فروشگاه|{{shop}}\nبرندها|#\nمجله|{{blog}}\nدرباره ما|{{about}}\nراه ارتباط پشتیبانی|{{contact}}" ),
				array( 'title' => 'راهنمای خرید', 'menu' => '', 'links' => "نحوه ارسال سفارش|#\nتاخیر در ارسال مرسولات|#\nبررسی مرسوله هنگام تحویل|#" ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();

		$this->section( 'c_bottom', 'نوار پایین' );
		$this->ctl( 'copyright', 'textarea', 'متن کپی‌رایت', 'تمامی حقوق مادی و معنوی این فروشگاه اینترنتی متعلق به زیته است.' );
		$this->end_controls_section();

		$this->style_section(
			'st',
			'فوتر',
			array(
				array( 'foot', 'فوتر', '.zt-footer', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'grid', 'شبکه ستون‌ها', '.zt-footer__grid', array( 'gap' ) ),
				array( 'logo', 'لوگو', '.zt-logo b', array( 'typo', 'color' ) ),
				array( 'tag', 'شعار', '.zt-footer__brand p', self::fx( 'text' ) ),
				array( 'soc', 'آیکون‌های اجتماعی', '.zt-socials a', array( 'size', 'color', 'hover_color', 'bg', 'bg_hover', 'radius' ) ),
				array( 'h4', 'عنوان ستون‌ها', '.zt-footer h4', self::fx( 'text' ) ),
				array( 'links', 'لینک‌ها', '.zt-footer__col a', self::fx( 'link' ) ),
				array( 'bottom', 'نوار کپی‌رایت', '.zt-footer__bottom', array( 'typo', 'color', 'bg', 'padding', 'margin', 'border_color' ) ),
			)
		);
	}

	/**
	 * Column links.
	 *
	 * @param array $c Column row.
	 * @return array
	 */
	private function links( $c ) {
		$out = array();
		if ( ! empty( $c['menu'] ) ) {
			foreach ( (array) wp_get_nav_menu_items( (int) $c['menu'] ) as $mi ) {
				$out[] = array( $mi->title, $mi->url );
			}
			return $out;
		}
		foreach ( zt_lines( $c['links'] ) as $l ) {
			$p     = array_map( 'trim', explode( '|', $l, 2 ) );
			$out[] = array( $p[0], zt_url( isset( $p[1] ) ? $p[1] : '#' ) );
		}
		return $out;
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$style = zt_page_setting( 'footer_style' );
		$style = in_array( $style, array( 'white', 'cream' ), true ) ? $style : $s['style'];
		$width = zt_page_setting( 'footer_width' );
		$width = in_array( $width, array( 'wide', 'narrow' ), true ) ? $width : $s['width'];
		$boxw  = zt_page_setting( 'footer_box' );
		$boxw  = $boxw ? (int) $boxw : (int) $s['box_width'];

		$cls  = 'zt-footer' . ( 'cream' === $style ? ' zt-footer--cream' : '' ) . ( 'yes' === $s['accordion'] ? ' zt-footer--acc' : '' );
		$attr = 'cream' === $style ? ' style="border:0;background:var(--zt-cream);border-radius:var(--zt-r-lg);max-width:' . esc_attr( $boxw ) . 'px;margin:0 auto 26px;padding-top:38px"' : '';
		echo '<footer class="' . esc_attr( $cls ) . '"' . $attr . '>'; // phpcs:ignore
		echo '<div class="zt-container' . ( 'narrow' === $width && 'cream' !== $style ? ' zt-container--narrow' : '' ) . '"><div class="zt-footer__grid">';

		echo '<div class="zt-footer__brand"><a href="' . esc_url( home_url( '/' ) ) . '" class="zt-logo zt-logo--lg">';
		if ( 'image' === $s['logo_type'] && ! empty( $s['logo_img']['url'] ) ) {
			echo zt_img( $s['logo_img'], zt_opt( 'general.brand_name' ) ); // phpcs:ignore
		} else {
			echo '<b>' . esc_html( '' !== $s['logo_text'] ? $s['logo_text'] : zt_opt( 'general.brand_name', 'زیته' ) ) . '</b>';
		}
		echo '</a><p>' . nl2br( esc_html( $s['tagline'] ) ) . '</p><div class="zt-socials">';
		foreach ( (array) $s['socials'] as $so ) {
			echo '<a' . zt_link_attrs( $so['url'] ) . ' aria-label="' . esc_attr( $so['label'] ) . '">' . zt_icon( $so['icon'] ) . '</a>'; // phpcs:ignore
		}
		echo '</div></div>';

		foreach ( (array) $s['cols'] as $c ) {
			echo '<div class="zt-footer__col"><h4>' . esc_html( $c['title'] ) . '</h4>';
			if ( 'yes' === $s['accordion'] ) {
				echo '<div class="zt-footer__col__body">';
			}
			echo '<ul>';
			foreach ( $this->links( $c ) as $l ) {
				echo '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
			}
			echo '</ul>';
			if ( 'yes' === $s['accordion'] ) {
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div></div>';
		$copy = zt_page_setting( 'footer_copy' );
		echo '<div class="zt-footer__bottom"' . ( 'cream' === $style ? ' style="border:0"' : '' ) . '>' . zt_kses( $copy ? $copy : $s['copyright'] ) . '</div>';
		echo '</footer>';
	}
}
