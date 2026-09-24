<?php
/**
 * Base class for every Ziteh widget.
 *
 * - wraps output in <div class="zt-w"> (scope of the design-system resets)
 * - tiny DSL for content controls (ctl / repeater)
 * - declarative style generator: every element of every widget gets a full
 *   set of style controls (typography, colours, background, border, radius,
 *   shadow, spacing, size, alignment, visibility…)
 * - shared data sources (products / categories / posts) with manual fallback
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Class ZT_Widget_Base
 */
abstract class ZT_Widget_Base extends Widget_Base {

	/**
	 * Category group (global|home|product|cart|checkout|tracking|account|guide|blog|shop).
	 *
	 * @var string
	 */
	protected $zt_group = 'global';

	/**
	 * Panel icon.
	 *
	 * @var string
	 */
	protected $zt_icon = 'eicon-star-o';

	/**
	 * Categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'zt-' . $this->zt_group );
	}

	/**
	 * Icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return $this->zt_icon . ' zt-el-icon';
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'ziteh', 'zite', 'زیته', $this->zt_group );
	}

	/**
	 * Style deps.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'zt-globals', 'zt-core', 'zt-extra', 'zt-icons' );
	}

	/**
	 * Script deps.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'zt-app' );
	}

	/**
	 * Help link.
	 *
	 * @return string
	 */
	public function get_custom_help_url() {
		return admin_url( 'admin.php?page=ziteh-core&tab=help' );
	}

	/**
	 * Render wrapper.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<div class="zt-w zt-w--' . esc_attr( str_replace( 'zt-', '', $this->get_name() ) ) . '">';
		$this->zt_render( $s );
		echo '</div>';
	}

	/**
	 * Widget output.
	 *
	 * @param array $s Settings.
	 */
	abstract protected function zt_render( $s );

	/* =====================================================================
	 * Control DSL
	 * =================================================================== */

	/**
	 * Map of short type names.
	 *
	 * @param string $t Type.
	 * @return string
	 */
	protected static function ctype( $t ) {
		$map = array(
			'text'     => Controls_Manager::TEXT,
			'textarea' => Controls_Manager::TEXTAREA,
			'wysiwyg'  => Controls_Manager::WYSIWYG,
			'url'      => Controls_Manager::URL,
			'media'    => Controls_Manager::MEDIA,
			'gallery'  => Controls_Manager::GALLERY,
			'icon'     => Controls_Manager::ICONS,
			'switch'   => Controls_Manager::SWITCHER,
			'select'   => Controls_Manager::SELECT,
			'select2'  => Controls_Manager::SELECT2,
			'number'   => Controls_Manager::NUMBER,
			'color'    => Controls_Manager::COLOR,
			'slider'   => Controls_Manager::SLIDER,
			'choose'   => Controls_Manager::CHOOSE,
			'heading'  => Controls_Manager::HEADING,
			'hidden'   => Controls_Manager::HIDDEN,
			'date'     => Controls_Manager::DATE_TIME,
			'notice'   => Controls_Manager::RAW_HTML,
			'code'     => Controls_Manager::CODE,
		);
		return isset( $map[ $t ] ) ? $map[ $t ] : $t;
	}

	/**
	 * Build a control args array.
	 *
	 * @param string $type    Type.
	 * @param string $label   Label.
	 * @param mixed  $default Default.
	 * @param array  $extra   Extra args.
	 * @return array
	 */
	protected static function cargs( $type, $label, $default = null, $extra = array() ) {
		$args = array(
			'label' => $label,
			'type'  => self::ctype( $type ),
		);
		if ( null !== $default ) {
			$args['default'] = $default;
		}
		switch ( $type ) {
			case 'text':
			case 'textarea':
			case 'wysiwyg':
				$args['dynamic']     = array( 'active' => true );
				$args['label_block'] = 'text' !== $type || ( isset( $extra['label_block'] ) ? $extra['label_block'] : true );
				break;
			case 'url':
				$args['dynamic']     = array( 'active' => true );
				$args['placeholder'] = '{{shop}} یا https://...';
				if ( is_string( $default ) ) {
					$args['default'] = array( 'url' => $default );
				}
				break;
			case 'media':
				$args['dynamic'] = array( 'active' => true );
				if ( is_string( $default ) ) {
					$args['default'] = '' === $default ? array( 'url' => '' ) : zt_media_default( $default );
				}
				break;
			case 'icon':
				if ( is_string( $default ) ) {
					$args['default'] = zt_icon_default( $default );
				}
				$args['skin'] = 'inline';
				break;
			case 'switch':
				$args['return_value'] = 'yes';
				$args['label_on']     = 'بله';
				$args['label_off']    = 'خیر';
				if ( true === $default ) {
					$args['default'] = 'yes';
				} elseif ( false === $default ) {
					$args['default'] = '';
				}
				break;
			case 'notice':
				$args['raw']             = $default;
				$args['content_classes'] = 'elementor-panel-alert elementor-panel-alert-info';
				unset( $args['default'] );
				break;
			case 'heading':
				$args['separator'] = 'before';
				unset( $args['default'] );
				break;
		}
		return array_merge( $args, $extra );
	}

