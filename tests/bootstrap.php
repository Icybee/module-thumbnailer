<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Modules\Thumbnailer\Versions;

require __DIR__ . '/../vendor/autoload.php';

$versions = new Versions
(
	array
	(
		'images-view' => array
		(
			'w' => 120,
			'h' => 100
		),

		'icon' => array
		(
			'w' => 64,
			'h' => 64,
			'background' => 'transparent',
			'format' => 'jpeg',
			'quality' => 85
		)
	)
);

$core = (object) array
(
	'thumbnailer_versions' => $versions
);