<?php
/**
 * Notifications add-on for Orbis.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications;

use Orbis_Plugin;

/**
 * Plugin
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class Plugin extends Orbis_Plugin {
	/**
	 * @var array<int,Notification>
	 */
	private $notifications;

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Plugin main file.
	 */
	public function __construct( $file ) {
		parent::__construct( $file );

		$this->set_name( 'orbis_notifications' );
		$this->set_db_version( '0.0.1' );

		// Tables.
		orbis_register_table( 'orbis_email_messages' );
		orbis_register_table( 'orbis_email_templates' );
		orbis_register_table( 'orbis_email_tracking' );

		// Includes.
		$this->plugin_include( 'includes/template.php' );

		// Email messages controller.
		( new EmailMessagesController() )->setup();

		if ( is_admin() ) {
			new Admin();
		}
	}

	/**
	 * Plugins loaded.
	 */
	public function loaded() {
		// Load translations.
		$this->load_textdomain( 'orbis-notifications', '/languages/' );

		// CLI.
		if ( \defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI( $this );
		}

		// Register notifications.
		$this->register_notifications();
	}

	/**
	 * Install.
	 */
	public function install() {
		// Tables
		orbis_install_table( 'orbis_email_messages', '
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL,
			`from_email` varchar(200) NOT NULL,
			`to_email` varchar(200) NOT NULL,
			`reply_to` varchar(200) NOT NULL,
			`subject` varchar(200) NOT NULL,
			`message` text NOT NULL,
			`headers` text NOT NULL,
			`is_sent` tinyint(1) NOT NULL,
			`number_attempts` tinyint(1) NOT NULL,
			`template_id` bigint(20) unsigned DEFAULT NULL,
			`user_id` bigint(20) unsigned DEFAULT NULL,
			`subscription_id` bigint(20) unsigned DEFAULT NULL,
			`company_id` bigint(20) unsigned DEFAULT NULL,
			`link_key` varchar(32) DEFAULT NULL,
			`test_mode` tinyint(1) unsigned DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `template_id` (`template_id`),
			KEY `user_id` (`user_id`),
			KEY `subscription_id` (`subscription_id`),
			CONSTRAINT `wp_orbis_email_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `wp_users` (`ID`),
			CONSTRAINT `wp_orbis_email_messages_ibfk_4` FOREIGN KEY (`subscription_id`) REFERENCES `wp_orbis_subscriptions` (`id`),
			CONSTRAINT `wp_orbis_email_messages_ibfk_5` FOREIGN KEY (`template_id`) REFERENCES `wp_orbis_email_templates` (`id`)
		' );

		orbis_install_table( 'orbis_email_templates', '
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL,
			`code` varchar(32) NOT NULL,
			`subject` varchar(200) NOT NULL,
			`message` text NOT NULL,
			`preheader_text` varchar(200) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `code` (`code`)
		' );

		orbis_install_table( 'orbis_email_tracking', '
			`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`email_message_id` bigint(20) unsigned NOT NULL,
			`ip_address` varchar(100) NOT NULL,
			`user_agent` varchar(255) NOT NULL,
			`request_time` datetime(6) NOT NULL,
			PRIMARY KEY (`id`)
		' );

		// Maybe convert
		global $wpdb;

		maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_messages );
		maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_templates );
		maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_tracking );

		parent::install();
	}

	/**
	 * Register notifications.
	 *
	 * @return void
	 */
	private function register_notifications() {
		$this->notifications = array();

		// Subscription support quota notifications for various quota threshold percentages.
		$thresholds = array(
			array( 'min' => 50, 'max' => 75 ),
			array( 'min' => 75, 'max' => 100 ),
			array( 'min' => 100, 'max' => 1000 ),
		);

		foreach ( $thresholds as $threshold ) {
			$this->notifications[] = new SubscriptionSupportQuotaNotification(
				array(
					'min_threshold' => $threshold['min'],
					'max_threshold' => $threshold['max'],
				)
			);
		}
	}

	/**
	 * Get notifications.
	 *
	 * @return array<int,Notification>
	 */
	public function get_notifications() {
		return $this->notifications;
	}
}
