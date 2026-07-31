<?php
/**
 * One-click template importer.
 *
 * Adds an admin screen (under Elementor) with a single button that builds a
 * ready-made "صفحه اصلی زیته" page from templates/ziteh-home.json — all 17
 * widgets pre-arranged in order — so the user doesn't have to place them by
 * hand. The created page is a full Elementor page in canvas layout.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Template_Importer
 */
class Ziteh_Template_Importer {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Template_Importer|null
	 */
	private static $instance = null;

	/**
	 * Admin action slug used for the importer form.
	 */
	const ACTION = 'ziteh_import_home';

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Template_Importer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Registers the admin menu + form handler.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_import' ) );
	}

	/**
	 * Add the "قالب آماده زیته" screen under the Elementor menu (falls back to
	 * the Tools menu if Elementor's top-level menu isn't present yet).
	 */
	public function register_menu() {
		$parent = menu_page_url( 'elementor', false ) ? 'elementor' : 'tools.php';

		add_submenu_page(
			$parent,
			esc_html__( 'قالب آماده زیته', 'ziteh' ),
			esc_html__( 'قالب آماده زیته', 'ziteh' ),
			'edit_pages',
			'ziteh-template',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the importer admin screen.
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$notice   = isset( $_GET['ziteh_notice'] ) ? sanitize_key( wp_unslash( $_GET['ziteh_notice'] ) ) : '';
		$edit_url = isset( $_GET['ziteh_edit'] ) ? esc_url_raw( wp_unslash( $_GET['ziteh_edit'] ) ) : '';
		$view_url = isset( $_GET['ziteh_view'] ) ? esc_url_raw( wp_unslash( $_GET['ziteh_view'] ) ) : '';
		?>
		<div class="wrap" dir="rtl" style="max-width:760px">
			<h1><?php esc_html_e( 'قالب آماده صفحه اصلی زیته', 'ziteh' ); ?></h1>

			<?php if ( 'success' === $notice ) : ?>
				<div class="notice notice-success">
					<p><?php esc_html_e( 'صفحه اصلی زیته با موفقیت ساخته شد. هر ۱۷ سکشن به ترتیب طرح چیده شده‌اند.', 'ziteh' ); ?></p>
					<p>
						<?php if ( $edit_url ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'ویرایش با المنتور', 'ziteh' ); ?></a>
						<?php endif; ?>
						<?php if ( $view_url ) : ?>
							<a class="button" href="<?php echo esc_url( $view_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'مشاهده صفحه', 'ziteh' ); ?></a>
						<?php endif; ?>
					</p>
				</div>
			<?php elseif ( 'error' === $notice ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'ساخت صفحه ناموفق بود. فایل قالب یافت نشد یا معتبر نیست.', 'ziteh' ); ?></p></div>
			<?php endif; ?>

			<p><?php esc_html_e( 'با یک کلیک، یک برگه‌ی کامل المنتور شامل تمام ویجت‌های زیته (از تاپ‌بار تا فوتر) با چیدمان دقیق طرح ساخته می‌شود. سپس می‌توانید محتوا و تصاویر را در المنتور ویرایش کنید.', 'ziteh' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
				<?php wp_nonce_field( self::ACTION ); ?>
				<p>
					<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'ساخت صفحه اصلی زیته', 'ziteh' ); ?></button>
				</p>
			</form>

			<hr>
			<h2><?php esc_html_e( 'روش جایگزین: ایمپورت دستی', 'ziteh' ); ?></h2>
			<p><?php esc_html_e( 'در المنتور به مسیر «قالب‌ها ← قالب‌های ذخیره‌شده ← ایمپورت» بروید و فایل زیر را بارگذاری کنید:', 'ziteh' ); ?></p>
			<p><code>wp-content/plugins/ziteh-elementor/templates/ziteh-home.json</code></p>
		</div>
		<?php
	}

	/**
	 * Handle the importer form submission: create the page + Elementor data.
	 */
	public function handle_import() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'ziteh' ) );
		}
		check_admin_referer( self::ACTION );

		$data = $this->load_template_content();

		if ( empty( $data ) ) {
			$this->redirect_back( 'error' );
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => esc_html__( 'صفحه اصلی زیته', 'ziteh' ),
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			)
		);

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			$this->redirect_back( 'error' );
		}

		// Elementor page meta so the builder recognises the layout.
		update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $page_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.5.0' );
		update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );
		// _elementor_data must be a JSON string of the elements array, slashed.
		update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );

		// Ask Elementor to regenerate the page CSS on next load.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			try {
				$css = \Elementor\Core\Files\CSS\Post::create( $page_id );
				$css->update();
			} catch ( \Exception $e ) {
				// Non-fatal: Elementor will regenerate CSS when the page is viewed.
				unset( $e );
			}
		}

		$this->redirect_back(
			'success',
			array(
				'ziteh_edit' => rawurlencode( admin_url( 'post.php?post=' . $page_id . '&action=elementor' ) ),
				'ziteh_view' => rawurlencode( get_permalink( $page_id ) ),
			)
		);
	}

	/**
	 * Read templates/ziteh-home.json and return its `content` array.
	 *
	 * @return array<int,mixed>
	 */
	private function load_template_content() {
		$file = ZITEH_EL_PATH . 'templates/ziteh-home.json';
		if ( ! file_exists( $file ) ) {
			return array();
		}
		$raw = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! $raw ) {
			return array();
		}
		$json = json_decode( $raw, true );
		if ( ! is_array( $json ) || empty( $json['content'] ) || ! is_array( $json['content'] ) ) {
			return array();
		}
		return $json['content'];
	}

	/**
	 * Redirect back to the importer screen with a notice.
	 *
	 * @param string               $notice 'success' | 'error'.
	 * @param array<string,string> $extra  Extra query args.
	 */
	private function redirect_back( $notice, $extra = array() ) {
		$args = array_merge(
			array(
				'page'         => 'ziteh-template',
				'ziteh_notice' => $notice,
			),
			$extra
		);
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}
}
