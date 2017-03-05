<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie;

return [

	ICanBoogie\Application::class . '::lazy_get_thumbnailer_versions' => Versions::class . '::prototype_get_thumbnailer_versions'

];
