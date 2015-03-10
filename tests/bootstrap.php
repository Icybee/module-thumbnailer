<?php

namespace ICanBoogie;

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

$app = new Core(array_merge_recursive(get_autoconfig(), [

	'config-path' => [

		__DIR__ . DIRECTORY_SEPARATOR . 'config' => 10

	],

	'module-path' => [

		realpath(__DIR__ . '/../')

	]

]));

$app->boot();

#
# Install modules
#

$errors = $app->modules->install(new Errors);

if ($errors->count())
{
	foreach ($errors as $module_id => $error)
	{
		echo "[$module_id] $error\n";
	}

	exit(1);
}
