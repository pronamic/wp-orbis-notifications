<?php
/**
 * Notifications CLI.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications;

use Pronamic\WordPress\Orbis\Notifications\Services\Email\Mailer;

/**
 * CLI
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class CLI {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * CLI constructor.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Execute all notifications.
		\WP_CLI::add_command(
			'orbis notifications run',
			function( $args, $assoc_args ) {
				$args = array(
					'dry_run' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false ),
				);

				$this->execute_all_notifications( $args );
			},
			array(
				'shortdesc' => 'Execute all registered notifications.',
			)
		);

		// Execute subscription support quota notification.
		\WP_CLI::add_command(
			'orbis notifications subscription-support-quota-exceeded',
			function( $args, $assoc_args ) {
				$options = array(
					'min_threshold' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'min-threshold', null ),
					'max_threshold' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'max-threshold', null ),
					'dry_run' => \WP_CLI\Utils\get_flag_value( $assoc_args, 'dry-run', false ),
				);

				$this->execute_subscription_support_quota_notification( $options );
			},
			array(
				'shortdesc' => 'Execute a subscription quota exceeded notification for the given quota threshold percentage.',
			)
		);

		// Process mailer queue.
		\WP_CLI::add_command(
			'orbis mailer process-queue',
			function( $args, $assoc_args ) {
				$mailer = new Mailer();

				$mailer->process_queue();
			},
			array(
				'shortdesc' => 'Process mailer queue.',
			)
		);
	}

	/**
	 * Execute all registered notifications.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	private function execute_all_notifications( $args ) {
		$notifications = $this->plugin->get_notifications();

		$dry_run = \array_key_exists( 'dry_run', $args ) ? $args['dry_run'] : false;

		foreach ( $notifications as $notification ) {
			$notification->dry_run = $dry_run;

			$notification->run();
		}
	}

	/**
	 * Execute subscription support quota notification.
	 *
	 * @param array $options Options.
	 * @return void
	 */
	private function execute_subscription_support_quota_notification( $options ) {
		$notification = new SubscriptionSupportQuotaNotification(
			array(
				'min_threshold' => $options['min_threshold'],
				'max_threshold' => $options['max_threshold'],
				'dry_run'       => $options['dry_run'],
			)
		);

		$notification->run();
	}
}
