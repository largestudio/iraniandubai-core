<?php
/**
 * Blog shortcode renderer.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Frontend;

use IDB\Blog\Defaults;
use IDB\Blog\Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders latest posts for the [idb_blog] shortcode.
 */
final class BlogRenderer {
	private const CATEGORY_QUERY_VAR = 'idb_category';

	/**
	 * Render latest blog posts.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render( array $atts ): string {
		$query = $this->get_query( $atts );

		$this->enqueue_assets();

		ob_start();

		$blog_query    = $query;
		$blog_renderer = $this;
		$blog_atts     = $this->normalize_atts( $atts );
		$blog_paged    = $this->get_current_page( $atts );
		$template      = IDB_CORE_PATH . 'templates/blog.php';

		if ( is_readable( $template ) ) {
			include $template;
		}

		wp_reset_postdata();

		return (string) ob_get_clean();
	}

	/**
	 * Enqueue assets when the current queried post contains the shortcode.
	 *
	 * @return void
	 */
	public function enqueue_assets_for_shortcode(): void {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();

		if ( ! $post instanceof \WP_Post || ! has_shortcode( $post->post_content, 'idb_blog' ) ) {
			return;
		}

		$this->enqueue_assets();
	}

	/**
	 * Get excerpt text for a post.
	 *
	 * @param int $post_id Post ID.
	 * @param int $length  Excerpt word count.
	 *
	 * @return string
	 */
	public function get_excerpt( int $post_id, int $length ): string {
		$excerpt = get_the_excerpt( $post_id );

		if ( '' === $excerpt ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		}

		return wp_trim_words( $excerpt, max( 1, $length ), '...' );
	}

	/**
	 * Estimate reading time for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return int
	 */
	public function get_read_time( int $post_id ): int {
		$content = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		$matches = array();

		preg_match_all( '/[\p{L}\p{N}]+/u', $content, $matches );

		return max( 1, (int) ceil( count( $matches[0] ) / 200 ) );
	}

	/**
	 * Get the first assigned category for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return \WP_Term|null
	 */
	public function get_category( int $post_id ): ?\WP_Term {
		$categories = get_the_category( $post_id );

		if ( empty( $categories ) || ! isset( $categories[0] ) || ! $categories[0] instanceof \WP_Term ) {
			return null;
		}

		return $categories[0];
	}

	/**
	 * Get post thumbnail markup.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_image( int $post_id ): string {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return '<span class="idb-blog-card__image idb-blog-card__image--placeholder" aria-hidden="true"></span>';
		}

		return (string) get_the_post_thumbnail(
			$post_id,
			'large',
			array(
				'class'    => 'idb-blog-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => '(min-width: 1024px) 50vw, 100vw',
			)
		);
	}

	/**
	 * Get category filter terms.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return \WP_Term[]
	 */
	public function get_filter_categories( array $atts ): array {
		$normalized = $this->normalize_atts( $atts );
		$args       = array(
			'hide_empty' => true,
			'taxonomy'   => 'category',
		);

		if ( '' !== $normalized['category'] ) {
			$category = $this->get_category_by_value( $normalized['category'] );

			if ( null !== $category ) {
				$args['include'] = array( $category->term_id );
			}
		}

		$terms = get_terms( $args );

		return is_array( $terms ) ? $terms : array();
	}

	/**
	 * Get selected category slug.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function get_selected_category( array $atts ): string {
		$normalized = $this->normalize_atts( $atts );
		$selected   = $this->get_requested_category();

		return '' !== $selected ? $selected : $normalized['category'];
	}

	/**
	 * Build a filter URL.
	 *
	 * @param string $category_slug Category slug.
	 *
	 * @return string
	 */
	public function get_category_filter_url( string $category_slug ): string {
		$url = remove_query_arg( array( self::CATEGORY_QUERY_VAR, 'paged', 'page' ), get_pagenum_link( 1 ) );

		if ( '' === $category_slug ) {
			return $url;
		}

		return add_query_arg( self::CATEGORY_QUERY_VAR, sanitize_title( $category_slug ), $url );
	}

	/**
	 * Check if pagination is enabled.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return bool
	 */
	public function has_pagination( array $atts ): bool {
		$normalized = $this->normalize_atts( $atts );

		return $normalized['pagination'];
	}

