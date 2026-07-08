<?php
/**
 * Blog Query Builder.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a standard WP_Query instance for blog listings.
 */
final class Query {

	/**
	 * Build query.
	 *
	 * @param array $args Query arguments.
	 *
	 * @return \WP_Query
	 */
	public static function make( array $args = array() ): \WP_Query {

		$defaults = array(
			'posts_per_page'    => Defaults::SETTINGS['posts_per_page'],
			'category_name'     => '',
			'include_categories' => Defaults::SETTINGS['include_categories'],
			'exclude_categories' => Defaults::SETTINGS['exclude_categories'],
			'include_tags'       => Defaults::SETTINGS['include_tags'],
			'exclude_tags'       => Defaults::SETTINGS['exclude_tags'],
			'author'             => Defaults::SETTINGS['author'],
			'sticky_posts_mode'  => Defaults::SETTINGS['sticky_posts_mode'],
			'offset'             => Defaults::SETTINGS['offset'],
			'orderby'            => Defaults::SETTINGS['orderby'],
			'order'              => Defaults::SETTINGS['order'],
			'paged'              => 1,
			'post_type'          => 'post',
			'post_status'        => 'publish',
			'no_found_rows'      => false,
			's'                  => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$posts_per_page = max( 1, absint( $args['posts_per_page'] ) );
		$paged          = max( 1, absint( $args['paged'] ) );
		$base_offset    = Defaults::clamp_int( $args['offset'], 0, Defaults::OFFSET_MAX );
		$offset         = $base_offset > 0 ? $base_offset + ( $paged - 1 ) * $posts_per_page : 0;
		$sticky_mode    = Defaults::sanitize_sticky_mode( $args['sticky_posts_mode'] );

		$query = array(
			'post_type'           => sanitize_key( (string) $args['post_type'] ),
			'post_status'         => sanitize_key( (string) $args['post_status'] ),
			'posts_per_page'      => $posts_per_page,
			'orderby'             => Defaults::sanitize_orderby( $args['orderby'] ),
			'order'               => Defaults::sanitize_order( $args['order'] ),
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => (bool) $args['no_found_rows'],
		);

		if ( $offset > 0 ) {
			$query['offset'] = $offset;
		}

		$author = absint( $args['author'] );

		if ( $author > 0 ) {
			$query['author'] = $author;
		}

		$search = sanitize_text_field( (string) $args['s'] );

		if ( '' !== $search ) {
			$query['s'] = $search;
		}

		$tax_query = self::build_tax_query( $args );

		if ( ! empty( $tax_query ) ) {
			$query['tax_query'] = $tax_query;
		}

		$sticky_posts = array_filter( array_map( 'absint', get_option( 'sticky_posts', array() ) ) );

		if ( 'only_sticky' === $sticky_mode ) {
			$query['post__in'] = ! empty( $sticky_posts ) ? $sticky_posts : array( 0 );
		}

		if ( 'exclude_sticky' === $sticky_mode && ! empty( $sticky_posts ) ) {
			$query['post__not_in'] = $sticky_posts;
		}

		if ( $base_offset > 0 && empty( $query['no_found_rows'] ) ) {
			$query['idb_core_base_offset'] = $base_offset;

			add_filter( 'found_posts', array( self::class, 'adjust_found_posts_for_offset' ), 10, 2 );
			$wp_query = new \WP_Query( $query );
			remove_filter( 'found_posts', array( self::class, 'adjust_found_posts_for_offset' ), 10 );

			return $wp_query;
		}

		return new \WP_Query( $query );
	}

	/**
	 * Build taxonomy query from safe query args.
	 *
	 * @param array<string,mixed> $args Query arguments.
	 *
	 * @return array<int|string,mixed>
	 */
	private static function build_tax_query( array $args ): array {
		$tax_query = array();

		if ( ! empty( $args['category_name'] ) ) {
			$tax_query[] = array(
				'field'    => 'slug',
				'taxonomy' => 'category',
				'terms'    => array( sanitize_title( (string) $args['category_name'] ) ),
			);
		}

		$include_categories = Defaults::sanitize_id_list( $args['include_categories'] ?? array() );
		$exclude_categories = Defaults::sanitize_id_list( $args['exclude_categories'] ?? array() );
		$include_tags       = Defaults::sanitize_id_list( $args['include_tags'] ?? array() );
		$exclude_tags       = Defaults::sanitize_id_list( $args['exclude_tags'] ?? array() );

		if ( ! empty( $include_categories ) ) {
			$tax_query[] = array(
				'field'    => 'term_id',
				'taxonomy' => 'category',
				'terms'    => $include_categories,
			);
		}

		if ( ! empty( $exclude_categories ) ) {
			$tax_query[] = array(
				'field'    => 'term_id',
				'operator' => 'NOT IN',
				'taxonomy' => 'category',
				'terms'    => $exclude_categories,
			);
		}

		if ( ! empty( $include_tags ) ) {
			$tax_query[] = array(
				'field'    => 'term_id',
				'taxonomy' => 'post_tag',
				'terms'    => $include_tags,
			);
		}

		if ( ! empty( $exclude_tags ) ) {
			$tax_query[] = array(
				'field'    => 'term_id',
				'operator' => 'NOT IN',
				'taxonomy' => 'post_tag',
				'terms'    => $exclude_tags,
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}

		return $tax_query;
	}

	/**
	 * Reduce found posts by the configured base offset.
	 *
	 * @param int       $found_posts Found posts.
	 * @param \WP_Query $query       Query object.
	 *
	 * @return int
	 */
	public static function adjust_found_posts_for_offset( int $found_posts, \WP_Query $query ): int {
		$offset = absint( $query->get( 'idb_core_base_offset' ) );

		if ( 0 === $offset ) {
			return $found_posts;
		}

		return max( 0, $found_posts - $offset );
	}
}
