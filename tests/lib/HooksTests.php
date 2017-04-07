<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer;

use function ICanBoogie\app;

class HooksTests extends \PHPUnit_Framework_TestCase
{
	public function test_core_thumbnails_version()
	{
		$this->assertInstanceOf(Versions::class, app()->thumbnailer_versions);
	}
}
