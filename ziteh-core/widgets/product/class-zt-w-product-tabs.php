<?php
/**
 * Product: tabs (description, ingredients, usage, reviews) — accordion on mobile.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Tabs
 */
class ZT_W_Product_Tabs extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-tabs';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-tabs';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'تب‌های محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'تب‌ها' );
		$this->ctl( 't_desc', 'text', 'عنوان تب توضیحات', 'توضیحات' );
		$this->ctl( 't_ing', 'text', 'عنوان تب ترکیبات', 'ترکیبات' );
		$this->ctl( 't_use', 'text', 'عنوان تب نحوه استفاده', 'نحوه استفاده' );
		$this->ctl( 't_rev', 'text', 'عنوان تب نظرات ({count})', 'نظرات ({count})' );
		$this->ctl( 'show_ing', 'switch', 'نمایش تب ترکیبات', true );
		$this->ctl( 'show_use', 'switch', 'نمایش تب نحوه استفاده', true );
		$this->ctl( 'show_rev', 'switch', 'نمایش تب نظرات', true );
		$this->ctl( 'rev_text', 'textarea', 'متن تب نظرات', 'میانگین امتیاز این محصول {avg} از ۵ بر اساس {count} نظر ثبت‌شده است. برای مشاهده همه نظرات به بخش پایین صفحه مراجعه کنید.' );
		$this->ctl( 'rev_form', 'switch', 'فرم ثبت نظر در تب نظرات', true );
		$this->ctl( 'show_props', 'switch', 'نوار ویژگی‌ها زیر توضیحات', true );
		$this->repeater(
			'props',
			'نوار ویژگی‌ها (پیش‌فرض/نمونه)',
			array(
				array( 'icon', 'icon', 'آیکون', 'leaf' ),
				array( 'text', 'text', 'متن', '' ),
			),
			array(
				array( 'icon' => 'waves', 'text' => 'مناسب انواع مو' ),
				array( 'icon' => 'flask', 'text' => 'بدون پارابن' ),
				array( 'icon' => 'leaf', 'text' => 'فرمول گیاهی' ),
				array( 'icon' => 'atom', 'text' => 'بدون سولفات' ),
			),
			'{{{ text }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'تب‌ها',
			array(
				array( 'bar', 'نوار تب‌ها', '.zt-tabs', array( 'gap', 'margin', 'justify', 'border_color' ) ),
				array( 'tab', 'تب', '.zt-tabs button', array( 'typo', 'color', 'hover_color', 'padding' ) ),
				array( 'taba', 'تب فعال', '.zt-tabs button.zt-is-active', array( 'color' ) ),
				array( 'line', 'خط تب فعال', '.zt-tabs button.zt-is-active::after', array( 'bg', 'height' ) ),
				array( 'panel', 'پنل', '.zt-tabpanel', array( 'bg', 'radius', 'padding', 'margin' ) ),
				array( 'h3', 'عنوان پنل', '.zt-tabpanel h3', self::fx( 'text' ) ),
				array( 'p', 'متن پنل', '.zt-tabpanel p, .zt-tabpanel ul', array( 'typo', 'color', 'max_width' ) ),
				array( 'img', 'تصویر', '.zt-tabpanel img', self::fx( 'image' ) ),
				array( 'props', 'نوار ویژگی‌ها', '.zt-propstrip', array( 'bg', 'border', 'radius', 'margin', 'columns' ) ),
				array( 'propi', 'آیکون ویژگی', '.zt-propstrip svg', array( 'size', 'color' ) ),
			)
		);
	}

	/**
	 * Demo content.
	 *
	 * @return array
	 */
	private function demo() {
		return array(
			'title' => 'قدرت طبیعت برای مویی سالم‌تر',
			'desc'  => '<p>شامپو تقویت‌کننده زیته با ترکیبی از عصاره‌های گیاهی و مواد مؤثر طبیعی، کاهش ریزش مو کمک کرده و باعث تقویت ریشه و افزایش استحکام تارهای مو می‌شود.</p><p>استفاده منظم از این شامپو، موهایی سالم‌تر، پرپشت‌تر و درخشان‌تر برای شما به ارمغان می‌آورد.</p>',
			'img'   => zt_asset_img( 'p-desc.jpg' ),
			'ing'   => array( 'عصاره رزماری — تحریک گردش خون پوست سر و تقویت ریشه مو', 'روغن آرگان — تغذیه و نرم‌کنندگی تارهای مو', 'بیوتین و پانتنول — افزایش استحکام و درخشندگی مو', 'عصاره آلوئه‌ورا — آبرسانی و تسکین پوست سر حساس', 'بدون سولفات، بدون پارابن و بدون رنگ مصنوعی' ),
			'use'   => array( 'موها را کاملاً مرطوب کنید و مقدار مناسبی از شامپو را روی پوست سر بمالید.', 'با نوک انگشتان به مدت ۲ تا ۳ دقیقه ماساژ دهید تا کف کند.', 'با آب ولرم کاملاً بشویید و در صورت نیاز مرحله را تکرار کنید.', 'برای بهترین نتیجه، ۳ تا ۴ بار در هفته استفاده شود.' ),
			'props' => array(),
			'avg'   => 4.8,
			'count' => 25,
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p = ZT_Context::product();
		if ( $p ) {
			$id   = $p->get_id();
			$img  = (int) get_post_meta( $id, '_zt_desc_image', true );
			$desc = $p->get_description();
			$d    = array(
				'title' => get_post_meta( $id, '_zt_desc_title', true ),
				'desc'  => $desc ? wpautop( do_shortcode( $desc ) ) : '',
				'img'   => $img ? wp_get_attachment_image_url( $img, 'large' ) : '',
				'ing'   => zt_lines( get_post_meta( $id, '_zt_ingredients', true ) ),
				'use'   => zt_lines( get_post_meta( $id, '_zt_usage', true ) ),
				'props' => ZT_Product_Meta::pairs( get_post_meta( $id, '_zt_props', true ), 'leaf' ),
				'avg'   => (float) $p->get_average_rating(),
				'count' => (int) $p->get_review_count(),
			);
		} else {
			$d = $this->demo();
		}
		$props = $d['props'];
		if ( ! $props ) {
			foreach ( (array) $s['props'] as $r ) {
				$props[] = array( $r['icon'], $r['text'] );
			}
		}
		$tabs = array( 'desc' => $s['t_desc'] );
		if ( 'yes' === $s['show_ing'] && $d['ing'] ) {
			$tabs['ing'] = $s['t_ing'];
		}
		if ( 'yes' === $s['show_use'] && $d['use'] ) {
			$tabs['use'] = $s['t_use'];
		}
		if ( 'yes' === $s['show_rev'] ) {
			$tabs['rev'] = str_replace( '{count}', zt_fa( $d['count'] ), $s['t_rev'] );
		}
		echo '<div data-zt-tabs class="zt-tabs">';
		$first = true;
		foreach ( $tabs as $k => $label ) {
			echo '<button' . ( $first ? ' class="zt-is-active"' : '' ) . ' data-zt-tab="' . esc_attr( $k ) . '">' . esc_html( $label ) . '</button>';
			$first = false;
		}
		echo '</div>';

		echo '<section class="zt-tabpanel" data-zt-panel="desc">';
		if ( $d['img'] ) {
			echo '<div class="zt-tabpanel__grid"><div>';
		}
		if ( $d['title'] ) {
			echo '<h3>' . esc_html( $d['title'] ) . '</h3>';
		}
		echo wp_kses_post( $d['desc'] );
		if ( $d['img'] ) {
			echo '</div><img src="' . esc_url( $d['img'] ) . '" alt="' . esc_attr( $d['title'] ) . '"></div>';
		}
		if ( 'yes' === $s['show_props'] && $props ) {
			echo '<div class="zt-propstrip">';
			foreach ( $props as $pr ) {
				echo '<div>' . zt_icon( $pr[0] ) . ' ' . esc_html( $pr[1] ) . '</div>'; // phpcs:ignore
			}
			echo '</div>';
		}
		echo '</section>';
		foreach ( array( 'ing', 'use' ) as $k ) {
			if ( isset( $tabs[ $k ] ) ) {
				echo '<section class="zt-tabpanel" data-zt-panel="' . esc_attr( $k ) . '" hidden><h3>' . esc_html( $tabs[ $k ] ) . '</h3><ul>';
				foreach ( $d[ $k ] as $li ) {
					echo '<li>' . esc_html( $li ) . '</li>';
				}
				echo '</ul></section>';
			}
		}
		if ( isset( $tabs['rev'] ) ) {
			$avg = rtrim( rtrim( number_format( $d['avg'], 1, '.', '' ), '0' ), '.' );
			echo '<section class="zt-tabpanel" data-zt-panel="rev" hidden><h3>نظرات کاربران</h3><p>' . esc_html( zt_fa( str_replace( array( '{avg}', '{count}' ), array( $avg, $d['count'] ), $s['rev_text'] ) ) ) . '</p>';
			if ( 'yes' === $s['rev_form'] && $p && comments_open( $p->get_id() ) ) {
				echo ZT_W_Product_Reviews::form( $p->get_id() ); // phpcs:ignore
			}
			echo '</section>';
		}
	}
}
