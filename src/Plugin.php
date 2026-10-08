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

/**
 * Plugin
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class Plugin {
	/**
	 * @var array<int,Notification>
	 */
	private $notifications;

	/**
	 * Plugin constructor.
	 *
	 * @param string $file Plugin main file.
	 */
	public function __construct( private $file ) {
		// Tables.
		global $wpdb;

		$wpdb->orbis_email_messages  = $wpdb->prefix . 'orbis_email_messages';
		$wpdb->orbis_email_templates = $wpdb->prefix . 'orbis_email_templates';
		$wpdb->orbis_email_tracking  = $wpdb->prefix . 'orbis_email_tracking';

		// Includes.
		include __DIR__ . '/../includes/template.php';

		// Email messages controller.
		( new EmailMessagesController() )->setup();

		if ( is_admin() ) {
			new Admin();
		}

		add_action( 'plugins_loaded', $this->loaded( ... ) );

		add_action( 'admin_init', $this->update( ... ), 5 );
	}

	/**
	 * Plugins loaded.
	 *
	 * @return void
	 */
	public function loaded() {
		// CLI.
		if ( \defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI( $this );
		}

		// Register notifications.
		$this->register_notifications();
	}

	/**
	 * Update.
	 *
	 * @return void
	 */
	public function update() {
		$version = '0.0.2';

		if ( \get_option( 'orbis_notifications_db_version' ) !== $version ) {
			$this->install();

			\update_option( 'orbis_notifications_db_version', $version );
		}
	}

	/**
	 * Install.
	 *
	 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
	 * @return void
	 */
	public function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = <<<SQL
			CREATE TABLE $wpdb->orbis_email_templates (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				code VARCHAR(32) NOT NULL,
				subject VARCHAR(200) NOT NULL,
				message TEXT NOT NULL,
				preheader_text VARCHAR(200) NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY code (code)
			) $charset_collate;
			CREATE TABLE $wpdb->orbis_email_messages (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created_at DATETIME NOT NULL,
				updated_at DATETIME NOT NULL,
				from_email VARCHAR(200) NOT NULL,
				to_email VARCHAR(200) NOT NULL,
				reply_to VARCHAR(200) NOT NULL,
				subject VARCHAR(200) NOT NULL,
				message TEXT NOT NULL,
				headers TEXT NOT NULL,
				is_sent TINYINT(1) NOT NULL,
				number_attempts TINYINT(1) NOT NULL,
				template_id BIGINT(20) UNSIGNED DEFAULT NULL,
				user_id BIGINT(20) UNSIGNED DEFAULT NULL,
				subscription_id BIGINT(20) UNSIGNED DEFAULT NULL,
				company_id BIGINT(20) UNSIGNED DEFAULT NULL,
				contact_id BIGINT(20) UNSIGNED DEFAULT NULL,
				link_key VARCHAR(32) DEFAULT NULL,
				test_mode TINYINT(1) UNSIGNED DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY template_id (template_id),
				KEY user_id (user_id),
				KEY subscription_id (subscription_id),
				KEY contact_id (contact_id)
			) $charset_collate;
			CREATE TABLE $wpdb->orbis_email_tracking (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				email_message_id BIGINT(20) UNSIGNED NOT NULL,
				ip_address VARCHAR(100) NOT NULL,
				user_agent VARCHAR(255) NOT NULL,
				request_time DATETIME(6) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;
			SQL;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_messages );
		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_templates );
		\maybe_convert_table_to_utf8mb4( $wpdb->orbis_email_tracking );

		$this->add_foreign_keys();

		flush_rewrite_rules();
	}

	/**
	 * Add foreign keys.
	 *
	 * `dbDelta` does not support foreign keys, so they are added separately
	 * when they do not exist yet. Foreign keys to tables that do not exist
	 * (yet) are skipped.
	 *
	 * @return void
	 */
	private function add_foreign_keys() {
		global $wpdb;

		$table = $wpdb->orbis_email_messages;

		$subscriptions_table = $wpdb->prefix . 'orbis_subscriptions';

		$foreign_keys = [
			[
				'name'      => $wpdb->prefix . 'orbis_email_messages_ibfk_2',
				'reference' => $wpdb->users,
				'sql'       => <<<SQL
					ALTER TABLE $table
						ADD CONSTRAINT {$wpdb->prefix}orbis_email_messages_ibfk_2
						FOREIGN KEY ( user_id ) REFERENCES $wpdb->users ( ID );
					SQL,
			],
			[
				'name'      => $wpdb->prefix . 'orbis_email_messages_ibfk_4',
				'reference' => $subscriptions_table,
				'sql'       => <<<SQL
					ALTER TABLE $table
						ADD CONSTRAINT {$wpdb->prefix}orbis_email_messages_ibfk_4
						FOREIGN KEY ( subscription_id ) REFERENCES $subscriptions_table ( id );
					SQL,
			],
			[
				'name'      => $wpdb->prefix . 'orbis_email_messages_ibfk_5',
				'reference' => $wpdb->orbis_email_templates,
				'sql'       => <<<SQL
					ALTER TABLE $table
						ADD CONSTRAINT {$wpdb->prefix}orbis_email_messages_ibfk_5
						FOREIGN KEY ( template_id ) REFERENCES $wpdb->orbis_email_templates ( id );
					SQL,
			],
		];

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared -- `dbDelta` does not support foreign keys, the queries are built from table names only.
		foreach ( $foreign_keys as $foreign_key ) {
			$reference_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $wpdb->esc_like( $foreign_key['reference'] ) ) );

			if ( null === $reference_exists ) {
				continue;
			}

			$exists = $wpdb->get_var(
				$wpdb->prepare(
					<<<'SQL'
						SELECT
							CONSTRAINT_NAME
						FROM
							information_schema.TABLE_CONSTRAINTS
						WHERE
							CONSTRAINT_SCHEMA = DATABASE()
								AND
							TABLE_NAME = %s
								AND
							CONSTRAINT_NAME = %s
								AND
							CONSTRAINT_TYPE = 'FOREIGN KEY'
						;
						SQL,
					$table,
					$foreign_key['name']
				)
			);

			if ( null !== $exists ) {
				continue;
			}

			$wpdb->query( $foreign_key['sql'] );
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Register notifications.
	 *
	 * @return void
	 */
	private function register_notifications() {
		$this->notifications = [];

		// Subscription support quota notifications for various quota threshold percentages.
		$thresholds = [
			[
				'min' => 50,
				'max' => 75,
			],
			[
				'min' => 75,
				'max' => 100,
			],
			[
				'min' => 100,
				'max' => 1000,
			],
		];

		foreach ( $thresholds as $threshold ) {
			$this->notifications[] = new SubscriptionSupportQuotaNotification(
				[
					'min_threshold' => $threshold['min'],
					'max_threshold' => $threshold['max'],
				]
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
