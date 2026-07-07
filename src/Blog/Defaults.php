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
			'posts_per_page' => max( 1, min( 48, absint( $settings['posts_per_page'] ?? self::SETTINGS['posts_per_page'] ) ) ),
			'excerpt_length' => max( 5, min( 100, absint( $settings['excerpt_length'] ?? self::SETTINGS['excerpt_length'] ) ) ),
			'columns'        => max( 1, min( 4, absint( $settings['columns'] ?? self::SETTINGS['columns'] ) ) ),
		);
	}
}
