<?php
/**
 * One-click importer for the ready-made Ziteh layouts.
 *
 * Adds an admin screen (under Elementor) listing every layout shipped in
 * templates/*.json — the home, about, contact and article-archive pages plus
 * the header, footer and single-post documents for Elementor's Theme Builder.
 * Each one is built with its widgets already arranged in the designed order, so
 * nothing has to be placed by hand.
 *
 * Widget settings are deliberately absent from the JSON wherever the widget's
 * own default is the intended content: the imported page then shows the same
 * copy the widget ships with, and improving a default improves every page
 * imported afterwards.
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
	const ACTION = 'ziteh_import_template';

	/**
	 * Meta key stamped on every imported document, so the screen can tell the
	 * user what already exists instead of quietly creating duplicates.
	 */
	const MARK = '_ziteh_template';

	/**
	 * Admin page slug.
	 */
	const PAGE = 'ziteh-template';

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
		// Priority 20: Elementor's own top-level menu is registered by then, so
		// the submenu lands under it instead of being orphaned.
		add_action( 'admin_menu', array( $this, 'register_menu' ), 20 );
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_import' ) );
	}

	/**
	 * The catalogue of shipped layouts.
	 *
	 * `kind` decides where the document lands: 'page' creates a real WordPress
	 * page, 'library' creates an Elementor Library document whose type is read
	 * by the Theme Builder. Library documents of type header/footer/single-post
	 * can only be *assigned* to the site by Elementor Pro, which is why they
	 * carry `pro`.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function templates() {
		return array(
			'home'        => array(
				'kind'        => 'page',
				'label'       => esc_html__( 'صفحه اصلی', 'ziteh' ),
				'post_title'  => esc_html__( 'صفحه اصلی زیته', 'ziteh' ),
				'description' => esc_html__( 'کل صفحه اصلی از تاپ‌بار تا فوتر: هیرو، دسته‌بندی‌ها، روتین، محصولات، پیشنهاد شگفت‌انگیز، کوییز پوست، برندها، مقالات، مشاوره، نظرات، اینستاگرام و خبرنامه.', 'ziteh' ),
				'after'       => esc_html__( 'برای نمایش به‌عنوان صفحه نخست، در «تنظیمات ← خواندن» این برگه را به‌عنوان صفحه اصلی انتخاب کنید.', 'ziteh' ),
			),
			'about'       => array(
				'kind'        => 'page',
				'label'       => esc_html__( 'درباره ما', 'ziteh' ),
				'post_title'  => esc_html__( 'درباره ما', 'ziteh' ),
				'description' => esc_html__( 'مسیر و تاریخچه برند، داستان زیته، مزیت‌ها، معرفی تیم، نظرات مشتریان و دعوت به مشاوره.', 'ziteh' ),
				'after'       => esc_html__( 'برگه ساخته شد ولی هنوز در منو نیست؛ از «نمایش ← فهرست‌ها» آن را به منوی اصلی اضافه کنید.', 'ziteh' ),
			),
			'contact'     => array(
				'kind'        => 'page',
				'label'       => esc_html__( 'تماس با ما', 'ziteh' ),
				'post_title'  => esc_html__( 'تماس با ما', 'ziteh' ),
				'description' => esc_html__( 'راه‌های ارتباطی، فرم تماس با ارسال بدون بارگذاری مجدد، نقشه اختیاری و نوار خدمات فروشگاه.', 'ziteh' ),
				'after'       => esc_html__( 'شماره، ایمیل و نشانی از «تنظیمات زیته ← محتوا» خوانده می‌شوند؛ یک‌بار آنجا پرشان کنید تا در همه صفحه‌ها یکسان بماند.', 'ziteh' ),
			),
			'articles'    => array(
				'kind'        => 'page',
				'label'       => esc_html__( 'آرشیو مقالات', 'ziteh' ),
				'post_title'  => esc_html__( 'مقالات', 'ziteh' ),
				'description' => esc_html__( 'شبکه کارت‌های مقاله با نوشته شاخص، فیلتر دسته‌ها و صفحه‌بندی، به‌همراه بخش خبرنامه.', 'ziteh' ),
				'after'       => esc_html__( 'این برگه را در «تنظیمات ← خواندن» به‌عنوان «صفحه نوشته‌ها» انتخاب نکنید؛ در آن حالت وردپرس محتوای برگه را کنار می‌گذارد و چیدمان دیده نمی‌شود. کافی است لینک همین برگه را در منو بگذارید.', 'ziteh' ),
			),
			'single-post' => array(
				'kind'          => 'library',
				'template_type' => 'single-post',
				'pro'           => true,
				'label'         => esc_html__( 'تک مقاله', 'ziteh' ),
				'post_title'    => esc_html__( 'زیته | تک مقاله', 'ziteh' ),
				'description'   => esc_html__( 'چیدمان کامل تک‌مقاله: تصویر شاخص، فهرست مطالب خودکار، بدنه مقاله، اشتراک‌گذاری و مطالب مرتبط.', 'ziteh' ),
				'after'         => esc_html__( 'پس از ساخت، در «قالب‌ها ← سازنده قالب» شرط نمایش را روی «همه نوشته‌ها» تنظیم کنید. تا وقتی شرط را تعیین نکنید هیچ تغییری در سایت دیده نمی‌شود.', 'ziteh' ),
			),
			'header'      => array(
				'kind'          => 'library',
				'template_type' => 'header',
				'pro'           => true,
				'label'         => esc_html__( 'هدر سایت', 'ziteh' ),
				'post_title'    => esc_html__( 'زیته | هدر سایت', 'ziteh' ),
				'description'   => esc_html__( 'تاپ‌بار و هدر زیته به‌صورت هدر سراسری، برای استفاده با قالب وردپرسی زیته.', 'ziteh' ),
				'after'         => esc_html__( 'شرط نمایش را روی «کل سایت» بگذارید و ویجت‌های هدر را از برگه‌ها حذف کنید تا دوباره تکرار نشوند.', 'ziteh' ),
			),
			'footer'      => array(
				'kind'          => 'library',
				'template_type' => 'footer',
				'pro'           => true,
				'label'         => esc_html__( 'فوتر سایت', 'ziteh' ),
				'post_title'    => esc_html__( 'زیته | فوتر سایت', 'ziteh' ),
				'description'   => esc_html__( 'فوتر زیته به‌صورت فوتر سراسری، برای استفاده با قالب وردپرسی زیته.', 'ziteh' ),
				'after'         => esc_html__( 'شرط نمایش را روی «کل سایت» بگذارید و ویجت فوتر را از برگه‌ها حذف کنید.', 'ziteh' ),
			),
		);
	}

	/**
	 * Add the "قالب آماده زیته" screen under the Elementor menu (falls back to
	 * the Tools menu if Elementor's top-level menu isn't present yet).
	 */
	public function register_menu() {
		add_submenu_page(
			$this->parent_slug(),
			esc_html__( 'قالب آماده زیته', 'ziteh' ),
			esc_html__( 'قالب آماده زیته', 'ziteh' ),
			'edit_pages',
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Which menu the screen hangs off.
	 *
	 * This is decided from Elementor's own load state rather than from the menu
	 * globals, because the form handler runs on admin-post.php where the admin
	 * menu is never built — reading the globals there would always fall back to
	 * Tools and redirect the user to a permissions error.
	 *
	 * @return string
	 */
	private function parent_slug() {
		return did_action( 'elementor/loaded' ) ? 'elementor' : 'tools.php';
	}

	/**
	 * Is Elementor Pro active? Theme Builder documents need it to be assigned.
	 *
	 * @return bool
	 */
	private function has_pro() {
		return defined( 'ELEMENTOR_PRO_VERSION' ) || did_action( 'elementor_pro/init' );
	}

	/**
	 * Documents already imported for a given template slug, newest first.
	 *
	 * @param string $slug Template slug.
	 * @return WP_Post[]
	 */
	private function existing( $slug ) {
		$posts = get_posts(
			array(
				'post_type'        => array( 'page', 'elementor_library' ),
				'post_status'      => array( 'publish', 'draft', 'pending', 'private' ),
				'meta_key'         => self::MARK, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $slug,      // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'posts_per_page'   => 5,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'suppress_filters' => false,
			)
		);
		return is_array( $posts ) ? $posts : array();
	}

	/**
	 * Render the importer admin screen.
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		$notice   = isset( $_GET['ziteh_notice'] ) ? sanitize_key( wp_unslash( $_GET['ziteh_notice'] ) ) : '';
		$made     = isset( $_GET['ziteh_made'] ) ? sanitize_key( wp_unslash( $_GET['ziteh_made'] ) ) : '';
		$edit_url = isset( $_GET['ziteh_edit'] ) ? esc_url_raw( wp_unslash( $_GET['ziteh_edit'] ) ) : '';
		$view_url = isset( $_GET['ziteh_view'] ) ? esc_url_raw( wp_unslash( $_GET['ziteh_view'] ) ) : '';

		$templates = $this->templates();
		$has_pro   = $this->has_pro();
		?>
		<div class="wrap ziteh-tpl" dir="rtl">
			<h1><?php esc_html_e( 'قالب‌های آماده زیته', 'ziteh' ); ?></h1>
			<p class="ziteh-tpl__lead"><?php esc_html_e( 'هر کارت یک چیدمان کامل است. با یک کلیک ساخته می‌شود، ویجت‌ها سرجای خودشان قرار می‌گیرند و بعد می‌توانید در المنتور محتوا و تصویرها را عوض کنید. ساختن یک قالب هیچ برگه یا تنظیم موجودی را تغییر نمی‌دهد.', 'ziteh' ); ?></p>

			<?php if ( 'success' === $notice ) : ?>
				<div class="notice notice-success">
					<p>
						<?php
						$label = isset( $templates[ $made ]['label'] ) ? $templates[ $made ]['label'] : '';
						printf(
							/* translators: %s: template label */
							esc_html__( 'قالب «%s» ساخته شد.', 'ziteh' ),
							esc_html( $label )
						);
						?>
					</p>
					<p>
						<?php if ( $edit_url ) : ?>
							<a class="button button-primary" href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'ویرایش با المنتور', 'ziteh' ); ?></a>
						<?php endif; ?>
						<?php if ( $view_url ) : ?>
							<a class="button" href="<?php echo esc_url( $view_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'مشاهده', 'ziteh' ); ?></a>
						<?php endif; ?>
					</p>
					<?php if ( ! empty( $templates[ $made ]['after'] ) ) : ?>
						<p><strong><?php esc_html_e( 'قدم بعدی:', 'ziteh' ); ?></strong> <?php echo esc_html( $templates[ $made ]['after'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php elseif ( 'error' === $notice ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'ساخت قالب ناموفق بود: فایل قالب پیدا نشد یا محتوای معتبری نداشت.', 'ziteh' ); ?></p></div>
			<?php elseif ( 'pro' === $notice ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'این قالب به المنتور پرو نیاز دارد و ساخته نشد.', 'ziteh' ); ?></p></div>
			<?php endif; ?>

			<?php
			foreach ( array( 'page', 'library' ) as $kind ) :
				$group = array_filter(
					$templates,
					function ( $tpl ) use ( $kind ) {
						return $kind === $tpl['kind'];
					}
				);
				if ( ! $group ) {
					continue;
				}
				?>
				<h2 class="ziteh-tpl__group">
					<?php echo 'page' === $kind ? esc_html__( 'برگه‌ها', 'ziteh' ) : esc_html__( 'قالب‌های سراسری (سازنده قالب)', 'ziteh' ); ?>
				</h2>

				<?php if ( 'library' === $kind && ! $has_pro ) : ?>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'این سه قالب برای اینکه روی سایت اعمال شوند به «سازنده قالب» المنتور پرو نیاز دارند و بدون آن ساخته نمی‌شوند. اگر پرو ندارید، ویجت‌های تاپ‌بار، هدر و فوتر را مثل الان داخل خود برگه‌ها نگه دارید؛ قالب وردپرسی زیته هم بدون پرو هدر و فوتر پیش‌فرض خودش را نشان می‌دهد.', 'ziteh' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="ziteh-tpl__grid">
					<?php
					foreach ( $group as $slug => $tpl ) :
						$locked = ! empty( $tpl['pro'] ) && ! $has_pro;
						$made_before = $this->existing( $slug );
						?>
						<div class="ziteh-tpl__card<?php echo $locked ? ' is-locked' : ''; ?>">
							<h3><?php echo esc_html( $tpl['label'] ); ?></h3>
							<p class="ziteh-tpl__desc"><?php echo esc_html( $tpl['description'] ); ?></p>
							<p class="ziteh-tpl__widgets"><?php echo esc_html( $this->widget_summary( $slug ) ); ?></p>

							<?php if ( $made_before ) : ?>
								<p class="ziteh-tpl__made">
									<strong><?php esc_html_e( 'قبلاً ساخته شده:', 'ziteh' ); ?></strong>
									<?php foreach ( $made_before as $post ) : ?>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post->ID . '&action=elementor' ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
									<?php endforeach; ?>
								</p>
							<?php endif; ?>

							<?php if ( $locked ) : ?>
								<p class="ziteh-tpl__locked"><?php esc_html_e( 'نیازمند المنتور پرو', 'ziteh' ); ?></p>
							<?php else : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
									<input type="hidden" name="template" value="<?php echo esc_attr( $slug ); ?>">
									<?php wp_nonce_field( self::ACTION ); ?>
									<button type="submit" class="button button-primary">
										<?php
										echo $made_before
											? esc_html__( 'ساخت یک نسخه دیگر', 'ziteh' )
											: esc_html__( 'ساخت این قالب', 'ziteh' );
										?>
									</button>
								</form>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>

			<hr>
			<h2><?php esc_html_e( 'روش جایگزین: ایمپورت دستی', 'ziteh' ); ?></h2>
			<p><?php esc_html_e( 'در المنتور به مسیر «قالب‌ها ← قالب‌های ذخیره‌شده ← ایمپورت» بروید و هر کدام از فایل‌های زیر را بارگذاری کنید:', 'ziteh' ); ?></p>
			<ul class="ziteh-tpl__files">
				<?php foreach ( $templates as $slug => $tpl ) : ?>
					<li><code><?php echo esc_html( 'wp-content/plugins/ziteh-elementor/templates/ziteh-' . $slug . '.json' ); ?></code></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<style>
			.ziteh-tpl { max-width: 1080px; }
			.ziteh-tpl__lead { max-width: 70ch; font-size: 14px; }
			.ziteh-tpl__group { margin-top: 28px; }
			.ziteh-tpl__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
			.ziteh-tpl__card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 16px 18px; display: flex; flex-direction: column; gap: 8px; }
			.ziteh-tpl__card.is-locked { opacity: .72; }
			.ziteh-tpl__card h3 { margin: 0; font-size: 15px; }
			.ziteh-tpl__desc { margin: 0; color: #50575e; }
			.ziteh-tpl__widgets { margin: 0; color: #787c82; font-size: 12px; direction: ltr; text-align: right; overflow-wrap: anywhere; }
			.ziteh-tpl__made { margin: 0; font-size: 12px; }
			.ziteh-tpl__made a { margin-inline-start: 6px; }
			.ziteh-tpl__locked { margin: 0; font-size: 12px; color: #8a6d1d; }
			.ziteh-tpl__card form { margin-top: auto; padding-top: 6px; }
			.ziteh-tpl__files code { font-size: 12px; }
		</style>
		<?php
	}

	/**
	 * A short "which widgets are in here" line, read from the JSON itself so it
	 * can never drift away from what the file actually contains.
	 *
	 * @param string $slug Template slug.
	 * @return string
	 */
	private function widget_summary( $slug ) {
		$content = $this->load_template_content( $slug );
		$names   = array();

		$walk = function ( $elements ) use ( &$walk, &$names ) {
			foreach ( $elements as $element ) {
				if ( isset( $element['widgetType'] ) ) {
					$names[] = str_replace( 'ziteh-', '', $element['widgetType'] );
				}
				if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
					$walk( $element['elements'] );
				}
			}
		};
		$walk( $content );

		if ( ! $names ) {
			return '';
		}

		return sprintf(
			/* translators: 1: number of widgets, 2: widget name list */
			esc_html__( '%1$s ویجت: %2$s', 'ziteh' ),
			number_format_i18n( count( $names ) ),
			implode( ' · ', $names )
		);
	}

	/**
	 * Handle the importer form submission: create the document + Elementor data.
	 */
	public function handle_import() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'ziteh' ) );
		}
		check_admin_referer( self::ACTION );

		$slug      = isset( $_POST['template'] ) ? sanitize_key( wp_unslash( $_POST['template'] ) ) : '';
		$templates = $this->templates();

		if ( ! isset( $templates[ $slug ] ) ) {
			$this->redirect_back( 'error' );
		}

		$tpl = $templates[ $slug ];

		// A Theme Builder document without Pro would be created but could never
		// be assigned to anything, so refuse rather than leave dead weight.
		if ( ! empty( $tpl['pro'] ) && ! $this->has_pro() ) {
			$this->redirect_back( 'pro' );
		}

		$data = $this->load_template_content( $slug );
		if ( empty( $data ) ) {
			$this->redirect_back( 'error' );
		}

		$is_library = 'library' === $tpl['kind'];

		$post_id = wp_insert_post(
			array(
				'post_title'   => $tpl['post_title'],
				'post_status'  => 'publish',
				'post_type'    => $is_library ? 'elementor_library' : 'page',
				'post_content' => '',
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			$this->redirect_back( 'error' );
		}

		$type = $is_library ? $tpl['template_type'] : 'wp-page';

		// Elementor meta so the builder recognises the document.
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.5.0' );
		// _elementor_data must be a JSON string of the elements array, slashed.
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		update_post_meta( $post_id, self::MARK, $slug );

		if ( $is_library ) {
			// Elementor lists library documents by taxonomy, not by meta, so a
			// document with only the meta set would be invisible in the UI.
			wp_set_object_terms( $post_id, $type, 'elementor_library_type' );
		} else {
			update_post_meta( $post_id, '_wp_page_template', 'elementor_canvas' );
		}

		// Ask Elementor to regenerate the document CSS on next load.
		if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
			try {
				$css = \Elementor\Core\Files\CSS\Post::create( $post_id );
				$css->update();
			} catch ( \Exception $e ) {
				// Non-fatal: Elementor will regenerate CSS when the page is viewed.
				unset( $e );
			}
		}

		$extra = array(
			'ziteh_made' => $slug,
			'ziteh_edit' => rawurlencode( admin_url( 'post.php?post=' . $post_id . '&action=elementor' ) ),
		);

		// Library documents have no meaningful public permalink of their own.
		if ( ! $is_library ) {
			$extra['ziteh_view'] = rawurlencode( get_permalink( $post_id ) );
		}

		$this->redirect_back( 'success', $extra );
	}

	/**
	 * Read templates/ziteh-{slug}.json and return its `content` array.
	 *
	 * @param string $slug Template slug.
	 * @return array<int,mixed>
	 */
	private function load_template_content( $slug ) {
		$file = ZITEH_EL_PATH . 'templates/ziteh-' . $slug . '.json';
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
		// Guard against a truncated or hand-edited file producing an empty page.
		foreach ( $json['content'] as $element ) {
			if ( ! is_array( $element ) || empty( $element['elType'] ) ) {
				return array();
			}
		}
		return $json['content'];
	}

	/**
	 * Redirect back to the importer screen with a notice.
	 *
	 * @param string               $notice 'success' | 'error' | 'pro'.
	 * @param array<string,string> $extra  Extra query args.
	 */
	private function redirect_back( $notice, $extra = array() ) {
		$args = array_merge(
			array(
				'page'         => self::PAGE,
				'ziteh_notice' => $notice,
			),
			$extra
		);

		$base = 'tools.php' === $this->parent_slug() ? 'tools.php' : 'admin.php';

		wp_safe_redirect( add_query_arg( $args, admin_url( $base ) ) );
		exit;
	}
}
