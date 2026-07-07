<?php
/**
 * Contract for bootable plugin modules.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the contract for plugin modules.
 */
interface ModuleInterface {
	/**
	 * Register hooks for the module.
	 */
	public function register(): void;
}
