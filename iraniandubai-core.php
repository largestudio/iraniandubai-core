<?php
/**
 * Plugin Name: IranianDubai Core
 * Plugin URI: https://iraniandubai.com
 * Description: Core Plugin for IranianDubai Website.
 * Version: 1.0.0-rc.1
 * Author: Large Studio
 * Author URI: https://iraniandubai.com
 * Requires PHP: 8.2
 * Requires at least: 6.7
 * Tested up to: 6.7
 * Text Domain: iraniandubai-core
 * Domain Path: /languages
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IDB_CORE_VERSION', '1.0.0-rc.1' );
define( 'IDB_CORE_FILE', __FILE__ );
define( 'IDB_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'IDB_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'IDB_CORE_BASENAME', plugin_basename( __FILE__ ) );
define( 'IDB_CORE_OPTION_NAME', 'idb_core_options' );

require_once IDB_CORE_PATH . 'src/Core/Autoloader.php';

IDB\Core\Autoloader::register();

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_register_style(
			'idb-blog',
			IDB_CORE_URL . 'assets/css/blog.css',
			array(),
			IDB_CORE_VERSION
		);
	}
);

register_activation_hook( __FILE__, array( IDB\Core\Plugin::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( IDB\Core\Plugin::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		IDB\Core\Plugin::instance()->boot();
	}
);
