<?php

global $wpdb;

$company_id = $wpdb->get_var(
	$wpdb->prepare(
		"
			SELECT
			    company.id
			FROM
			    $wpdb->orbis_companies AS company
			WHERE
			    company.post_id = %d
			;
			",
		get_the_ID()
	)
);

$email_messages = $wpdb->get_results(
	$wpdb->prepare(
		"
		SELECT
			email_message.*
		FROM
			$wpdb->orbis_email_messages AS email_message
		WHERE
			email_message.company_id = %d
		ORDER BY
			email_message.created_at DESC
		LIMIT
			0, 100
		;
		",
		$company_id
	)
);

$utc = new \DateTimeZone( 'UTC' );
$tz  = \wp_timezone();

if ( $email_messages ) : ?>

	<table class="table table-striped mb-0">
		<thead>
			<tr>
				<th scope="col"><?php \esc_html_e( 'Created', 'orbis-notifications' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'To', 'orbis-notifications' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Subject', 'orbis-notifications' ); ?></th>
			</tr>
		</thead>

		<tbody>

			<?php foreach ( $email_messages as $email_message ) : ?>

				<tr>
					<td><?php echo \esc_html( ( new \DateTimeImmutable( $email_message->created_at, $utc ) )->setTimezone( $tz )->format( 'd-m-Y H:i:s' ) ); ?></td>
					<td><?php echo \esc_html( $email_message->to_email ); ?></td>
					<td>
						<?php

						$url = \home_url( \user_trailingslashit( 'email-messages/' . $email_message->id ) );

						\printf(
							'<a href="%s">%s</a>',
							\esc_url( $url ),
							\esc_html( $email_message->subject )
						);

						?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>

<?php else : ?>

	<div class="card-body">
		<p class="text-muted m-0">
			<?php esc_html_e( 'No email messages found.', 'orbis-notifications' ); ?>
		</p>
	</div>

<?php endif; ?>
