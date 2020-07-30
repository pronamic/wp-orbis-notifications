<?php

namespace Pronamic\WordPress\Orbis\Notifications;

global $wpdb;

$email_message_id = \get_query_var( 'orbis_email_message_id', null );

$query = $wpdb->prepare(
	"
	SELECT
		email_message.*
	FROM
		$wpdb->orbis_emails AS email_message
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

$preview_url = \home_url( \user_trailingslashit( 'email-messages/' . $item->id . '/preview' ) );

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
					<?php echo esc_html( $item->created_at ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Updated At', 'lookup' ); ?>
				</th>
				<td>
					<?php echo esc_html( $item->updated_at ); ?>
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
<?php

\get_footer();
