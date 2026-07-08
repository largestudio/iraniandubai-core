<?php
/**
 * Theme Builder manager.
 *
 * @package IranianDubaiCore
 */

namespace IDB\ThemeBuilder;

use IDB\Core\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots the Theme Builder foundation.
 */
final class Manager implements ModuleInterface {

	/**
	 * Template registry.
	 *
	 * @var TemplateRegistry
	 */
	private TemplateRegistry $registry;

	/**
	 * Location manager.
	 *
	 * @var LocationManager
	 */
	private LocationManager $locations;

	/**
	 * Template loader.
	 *
	 * @var TemplateLoader
	 */
	private TemplateLoader $loader;

	/**
	 * Constructor.
	 *
	 * @param TemplateRegistry|null $registry  Optional template registry.
	 * @param LocationManager|null  $locations Optional location manager.
	 * @param TemplateLoader|null   $loader    Optional template loader.
	 */
	public function __construct(
		?TemplateRegistry $registry = null,
		?LocationManager $locations = null,
		?TemplateLoader $loader = null
	) {
		$this->registry  = $registry ?? new TemplateRegistry();
		$this->locations = $locations ?? new LocationManager( $this->registry );
		$this->loader    = $loader ?? new TemplateLoader( $this->registry );
	}

	/**
	 * Legacy Theme Builder render hooks.
	 *
	 * @var array<string,string>
	 */
	private const LEGACY_RENDER_HOOKS = array(
		'header'  => 'lsos/theme/header',
		'footer'  => 'lsos/theme/footer',
		'single'  => 'lsos/theme/single',
		'archive' => 'lsos/theme/archive',
		'search'  => 'lsos/theme/search',
		'404'     => 'lsos/theme/404',
	);

	/**
	 * Register Theme Builder module.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->register_locations();
		$this->register_hooks();
	}

	/**
	 * Register core and extension template locations.
	 *
	 * @return void
	 */
	private function register_locations(): void {
		$this->locations->register_defaults();

		/**
		 * Fires after core Theme Builder locations are registered.
		 *
		 * Future modules can call:
		 * $locations->register_location( $id, $label, $callback );
		 *
		 * @param LocationManager  $locations Location manager.
		 * @param TemplateRegistry $registry  Template registry.
		 */
		do_action( 'lsos/theme/register_locations', $this->locations, $this->registry );
	}

	/**
	 * Register render hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_action( 'lsos/theme/location', array( $this->loader, 'render' ), 10, 1 );

		foreach ( self::LEGACY_RENDER_HOOKS as $location_id => $hook_name ) {
			add_action(
				$hook_name,
				function () use ( $location_id ): void {
					do_action( 'lsos/theme/location', $location_id );
				}
			);
		}
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
		return $this->locations->register_location( $id, $label, $callback, $priority, $meta );
	}

	/**
	 * Get all registered locations.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,priority:int,meta:array<string,mixed>}>
	 */
	public function get_locations(): array {
		return $this->locations->get_locations();
	}

	/**
	 * Get the template registry.
	 *
	 * @return TemplateRegistry
	 */
	public function get_registry(): TemplateRegistry {
		return $this->registry;
	}

	/**
	 * Get the location manager.
	 *
	 * @return LocationManager
	 */
	public function get_location_manager(): LocationManager {
		return $this->locations;
	}

	/**
	 * Get the template loader.
	 *
	 * @return TemplateLoader
	 */
	public function get_template_loader(): TemplateLoader {
		return $this->loader;
	}
}
