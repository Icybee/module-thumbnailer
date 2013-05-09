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

class VersionTest extends \PHPUnit_Framework_TestCase
{
	public function testSerializeDefaultValues()
	{
		$this->assertEquals('', Version::serialize(Version::$defaults));
	}

	public function testUnserialize()
	{
		$this->assertEquals(array('width' => 120, 'height' => 100), Version::unserialize('w:120;h:100;anything:whatever'));
	}

	public function testSerialize()
	{
		$this->assertEquals('h:100;w:120;', Version::serialize(array('width' => 120, 'height' => 100, 'anything' => 'whatever', 'format' => 'jpeg')));
	}

	public function testVersion()
	{
		$v = new Version('h:100;w:120');

		$this->assertEquals(Version::$defaults['background'], $v->b);
		$this->assertEquals(Version::$defaults['background'], $v->background);
		$this->assertEquals(Version::$defaults['default'], $v->d);
		$this->assertEquals(Version::$defaults['default'], $v->default);
		$this->assertEquals(Version::$defaults['format'], $v->f);
		$this->assertEquals(Version::$defaults['format'], $v->format);
		$this->assertEquals(Version::$defaults['filter'], $v->ft);
		$this->assertEquals(Version::$defaults['filter'], $v->filter);
		$this->assertEquals(100, $v->h);
		$this->assertEquals(100, $v->height);
		$this->assertEquals(Version::$defaults['method'], $v->m);
		$this->assertEquals(Version::$defaults['method'], $v->method);
		$this->assertEquals(Version::$defaults['no-interlace'], $v->ni);
		$this->assertEquals(Version::$defaults['no-interlace'], $v->no_interlace);
		$this->assertEquals(Version::$defaults['no-upscale'], $v->nu);
		$this->assertEquals(Version::$defaults['no-upscale'], $v->no_upscale);
		$this->assertEquals(Version::$defaults['overlay'], $v->o);
		$this->assertEquals(Version::$defaults['overlay'], $v->overlay);
		$this->assertEquals(Version::$defaults['path'], $v->p);
		$this->assertEquals(Version::$defaults['path'], $v->path);
		$this->assertEquals(Version::$defaults['quality'], $v->q);
		$this->assertEquals(Version::$defaults['quality'], $v->quality);
		$this->assertEquals(Version::$defaults['src'], $v->s);
		$this->assertEquals(Version::$defaults['src'], $v->src);
		$this->assertEquals(120, $v->w);
		$this->assertEquals(120, $v->width);

		$v->q = 90;
		$this->assertEquals(90, $v->quality);
		$v->quality = Version::$defaults['quality'];
		$this->assertEquals(Version::$defaults['quality'], $v->q);

		$this->assertSame
		(
			array
			(
				'background' => Version::$defaults['background'],
				'default' => Version::$defaults['default'],
				'filter' => Version::$defaults['filter'],
				'format' => Version::$defaults['format'],
				'height' => '100',
				'method' => Version::$defaults['method'],
				'no-interlace' => Version::$defaults['no-interlace'],
				'no-upscale' => Version::$defaults['no-upscale'],
				'overlay' => Version::$defaults['overlay'],
				'path' => Version::$defaults['path'],
				'quality' => Version::$defaults['quality'],
				'src' => Version::$defaults['src'],
				'width' => '120'
			),

			$v->to_array()
		);

		$this->assertSame
		(
			array
			(
				'height' => '100',
				'width' => '120'
			),

			$v->to_array(Version::ARRAY_FILTER)
		);

		$this->assertSame
		(
			array
			(
				'b' => Version::$defaults['background'],
				'd' => Version::$defaults['default'],
				'ft' => Version::$defaults['filter'],
				'f' => Version::$defaults['format'],
				'h' => '100',
				'm' => Version::$defaults['method'],
				'ni' => Version::$defaults['no-interlace'],
				'nu' => Version::$defaults['no-upscale'],
				'o' => Version::$defaults['overlay'],
				'p' => Version::$defaults['path'],
				'q' => Version::$defaults['quality'],
				's' => Version::$defaults['src'],
				'w' => '120'
			),

			$v->to_array(Version::ARRAY_SHORTEN)
		);

		$this->assertSame
		(
			array
			(
				'h' => '100',
				'w' => '120'
			),

			$v->to_array(Version::ARRAY_FILTER | Version::ARRAY_SHORTEN)
		);
	}
}