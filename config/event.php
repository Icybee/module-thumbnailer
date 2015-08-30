<?php

namespace ICanBoogie\Modules\Thumbnailer;

use Icybee;

$hooks = Hooks::class . '::';

return [

	Icybee\Block\ConfigBlock::class . '::alter_children' => $hooks . 'on_configblock_alter_children',
	Icybee\Operation\Module\ConfigOperation::class . '::properties:before' => $hooks . 'before_configoperation_properties',
	Icybee\Modules\Cache\CacheCollection::class . '::collect' => $hooks . 'on_cache_collection_collect'

];
