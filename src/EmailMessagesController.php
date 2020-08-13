<?php

namespace Pronamic\WordPress\Orbis\Notifications;

/**
 * Email messages controller.
 */
class EmailMessagesController {
	/**
	 * Setup.
	 */
	public function setup() {
		add_action( 'init', array( $this, 'init' ) );

		add_filter( 'query_vars', array( $this, 'query_vars' ) );

		add_filter( 'template_include', array( $this, 'template_include_email_messages' ) );
		add_filter( 'template_include', array( $this, 'template_include_email_message' ) );
	}

	/**
	 * Initialize.
	 */
	public function init() {
		$slug = 'email-messages';

		// Rewrite Rules
		$match_dir = '([^/]+)';

		// @see https://make.wordpress.org/core/2015/10/07/add_rewrite_rule-accepts-an-array-of-query-vars-in-wordpress-4-4/
		// Matching
		add_rewrite_rule(
			$slug . '/?$',
			array(
				'orbis_email_messages' => true,
			),
			'top'
		);

		add_rewrite_rule(
			$slug . '/' . $match_dir . '/?$',
			array(
				'orbis_email_messages'   => true,
				'orbis_email_message_id' => '$matches[1]',
			),
			'top'
		);

		add_rewrite_rule(
			$slug . '/' . $match_dir . '/' . $match_dir . '/?$',
			array(
				'orbis_email_messages'     => true,
				'orbis_email_message_id'   => '$matches[1]',
				'orbis_email_message_view' => '$matches[2]',
			),
			'top'
		);
	}

	/**
	 * Query vars.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function query_vars( $query_vars ) {
		$query_vars[] = 'orbis_email_messages';
		$query_vars[] = 'orbis_email_message_id';
		$query_vars[] = 'orbis_email_message_view';

		return $query_vars;
	}

	/**
	 * Template include.
	 *
	 * @param string $template
	 * @return string
	 */
	public function template_include_email_messages( $template ) {
		$value = get_query_var( 'orbis_email_messages', null );

		if ( null === $value ) {
			return $template;
		}

		$template = __DIR__ . '/../templates/email-messages.php';

		return $template;
	}

	/**
	 * Template include.
	 *
	 * @param string $template
	 * @return string
	 */
	public function template_include_email_message( $template ) {
		$value = get_query_var( 'orbis_email_message_id', null );
		$view  = get_query_var( 'orbis_email_message_view', null );

		if ( null === $value ) {
			return $template;
		}

		if ( 'preview' === $view ) {
			return  __DIR__ . '/../templates/email-message-preview.php';
		}

		$template = __DIR__ . '/../templates/email-message.php';

		return $template;
	}
}
