<?php
/**
 * Global: display any Ziteh meta field of the current product / post.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Meta_Field
 */
class ZT_W_Meta_Field extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-database';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-meta-field';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'متافیلد زیته';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'فیلد' );
		$opts = array();
		foreach ( (array) zt_opt( 'meta.fields', array() ) as $f ) {
			if ( ! empty( $f['key'] ) ) {
				$opts[ 'zt_' . sanitize_key( $f['key'] ) ] = $f['label'];
			}
		}
		$opts['_zt_en_name'] = 'نام انگلیسی محصول';
		$opts['_zt_expiry']  = 'تاریخ انقضا';
		$opts['_zt_volume']  = 'حجم';
		$opts['_custom']     = 'کلید دلخواه…';
		$this->ctl( 'key', 'select', 'فیلد', key( $opts ), array( 'options' => $opts ) );
		$this->ctl( 'custom', 'text', 'کلید دلخواه', '', array( 'condition' => array( 'key' => '_custom' ) ) );
		$this->ctl( 'label', 'text', 'برچسب قبل از مقدار', '' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'leaf' );
		$this->ctl( 'tag', 'select', 'تگ', 'div', array( 'options' => array( 'div' => 'div', 'p' => 'p', 'span' => 'span', 'h3' => 'h3' ) ) );
		$this->ctl( 'fallback', 'text', 'متن جایگزین (وقتی خالی است)', '' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'متافیلد',
			array(
				array( 'box', 'کادر', '.zt-metaf', array_merge( self::fx( 'text' ), array( 'bg', 'radius', 'padding', 'gap' ) ) ),
				array( 'label', 'برچسب', '.zt-metaf__l', array( 'typo', 'color' ) ),
				array( 'icon', 'آیکون', '.zt-metaf svg', array( 'size', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$key = '_custom' === $s['key'] ? $s['custom'] : $s['key'];
		$p   = ZT_Context::product();
		$id  = $p ? $p->get_id() : get_the_ID();
		$v   = $key ? get_post_meta( $id, $key, true ) : '';
		if ( is_array( $v ) ) {
			$v = implode( '، ', array_map( 'strval', $v ) );
		}
		if ( is_numeric( $v ) && wp_attachment_is_image( (int) $v ) ) {
			echo wp_get_attachment_image( (int) $v, 'large' );
			return;
		}
		$v = '' === (string) $v ? $s['fallback'] : $v;
		if ( '' === (string) $v ) {
			if ( $this->is_editor() ) {
				echo '<div class="zt-metaf">[' . esc_html( $key ) . ']</div>';
			}
			return;
		}
		$tag = in_array( $s['tag'], array( 'div', 'p', 'span', 'h3' ), true ) ? $s['tag'] : 'div';
		echo '<' . $tag . ' class="zt-metaf" style="display:flex;align-items:center;gap:8px">' . zt_icon( $s['icon'] ); // phpcs:ignore
		if ( '' !== $s['label'] ) {
			echo '<span class="zt-metaf__l">' . esc_html( $s['label'] ) . '</span> ';
		}
		echo '<span>' . wp_kses_post( nl2br( zt_fa( $v ) ) ) . '</span></' . $tag . '>'; // phpcs:ignore
	}
}
