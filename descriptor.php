<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_TITLE => 'Thumbnailer',
	Module::T_DESCRIPTION => 'Create thumbnails on the fly',
	Module::T_NAMESPACE => __NAMESPACE__,
	Module::T_PERMISSION => false,
	Module::T_REQUIRES => array
	(
		"registry" => "1.x"
	)
);