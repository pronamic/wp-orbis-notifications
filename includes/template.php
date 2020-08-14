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
 * Orbis subscription section email messages.
 *
 * @param array $sections Sections.
 * @return array
 */
function orbis_subscription_section_email_messages( $sections ) {
	$sections[] = array(
		'id'       => 'email-messages',
		'name'     => __( 'Email messages', 'orbis-notifications' ),
		'callback' => function() {
			if ( ! is_singular( 'orbis_subscription' ) ) {
				return;
			}

			global $orbis_notifications_plugin;

			$orbis_notifications_plugin->plugin_include( 'templates/subscription-email-messages.php' );
		},
	);

	return $sections;
}

add_filter( 'orbis_subscription_sections', 'orbis_subscription_section_email_messages', 30 );
