<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\HTTP\Request;

return [

	'api:thumbnail' => [

		'pattern' => '/api/thumbnail',
		'controller' => GetOperation::class,
		'via' => Request::METHOD_GET

	],

	'api:thumbnail/size' => [

		'pattern' => '/api/thumbnail/<size:\d+x\d+|\d+x|x\d+>*',
		'controller' => GetOperation::class,
		'via' => Request::METHOD_GET

	]

];
