<?php
/**
 * E-mail.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications\Services\Email;

/**
 * E-mail.
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
class Email {
	/**
	 * Email ID.
	 *
	 * @var int|null
	 */
	private $id;

	/**
	 * To email address.
	 *
	 * @var string|null
	 */
	private $to;

	/**
	 * From address.
	 *
	 * @var string|null
	 */
	private $from;

	/**
	 * Reply to address.
	 *
	 * @var string|null
	 */
	private $reply_to;

	/**
	 * Subject.
	 *
	 * @var string|null
	 */
	private $subject;

	/**
	 * Message body.
	 *
	 * @var string|null
	 */
	private $message;

	/**
	 * Headers.
	 *
	 * @var string|null
	 */
	private $headers;

	/**
	 * Is sent?
	 *
	 * @var bool
	 */
	private $sent;

	/**
	 * Number attempts.
	 *
	 * @var int
	 */
	private $number_attempts;

	/**
	 * Email template ID.
	 *
	 * @var int|null
	 */
	private $template_id;

	/**
	 * WordPress user ID.
	 *
	 * @var int|null
	 */
	private $user_id;

	/**
	 * Subscription ID.
	 *
	 * @var int|null
	 */
	private $subscription_id;

	public function __construct( $id = null ) {
		$this->number_attempts = 0;
		$this->sent            = false;

		// Load email.
		if ( null !== $id ) {
			$this->load_email( $id );
		}
	}

	/**
	 * Load email.
	 *
	 * @param int $id Email ID.
	 * @return Email|null
	 */
	public function load_email( $id ) {
		global $wpdb;

		$email_query = "
			SELECT
				id,
			    created_at,
			    updated_at,
			    from_email,
			    to_email,
			    reply_to,
			    subject,
			    message,
			    headers,
			    is_sent,
			    number_attempts,
			    template_id,
				user_id,
			    subscription_id
			FROM
			    $wpdb->orbis_email_messages
			WHERE
				id = %d
		;";

		$query = $wpdb->prepare( $email_query, $id );

		$email = $wpdb->get_row( $query );

		if ( null !== $email ) {
			// Set ID property if email was loaded.
			$this->id = $id;

			$this->set_from( $email->from_email );
			$this->set_to( $email->to_email );
			$this->set_reply_to( $email->reply_to );
			$this->set_subject( $email->subject );
			$this->set_message( $email->message );
			$this->set_headers( $email->headers );
			$this->set_sent( (bool) $email->is_sent );
			$this->set_number_attempts( \intval( $email->number_attempts ) );
			$this->set_template_id( $email->template_id );
			$this->set_user_id( $email->user_id );
			$this->set_subscription_id( $email->subscription_id );

			return $this;
		}

		return null;
	}

	/**
	 * Load template.
	 *
	 * @param string $code Template code.
	 *
	 * @return object|null
	 */
	public function load_template( $code ) {
		global $wpdb;

		$template_query = "
			SELECT
			    id,
				subject,
				message
			FROM
				$wpdb->orbis_email_templates
			WHERE
				code = %s
		;";

		$query = $wpdb->prepare( $template_query, $code );

		$template = $wpdb->get_row( $query );

		if ( null !== $template) {
			$this->set_template_id( $template->id );
			$this->set_subject( $template->subject );
			$this->set_message( $template->message );

			return $template;
		}

		return null;
	}

	/**
	 * Get 'To'.
	 *
	 * @return string|null
	 */
	public function get_to() {
		return $this->to;
	}

	/**
	 * Set 'To'.
	 *
	 * @param string|null $to To address.
	 *
	 * @return void
	 */
	public function set_to( $to ) {
		$this->to = $to;
	}

	/**
	 * Get 'From'.
	 *
	 * @return string|null
	 */
	public function get_from() {
		return $this->from;
	}

	/**
	 * Set 'From'.
	 *
	 * @param string|null $from From address.
	 *
	 * @return void
	 */
	public function set_from( $from ) {
		$this->from = $from;
	}

	/**
	 * Get 'Reply to'.
	 *
	 * @return string|null
	 */
	public function get_reply_to() {
		return $this->reply_to;
	}

	/**
	 * Set 'Reply to'.
	 *
	 * @param string|null $reply_to Reply to address.
	 *
	 * @return void
	 */
	public function set_reply_to( $reply_to ) {
		$this->reply_to = $reply_to;
	}

	/**
	 * Get subject.
	 *
	 * @return string|null
	 */
	public function get_subject() {
		return $this->subject;
	}

	/**
	 * Set subject.
	 *
	 * @param string|null $subject Subject.
	 *
	 * @return void
	 */
	public function set_subject( $subject ) {
		$this->subject = $subject;
	}

	/**
	 * Get message body.
	 *
	 * @return string|null
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Set message body.
	 *
	 * @param string|null $message Message.
	 *
	 * @return void
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Get headers.
	 *
	 * @return string|null
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Set headers.
	 *
	 * @param string|null $headers Headers.
	 *
	 * @return void
	 */
	public function set_headers( $headers ) {
		$this->headers = $headers;
	}

	/**
	 * Is sent?
	 *
	 * @return bool
	 */
	public function is_sent() {
		return $this->sent;
	}

	/**
	 * Set sent.
	 *
	 * @param bool $is_sent Whether or not email has been sent.
	 *
	 * @return void
	 */
	public function set_sent( $is_sent ) {
		$this->sent = $is_sent;
	}

	/**
	 * Get number attempts.
	 *
	 * @return int
	 */
	public function get_number_attempts() {
		return $this->number_attempts;
	}

	/**
	 * Set number attempts.
	 *
	 * @param int $number_attempts Number attempts.
	 *
	 * @return void
	 */
	public function set_number_attempts( $number_attempts ) {
		$this->number_attempts = $number_attempts;
	}

	/**
	 * Get template ID.
	 *
	 * @return int|null
	 */
	public function get_template_id() {
		return $this->template_id;
	}

	/**
	 * Set template ID.
	 *
	 * @param int|null $template_id Template id.
	 *
	 * @return void
	 */
	public function set_template_id( $template_id ) {
		$this->template_id = $template_id;
	}

	/**
	 * Get user id.
	 *
	 * @return int|null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Set user id.
	 *
	 * @param int|null $user_id User id.
	 *
	 * @return void
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * Get subscription id.
	 *
	 * @return int|null
	 */
	public function get_subscription_id() {
		return $this->subscription_id;
	}

	/**
	 * Set subscription id.
	 *
	 * @param int|null $subscription_id Subscription id.
	 *
	 * @return void
	 */
	public function set_subscription_id( $subscription_id ) {
		$this->subscription_id = $subscription_id;
	}

	/**
	 * Get headers.
	 *
	 * @return array
	 */
	public function build_headers() {
		$headers = array(
			'Content-Type: text/html; charset=' . get_option( 'blog_charset' ),
		);

		// From.
		$from = $this->get_from();

		if ( ! empty( $from ) ) {
			$headers[] = 'From: ' . $from;
		}

		// Reply to.
		$reply_to = $this->get_reply_to();

		if ( ! empty( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		return $headers;
	}

	/**
	 * Get attachments.
	 */
	public function get_attachments() {
		return array();
	}

	/**
	 * Save email.
	 *
	 * @return bool|int
	 * @throws \Exception Throws exception if email could not be inserted in database.
	 */
	public function save() {
		global $wpdb;

		$data = $this->get_data();

		// Insert email.
		$result  = $wpdb->insert(
			$wpdb->orbis_email_messages,
			$data['data'],
			$data['format']
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Error inserting mail: %s, Data: %s, Format: %s.',
					$wpdb->last_error,
					print_r( $data['data'], true ),
					print_r( $data['format'], true )
				)
			);
		}

		$this->id = $wpdb->insert_id;

		return $this->id;
	}

	/**
	 * Get data and format.
	 *
	 * @return array<string,array>
	 */
	public function get_data() {
		// Data.
		$data = array(
			'created_at' => current_time( 'mysql', true ),
			'updated_at' => current_time( 'mysql', true ),
			'to_email'   => $this->get_to(),
			'subject'    => $this->get_subject(),
			'message'    => $this->get_message(),
			'link_key'   => \wp_generate_password( 32, false, false ),
		);

		// Format.
		$format = array(
			'created_at' => '%s',
			'updated_at' => '%s',
			'to_email'   => '%s',
			'subject'    => '%s',
			'message'    => '%s',
			'link_key'   => '%s',
		);

		// From.
		$from = $this->get_from();

		if ( ! empty( $from ) ) {
			$data['from_email']   = $from;
			$format['from_email'] = '%s';
		}

		// Reply to.
		if ( ! empty( $this->reply_to ) ) {
			$data['reply_to']   = $this->reply_to;
			$format['reply_to'] = '%s';
		}

		// Template ID.
		$template_id = $this->get_template_id();

		if ( ! empty( $template_id ) ) {
			$data['template_id']   = $template_id;
			$format['template_id'] = '%d';
		}

		// User ID.
		$user_id = $this->get_user_id();

		if ( ! empty( $user_id ) ) {
			$data['user_id']   = $user_id;
			$format['user_id'] = '%d';
		}

		// Subscription ID.
		$subscription_id = $this->get_subscription_id();

		if ( ! empty( $subscription_id ) ) {
			$data['subscription_id']   = $subscription_id;
			$format['subscription_id'] = '%d';
		}

		// Headers.
		$headers = implode( "\r\n", $this->build_headers() );

		$this->set_headers( $headers );

		$data['headers']   = $headers;
		$format['headers'] = '%s';

		return array(
			'data'   => $data,
			'format' => $format,
		);
	}

	/**
	 * Send.
	 *
	 * @return void
	 * @throws \Exception Throws exception if email could not be updated in database.
	 */
	public function send() {
		global $wpdb;

		if ( null === $this->id ) {
			$this->save();
		}

		// Get single email.
		$query = $wpdb->prepare(
			"
			SELECT
				email_message.*
			FROM
				$wpdb->orbis_email_messages AS email_message
			WHERE
				email_message.id = %d
			;
		",
			$this->id
		);

		$email_message = $wpdb->get_row( $query );

		if ( null === $email_message ) {
			throw new \Exception(
				sprintf(
					__( 'Failed sending email ID #%d. Message could not be found.', 'orbis-notifications' ),
					$this->id
				)
			);
		}

		// Send mail.
		$mailer = new Mailer();

		$mailer->send( $email_message );
	}
}
