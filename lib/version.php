<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\ToArray;

/**
 * A thumbnail version.
 *
 * @property string $background Thumbnail background. Defaults to "transparent".
 * @property string $default Thumbnail fallback image. Defaults to `null`.
 * @property string $format Thumbnail format. Defaults to "jpeg".
 * @property string $filter Thumbnail filter. Defaults to `null`.
 * @property int $height Thumbnail height. Defaults to `null`.
 * @property string $method Thumbnail resizing method. Default to "fill".
 * @property bool $no_interlace Should the thumbnail *not* be interlaced. Default to `false`.
 * @property bool $no_upscale Should the thumbnail *not* be upscaled. Default to `false`.
 * @property string $overlay Thumbnail overlay path. Defaults to `null`.
 * @property string $path Path to the directory of the source image. Defaults to `null`.
 * @property int $quality Quality of the compression. Only applicable to JPEG. Defaults to 85.
 * @property string $src Path to the source image. Defaults to `null`.
 * @property int $width The width of the image. Defaults to `null`.
 *
 * @property string $b Alias to {@link $background}.
 * @property string $d Alias to {@link $default}.
 * @property string $f Alias to {@link $format}.
 * @property string $ft Alias to {@link $filter}.
 * @property int $h Alias to {@link $height}.
 * @property string $m Alias to {@link $method}.
 * @property bool $ni Alias to {@link $no_interlace}.
 * @property bool $nu Alias to {@link $no_upscale}.
 * @property string $o Alias to {@link $overlay}.
 * @property string $p Alias to {@link $path}.
 * @property int $q Alias to {@link $quality}.
 * @property string $s Alias to {@link $src}.
 * @property string $w alias to {@link $width}.
 */
class Version implements ToArray
{
	/**
	 * Option for the {@link to_array()} method.
	 *
	 * @var int
	 */
	const ARRAY_SHORTEN = 1;

	/**
	 * Option for the {@link to_array()} method.
	 *
	 * @var int
	 */
	const ARRAY_FILTER = 2;

	/**
	 * Options and their default value.
	 *
	 * @var array[string]mixed
	 */
	static public $defaults = array
	(
		'background' => 'transparent',
		'default' => null,
		'format' => null,
		'filter' => null,
		'height' => null,
		'method' => 'fill',
		'no-interlace' => false,
		'no-upscale' => false,
		'overlay' => null,
		'path' => null,
		'quality' => 90,
		'src' => null,
		'width' => null
	);

	/**
	 * Options shorthands.
	 *
	 * @var array[string]string
	 */
	static public $shorthands = array
	(
		'b' => 'background',
		'd' => 'default',
		'f' => 'format',
		'ft' => 'filter',
		'h' => 'height',
		'm' => 'method',
		'ni' => 'no-interlace',
		'nu' => 'no-upscale',
		'o' => 'overlay',
		'p' => 'path',
		'q' => 'quality',
		's' => 'src',
		'v' => 'version', // FIXME-20130507: remove this
		'w' => 'width'
	);

	/**
	 * Returns version options extracted from the URI.
	 *
	 * Options are extracted from the pathinfo (`width`, `height`, `method`, and `format`) as well
	 * as from the query string.
	 *
	 * @param string $uri The URI from which options should be extracted.
	 *
	 * @return \ICanBoogie\Modules\Thumbnailer\Version
	 */
	static public function from_uri($uri)
	{
		$options = [];
		$path = $uri;
		$query_string_position = strpos($uri, '?');

		if ($query_string_position)
		{
			$path = substr($uri, 0, $query_string_position);
			$query_string = substr($uri, $query_string_position + 1);
			parse_str($query_string, $options);
			$options = Version::widen($options);
		}

		$options += [

			'width' => null,
			'height' => null,
			'method' => null,
			'format' => null

		];

		preg_match('#/(\d+x\d+|\d+x|x\d+)(/([^/\.]+))?(\.([a-z]+))?#', $path, $matches);

		if ($matches)
		{
			list($w, $h) = explode('x', $matches[1]);

			if ($w)
			{
				$options['width'] = (int) $w;
			}

			if ($h)
			{
				$options['height'] = (int) $h;
			}

			if (isset($matches[3]))
			{
				$options['method'] = $matches[3];
			}

			if (isset($matches[5]))
			{
				$options['format'] = $matches[5];
			}

			if (!$options['method'] && (!$options['width'] || !$options['height']))
			{
				$options['method'] = $options['width'] ? 'fixed-width' : 'fixed-height';
			}

			if ($options['format'] && $options['format'] == 'jpg')
			{
				$options['format'] = 'jpeg';
			}
		}

		$options = array_filter($options);

		return new static($options);
	}

