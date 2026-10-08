<?php

/**
 * Orbis organization section email messages.
 *
 * @param array $sections Sections.
 * @return array
 */
function orbis_organization_sections_email_messages( $sections ) {
	$sections[] = array(
		'id'       => 'email-messages',
		'name'     => __( 'Email messages', 'orbis-notifications' ),
		'callback' => function() {
			if ( ! is_singular( 'orbis_organization' ) ) {
				return;
			}

			include __DIR__ . '/../templates/organization-email-messages.php';
		},
	);

	return $sections;
}

add_filter( 'orbis_organization_sections', 'orbis_organization_sections_email_messages', 30 );

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

			include __DIR__ . '/../templates/subscription-email-messages.php';
		},
	);

	return $sections;
}

add_filter( 'orbis_subscription_sections', 'orbis_subscription_section_email_messages', 30 );
