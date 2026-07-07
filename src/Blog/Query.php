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
			'posts_per_page' => Defaults::SETTINGS['posts_per_page'],
			'category_name'  => '',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'paged'          => 1,
			'post_type'      => 'post',
			'post_status'    => 'publish',
		);

		$args = wp_parse_args( $args, $defaults );

		$order = strtoupper( sanitize_text_field( (string) $args['order'] ) );

		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = $defaults['order'];
		}

		$orderby = sanitize_key( (string) $args['orderby'] );

		if ( 'id' === $orderby ) {
			$orderby = 'ID';
		}

		if ( ! in_array( $orderby, array( 'date', 'title', 'modified', 'menu_order', 'rand', 'comment_count', 'ID' ), true ) ) {
			$orderby = $defaults['orderby'];
		}

		$query = array(
			'post_type'           => sanitize_key( (string) $args['post_type'] ),
			'post_status'         => sanitize_key( (string) $args['post_status'] ),
			'posts_per_page'      => max( 1, absint( $args['posts_per_page'] ) ),
			'orderby'             => $orderby,
			'order'               => $order,
			'paged'               => max( 1, absint( $args['paged'] ) ),
			'ignore_sticky_posts' => true,
		);

		if ( ! empty( $args['category_name'] ) ) {
			$query['category_name'] = sanitize_title( (string) $args['category_name'] );
		}

		return new \WP_Query( $query );
	}
}
