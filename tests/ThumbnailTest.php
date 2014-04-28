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

class ThumbnailTest extends \PHPUnit_Framework_TestCase
{
	static private $path = '/repository/images/madonna.jpeg';

	static public function setupBeforeClass()
	{
		global $core;

		$core->thumbnailer_versions['icon'] = [

			'w' => 64,
			'h' => 64,
			'background' => 'transparent',
			'format' => 'jpeg',
			'quality' => 85

		];
	}

	static public function tearDownAfterClass()
	{
		global $core;

		unset($core->thumbnailer_versions['icon']);
	}

	/**
	 * @dataProvider provider_test_url
	 */
	public function test_url($src, $options, $expected)
	{
		$t = new Thumbnail($src, $options);

		$this->assertEquals($expected, $t->url);
	}

	public function provider_test_url()
	{
		return require __DIR__ . '/cases/url.php';
	}

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
		$this->assertEquals("/api/thumbnail/100x120?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="100" height="120" class="thumbnail" />', (string) $thumbnail);
	}

	public function testVersion()
	{
		$thumbnail = new Thumbnail(self::$path, 'icon');

		$this->assertEquals(64, $thumbnail->w);
		$this->assertEquals(64, $thumbnail->h);
		$this->assertEquals("/api/thumbnail/icon?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="64" height="64" class="thumbnail thumbnail--icon" />', (string) $thumbnail);
	}
}