<?php
/**
 * Rector
 *
 * @author    Pronamic
 * @copyright 2020-2026 Pronamic
 * @license   Proprietary
 * @package   Pronamic\WordPress\Orbis\Notifications
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/src',
		]
	)
	->withPhpSets()
	->withTypeCoverageLevel( 0 )
	->withDeadCodeLevel( 0 )
	->withCodeQualityLevel( 0 );
