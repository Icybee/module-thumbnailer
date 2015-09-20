<?php

namespace ICanBoogie\Modules\Thumbnailer\Routing;

use ICanBoogie\HTTP\Request;

return [

	'api:thumbnail' => [

		'pattern' => '/api/thumbnail',
		'controller' => ThumbnailController::class,
		'via' => Request::METHOD_GET

	],

	'api:thumbnail/size' => [

		'pattern' => '/api/thumbnail/<size:\d+x\d+|\d+x|x\d+>*',
		'controller' => ThumbnailController::class,
		'via' => Request::METHOD_GET

	]

];
