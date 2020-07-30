<?php

namespace Pronamic\WordPress\Orbis\Notifications;

global $wpdb;

$email_message_id = \get_query_var( 'orbis_email_message_id', null );

$query = $wpdb->prepare(
	"
	SELECT
		email_message.*
	FROM
		$wpdb->orbis_email_messages AS email_message
	WHERE
		email_message.id = %d
	LIMIT
		1
	;
	",
	$email_message_id
);

$item = $wpdb->get_row( $query );

if ( empty( $item ) ) {
	return \get_404_template();
}

$query = $wpdb->prepare(
	"
	SELECT
		*
	FROM
		orbis_email_tracking
	WHERE
		email_message_id = %d
	LIMIT
		0, 100
	;
	",
	$email_message_id
);

$tracking_data = $wpdb->get_results( $query );

$track_url   = 'https://track.orbis.pronamic.nl/notification/' . $item->link_key . '/Logo-Pronamic-2010-RGB.png';
$preview_url = \home_url( \user_trailingslashit( 'email-messages/' . $item->id . '/preview' ) );

$utc = new \DateTimeZone( 'UTC' );
$tz  = \wp_timezone();

\get_header();

?>
<div class="card mb-4">
	<div class="card-header">
		<?php \esc_html_e( 'Email', 'orbis-notifications' ); ?>
	</div>

	<div class="card-body">
		<table class="table table-borderless table-sm mb-0 w-auto">
			<tr>
				<th scope="row">
					<?php esc_html_e( 'ID', 'lookup' ); ?>
				</th>
				<td>
					<?php echo esc_html( $item->id ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Created At', 'lookup' ); ?>
				</th>
				<td>
					<?php echo \esc_html( ( new \DateTimeImmutable( $item->created_at, $utc ) )->setTimezone( $tz )->format( 'd-m-Y H:i:s' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Updated At', 'lookup' ); ?>
				</th>
				<td>
					<?php echo \esc_html( ( new \DateTimeImmutable( $item->updated_at, $utc ) )->setTimezone( $tz )->format( 'd-m-Y H:i:s' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'From', 'lookup' ); ?>
				</th>
				<td>
					<?php echo esc_html( $item->from_email ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'To', 'lookup' ); ?>
				</th>
				<td>
					<?php echo esc_html( $item->to_email ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Subject', 'lookup' ); ?>
				</th>
				<td>
					<?php echo esc_html( $item->subject ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Link Key', 'lookup' ); ?>
				</th>
				<td>
					<code><?php echo esc_html( $item->link_key ); ?></code>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Email Tracking Image Link', 'lookup' ); ?>
				</th>
				<td>
					<?php 

					\printf(
						'<a href="%s">%s</a>',
						\esc_url( $track_url ),
						\esc_html( $track_url ),
					);

					?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Preview Link', 'lookup' ); ?>
				</th>
				<td>
					<?php 

					\printf(
						'<a href="%s">%s</a>',
						\esc_url( $preview_url ),
						\esc_html( $preview_url ),
					);

					?>
				</td>
			</tr>
		</table>
	</div>
</div>

<div class="card mb-4">
	<div class="card-header">
		<?php \esc_html_e( 'Email Preview', 'orbis-notifications' ); ?>
	</div>

	<iframe style="min-height: 500px;" src="<?php echo \esc_url( $preview_url ); ?>" frameborder="0" allowtransparency="true" seamless="seamless" width="100%" height="100%"></iframe>
</div>

<div class="card mb-4">
	<div class="card-header">
		<?php \esc_html_e( 'Email Tracking', 'orbis-notifications' ); ?>
	</div>

	<table class="table table-striped">
		<thead>
			<tr>
				<th scope="col"><?php \esc_html_e( 'ID', 'orbis-notifications' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'IP Address', 'orbis-notifications' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'User Agent', 'orbis-notifications' ); ?></th>
				<th scope="col"><?php \esc_html_e( 'Request Time', 'orbis-notifications' ); ?></th>
			</tr>
		</thead>

		<tbody>
			
			<?php foreach ( $tracking_data as $item ) : ?>

				<tr>
					<td>
						<?php echo \esc_html( $item->id ); ?>
					</td>
					<td>
						<code><?php echo \esc_html( $item->ip_address ); ?></code>
					</td>
					<td>
						<?php echo \esc_html( $item->user_agent ); ?>
					</td>
					<td>
						<?php echo \esc_html( $item->request_time ); ?>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody>
	</table>
</div>
<?php

\get_footer();
