<?php

namespace ICanBoogie\Modules\Thumbnailer;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\ConfigBlock::alter_children' => $hooks . 'on_configblock_alter_children',
		'Icybee\ConfigOperation::properties:before' => $hooks . 'before_configoperation_properties',
		'Icybee\Modules\Cache\Collection::collect' => $hooks . 'on_cache_collection_collect'
	),

	'prototypes' => array
	(
		'Icybee\Modules\Images\Image::thumbnail' => $hooks . 'method_thumbnail',
		'Icybee\Modules\Images\Image::get_thumbnail' => $hooks . 'method_get_thumbnail'
	)
);