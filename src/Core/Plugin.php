<?php
/**
 * Main plugin bootstrap.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Core;

use IDB\Admin\Settings;
use IDB\Blog\Shortcode;
use IDB\Elementor\Manager as ElementorManager;
use IDB\Frontend\BlogRenderer;
use IDB\ThemeBuilder\Manager as ThemeBuilderManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates plugin lifecycle and module registration.
 */
final class Plugin {

	/**
	 * Minimum supported PHP version.
	 */
	private const MINIMUM_PHP = '8.2';

	/**
	 * Minimum supported WordPress version.
	 */
	private const MINIMUM_WP = '6.7';

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Prevent multiple boot executions.
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * Constructor.
	 */
	private function __construct() {}

	/**
	 * Prevent cloning.
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @return void
	 */
	public function __wakeup(): void {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}

	/**
	 * Get singleton instance.
	 *
	 * @return self
	 */
	public static function instance(): self {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public static function activate(): void {

		if ( ! self::meets_requirements() ) {

			deactivate_plugins( IDB_CORE_BASENAME );

			wp_die(
				esc_html( self::requirements_message() ),
				esc_html__( 'Plugin activation failed', 'iraniandubai-core' ),
				array(
					'back_link' => true,
				)
			);
		}

		add_option(
			IDB_CORE_OPTION_NAME,
			array()
		);

		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate(): void {

		flush_rewrite_rules();
	}

	/**
	 * Boot plugin.
	 *
	 * @return void
	 */
	public function boot(): void {

		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		if ( ! self::meets_requirements() ) {

			add_action(
				'admin_notices',
				array(
					$this,
					'render_requirements_notice',
				)
			);

			return;
		}

		add_action(
			'init',
			array(
				$this,
				'load_textdomain',
			)
		);

		$blog_renderer = new BlogRenderer();

		add_action(
			'wp_ajax_idb_core_blog',
			array(
				$blog_renderer,
				'ajax_render',
			)
		);

		add_action(
			'wp_ajax_nopriv_idb_core_blog',
			array(
				$blog_renderer,
				'ajax_render',
			)
		);

		add_action(
			'save_post',
			array(
				BlogRenderer::class,
				'flush_cache',
			)
		);

		add_action(
			'deleted_post',
			array(
				BlogRenderer::class,
				'flush_cache',
			)
		);

		add_action(
			'transition_post_status',
			array(
				BlogRenderer::class,
				'flush_cache',
			)
		);

		foreach ( $this->modules() as $module ) {
			$module->register();
		}
	}

	/**
	 * Load translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {

		load_plugin_textdomain(
			'iraniandubai-core',
			false,
			dirname( IDB_CORE_BASENAME ) . '/languages'
		);
	}

	/**
	 * Display requirements notice.
	 *
	 * @return void
	 */
	public function render_requirements_notice(): void {

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html( self::requirements_message() )
		);
	}

	/**
	 * Check runtime requirements.
	 *
	 * @return bool
	 */
	private static function meets_requirements(): bool {

		global $wp_version;

		return version_compare(
			PHP_VERSION,
			self::MINIMUM_PHP,
			'>='
		)
		&& isset( $wp_version )
		&& version_compare(
			$wp_version,
			self::MINIMUM_WP,
			'>='
		);
	}

	/**
	 * Requirements failure message.
	 *
	 * @return string
	 */
	private static function requirements_message(): string {

		return __(
			'IranianDubai Core requires PHP 8.2+ and WordPress 6.7+.',
			'iraniandubai-core'
		);
	}

	/**
	 * Register plugin modules.
	 *
	 * @return ModuleInterface[]
	 */
	private function modules(): array {
		return array(
			new Settings(),
			new Shortcode(),
			new ElementorManager(),
			new ThemeBuilderManager(),
		);
	}

}
