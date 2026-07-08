<?php
/**
 * Plugin uninstall cleanup.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'idb_core_options' );
delete_option( 'idb_core_blog_cache_version' );