	/**
	 * Add a control.
	 *
	 * @param string $id      Id.
	 * @param string $type    Type.
	 * @param string $label   Label.
	 * @param mixed  $default Default.
	 * @param array  $extra   Extra.
	 */
	protected function ctl( $id, $type, $label, $default = null, $extra = array() ) {
		$this->add_control( $id, self::cargs( $type, $label, $default, $extra ) );
	}

	/**
	 * Start a content section.
	 *
	 * @param string $id    Id.
	 * @param string $label Label.
	 * @param array  $extra Extra.
	 */
	protected function section( $id, $label, $extra = array() ) {
		$this->start_controls_section( $id, array_merge( array( 'label' => $label ), $extra ) );
	}

	/**
	 * Add a repeater.
	 *
	 * @param string $id       Id.
	 * @param string $label    Label.
	 * @param array  $fields   [ [id, type, label, default, extra], ... ].
	 * @param array  $defaults Default rows.
	 * @param string $title    Title field template.
	 * @param array  $extra    Extra args.
	 */
	protected function repeater( $id, $label, $fields, $defaults, $title = '', $extra = array() ) {
		$r = new Repeater();
		foreach ( $fields as $f ) {
			$r->add_control( $f[0], self::cargs( $f[1], $f[2], isset( $f[3] ) ? $f[3] : null, isset( $f[4] ) ? $f[4] : array() ) );
		}
		// Normalise repeater defaults for url/media/icon types.
		$types = array();
		foreach ( $fields as $f ) {
			$types[ $f[0] ] = $f[1];
		}
		foreach ( $defaults as &$row ) {
			foreach ( $row as $k => $v ) {
				if ( ! isset( $types[ $k ] ) ) {
					continue;
				}
				if ( 'url' === $types[ $k ] && is_string( $v ) ) {
					$row[ $k ] = array( 'url' => $v );
				} elseif ( 'media' === $types[ $k ] && is_string( $v ) ) {
					$row[ $k ] = '' === $v ? array( 'url' => '' ) : zt_media_default( $v );
				} elseif ( 'icon' === $types[ $k ] && is_string( $v ) ) {
					$row[ $k ] = zt_icon_default( $v );
				} elseif ( 'switch' === $types[ $k ] && is_bool( $v ) ) {
					$row[ $k ] = $v ? 'yes' : '';
				}
			}
		}
		unset( $row );
		$this->add_control(
			$id,
			array_merge(
				array(
					'label'       => $label,
					'type'        => Controls_Manager::REPEATER,
					'fields'      => $r->get_controls(),
					'default'     => $defaults,
					'title_field' => $title ? $title : '{{{ ' . $fields[0][0] . ' }}}',
				),
				$extra
			)
		);
	}

	/* =====================================================================
	 * Section wrapper (for full-width page sections)
	 * =================================================================== */

