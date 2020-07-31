<?php
/**
 * Mailer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications\Services\Email;

/**
 * Mailer.
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class Mailer {
	/**
	 * Process queue (retries failed messages up to 5 items).
	 *
	 * @return void
	 * @throws \Exception Throws exception if email could not be updated in database.
	 */
	public function process_queue() {
		global $wpdb;

		$query = "
			SELECT
				email_message.*
			FROM
				$wpdb->orbis_email_messages AS email_message
			WHERE
				email_message.is_sent = 0
					AND
				email_message.number_attempts < 5
					AND
				email_message.created_at > DATE_SUB( UTC_TIMESTAMP(), INTERVAL 7 DAY )
			;
		";

		$email_messages = $wpdb->get_results( $query );

		foreach ( $email_messages as $email ) {
			$this->send( $email );
		}
	}

	/**
	 * Send single email message.
	 *
	 * @param object $email_message Email message object.
	 *
	 * @return bool
	 * @throws \Exception Throws exception if email could not be updated in database.
	 */
	public function send( $email_message ) {
		global $wpdb;

		$to_email = $email_message->to_email;

		// Test mode.
		if ( '1' === $email_message->test_mode ) {
			if ( ! \defined( 'ORBIS_NOTIFICATIONS_TEST_EMAIL' ) ) {
				throw new \Exception( 'No test e-mail address configured for email in test mode. Set `ORBIS_NOTIFICATIONS_TEST_EMAIL` constant in WordPress config.' );
			}

			$to_email = ORBIS_NOTIFICATIONS_TEST_EMAIL;
		}

		$headers = str_replace( "\n", "\r\n", $email_message->headers );

		$is_sent = wp_mail( $to_email, $email_message->subject, $email_message->message, $headers );

		// Update.
		$data = array(
			'id'              => $email_message->id,
			'to_email'        => $email_message->to_email,
			'subject'         => $email_message->subject,
			'message'         => $email_message->message,
			'headers'         => $email_message->headers,
			'is_sent'         => $is_sent,
			'number_attempts' => $email_message->number_attempts + 1,
		);

		$format = array(
			'id'              => '%d',
			'to_email'        => '%s',
			'subject'         => '%s',
			'message'         => '%s',
			'headers'         => '%s',
			'is_sent'         => '%d',
			'number_attempts' => '%d',
		);

		$result = $wpdb->update(
			$wpdb->orbis_email_messages,
			array(
				'is_sent'         => $data['is_sent'],
				'number_attempts' => $data['number_attempts'],
			),
			array(
				'id' => $data['id'],
			),
			array(
				'is_sent'         => $format['is_sent'],
				'number_attempts' => $format['number_attempts'],
			),
			array(
				'id' => $format['id'],
			)
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Error updating mail: %s, Data: %s, Format: %s.',
					$wpdb->last_error,
					print_r( $data, true ),
					print_r( $format, true )
				)
			);
		}

		return $is_sent;
	}
}
