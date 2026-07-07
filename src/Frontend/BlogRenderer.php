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
	private const SEARCH_QUERY_VAR   = 'idb_search';
	private const AJAX_ACTION        = 'idb_core_blog';
	private const NONCE_ACTION       = 'idb_core_blog';
	private const SCRIPT_OBJECT      = 'idbCoreBlog';

	/**
	 * Front-end URL being rendered during an AJAX request.
	 *
	 * @var string
	 */
	private string $ajax_url = '';

	/**
	 * Request-local normalized attribute cache.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private array $normalized_atts_cache = array();

	/**
	 * Request-local post excerpt cache.
	 *
	 * @var array<string,string>
	 */
	private array $excerpt_cache = array();

	/**
	 * Request-local post read time cache.
	 *
	 * @var array<int,int>
	 */
	private array $read_time_cache = array();

	/**
	 * Request-local post category cache.
	 *
	 * @var array<int,\WP_Term|null>
	 */
	private array $post_category_cache = array();

	/**
	 * Request-local category lookup cache.
	 *
	 * @var array<string,\WP_Term|null>
	 */
	private array $category_lookup_cache = array();

	/**
	 * Request-local filter term cache.
	 *
	 * @var array<string,\WP_Term[]>
	 */
	private array $filter_categories_cache = array();

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
	 * Render blog posts for AJAX pagination requests.
	 *
	 * @return void
	 */
	public function ajax_render(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		$atts = $this->get_ajax_request_atts();
		$url  = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

		$this->ajax_url = $url;

		$atts['paged'] = $this->get_page_from_url( $url );

		$this->apply_url_filters_to_request( $url );

		wp_send_json_success(
			array(
				'html' => $this->render( $atts ),
				'url'  => $url,
			)
		);
	}

	/**
	 * Get AJAX-safe shortcode attributes for the rendered wrapper.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function get_ajax_attributes( array $atts ): string {
		$attributes = wp_json_encode( $this->normalize_atts( $atts ) );

		return is_string( $attributes ) ? $attributes : '{}';
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
		$cache_key = $post_id . ':' . max( 1, $length );

		if ( isset( $this->excerpt_cache[ $cache_key ] ) ) {
			return $this->excerpt_cache[ $cache_key ];
		}

		$excerpt = get_the_excerpt( $post_id );

		if ( '' === $excerpt ) {
			$excerpt = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		}

		$this->excerpt_cache[ $cache_key ] = wp_trim_words( $excerpt, max( 1, $length ), '...' );

		return $this->excerpt_cache[ $cache_key ];
	}

	/**
	 * Estimate reading time for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return int
	 */
	public function get_read_time( int $post_id ): int {
		if ( isset( $this->read_time_cache[ $post_id ] ) ) {
			return $this->read_time_cache[ $post_id ];
		}

		$content = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
		$matches = array();

		preg_match_all( '/[\p{L}\p{N}]+/u', $content, $matches );

		$this->read_time_cache[ $post_id ] = max( 1, (int) ceil( count( $matches[0] ) / 200 ) );

		return $this->read_time_cache[ $post_id ];
	}

	/**
	 * Get the first assigned category for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return \WP_Term|null
	 */
	public function get_category( int $post_id ): ?\WP_Term {
		if ( array_key_exists( $post_id, $this->post_category_cache ) ) {
			return $this->post_category_cache[ $post_id ];
		}

		$categories = get_the_category( $post_id );

		if ( empty( $categories ) || ! isset( $categories[0] ) || ! $categories[0] instanceof \WP_Term ) {
			$this->post_category_cache[ $post_id ] = null;

			return null;
		}

		$this->post_category_cache[ $post_id ] = $categories[0];

		return $this->post_category_cache[ $post_id ];
	}

	/**
	 * Get post thumbnail markup.
	 *
	 * @param int  $post_id     Post ID.
	 * @param bool $is_priority Whether the image is the first visible blog image.
	 *
	 * @return string
	 */
	public function get_image( int $post_id, bool $is_priority = false ): string {
		if ( ! has_post_thumbnail( $post_id ) ) {
			return '<span class="idb-blog-card__image idb-blog-card__image--placeholder" aria-hidden="true"></span>';
		}

		$attributes = array(
			'class'    => 'idb-blog-card__image',
			'loading'  => $is_priority ? 'eager' : 'lazy',
			'decoding' => 'async',
			'sizes'    => '(min-width: 1024px) 50vw, 100vw',
		);

		if ( $is_priority ) {
			$attributes['fetchpriority'] = 'high';
		}

		return (string) get_the_post_thumbnail(
			$post_id,
			'large',
			$attributes
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
		$cache_key  = $normalized['category'];

		if ( isset( $this->filter_categories_cache[ $cache_key ] ) ) {
			return $this->filter_categories_cache[ $cache_key ];
		}

		$args = array(
			'hide_empty'             => true,
			'taxonomy'               => 'category',
			'update_term_meta_cache' => false,
		);

		if ( '' !== $normalized['category'] ) {
			$category = $this->get_category_by_value( $normalized['category'] );

			if ( null !== $category ) {
				$args['include'] = array( $category->term_id );
			}
		}

		$terms = get_terms( $args );

		$this->filter_categories_cache[ $cache_key ] = is_array( $terms ) ? $terms : array();

		return $this->filter_categories_cache[ $cache_key ];
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
		$url = $this->get_frontend_base_url();
		$search = $this->get_requested_search();

		if ( '' !== $search ) {
			$url = add_query_arg( self::SEARCH_QUERY_VAR, $search, $url );
		}

		if ( '' === $category_slug ) {
			return $url;
		}

		return add_query_arg( self::CATEGORY_QUERY_VAR, sanitize_title( $category_slug ), $url );
	}

	/**
	 * Get selected search query.
	 *
	 * @return string
	 */
	public function get_selected_search(): string {
		return $this->get_requested_search();
	}

	/**
	 * Get search form action URL.
	 *
	 * @return string
	 */
	public function get_search_url(): string {
		return $this->get_frontend_base_url();
	}

	/**
	 * Get paginate_links base URL.
	 *
	 * @param int $big Placeholder page number.
	 *
	 * @return string
	 */
	public function get_pagination_base( int $big ): string {
		if ( '' === $this->ajax_url ) {
			return str_replace(
				$big,
				'%#%',
				esc_url( get_pagenum_link( $big ) )
			);
		}

		return str_replace(
			$big,
			'%#%',
			esc_url(
				add_query_arg(
					'paged',
					$big,
					$this->get_frontend_base_url()
				)
			)
		);
	}

	/**
	 * Get paginate_links format argument.
	 *
	 * @return string
	 */
	public function get_pagination_format(): string {
		return '' === $this->ajax_url ? '?paged=%#%' : '';
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
		$requested_search   = $this->get_requested_search();

		return Query::make(
			array(
				'posts_per_page' => $normalized['posts'],
				'paged'          => $this->get_current_page( $atts ),
				'order'          => $normalized['order'],
				'orderby'        => $normalized['orderby'],
				'category_name'  => '' !== $requested_category ? $requested_category : $normalized['category'],
				'no_found_rows'  => ! $normalized['pagination'],
				's'              => $requested_search,
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
		$cache_key = md5( (string) wp_json_encode( $atts ) );

		if ( isset( $this->normalized_atts_cache[ $cache_key ] ) ) {
			return $this->normalized_atts_cache[ $cache_key ];
		}

		$options = Defaults::settings();
		$posts   = isset( $atts['posts'] ) && '' !== $atts['posts'] ? absint( $atts['posts'] ) : absint( $options['posts_per_page'] );

		if ( isset( $atts['posts_per_page'] ) && '' !== $atts['posts_per_page'] ) {
			$posts = absint( $atts['posts_per_page'] );
		}

		$columns = $this->get_int_attribute( $atts, 'columns', $options['columns'] );
		$excerpt = $this->get_int_attribute( $atts, 'excerpt', $options['excerpt_length'] );
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

		$this->normalized_atts_cache[ $cache_key ] = array(
			'category'   => $category,
			'columns'    => Defaults::clamp_int( $columns, Defaults::COLUMNS_MIN, Defaults::COLUMNS_MAX ),
			'excerpt'    => max( 0, min( 80, $excerpt ) ),
			'order'      => $order,
			'orderby'    => $orderby,
			'pagination' => $this->string_to_bool( $atts['pagination'] ?? 'yes' ),
			'posts'      => Defaults::clamp_int( $posts, Defaults::POSTS_PER_PAGE_MIN, Defaults::POSTS_PER_PAGE_MAX ),
		);

		return $this->normalized_atts_cache[ $cache_key ];
	}

	/**
	 * Get an integer shortcode/widget attribute with a fallback.
	 *
	 * @param array<string,mixed> $atts    Attributes.
	 * @param string              $key     Attribute key.
	 * @param int                 $default Default value.
	 *
	 * @return int
	 */
	private function get_int_attribute( array $atts, string $key, int $default ): int {
		return isset( $atts[ $key ] ) && '' !== $atts[ $key ] ? absint( $atts[ $key ] ) : $default;
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
	 * Get requested search value.
	 *
	 * @return string
	 */
	private function get_requested_search(): string {
		if ( ! isset( $_GET[ self::SEARCH_QUERY_VAR ] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_GET[ self::SEARCH_QUERY_VAR ] ) );
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

		if ( array_key_exists( $value, $this->category_lookup_cache ) ) {
			return $this->category_lookup_cache[ $value ];
		}

		$term = is_numeric( $value ) ? get_category( absint( $value ) ) : get_category_by_slug( $value );

		$this->category_lookup_cache[ $value ] = $term instanceof \WP_Term ? $term : null;

		return $this->category_lookup_cache[ $value ];
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

		wp_localize_script(
			'idb-core-blog',
			self::SCRIPT_OBJECT,
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => self::AJAX_ACTION,
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
			)
		);
	}

	/**
	 * Read and sanitize AJAX shortcode attributes.
	 *
	 * @return array<string,mixed>
	 */
	private function get_ajax_request_atts(): array {
		if ( ! isset( $_POST['atts'] ) ) {
			return array();
		}

		$raw_atts = wp_unslash( $_POST['atts'] );
		$atts     = json_decode( is_string( $raw_atts ) ? $raw_atts : '', true );

		if ( ! is_array( $atts ) ) {
			return array();
		}

		return array(
			'category'       => sanitize_title( (string) ( $atts['category'] ?? '' ) ),
			'columns'        => absint( $atts['columns'] ?? 0 ),
			'excerpt'        => absint( $atts['excerpt'] ?? 0 ),
			'order'          => sanitize_key( (string) ( $atts['order'] ?? 'DESC' ) ),
			'orderby'        => sanitize_key( (string) ( $atts['orderby'] ?? 'date' ) ),
			'pagination'     => ! empty( $atts['pagination'] ) ? 'yes' : 'no',
			'posts'          => absint( $atts['posts'] ?? 0 ),
			'posts_per_page' => absint( $atts['posts'] ?? 0 ),
		);
	}

	/**
	 * Read pagination from a URL while preserving non-JS URL formats.
	 *
	 * @param string $url Target pagination URL.
	 *
	 * @return int
	 */
	private function get_page_from_url( string $url ): int {
		$parts = wp_parse_url( $url );

		if ( ! is_array( $parts ) ) {
			return 1;
		}

		if ( isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_args );

			foreach ( array( 'paged', 'page' ) as $page_key ) {
				if ( isset( $query_args[ $page_key ] ) ) {
					return max( 1, absint( $query_args[ $page_key ] ) );
				}
			}
		}

		if ( isset( $parts['path'] ) && preg_match( '#/page/([0-9]+)/?#', $parts['path'], $matches ) ) {
			return max( 1, absint( $matches[1] ) );
		}

		return 1;
	}

	/**
	 * Get the front-end listing URL without pagination state.
	 *
	 * @return string
	 */
	private function get_frontend_base_url(): string {
		if ( '' === $this->ajax_url ) {
			return remove_query_arg( array( self::CATEGORY_QUERY_VAR, self::SEARCH_QUERY_VAR, 'paged', 'page' ), get_pagenum_link( 1 ) );
		}

		$url = remove_query_arg( array( self::CATEGORY_QUERY_VAR, self::SEARCH_QUERY_VAR, 'paged', 'page' ), $this->ajax_url );

		return preg_replace( '#/page/[0-9]+/?#', '/', $url ) ?? $url;
	}

	/**
	 * Make target URL filters available to existing query helpers.
	 *
	 * @param string $url Target pagination or filter URL.
	 *
	 * @return void
	 */
	private function apply_url_filters_to_request( string $url ): void {
		$parts    = wp_parse_url( $url );
		$category = '';
		$search   = '';

		if ( is_array( $parts ) && isset( $parts['query'] ) ) {
			parse_str( $parts['query'], $query_args );

			if ( isset( $query_args[ self::CATEGORY_QUERY_VAR ] ) ) {
				$category = sanitize_title( (string) $query_args[ self::CATEGORY_QUERY_VAR ] );
			}

			if ( isset( $query_args[ self::SEARCH_QUERY_VAR ] ) ) {
				$search = sanitize_text_field( (string) $query_args[ self::SEARCH_QUERY_VAR ] );
			}
		}

		if ( '' === $category ) {
			unset( $_GET[ self::CATEGORY_QUERY_VAR ] );
		} else {
			$_GET[ self::CATEGORY_QUERY_VAR ] = $category;
		}

		if ( '' === $search ) {
			unset( $_GET[ self::SEARCH_QUERY_VAR ] );
			return;
		}

		$_GET[ self::SEARCH_QUERY_VAR ] = $search;
	}
}
