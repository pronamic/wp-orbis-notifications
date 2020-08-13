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

		orbis_register_table( 'orbis_email_templates' );
		orbis_register_table( 'orbis_email_messages' );
		orbis_register_table( 'orbis_email_tracking' );

		// Includes.
		$this->plugin_include( 'includes/template.php' );

		// Email messages controller.
		( new EmailMessagesController() )->setup();
	}

	/**
	 * Plugins loaded.
	 */
	public function loaded() {
		/**
		 * CLI.
		 */
		if ( \defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI( $this );
		}

		// Register notifications.
		$this->register_notifications();
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
