<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Module\Descriptor;

return array
(
	Descriptor::CATEGORY => 'features',
	Descriptor::TITLE => 'Thumbnailer',
	Descriptor::DESCRIPTION => 'Create thumbnails on the fly',
	Descriptor::NS => __NAMESPACE__,
	Descriptor::PERMISSION => false,
	Descriptor::REQUIRES => array
	(
		"registry" => "1.x"
	)
);