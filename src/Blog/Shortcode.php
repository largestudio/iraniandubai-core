<?php
/**
 * Blog shortcode module.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Blog;

use IDB\Core\ModuleInterface;
use IDB\Frontend\BlogRenderer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the [idb_blog] shortcode.
 */
final class Shortcode implements ModuleInterface {
	/**
	 * Blog renderer.
	 *
	 * @var BlogRenderer
	 */
	private BlogRenderer $renderer;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->renderer = new BlogRenderer();
	}

	/**
	 * Register shortcode hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( 'idb_blog', array( $this, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( $this->renderer, 'enqueue_assets_for_shortcode' ) );
	}

	/**
	 * Render the [idb_blog] shortcode.
	 *
	 * @param mixed $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render( mixed $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'category'       => '',
				'columns'        => '',
				'excerpt'        => '',
				'order'          => '',
				'orderby'        => '',
				'pagination'     => 'yes',
				'posts'          => '',
				'paged'          => '',
				'posts_per_page' => '',
			),
			is_array( $atts ) ? $atts : array(),
			'idb_blog'
		);

		return $this->renderer->render( $atts );
	}
}
