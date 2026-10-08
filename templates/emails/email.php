<?php

/**
 * Really Simple Responsive HTML Email Template.
 *
 * @link https://github.com/leemunroe/responsive-html-email-template/blob/v1.0.1/email.html
 */

if ( ! isset( $email ) ) {
	return;
}

require __DIR__ . '/header.php';

echo $email->get_message();

require __DIR__ . '/footer.php';
