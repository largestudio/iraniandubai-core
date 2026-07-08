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
	 * Legacy Theme Builder render hooks and dispatcher methods.
	 *
	 * @var array<string,array{hook:string,method:string}>
	 */
	private const LEGACY_RENDER_HOOKS = array(
		'header'  => array(
			'hook'   => 'lsos/theme/header',
			'method' => 'dispatch_header',
		),
		'footer'  => array(
			'hook'   => 'lsos/theme/footer',
			'method' => 'dispatch_footer',
		),
		'single'  => array(
			'hook'   => 'lsos/theme/single',
			'method' => 'dispatch_single',
		),
		'archive' => array(
			'hook'   => 'lsos/theme/archive',
			'method' => 'dispatch_archive',
		),
		'search'  => array(
			'hook'   => 'lsos/theme/search',
			'method' => 'dispatch_search',
		),
		'404'     => array(
			'hook'   => 'lsos/theme/404',
			'method' => 'dispatch_404',
		),
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

		foreach ( self::LEGACY_RENDER_HOOKS as $hook ) {
			add_action( $hook['hook'], array( $this, $hook['method'] ) );
		}
	}

	/**
	 * Dispatch the legacy header hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_header(): void {
		$this->dispatch_location( 'header' );
	}

	/**
	 * Dispatch the legacy footer hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_footer(): void {
		$this->dispatch_location( 'footer' );
	}

	/**
	 * Dispatch the legacy single hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_single(): void {
		$this->dispatch_location( 'single' );
	}

	/**
	 * Dispatch the legacy archive hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_archive(): void {
		$this->dispatch_location( 'archive' );
	}

	/**
	 * Dispatch the legacy search hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_search(): void {
		$this->dispatch_location( 'search' );
	}

	/**
	 * Dispatch the legacy 404 hook to the generic location hook.
	 *
	 * @return void
	 */
	public function dispatch_404(): void {
		$this->dispatch_location( '404' );
	}

	/**
	 * Dispatch a location through the generic Theme Builder render hook.
	 *
	 * @param string $location_id Location identifier.
	 *
	 * @return void
	 */
	private function dispatch_location( string $location_id ): void {
		do_action( 'lsos/theme/location', $location_id );
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
