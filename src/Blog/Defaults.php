<?php
/**
 * Blog default settings.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Blog;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides one source of truth for blog defaults.
 */
final class Defaults {

	/**
	 * Minimum and maximum listing sizes.
	 */
	public const POSTS_PER_PAGE_MIN = 1;
	public const POSTS_PER_PAGE_MAX = 48;

	/**
	 * Minimum and maximum saved excerpt lengths.
	 */
	public const EXCERPT_LENGTH_MIN = 5;
	public const EXCERPT_LENGTH_MAX = 100;

	/**
	 * Minimum and maximum column counts.
	 */
	public const COLUMNS_MIN = 1;
	public const COLUMNS_MAX = 4;

	/**
	 * Maximum safe query offset.
	 */
	public const OFFSET_MAX = 500;

	/**
	 * Supported blog layout styles.
	 */
	public const LAYOUTS = array(
		'grid',
		'list',
		'magazine',
		'minimal',
	);

	/**
	 * Supported sticky posts modes.
	 */
	public const STICKY_MODES = array(
		'include_all',
		'only_sticky',
		'exclude_sticky',
	);

	/**
	 * Supported query orderby values.
	 */
	public const ORDERBY_VALUES = array(
		'date',
		'modified',
		'title',
		'comment_count',
		'rand',
		'menu_order',
		'ID',
	);

	/**
	 * Supported query order values.
	 */
	public const ORDER_VALUES = array(
		'DESC',
		'ASC',
	);

	/**
	 * Default blog settings.
	 */
	public const SETTINGS = array(
		'posts_per_page'     => 6,
		'excerpt_length'     => 24,
		'columns'            => 2,
		'layout'             => 'grid',
		'include_categories' => array(),
		'exclude_categories' => array(),
		'include_tags'       => array(),
		'exclude_tags'       => array(),
		'author'             => 0,
		'sticky_posts_mode'  => 'include_all',
		'offset'             => 0,
		'orderby'            => 'date',
		'order'              => 'DESC',
	);

	/**
	 * Get saved settings merged with defaults.
	 *
	 * @return array<string,mixed>
	 */
	public static function settings(): array {
		$options = get_option( IDB_CORE_OPTION_NAME, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return self::sanitize( wp_parse_args( $options, self::SETTINGS ) );
	}

	/**
	 * Sanitize blog settings.
	 *
	 * @param array<string,mixed> $settings Raw settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function sanitize( array $settings ): array {
		return array(
			'posts_per_page'     => self::clamp_int(
				$settings['posts_per_page'] ?? self::SETTINGS['posts_per_page'],
				self::POSTS_PER_PAGE_MIN,
				self::POSTS_PER_PAGE_MAX
			),
			'excerpt_length'     => self::clamp_int(
				$settings['excerpt_length'] ?? self::SETTINGS['excerpt_length'],
				self::EXCERPT_LENGTH_MIN,
				self::EXCERPT_LENGTH_MAX
			),
			'columns'            => self::clamp_int(
				$settings['columns'] ?? self::SETTINGS['columns'],
				self::COLUMNS_MIN,
				self::COLUMNS_MAX
			),
			'layout'             => self::sanitize_layout( $settings['layout'] ?? self::SETTINGS['layout'] ),
			'include_categories' => self::sanitize_id_list( $settings['include_categories'] ?? self::SETTINGS['include_categories'] ),
			'exclude_categories' => self::sanitize_id_list( $settings['exclude_categories'] ?? self::SETTINGS['exclude_categories'] ),
			'include_tags'       => self::sanitize_id_list( $settings['include_tags'] ?? self::SETTINGS['include_tags'] ),
			'exclude_tags'       => self::sanitize_id_list( $settings['exclude_tags'] ?? self::SETTINGS['exclude_tags'] ),
			'author'             => absint( $settings['author'] ?? self::SETTINGS['author'] ),
			'sticky_posts_mode'  => self::sanitize_sticky_mode( $settings['sticky_posts_mode'] ?? self::SETTINGS['sticky_posts_mode'] ),
			'offset'             => self::clamp_int(
				$settings['offset'] ?? self::SETTINGS['offset'],
				0,
				self::OFFSET_MAX
			),
			'orderby'            => self::sanitize_orderby( $settings['orderby'] ?? self::SETTINGS['orderby'] ),
			'order'              => self::sanitize_order( $settings['order'] ?? self::SETTINGS['order'] ),
		);
	}

	/**
	 * Sanitize a blog layout value.
	 *
	 * @param mixed $layout Raw layout value.
	 *
	 * @return string
	 */
	public static function sanitize_layout( mixed $layout ): string {
		$layout = sanitize_key( (string) $layout );

		return in_array( $layout, self::LAYOUTS, true ) ? $layout : self::SETTINGS['layout'];
	}

	/**
	 * Sanitize a list of term IDs.
	 *
	 * @param mixed $ids Raw IDs.
	 *
	 * @return int[]
	 */
	public static function sanitize_id_list( mixed $ids ): array {
		if ( ! is_array( $ids ) ) {
			$ids = array();
		}

		$ids = array_filter( array_map( 'absint', $ids ) );

		return array_values( array_unique( $ids ) );
	}

	/**
	 * Sanitize sticky posts mode.
	 *
	 * @param mixed $mode Raw mode.
	 *
	 * @return string
	 */
	public static function sanitize_sticky_mode( mixed $mode ): string {
		$mode = sanitize_key( (string) $mode );

		return in_array( $mode, self::STICKY_MODES, true ) ? $mode : self::SETTINGS['sticky_posts_mode'];
	}

	/**
	 * Sanitize orderby value.
	 *
	 * @param mixed $orderby Raw orderby.
	 *
	 * @return string
	 */
	public static function sanitize_orderby( mixed $orderby ): string {
		$orderby = sanitize_key( (string) $orderby );

		if ( 'id' === $orderby ) {
			$orderby = 'ID';
		}

		return in_array( $orderby, self::ORDERBY_VALUES, true ) ? $orderby : self::SETTINGS['orderby'];
	}

	/**
	 * Sanitize order value.
	 *
	 * @param mixed $order Raw order.
	 *
	 * @return string
	 */
	public static function sanitize_order( mixed $order ): string {
		$order = strtoupper( sanitize_key( (string) $order ) );

		return in_array( $order, self::ORDER_VALUES, true ) ? $order : self::SETTINGS['order'];
	}

	/**
	 * Clamp a positive integer to a configured range.
	 *
	 * @param mixed $value Raw value.
	 * @param int   $min   Minimum allowed value.
	 * @param int   $max   Maximum allowed value.
	 *
	 * @return int
	 */
	public static function clamp_int( mixed $value, int $min, int $max ): int {
		return max( $min, min( $max, absint( $value ) ) );
	}
}
