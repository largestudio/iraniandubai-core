<?php
/**
 * IranianDubai Blog Elementor widget.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use IDB\Blog\Defaults;
use IDB\Elementor\Contracts\WidgetInterface;
use IDB\Frontend\BlogRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor widget that renders the blog through BlogRenderer.
 */
final class Widget extends Widget_Base implements WidgetInterface {
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
		return 'idb_blog';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'IranianDubai Blog', 'iraniandubai-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-post-list';
	}

	/**
	 * Widget stylesheet.
	 *
	 * @return string[]
	 */
	public function get_style_depends(): array {
		return array( 'idb-blog' );
	}

	/**
	 * Widget script.
	 *
	 * @return string[]
	 */
	public function get_script_depends(): array {
		return array();
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
		$defaults = Defaults::SETTINGS;

		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Content', 'iraniandubai-core' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'iraniandubai-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					'' => __( 'Default', 'iraniandubai-core' ),
					1 => __( '1 Column', 'iraniandubai-core' ),
					2 => __( '2 Columns', 'iraniandubai-core' ),
					3 => __( '3 Columns', 'iraniandubai-core' ),
					4 => __( '4 Columns', 'iraniandubai-core' ),
				),
			)
		);

		$this->add_control(
			'excerpt',
			array(
				'label'       => __( 'Excerpt', 'iraniandubai-core' ),
				'type'        => Controls_Manager::NUMBER,
				'default'     => '',
				'min'         => 0,
				'max'         => 80,
				'step'        => 1,
				'placeholder' => (string) $defaults['excerpt_length'],
			)
		);

		$this->add_control(
			'pagination',
			array(
				'label'        => __( 'Pagination', 'iraniandubai-core' ),
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

		$this->add_control(
			'card_radius',
			array(
				'label'      => __( 'Card Radius', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card' => 'border-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog__grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Typography', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card__title',
			)
		);

		$this->add_control(
			'button_color',
			array(
				'label'     => __( 'Button Text Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background',
			array(
				'label'     => __( 'Button Background', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__button' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'image_ratio',
			array(
				'label'     => __( 'Image Ratio', 'iraniandubai-core' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 1.78,
				'min'       => 1,
				'max'       => 2.4,
				'step'      => 0.01,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__media' => 'aspect-ratio: {{VALUE}} / 1;',
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
		$data     = $this->get_data();
		$settings = isset( $data['settings'] ) && is_array( $data['settings'] ) ? $data['settings'] : array();
		$atts     = array(
			'pagination' => $settings['pagination'] ?? 'yes',
		);

		foreach ( array( 'columns', 'excerpt' ) as $setting_key ) {
			if ( array_key_exists( $setting_key, $settings ) && '' !== $settings[ $setting_key ] ) {
				$atts[ $setting_key ] = $settings[ $setting_key ];
			}
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- BlogRenderer returns escaped template markup.
		echo $this->renderer->render( $atts );
	}

}
