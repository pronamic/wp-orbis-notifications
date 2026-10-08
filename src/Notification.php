<?php
/**
 * Notification
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

namespace Pronamic\WordPress\Orbis\Notifications;

/**
 * Notification
 *
 * @author  Reüel van der Steege
 * @since   1.0.0
 * @version 1.0.0
 */
abstract class Notification {
	/**
	 * Notification constructor.
	 *
	 * @param array<string,array|int|string> $options Options.
	 */
	public function __construct( protected $options ) {
	}

	/**
	 * Run notifications.
	 */
	abstract public function run();
}
