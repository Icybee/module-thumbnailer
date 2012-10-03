<?php

namespace ICanBoogie\Modules\Thumbnailer;

return array
(
	__NAMESPACE__ . '\CacheManager' => $path . 'lib/cache-manager.php',
	__NAMESPACE__ . '\Thumbnail' => $path . 'lib/thumbnail.php',
	__NAMESPACE__ . '\Versions' => $path . 'lib/versions.php',

	'Brickrouge\Widget\AdjustThumbnailOptions' => $path . 'elements/adjust-thumbnail-options.php',
	'Brickrouge\Widget\AdjustThumbnailVersion' => $path . 'elements/adjust-thumbnail-version.php',
	'Brickrouge\Widget\PopThumbnailVersion' => $path . 'elements/pop-thumbnail-version.php'
);