<?php

namespace ICanBoogie\Modules\Thumbnailer;

$hooks = Hooks::class . '::';

use ICanBoogie;
use Icybee;

return [

	'events' => [

		Icybee\ConfigBlock::class . '::alter_children' => $hooks . 'on_configblock_alter_children',
		Icybee\ConfigOperation::class . '::properties:before' => $hooks . 'before_configoperation_properties',
		Icybee\Modules\Cache\CacheCollection::class . '::collect' => $hooks . 'on_cache_collection_collect'

	],

	'prototypes' => [

		ICanBoogie\Core::class . '::lazy_get_thumbnailer_versions' => Versions::class . '::prototype_get_thumbnailer_versions'

	]

];