	/**
	 * Normalizes thumbnail options.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	static public function normalize(array $options)
	{
		foreach (self::$shorthands as $shorthand => $full)
		{
			if (isset($options[$shorthand]))
			{
				$options[$full] = $options[$shorthand];
			}
		}

		#
		# add defaults so that all options are defined
		#

		$options += self::$defaults;

		#
		# The parameters are filtered and sorted, making extraneous parameters and parameters order
		# non important.
		#

		$options = array_intersect_key($options, self::$defaults);

		ksort($options);

		return $options;
	}

	/**
	 * Filter thumbnail options.
	 *
	 * Options than match default values are removed. The options are normalized using
	 * {@link normalize_options()} before they are filtered.
	 *
	 * @param array $options
	 *
	 * @return array The filtered thumbnail options.
	 */
	static public function filter(array $options)
	{
		return array_diff_assoc(self::normalize($options), self::$defaults);
	}

	/**
	 * Shorten option names.
	 *
	 * Note: Extraneous options are not filtered.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	static public function shorten(array $options)
	{
		$rc = [];
		$longhands = array_flip(self::$shorthands);

		foreach ($options as $name => $value)
		{
			if (isset($longhands[$name]))
			{
				$name = $longhands[$name];
			}

			$rc[$name] = $value;
		}

		return $rc;
	}

	/**
	 * Widen option names.
	 *
	 * Note: Extraneous options are not filtered.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	static public function widen(array $options)
	{
		$rc = [];
		$shorthands = self::$shorthands;

		foreach ($options as $name => $value)
		{
			if (isset($shorthands[$name]))
			{
				$name = $shorthands[$name];
			}

			$rc[$name] = $value;
		}

		return $rc;
	}

	/**
	 * Unserialize serialized options.
	 *
	 * @param string $serialized_options
	 *
	 * @return array
	 */
	static public function unserialize($serialized_options)
	{
		preg_match_all('#([^:]+):\s*([^;]+);?#', $serialized_options, $matches, PREG_PATTERN_ORDER);

		return self::filter(array_combine($matches[1], $matches[2]));
	}

	/**
	 * Serializes options.
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	static public function serialize(array $options)
	{
		$options = self::filter($options);
		$options = self::shorten($options);
		$serialized_options = '';

		foreach ($options as $option => $value)
		{
			$serialized_options .= "$option:$value;";
		}

		return $serialized_options;
	}

	protected $options;

	/**
	 * Initializes and normalizes options.
	 *
	 * @param string|array $options
	 */
	public function __construct($options)
	{
		if (is_string($options))
		{
			$options = self::unserialize($options);
		}

		$this->options = self::normalize($options);
	}

	/**
	 * Translates a property name into an option name.
	 *
	 * @param string $property
	 *
	 * @return string
	 */
	static private function property_name_to_option_name($property)
	{
		if (isset(self::$shorthands[$property]))
		{
			$property = self::$shorthands[$property];
		}
		else
		{
			if ($property == 'no_interlace')
			{
				$property = 'no-interlace';
			}
			else if ($property == 'no_upscale')
			{
				$property = 'no-upscale';
			}
		}

		return $property;
	}

	/**
	 * Returns an option's value.
	 *
	 * @param string $property
	 *
	 * @return mixed
	 */
	public function __get($property)
	{
		$option = self::property_name_to_option_name($property);
		return $this->options[$option];
	}

	/**
	 * Sets an option's value.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value)
	{
		$option = self::property_name_to_option_name($property);
		$this->options[$option] = $value;
	}

	/**
	 * Returns a string representation of the instance.
	 *
	 * @return string
	 *
	 * @see Version::serialize
	 */
	public function __toString()
	{
		return self::serialize($this->options);
	}

	/**
	 * Returns the instance as an array.
	 *
	 * @param int $flags A bitmask of one or more of the following flags:
	 * - {@link ARRAY_FILTER} The options are filtered with {@link filter()}.
	 * - {@link ARRAY_SHORTEN} The options are shortened with {@link shorten()}.
	 *
	 * @return array
	 */
	public function to_array($flags=0)
	{
		$array = self::normalize($this->options);

		if ($flags & self::ARRAY_FILTER)
		{
			$array = self::filter($array);
		}

		if ($flags & self::ARRAY_SHORTEN)
		{
			$array = self::shorten($array);
		}

		return $array;
	}
}