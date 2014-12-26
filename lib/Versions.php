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

const CACHE_VERSIONS = true;

class Versions implements \ArrayAccess, \IteratorAggregate
{
	/**
	 * Creates a {@link Versions} instance and initializes it with the defined versions.
	 *
	 * The event `ICanBoogie\Modules\Thumbnailer\Versions::alter` of class
	 * {@link ICanBoogie\Modules\Thumbnailer\Versions\AlterEvent} is fired to allow third parties
	 * to alter the instance.
	 *
	 * @param \ICanBoogie\Core $app
	 */
	static public function prototype_get_thumbnailer_versions(\ICanBoogie\Core $app)
	{
		if (CACHE_VERSIONS)
		{
			$versions = $app->vars['cached_thumbnailer_versions'];

			if (!$versions)
			{
				$versions = self::collect($app);

				$app->vars['cached_thumbnailer_versions'] = $versions;
			}
		}
		else
		{
			$versions = self::collect($app);
		}

		$instance = new static($versions);

		new Versions\AlterEvent($instance);

		return $instance;
	}

	/**
	 * Collects versions.
	 *
	 * @return array[string]array
	 */
	static private function collect(\ICanBoogie\Core $app)
	{
		$versions = [];
		$definitions = $app->registry
		->select('SUBSTR(name, LENGTH("thumbnailer.versions.") + 1) as name, value')
		->where('name LIKE ?', 'thumbnailer.versions.%')
		->pairs;

		foreach ($definitions as $name => $serialized_options)
		{
			$versions[$name] = Version::unserialize($serialized_options);
		}

		return $versions;
	}

	/**
	 * Defined versions.
	 *
	 * @var array[string]mixed
	 */
	protected $versions;

	/**
	 * Initializes the specified versions.
	 *
	 * @param array $versions
	 */
	public function __construct(array $versions=[])
	{
		foreach ($versions as $name => $version)
		{
			$this[$name] = $version;
		}
	}

	/**
	 * Saves the versions.
	 */
	public function save()
	{
		foreach ($this->versions as $name => $version)
		{
			$this->save_version($name, $version);
		}
	}

	/**
	 * Persists a version.
	 *
	 * @param string $name Name of the version.
	 * @param Version|array|string $version Version to persist. The version can be specified as
	 * a {@link Version} instance, an array or as a string (a serialized version).
	 *
	 * @return array The options actually saved.
	 */
	public function save_version($name, $version)
	{
		$app = \ICanBoogie\app();

		if (!($version instanceof Versions))
		{
			$version = new Version($version);
		}

		$app->registry["thumbnailer.versions.$name"] = (string) $version;

		# revoke cache

		unset($app->vars['cached_thumbnailer_versions']);

		return $version;
	}

	/**
	 * Checks if a version exists.
	 */
	public function offsetExists($version)
	{
		return isset($this->versions[$version]);
	}

	/**
	 * Returns the definition of a version.
	 *
	 * @throws VersionNotDefined in attempt to get a version that is not defined.
	 */
	public function offsetGet($version)
	{
		if (!$this->offsetExists($version))
		{
			throw new VersionNotDefined($version);
		}

		$v = $this->versions[$version];

		if (!($v instanceof Version))
		{
			$v = new Version($v);
			$this->versions[$version] = $v;
		}

		return $v;
	}

	/**
	 * Sets a version.
	 *
	 * @param string $version Name of the version
	 * @param array[string]mixed Options of the version.
	 */
	public function offsetSet($version, $options)
	{
		$this->versions[$version] = $options;
	}

	/**
	 * Deletes a version.
	 */
	public function offsetUnset($version)
	{
		unset($this->versions[$version]);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->versions);
	}
}