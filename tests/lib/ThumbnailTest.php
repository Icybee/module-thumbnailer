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
		\ICanBoogie\app()->thumbnailer_versions['icon'] = [

			'w' => 64,
			'h' => 64,
			'background' => 'transparent',
			'format' => 'jpeg',
			'quality' => 85

		];
	}

	static public function tearDownAfterClass()
	{
		unset(\ICanBoogie\app()->thumbnailer_versions['icon']);
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
		$t = new Thumbnail(self::$path, 'w:100;h:120;m:fill;f:png');

		$this->assertEquals(100, $t->w);
		$this->assertEquals(100, $t->width);
		$this->assertEquals(120, $t->h);
		$this->assertEquals(120, $t->height);
		$this->assertEquals('fill', $t->m);
		$this->assertEquals('fill', $t->method);
		$this->assertEquals('png', $t->f);
		$this->assertEquals('png', $t->format);
		$this->assertInternalType('array', $t->options);

		$t->w = 300;
		$this->assertEquals(300, $t->w);
		$this->assertEquals(300, $t->width);

		$t->width = 100;
		$this->assertEquals(100, $t->w);
		$this->assertEquals(100, $t->width);

		$t->h = 400;
		$this->assertEquals(400, $t->h);
		$this->assertEquals(400, $t->height);

		$t->height = 100;
		$this->assertEquals(100, $t->h);
		$this->assertEquals(100, $t->height);

		$t->m = 'surface';
		$this->assertEquals('surface', $t->m);
		$this->assertEquals('surface', $t->method);

		$t->method = 'fit';
		$this->assertEquals('fit', $t->m);
		$this->assertEquals('fit', $t->method);
	}

	public function test_with_json_version()
	{
		$thumbnail = new Thumbnail(self::$path, 'w:100;h:120;m:fill');

		$this->assertEquals(100, $thumbnail->w);
		$this->assertEquals(120, $thumbnail->h);
		$this->assertEquals('fill', $thumbnail->method);
		$this->assertEquals("/api/thumbnail/100x120?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="100" height="120" class="thumbnail" />', (string) $thumbnail);
	}

	public function test_with_uri()
	{
		$thumbnail = new Thumbnail(self::$path, '100x120/fill');

		$this->assertEquals(100, $thumbnail->w);
		$this->assertEquals(120, $thumbnail->h);
		$this->assertEquals('fill', $thumbnail->method);
		$this->assertEquals("/api/thumbnail/100x120?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="100" height="120" class="thumbnail" />', (string) $thumbnail);
	}

	public function test_with_version_name()
	{
		$thumbnail = new Thumbnail(self::$path, 'icon');

		$this->assertInstanceOf('ICanBoogie\Modules\Thumbnailer\Version', $thumbnail->version);
		$this->assertInstanceOf('ICanBoogie\Modules\Thumbnailer\Version', $thumbnail->v);

		$this->assertEquals(64, $thumbnail->w);
		$this->assertEquals(64, $thumbnail->h);
		$this->assertEquals("/api/thumbnail/icon?s=%2Frepository%2Fimages%2Fmadonna.jpeg", $thumbnail->url);
		$this->assertEquals('<img src="' . \Brickrouge\escape($thumbnail->url) . '" alt="" width="64" height="64" class="thumbnail thumbnail--icon" />', (string) $thumbnail);
	}
}
