<?php
/**
 * Shared state for the account (panel) widgets.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ZT_Acc' ) ) {
	/**
	 * Class ZT_Acc
	 */
	class ZT_Acc {

		/**
		 * Render mode: sample (design / editor), user, guest.
		 *
		 * @return string
		 */
		public static function mode() {
			if ( ZT_Context::demo() || zt_is_editor() || ! zt_is_woo() ) {
				return 'sample';
			}
			return is_user_logged_in() ? 'user' : 'guest';
		}

		/**
		 * Current account endpoint ('' on the dashboard).
		 *
		 * @return string
		 */
		public static function endpoint() {
			return 'sample' === self::mode() ? '' : ZT_Account::endpoint();
		}

		/**
		 * Is the dashboard shown?
		 *
		 * @return bool
		 */
		public static function dashboard() {
			$m = self::mode();
			return 'sample' === $m || ( 'user' === $m && '' === self::endpoint() );
		}

		/**
		 * dcard head.
		 *
		 * @param array  $s    Settings.
		 * @param string $link Url of the header link (or '').
		 * @return string
		 */
		public static function head( $s, $link ) {
			$out = '<div class="zt-dcard__head"><h3>' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h3>';
			if ( ! empty( $s['head_link_text'] ) ) {
				$out .= '<a href="' . esc_url( $link ) . '">' . esc_html( $s['head_link_text'] ) . '</a>';
			}
			return $out . '</div>';
		}

		/**
		 * Wide button.
		 *
		 * @param array  $s    Settings.
		 * @param string $link Url.
		 * @return string
		 */
		public static function wide( $s, $link ) {
			if ( empty( $s['wide_text'] ) ) {
				return '';
			}
			return '<a href="' . esc_url( $link ) . '" class="zt-wide-btn">' . esc_html( $s['wide_text'] ) . ' ' . zt_icon( 'arrow-left' ) . '</a>';
		}
	}
}
