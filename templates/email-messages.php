<?php

namespace Pronamic\WordPress\Orbis\Notifications;

global $wpdb;

$query = "
	SELECT
		email_message.*
	FROM
		$wpdb->orbis_emails AS email_message
	ORDER BY
		email_message.created_at DESC
	LIMIT
		0, 100
	;
";

$data = $wpdb->get_results( $query );

if ( empty( $data ) ) {
	return \get_404_template();
}

\get_header();

?>
<table class="table table-striped">
	<thead>
		<tr>
			<th scope="col"><?php \esc_html_e( 'ID', 'orbis-notifications' ); ?></th>
			<th scope="col"><?php \esc_html_e( 'Created At', 'orbis-notifications' ); ?></th>
			<th scope="col"><?php \esc_html_e( 'From', 'orbis-notifications' ); ?></th>
			<th scope="col"><?php \esc_html_e( 'To', 'orbis-notifications' ); ?></th>
			<th scope="col"><?php \esc_html_e( 'Subject', 'orbis-notifications' ); ?></th>
			<th scope="col"><i class="fas fa-link"></i></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $data as $item ) : ?>

			<tr>
				<td><?php echo \esc_html( $item->id ); ?></td>
				<td><?php echo \esc_html( $item->created_at ); ?></td>
				<td><?php echo \esc_html( $item->from_email ); ?></td>
				<td><?php echo \esc_html( $item->to_email ); ?></td>
				<td><?php echo \esc_html( $item->subject ); ?></td>
				<td>
					<?php

					$url = \home_url( \user_trailingslashit( 'email-messages/' . $item->id ) );

					\printf(
						'<a href="%s"><i class="fas fa-link"></i></a>',
						\esc_url( $url )
					);

					?>
				</td>
			</tr>

		<?php endforeach; ?>

	</tbody>
</table>

<?php

\get_footer();
