<?php
/**
 * Theme Builder location manager.
 *
 * @package IranianDubaiCore
 */

namespace IDB\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates supported Theme Builder template locations.
 */
final class LocationManager {

	/**
	 * Template registry.
	 *
	 * @var TemplateRegistry
	 */
	private TemplateRegistry $registry;

	/**
	 * Constructor.
	 *
	 * @param TemplateRegistry $registry Template registry.
	 */
	public function __construct( TemplateRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * Register core template locations.
	 *
	 * @return void
	 */
	public function register_defaults(): void {
		$this->register_location( 'header', __( 'Header', 'iraniandubai-core' ) );
		$this->register_location( 'footer', __( 'Footer', 'iraniandubai-core' ) );
		$this->register_location( 'single', __( 'Single', 'iraniandubai-core' ) );
		$this->register_location( 'archive', __( 'Archive', 'iraniandubai-core' ) );
		$this->register_location( 'search', __( 'Search', 'iraniandubai-core' ) );
		$this->register_location( '404', __( '404', 'iraniandubai-core' ) );
	}

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
		return $this->registry->register_location( $id, $label, $callback, $priority, $meta );
	}

	/**
	 * Get all registered locations.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,priority:int,meta:array<string,mixed>}>
	 */
	public function get_locations(): array {
		return $this->registry->get_locations();
	}

	/**
	 * Get the underlying registry.
	 *
	 * @return TemplateRegistry
	 */
	public function get_registry(): TemplateRegistry {
		return $this->registry;
	}
}
