<?php
/**
 * Content contract.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the minimum shape for future LSOS content objects.
 */
interface ContentInterface {

	/**
	 * Get the stable content identifier.
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Get the content type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string;

	/**
	 * Get the content context identifier.
	 *
	 * @return string
	 */
	public function get_context(): string;

	/**
	 * Get structured content data.
	 *
	 * @return array<string,mixed>
	 */
	public function get_data(): array;
}
