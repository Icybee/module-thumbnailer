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

class HooksTests extends \PHPUnit_Framework_TestCase
{
	public function test_core_thumbnails_version()
	{
		$this->assertIntanceOf('ICanBoogie\Modules\Thumbnailer\Versions', \ICanBoogie\app()->thumbnailer_versions);
	}
}
