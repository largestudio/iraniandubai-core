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
