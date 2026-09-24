<?php
/**
 * Home: trusted brands row.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Brands
 */
class ZT_W_Brands extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-logo';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-brands';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'برندهای معتبر';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'برندهای معتبر', array( 'center' => true ) );
		$this->end_controls_section();
		$this->section( 'c2', 'برندها' );
		$this->ctl( 'bsrc', 'select', 'منبع', 'manual', array( 'options' => array( 'manual' => 'دستی', 'terms' => 'برندهای ووکامرس (product_brand)' ) ) );
		$this->repeater(
			'brands',
			'برندها',
			array(
				array( 'name', 'text', 'نام', 'Brand' ),
				array( 'logo', 'media', 'لوگو (اختیاری — جایگزین متن)', '' ),
				array( 'url', 'url', 'لینک', '' ),
			),
			array_map(
				function ( $n ) {
					return array( 'name' => $n, 'logo' => '', 'url' => '' );
				},
				array( 'DERMATYPIQUE', 'Ormus', 'Revival', 'SKIN ONE', 'racuten', 'With you', 'EIN', 'Veronique', 'SEBYCTA', 'Bio Marine' )
			),
			'{{{ name }}}',
			array( 'condition' => array( 'bsrc' => 'manual' ) )
		);
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'برندها',
			array(
				array( 'list', 'فهرست', '.zt-brands', array( 'gap', 'justify', 'margin' ) ),
				array( 'item', 'نام برند', '.zt-brands li', array( 'typo', 'color', 'hover_color', 'padding', 'bg', 'radius' ) ),
				array( 'logo', 'لوگو', '.zt-brands img', array( 'height', 'opacity' ) ),
				array( 'sep', 'جداکننده', '.zt-brands li + li::after', array( 'bg', 'display' ) ),
				array( 'title', 'عنوان بخش', '.zt-sec-title', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$list = array();
		if ( 'terms' === $s['bsrc'] && taxonomy_exists( 'product_brand' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_brand',
					'hide_empty' => false,
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$thumb  = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
				$list[] = array( $t->name, $thumb ? wp_get_attachment_image_url( $thumb, 'medium' ) : '', get_term_link( $t ) );
			}
		} else {
			foreach ( (array) $s['brands'] as $b ) {
				$list[] = array( $b['name'], zt_img_url( $b['logo'] ), ! empty( $b['url']['url'] ) ? zt_url( $b['url'] ) : '' );
			}
		}
		$this->section_open( $s, 'zt-brands-sec' );
		$this->heading_render( $s );
		echo '<ul class="zt-brands' . esc_attr( $this->reveal( $s ) ) . '">';
		foreach ( $list as $b ) {
			$in = $b[1] ? '<img src="' . esc_url( $b[1] ) . '" alt="' . esc_attr( $b[0] ) . '" style="height:32px;width:auto">' : esc_html( $b[0] );
			if ( $b[2] ) {
				$in = '<a href="' . esc_url( $b[2] ) . '">' . $in . '</a>';
			}
			echo '<li>' . $in . '</li>'; // phpcs:ignore
		}
		echo '</ul>';
		$this->section_close( $s );
	}
}
