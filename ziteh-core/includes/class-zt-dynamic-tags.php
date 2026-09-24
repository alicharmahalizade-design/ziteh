<?php
/**
 * Elementor dynamic tags (group "زیته").
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Controls_Manager;
use Elementor\Modules\DynamicTags\Module as TagsModule;

/**
 * Current post id for tags (product in templates).
 *
 * @return int
 */
function zt_tag_post_id() {
	$p = ZT_Context::product();
	if ( $p ) {
		return $p->get_id();
	}
	return get_the_ID();
}

/**
 * Meta key choices (custom meta fields + built-in product fields).
 *
 * @return array
 */
function zt_tag_meta_choices() {
	$c = array(
		'_zt_en_name'  => 'محصول: نام انگلیسی',
		'_zt_expiry'   => 'محصول: تاریخ انقضا',
		'_zt_volume'   => 'محصول: حجم',
		'_zt_brand'    => 'محصول: برند',
		'_zt_flag'     => 'محصول: برچسب باکس خرید',
		'_zt_note'     => 'محصول: توضیح برند',
		'_zt_desc_title' => 'محصول: عنوان توضیحات',
	);
	foreach ( (array) zt_opt( 'meta.fields', array() ) as $f ) {
		if ( ! empty( $f['key'] ) ) {
			$c[ 'zt_' . sanitize_key( $f['key'] ) ] = $f['label'];
		}
	}
	return $c;
}

/**
 * Text meta.
 */
class ZT_Tag_Meta extends Tag {
	/** @inheritDoc */
	public function get_name() {
		return 'zt-meta';
	}
	/** @inheritDoc */
	public function get_title() {
		return 'متافیلد زیته';
	}
	/** @inheritDoc */
	public function get_group() {
		return 'ziteh';
	}
	/** @inheritDoc */
	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY, TagsModule::NUMBER_CATEGORY );
	}
	/** @inheritDoc */
	protected function register_controls() {
		$this->add_control(
			'key',
			array(
				'label'   => 'فیلد',
				'type'    => Controls_Manager::SELECT,
				'options' => zt_tag_meta_choices(),
			)
		);
		$this->add_control(
			'custom',
			array(
				'label' => 'یا کلید دلخواه',
				'type'  => Controls_Manager::TEXT,
			)
		);
	}
	/** @inheritDoc */
	public function render() {
		$key = $this->get_settings( 'custom' ) ? $this->get_settings( 'custom' ) : $this->get_settings( 'key' );
		if ( ! $key ) {
			return;
		}
		$v = get_post_meta( zt_tag_post_id(), $key, true );
		if ( is_array( $v ) ) {
			$v = implode( '، ', array_map( 'strval', $v ) );
		}
		echo wp_kses_post( zt_fa( (string) $v ) );
	}
}

/**
 * URL meta.
 */
class ZT_Tag_Meta_Url extends Data_Tag {
	/** @inheritDoc */
	public function get_name() {
		return 'zt-meta-url';
	}
	/** @inheritDoc */
	public function get_title() {
		return 'لینک از متافیلد';
	}
	/** @inheritDoc */
	public function get_group() {
		return 'ziteh';
	}
	/** @inheritDoc */
	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}
	/** @inheritDoc */
	protected function register_controls() {
		$this->add_control(
			'key',
			array(
				'label' => 'کلید متافیلد',
				'type'  => Controls_Manager::TEXT,
			)
		);
	}
	/** @inheritDoc */
	public function get_value( array $options = array() ) {
		$k = $this->get_settings( 'key' );
		return $k ? esc_url( (string) get_post_meta( zt_tag_post_id(), $k, true ) ) : '';
	}
}

/**
 * Image meta.
 */
class ZT_Tag_Meta_Image extends Data_Tag {
	/** @inheritDoc */
	public function get_name() {
		return 'zt-meta-image';
	}
	/** @inheritDoc */
	public function get_title() {
		return 'تصویر از متافیلد';
	}
	/** @inheritDoc */
	public function get_group() {
		return 'ziteh';
	}
	/** @inheritDoc */
	public function get_categories() {
		return array( TagsModule::IMAGE_CATEGORY );
	}
	/** @inheritDoc */
	protected function register_controls() {
		$this->add_control(
			'key',
			array(
				'label'   => 'کلید متافیلد',
				'type'    => Controls_Manager::TEXT,
				'default' => '_zt_desc_image',
			)
		);
	}
	/** @inheritDoc */
	public function get_value( array $options = array() ) {
		$v = get_post_meta( zt_tag_post_id(), $this->get_settings( 'key' ), true );
		if ( is_numeric( $v ) ) {
			return array(
				'id'  => (int) $v,
				'url' => (string) wp_get_attachment_image_url( (int) $v, 'full' ),
			);
		}
		return array(
			'id'  => 0,
			'url' => (string) $v,
		);
	}
}

/**
 * Product price.
 */
class ZT_Tag_Price extends Tag {
	/** @inheritDoc */
	public function get_name() {
		return 'zt-price';
	}
	/** @inheritDoc */
	public function get_title() {
		return 'قیمت محصول (زیته)';
	}
	/** @inheritDoc */
	public function get_group() {
		return 'ziteh';
	}
	/** @inheritDoc */
	public function get_categories() {
		return array( TagsModule::TEXT_CATEGORY );
	}
	/** @inheritDoc */
	protected function register_controls() {
		$this->add_control(
			'which',
			array(
				'label'   => 'مقدار',
				'type'    => Controls_Manager::SELECT,
				'default' => 'price',
				'options' => array(
					'price'   => 'قیمت فعلی',
					'regular' => 'قیمت قبل از تخفیف',
					'pct'     => 'درصد تخفیف',
				),
			)
		);
	}
	/** @inheritDoc */
	public function render() {
		$p = ZT_Context::product();
		if ( ! $p ) {
			return;
		}
		$it = ZT_Parts::product_item( $p );
		$w  = $this->get_settings( 'which' );
		if ( 'pct' === $w ) {
			echo esc_html( zt_fa( $it['pct'] ) );
		} else {
			echo esc_html( zt_money( 'regular' === $w ? $it['regular'] : $it['price'], true ) );
		}
	}
}

/**
 * Site page link (token).
 */
class ZT_Tag_Token_Url extends Data_Tag {
	/** @inheritDoc */
	public function get_name() {
		return 'zt-page-url';
	}
	/** @inheritDoc */
	public function get_title() {
		return 'لینک صفحات زیته';
	}
	/** @inheritDoc */
	public function get_group() {
		return 'ziteh';
	}
	/** @inheritDoc */
	public function get_categories() {
		return array( TagsModule::URL_CATEGORY );
	}
	/** @inheritDoc */
	protected function register_controls() {
		$this->add_control(
			'page',
			array(
				'label'   => 'صفحه',
				'type'    => Controls_Manager::SELECT,
				'default' => 'shop',
				'options' => array(
					'home'     => 'خانه',
					'shop'     => 'فروشگاه',
					'cart'     => 'سبد خرید',
					'checkout' => 'صورت‌حساب',
					'account'  => 'پنل کاربری',
					'tracking' => 'پیگیری سفارش',
					'routine'  => 'راهنمای روتین',
					'blog'     => 'مجله',
					'about'    => 'درباره ما',
					'contact'  => 'تماس با ما',
					'wishlist' => 'علاقه‌مندی‌ها',
				),
			)
		);
	}
	/** @inheritDoc */
	public function get_value( array $options = array() ) {
		return zt_page_url( $this->get_settings( 'page' ) );
	}
}
