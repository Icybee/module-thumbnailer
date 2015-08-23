<?php

namespace ICanBoogie\Modules\Thumbnailer;

use Icybee;

$hooks = Hooks::class . '::';

return [

	Icybee\ConfigBlock::class . '::alter_children' => $hooks . 'on_configblock_alter_children',
	Icybee\ConfigOperation::class . '::properties:before' => $hooks . 'before_configoperation_properties',
	Icybee\Modules\Cache\CacheCollection::class . '::collect' => $hooks . 'on_cache_collection_collect'

];
