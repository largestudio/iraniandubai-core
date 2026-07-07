<?php
/**
 * WordPress admin integration.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Admin;

use IDB\Core\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the WordPress admin menu and settings page.
 */
final class Admin implements ModuleInterface {
	private const PAGE_SLUG = 'idb-core';

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . IDB_CORE_BASENAME, array( $this, 'add_settings_link' ) );
	}

	/**
	 * Add the plugin settings screen.
	 *
	 * @return void
	 */
	public function add_menu(): void {
		add_menu_page(
			__( 'IranianDubai Core', 'iraniandubai-core' ),
			__( 'IranianDubai', 'iraniandubai-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			'dashicons-admin-site-alt3',
			58
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'idb_core_settings',
			IDB_CORE_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => array(),
			)
		);

		add_settings_section(
			'idb_core_general_section',
			__( 'General', 'iraniandubai-core' ),
			static function (): void {
				echo '<p>' . esc_html__( 'Plugin settings will be added incrementally in future sprints.', 'iraniandubai-core' ) . '</p>';
			},
			self::PAGE_SLUG
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param mixed $options Raw settings.
	 *
	 * @return array<string,mixed>
	 */
public function sanitize_options( mixed $options ): array {

	if ( ! is_array( $options ) ) {
		return array();
	}

	return $options;

}

	/**
	 * Render admin settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'iraniandubai-core' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Core functionality for the IranianDubai website. Built to work alongside WoodMart and Elementor Pro without modifying the active theme.', 'iraniandubai-core' ); ?></p>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'idb_core_settings' );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add a direct settings link on the Plugins screen.
	 *
	 * @param string[] $links Existing action links.
	 *
	 * @return string[]
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ),
			esc_html__( 'Settings', 'iraniandubai-core' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}
