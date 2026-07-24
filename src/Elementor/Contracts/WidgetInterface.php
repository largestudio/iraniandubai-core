<?php
/**
 * Elementor widget contract.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Identifies a widget that can be registered with Elementor.
 */
interface WidgetInterface {
	/**
	 * Get the stable Elementor widget identifier.
	 *
	 * @return string
	 */
	public function get_name(): string;
}
