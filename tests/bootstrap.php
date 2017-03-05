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

$_SERVER['DOCUMENT_ROOT'] = __DIR__ . DIRECTORY_SEPARATOR . 'web';

chdir(__DIR__);

require __DIR__ . '/../vendor/autoload.php';

#
# Create the _core_ instance used for the tests.
#

$app = boot();
$app->locale = 'en';
$app->modules->install();
