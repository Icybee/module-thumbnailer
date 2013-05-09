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
	private static $instance;

	/**
	 * Returns a unique instance.
	 *
	 * @return Versions
	 */
	static public function get()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		$class = get_called_class();

		return self::$instance = new $class;
	}

	protected $versions;

	protected function __construct()
	{
		global $core;

		if (empty($core))
		{
			return;
		}

		if (CACHE_VERSIONS)
		{
			$versions = $core->vars['cached_thumbnailer_versions'];

			if (!$versions)
			{
				$versions = $this->collect();

				$core->vars['cached_thumbnailer_versions'] = $versions;
			}
		}
		else
		{
			$versions = $this->collect();
		}

		$this->versions = $versions;
	}

	/**
	 * Collects versions.
	 *
	 * {@link Version\CollectEvent} is fired.
	 *
	 * @return array[string]array
	 */
	protected function collect()
	{
		global $core;

		$versions = array();
		$definitions = $core->registry
		->select('SUBSTR(name, LENGTH("thumbnailer.versions.") + 1) as name, value')
		->where('name LIKE ?', 'thumbnailer.versions.%')
		->pairs;

		foreach ($definitions as $name => $options)
		{
			if (!$options || !is_string($options) || $options{0} != '{')
			{
				\ICanBoogie\log_error('bad version: %name, :options', array('name' => $name, 'options' => $options));

				continue;
			}

			$versions[$name] = Version::normalize(json_decode($options, true));
		}

		new Versions\CollectEvent($this, array('versions' => &$versions));

		return $versions;
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
 * Event class for the `ICanBoogie\Modules\Thumbnailer\Versions::collect` event.
 */
class CollectEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the thumbnail versions.
	 *
	 * @var array[string]array
	 */
	public $versions;

	/**
	 * The event is constructed with the type `collect`.
	 *
	 * @param \ICanBoogie\Modules\Thumbnailer\Versions $target
	 * @param array $payload
	 */
	public function __construct(\ICanBoogie\Modules\Thumbnailer\Versions $target, array $payload)
	{
		parent::__construct($target, 'collect', $payload);
	}
}