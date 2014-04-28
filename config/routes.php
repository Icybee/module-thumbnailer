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

	'api:thumbnail/size' => array
	(
		'pattern' => '/api/thumbnail/<size:\d+x\d+|\d+x|x\d+>*',
		'controller' => 'ICanBoogie\Modules\Thumbnailer\GetOperation',
		'via' => Request::METHOD_GET
	)
);