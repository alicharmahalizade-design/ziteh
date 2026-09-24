<?php
/**
 * Custom meta fields defined in Ziteh → Meta fields (product / post / page).
 * Values are stored as "zt_{key}" post meta and available to the
 * "متافیلد زیته" widget and Elementor dynamic tags.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Meta_Fields
 */
class ZT_Meta_Fields {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ), 20, 2 );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
	}

	/**
	 * Fields for a post type.
	 *
	 * @param string $pt Post type.
	 * @return array
	 */
	public static function for_type( $pt ) {
		$out = array();
		foreach ( (array) zt_opt( 'meta.fields', array() ) as $f ) {
			if ( ! empty( $f['key'] ) && ( empty( $f['post_type'] ) || $f['post_type'] === $pt ) ) {
				$out[ 'zt_' . sanitize_key( $f['key'] ) ] = $f;
			}
		}
		return $out;
	}

	/**
	 * Expose in REST.
	 */
	public static function register_meta() {
		foreach ( array( 'product', 'post', 'page' ) as $pt ) {
			foreach ( self::for_type( $pt ) as $key => $f ) {
				register_post_meta(
					$pt,
					$key,
					array(
						'single'        => true,
						'type'          => 'string',
						'show_in_rest'  => true,
						'auth_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		}
	}

	/**
	 * Meta boxes.
	 */
	public static function boxes() {
		foreach ( array( 'product', 'post', 'page' ) as $pt ) {
			if ( self::for_type( $pt ) ) {
				add_meta_box( 'zt-meta-fields', 'متافیلدهای زیته', array( __CLASS__, 'render' ), $pt, 'normal', 'default' );
			}
		}
	}

	/**
	 * Render box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render( $post ) {
		wp_nonce_field( 'zt_meta', 'zt_meta_nonce' );
		echo '<table class="form-table"><tbody>';
		foreach ( self::for_type( $post->post_type ) as $key => $f ) {
			$v = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
			switch ( $f['type'] ) {
				case 'textarea':
				case 'lines':
					echo '<textarea class="large-text" rows="4" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $v ) . '</textarea>';
					break;
				case 'select':
					echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '"><option value="">—</option>';
					foreach ( zt_lines( $f['options'] ) as $o ) {
						echo '<option' . selected( $v, $o, false ) . '>' . esc_html( $o ) . '</option>';
					}
					echo '</select>';
					break;
				case 'toggle':
					echo '<label><input type="checkbox" name="' . esc_attr( $key ) . '" value="1"' . checked( $v, '1', false ) . '> بله</label>';
					break;
				case 'image':
					echo '<input type="text" class="regular-text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $v ) . '" placeholder="شناسه یا آدرس تصویر">';
					break;
				default:
					$type = in_array( $f['type'], array( 'number', 'url' ), true ) ? $f['type'] : 'text';
					echo '<input type="' . esc_attr( $type ) . '" class="regular-text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $v ) . '"' . ( 'date' === $f['type'] ? ' placeholder="۱۴۰۳/۰۲/۲۳"' : '' ) . '>';
			}
			if ( ! empty( $f['help'] ) ) {
				echo '<p class="description">' . esc_html( $f['help'] ) . '</p>';
			}
			echo '<p class="description"><code>' . esc_html( $key ) . '</code></p></td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Save.
	 *
	 * @param int     $post_id Id.
	 * @param WP_Post $post    Post.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['zt_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zt_meta_nonce'] ), 'zt_meta' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( self::for_type( $post->post_type ) as $key => $f ) {
			if ( 'toggle' === $f['type'] ) {
				update_post_meta( $post_id, $key, empty( $_POST[ $key ] ) ? '' : '1' );
				continue;
			}
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore
			$val = in_array( $f['type'], array( 'textarea', 'lines' ), true ) ? sanitize_textarea_field( $raw ) : ( 'url' === $f['type'] ? esc_url_raw( $raw ) : sanitize_text_field( $raw ) );
			update_post_meta( $post_id, $key, $val );
		}
	}
}
