<?php
/**
 * LSOS Blog Hero Elementor widget.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use IDB\Frontend\BlogRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor widget for the standalone LSOS Blog Hero section.
 */
final class BlogHeroWidget extends Widget_Base {
	/**
	 * Blog renderer.
	 *
	 * @var BlogRenderer
	 */
	private BlogRenderer $renderer;

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed> $data Widget data.
	 * @param mixed               $args Widget args.
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );

		$this->renderer = new BlogRenderer();
	}

	/**
	 * Get widget name.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'lsos_blog_hero';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'LSOS Blog Hero', 'iraniandubai-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-image-box';
	}

	/**
	 * Get widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories(): array {
		return array( 'general' );
	}

	/**
	 * Register widget controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register content controls.
	 *
	 * @return void
	 */
	private function register_content_controls(): void {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'iraniandubai-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Title', 'iraniandubai-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'راهنمای زندگی و رشد ایرانیان در دبی', 'iraniandubai-core' ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => __( 'Description', 'iraniandubai-core' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => __( 'مطالب کاربردی، تازه‌ترین راهنماها و روایت‌های عملی برای زندگی، سرمایه‌گذاری و رشد در دبی.', 'iraniandubai-core' ),
			)
		);

		$this->add_control(
			'image',
			array(
				'label' => __( 'Background/Image', 'iraniandubai-core' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'search_placeholder',
			array(
				'label'   => __( 'Search Placeholder', 'iraniandubai-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'جستجو در مقالات', 'iraniandubai-core' ),
			)
		);

		$this->add_control(
			'search_button_text',
			array(
				'label'   => __( 'Search Button Text', 'iraniandubai-core' ),
				'type'    => Controls_Manager::TEXT,
				'default' => __( 'جستجو', 'iraniandubai-core' ),
			)
		);

		$this->add_control(
			'show_search',
			array(
				'label'        => __( 'Show Search', 'iraniandubai-core' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'iraniandubai-core' ),
				'label_off'    => __( 'Hide', 'iraniandubai-core' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register style controls.
	 *
	 * @return void
	 */
	private function register_style_controls(): void {
		$this->start_controls_section(
			'style_section',
			array(
				'label' => __( 'Style', 'iraniandubai-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'content_width',
			array(
				'label'      => __( 'Content Width', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%', 'px' ),
				'range'      => array(
					'%'  => array(
						'min' => 25,
						'max' => 75,
					),
					'px' => array(
						'min' => 280,
						'max' => 900,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog__hero-content' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => __( 'Image Width', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min' => 35,
						'max' => 65,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog__hero' => 'grid-template-columns: minmax(0, {{SIZE}}%) minmax(0, calc(100% - {{SIZE}}%));',
				),
			)
		);

		$this->add_responsive_control(
			'min_height',
			array(
				'label'      => __( 'Minimum Height', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog__hero' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => __( 'Border Radius', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog__hero, {{WRAPPER}} .idb-blog__hero-media' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'overlay',
			array(
				'label'     => __( 'Overlay', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__hero-overlay' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__heading' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'description_color',
			array(
				'label'     => __( 'Description Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__intro' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_color',
			array(
				'label'     => __( 'Search Field Text', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__search-input' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'field_background',
			array(
				'label'     => __( 'Search Field Background', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__search, {{WRAPPER}} .idb-blog__search-input' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => __( 'Button Text', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__search-button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background',
			array(
				'label'     => __( 'Button Background', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog__search-button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'spacing',
			array(
				'label'      => __( 'Spacing', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog__hero' => 'gap: {{SIZE}}{{UNIT}}; padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 *
	 * @return void
	 */
	protected function render(): void {
		$this->renderer->enqueue_assets();

		$settings      = $this->get_settings_for_display();
		$blog_renderer = $this->renderer;
		$template      = IDB_CORE_PATH . 'templates/elementor/blog-hero.php';

		if ( is_readable( $template ) ) {
			include $template;
		}
	}
}
