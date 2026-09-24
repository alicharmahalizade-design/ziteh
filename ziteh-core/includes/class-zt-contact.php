<?php
/**
 * Contact messages: stored as a private post type + emailed to the admin.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Contact
 */
class ZT_Contact {

	const CPT = 'zt_message';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Post type.
	 */
	public static function register() {
		register_post_type(
			self::CPT,
			array(
				'labels'       => array(
					'name'          => 'پیام‌های تماس',
					'singular_name' => 'پیام',
					'menu_name'     => 'پیام‌های تماس',
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => 'ziteh-core',
				'supports'     => array( 'title', 'editor' ),
				'capabilities' => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap' => true,
			)
		);
	}

	/**
	 * Store + email.
	 *
	 * @param array $d name, phone, email, msg.
	 */
	public static function store( $d ) {
		$body = $d['msg'] . "\n\n" . 'تلفن: ' . $d['phone'] . "\n" . 'ایمیل: ' . $d['email'];
		wp_insert_post(
			array(
				'post_type'    => self::CPT,
				'post_status'  => 'private',
				'post_title'   => $d['name'] . ' — ' . ( $d['phone'] ? $d['phone'] : $d['email'] ),
				'post_content' => $body,
			)
		);
		$to = zt_opt( 'newsletter.contact_to' );
		wp_mail( $to ? $to : get_option( 'admin_email' ), 'پیام جدید از فرم تماس: ' . $d['name'], $body, $d['email'] ? array( 'Reply-To: ' . $d['email'] ) : array() );
	}
}
