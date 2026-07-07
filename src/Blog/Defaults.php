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
	 * Default blog settings.
	 */
	public const SETTINGS = array(
		'posts_per_page' => 6,
		'excerpt_length' => 24,
		'columns'        => 2,
	);

	/**
	 * Get saved settings merged with defaults.
	 *
	 * @return array{posts_per_page:int,excerpt_length:int,columns:int}
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
	 * @return array{posts_per_page:int,excerpt_length:int,columns:int}
	 */
	public static function sanitize( array $settings ): array {
		return array(
			'posts_per_page' => self::clamp_int(
				$settings['posts_per_page'] ?? self::SETTINGS['posts_per_page'],
				self::POSTS_PER_PAGE_MIN,
				self::POSTS_PER_PAGE_MAX
			),
			'excerpt_length' => self::clamp_int(
				$settings['excerpt_length'] ?? self::SETTINGS['excerpt_length'],
				self::EXCERPT_LENGTH_MIN,
				self::EXCERPT_LENGTH_MAX
			),
			'columns'        => self::clamp_int(
				$settings['columns'] ?? self::SETTINGS['columns'],
				self::COLUMNS_MIN,
				self::COLUMNS_MAX
			),
		);
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
