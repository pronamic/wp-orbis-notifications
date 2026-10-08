<?php
/**
 * Orbis Notifications
 *
 * @author    Pronamic
 * @copyright 2020-2026 Pronamic
 * @license   Proprietary
 * @package   Pronamic\WordPress\Orbis\Notifications
 *
 * @wordpress-plugin
 * Plugin Name:       Orbis Notifications
 * Plugin URI:        https://www.pronamic.eu/plugins/orbis-notifications/
 * Description:       The Orbis Notifications plugin extends your Orbis environment with notifications.
 * Version:           1.0.0
 * Requires at least: 7.1
 * Requires PHP:      8.3
 * Requires Plugins:  orbis-contacts, orbis-subscriptions, orbis-timesheets
 * Author:            Pronamic
 * Author URI:        https://www.pronamic.eu/
 * Text Domain:       orbis-notifications
 * Domain Path:       /languages/
 * License:           Copyright (c) Pronamic
 * GitHub URI:        https://github.com/wp-orbis/wp-orbis-notifications
 */

/**
 * Autoload
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * Bootstrap
 */
new Pronamic\WordPress\Orbis\Notifications\Plugin( __FILE__ );
