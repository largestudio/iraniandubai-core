<?php
/**
 * Elementor integration manager.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor;

use IDB\Core\ModuleInterface;
use IDB\Elementor\Contracts\WidgetInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers IranianDubai Elementor widgets when Elementor is active.
 */
final class Manager implements ModuleInterface {
	/**
	 * Register Elementor hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_elementor_active() ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register widgets with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function register_widgets( \Elementor\Widgets_Manager $widgets_manager ): void {
		$widgets = array(
			new Widget(),
			new BlogHeroWidget(),
			new BlogGridWidget(),
		);

		foreach ( $widgets as $widget ) {
			if ( $widget instanceof WidgetInterface ) {
				$widgets_manager->register( $widget );
			}
		}
	}

	/**
	 * Check if Elementor is active and loaded.
	 *
	 * @return bool
	 */
	private function is_elementor_active(): bool {
		return did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) || class_exists( '\Elementor\Plugin' );
	}
}
