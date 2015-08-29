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

use ICanBoogie\HTTP\Request;
use ICanBoogie\Modules\Thumbnailer\Operation\GetOperation;

class GetOperationTests extends \PHPUnit_Framework_TestCase
{
	private function assert_successful_response($response, $expected_imagesize)
	{
		$rc = $response->rc;
		$this->assertTrue($response->status->is_successful);
		$this->assertInstanceOf('Closure', $rc);

		ob_start();
		$rc();
		$imagefile = ob_get_clean();

		$imagesize = getimagesizefromstring($imagefile);

		$this->assertEquals($expected_imagesize[0], $imagesize[0]);
		$this->assertEquals($expected_imagesize[1], $imagesize[1]);
		$this->assertEquals('image/' . $expected_imagesize[2], $imagesize['mime']);
	}

	/**
	 * @dataProvider provide_test_successful
	 */
	public function test_successful($uri, $expected_imagesize)
	{
		$request = Request::from($uri);
		$operation = new GetOperation;
		$response = $operation($request);

		$this->assert_successful_response($response, $expected_imagesize);
	}

	/**
	 * @dataProvider provide_test_successful
	 */
	public function test_successful_dispatch($uri, $expected_imagesize)
	{
		$request = Request::from($uri);
		$response = $request();

		$this->assert_successful_response($response, $expected_imagesize);
	}

	public function provide_test_successful()
	{
		return [

			[ '/api/thumbnail/200x200?src=' . urlencode('/cases/claire.png'), [ 200, 200, 'png' ] ],
			[ '/api/thumbnail/100x?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'png' ] ],
			[ '/api/thumbnail/x100?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'png' ] ],

			[ '/api/thumbnail/200x200?src=' . urlencode('/cases/john.png'), [ 200, 200, 'png' ] ],
			[ '/api/thumbnail/100x?src=' . urlencode('/cases/john.png'), [ 100, 400, 'png' ] ],
			[ '/api/thumbnail/x100?src=' . urlencode('/cases/john.png'), [ 25, 100, 'png' ] ],

			[ '/api/thumbnail/200x200?src=' . urlencode('/cases/laura.png'), [ 200, 200, 'png' ] ],
			[ '/api/thumbnail/100x?src=' . urlencode('/cases/laura.png'), [ 100, 25, 'png' ] ],
			[ '/api/thumbnail/x100?src=' . urlencode('/cases/laura.png'), [ 400, 100, 'png' ] ],

			# m:surface

			[ '/api/thumbnail/200x200/surface?src=' . urlencode('/cases/claire.png'), [ 200, 200, 'png' ] ],
			[ '/api/thumbnail/200x200/surface?src=' . urlencode('/cases/john.png'), [ 100, 400, 'png' ] ],
			[ '/api/thumbnail/200x200/surface?src=' . urlencode('/cases/laura.png'), [ 400, 100, 'png' ] ],

			# f:gif

			[ '/api/thumbnail/200x200.gif?src=' . urlencode('/cases/claire.png'), [ 200, 200, 'gif' ] ],
			[ '/api/thumbnail/100x.gif?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],
			[ '/api/thumbnail/x100.gif?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],

			[ '/api/thumbnail/200x200/fit.gif?src=' . urlencode('/cases/claire.png'), [ 200, 200, 'gif' ] ],
			[ '/api/thumbnail/100x/fit.gif?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],
			[ '/api/thumbnail/x100/fit.gif?src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],

			[ '/api/thumbnail/200x200?f=gif&src=' . urlencode('/cases/claire.png'), [ 200, 200, 'gif' ] ],
			[ '/api/thumbnail/100x?f=gif&src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],
			[ '/api/thumbnail/x100?f=gif&src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],

			[ '/api/thumbnail/200x200?format=gif&src=' . urlencode('/cases/claire.png'), [ 200, 200, 'gif' ] ],
			[ '/api/thumbnail/100x?format=gif&src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],
			[ '/api/thumbnail/x100?format=gif&src=' . urlencode('/cases/claire.png'), [ 100, 100, 'gif' ] ],

		];
	}
}
