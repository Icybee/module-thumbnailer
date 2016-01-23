<?php

namespace ICanBoogie;

use ICanBoogie\Binding;

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_SERVER['DOCUMENT_ROOT'] = __DIR__;

require __DIR__ . '/../vendor/autoload.php';

#
# Create the _core_ instance used for the tests.
#

/* @var $app Core|Binding\CLDR\CoreBindings|\ICanBoogie\Module\CoreBindings */

$app = new Core(array_merge_recursive(get_autoconfig(), [

	'config-path' => [

		__DIR__ . DIRECTORY_SEPARATOR . 'config' => 10

	],

	'module-path' => [

		realpath(__DIR__ . '/../')

	]

]));

$app->locale = 'en';

$app->boot();
$app->modules->install();
