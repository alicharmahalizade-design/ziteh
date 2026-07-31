<?php
/**
 * Contact page: reachable details, a working form and an optional map.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

/**
 * Class Ziteh_Contact_Widget.
 */
class Ziteh_Contact_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-contact';
	}

	public function get_title() {
		return esc_html__( 'زیته | تماس با ما', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-envelope';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'contact', 'form', 'تماس', 'فرم', 'پشتیبانی' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_intro', array( 'label' => esc_html__( 'معرفی', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'پیش‌عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'در دسترس شما هستیم', 'ziteh' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'تماس با زیته', 'ziteh' ), 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => esc_html__( 'توضیح', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => esc_html__( 'هر پرسشی درباره محصولات، سفارش یا مراقبت از پوست و مو دارید بنویسید. کارشناسان ما در ساعات کاری پاسخ می‌دهند.', 'ziteh' ) ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'DIV' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_channels', array( 'label' => esc_html__( 'راه‌های ارتباطی', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control(
			'use_global',
			array(
				'label'        => esc_html__( 'استفاده از اطلاعات تماس سراسری', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'به‌جای فهرست زیر، تلفن، ایمیل، نشانی و ساعات کاری را از «هسته زیته ← محتوای سراسری» می‌خواند. موارد خالی نمایش داده نمی‌شوند.', 'ziteh' ),
			)
		);
		$channel = new Repeater();
		$channel->add_control( 'icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-phone', 'library' => 'fa-solid' ) ) );
		$channel->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'تلفن پشتیبانی', 'ziteh' ), 'label_block' => true ) );
		$channel->add_control( 'value', array( 'label' => esc_html__( 'مقدار', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => '۰۲۱-۱۲۳۴۵۶۷۸', 'label_block' => true ) );
		$channel->add_control( 'note', array( 'label' => esc_html__( 'توضیح کوتاه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'شنبه تا چهارشنبه، ۹ تا ۱۷', 'ziteh' ), 'label_block' => true ) );
		$channel->add_control( 'link', array( 'label' => esc_html__( 'لینک', 'ziteh' ), 'type' => Controls_Manager::URL, 'placeholder' => 'tel:+982112345678' ) );
		$this->add_control(
			'channels',
			array(
				'label'       => esc_html__( 'آیتم‌ها', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $channel->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array( 'icon' => array( 'value' => 'fas fa-phone', 'library' => 'fa-solid' ), 'title' => esc_html__( 'تلفن پشتیبانی', 'ziteh' ), 'value' => '۰۲۱-۱۲۳۴۵۶۷۸', 'note' => esc_html__( 'شنبه تا چهارشنبه، ۹ تا ۱۷', 'ziteh' ) ),
					array( 'icon' => array( 'value' => 'fas fa-envelope', 'library' => 'fa-solid' ), 'title' => esc_html__( 'ایمیل', 'ziteh' ), 'value' => 'info@ziteh.com', 'note' => esc_html__( 'پاسخ حداکثر تا ۲۴ ساعت', 'ziteh' ) ),
					array( 'icon' => array( 'value' => 'fas fa-map-marker-alt', 'library' => 'fa-solid' ), 'title' => esc_html__( 'نشانی', 'ziteh' ), 'value' => esc_html__( 'تهران، خیابان نمونه، پلاک ۱۲', 'ziteh' ), 'note' => esc_html__( 'مراجعه حضوری با هماهنگی قبلی', 'ziteh' ) ),
					array( 'icon' => array( 'value' => 'fas fa-headset', 'library' => 'fa-solid' ), 'title' => esc_html__( 'پشتیبانی آنلاین', 'ziteh' ), 'value' => esc_html__( 'گفت‌وگوی زنده', 'ziteh' ), 'note' => esc_html__( 'همه روزه، ۹ تا ۲۱', 'ziteh' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_form', array( 'label' => esc_html__( 'فرم تماس', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_form', array( 'label' => esc_html__( 'نمایش فرم', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'form_title', array( 'label' => esc_html__( 'عنوان فرم', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'برای ما پیام بگذارید', 'ziteh' ), 'condition' => array( 'show_form' => 'yes' ) ) );
		$this->add_control( 'show_phone_field', array( 'label' => esc_html__( 'فیلد شماره تماس', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'show_form' => 'yes' ) ) );
		$this->add_control( 'show_subject_field', array( 'label' => esc_html__( 'فیلد موضوع', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'show_form' => 'yes' ) ) );
		$this->add_control( 'submit_text', array( 'label' => esc_html__( 'متن دکمه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ارسال پیام', 'ziteh' ), 'condition' => array( 'show_form' => 'yes' ) ) );
		$this->add_control( 'consent_text', array( 'label' => esc_html__( 'متن زیر دکمه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'اطلاعات شما فقط برای پاسخ به همین پیام استفاده می‌شود.', 'ziteh' ), 'label_block' => true, 'condition' => array( 'show_form' => 'yes' ) ) );
		$this->add_control(
			'recipient_note',
			array(
				'label'           => esc_html__( 'گیرنده پیام‌ها', 'ziteh' ),
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => sprintf(
					/* translators: %s: admin email address */
					esc_html__( 'پیام‌ها به %s ارسال می‌شوند. برای تغییر، از فیلتر ziteh_contact_recipient استفاده کنید. گیرنده عمداً در تنظیمات ویجت قابل تغییر نیست تا این نقطه به یک رله ایمیل باز تبدیل نشود.', 'ziteh' ),
					esc_html( get_option( 'admin_email' ) )
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				'condition'       => array( 'show_form' => 'yes' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_map', array( 'label' => esc_html__( 'نقشه', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_map', array( 'label' => esc_html__( 'نمایش نقشه', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'map_embed', array( 'label' => esc_html__( 'آدرس embed نقشه', 'ziteh' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://www.google.com/maps/embed?...', 'description' => esc_html__( 'فقط نشانی embed را بگذارید؛ نقشه با بارگذاری تنبل و بدون اسکریپت خارجی درج می‌شود.', 'ziteh' ), 'condition' => array( 'show_map' => 'yes' ) ) );
		$this->add_control( 'map_height', array( 'label' => esc_html__( 'ارتفاع نقشه', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 200, 'max' => 700 ) ), 'default' => array( 'size' => 340, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-map: {{SIZE}}{{UNIT}};' ), 'condition' => array( 'show_map' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 760, 'max' => 1680 ) ), 'default' => array( 'size' => 1240, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'form_width', array( 'label' => esc_html__( 'عرض ستون فرم', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 320, 'max' => 720 ) ), 'default' => array( 'size' => 520, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-form: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'فاصله ستون‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 90 ) ), 'default' => array( 'size' => 40, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'channel_columns', array( 'label' => esc_html__( 'ستون‌های راه ارتباطی', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '2', 'options' => array( '1' => '۱', '2' => '۲' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-cols: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#faf9f6', '--ziteh-ct-bg' ),
			'surface_color'    => array( 'سطح کارت', '#ffffff', '--ziteh-ct-surface' ),
			'primary_color'    => array( 'رنگ اصلی', '#6f7d5f', '--ziteh-ct-primary' ),
			'heading_color'    => array( 'رنگ عنوان', '#333830', '--ziteh-ct-heading' ),
			'text_color'       => array( 'رنگ متن', '#787b74', '--ziteh-ct-text' ),
			'border_color'     => array( 'رنگ خطوط', '#e9e6df', '--ziteh-ct-border' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی گوشه‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 18, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ct' => '--ziteh-ct-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'label' => esc_html__( 'سایه کارت‌ها', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ct__card, {{WRAPPER}} .ziteh-ct__form' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ct__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_typography', 'label' => esc_html__( 'متن', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ct__description' ) );
		$this->end_controls_section();
	}

	/**
	 * Contact channels assembled from the shop-wide settings.
	 *
	 * Only the entries the site owner actually filled in are returned, so an
	 * unset address does not leave an empty card on the page.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function global_channels() {
		if ( ! class_exists( 'Ziteh_Settings' ) ) {
			return array();
		}

		$hours = Ziteh_Settings::content( 'hours' );
		$map   = array(
			array( 'phone', __( 'تلفن پشتیبانی', 'ziteh' ), 'chat', 'tel:' ),
			array( 'email', __( 'ایمیل', 'ziteh' ), 'chat', 'mailto:' ),
			array( 'address', __( 'نشانی', 'ziteh' ), 'chat', '' ),
		);

		$channels = array();
		foreach ( $map as $row ) {
			$value = Ziteh_Settings::content( $row[0] );
			if ( '' === $value ) {
				continue;
			}

			$link = '';
			if ( $row[3] ) {
				// tel: needs the digits unspaced; mailto: takes the address as-is.
				$link = 'tel:' === $row[3]
					? 'tel:' . preg_replace( '/[^0-9+]/u', '', $value )
					: $row[3] . $value;
			}

			$channels[] = array(
				'icon'  => array( 'value' => '', 'library' => '' ),
				'title' => $row[1],
				'value' => $value,
				'note'  => $hours,
				'link'  => array( 'url' => $link, 'is_external' => '', 'nofollow' => '' ),
			);
		}

		return $channels;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$tag      = in_array( $settings['heading_tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? $settings['heading_tag'] : 'h1';
		$uid      = 'ziteh-ct-' . $this->get_id();
		$channels = 'yes' === $settings['use_global'] ? $this->global_channels() : (array) $settings['channels'];
		?>
		<section class="ziteh-ct" dir="rtl" data-ziteh-contact>
			<div class="ziteh-ct__container">
				<header class="ziteh-ct__head">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
						<span class="ziteh-ct__eyebrow"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $settings['eyebrow'] ); ?></span></span>
					<?php endif; ?>
					<<?php echo esc_attr( $tag ); ?> class="ziteh-ct__title"><?php echo esc_html( $settings['title'] ); ?></<?php echo esc_attr( $tag ); ?>>
					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<p class="ziteh-ct__description"><?php echo esc_html( $settings['description'] ); ?></p>
					<?php endif; ?>
				</header>

				<div class="ziteh-ct__layout<?php echo 'yes' === $settings['show_form'] ? '' : ' is-single'; ?>">
					<?php if ( ! empty( $channels ) ) : ?>
						<ul class="ziteh-ct__channels">
							<?php
							foreach ( $channels as $item ) :
								$has_link = ! empty( $item['link']['url'] );
								$tag_name = $has_link ? 'a' : 'div';
								?>
								<li>
									<<?php echo esc_attr( $tag_name ); ?> class="ziteh-ct__card"
										<?php if ( $has_link ) : ?>
											href="<?php echo esc_url( $item['link']['url'] ); ?>"
											<?php echo ! empty( $item['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : ''; ?>
										<?php endif; ?>>
										<span class="ziteh-ct__card-icon"><?php Ziteh_Icons::render_control( $item['icon'], 'chat' ); ?></span>
										<span class="ziteh-ct__card-body">
											<strong class="ziteh-ct__card-title"><?php echo esc_html( $item['title'] ); ?></strong>
											<span class="ziteh-ct__card-value"><?php echo esc_html( $item['value'] ); ?></span>
											<?php if ( ! empty( $item['note'] ) ) : ?><small><?php echo esc_html( $item['note'] ); ?></small><?php endif; ?>
										</span>
									</<?php echo esc_attr( $tag_name ); ?>>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( 'yes' === $settings['show_form'] ) : ?>
						<form class="ziteh-ct__form" data-ziteh-contact-form novalidate>
							<?php if ( ! empty( $settings['form_title'] ) ) : ?>
								<h3 class="ziteh-ct__form-title"><?php echo esc_html( $settings['form_title'] ); ?></h3>
							<?php endif; ?>

							<div class="ziteh-ct__field">
								<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'نام و نام خانوادگی', 'ziteh' ); ?><span aria-hidden="true">*</span></label>
								<input type="text" id="<?php echo esc_attr( $uid ); ?>-name" name="name" autocomplete="name" required>
								<em class="ziteh-ct__error" data-error-for="name" hidden></em>
							</div>

							<div class="ziteh-ct__row">
								<div class="ziteh-ct__field">
									<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'ایمیل', 'ziteh' ); ?><span aria-hidden="true">*</span></label>
									<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" autocomplete="email" dir="ltr" required>
									<em class="ziteh-ct__error" data-error-for="email" hidden></em>
								</div>

								<?php if ( 'yes' === $settings['show_phone_field'] ) : ?>
									<div class="ziteh-ct__field">
										<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'شماره تماس', 'ziteh' ); ?></label>
										<input type="tel" id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" autocomplete="tel" dir="ltr">
										<em class="ziteh-ct__error" data-error-for="phone" hidden></em>
									</div>
								<?php endif; ?>
							</div>

							<?php if ( 'yes' === $settings['show_subject_field'] ) : ?>
								<div class="ziteh-ct__field">
									<label for="<?php echo esc_attr( $uid ); ?>-subject"><?php esc_html_e( 'موضوع', 'ziteh' ); ?></label>
									<input type="text" id="<?php echo esc_attr( $uid ); ?>-subject" name="subject">
								</div>
							<?php endif; ?>

							<div class="ziteh-ct__field">
								<label for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'متن پیام', 'ziteh' ); ?><span aria-hidden="true">*</span></label>
								<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="5" required></textarea>
								<em class="ziteh-ct__error" data-error-for="message" hidden></em>
							</div>

							<?php
							// Honeypot. Hidden from sight and from assistive tech, but a bot
							// walking the DOM will happily fill it in.
							?>
							<div class="ziteh-ct__trap" aria-hidden="true">
								<label for="<?php echo esc_attr( $uid ); ?>-website"><?php esc_html_e( 'وب‌سایت', 'ziteh' ); ?></label>
								<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="ziteh_website" tabindex="-1" autocomplete="off">
							</div>

							<button class="ziteh-ct__submit" type="submit">
								<span class="ziteh-ct__submit-label"><?php echo esc_html( $settings['submit_text'] ); ?></span>
								<span class="ziteh-ct__spinner" aria-hidden="true"></span>
							</button>

							<?php if ( ! empty( $settings['consent_text'] ) ) : ?>
								<p class="ziteh-ct__consent"><?php echo esc_html( $settings['consent_text'] ); ?></p>
							<?php endif; ?>

							<p class="ziteh-ct__status" data-ziteh-contact-status role="status" aria-live="polite" hidden></p>
						</form>
					<?php endif; ?>
				</div>

				<?php
				$map = ! empty( $settings['map_embed']['url'] ) ? $settings['map_embed']['url'] : '';
				if ( ! $map && class_exists( 'Ziteh_Settings' ) ) {
					$map = Ziteh_Settings::content( 'map' );
				}
				?>
				<?php if ( 'yes' === $settings['show_map'] && $map ) : ?>
					<div class="ziteh-ct__map">
						<iframe
							src="<?php echo esc_url( $map ); ?>"
							title="<?php esc_attr_e( 'نقشه محل زیته', 'ziteh' ); ?>"
							loading="lazy"
							referrerpolicy="no-referrer-when-downgrade"
							allowfullscreen></iframe>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
