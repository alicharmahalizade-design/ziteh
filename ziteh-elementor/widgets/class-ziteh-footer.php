<?php
/**
 * Footer widget — the multi-column site footer: a brand column with the logo,
 * tagline and social icons on the right, several link columns, a contact-info
 * column, and a thin copyright bar beneath.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Footer_Widget
 */
class Ziteh_Footer_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-footer';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | فوتر', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-footer';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		// Brand column.
		$this->start_controls_section(
			'section_brand',
			array(
				'label' => esc_html__( 'برند', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'logo_text',
			array(
				'label'   => esc_html__( 'نام برند', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'زیته', 'ziteh' ),
			)
		);

		$this->add_control(
			'tagline',
			array(
				'label'   => esc_html__( 'شعار', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => esc_html__( 'جوانه‌ای برای مراقبت از خودت', 'ziteh' ),
			)
		);

		$social_rep = new Repeater();
		$social_rep->add_control(
			'network',
			array(
				'label'   => esc_html__( 'شبکه', 'ziteh' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'instagram',
				'options' => array(
					'instagram' => 'Instagram',
					'phone'     => esc_html__( 'تلفن', 'ziteh' ),
				),
			)
		);
		$social_rep->add_control(
			'url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => true,
			)
		);

		$this->add_control(
			'socials',
			array(
				'label'       => esc_html__( 'شبکه‌های اجتماعی', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $social_rep->get_controls(),
				'title_field' => '{{{ network }}}',
				'default'     => array(
					array( 'network' => 'instagram' ),
					array( 'network' => 'phone' ),
				),
			)
		);

		$this->end_controls_section();

		// Link columns.
		$this->start_controls_section(
			'section_columns',
			array(
				'label' => esc_html__( 'ستون‌های لینک', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$link_rep = new Repeater();
		$link_rep->add_control(
			'col_title',
			array(
				'label'   => esc_html__( 'عنوان ستون', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'عنوان', 'ziteh' ),
			)
		);
		$link_rep->add_control(
			'links',
			array(
				'label'       => esc_html__( 'لینک‌ها (هر خط یک لینک)', 'ziteh' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 5,
				'description' => esc_html__( 'هر خط یک آیتم. برای تعیین آدرس از قالب «عنوان | https://...» استفاده کنید.', 'ziteh' ),
				'default'     => "لینک اول\nلینک دوم\nلینک سوم",
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'       => esc_html__( 'ستون‌ها', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $link_rep->get_controls(),
				'title_field' => '{{{ col_title }}}',
				'default'     => array(
					array(
						'col_title' => esc_html__( 'خدمات مشتریان', 'ziteh' ),
						'links'     => "پیشنهاد متمایان\nحریم خصوصی\nشرایط و قوانین\nرویه بازگشت کالا",
					),
					array(
						'col_title' => esc_html__( 'دسترسی سریع', 'ziteh' ),
						'links'     => "فروشگاه\nمجله\nدرباره ما\nتماس با ما",
					),
					array(
						'col_title' => esc_html__( 'راهنمای خرید', 'ziteh' ),
						'links'     => "روش‌های پرداخت\nنحوه ارسال\nپیگیری سفارش\nپرسش‌های متداول",
					),
				),
			)
		);

		$this->end_controls_section();

		// Contact column.
		$this->start_controls_section(
			'section_contact',
			array(
				'label' => esc_html__( 'اطلاعات تماس', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'contact_title',
			array(
				'label'   => esc_html__( 'عنوان ستون', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'اطلاعات تماس', 'ziteh' ),
			)
		);

		$this->add_control(
			'phone',
			array(
				'label'   => esc_html__( 'تلفن', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '۰۹۱۲ ۱۲۳ ۴۵۶۷', 'ziteh' ),
			)
		);

		$this->add_control(
			'email',
			array(
				'label'   => esc_html__( 'ایمیل', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'info@ziteh.com',
			)
		);

		$this->add_control(
			'hours',
			array(
				'label'   => esc_html__( 'ساعات پاسخگویی', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'شنبه تا چهارشنبه ۹ تا ۱۶', 'ziteh' ),
			)
		);

		$this->end_controls_section();

		// Bottom bar.
		$this->start_controls_section(
			'section_bottom',
			array(
				'label' => esc_html__( 'نوار پایین', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'copyright',
			array(
				'label'   => esc_html__( 'متن کپی‌رایت', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'تمامی حقوق مادی و معنوی این فروشگاه اینترنتی متعلق به زیته است.', 'ziteh' ),
			)
		);

		$this->end_controls_section();

		// Style.
		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'استایل', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-footer' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-footer' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Parse a multi-line "title | url" list into an array of link rows.
	 *
	 * @param string $raw Raw textarea content.
	 * @return array<int,array{label:string,url:string}>
	 */
	private function parse_links( $raw ) {
		$out   = array();
		$lines = preg_split( '/\r\n|\r|\n/', (string) $raw );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			if ( false !== strpos( $line, '|' ) ) {
				list( $label, $url ) = array_map( 'trim', explode( '|', $line, 2 ) );
			} else {
				$label = $line;
				$url   = '#';
			}
			$out[] = array(
				'label' => $label,
				'url'   => $url ? $url : '#',
			);
		}
		return $out;
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<footer class="ziteh-footer">
			<div class="ziteh-container ziteh-footer__inner">

				<?php foreach ( $settings['columns'] as $col ) : ?>
					<div class="ziteh-footer__col">
						<h3 class="ziteh-footer__col-title"><?php echo esc_html( $col['col_title'] ); ?></h3>
						<ul class="ziteh-footer__links">
							<?php foreach ( $this->parse_links( $col['links'] ) as $link ) : ?>
								<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>

				<div class="ziteh-footer__col ziteh-footer__col--contact">
					<h3 class="ziteh-footer__col-title"><?php echo esc_html( $settings['contact_title'] ); ?></h3>
					<ul class="ziteh-footer__contact">
						<?php if ( ! empty( $settings['phone'] ) ) : ?>
							<li><?php echo $this->get_icon_svg( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['phone'] ); ?></span></li>
						<?php endif; ?>
						<?php if ( ! empty( $settings['email'] ) ) : ?>
							<li><?php echo $this->get_icon_svg( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['email'] ); ?></span></li>
						<?php endif; ?>
						<?php if ( ! empty( $settings['hours'] ) ) : ?>
							<li><?php echo $this->get_icon_svg( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $settings['hours'] ); ?></span></li>
						<?php endif; ?>
					</ul>
				</div>

				<div class="ziteh-footer__col ziteh-footer__col--brand">
					<div class="ziteh-footer__logo"><?php echo esc_html( $settings['logo_text'] ); ?></div>
					<p class="ziteh-footer__tagline"><?php echo esc_html( $settings['tagline'] ); ?></p>
					<div class="ziteh-footer__socials">
						<?php foreach ( $settings['socials'] as $social ) : ?>
							<?php $url = ! empty( $social['url']['url'] ) ? $social['url']['url'] : '#'; ?>
							<a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $social['network'] ); ?>">
								<?php echo $this->get_icon_svg( $social['network'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>

			</div>

			<?php if ( ! empty( $settings['copyright'] ) ) : ?>
				<div class="ziteh-footer__bottom">
					<div class="ziteh-container">
						<span><?php echo esc_html( $settings['copyright'] ); ?></span>
					</div>
				</div>
			<?php endif; ?>
		</footer>
		<?php
	}
}
