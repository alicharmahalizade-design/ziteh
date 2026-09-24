<?php
/**
 * Home: testimonials.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Testimonials
 */
class ZT_W_Testimonials extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-testimonial';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-testimonials';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نظرات مشتریان';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'نظر شما، برای ما ارزشمند است', array( 'center' => true ) );
		$this->end_controls_section();
		$this->section( 'c2', 'نظرات' );
		$this->ctl( 'qsrc', 'select', 'منبع', 'manual', array( 'options' => array( 'manual' => 'دستی', 'reviews' => 'آخرین نظرات محصولات (۴ ستاره و بالاتر)' ) ) );
		$this->ctl( 'q_limit', 'number', 'تعداد', 3, array( 'condition' => array( 'qsrc' => 'reviews' ) ) );
		$this->repeater(
			'quotes',
			'نظرات',
			array(
				array( 'rating', 'number', 'امتیاز (۱ تا ۵)', 5 ),
				array( 'text', 'textarea', 'متن', '' ),
				array( 'name', 'text', 'نام', '' ),
				array( 'avatar', 'media', 'تصویر (خالی = حرف اول نام)', '' ),
			),
			array(
				array( 'rating' => 5, 'text' => 'محصولات اورجینال و ارسال سریع. تجربه خرید براِم عالی کرد.', 'name' => 'نهال محمدی', 'avatar' => '' ),
				array( 'rating' => 5, 'text' => 'تنوع محصولات و توضیحات کامل باعث شد انتخاب راحتی داشته باشم.', 'name' => 'سارا احمدی', 'avatar' => '' ),
				array( 'rating' => 5, 'text' => 'پشتیبانی عالی و بسته‌بندی شیک، حس خاصی به من داد. ممنون از زیته!', 'name' => 'مریم کریمی', 'avatar' => '' ),
			),
			'{{{ name }}}',
			array( 'condition' => array( 'qsrc' => 'manual' ) )
		);
		$this->end_controls_section();
		$this->section_wrap_controls( 'cream' );
		$this->style_section(
			'st',
			'نظرات',
			array(
				array( 'grid', 'شبکه', '.zt-quotes', self::fx( 'grid' ) ),
				array( 'card', 'کارت', '.zt-quote', self::fx( 'card' ) ),
				array( 'stars', 'ستاره‌ها', '.zt-quote .zt-stars', array( 'color', 'margin', 'justify' ) ),
				array( 'text', 'متن', '.zt-quote p', self::fx( 'text' ) ),
				array( 'ava', 'آواتار', '.zt-quote__ava', array( 'size', 'bg', 'color', 'typo', 'radius' ) ),
				array( 'name', 'نام', '.zt-quote b', self::fx( 'text' ) ),
				array( 'title', 'عنوان بخش', '.zt-sec-title', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$rows = array();
		if ( 'reviews' === $s['qsrc'] ) {
			$comments = get_comments(
				array(
					'post_type'  => 'product',
					'status'     => 'approve',
					'number'     => max( 1, (int) $s['q_limit'] ),
					'meta_query' => array( array( 'key' => 'rating', 'value' => 4, 'compare' => '>=', 'type' => 'NUMERIC' ) ), // phpcs:ignore
				)
			);
			foreach ( $comments as $c ) {
				$rows[] = array( (int) get_comment_meta( $c->comment_ID, 'rating', true ), $c->comment_content, $c->comment_author, '' );
			}
		} else {
			foreach ( (array) $s['quotes'] as $q ) {
				$rows[] = array( (int) $q['rating'], $q['text'], $q['name'], zt_img_url( $q['avatar'] ) );
			}
		}
		$this->section_open( $s );
		$this->heading_render( $s );
		echo '<div class="zt-quotes' . esc_attr( $this->reveal( $s ) ) . '">';
		foreach ( $rows as $r ) {
			$initial = function_exists( 'mb_substr' ) ? mb_substr( trim( $r[2] ), 0, 1 ) : substr( trim( $r[2] ), 0, 1 );
			echo '<div class="zt-quote">' . ZT_Parts::stars( $r[0], 'div' ) . '<p>' . esc_html( $r[1] ) . '</p>'; // phpcs:ignore
			echo '<div class="zt-quote__ava">' . ( $r[3] ? '<img src="' . esc_url( $r[3] ) . '" alt="" style="width:100%;height:100%;border-radius:50%;object-fit:cover">' : esc_html( $initial ) ) . '</div>';
			echo '<b>' . esc_html( $r[2] ) . '</b></div>';
		}
		echo '</div>';
		$this->section_close( $s );
	}
}
