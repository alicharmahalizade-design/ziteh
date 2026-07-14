<?php
/**
 * Skin quiz widget — an interactive, multi-step consultation that asks a few
 * questions (skin type, concern, goal…) with a progress bar, then shows a
 * result screen with a recommended routine and a CTA. Fully client-side; each
 * question and result is editable from Elementor.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Quiz_Widget
 */
class Ziteh_Quiz_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-quiz';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | کوییز پوست', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_intro',
			array(
				'label' => esc_html__( 'معرفی', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'کوییز پوست: روتین مناسب خودت را پیدا کن', 'ziteh' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => esc_html__( 'زیرعنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => esc_html__( 'با چند سؤال کوتاه، بهترین محصولات و روتین متناسب با پوست تو را پیشنهاد می‌دهیم.', 'ziteh' ),
			)
		);

		$this->add_control(
			'start_text',
			array(
				'label'   => esc_html__( 'متن دکمه شروع', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'شروع کوییز', 'ziteh' ),
			)
		);

		$this->end_controls_section();

		// Questions.
		$this->start_controls_section(
			'section_questions',
			array(
				'label' => esc_html__( 'سؤالات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$q = new Repeater();
		$q->add_control(
			'question',
			array(
				'label'   => esc_html__( 'متن سؤال', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'سؤال', 'ziteh' ),
			)
		);
		$q->add_control(
			'answers',
			array(
				'label'       => esc_html__( 'گزینه‌ها (هر خط یک گزینه)', 'ziteh' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => "گزینه اول\nگزینه دوم\nگزینه سوم",
				'description' => esc_html__( 'هر خط یک گزینه.', 'ziteh' ),
			)
		);

		$this->add_control(
			'questions',
			array(
				'label'       => esc_html__( 'سؤالات', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $q->get_controls(),
				'title_field' => '{{{ question }}}',
				'default'     => array(
					array(
						'question' => esc_html__( 'نوع پوست تو چیه؟', 'ziteh' ),
						'answers'  => "خشک\nچرب\nمختلط\nنرمال\nحساس",
					),
					array(
						'question' => esc_html__( 'مهم‌ترین دغدغه‌ات چیه؟', 'ziteh' ),
						'answers'  => "خشکی و کم‌آبی\nجوش و منافذ باز\nلک و روشن‌سازی\nچین‌وچروک\nقرمزی و حساسیت",
					),
					array(
						'question' => esc_html__( 'چقدر برای روتین وقت داری؟', 'ziteh' ),
						'answers'  => "کم (۲ مرحله)\nمتوسط (۳ تا ۴ مرحله)\nزیاد (روتین کامل)",
					),
					array(
						'question' => esc_html__( 'رده سنی‌ات؟', 'ziteh' ),
						'answers'  => "زیر ۲۵\n۲۵ تا ۳۵\n۳۵ تا ۴۵\nبالای ۴۵",
					),
				),
			)
		);

		$this->end_controls_section();

		// Result.
		$this->start_controls_section(
			'section_result',
			array(
				'label' => esc_html__( 'صفحه نتیجه', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'result_title',
			array(
				'label'   => esc_html__( 'عنوان نتیجه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'روتین پیشنهادی تو آماده‌ست!', 'ziteh' ),
			)
		);

		$this->add_control(
			'result_text',
			array(
				'label'   => esc_html__( 'متن نتیجه', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => esc_html__( 'بر اساس پاسخ‌های تو، این محصولات و روتین بهترین گزینه برای پوستت هستند. می‌تونی همین حالا مشاهده و خرید کنی.', 'ziteh' ),
			)
		);

		$this->add_control(
			'result_button_text',
			array(
				'label'   => esc_html__( 'متن دکمه نتیجه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده محصولات پیشنهادی', 'ziteh' ),
			)
		);

		$this->add_control(
			'result_button_url',
			array(
				'label'         => esc_html__( 'لینک دکمه نتیجه', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'restart_text',
			array(
				'label'   => esc_html__( 'متن دکمه شروع مجدد', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'شروع دوباره', 'ziteh' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Split a textarea of answers into a clean array.
	 *
	 * @param string $raw Raw textarea value.
	 * @return string[]
	 */
	private function lines( $raw ) {
		$out = array();
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out;
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings   = $this->get_settings_for_display();
		$questions  = ! empty( $settings['questions'] ) ? $settings['questions'] : array();
		$total      = count( $questions );
		$result_url = ! empty( $settings['result_button_url']['url'] ) ? $settings['result_button_url']['url'] : '#';
		?>
		<section class="ziteh-quiz ziteh-reveal" data-ziteh-quiz>
			<div class="ziteh-container">
				<div class="ziteh-quiz__box">

					<!-- Intro -->
					<div class="ziteh-quiz__screen ziteh-quiz__intro is-active" data-quiz-screen="intro">
						<span class="ziteh-quiz__icon"><?php echo $this->get_icon_svg( 'droplet' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h2 class="ziteh-quiz__title"><?php echo esc_html( $settings['title'] ); ?></h2>
						<p class="ziteh-quiz__subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
						<button class="ziteh-btn ziteh-btn--primary" type="button" data-quiz-start>
							<?php echo esc_html( $settings['start_text'] ); ?>
							<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</button>
					</div>

					<!-- Questions -->
					<div class="ziteh-quiz__screen ziteh-quiz__questions" data-quiz-screen="questions" data-total="<?php echo esc_attr( $total ); ?>">
						<div class="ziteh-quiz__progress">
							<div class="ziteh-quiz__progress-bar" data-quiz-progress></div>
						</div>
						<div class="ziteh-quiz__step-meta">
							<span data-quiz-current>1</span> / <span><?php echo esc_html( $total ); ?></span>
						</div>

						<?php foreach ( $questions as $i => $item ) : ?>
							<div class="ziteh-quiz__step<?php echo 0 === $i ? ' is-active' : ''; ?>" data-quiz-step="<?php echo esc_attr( $i ); ?>">
								<h3 class="ziteh-quiz__question"><?php echo esc_html( $item['question'] ); ?></h3>
								<div class="ziteh-quiz__answers">
									<?php foreach ( $this->lines( $item['answers'] ) as $answer ) : ?>
										<button class="ziteh-quiz__answer" type="button" data-quiz-answer><?php echo esc_html( $answer ); ?></button>
									<?php endforeach; ?>
								</div>
								<button class="ziteh-quiz__back" type="button" data-quiz-back hidden>
									<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php esc_html_e( 'قبلی', 'ziteh' ); ?>
								</button>
							</div>
						<?php endforeach; ?>
					</div>

					<!-- Result -->
					<div class="ziteh-quiz__screen ziteh-quiz__result" data-quiz-screen="result">
						<span class="ziteh-quiz__icon ziteh-quiz__icon--ok"><?php echo $this->get_icon_svg( 'leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h2 class="ziteh-quiz__title"><?php echo esc_html( $settings['result_title'] ); ?></h2>
						<p class="ziteh-quiz__subtitle"><?php echo esc_html( $settings['result_text'] ); ?></p>
						<ul class="ziteh-quiz__summary" data-quiz-summary></ul>
						<div class="ziteh-quiz__result-actions">
							<a class="ziteh-btn ziteh-btn--primary" href="<?php echo esc_url( $result_url ); ?>">
								<?php echo esc_html( $settings['result_button_text'] ); ?>
							</a>
							<button class="ziteh-btn ziteh-btn--outline" type="button" data-quiz-restart>
								<?php echo esc_html( $settings['restart_text'] ); ?>
							</button>
						</div>
					</div>

				</div>
			</div>
		</section>
		<?php
	}
}
