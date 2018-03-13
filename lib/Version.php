<?php

namespace ICanBoogie\Modules\Thumbnailer;

use ICanBoogie\ToArray;

/**
 * A thumbnail version.
 *
 * @property string $background Thumbnail background. Defaults to "transparent".
 * @property string $default Thumbnail fallback image. Defaults to `null`.
 * @property string $device_pixel_ratio Device pixel ratio. Defaults to `null`.
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
 * @property string $dpr Alias to {@link $device_pixel_ratio}.
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

	const OPTION_BACKGROUND = 'background';
	const OPTION_DEFAULT = 'default';
	const OPTION_DEVICE_PIXEL_RATIO = 'device-pixel-ratio';
	const OPTION_FORMAT = 'format';
	const OPTION_FILTER = 'filter';
	const OPTION_HEIGHT = 'height';
	const OPTION_METHOD = 'method';
	const OPTION_NO_INTERLACE = 'no-interlace';
	const OPTION_NO_UPSCALE = 'no-upscale';
	const OPTION_OVERLAY = 'overlay';
	const OPTION_PATH = 'path';
	const OPTION_QUALITY = 'quality';
	const OPTION_SRC = 'src';
	const OPTION_WIDTH = 'width';

	/**
	 * Options and their default value.
	 *
	 * @var array[string]mixed
	 */
	static public $defaults = [

		self::OPTION_BACKGROUND => null,
		self::OPTION_DEFAULT => null,
		self::OPTION_DEVICE_PIXEL_RATIO => null,
		self::OPTION_FILTER => null,
		self::OPTION_FORMAT => null,
		self::OPTION_HEIGHT => null,
		self::OPTION_METHOD => 'fill',
		self::OPTION_NO_INTERLACE => false,
		self::OPTION_NO_UPSCALE => false,
		self::OPTION_OVERLAY => null,
		self::OPTION_PATH => null,
		self::OPTION_QUALITY => 90,
		self::OPTION_SRC => null,
		self::OPTION_WIDTH => null

	];

	/**
	 * Options shorthands.
	 *
	 * @var array[string]string
	 */
	static public $shorthands = [

		'b'   => self::OPTION_BACKGROUND,
		'd'   => self::OPTION_DEFAULT,
		'dpr' => self::OPTION_DEVICE_PIXEL_RATIO,
		'ft'  => self::OPTION_FILTER,
		'f'   => self::OPTION_FORMAT,
		'h'   => self::OPTION_HEIGHT,
		'm'   => self::OPTION_METHOD,
		'ni'  => self::OPTION_NO_INTERLACE,
		'nu'  => self::OPTION_NO_UPSCALE,
		'o'   => self::OPTION_OVERLAY,
		'p'   => self::OPTION_PATH,
		'q'   => self::OPTION_QUALITY,
		's'   => self::OPTION_SRC,
		'v'   => 'version', // FIXME-20130507: remove this
		'w'   => self::OPTION_WIDTH

	];

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
		$options = self::extract_options_from_uri($uri);
		$options = self::normalize($options);

		return new static($options);
	}

	static private function extract_options_from_uri($uri)
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

			self::OPTION_WIDTH => null,
			self::OPTION_HEIGHT => null,
			self::OPTION_METHOD => null,
			self::OPTION_FORMAT => null

		];

		preg_match('#/?(\d+x\d+|\d+x|x\d+)(/([^/\.]+))?(\.([a-z]+))?#', $path, $matches);

		if ($matches)
		{
			list($w, $h) = explode('x', $matches[1]);

			if ($w)
			{
				$options[self::OPTION_WIDTH] = (int) $w;
			}

			if ($h)
			{
				$options[self::OPTION_HEIGHT] = (int) $h;
			}

			if (isset($matches[3]))
			{
				$options[self::OPTION_METHOD] = $matches[3];
			}

			if (isset($matches[5]))
			{
				$options[self::OPTION_FORMAT] = $matches[5];
			}

			if ($options[self::OPTION_FORMAT] && $options[self::OPTION_FORMAT] == 'jpg')
			{
				$options[self::OPTION_FORMAT] = 'jpeg';
			}
		}

		return array_filter($options);
	}

	/**
	 * Normalizes thumbnail options.
	 *
	 * The method fixes the resizing method according to the `width` and `height` option:
	 *
	 * - If `width` and `height` are defined but `method` is empty, `method` is set to "fill".
	 * - If `width` is defined but `height` is empty, `method` is set to "fixed-width".
	 * - If `height` is defined but `width` is empty, `method` is set to "fixed-height".
	 * - If `width`, `height` and `method` are empty, `method` is set to the default value "fill".
	 *
	 * Thus if `method` is defined as "surface", but only the `width` is defined, `method` is set
	 * to "fixed-width".
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

		$options += [

			self::OPTION_WIDTH => null,
			self::OPTION_HEIGHT => null,
			self::OPTION_METHOD => null

		];

		$m = $options[self::OPTION_METHOD];
		$w = $options[self::OPTION_WIDTH];
		$h = $options[self::OPTION_HEIGHT];

		if ($w && $h && !$m)
		{
			$m = 'fill';
		}
		else if ($w && !$h)
		{
			$m = 'fixed-width';
		}
		else if (!$w && $h)
		{
			$m = 'fixed-height';
		}

		if ($m)
		{
			$options[self::OPTION_METHOD] = $m;
		}
		else
		{
			unset($options[self::OPTION_METHOD]);
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
	 * Filter options.
	 *
	 * The options are normalized using {@link normalize()} before they are filtered.
	 * Options than match default values are removed. If `method` equals the implicit resizing
	 * method the option is removed.
	 *
	 * @param array $options
	 *
	 * @return array The filtered thumbnail options.
	 */
	static public function filter(array $options)
	{
		$options = self::normalize($options);

		$w = $options[self::OPTION_WIDTH];
		$h = $options[self::OPTION_HEIGHT];
		$m = $options[self::OPTION_METHOD];

		if (($w && $h && $m === 'fill')
		|| ($w && !$h && $m === 'fixed-width')
		|| (!$w && $h && $m === 'fixed-height'))
		{
			unset($options[self::OPTION_METHOD]);
		}

		return array_diff_assoc($options, self::$defaults);
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
		#
		# JSON style
		#
		if (is_string($serialized_options) && strlen($serialized_options) > 2 && $serialized_options{0} === '{')
		{
			$options = json_decode($serialized_options, true);
			$options = self::filter($options);
			return $options;
		}

		#
		# CSS style
		#

		if (!preg_match('#\d+x\d+|\d+x|x\d+#', $serialized_options))
		{
			preg_match_all('#([^:]+):\s*([^;]+);?#', $serialized_options, $matches, PREG_PATTERN_ORDER);

			return self::filter(array_combine($matches[1], $matches[2]));
		}

		#
		# URL style
		#

		$options = self::extract_options_from_uri($serialized_options);
		$options = self::filter($options);

		return $options;
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
		$options = self::shorten($options) + [

			'w' => null,
			'h' => null,
			'm' => null,
			'f' => null

		];

		$rc = '';
		$w = $options['w'];
		$h = $options['h'];

		if ($w || $h)
		{
			$rc .= "{$w}x{$h}";

			$m = $options['m'];

			if ($w && $h && $m === 'fill')
			{
				$m = null;
			}
			else if ($w && !$h && $m === 'fixed-width')
			{
				$m = null;
			}
			else if (!$w && $h && $m === 'fixed-height')
			{
				$m = null;
			}

			if ($m)
			{
				$rc .= "/{$m}";
			}

			$f = $options['f'];

			if ($f)
			{
				$rc .= ".{$f}";
			}
		}

		unset($options['w']);
		unset($options['h']);
		unset($options['m']);
		unset($options['f']);

		if ($options)
		{
			$rc .= '?' . http_build_query($options);
		}

		return $rc;
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
		static $mapping = [

			'no_interlace' => self::OPTION_NO_INTERLACE,
			'no_upscale' => self::OPTION_NO_UPSCALE,
			'device_pixel_ratio' => self::OPTION_DEVICE_PIXEL_RATIO,

		];

		if (isset(self::$shorthands[$property]))
		{
			$property = self::$shorthands[$property];
		}
		else if (isset($mapping[$property]))
		{
			$property = $mapping[$property];
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
	 * @param int $flags A bit mask of one or more of the following flags:
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
