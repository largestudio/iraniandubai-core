<?php
/**
 * Theme Builder template loader.
 *
 * @package IranianDubaiCore
 */

namespace IDB\ThemeBuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dispatches rendering for registered Theme Builder locations.
 */
final class TemplateLoader {

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
	 * Render a registered location if it has a callback.
	 *
	 * @param string $location_id Location identifier.
	 *
	 * @return void
	 */
	public function render( string $location_id ): void {
		$callback = $this->registry->get_callback( $location_id );

		if ( null === $callback ) {
			return;
		}

		call_user_func( $callback, $location_id, $this->registry );
	}
}
