<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\Updater\Update;
use ICanBoogie\Updater\AssertionFailed;

/**
 * @module thumbnailer
 */
class Update20140918 extends Update
{
	public function update_registry()
	{
		$model = $this->app->models['registry'];
		$values = $model
		->select('name, value')
		->where('name LIKE "thumbnailer.versions.%" AND value LIKE "{%"')
		->pairs;

		if (!$values)
		{
			throw new AssertionFailed(__FUNCTION__, [ "thumbnailer.versions" ]);
		}

		foreach ($values as $name => $value)
		{
			$version = new Version(json_decode($value, true));

			if ($version->q == 80)
			{
				$version->q = Version::$defaults['quality'];
			}

			$model[$name] = (string) $version;
		}
	}
}
