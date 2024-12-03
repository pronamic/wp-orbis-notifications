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
 * Admin
 *
 * @author  Reüel van der Steege
 * @since   1.1.0
 * @version 1.1.0
 */
class Admin {
	/**
	 * Admin constructor.
	 */
	public function __construct() {
		// Actions.
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Admin initialize.
	 *
	 * @return void
	 */
	public function admin_init() {
		add_settings_section(
			'orbis_notifications',
			__( 'Notifications', 'orbis-notifications' ),
			null,
			'orbis'
		);

		add_settings_field(
			'orbis_notifications_additional_recipient_user_id',
			_x( 'Additional recipient for 100% quota notifications', 'notifications', 'orbis-notifications' ),
			[ $this, 'input_select_user' ],
			'orbis',
			'orbis_notifications',
			[
				'label_for' => 'orbis_notifications_additional_recipient_user_id',
			]
		);

		register_setting( 'orbis', 'orbis_notifications_additional_recipient_user_id' );
	}

	/**
	 * User select field.
	 *
	 * @return void
	 */
	public function input_select_user( $args ) {
		wp_dropdown_users(
			[
				'name'             => $args['label_for'],
				'show'             => 'user_email',
				'selected'         => \get_option( $args['label_for'] ),
				'show_option_none' => __( 'No additional recipient', 'orbis-notifications' ),
			]
		);
	}
}
