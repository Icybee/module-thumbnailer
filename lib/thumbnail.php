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

use Brickrouge\Element;

/**
 * Cette classes est une aide à la création de miniatures. Elle prend en paramètres une source et
 * un tableau d'option ou le nom d'une version, et permet d'obtenir l'URL de la miniature ou le
 * marqueur IMG de la miniature.
 *
 * La source peut être définie par l'URL d'une image ou une instance de la classe
 * {@link Icybee\Modules\Images\Image}. Les options peuvent être un tableau de paramètres ou le
 * nom d'une version.
 *
 * @property $version array|null Il s'agit des paramètres correspondant à la version.
 * @property $w int|null Largeur de la miniature, extraite des options ou de la version.
 * @property $h int|null Hauteur de la miniature, extraite des options ou de la version.
 * @property $method string|null Méthode de redimensionnement de la miniature, extraite des options
 * ou de la version.
 * @property-read array $filtered_options Filtered thumbnail options.
 * @property-read string $url The URL of the thumbnail.
 */
class Thumbnail extends \ICanBoogie\Object
{
	/**
	 * Parameters that can be used to create a path.
	 *
	 * @var array
	 */
	static private $path_params = [

		'width',
		'height',
		'method',
		'format'

	];

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

	static public function format_options_as_path(array $options)
	{
		$options += self::$path_params_defaults;

		$w = $options['width'];
		$h = $options['height'];

		if (!$w && !$h)
		{
			return;
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
	 * @var \ICanBoogie\ActiveRecord|int|string
	 */
	public $src;

	/**
	 * Options to create the thumbnail.
	 *
	 * @var array
	 */
	public $options = array();

	/**
	 * Version name of the thumbnail.
	 *
	 * @var string
	 */
	protected $version_name;

	/**
	 * Constructor.
	 *
	 * @param Icybee\Modules\Images\Image|int|string The souce of the thumbnail.
	 *
	 * @param string|array $options The options to create the thumbnail can be provided as a
	 * version name or an array of options. If a version name is provided, the `image` parameter
	 * must also be provided.
	 *
	 * @param string|array $additionnal_options Additionnal options to create the thumbnail.
	 */
	public function __construct($src, $options=null, $additionnal_options=null)
	{
		if (is_string($options))
		{
			if (strpos($options, ':') !== false)
			{
				$options = Version::unserialize($options);
			}
			else
			{
				$this->version_name = $options;
			}
		}

		if (is_array($options))
		{
			$this->options = Version::normalize($options);
		}

		if ($additionnal_options)
		{
			if (is_string($additionnal_options))
			{
				$additionnal_options = Version::unserialize($additionnal_options);
			}

			$this->options = $additionnal_options + $this->options;
		}

		$this->src = $src;
	}

	private $_version;

	protected function get_version()
	{
		global $core;

		if ($this->_version)
		{
			return $this->_version;
		}
		else if (!$this->version_name)
		{
			return;
		}

		return $core->thumbnailer_versions[$this->version_name];
	}

	private function get_option($property)
	{
		if (!empty($this->options[$property]))
		{
			return $this->options[$property];
		}

		if (!$this->version)
		{
			return;
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

	/**
	 * Returns the options, filtered.
	 *
	 * @return array
	 */
	protected function get_filtered_options()
	{
		return Version::filter($this->options);
	}

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

		return parent::__get($property);
	}

	/**
	 * Returns the width of the thumbnail.
	 *
	 * The width of the thumbnail is extracted from the options or the version parameters.
	 *
	 * @return int|null The width of the thumbnail or null if it's not available.
	 */
	protected function get_w()
	{
		return $this->get_option('width');
	}

	protected function set_w($weight)
	{
		$this->set_option('weight', $weight);
	}

	/**
	 * Returns the height of the thumbnail.
	 *
	 * The height of the thumbnail is extracted from the options or the version's parameters.
	 *
	 * @return int|null The height of the thumbnail or null if it's not available.
	 */
	protected function get_h()
	{
		return $this->get_option('height');
	}

	protected function set_h($height)
	{
		$this->set_option('height', $height);
	}

	/**
	 * Returns the name of the method used to resize the image.
	 *
	 * The resizing method of the thumbnail is extracted from the options or the version's
	 * parameters.
	 *
	 * @return int|null The name of method used to resize the image or null if it's not
	 * available.
	 */
	protected function get_method()
	{
		return $this->get_option('method');
	}

	protected function set_method($method)
	{
		$this->set_option('method', $method);
	}

	/**
	 * Returns the thumbnail URL.
	 *
	 * @return string The thumbnail URL.
	 */
	protected function get_url()
	{
		$w = $this->w;
		$h = $this->h;
		$src = $this->src;
		$method = $this->method;
		$version_name = $this->version_name;

		$options = $this->filtered_options;
		$options['src'] = $src;
// 		$options['version'] = $version_name;

		$url = '/api/thumbnail';

		if ($version_name)
		{
			$url .= '/' . $version_name;
		}
		else
		{
			if ($w || $h)
			{
				$url .= self::format_options_as_path($options);
			}
		}

		$query_string = self::format_options_as_query_string($options, true);

		if ($query_string)
		{
			$url .= '?'. $query_string;
		}

		return $url;
	}

	/**
	 * Convert the thumbnail into a IMG element.
	 *
	 * @param array $attributes
	 *
	 * @return \Brickrouge\Element
	 */
	public function to_element(array $attributes=[])
	{
		$w = $this->w;
		$h = $this->h;
		$src = $this->src;

		if (is_string($src))
		{
			$size_reference = \Brickrouge\DOCUMENT_ROOT . $src;

			list($w, $h) = \ICanBoogie\Image::compute_final_size($w, $h, $this->method, $size_reference);
		}

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
	 *
	 * The `width` and `height` attribute of the element are defined whenever possible. The `alt`
	 * attribute is also defined if the image src is an Image active record.
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