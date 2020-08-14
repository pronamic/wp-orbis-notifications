<?php

/**
 * Orbis company section email messages.
 *
 * @param array $sections Sections.
 * @return array
 */
function orbis_company_sections_email_messages( $sections ) {
	$sections[] = array(
		'id'       => 'email-messages',
		'name'     => __( 'Email messages', 'orbis-notifications' ),
		'callback' => function() {
			if ( ! is_singular( 'orbis_company' ) ) {
				return;
			}

			global $orbis_notifications_plugin;

			$orbis_notifications_plugin->plugin_include( 'templates/company-email-messages.php' );
		},
	);

	return $sections;
}

add_filter( 'orbis_company_sections', 'orbis_company_sections_email_messages', 30 );

/**
 * Orbis subscription email messages.
 *
 * @return void
 */
function orbis_notifications_subscription_email_messages() {
	require __DIR__ . '/../templates/subscription-email-messages.php';
}

add_filter( 'orbis_after_main_content', 'orbis_notifications_subscription_email_messages' );
