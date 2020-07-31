<?php

/**
 * Really Simple Responsive HTML Email Template.
 *
 * @link https://github.com/leemunroe/responsive-html-email-template/blob/v1.0.1/email.html
 */

if ( ! isset( $email ) ) {
	return;
}

include __DIR__ . '/header.php';

echo $email->get_message();

include __DIR__ . '/footer.php';
