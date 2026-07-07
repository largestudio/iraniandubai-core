<?php
/**
 * Lightweight PSR-4 autoloader for the IranianDubai Core namespace.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers plugin classes without requiring Composer in production.
 */
final class Autoloader {
	/**
	 * Namespace prefix handled by this autoloader.
	 */
	private const PREFIX = 'IDB\\';

	/**
	 * Register the autoload callback.
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'autoload' ) );
	}

	/**
	 * Load a class file for the plugin namespace.
	 *
	 * @param string $class Fully qualified class name.
	 */
	public static function autoload( string $class ): void {
		if ( 0 !== strpos( $class, self::PREFIX ) ) {
			return;
		}

		$relative_class = substr( $class, strlen( self::PREFIX ) );
		$relative_path  = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';
		$file           = IDB_CORE_PATH . 'src' . DIRECTORY_SEPARATOR . $relative_path;

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
