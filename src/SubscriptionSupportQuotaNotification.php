<?php
/**
 * Subscription support quota notification
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications;

use InvalidArgumentException;
use WP_CLI;

/**
 * Subscription support quota notification
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class SubscriptionSupportQuotaNotification extends Notification {
	/**
	 * Min quota threshold percentage.
	 *
	 * @var int
	 */
	private $min_threshold;

	/**
	 * Max quota threshold percentage.
	 *
	 * @var int
	 */
	private $max_threshold;

	/**
	 * Dry run?
	 *
	 * @var bool
	 */
	public $dry_run;

	/**
	 * Subscription quota notification constructor.
	 *
	 * @param array<string,array|int|string> $options Options.
	 */
	public function __construct( $options = array() ) {
		parent::__construct( $options );

		// Check for quota threshold option.
		if ( ! \array_key_exists( 'min_threshold', $options ) || null === $options['min_threshold'] ) {
			throw new InvalidArgumentException( __( 'Missing required `min_threshold` option for support quota notification.' ) );
		}

		// Check for quota threshold option.
		if ( ! \array_key_exists( 'max_threshold', $options ) || null === $options['max_threshold'] ) {
			throw new InvalidArgumentException( __( 'Missing required `max_threshold` option for support quota notification.' ) );
		}

		$this->min_threshold = \intval( $options['min_threshold'] );
		$this->max_threshold = \intval( $options['max_threshold'] );
		$this->dry_run       = \array_key_exists( 'dry_run', $options ) ? $options['dry_run'] : false;
	}

	/**
	 * Run notification.
	 *
	 * @return void
	 */
	public function run() {
		$events = $this->get_events();

		foreach ( $events as $event ) {
			/*
			 * Compose email.
			 */
			$email = new Services\Email\Email();

			$email->set_subscription_id( $event->subscription_id );
			$email->set_company_id( $event->company_id );
			$email->set_user_id( $event->user_id );

			// Template.
			$template_code = sprintf( 'wp-support-update-%s-percent', $this->min_threshold );

			$template = $email->load_template( $template_code );

			// Check if email has already been sent.
			if ( $event->email_template_id === $template->id ) {
				continue;
			}

			$link_key = \wp_generate_password( 32, false, false );

			$replacements = array(
				'{company_id}'              => $event->company_id,
				'{company_name}'            => $event->company_name,
				'{subscription_id}'         => $event->subscription_id,
				'{subscription_name}'       => $event->subscription_name,
				'{product_id}'              => $event->product_id,
				'{product_name}'            => $event->product_name,
				'{product_time_per_year}'   => ( $event->product_time_per_year > 0 ? ( $event->product_time_per_year / HOUR_IN_SECONDS ) : 0 ),
				'{registered_time}'         => ( $event->registered_time > 0 ? ( $event->registered_time / HOUR_IN_SECONDS ) : 0 ),
				'{time_percentage}'         => $event->time_percentage,
				'{user_id}'                 => $event->user_id,
				'{user_display_name}'       => $event->user_display_name,
				'{user_email}'              => $event->user_email,
				'{link_key}'                => $link_key,
			);

			// Data.
			$subject        = \strtr( $email->get_subject(), $replacements );
			$message        = \strtr( $email->get_message(), $replacements );

			$preheader_text = $email->get_preheader_text();

			if ( null !== $preheader_text ) {
				$preheader_text = \strtr( $preheader_text, $replacements );
			}

			$from = \get_option( 'orbis_notifications_from_address' );

			if ( empty( $from ) ) {
				$from = \get_option( 'admin_email' );
			}

			$email->set_to( $event->user_email );
			$email->set_from( $from );
			$email->set_subject( $subject );
			$email->set_message( $message );
			$email->set_preheader_text( $preheader_text );
			$email->set_link_key( $link_key );

			// Reply to.
			$reply_to = \get_option( 'orbis_notifications_reply_to_address' );

			if ( ! empty( $reply_to ) ) {
				$email->set_reply_to( $reply_to );
			}

			// Additional recipient.
			if ( 100 === $this->min_threshold && $this->max_threshold > 100 ) {
				$additional_recipient_user_id = \get_option( 'orbis_notifications_additional_recipient_user_id' );

				$user = \get_userdata( $additional_recipient_user_id );

				if ( $user ) {
					$email->set_bcc( $user->user_email );
				}
			}

			// Wrap message in HTML template.
			$email->wrap_message_in_template();

			// Print info message.
			/* translators: 3: subscription ID, 4: company name, 5: product name, 6: time percentage */
			$format = __( 'Subscription #%3$s (%4$s - %5$s - %6$s) reached %7$s%% of support quota → ', 'orbis-notifications' );

			// Check if template for notification was found.
			if ( null === $template ) {
				/* translators: 1: min threshold, 2: user email */
				$format .= __( 'no template for %1$s%% notification to %2$s', 'orbis-notifications' );
			} else {
				/* translators: 1: min threshold, 2: user email */
				$format .= __( '%1$s%% notification to %2$s', 'orbis-notifications' );
			}

			$this->log(
				\sprintf(
					$format,
					$this->min_threshold,
					$event->user_email,
					$event->subscription_id,
					$event->company_name,
					$event->product_name,
					$event->subscription_name,
					$event->time_percentage
				)
			);

			// Skip saving email if dry run.
			if ( $this->dry_run ) {
				continue;
			}

			// Save email in database.
			try {
				$email->save();
			} catch ( \Exception $e ) {
				printf(
					\__( 'Unable to send "%1$s" to %2$s, error: "%3$s"', 'orbis-notifications' ) . \PHP_EOL,
					\esc_html( $subject ),
					\esc_html( $event->user_email ),
					$e->getMessage()
				);
			}
		}
	}

	/**
	 * Get exceeded quota subscriptions.
	 *
	 * @return array<int,object>
	 */
	private function get_events() {
		global $wpdb;

		// Find subscriptions within support quota threshold.
		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT
					exceeded_subscription.subscription_id       AS subscription_id,
					exceeded_subscription.subscription_name     AS subscription_name,
					exceeded_subscription.product_id            AS product_id,
					exceeded_subscription.product_name          AS product_name,
					exceeded_subscription.product_time_per_year AS product_time_per_year,
					exceeded_subscription.registered_time       AS registered_time,
					exceeded_subscription.time_percentage       AS time_percentage,
					company.id                                  AS company_id,
					company.name                                AS company_name,
					user.ID                                     AS user_id,
					user.display_name                           AS user_display_name,
					user.user_email                             AS user_email,
					email_message.created_at                    AS email_created_at,
					email_message.template_id                   AS email_template_id
				FROM
					(
						SELECT
							subscription.id                                                                        AS subscription_id,
							subscription.name                                                                      AS subscription_name,
							subscription.company_id                                                                AS company_id,
							subscription.activation_date                                                           AS activation_date,
							product.id                                                                             AS product_id,
							product.name                                                                           AS product_name,
							product.time_per_year                                                                  AS product_time_per_year,
							SUM( timesheet.number_seconds )                                                        AS registered_time,
							FLOOR( ROUND( ( 100 / product.time_per_year ) * SUM( timesheet.number_seconds ), 1 ) ) AS time_percentage
						FROM
							$wpdb->orbis_subscriptions AS subscription
								INNER JOIN
							$wpdb->orbis_products AS product
									ON subscription.type_id = product.id
								LEFT JOIN
							$wpdb->orbis_timesheets AS timesheet
									ON (
										timesheet.subscription_id = subscription.id
											AND
										timesheet.date > DATE_ADD( subscription.activation_date, INTERVAL TIMESTAMPDIFF( YEAR, subscription.activation_date, NOW() ) YEAR )
											AND
										timesheet.date < DATE_SUB( NOW(), INTERVAL 1 WEEK )
									)
						WHERE
							product.type = 'wp_support'
								AND
							(
								subscription.cancel_date IS NULL
									OR
								subscription.expiration_date > NOW()
							)
						GROUP BY
							subscription.id
						HAVING
							MAX( timesheet.date ) > DATE_SUB( NOW(), INTERVAL 1 MONTH )
								AND
							CAST( ( 100 / MIN( product.time_per_year ) * SUM( timesheet.number_seconds ) ) AS UNSIGNED ) >= %d
								AND
							CAST( ( 100 / MIN( product.time_per_year ) * SUM( timesheet.number_seconds ) ) AS UNSIGNED ) < %d
					) AS exceeded_subscription
						INNER JOIN
					$wpdb->orbis_companies AS company
							ON exceeded_subscription.company_id = company.id
						LEFT JOIN
					{$wpdb->prefix}p2p AS user_company_p2p
							ON (
								user_company_p2p.p2p_type = 'orbis_users_to_companies'
									AND
								user_company_p2p.p2p_to = company.post_id
							)
						LEFT JOIN
					$wpdb->users AS user
							ON user_company_p2p.p2p_from = user.ID
						LEFT JOIN
					$wpdb->orbis_email_messages AS email_message
						ON (
	    					email_message.subscription_id = exceeded_subscription.subscription_id
	    						AND
	    					email_message.user_id = user.ID
	    						AND
	    					email_message.created_at =
	    						(
		        					SELECT
		            					created_at
		        					FROM
		            					$wpdb->orbis_email_messages
		        					WHERE
		            					subscription_id = exceeded_subscription.subscription_id
		                					AND
		            					user_id = user.ID
		                					AND
		            					created_at > DATE_ADD( exceeded_subscription.activation_date, INTERVAL TIMESTAMPDIFF( YEAR, exceeded_subscription.activation_date, NOW( ) ) YEAR )
		        					ORDER BY
		        						created_at DESC
									LIMIT 1
								)
						)
				GROUP BY
					user.ID, exceeded_subscription.subscription_id;
				",
				$this->min_threshold,
				$this->max_threshold
			)
		);

		return $subscriptions;
	}

	/**
	 * Log.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	private function log( $message ) {
		if ( \method_exists( WP_CLI::class, 'log' ) ) {
			WP_CLI::log( $message );
		}
	}
}
