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

use Icybee\Modules\Images\Image;

class ThumbnailTest extends \PHPUnit_Framework_TestCase
{
	static private $path = '/repository/images/madonna.jpeg';

	public function testOptions()
	{
		$t = new Thumbnail(self::$path, 'w:100;h:120;m:fill');

		$this->assertEquals(100, $t->w);
		$this->assertEquals(100, $t->width);
		$this->assertEquals(120, $t->h);
		$this->assertEquals(120, $t->height);
		$this->assertEquals('fill', $t->m);
		$this->assertEquals('fill', $t->method);
	}

	public function testThumbnail()
	{
		$thumbnail = new Thumbnail(self::$path, 'w:100;h:120;m:fill');

		$this->assertEquals(100, $thumbnail->w);
		$this->assertEquals(120, $thumbnail->h);
		$this->assertEquals('fill', $thumbnail->method);
		$this->assertEquals("/api/thumbnail/100x120/fill?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="100" height="120" class="thumbnail" />', (string) $thumbnail);
	}

	public function testVersion()
	{
		$thumbnail = new Thumbnail(self::$path, 'icon');

		$this->assertEquals(64, $thumbnail->w);
		$this->assertEquals(64, $thumbnail->h);
		$this->assertEquals("/api/thumbnail/64x64/fill?s=%2Frepository%2Fimages%2Fmadonna.jpeg&v=icon", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="64" height="64" class="thumbnail thumbnail--icon" />', (string) $thumbnail);
	}
}