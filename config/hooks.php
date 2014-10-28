<?php

namespace ICanBoogie\Modules\Thumbnailer;

$hooks = __NAMESPACE__ . '\Hooks::';

return [

	'events' => [

		'Icybee\ConfigBlock::alter_children' => $hooks . 'on_configblock_alter_children',
		'Icybee\ConfigOperation::properties:before' => $hooks . 'before_configoperation_properties',
		'Icybee\Modules\Cache\Collection::collect' => $hooks . 'on_cache_collection_collect'

	],

	'prototypes' => [

		'ICanBoogie\Core::lazy_get_thumbnailer_versions' => __NAMESPACE__ . '\Versions::prototype_get_thumbnailer_versions'

	]

];