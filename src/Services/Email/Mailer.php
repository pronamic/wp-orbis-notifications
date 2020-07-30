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
	 * Send.
	 *
	 * @param int $email_id Email ID.
	 *
	 * @return bool
	 * @throws \Exception Throws exception if email could not be updated in database.
	 */
	public function send( $email_id ) {
		// @todo enable sending email notifications.
		return;

		global $wpdb;

		$email = new Email( $email_id );

		$is_sent = \wp_mail(
			$email->get_to(),
			$email->get_subject(),
			$email->get_message(),
			$email->get_headers(),
			$email->get_attachments()
		);

		// Update.
		$result = $wpdb->update(
			$wpdb->orbis_email_messages,
			array( 'is_sent' => $is_sent ),
			array( 'id' => $email_id ),
			array( 'is_sent' => '%d' ),
			array( 'id' => '%d' )
		);

		if ( false === $result ) {
			$data = $email->get_data();

			throw new \Exception(
				sprintf(
					'Error updating mail: %s, Data: %s, Format: %s.',
					$wpdb->last_error,
					print_r( $data['data'], true ),
					print_r( $data['format'], true )
				)
			);
		}

		return $is_sent;
	}
}
