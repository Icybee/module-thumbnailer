<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Module\Descriptor;

return [

	Descriptor::CATEGORY => 'features',
	Descriptor::TITLE => "Thumbnailer",
	Descriptor::DESCRIPTION => "Create thumbnails on the fly",
	Descriptor::ID => 'thumbnailer',
	Descriptor::NS => __NAMESPACE__,
	Descriptor::PERMISSION => false,
	Descriptor::REQUIRES => [ 'registry' ]

];
