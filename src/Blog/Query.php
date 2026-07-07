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
			'posts'       => 6,
			'category'    => '',
			'orderby'     => 'date',
			'order'       => 'DESC',
			'offset'      => 0,
			'paged'       => 1,
			'post_type'   => 'post',
			'post_status' => 'publish',
		);

		$args = wp_parse_args( $args, $defaults );

		$query = array(
			'post_type'           => sanitize_key( $args['post_type'] ),
			'post_status'         => sanitize_key( $args['post_status'] ),
			'posts_per_page'      => absint( $args['posts'] ),
			'orderby'             => sanitize_key( $args['orderby'] ),
			'order'               => strtoupper( sanitize_text_field( $args['order'] ) ),
			'offset'              => absint( $args['offset'] ),
			'paged'               => max( 1, absint( $args['paged'] ) ),
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $args['category'] ) ) {
			$query['category_name'] = sanitize_title( $args['category'] );
		}

		return new \WP_Query( $query );
	}
}