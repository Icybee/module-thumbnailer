<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie;

return [

	ICanBoogie\Core::class . '::lazy_get_thumbnailer_versions' => Versions::class . '::prototype_get_thumbnailer_versions'

];
