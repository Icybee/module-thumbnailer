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

$versions = Versions::get();

$versions['images-view'] = array
(
	'w' => 120,
	'h' => 100
);

$versions['icon'] = array
(
	'w' => 64,
	'h' => 64,
	'background' => 'transparent',
	'format' => 'jpeg',
	'quality' => 85
);