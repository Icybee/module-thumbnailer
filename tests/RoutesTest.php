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

use ICanBoogie\Routing\RouteCollection;

class RoutesTests extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var RouteCollection
	 */
	static private $routes;

	static public function setupBeforeClass()
	{
		self::$routes = \ICanBoogie\app()->routes;
	}

	/**
	 * @dataProvider provider_test_route
	 *
	 * @param string $expected
	 * @param string $uri
	 */
	public function test_route($expected, $uri)
	{
		$route = self::$routes->find($uri);

		$this->assertInstanceOf('ICanBoogie\Routing\Route', $route);
		$this->assertEquals($expected, $route->id);
	}

	public function provider_test_route()
	{
		return [

			[ 'api:thumbnail', '/api/thumbnail' ],
			[ 'api:thumbnail', '/api/thumbnail' ],
			[ 'api:thumbnail', '/api/thumbnail' ],

			[ 'api:thumbnail', '/api/thumbnail?w=100&h=200' ],
			[ 'api:thumbnail', '/api/thumbnail?w=100&h=200' ],
			[ 'api:thumbnail', '/api/thumbnail?w=100&h=200' ],

			# w, h

			[ 'api:thumbnail/size', '/api/thumbnail/100x200' ],
			[ 'api:thumbnail/size', '/api/thumbnail/100x' ],
			[ 'api:thumbnail/size', '/api/thumbnail/x200' ],

			# w, h, f

			[ 'api:thumbnail/size', '/api/thumbnail/100x200.png' ],
			[ 'api:thumbnail/size', '/api/thumbnail/100x.png' ],
			[ 'api:thumbnail/size', '/api/thumbnail/x200.png' ],

			# w, h, m

			[ 'api:thumbnail/size', '/api/thumbnail/100x200/surface' ],
			[ 'api:thumbnail/size', '/api/thumbnail/100x/surface' ],
			[ 'api:thumbnail/size', '/api/thumbnail/x200/surface' ],

			# w, h, f

			[ 'api:thumbnail/size', '/api/thumbnail/100x200/surface.png' ],
			[ 'api:thumbnail/size', '/api/thumbnail/100x/surface.png' ],
			[ 'api:thumbnail/size', '/api/thumbnail/x200/surface.png' ]

		];
	}
}
