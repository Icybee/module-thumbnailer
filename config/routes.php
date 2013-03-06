<?php

use ICanBoogie\HTTP\Request;

return array
(
	'api:thumbnail' => array
	(
		'pattern' => '/api/thumbnail',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:w/h/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>/:m',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:w/h' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>x<h:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:w/m' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>/:m',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:w' => array
	(
		'pattern' => '/api/thumbnail/<w:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:h/m' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>/:m',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:h' => array
	(
		'pattern' => '/api/thumbnail/x<h:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	),

	/*
	 * Module's thumbnails
	 */

	'api:thumbnail:image' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/thumbnail',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:w/h/m' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/<w:\d+>x<h:\d+>/:m',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:w/h' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/<w:\d+>x<h:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:w/m' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/<w:\d+>/:m',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:w' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/<w:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:h/m' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/x<h:\d+>/:method',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:h' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/x<h:\d+>',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	),

	'api:thumbnail:image:version' => array
	(
		'pattern' => '/api/:module/<nid:\d+>/thumbnails/:version',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\ThumbnailOperation',
		'via' => Request::METHOD_GET
	)
);