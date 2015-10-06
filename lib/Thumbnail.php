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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\Image;

use Brickrouge\Element;

/**
 * Representation of an image thumbnail.
 *
 * Instances of the class are used to create IMG elements. The parameters used to create the
 * thumbnail can be specified as a serialized string, an array of options, a version name, or
 * a version instance.
 *
 * @property $width int|null Width of the thumbnail, or `null` if it is not defined.
 * @property $height int|null Height of the thumbnail, or `null`if it is not defined.
 * @property $method string|null Resizing method, or `null` if it is not defined.
 * @property $format string|null Image format, or `null` if it is not defined.
 * @property $version Version|null The {@link Version} instance used to created the thumbnail,
 * or `null` if it is not defined.
 *
 * @property $w int|null Alias to {@link $width}.
 * @property $h int|null Alias to {@link $height}.
 * @property $m string|null Alias to {@link $method}.
 * @property $f string|null Alias to {@link $format}.
 * @property $v Version|null Alias to {@link $version}.
 *
 * @property array $options Options used to create the thumbnail.
 * @property-read array $filtered_options Filtered thumbnail options.
 * @property-read string $url The URL of the thumbnail.
 * @property-read string $url_base The base to build the URL of the thumbnail.
 * @property-read array $final_size The final size (width and height) of the thumbnail.
 */
class Thumbnail
{
	use AccessorTrait;

	/**
	 * The default values of the parameters that can be used to create a path.
	 *
	 * @var array
	 */
	static private $path_params_defaults = [

		'width' => null,
		'height' => null,
		'method' => null,
		'format' => null

	];

	/**
	 * Format the specified options as a path.
	 *
	 * Only the `width`, `height`, `method` and `format` options are used to create the path.
	 *
	 * If it is not defined, the `method` option is inferred from the `width` and `height`
	 * options. For instance, If the `width` option is defined but the `height` option is empty,
	 * the `method` option is set to `fixed-width`. Similarly, if the `height` option is
	 * defined but the `width` option is empty, the `method` option is set to `fixed-height`.
	 *
	 * The `format` option is used as the extension of the path. e.g. "200x300.png".
	 *
	 * @param array $options
	 *
	 * @return string|null A path, or `null` if both `width` and `height` are empty.
	 */
	static public function format_options_as_path(array $options)
	{
		$options = Version::widen($options) + self::$path_params_defaults;

		$w = $options['width'];
		$h = $options['height'];

		if (!$w && !$h)
		{
			return null;
		}

		$m = $options['method'];

		if (!$m && (!$w || !$h))
		{
			$m = $w ? 'fixed-width' : 'fixed-height';
		}

		$f = $options['format'];

		$rc = "/{$w}x{$h}";

		if ($m)
		{
			$rc .= "/{$m}";
		}

		if ($f)
		{
			$rc .= ".{$f}";
		}

		return $rc;
	}

	/**
	 * Formats the options as a query string.
	 *
	 * @param array $options The options to format. They are filtered using
	 * {@link Version::filter()}.
	 * @param bool $remove_path_params Optionally the options that can be used to format a path
	 * using the {@link format_options_as_path()} function can be filtered out.
	 *
	 * @return string A query string. Note that the option that are actually used to create the
	 * query string are shortened using the {@link Version::shorten()} method.
	 */
	static public function format_options_as_query_string(array $options, $remove_path_params=false)
	{
		$options = Version::filter($options);

		if ($remove_path_params)
		{
			$options = array_diff_key($options, self::$path_params_defaults);
		}

		$options = Version::shorten($options);

		return http_build_query($options);
	}

	/**
	 * The source of the thumbnail.
	 *
	 * @var mixed
	 */
	public $src;

	/**
	 * Options to create the thumbnail.
	 *
	 * @var array
	 */
	public $options = [];

	/**
	 * Version name of the thumbnail.
	 *
	 * @var string
	 */
	protected $version_name;

