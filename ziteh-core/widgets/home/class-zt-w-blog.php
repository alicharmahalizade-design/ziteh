<?php
/**
 * Home: magazine (latest posts).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Blog
 */
class ZT_W_Blog extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-posts-grid';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-blog';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'مجله زیته (مقالات)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'مجله زیته', array( 'link_text' => 'مشاهده مقالات', 'link_url' => '{{blog}}' ) );
		$this->end_controls_section();
		$this->section( 'c2', 'مقالات' );
		$this->post_source_controls(
			array(
				array( 'title' => 'جوش‌های زیر پوستی چیست؟ دلایل و روش های درمان', 'tag' => 'دسته‌بندی نشده', 'img' => 'blog-1.jpg', 'url' => '#' ),
				array( 'title' => 'از بین بردن لک صورت با خمیردندان', 'tag' => 'وبلاگ', 'img' => 'blog-2.jpg', 'url' => '#' ),
				array( 'title' => '۴ ویژگی که پماد حساسیت پوستی باید داشته باشد! + معرفی انواع و علائم حساسیت پوستی', 'tag' => 'وبلاگ', 'img' => 'blog-3.jpg', 'url' => '#' ),
			)
		);
		$this->ctl( 'more', 'text', 'متن لینک کارت', 'مطالعه مقاله' );
		$this->ctl( 'show_tag', 'switch', 'نمایش برچسب دسته', true );
		$this->end_controls_section();
		$this->section_wrap_controls( 'cream' );
		$this->post_card_styles();
	}

	/**
	 * Card styles.
	 */
	protected function post_card_styles() {
		$this->style_section(
			'st',
			'کارت مقاله',
			array(
				array( 'grid', 'شبکه', '.zt-posts', self::fx( 'grid' ) ),
				array( 'card', 'کارت', '.zt-post', array( 'bg', 'radius', 'shadow' ) ),
				array( 'img', 'تصویر', '.zt-post__img', array( 'radius', 'ratio', 'margin' ) ),
				array( 'tag', 'برچسب', '.zt-post__tag', self::fx( 'badge' ) ),
				array( 'body', 'بدنه', '.zt-post__body', array( 'padding' ) ),
				array( 'title', 'عنوان', '.zt-post h3', array( 'typo', 'color', 'min_height' ) ),
				array( 'more', 'لینک', '.zt-post .zt-link-more', self::fx( 'link' ) ),
				array( 'stitle', 'عنوان بخش', '.zt-sec-title', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s );
		$this->heading_render( $s );
		echo '<div class="zt-posts' . esc_attr( $this->reveal( $s ) ) . '">';
		foreach ( $this->get_posts_items( $s, is_singular( 'post' ) ? array( get_the_ID() ) : array() ) as $it ) {
			echo ZT_Parts::post_card( $it, array( 'more' => $s['more'], 'tag' => 'yes' === $s['show_tag'] ) ); // phpcs:ignore
		}
		echo '</div>';
		$this->section_close( $s );
	}
}