	/**
	 * Build a dedicated posts query.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return \WP_Query
	 */
	private function get_query( array $atts ): \WP_Query {
		$normalized = $this->normalize_atts( $atts );

		$requested_category = $this->get_requested_category();

		return Query::make(
			array(
				'posts_per_page' => $normalized['posts'],
				'paged'          => $this->get_current_page( $atts ),
				'order'          => $normalized['order'],
				'orderby'        => $normalized['orderby'],
				'category_name'  => '' !== $requested_category ? $requested_category : $normalized['category'],
			)
		);
	}

	/**
	 * Get the current pagination page.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return int
	 */
	public function get_current_page( array $atts = array() ): int {
		if ( isset( $atts['paged'] ) && '' !== $atts['paged'] ) {
			return max( 1, absint( $atts['paged'] ) );
		}

		$paged = get_query_var( 'paged' );

		if ( $paged ) {
			return max( 1, absint( $paged ) );
		}

		$page = get_query_var( 'page' );

		if ( $page ) {
			return max( 1, absint( $page ) );
		}

		return 1;
	}

	/**
	 * Normalize shortcode attributes.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return array{category:string,columns:int,excerpt:int,order:string,orderby:string,pagination:bool,posts:int}
	 */
	private function normalize_atts( array $atts ): array {
		$options = Defaults::settings();
		$posts   = isset( $atts['posts'] ) && '' !== $atts['posts'] ? absint( $atts['posts'] ) : absint( $options['posts_per_page'] );

		if ( isset( $atts['posts_per_page'] ) && '' !== $atts['posts_per_page'] ) {
			$posts = absint( $atts['posts_per_page'] );
		}

		$columns = isset( $atts['columns'] ) && '' !== $atts['columns'] ? absint( $atts['columns'] ) : absint( $options['columns'] );
		$excerpt = isset( $atts['excerpt'] ) && '' !== $atts['excerpt'] ? absint( $atts['excerpt'] ) : absint( $options['excerpt_length'] );
		$order = strtoupper( sanitize_key( (string) ( $atts['order'] ?? 'DESC' ) ) );

		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$orderby = sanitize_key( (string) ( $atts['orderby'] ?? 'date' ) );

		if ( 'id' === $orderby ) {
			$orderby = 'ID';
		}

		if ( ! in_array( $orderby, array( 'date', 'title', 'modified', 'menu_order', 'rand', 'comment_count', 'ID' ), true ) ) {
			$orderby = 'date';
		}

		$category = sanitize_title( (string) ( $atts['category'] ?? '' ) );
		$term     = $this->get_category_by_value( $category );

		if ( null !== $term ) {
			$category = $term->slug;
		}

		return array(
			'category'   => $category,
			'columns'    => max( 1, min( 4, $columns ) ),
			'excerpt'    => max( 0, min( 80, $excerpt ) ),
			'order'      => $order,
			'orderby'    => $orderby,
			'pagination' => $this->string_to_bool( $atts['pagination'] ?? 'yes' ),
			'posts'      => max( 1, min( 48, $posts ) ),
		);
	}

	/**
	 * Get requested category filter value.
	 *
	 * @return string
	 */
	private function get_requested_category(): string {
		if ( ! isset( $_GET[ self::CATEGORY_QUERY_VAR ] ) ) {
			return '';
		}

		return sanitize_title( wp_unslash( $_GET[ self::CATEGORY_QUERY_VAR ] ) );
	}

	/**
	 * Get category term from a slug or ID.
	 *
	 * @param string $value Category value.
	 *
	 * @return \WP_Term|null
	 */
	private function get_category_by_value( string $value ): ?\WP_Term {
		if ( '' === $value ) {
			return null;
		}

		$term = is_numeric( $value ) ? get_category( absint( $value ) ) : get_category_by_slug( $value );

		return $term instanceof \WP_Term ? $term : null;
	}

	/**
	 * Convert shortcode boolean-ish values to bool.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return bool
	 */
	private function string_to_bool( mixed $value ): bool {
		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Load blog assets only when the shortcode renders.
	 *
	 * @return void
	 */
	private function enqueue_assets(): void {
		wp_enqueue_style(
			'idb-core-blog',
			IDB_CORE_URL . 'assets/css/blog.css',
			array(),
			IDB_CORE_VERSION
		);

		wp_enqueue_script(
			'idb-core-blog',
			IDB_CORE_URL . 'assets/js/blog.js',
			array(),
			IDB_CORE_VERSION,
			true
		);
	}
}
