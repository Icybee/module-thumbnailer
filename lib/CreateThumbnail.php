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

use ICanBoogie\Image;

/**
 * Creates a thumbnail given a source and a version.
 */
class CreateThumbnail
{
	/**
	 * Decodes background.
	 *
	 * @param string $background
	 *
	 * @return array
	 */
	static private function decode_background($background)
	{
		$parts = explode(',', $background);

		$parts[0] = Image::decode_color($parts[0]);

		if (count($parts) == 1)
		{
			return [ $parts[0], null, 0 ];
		}

		$parts[1] = Image::decode_color($parts[1]);

		return $parts;
	}

	/**
	 * Creates a thumbnail give a source and a version.
	 *
	 * @param string $source Pathname of the source.
	 * @param string $destination Pathname of the thumbnail.
	 * @param Version $version
	 *
	 * @return string Pathname of the thumbnail.
	 *
	 * @throws \Exception
	 */
	public function __invoke($source, $destination, $version)
	{
		$image = Image::load($source, $info);

		if (!$image)
		{
			throw new \Exception($this->format('Unable to load image from file %path', [ '%path' => $source ]));
		}

		$fill_callback = $this->resolve_fill_callback($version);

		$this->apply_resize($image, $version, $info, $fill_callback);
		$this->apply_filter($image, $version);
		$this->apply_overlay($image, $version);
		$this->apply_interlace($image, $version);
		$this->apply_format($image, $version, $destination, $fill_callback);

		imagedestroy($image);

		return $destination;
	}

	/**
	 * Resolves fill callback.
	 *
	 * @param Version $version
	 *
	 * @return \Closure|null
	 */
	protected function resolve_fill_callback(Version $version)
	{
		if (!$version->background)
		{
			return null;
		}

		$background = self::decode_background($version->background);

		if (!$background)
		{
			return null;
		}

		return function($image, $w, $h) use ($background) {

			$this->fill_with_background($image, $w, $h, $background);

		};
	}

	/**
	 * Applies resize to the image.
	 *
	 * @param resource $image
	 * @param Version $version
	 * @param array $info
	 * @param callable|null $fill_callback
	 *
	 * @throws \Exception
	 */
	protected function apply_resize(&$image, Version $version, array $info, callable $fill_callback = null)
	{
		$w = $version->width;
		$h = $version->height;

		list($ow, $oh) = $info;

		$method = $version->method;

		if ($version->no_upscale)
		{
			if ($method == Image::RESIZE_SURFACE)
			{
				if ($w * $h > $ow * $oh)
				{
					$w = $ow;
					$h = $oh;
				}
			}
			else
			{
				if ($w > $ow)
				{
					$w = $ow;
				}

				if ($h > $oh)
				{
					$h = $ow;
				}
			}
		}

		$image = Image::resize($image, $w, $h, $method, $fill_callback);

		if (!$image)
		{
			throw new \Exception($this->format('Unable to resize image, version: !version', [

				'version' => $version

			]));
		}
	}

	/**
	 * Applies filter to the image.
	 *
	 * @param resource $image
	 * @param Version $version
	 */
	protected function apply_filter($image, Version $version)
	{
		$filter = $version->filter;

		if ($filter != 'grayscale')
		{
			return;
		}

		imagefilter($image, IMG_FILTER_GRAYSCALE);
	}

	/**
	 * Applies an overlay to the image.
	 *
	 * @param resource $image
	 * @param Version $version
	 */
	protected function apply_overlay($image, Version $version)
	{
		if (!$version->overlay)
		{
			return;
		}

		$overlay_file = \ICanBoogie\DOCUMENT_ROOT . $version->overlay;

		list($o_w, $o_h) = getimagesize($overlay_file);

		$overlay_source = imagecreatefrompng($overlay_file);

		imagecopyresampled($image, $overlay_source, 0, 0, 0, 0, imagesx($image), imagesy($image), $o_w, $o_h);
	}

	/**
	 * Applies interlace to the image.
	 *
	 * @param resource $image
	 * @param Version $version
	 */
	protected function apply_interlace($image, Version $version)
	{
		if ($version->no_interlace)
		{
			return;
		}

		imageinterlace($image, true);
	}

	/**
	 * Applies format ans saves the image.
	 *
	 * @param resource $image
	 * @param Version $version
	 * @param string $destination
	 * @param callable|null $fill_callback
	 *
	 * @throws \Exception when the image cannot be saved.
	 */
	protected function apply_format($image, Version $version, $destination, callable $fill_callback = null)
	{
		static $functions = [

			'gif' => 'imagegif',
			'jpeg' => 'imagejpeg',
			'png' => 'imagepng'

		];

		$format = $version->format;
		$function = $functions[$format];
		$args = [ $image, $destination ];

		if ($format == 'jpeg')
		{
			#
			# add quality option for the 'jpeg' format
			#

			$args[] = $version->quality;
		}
		else if ($format == 'png' && !$fill_callback)
		{
			#
			# If there is no background callback defined, the image is defined as transparent in
			# order to obtain a transparent thumbnail when the resulting image is centered.
			#

			imagealphablending($image, false);
			imagesavealpha($image, true);
		}

		if (!call_user_func_array($function, $args))
		{
			throw new \Exception('Unable to save thumbnail');
		}
	}

	/**
	 * Fills image background.
	 *
	 * @param resource $image
	 * @param int $w
	 * @param int $h
	 * @param mixed $background
	 */
	protected function fill_with_background($image, $w, $h, $background)
	{
		#
		# We create Image::draw_grid() arguments from the dimensions of the image
		# and the values passed using the 'background' parameter.
		#

		$args = (array) $background;

		array_unshift($args, $image, 0, 0, (int) $w - 1, (int) $h - 1);

		call_user_func_array(Image::class . '::draw_grid', $args);
	}

	/**
	 * Formats a string.
	 *
	 * @param string $string
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function format($string, array $args = [])
	{
		return \ICanBoogie\format($string, $args);
	}
}