	/**
	 * Controls for the <section> wrapper.
	 *
	 * @param string $bg        Default background (white|cream|paper|none|custom).
	 * @param string $container Default container (wide|narrow|none).
	 * @param string $custom    Default custom colour.
	 */
	protected function section_wrap_controls( $bg = 'white', $container = 'wide', $custom = '' ) {
		$this->section( 'zt_wrap', 'بخش (سکشن)' );
		$this->ctl(
			'wrap_bg',
			'select',
			'پس‌زمینه بخش',
			$custom ? 'custom' : $bg,
			array(
				'options' => array(
					'white'  => 'سفید',
					'cream'  => 'کرم',
					'paper'  => 'کاغذی (پس‌زمینه صفحه)',
					'none'   => 'بدون پس‌زمینه',
					'custom' => 'رنگ دلخواه',
				),
			)
		);
		$this->ctl(
			'wrap_bg_color',
			'color',
			'رنگ دلخواه',
			$custom,
			array(
				'condition' => array( 'wrap_bg' => 'custom' ),
				'selectors' => array( '{{WRAPPER}} .zt-section' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->ctl(
			'wrap_container',
			'select',
			'عرض محتوا',
			$container,
			array(
				'options' => array(
					'wide'   => 'کانتینر اصلی (۱۴۴۰)',
					'narrow' => 'کانتینر باریک (۱۲۴۰)',
					'none'   => 'تمام عرض',
				),
			)
		);
		foreach ( array( 'wrap_pt' => array( 'فاصله بالای بخش', 'padding-top' ), 'wrap_pb' => array( 'فاصله پایین بخش', 'padding-bottom' ) ) as $cid => $c ) {
			$this->add_responsive_control(
				$cid,
				array(
					'label'       => $c[0],
					'type'        => Controls_Manager::SLIDER,
					'size_units'  => array( 'px', 'em', 'vh' ),
					'range'       => array( 'px' => array( 'min' => 0, 'max' => 240 ) ),
					'selectors'   => array( '{{WRAPPER}} .zt-section' => $c[1] . ': {{SIZE}}{{UNIT}};' ),
					'description' => 'wrap_pb' === $cid ? 'خالی = مطابق طرح (۶۴ دسکتاپ / ۴۲ تبلت / ۳۴ موبایل)' : '',
				)
			);
		}
		$this->ctl( 'wrap_reveal', 'switch', 'انیمیشن ظاهر شدن هنگام اسکرول', true );
		$this->ctl( 'wrap_anchor', 'text', 'شناسه (anchor) بخش', '', array( 'label_block' => false ) );
		$this->end_controls_section();
	}

	/**
	 * Open section + container.
	 *
	 * @param array  $s     Settings.
	 * @param string $extra Extra section classes.
	 * @param string $attrs Extra attributes.
	 */
	protected function section_open( $s, $extra = '', $attrs = '' ) {
		$bg  = isset( $s['wrap_bg'] ) ? $s['wrap_bg'] : 'white';
		$cls = 'zt-section';
		if ( 'white' === $bg ) {
			$cls .= ' zt-section--white';
		} elseif ( 'cream' === $bg ) {
			$cls .= ' zt-section--cream';
		} elseif ( 'paper' === $bg ) {
			$cls .= ' zt-section--paper';
		}
		if ( $extra ) {
			$cls .= ' ' . $extra;
		}
		$id = ! empty( $s['wrap_anchor'] ) ? ' id="' . esc_attr( $s['wrap_anchor'] ) . '"' : '';
		echo '<section class="' . esc_attr( $cls ) . '"' . $id . $attrs . '>'; // phpcs:ignore
		$c = isset( $s['wrap_container'] ) ? $s['wrap_container'] : 'wide';
		if ( 'none' !== $c ) {
			echo '<div class="zt-container' . ( 'narrow' === $c ? ' zt-container--narrow' : '' ) . '">';
		}
	}

	/**
	 * Close section.
	 *
	 * @param array $s Settings.
	 */
	protected function section_close( $s ) {
		$c = isset( $s['wrap_container'] ) ? $s['wrap_container'] : 'wide';
		if ( 'none' !== $c ) {
			echo '</div>';
		}
		echo '</section>';
	}

	/**
	 * "reveal" class when enabled.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	protected function reveal( $s ) {
		return ( ! isset( $s['wrap_reveal'] ) || 'yes' === $s['wrap_reveal'] ) ? ' zt-reveal' : '';
	}

	/**
	 * Section heading (.sec-head) controls.
	 *
	 * @param string $title    Default title.
	 * @param array  $o        Options: center, sub, link_text, link_url, icon.
	 */
	protected function heading_controls( $title, $o = array() ) {
		$o = wp_parse_args(
			$o,
			array(
				'center'    => false,
				'sub'       => '',
				'link_text' => '',
				'link_url'  => '#',
				'icon'      => 'leaf',
				'show'      => true,
				'mb'        => '',
			)
		);
		$this->ctl( 'head_show', 'switch', 'نمایش عنوان بخش', $o['show'] );
		$this->ctl( 'head_title', 'text', 'عنوان', $title, array( 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_icon', 'icon', 'آیکون عنوان', $o['icon'], array( 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_tag', 'select', 'تگ HTML', 'h2', array( 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ), 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_center', 'switch', 'عنوان وسط‌چین', $o['center'], array( 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_sub', 'text', 'زیرعنوان', $o['sub'], array( 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_link_text', 'text', 'متن لینک «مشاهده همه»', $o['link_text'], array( 'condition' => array( 'head_show' => 'yes' ) ) );
		$this->ctl( 'head_link', 'url', 'لینک', $o['link_url'], array( 'condition' => array( 'head_show' => 'yes' ) ) );
		if ( '' !== $o['mb'] ) {
			$this->ctl( 'head_mb', 'hidden', '', $o['mb'] );
		}
	}

	/**
	 * Render .sec-head.
	 *
	 * @param array $s Settings.
	 */
	protected function heading_render( $s ) {
		if ( empty( $s['head_show'] ) ) {
			return;
		}
		$center = ! empty( $s['head_center'] );
		$tag    = in_array( $s['head_tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? $s['head_tag'] : 'h2';
		$style  = ! empty( $s['head_mb'] ) ? ' style="margin-bottom:' . esc_attr( $s['head_mb'] ) . '"' : '';
		echo '<div class="zt-sec-head' . ( $center ? ' zt-sec-head--center' : '' ) . '"' . $style . '>'; // phpcs:ignore
		echo '<' . $tag . ' class="zt-sec-title">' . zt_icon( $s['head_icon'], array( 'class' => 'zt-leaf', 'width' => '22', 'height' => '22' ) ) . ' ' . esc_html( $s['head_title'] ) . '</' . $tag . '>'; // phpcs:ignore
		if ( $center && '' !== $s['head_sub'] ) {
			echo '<span class="zt-sec-sub">' . esc_html( $s['head_sub'] ) . '</span>';
		}
		if ( '' !== $s['head_link_text'] ) {
			echo '<a' . zt_link_attrs( $s['head_link'] ) . ' class="zt-link-more">' . esc_html( $s['head_link_text'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>'; // phpcs:ignore
		}
		echo '</div>';
	}

	/* =====================================================================
	 * Style generator
	 * =================================================================== */

	/**
	 * Append a pseudo class to every part of a selector list.
	 *
	 * @param string $sel    Selector.
	 * @param string $pseudo Pseudo (":hover").
	 * @return string
	 */
	protected static function pseudo( $sel, $pseudo ) {
		return implode( ', ', array_map( function ( $p ) use ( $pseudo ) {
			return trim( $p ) . $pseudo;
		}, explode( ',', $sel ) ) );
	}

	/**
	 * Add a style section with one block per element.
	 *
	 * @param string $id    Section id.
	 * @param string $label Section label.
	 * @param array  $items [ [key, label, selector, features[]], ... ].
	 */
	protected function style_section( $id, $label, $items ) {
		$this->start_controls_section(
			'st_' . $id,
			array(
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$first = true;
		foreach ( $items as $it ) {
			list( $key, $lbl, $sel, $features ) = $it;
			$this->add_control(
				'st_' . $key . '_h',
				array(
					'label'     => $lbl,
					'type'      => Controls_Manager::HEADING,
					'separator' => $first ? 'none' : 'before',
				)
			);
			$first = false;
			$this->style_block( $key, $sel, $features );
		}
		$this->end_controls_section();
	}

	/**
	 * Controls for one element.
	 *
	 * @param string $key      Key.
	 * @param string $sel      Selector (relative to the widget, {{WRAPPER}} added).
	 * @param array  $features Features.
	 */
	protected function style_block( $key, $sel, $features ) {
		$full = implode( ', ', array_map( function ( $p ) {
			return '{{WRAPPER}} ' . trim( $p );
		}, explode( ',', $sel ) ) );
		$p    = 'st_' . $key . '_';
		foreach ( $features as $f ) {
			switch ( $f ) {
				case 'typo':
					$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $p . 'typo', 'label' => 'تایپوگرافی', 'selector' => $full ) );
					break;
				case 'color':
					$this->add_control( $p . 'color', array( 'label' => 'رنگ', 'type' => Controls_Manager::COLOR, 'selectors' => array( $full => 'color: {{VALUE}};' ) ) );
					break;
				case 'hover_color':
					$this->add_control( $p . 'hcolor', array( 'label' => 'رنگ (هاور)', 'type' => Controls_Manager::COLOR, 'selectors' => array( self::pseudo( $full, ':hover' ) => 'color: {{VALUE}};' ) ) );
					break;
				case 'bg':
					$this->add_control( $p . 'bg', array( 'label' => 'رنگ پس‌زمینه', 'type' => Controls_Manager::COLOR, 'selectors' => array( $full => 'background-color: {{VALUE}};' ) ) );
					break;
				case 'bg_hover':
					$this->add_control( $p . 'hbg', array( 'label' => 'پس‌زمینه (هاور)', 'type' => Controls_Manager::COLOR, 'selectors' => array( self::pseudo( $full, ':hover' ) => 'background-color: {{VALUE}};' ) ) );
					break;
				case 'bg_group':
					$this->add_group_control( Group_Control_Background::get_type(), array( 'name' => $p . 'bgg', 'types' => array( 'classic', 'gradient' ), 'selector' => $full ) );
					break;
				case 'border':
					$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => $p . 'border', 'selector' => $full ) );
					break;
				case 'border_color':
					$this->add_control( $p . 'bcolor', array( 'label' => 'رنگ حاشیه', 'type' => Controls_Manager::COLOR, 'selectors' => array( $full => 'border-color: {{VALUE}};' ) ) );
					break;
				case 'radius':
					$this->add_responsive_control( $p . 'radius', array( 'label' => 'گردی گوشه', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( $full => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
					break;
				case 'shadow':
					$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => $p . 'shadow', 'selector' => $full ) );
					break;
				case 'padding':
					$this->add_responsive_control( $p . 'padding', array( 'label' => 'فاصله داخلی', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em', '%' ), 'selectors' => array( $full => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
					break;
				case 'margin':
					$this->add_responsive_control( $p . 'margin', array( 'label' => 'فاصله بیرونی', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em', '%' ), 'allowed_dimensions' => 'all', 'selectors' => array( $full => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
					break;
				case 'size':
					$this->add_responsive_control( $p . 'size', array( 'label' => 'اندازه', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'em' ), 'range' => array( 'px' => array( 'min' => 4, 'max' => 300 ) ), 'selectors' => array( $full => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'width':
					$this->add_responsive_control( $p . 'width', array( 'label' => 'عرض', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%', 'vw' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1600 ) ), 'selectors' => array( $full => 'width: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'max_width':
					$this->add_responsive_control( $p . 'maxw', array( 'label' => 'حداکثر عرض', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', '%', 'vw' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1600 ) ), 'selectors' => array( $full => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'height':
					$this->add_responsive_control( $p . 'height', array( 'label' => 'ارتفاع', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh', '%' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1000 ) ), 'selectors' => array( $full => 'height: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'min_height':
					$this->add_responsive_control( $p . 'minh', array( 'label' => 'حداقل ارتفاع', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1000 ) ), 'selectors' => array( $full => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'gap':
					$this->add_responsive_control( $p . 'gap', array( 'label' => 'فاصله بین آیتم‌ها', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'em' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 120 ) ), 'selectors' => array( $full => 'gap: {{SIZE}}{{UNIT}};' ) ) );
					break;
				case 'columns':
					$this->add_responsive_control( $p . 'cols', array( 'label' => 'تعداد ستون', 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 8, 'selectors' => array( $full => 'grid-template-columns: repeat({{VALUE}}, minmax(0,1fr));' ) ) );
					break;
				case 'align':
					$this->add_responsive_control( $p . 'align', array( 'label' => 'تراز متن', 'type' => Controls_Manager::CHOOSE, 'options' => array( 'right' => array( 'title' => 'راست', 'icon' => 'eicon-text-align-right' ), 'center' => array( 'title' => 'وسط', 'icon' => 'eicon-text-align-center' ), 'left' => array( 'title' => 'چپ', 'icon' => 'eicon-text-align-left' ) ), 'selectors' => array( $full => 'text-align: {{VALUE}};' ) ) );
					break;
				case 'justify':
					$this->add_responsive_control( $p . 'justify', array( 'label' => 'چینش افقی', 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'پیش‌فرض', 'flex-start' => 'ابتدا', 'center' => 'وسط', 'flex-end' => 'انتها', 'space-between' => 'فاصله برابر' ), 'selectors' => array( $full => 'justify-content: {{VALUE}};' ) ) );
					break;
				case 'opacity':
					$this->add_control( $p . 'opacity', array( 'label' => 'شفافیت', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => .01 ) ), 'selectors' => array( $full => 'opacity: {{SIZE}};' ) ) );
					break;
				case 'fit':
					$this->add_control( $p . 'fit', array( 'label' => 'نحوه نمایش تصویر', 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'پیش‌فرض', 'cover' => 'پوشش', 'contain' => 'کامل', 'fill' => 'کشیده' ), 'selectors' => array( $full => 'object-fit: {{VALUE}};' ) ) );
					break;
				case 'ratio':
					$this->add_responsive_control( $p . 'ratio', array( 'label' => 'نسبت ابعاد (مثلا 1/1)', 'type' => Controls_Manager::TEXT, 'selectors' => array( $full => 'aspect-ratio: {{VALUE}};' ) ) );
					break;
				case 'stroke':
					$this->add_control( $p . 'stroke', array( 'label' => 'ضخامت خط آیکون', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => .5, 'max' => 4, 'step' => .1 ) ), 'selectors' => array( $full => 'stroke-width: {{SIZE}};' ) ) );
					break;
				case 'display':
					$this->add_responsive_control( $p . 'display', array( 'label' => 'نمایش', 'type' => Controls_Manager::SELECT, 'options' => array( '' => 'پیش‌فرض', 'none' => 'پنهان' ), 'selectors' => array( $full => 'display: {{VALUE}} !important;' ) ) );
					break;
			}
		}
	}

	/**
	 * Shorthand feature sets.
	 *
	 * @param string $kind text|box|icon|image|button|card.
	 * @return array
	 */
	protected static function fx( $kind ) {
		$sets = array(
			'text'   => array( 'typo', 'color', 'margin', 'align', 'display' ),
			'link'   => array( 'typo', 'color', 'hover_color', 'display' ),
			'box'    => array( 'bg', 'border', 'radius', 'shadow', 'padding', 'margin', 'display' ),
			'card'   => array( 'bg', 'bg_hover', 'border', 'radius', 'shadow', 'padding', 'min_height' ),
			'icon'   => array( 'size', 'color', 'stroke', 'margin', 'display' ),
			'image'  => array( 'width', 'height', 'ratio', 'radius', 'fit', 'shadow', 'opacity', 'display' ),
			'button' => array( 'typo', 'color', 'hover_color', 'bg', 'bg_hover', 'border', 'radius', 'shadow', 'padding', 'height', 'display' ),
			'grid'   => array( 'columns', 'gap', 'margin' ),
			'badge'  => array( 'typo', 'color', 'bg', 'radius', 'padding', 'height', 'display' ),
		);
		return isset( $sets[ $kind ] ) ? $sets[ $kind ] : array();
	}

	/* =====================================================================
	 * Data sources
	 * =================================================================== */

	/**
	 * Product source controls.
	 *
	 * @param array $manual_defaults Manual rows (the design content).
	 * @param array $o              Options: query default, limit.
	 */
	protected function product_source_controls( $manual_defaults, $o = array() ) {
		$o = wp_parse_args(
			$o,
			array(
				'query' => 'latest',
				'limit' => 4,
			)
		);
		$this->ctl(
			'src',
			'select',
			'منبع محصولات',
			'auto',
			array(
				'options'     => array(
					'auto'   => 'خودکار (ووکامرس، در صورت نبود محصول: نمونه طرح)',
					'query'  => 'ووکامرس',
					'manual' => 'دستی',
				),
				'description' => 'در حالت خودکار تا وقتی محصولی در فروشگاه نباشد، محتوای نمونه‌ی طرح نمایش داده می‌شود.',
			)
		);
		$cats = array();
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'number'     => 200,
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$cats[ $t->term_id ] = $t->name;
			}
		}
		$cond = array( 'src!' => 'manual' );
		$this->ctl(
			'q_type',
			'select',
			'نوع کوئری',
			$o['query'],
			array(
				'options'   => array(
					'latest'   => 'جدیدترین',
					'on_sale'  => 'تخفیف‌دار (حراج)',
					'featured' => 'ویژه',
					'best'     => 'پرفروش‌ترین',
					'rated'    => 'بیشترین امتیاز',
					'related'  => 'مرتبط با محصول فعلی',
					'upsells'  => 'محصولات پیشنهادی (Upsell) محصول فعلی',
					'cross'    => 'محصولات مکمل سبد',
					'ids'      => 'انتخاب دستی شناسه‌ها',
					'meta'     => 'علامت‌خورده با «نمایش در این بخش»',
				),
				'condition' => $cond,
			)
		);
		$this->ctl( 'q_ids', 'text', 'شناسه محصولات (با کاما)', '', array( 'condition' => array_merge( $cond, array( 'q_type' => 'ids' ) ) ) );
		$this->ctl( 'q_meta', 'text', 'کلید بخش (مثلا selected)', 'selected', array( 'condition' => array_merge( $cond, array( 'q_type' => 'meta' ) ), 'description' => 'در صفحه ویرایش محصول، در فیلد «نمایش در بخش‌ها» همین کلید را وارد کنید.' ) );
		$this->ctl( 'q_cats', 'select2', 'محدود به دسته‌ها', array(), array( 'options' => $cats, 'multiple' => true, 'label_block' => true, 'condition' => $cond ) );
		$this->ctl( 'q_limit', 'number', 'تعداد', $o['limit'], array( 'min' => 1, 'max' => 48, 'condition' => $cond ) );
		$this->ctl(
			'q_orderby',
			'select',
			'مرتب‌سازی',
			'date',
			array(
				'options'   => array(
					'date'       => 'تاریخ',
					'title'      => 'عنوان',
					'price'      => 'قیمت',
					'popularity' => 'فروش',
					'rating'     => 'امتیاز',
					'rand'       => 'تصادفی',
					'menu_order' => 'ترتیب دستی',
				),
				'condition' => $cond,
			)
		);
		$this->ctl( 'q_order', 'select', 'جهت', 'DESC', array( 'options' => array( 'DESC' => 'نزولی', 'ASC' => 'صعودی' ), 'condition' => $cond ) );
		$this->ctl( 'q_instock', 'switch', 'فقط کالاهای موجود', false, array( 'condition' => $cond ) );
		$this->repeater(
			'manual',
			'محصولات دستی / نمونه',
			array(
				array( 'title', 'text', 'عنوان', 'محصول' ),
				array( 'cat', 'text', 'دسته', '' ),
				array( 'img', 'media', 'تصویر', '' ),
				array( 'price', 'number', 'قیمت (تومان)', 0 ),
				array( 'regular', 'number', 'قیمت قبل از تخفیف (اختیاری)', 0 ),
				array( 'exp', 'text', 'تاریخ انقضا (مثلا ۱۴۰۸/۰۲)', '' ),
				array( 'url', 'url', 'لینک', '{{shop}}' ),
				array( 'pid', 'number', 'شناسه محصول ووکامرس (برای افزودن به سبد)', 0 ),
			),
			$manual_defaults,
			'{{{ title }}}'
		);
	}

	/**
	 * Resolve product items.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function get_products( $s ) {
		$items = array();
		$src   = isset( $s['src'] ) ? $s['src'] : 'auto';
		if ( 'auto' === $src && ZT_Context::demo() ) {
			$src = 'manual'; // design preview: the design's own sample products.
		}
		if ( 'manual' !== $src && zt_is_woo() ) {
			foreach ( $this->query_products( $s ) as $p ) {
				$it = ZT_Parts::product_item( $p );
				if ( $it ) {
					$items[] = $it;
				}
			}
			if ( $items || 'query' === $src ) {
				return $items;
			}
		}
		foreach ( (array) ( isset( $s['manual'] ) ? $s['manual'] : array() ) as $r ) {
			$price   = (float) $r['price'];
			$regular = (float) $r['regular'];
			$pid     = (int) ( isset( $r['pid'] ) ? $r['pid'] : 0 );
			$items[] = array(
				'id'          => $pid,
				'title'       => $r['title'],
				'cat'         => $r['cat'],
				'url'         => zt_url( $r['url'] ),
				'img'         => zt_img_url( $r['img'] ),
				'price'       => $price * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) ),
				'regular'     => $regular * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) ),
				'pct'         => ( $regular > $price && $regular > 0 ) ? (int) round( ( $regular - $price ) / $regular * 100 ) : 0,
				'exp'         => $r['exp'],
				'type'        => 'simple',
				'purchasable' => true,
			);
		}
		return $items;
	}

	/**
	 * Run the product query.
	 *
	 * @param array $s Settings.
	 * @return WC_Product[]
	 */
	protected function query_products( $s ) {
		$type  = isset( $s['q_type'] ) ? $s['q_type'] : 'latest';
		$limit = max( 1, (int) ( isset( $s['q_limit'] ) ? $s['q_limit'] : 4 ) );
		$args  = array(
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => isset( $s['q_orderby'] ) ? $s['q_orderby'] : 'date',
			'order'   => isset( $s['q_order'] ) ? $s['q_order'] : 'DESC',
		);
		if ( 'popularity' === $args['orderby'] ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'total_sales'; // phpcs:ignore
		} elseif ( 'rating' === $args['orderby'] ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
		} elseif ( 'price' === $args['orderby'] ) {
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_price'; // phpcs:ignore
		}
		if ( ! empty( $s['q_instock'] ) ) {
			$args['stock_status'] = 'instock';
		}
		if ( ! empty( $s['q_cats'] ) ) {
			$slugs = array();
			foreach ( (array) $s['q_cats'] as $tid ) {
				$t = get_term( (int) $tid, 'product_cat' );
				if ( $t && ! is_wp_error( $t ) ) {
					$slugs[] = $t->slug;
				}
			}
			$args['category'] = $slugs;
		}
		$current = ZT_Context::product();
		switch ( $type ) {
			case 'on_sale':
				$ids = wc_get_product_ids_on_sale();
				if ( ! $ids ) {
					return array();
				}
				$args['include'] = $ids;
				break;
			case 'featured':
				$args['featured'] = true;
				break;
			case 'best':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'rated':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'related':
				if ( ! $current ) {
					break;
				}
				$ids = wc_get_related_products( $current->get_id(), $limit );
				if ( ! $ids ) {
					return array();
				}
				$args['include'] = $ids;
				break;
			case 'upsells':
				if ( ! $current ) {
					return array();
				}
				$ids = $current->get_upsell_ids();
				if ( ! $ids ) {
					return array();
				}
				$args['include'] = $ids;
				break;
			case 'cross':
				$ids = ( WC()->cart ) ? WC()->cart->get_cross_sells() : array();
				if ( ! $ids ) {
					return array();
				}
				$args['include'] = $ids;
				break;
			case 'ids':
				$ids = array_filter( array_map( 'absint', explode( ',', zt_en( (string) $s['q_ids'] ) ) ) );
				if ( ! $ids ) {
					return array();
				}
				$args['include'] = $ids;
				$args['orderby'] = 'include';
				break;
			case 'meta':
				$key = sanitize_key( isset( $s['q_meta'] ) ? $s['q_meta'] : 'selected' );
				$args['meta_query'] = array( // phpcs:ignore
					array(
						'key'     => '_zt_sections',
						'value'   => $key,
						'compare' => 'LIKE',
					),
				);
				break;
		}
		if ( $current && in_array( $type, array( 'latest', 'on_sale', 'featured', 'best', 'rated', 'meta' ), true ) && is_product() ) {
			$args['exclude'] = array( $current->get_id() );
		}
		$res = wc_get_products( $args );
		return is_array( $res ) ? $res : array();
	}

	/**
	 * Category source controls.
	 *
	 * @param array $manual_defaults Manual rows.
	 */
	protected function category_source_controls( $manual_defaults ) {
		$this->ctl(
			'csrc',
			'select',
			'منبع دسته‌بندی‌ها',
			'manual',
			array(
				'options' => array(
					'manual' => 'دستی (تصویر و لینک دلخواه)',
					'terms'  => 'دسته‌بندی‌های ووکامرس',
				),
			)
		);
		$cats = array();
		if ( taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'number'     => 200,
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$cats[ $t->term_id ] = $t->name;
			}
		}
		$this->ctl( 'c_terms', 'select2', 'دسته‌ها (خالی = دسته‌های اصلی)', array(), array( 'options' => $cats, 'multiple' => true, 'label_block' => true, 'condition' => array( 'csrc' => 'terms' ) ) );
		$this->ctl( 'c_limit', 'number', 'تعداد', 6, array( 'condition' => array( 'csrc' => 'terms' ) ) );
		$this->repeater(
			'cats',
			'دسته‌ها',
			array(
				array( 'name', 'text', 'عنوان', 'دسته' ),
				array( 'img', 'media', 'تصویر', '' ),
				array( 'url', 'url', 'لینک', '{{shop}}' ),
			),
			$manual_defaults,
			'{{{ name }}}',
			array( 'condition' => array( 'csrc' => 'manual' ) )
		);
	}

	/**
	 * Resolve category items [name, img, url].
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	protected function get_categories_items( $s ) {
		$out = array();
		if ( 'terms' === ( isset( $s['csrc'] ) ? $s['csrc'] : 'manual' ) && taxonomy_exists( 'product_cat' ) ) {
			$args = array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false,
				'number'     => max( 1, (int) $s['c_limit'] ),
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			);
			if ( ! empty( $s['c_terms'] ) ) {
				$args['include'] = array_map( 'intval', (array) $s['c_terms'] );
				$args['orderby'] = 'include';
			} else {
				$args['parent'] = 0;
			}
			$terms = get_terms( $args );
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$thumb = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
				$out[] = array(
					'name' => $t->name,
					'img'  => $thumb ? wp_get_attachment_image_url( $thumb, 'large' ) : '',
					'url'  => get_term_link( $t ),
				);
			}
			return $out;
		}
		foreach ( (array) ( isset( $s['cats'] ) ? $s['cats'] : array() ) as $r ) {
			$out[] = array(
				'name' => $r['name'],
				'img'  => zt_img_url( $r['img'] ),
				'url'  => zt_url( $r['url'] ),
			);
		}
		return $out;
	}

	/**
	 * Post source controls.
	 *
	 * @param array $manual_defaults Manual rows.
	 * @param int   $limit           Default limit.
	 */
	protected function post_source_controls( $manual_defaults, $limit = 3 ) {
		$this->ctl(
			'psrc',
			'select',
			'منبع مقالات',
			'auto',
			array(
				'options' => array(
					'auto'   => 'خودکار (نوشته‌های وردپرس، در صورت نبود: نمونه طرح)',
					'query'  => 'نوشته‌های وردپرس',
					'manual' => 'دستی',
				),
			)
		);
		$cats = array();
		foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
			$cats[ $c->term_id ] = $c->name;
		}
		$this->ctl( 'p_cats', 'select2', 'دسته‌ها', array(), array( 'options' => $cats, 'multiple' => true, 'label_block' => true, 'condition' => array( 'psrc!' => 'manual' ) ) );
		$this->ctl( 'p_limit', 'number', 'تعداد', $limit, array( 'condition' => array( 'psrc!' => 'manual' ) ) );
		$this->ctl( 'p_orderby', 'select', 'مرتب‌سازی', 'date', array( 'options' => array( 'date' => 'تاریخ', 'comment_count' => 'بیشترین دیدگاه', 'rand' => 'تصادفی', 'title' => 'عنوان' ), 'condition' => array( 'psrc!' => 'manual' ) ) );
		$this->repeater(
			'posts',
			'مقالات دستی / نمونه',
			array(
				array( 'title', 'textarea', 'عنوان', 'عنوان مقاله' ),
				array( 'tag', 'text', 'برچسب', 'وبلاگ' ),
				array( 'img', 'media', 'تصویر', '' ),
				array( 'url', 'url', 'لینک', '#' ),
			),
			$manual_defaults,
			'{{{ title }}}'
		);
	}

	/**
	 * Resolve post items.
	 *
	 * @param array $s       Settings.
	 * @param array $exclude Ids to exclude.
	 * @return array
	 */
	protected function get_posts_items( $s, $exclude = array() ) {
		$src = isset( $s['psrc'] ) ? $s['psrc'] : 'auto';
		if ( 'auto' === $src && ZT_Context::demo() ) {
			$src = 'manual';
		}
		$out = array();
		if ( 'manual' !== $src ) {
			$args = array(
				'post_type'           => 'post',
				'posts_per_page'      => max( 1, (int) $s['p_limit'] ),
				'orderby'             => $s['p_orderby'],
				'ignore_sticky_posts' => true,
				'post__not_in'        => $exclude,
			);
			if ( ! empty( $s['p_cats'] ) ) {
				$args['category__in'] = array_map( 'intval', (array) $s['p_cats'] );
			}
			foreach ( get_posts( $args ) as $p ) {
				$out[] = ZT_Parts::post_item( $p );
			}
			if ( $out || 'query' === $src ) {
				return $out;
			}
		}
		foreach ( (array) ( isset( $s['posts'] ) ? $s['posts'] : array() ) as $r ) {
			$out[] = array(
				'title' => $r['title'],
				'tag'   => $r['tag'],
				'img'   => zt_img_url( $r['img'] ),
				'url'   => zt_url( $r['url'] ),
			);
		}
		return $out;
	}

	/**
	 * Carousel arrows (desktop).
	 *
	 * @param string $top  CSS top.
	 */
	protected function carousel_arrows( $top = '50%' ) {
		echo '<button class="zt-iconbtn" data-zt-dir="prev" style="position:absolute;top:' . esc_attr( $top ) . ';inset-inline-end:-21px;transform:translateY(-50%)" aria-label="قبلی">' . zt_icon( 'chev-left' ) . '</button>'; // phpcs:ignore
		echo '<button class="zt-iconbtn" data-zt-dir="next" style="position:absolute;top:' . esc_attr( $top ) . ';inset-inline-start:-21px;transform:translateY(-50%)" aria-label="بعدی">' . zt_icon( 'chev-right' ) . '</button>'; // phpcs:ignore
	}

	/**
	 * Is this render in the editor?
	 *
	 * @return bool
	 */
	protected function is_editor() {
		return zt_is_editor();
	}
}
