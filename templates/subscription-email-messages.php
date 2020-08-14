<?php

global $wpdb;

$subscription_id = $wpdb->get_var(
	$wpdb->prepare(
		"
			SELECT
			    subscription.id
			FROM
			    $wpdb->orbis_subscriptions AS subscription
			WHERE
			    subscription.post_id = %d
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
			email_message.subscription_id = %d
		ORDER BY
			email_message.created_at DESC
		LIMIT
			0, 100
		;
		",
		$subscription_id
	)
);

$utc = new \DateTimeZone( 'UTC' );
$tz  = \wp_timezone();

if ( $email_messages ) : ?>

<div class="card mb-3">
	<div class="card-header">
		<?php esc_html_e('Email messages', 'orbis-notifications' ); ?>
	</div>

	<div class="table-responsive" id="orbis-subscription-email-messages">
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
						<td><?php echo \esc_html( ( new \DateTimeImmutable( $email_message->created_at, $utc ) )->setTimezone( $tz )->format( 'd-m-Y H:i' ) ); ?></td>
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
	</div>
</div>

<?php endif; ?>