	/**
	 * Constructor.
	 *
	 * @param mixed $src The source of the thumbnail.
	 *
	 * @param string|array $options The options to create the thumbnail can be provided as a
	 * version name or an array of options. If a version name is provided, the `image` parameter
	 * must also be provided.
	 *
	 * @param string|array $additional_options Additional options to create the thumbnail.
	 */
	public function __construct($src, $options=null, $additional_options=null)
	{
		if (is_string($options))
		{
			$unserialized_version = Version::unserialize($options);

			if ($unserialized_version)
			{
				$options = $unserialized_version;
			}
			else
			{
				$this->version_name = $options;
			}
		}

		if ($options instanceof Version)
		{
			$this->options = $options->to_array();
		}
		else if (is_array($options))
		{
			$this->options = Version::normalize($options);
		}

		if ($additional_options)
		{
			if (is_string($additional_options))
			{
				$additional_options = Version::unserialize($additional_options);
			}

			$this->options = $additional_options + $this->options;
		}

		$this->src = $src;
	}

	/**
	 * Handles version options.
	 *
	 * @inheritdoc
	 */
	public function __get($property)
	{
		if (isset(Version::$shorthands[$property]))
		{
			$property = Version::$shorthands[$property];
		}

		if (array_key_exists($property, Version::$defaults))
		{
			return $this->get_option($property);
		}

		return $this->accessor_get($property);
	}

	/**
	 * Handles version options.
	 *
	 * @inheritdoc
	 */
	public function __set($property, $value)
	{
		if (isset(Version::$shorthands[$property]))
		{
			$property = Version::$shorthands[$property];
		}

		if (array_key_exists($property, Version::$defaults))
		{
			$this->set_option($property, $value);

			return;
		}

		$this->accessor_set($property, $value);
	}

	private function get_option($property)
	{
		if (!empty($this->options[$property]))
		{
			return $this->options[$property];
		}

		if (!$this->version)
		{
			return null;
		}

		return $this->version->$property;
	}

	private function set_option($option, $value)
	{
		if ($value === null)
		{
			unset($this->options[$option]);
		}
		else
		{
			$this->options[$option] = $value;
		}
	}

	private $_version;

	protected function get_version()
	{
		if ($this->_version)
		{
			return $this->_version;
		}

		if (!$this->version_name)
		{
			return null;
		}

		return $this->_version = \ICanBoogie\app()->thumbnailer_versions[$this->version_name];
	}

	/**
	 * Returns the options, filtered.
	 *
	 * @return array
	 */
	protected function get_filtered_options()
	{
		return Version::filter($this->options);
	}

	/**
	 * Returns the thumbnail URL.
	 *
	 * @return string The thumbnail URL.
	 */
	protected function get_url()
	{
		$version_name = $this->version_name;
		$options = $this->filtered_options;

		$url = $this->url_base;

		if ($version_name)
		{
			$url .= '/' . $version_name;
		}
		else
		{
			$url .= self::format_options_as_path($options);
		}

		$query_string = self::format_options_as_query_string($options + [ 'src' => $this->src ], true);

		if ($query_string)
		{
			$url .= '?'. $query_string;
		}

		return $url;
	}

	/**
	 * Returns a base to build the thumbnail's URL.
	 *
	 * @return string
	 */
	protected function get_url_base()
	{
		return '/api/thumbnail';
	}

	/**
	 * Returns the final size (width and height) of the thumbnail.
	 *
	 * @return array
	 */
	protected function get_final_size()
	{
		$w = $this->w;
		$h = $this->h;
		$src = $this->src;

		if (is_string($src))
		{
			list($w, $h) = Image::compute_final_size($w, $h, $this->method, \ICanBoogie\DOCUMENT_ROOT . $src);
		}

		return [ $w , $h ];
	}

	/**
	 * Convert the thumbnail into a IMG element.
	 *
	 * The `width` and `height` attribute of the element are defined whenever possible. The `alt`
	 * attribute is also defined if the image src is an Image active record.
	 *
	 * @param array $attributes Additional attributes to create the {@link Element} instance.
	 *
	 * @return Element
	 */
	public function to_element(array $attributes=[])
	{
		list($w, $h) = $this->final_size;

		$class = 'thumbnail';
		$version_name = $this->version_name;

		if ($version_name)
		{
			$class .= ' thumbnail--' . \Brickrouge\normalize($version_name);
		}

		return new Element('img', $attributes + [

			'src' => $this->url,
			'alt' => '',
			'width' => $w,
			'height' => $h,
			'class' => $class

		]);
	}

	/**
	 * Return a IMG element that can be inserted as is in the document.
	 */
	public function __toString()
	{
		try
		{
			return (string) $this->to_element();
		}
		catch (\Exception $e)
		{
			echo \Brickrouge\render_exception($e);
		}
	}
}
