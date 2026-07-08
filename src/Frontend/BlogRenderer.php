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
	private const CACHE_VERSION_KEY  = 'idb_core_blog_cache_version';
	private const CACHE_TTL          = 900;
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
		$this->enqueue_assets();

		$cache_key = $this->get_cache_key( $atts );

		if ( '' !== $cache_key ) {
			$cached = get_transient( $cache_key );

			if ( is_string( $cached ) ) {
				return $cached;
			}
		}

		$query = $this->get_query( $atts );

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

		$output = (string) ob_get_clean();

		if ( '' !== $cache_key && '' !== $output ) {
			set_transient( $cache_key, $output, self::CACHE_TTL );
		}

		return $output;
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
	 * Invalidate cached blog renderer output.
	 *
	 * @return void
	 */
	public static function flush_cache( mixed ...$args ): void {
		update_option( self::CACHE_VERSION_KEY, (string) time(), false );
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
	 * Get localized reading time text.
	 *
	 * @param int $minutes Estimated reading time in minutes.
	 *
	 * @return string
	 */
	public function get_read_time_text( int $minutes ): string {
		$minutes = max( 1, absint( $minutes ) );

		if ( $this->is_persian_locale() ) {
			return sprintf(
				/* translators: %d: Estimated reading time in minutes. */
				__( '%d دقیقه مطالعه', 'iraniandubai-core' ),
				$minutes
			);
		}

		return sprintf(
			/* translators: %d: Estimated reading time in minutes. */
			_n( '%d min read', '%d min read', $minutes, 'iraniandubai-core' ),
			$minutes
		);
	}

	/**
	 * Get localized read more button text.
	 *
	 * @return string
	 */
	public function get_read_more_text(): string {
		if ( $this->is_persian_locale() ) {
			return __( 'ادامه مطلب ...', 'iraniandubai-core' );
		}

		return __( 'Read more', 'iraniandubai-core' );
	}

	/**
	 * Get localized display date for a post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public function get_display_date( int $post_id ): string {
		$timestamp = (int) get_post_time( 'U', true, $post_id );

		if ( ! $timestamp ) {
			return get_the_date( '', $post_id );
		}

		if ( ! $this->is_persian_locale() ) {
			return get_the_date( '', $post_id );
		}

		$format = 'Y/m/d';

		foreach ( array( 'jdate', 'parsidate', 'gregorian_to_jalali' ) as $function_name ) {
			if ( function_exists( $function_name ) ) {
				return $this->get_external_jalali_date( $function_name, $format, $timestamp );
			}
		}

		return $this->format_jalali_date( $format, $timestamp );
	}

	/**
	 * Get text direction for the displayed post date.
	 *
	 * @return string
	 */
	public function get_display_date_direction(): string {
		return $this->is_persian_locale() ? 'ltr' : 'auto';
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
		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $thumbnail_id ) {
			return '<span class="idb-blog-card__image idb-blog-card__image--placeholder" aria-hidden="true"></span>';
		}

		$alt = trim( (string) get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true ) );

		if ( '' === $alt ) {
			$alt = get_the_title( $post_id );
		}

		if ( '' === $alt ) {
			$alt = __( 'Blog post image', 'iraniandubai-core' );
		}

		$attributes = array(
			'class'    => 'idb-blog-card__image',
			'alt'      => $alt,
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
	 * Render Schema.org JSON-LD for the current blog listing.
	 *
	 * @param \WP_Query $query Blog posts query.
	 *
	 * @return string
	 */
	public function get_schema( \WP_Query $query ): string {
		if ( empty( $query->posts ) ) {
			return '';
		}

		$items    = array();
		$position = 1;

		foreach ( $query->posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			$post_schema = array(
				'@type'         => 'BlogPosting',
				'headline'      => get_the_title( $post ),
				'url'           => get_permalink( $post ),
				'datePublished' => get_post_time( DATE_W3C, true, $post ),
				'dateModified'  => get_post_modified_time( DATE_W3C, true, $post ),
			);

			$image = get_the_post_thumbnail_url( $post, 'large' );

			if ( is_string( $image ) && '' !== $image ) {
				$post_schema['image'] = $image;
			}

			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'item'     => $post_schema,
			);

			++$position;
		}

		if ( empty( $items ) ) {
			return '';
		}

		$schema = wp_json_encode(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'ItemList',
				'itemListElement' => $items,
			),
			JSON_HEX_TAG
			| JSON_HEX_AMP
			| JSON_HEX_APOS
			| JSON_HEX_QUOT
			| JSON_UNESCAPED_SLASHES
			| JSON_UNESCAPED_UNICODE
		);

		if ( ! is_string( $schema ) ) {
			return '';
		}

		return '<script type="application/ld+json">' . $schema . '</script>';
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
	 * Check whether the active locale is Persian.
	 *
	 * @return bool
	 */
	private function is_persian_locale(): bool {
		return str_starts_with( get_locale(), 'fa' ) || str_starts_with( determine_locale(), 'fa' );
	}

	/**
	 * Format a date with a site-provided Jalali function.
	 *
	 * @param string $function_name Jalali function name.
	 * @param string $format        Date format.
	 * @param int    $timestamp     Unix timestamp.
	 *
	 * @return string
	 */
	private function get_external_jalali_date( string $function_name, string $format, int $timestamp ): string {
		if ( 'gregorian_to_jalali' === $function_name ) {
			$jalali = gregorian_to_jalali(
				(int) wp_date( 'Y', $timestamp ),
				(int) wp_date( 'n', $timestamp ),
				(int) wp_date( 'j', $timestamp )
			);

			if ( is_array( $jalali ) && isset( $jalali[0], $jalali[1], $jalali[2] ) ) {
				return $this->format_jalali_parts( $format, (int) $jalali[0], (int) $jalali[1], (int) $jalali[2], $timestamp );
			}
		}

		$date = $function_name( $format, $timestamp );

		return is_string( $date ) && '' !== $date ? $date : $this->format_jalali_date( $format, $timestamp );
	}

	/**
	 * Format a date using the internal Gregorian-to-Jalali converter.
	 *
	 * @param string $format    Date format.
	 * @param int    $timestamp Unix timestamp.
	 *
	 * @return string
	 */
	private function format_jalali_date( string $format, int $timestamp ): string {
		$jalali = $this->gregorian_to_jalali(
			(int) wp_date( 'Y', $timestamp ),
			(int) wp_date( 'n', $timestamp ),
			(int) wp_date( 'j', $timestamp )
		);

		return $this->format_jalali_parts( $format, $jalali[0], $jalali[1], $jalali[2], $timestamp );
	}

	/**
	 * Format Jalali date parts with a small subset of WordPress date tokens.
	 *
	 * @param string $format    Date format.
	 * @param int    $year      Jalali year.
	 * @param int    $month     Jalali month.
	 * @param int    $day       Jalali day.
	 * @param int    $timestamp Unix timestamp.
	 *
	 * @return string
	 */
	private function format_jalali_parts( string $format, int $year, int $month, int $day, int $timestamp ): string {
		$months = array(
			1  => __( 'فروردین', 'iraniandubai-core' ),
			2  => __( 'اردیبهشت', 'iraniandubai-core' ),
			3  => __( 'خرداد', 'iraniandubai-core' ),
			4  => __( 'تیر', 'iraniandubai-core' ),
			5  => __( 'مرداد', 'iraniandubai-core' ),
			6  => __( 'شهریور', 'iraniandubai-core' ),
			7  => __( 'مهر', 'iraniandubai-core' ),
			8  => __( 'آبان', 'iraniandubai-core' ),
			9  => __( 'آذر', 'iraniandubai-core' ),
			10 => __( 'دی', 'iraniandubai-core' ),
			11 => __( 'بهمن', 'iraniandubai-core' ),
			12 => __( 'اسفند', 'iraniandubai-core' ),
		);
		$short_months = array(
			1  => __( 'فرو', 'iraniandubai-core' ),
			2  => __( 'ارد', 'iraniandubai-core' ),
			3  => __( 'خرد', 'iraniandubai-core' ),
			4  => __( 'تیر', 'iraniandubai-core' ),
			5  => __( 'مرد', 'iraniandubai-core' ),
			6  => __( 'شهر', 'iraniandubai-core' ),
			7  => __( 'مهر', 'iraniandubai-core' ),
			8  => __( 'آبا', 'iraniandubai-core' ),
			9  => __( 'آذر', 'iraniandubai-core' ),
			10 => __( 'دی', 'iraniandubai-core' ),
			11 => __( 'بهم', 'iraniandubai-core' ),
			12 => __( 'اسف', 'iraniandubai-core' ),
		);
		$replacements = array(
			'Y' => (string) $year,
			'y' => substr( (string) $year, -2 ),
			'F' => $months[ $month ],
			'M' => $short_months[ $month ],
			'm' => str_pad( (string) $month, 2, '0', STR_PAD_LEFT ),
			'n' => (string) $month,
			'd' => str_pad( (string) $day, 2, '0', STR_PAD_LEFT ),
			'j' => (string) $day,
			'l' => wp_date( 'l', $timestamp ),
			'D' => wp_date( 'D', $timestamp ),
		);

		return strtr( $format, $replacements );
	}

	/**
	 * Convert Gregorian date parts to Jalali date parts.
	 *
	 * @param int $gy Gregorian year.
	 * @param int $gm Gregorian month.
	 * @param int $gd Gregorian day.
	 *
	 * @return array{0:int,1:int,2:int}
	 */
	private function gregorian_to_jalali( int $gy, int $gm, int $gd ): array {
		$g_days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		$j_days_in_month = array( 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29 );
		$gy -= 1600;
		$gm -= 1;
		$gd -= 1;
		$g_day_no = 365 * $gy + (int) floor( ( $gy + 3 ) / 4 ) - (int) floor( ( $gy + 99 ) / 100 ) + (int) floor( ( $gy + 399 ) / 400 );

		for ( $i = 0; $i < $gm; ++$i ) {
			$g_day_no += $g_days_in_month[ $i ];
		}

		if ( $gm > 1 && ( ( 0 === $gy % 4 && 0 !== $gy % 100 ) || 0 === $gy % 400 ) ) {
			++$g_day_no;
		}

		$g_day_no += $gd;
		$j_day_no = $g_day_no - 79;
		$j_np = (int) floor( $j_day_no / 12053 );
		$j_day_no %= 12053;
		$jy = 979 + 33 * $j_np + 4 * (int) floor( $j_day_no / 1461 );
		$j_day_no %= 1461;

		if ( $j_day_no >= 366 ) {
			$jy += (int) floor( ( $j_day_no - 1 ) / 365 );
			$j_day_no = ( $j_day_no - 1 ) % 365;
		}

		for ( $i = 0; $i < 11 && $j_day_no >= $j_days_in_month[ $i ]; ++$i ) {
			$j_day_no -= $j_days_in_month[ $i ];
		}

		return array( $jy, $i + 1, $j_day_no + 1 );
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
	 * Build a safe transient cache key for public blog output.
	 *
	 * @param array<string,mixed> $atts Shortcode attributes.
	 *
	 * @return string
	 */
	private function get_cache_key( array $atts ): string {
		if ( ! $this->can_cache_render() ) {
			return '';
		}

		$cache_parts = array(
			'version'  => $this->get_cache_version(),
			'plugin'   => IDB_CORE_VERSION,
			'locale'   => determine_locale(),
			'rtl'      => is_rtl(),
			'base_url' => $this->get_frontend_base_url(),
			'category' => $this->get_requested_category(),
			'search'   => $this->get_requested_search(),
			'paged'    => $this->get_current_page( $atts ),
			'atts'     => $this->normalize_atts( $atts ),
		);

		return 'idb_blog_' . md5( (string) wp_json_encode( $cache_parts ) );
	}

	/**
	 * Check whether the current request is safe to cache.
	 *
	 * @return bool
	 */
	private function can_cache_render(): bool {
		if ( is_user_logged_in() ) {
			return false;
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}

		if ( is_preview() ) {
			return false;
		}

		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the current blog cache version.
	 *
	 * @return string
	 */
	private function get_cache_version(): string {
		$version = get_option( self::CACHE_VERSION_KEY, '1' );

		return is_scalar( $version ) ? (string) $version : '1';
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
