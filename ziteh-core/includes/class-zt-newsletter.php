<?php
/**
 * Newsletter subscribers (custom table) + admin list / CSV export.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Newsletter
 */
class ZT_Newsletter {

	/**
	 * Table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'zt_subscribers';
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_post_zt_newsletter_export', array( __CLASS__, 'export' ) );
		add_action( 'admin_post_zt_newsletter_delete', array( __CLASS__, 'delete' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_install' ) );
	}

	/**
	 * Create the table.
	 */
	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		dbDelta(
			'CREATE TABLE ' . self::table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(190) NOT NULL,
				created datetime NOT NULL,
				ip varchar(64) NOT NULL DEFAULT '',
				PRIMARY KEY  (id),
				UNIQUE KEY email (email)
			) $charset;"
		);
		update_option( 'zt_newsletter_db', 1 );
	}

	/**
	 * Install on first load after an update.
	 */
	public static function maybe_install() {
		if ( ! get_option( 'zt_newsletter_db' ) ) {
			self::install();
		}
	}

	/**
	 * Subscribe.
	 *
	 * @param string $email Email.
	 * @return string ok|exists
	 */
	public static function subscribe( $email ) {
		global $wpdb;
		$t = self::table();
		if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$t} WHERE email = %s", $email ) ) ) { // phpcs:ignore
			return 'exists';
		}
		$wpdb->insert( // phpcs:ignore
			$t,
			array(
				'email'   => $email,
				'created' => current_time( 'mysql' ),
				'ip'      => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			)
		);
		if ( zt_opt( 'newsletter.notify_admin', 0 ) ) {
			wp_mail( get_option( 'admin_email' ), 'عضویت جدید در خبرنامه', $email );
		}
		do_action( 'zt_newsletter_subscribed', $email );
		return 'ok';
	}

	/**
	 * Rows.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function rows( $limit = 500 ) {
		global $wpdb;
		$t = self::table();
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$t} ORDER BY id DESC LIMIT %d", $limit ) ); // phpcs:ignore
	}

	/**
	 * CSV export.
	 */
	public static function export() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_newsletter' ) ) {
			wp_die( 'forbidden' );
		}
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=ziteh-subscribers.csv' );
		echo "\xEF\xBB\xBF" . "email,date\n"; // phpcs:ignore
		foreach ( self::rows( 100000 ) as $r ) {
			echo esc_html( $r->email ) . ',' . esc_html( $r->created ) . "\n";
		}
		exit;
	}

	/**
	 * Delete one.
	 */
	public static function delete() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'zt_newsletter' ) ) {
			wp_die( 'forbidden' );
		}
		global $wpdb;
		$wpdb->delete( self::table(), array( 'id' => isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 ) ); // phpcs:ignore
		wp_safe_redirect( admin_url( 'admin.php?page=ziteh-core&tab=newsletter' ) );
		exit;
	}
}
