<?php
/*
Plugin Name: Orbis Notifications
Plugin URI: http://www.pronamic.eu/plugins/orbis-notifications/
Description: The Orbis Notifications plugin extends your Orbis environment with notifications.

Version: 1.0.0
Requires at least: 5.2

Author: Pronamic
Author URI: http://www.pronamic.eu/

Text Domain: orbis_notifications
Domain Path: /languages/

License: Copyright (c) Pronamic

GitHub URI: https://github.com/wp-orbis/wp-orbis-notifications
*/

/**
 * Autoload
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Bootstrap
 */
function orbis_notifications_bootstrap() {
	global $orbis_notifications_plugin;

	$orbis_notifications_plugin = new Pronamic\WordPress\Orbis\Notifications\Plugin( __FILE__ );
}

\add_action( 'orbis_bootstrap', 'orbis_notifications_bootstrap' );
