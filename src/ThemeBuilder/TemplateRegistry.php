<?php
/**
 * Theme Builder template registry.
 *
 * @package IranianDubaiCore
 */

namespace IDB\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores Theme Builder template locations.
 */
final class TemplateRegistry {

	/**
	 * Registered template locations.
	 *
	 * @var array<string,array{id:string,label:string,callback:callable|null,priority:int,meta:array<string,mixed>}>
	 */
	private array $locations = array();

	/**
	 * Register a template location.
	 *
	 * @param string              $id       Location identifier.
	 * @param string              $label    Human-readable location label.
	 * @param callable|null       $callback Optional render callback.
	 * @param int                 $priority Sort priority.
	 * @param array<string,mixed> $meta     Future-friendly location metadata.
	 *
	 * @return bool True when the location was registered.
	 */
	public function register_location(
		string $id,
		string $label,
		?callable $callback = null,
		int $priority = 10,
		array $meta = array()
	): bool {
		$id       = $this->sanitize_id( $id );
		$label    = trim( $label );
		$priority = (int) $priority;

		if ( '' === $id || '' === $label ) {
			return false;
		}

		$this->locations[ $id ] = array(
			'id'       => $id,
			'label'    => $label,
			'callback' => $callback,
			'priority' => $priority,
			'meta'     => $meta,
		);

		return true;
	}

	/**
	 * Check whether a location exists.
	 *
	 * @param string $id Location identifier.
	 *
	 * @return bool
	 */
	public function has_location( string $id ): bool {
		return isset( $this->locations[ $this->sanitize_id( $id ) ] );
	}

	/**
	 * Get one registered location.
	 *
	 * @param string $id Location identifier.
	 *
	 * @return array{id:string,label:string,callback:callable|null,priority:int,meta:array<string,mixed>}|null
	 */
	public function get_location( string $id ): ?array {
		$id = $this->sanitize_id( $id );

		return $this->locations[ $id ] ?? null;
	}

	/**
	 * Get all registered locations.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,priority:int,meta:array<string,mixed>}>
	 */
	public function get_locations(): array {
		$locations = $this->locations;

		uasort(
			$locations,
			static function ( array $first, array $second ): int {
				return $first['priority'] <=> $second['priority'];
			}
		);

		return $locations;
	}

	/**
	 * Get a registered render callback.
	 *
	 * @param string $id Location identifier.
	 *
	 * @return callable|null
	 */
	public function get_callback( string $id ): ?callable {
		$location = $this->get_location( $id );

		if ( null === $location || ! is_callable( $location['callback'] ) ) {
			return null;
		}

		return $location['callback'];
	}

	/**
	 * Sanitize a location identifier.
	 *
	 * @param string $id Raw location identifier.
	 *
	 * @return string
	 */
	private function sanitize_id( string $id ): string {
		return sanitize_key( $id );
	}
}
