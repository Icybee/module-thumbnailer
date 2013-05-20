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
	 * @param \ICanBoogie\Core $core
	 */
	static public function prototype_get_thumbnailer_versions(\ICanBoogie\Core $core)
	{
		if (CACHE_VERSIONS)
		{
			$versions = $core->vars['cached_thumbnailer_versions'];

			if (!$versions)
			{
				$versions = self::collect($core);

				$core->vars['cached_thumbnailer_versions'] = $versions;
			}
		}
		else
		{
			$versions = self::collect($core);
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
	static private function collect(\ICanBoogie\Core $core)
	{
		$versions = array();
		$definitions = $core->registry
		->select('SUBSTR(name, LENGTH("thumbnailer.versions.") + 1) as name, value')
		->where('name LIKE ?', 'thumbnailer.versions.%')
		->pairs;

		foreach ($definitions as $name => $options)
		{
			if (!$options || !is_string($options) || $options{0} != '{')
			{
				\ICanBoogie\log_error('Bad version: %name, :options', array('name' => $name, 'options' => $options));

				continue;
			}

			$versions[$name] = Version::normalize(json_decode($options, true));
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
	public function __construct(array $versions=array())
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
		global $core;

		if (!($version instanceof Versions))
		{
			$version = new Version($version);
		}

		$options = $version->to_array(Version::ARRAY_FILTER | Version::ARRAY_SHORTEN);
		$core->registry["thumbnailer.versions.$name"] = json_encode($options);

		# revoke cache

		unset($core->vars['cached_thumbnailer_versions']);

		return $options;
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

/*
 * Exception
 */

/**
 * Exception thrown when a thumbnail version is not defined.
 *
 * @property-read string $version The thumbnail version identifier.
 */
class VersionNotDefined extends \InvalidArgumentException
{
	private $version;

	public function __construct($version, $code=500, \Exception $previous=null)
	{
		$this->version = $version;

		parent::__construct("Version not defined: $version.", $code, $previous);
	}

	public function __get($property)
	{
		if ($property == 'version')
		{
			return $this->version;
		}
	}
}

namespace ICanBoogie\Modules\Thumbnailer\Versions;

/**
 * Event class for the `ICanBoogie\Modules\Thumbnailer\Versions::alert` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param \ICanBoogie\Modules\Thumbnailer\Versions $target
	 */
	public function __construct(\ICanBoogie\Modules\Thumbnailer\Versions $target)
	{
		parent::__construct($target, 'alter');
	}
}