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
	}

	/**
	 * Run notification.
	 *
	 * @param array<string,array|bool|int|string> $args Arguments.
	 * @return void
	 */
	public function run( $args = array() ) {
		$events = $this->get_events();

		foreach ( $events as $event ) {
			\printf(
				__( 'Sending %1$s%% notification for subscription %2$s to %3$s.', 'orbis-notifications' ) . \PHP_EOL,
				$this->min_threshold,
				$event['subscription_id']
			);

			if ( isset( $args['dry_run'] ) && true === $args['dry_run'] ) {
				continue;
			}

			// @todo send email notifications.
		}
	}

	/**
	 * Get exceeded quota subscriptions.
	 *
	 * @return array<int,int>
	 */
	private function get_events() {
		global $wpdb;

		// Find subscriptions within support quota threshold.
		$query = "
			SELECT
				company.id                                                                 AS company_id,
				company.name                                                               AS company_name,
				subscription.id                                                            AS subscription_id,
				subscription.name                                                          AS subscription_name,
				product.id                                                                 AS product_id,
				product.name                                                               AS product_name,
				product.time_per_year                                                      AS product_time_per_year,
				SUM( timesheet.number_seconds )                                            AS registered_time,
				FLOOR( ( 100 / product.time_per_year * SUM( timesheet.number_seconds ) ) ) AS time_percentage,
				user.ID                                                                    AS user_id,
				user.display_name                                                          AS user_display_name,
				user.user_email                                                            AS user_email
			FROM
				$wpdb->orbis_subscriptions AS subscription
					INNER JOIN
				$wpdb->orbis_companies AS company
						ON subscription.company_id = company.id
					INNER JOIN
				$wpdb->orbis_subscription_products AS product
						ON subscription.type_id = product.id
					LEFT JOIN
				$wpdb->orbis_timesheets AS timesheet
						ON (
							timesheet.subscription_id = subscription.id
								AND
							timesheet.date > DATE_ADD( subscription.activation_date, INTERVAL TIMESTAMPDIFF( YEAR, subscription.activation_date, NOW() ) YEAR )
						)
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
			WHERE
				product.name IN (
					'WordPress onderhoud XS',
					'WordPress onderhoud S',
					'WordPress onderhoud M'
				)
					AND
				(
					subscription.cancel_date IS NULL
						OR
					subscription.expiration_date > NOW()
				)
			GROUP BY
				subscription.id
			HAVING
				MAX( timesheet.date ) > DATE_SUB( NOW( ), INTERVAL 1 MONTH )
					AND
				CAST( ( 100 / MIN( product.time_per_year ) * SUM( timesheet.number_seconds ) ) AS UNSIGNED ) >= %d
					AND				
				CAST( ( 100 / MIN( product.time_per_year ) * SUM( timesheet.number_seconds ) ) AS UNSIGNED ) < %d";

		$query = $wpdb->prepare(
			$query,
			$this->min_threshold,
			$this->max_threshold
		);

		$subscriptions = $wpdb->get_results( $query );

		return $subscriptions;
	}
}
