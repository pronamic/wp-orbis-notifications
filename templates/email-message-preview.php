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

?>
<style type="text/css">
	th {
		text-align: left;
	}
</style>

<table>
	<tbody>
		<tr>
			<th scope="row"><?php \esc_html_e( 'From', 'orbis-notifications' ); ?></th>
			<td><?php echo \esc_html( $item->from_email ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php \esc_html_e( 'To', 'orbis-notifications' ); ?></th>
			<td><?php echo \esc_html( $item->to_email ); ?></td>
		</tr>
		<tr>
			<th scope="row"><?php \esc_html_e( 'Subject', 'orbis-notifications' ); ?></th>
			<td><?php echo \esc_html( $item->subject ); ?></td>
		</tr>
	</tbody>
</table>

<?php

echo $item->message;
