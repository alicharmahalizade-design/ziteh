<?php
/**
 * Routine widget — "روتین مراقبت از پوست": a section title, a صبح/شب (day/night)
 * pill toggle on the right, a horizontal row of numbered steps (icon + title +
 * short text) and a "راهنمای کامل روتین" link.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Routine_Widget
 */
class Ziteh_Routine_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-routine';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | روتین پوست', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-time-line';
	}

	/**
	 * Build the repeater used for both the day and night step lists.
	 *
	 * @return Repeater
	 */
	private function get_steps_repeater() {
		$repeater = new Repeater();

		$repeater->add_control(
			'step_title',
			array(
				'label'   => esc_html__( 'عنوان مرحله', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مرحله', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'step_text',
			array(
				'label'   => esc_html__( 'توضیح کوتاه', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => esc_html__( 'توضیح این مرحله را بنویسید.', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'step_icon',
			array(
				'label'   => esc_html__( 'آیکون', 'ziteh' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'droplet',
				'options' => array(
					'droplet' => esc_html__( 'قطره', 'ziteh' ),
					'spray'   => esc_html__( 'اسپری', 'ziteh' ),
					'leaf'    => esc_html__( 'برگ', 'ziteh' ),
					'shield'  => esc_html__( 'سپر (ضدآفتاب)', 'ziteh' ),
					'brush'   => esc_html__( 'قلم‌مو', 'ziteh' ),
					'sun'     => esc_html__( 'خورشید', 'ziteh' ),
					'moon'    => esc_html__( 'ماه', 'ziteh' ),
				),
			)
		);

		return $repeater;
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_head',
			array(
				'label' => esc_html__( 'سربخش', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان بخش', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'روتین مراقبت از پوست', 'ziteh' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => esc_html__( 'زیرعنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'پوستی سالم، با یک روتین ساده و مداوم', 'ziteh' ),
			)
		);

		$this->add_control(
			'day_label',
			array(
				'label'   => esc_html__( 'برچسب صبح', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'صبح', 'ziteh' ),
			)
		);

		$this->add_control(
			'night_label',
			array(
				'label'   => esc_html__( 'برچسب شب', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'شب', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن لینک راهنما', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'راهنمای کامل روتین', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'         => esc_html__( 'لینک راهنما', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->end_controls_section();

		// Day steps.
		$this->start_controls_section(
			'section_day',
			array(
				'label' => esc_html__( 'مراحل صبح', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'day_steps',
			array(
				'label'       => esc_html__( 'مراحل', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $this->get_steps_repeater()->get_controls(),
				'title_field' => '{{{ step_title }}}',
				'default'     => array(
					array(
						'step_title' => esc_html__( 'شست‌وشو', 'ziteh' ),
						'step_text'  => esc_html__( 'پوست را تمیز و آماده کن.', 'ziteh' ),
						'step_icon'  => 'droplet',
					),
					array(
						'step_title' => esc_html__( 'تونر', 'ziteh' ),
						'step_text'  => esc_html__( 'تعادل رطوبت پوست.', 'ziteh' ),
						'step_icon'  => 'spray',
					),
					array(
						'step_title' => esc_html__( 'سرم', 'ziteh' ),
						'step_text'  => esc_html__( 'مراقبت تخصصی.', 'ziteh' ),
						'step_icon'  => 'leaf',
					),
					array(
						'step_title' => esc_html__( 'مرطوب‌کننده', 'ziteh' ),
						'step_text'  => esc_html__( 'آب‌رسانی و حفظ رطوبت.', 'ziteh' ),
						'step_icon'  => 'brush',
					),
					array(
						'step_title' => esc_html__( 'ضد آفتاب', 'ziteh' ),
						'step_text'  => esc_html__( 'پوست را در برابر UV محافظت کن.', 'ziteh' ),
						'step_icon'  => 'shield',
					),
				),
			)
		);

		$this->end_controls_section();

		// Night steps.
		$this->start_controls_section(
			'section_night',
			array(
				'label' => esc_html__( 'مراحل شب', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'night_steps',
			array(
				'label'       => esc_html__( 'مراحل', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $this->get_steps_repeater()->get_controls(),
				'title_field' => '{{{ step_title }}}',
				'default'     => array(
					array(
						'step_title' => esc_html__( 'پاک‌سازی', 'ziteh' ),
						'step_text'  => esc_html__( 'آرایش و آلودگی را پاک کن.', 'ziteh' ),
						'step_icon'  => 'droplet',
					),
					array(
						'step_title' => esc_html__( 'تونر', 'ziteh' ),
						'step_text'  => esc_html__( 'تعادل رطوبت پوست.', 'ziteh' ),
						'step_icon'  => 'spray',
					),
					array(
						'step_title' => esc_html__( 'سرم شب', 'ziteh' ),
						'step_text'  => esc_html__( 'ترمیم و بازسازی شبانه.', 'ziteh' ),
						'step_icon'  => 'leaf',
					),
					array(
						'step_title' => esc_html__( 'کرم شب', 'ziteh' ),
						'step_text'  => esc_html__( 'تغذیه عمیق پوست.', 'ziteh' ),
						'step_icon'  => 'moon',
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render a single tab pane of steps.
	 *
	 * @param array  $steps Repeater rows.
	 * @param string $mode  'day' | 'night'.
	 * @param bool   $active Whether this pane is initially visible.
	 */
	private function render_steps( $steps, $mode, $active ) {
		$classes = 'ziteh-routine__steps' . ( $active ? ' is-active' : '' );
		?>
		<ol class="<?php echo esc_attr( $classes ); ?>" data-ziteh-pane="<?php echo esc_attr( $mode ); ?>">
			<?php
			$i = 0;
			foreach ( $steps as $step ) :
				$i++;
				?>
				<li class="ziteh-step">
					<span class="ziteh-step__num"><?php echo esc_html( $i ); ?></span>
					<span class="ziteh-step__icon">
						<?php echo $this->get_icon_svg( $step['step_icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
					<span class="ziteh-step__title"><?php echo esc_html( $step['step_title'] ); ?></span>
					<span class="ziteh-step__text"><?php echo esc_html( $step['step_text'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ol>
		<?php
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$btn_url  = ! empty( $settings['button_url']['url'] ) ? $settings['button_url']['url'] : '#';
		?>
		<section class="ziteh-routine">
			<div class="ziteh-container">

				<div class="ziteh-routine__head">
					<div class="ziteh-routine__heading">
						<h2 class="ziteh-section-title ziteh-section-title--start">
							<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['title'] ); ?>
						</h2>
						<p class="ziteh-routine__subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
					</div>

					<div class="ziteh-routine__toggle" data-ziteh-routine>
						<button class="ziteh-routine__toggle-btn is-active" type="button" data-ziteh-tab="day">
							<?php echo $this->get_icon_svg( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['day_label'] ); ?>
						</button>
						<button class="ziteh-routine__toggle-btn" type="button" data-ziteh-tab="night">
							<?php echo $this->get_icon_svg( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['night_label'] ); ?>
						</button>
					</div>
				</div>

				<div class="ziteh-routine__body">
					<?php
					$this->render_steps( $settings['day_steps'], 'day', true );
					$this->render_steps( $settings['night_steps'], 'night', false );
					?>
				</div>

				<?php if ( ! empty( $settings['button_text'] ) ) : ?>
					<div class="ziteh-routine__foot">
						<a class="ziteh-link" href="<?php echo esc_url( $btn_url ); ?>">
							<?php echo esc_html( $settings['button_text'] ); ?>
							<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					</div>
				<?php endif; ?>

			</div>
		</section>
		<?php
	}
}
