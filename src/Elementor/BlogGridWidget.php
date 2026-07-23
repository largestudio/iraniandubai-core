<?php
/**
 * LSOS Blog Grid Elementor widget.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;
use IDB\Blog\Defaults;
use IDB\Frontend\BlogRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor widget for the standalone LSOS Blog Grid section.
 */
final class BlogGridWidget extends Widget_Base {
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
		return 'lsos_blog_grid';
	}

	/**
	 * Get widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'LSOS Blog Grid', 'iraniandubai-core' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-posts-grid';
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
			'posts',
			array(
				'label'   => __( 'Posts Per Page', 'iraniandubai-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => $defaults['posts_per_page'],
				'min'     => Defaults::POSTS_PER_PAGE_MIN,
				'max'     => Defaults::POSTS_PER_PAGE_MAX,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'              => __( 'Columns', 'iraniandubai-core' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 3,
				'tablet_default'     => 2,
				'mobile_default'     => 1,
				'options'            => array(
					1 => __( '1 Column', 'iraniandubai-core' ),
					2 => __( '2 Columns', 'iraniandubai-core' ),
					3 => __( '3 Columns', 'iraniandubai-core' ),
					4 => __( '4 Columns', 'iraniandubai-core' ),
				),
				'selectors'          => array(
					'{{WRAPPER}} .idb-blog .idb-blog__grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)) !important;',
				),
				'frontend_available' => true,
			)
		);

		$this->add_control(
			'excerpt',
			array(
				'label'   => __( 'Excerpt Length', 'iraniandubai-core' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => $defaults['excerpt_length'],
				'min'     => 0,
				'max'     => 80,
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => __( 'Order', 'iraniandubai-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['order'],
				'options' => array(
					'DESC' => __( 'Descending', 'iraniandubai-core' ),
					'ASC'  => __( 'Ascending', 'iraniandubai-core' ),
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order By', 'iraniandubai-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['orderby'],
				'options' => array(
					'date'          => __( 'Date', 'iraniandubai-core' ),
					'modified'      => __( 'Modified', 'iraniandubai-core' ),
					'title'         => __( 'Title', 'iraniandubai-core' ),
					'comment_count' => __( 'Comment Count', 'iraniandubai-core' ),
					'rand'          => __( 'Random', 'iraniandubai-core' ),
					'menu_order'    => __( 'Menu Order', 'iraniandubai-core' ),
					'ID'            => __( 'ID', 'iraniandubai-core' ),
				),
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

		$this->add_control(
			'pagination_mode',
			array(
				'label'     => __( 'Pagination Mode', 'iraniandubai-core' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $defaults['pagination_mode'],
				'condition' => array(
					'pagination' => 'yes',
				),
				'options'   => array(
					'pagination'      => __( 'Pagination', 'iraniandubai-core' ),
					'load_more'       => __( 'Load More', 'iraniandubai-core' ),
					'infinite_scroll' => __( 'Infinite Scroll', 'iraniandubai-core' ),
				),
			)
		);

		$this->add_control(
			'category',
			array(
				'label'   => __( 'Category', 'iraniandubai-core' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => $this->get_category_options(),
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
			'grid_style_section',
			array(
				'label' => __( 'Blog Grid', 'iraniandubai-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => __( 'Grid Gap', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog__grid' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_background',
			array(
				'label'     => __( 'Card Background', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_border_radius',
			array(
				'label'      => __( 'Card Border Radius', 'iraniandubai-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'label'    => __( 'Card Shadow', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card',
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Card Padding', 'iraniandubai-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'Image Height', 'iraniandubai-core' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 80,
						'max' => 800,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__media' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;',
				),
			)
		);

		$this->add_responsive_control(
			'image_border_radius',
			array(
				'label'      => __( 'Image Border Radius', 'iraniandubai-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__media, {{WRAPPER}} .idb-blog .idb-blog-card__image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Title Typography', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card__title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Title Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__title, {{WRAPPER}} .idb-blog .idb-blog-card__title a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'excerpt_typography',
				'label'    => __( 'Excerpt Typography', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card__excerpt',
			)
		);

		$this->add_control(
			'excerpt_color',
			array(
				'label'     => __( 'Excerpt Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__excerpt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'meta_typography',
				'label'    => __( 'Meta Typography', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card__meta, {{WRAPPER}} .idb-blog .idb-blog-card__date',
			)
		);

		$this->add_control(
			'meta_color',
			array(
				'label'     => __( 'Meta Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__meta, {{WRAPPER}} .idb-blog .idb-blog-card__meta a, {{WRAPPER}} .idb-blog .idb-blog-card__date' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => __( 'Button Typography', 'iraniandubai-core' ),
				'selector' => '{{WRAPPER}} .idb-blog .idb-blog-card__button',
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => __( 'Button Text Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__button' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => __( 'Button Background Color', 'iraniandubai-core' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .idb-blog .idb-blog-card__button' => 'background-color: {{VALUE}};',
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
		$settings      = $this->get_settings_for_display();
		$blog_renderer = $this->renderer;
		$blog_atts     = $this->get_blog_atts( $settings );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- BlogRenderer returns escaped template markup.
		echo $blog_renderer->render_grid( $blog_atts );
	}

	/**
	 * Build BlogRenderer attributes from widget settings.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 *
	 * @return array<string,mixed>
	 */
	private function get_blog_atts( array $settings ): array {
		return array(
			'posts'           => absint( $settings['posts'] ?? Defaults::SETTINGS['posts_per_page'] ),
			'posts_per_page'  => absint( $settings['posts'] ?? Defaults::SETTINGS['posts_per_page'] ),
			'columns'         => absint( $settings['columns'] ?? 3 ),
			'excerpt'         => absint( $settings['excerpt'] ?? Defaults::SETTINGS['excerpt_length'] ),
			'order'           => (string) ( $settings['order'] ?? Defaults::SETTINGS['order'] ),
			'orderby'         => (string) ( $settings['orderby'] ?? Defaults::SETTINGS['orderby'] ),
			'pagination'      => 'yes' === ( $settings['pagination'] ?? 'yes' ) ? 'yes' : 'no',
			'pagination_mode' => (string) ( $settings['pagination_mode'] ?? Defaults::SETTINGS['pagination_mode'] ),
			'category'        => sanitize_title( (string) ( $settings['category'] ?? '' ) ),
			'layout'          => 'grid',
			'render_context'  => 'grid',
		);
	}

	/**
	 * Get category options.
	 *
	 * @return array<string,string>
	 */
	private function get_category_options(): array {
		$options = array(
			'' => __( 'All Categories', 'iraniandubai-core' ),
		);
		$terms   = get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => 'category',
			)
		);

		if ( ! is_array( $terms ) ) {
			return $options;
		}

		foreach ( $terms as $term ) {
			if ( $term instanceof \WP_Term ) {
				$options[ $term->slug ] = $term->name;
			}
		}

		return $options;
	}
}
