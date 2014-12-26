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

use ICanBoogie\FileCache;
use ICanBoogie\HTTP\HTTPError;
use ICanBoogie\HTTP\NotFound;
use ICanBoogie\HTTP\Request;
use ICanBoogie\I18n;
use ICanBoogie\Image;
use ICanBoogie\Operation;

/**
 * @property Modules\Thumbnailer $module
 * @property string $repository Path to the thumbnails repository.
 * @property FileCache $cache Thumbnails cache manager.
 */
class GetOperation extends Operation
{
	const VERSION = '2.1';

	static public $background;

	/**
	 * Tries to create a {@link Version} instance from the request.
	 *
	 * @param Request $request
	 *
	 * @return \ICanBoogie\Modules\Thumbnailer\Version
	 */
	protected function resolve_version(Request $request)
	{
		$version_name = $request['version'] ?: $request['v'];

		if ($version_name)
		{
			$version = $this->app->thumbnailer_versions[$version_name];
		}
		else
		{
			$version = Version::from_uri($request->uri);

			if (!$version)
			{
				$version = new Version($request->params);
			}
		}

		Image::assert_sizes($version->method, $version->width, $version->height); // TODO-20140423: $version->validate($errors)

		return $version;
	}

	/**
	 * Returns the location of the thumbnail on the server, relative to the document root.
	 *
	 * The thumbnail is created using the parameters supplied, if it is not already available in
	 * the cache.
	 *
	 * @param array $params
	 * @throws HTTPError
	 */
	public function get()
	{
		$version = clone $this->resolve_version($this->request);

		#
		# We check if the source file exists
		#

		$src = $version->src;
		$path = $version->path;

		if (!$src)
		{
			throw new NotFound('Missing thumbnail source.');
		}

		$src = $path . $src;
		$location = \ICanBoogie\DOCUMENT_ROOT . DIRECTORY_SEPARATOR . $src;

		if (!is_file($location))
		{
			$default = $version->default;

			#
			# use the provided default file is defined
			#

			if (!$default)
			{
				throw new NotFound(I18n\t('Thumbnail source not found: %src', [ '%src' => $src ]));
			}

			$src = $path . $default;
			$location = \ICanBoogie\DOCUMENT_ROOT . DIRECTORY_SEPARATOR . $src;

			if (!is_file($location))
			{
				throw new NotFound(I18n\t('Thumbnail source (default) not found: %src', [ '%src' => $src ]));
			}
		}

		if (!$version->format)
		{
			$info = getimagesize($location);
			$version->format = substr($info['mime'], 6);
		}

		if ($version->format == 'jpeg' && !$version->background)
		{
			$version->background = 'white';
		}

		#
		# We create a unique key for the thumbnail, using the image information
		# and the options used to create the thumbnail.
		#

		$key = filemtime($location) . '#' . filesize($location) . '#' . json_encode($version->to_array());
		$key = sha1($key) . '.' . $version->format;

		#
		# Use the cache object to get the file
		#

		$cache = new CacheManager;

		return $cache->retrieve($key, [ $this, 'get_construct' ], [ $location, $version ]);
	}

	/**
	 * Constructor for the cache entry.
	 *
	 * @param FileCache $cache The cache object.
	 * @param string $destination The file to create.
	 * @param array $userdata An array with the path of the original image and the options to use
	 * to create the thumbnail.
	 * @throws \Exception
	 */
	public function get_construct(FileCache $cache, $destination, $userdata)
	{
		list($path, $version) = $userdata;

		$callback = null;

		if ($version->background)
		{
			self::$background = self::decode_background($version->background);

			$callback = __CLASS__ . '::fill_callback';
		}

		$image = Image::load($path, $info);

		if (!$image)
		{
			throw new \Exception(\ICanBoogie\format('Unable to load image from file %path', [ '%path' => $path ]));
		}

		#
		# resize image
		#

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

		$image = Image::resize($image, $w, $h, $method, $callback);

		if (!$image)
		{
			throw new \Exception(\ICanBoogie\format('Unable to resize image for file %path with version: !version', [

				'%path' => $path,
				'!version' => $version

			]));
		}

		#
		# apply filters
		#

		$filter = $version->filter;

		if ($filter)
		{
			$this->apply_filter($image, $filter);
		}

		#
		# apply the overlay
		#

		if ($version->overlay)
		{
			$overlay_file = \ICanBoogie\DOCUMENT_ROOT . $version->overlay;

			list($o_w, $o_h) = getimagesize($overlay_file);

			$overlay_source = imagecreatefrompng($overlay_file);

			imagecopyresampled($image, $overlay_source, 0, 0, 0, 0, $w, $h, $o_w, $o_h);
		}

		#
		# interlace
		#

		if (!$version->no_interlace)
		{
			imageinterlace($image, true);
		}

		#
		# choose export format
		#

		$format = $version->format;

		static $functions = [

			'gif' => 'imagegif',
			'jpeg' => 'imagejpeg',
			'png' => 'imagepng'

		];

		$function = $functions[$format];
		$args = [ $image, $destination ];

		if ($format == 'jpeg')
		{
			#
			# add quality option for the 'jpeg' format
			#

			$args[] = $version->quality;
		}
		else if ($format == 'png' && !$callback)
		{
			#
			# If there is no background callback defined, the image is defined as transparent in
			# order to obtain a transparent thumbnail when the resulting image is centered.
			#

			imagealphablending($image, false);
			imagesavealpha($image, true);
		}

		$rc = call_user_func_array($function, $args);

		imagedestroy($image);

		if (!$rc)
		{
			throw new \Exception('Unable to save thumbnail');
		}

		return $destination;
	}

	protected function apply_filter($image, $filter)
	{
		if ($filter != 'grayscale')
		{
			return;
		}

		imagefilter($image, IMG_FILTER_GRAYSCALE);
	}

	protected function validate(\ICanBoogie\Errors $errors)
	{
		return true;
	}

	/**
	 * Operation interface to the {@link get()} method.
	 *
	 * The function uses the {@link get()} method to obtain the location of the image version.
	 * A HTTP redirection is made to the location of the image.
	 *
	 * A HTTPException exception with code 404 is thrown if the function fails to obtain the
	 * location of the image version.
	 *
	 * @throws HTTPError
	 */
	protected function process()
	{
		$this->rescue_uri();

		$path = $this->get();

		if (!$path)
		{
			throw new HTTPError(\ICanBoogie\format('Unable to create thumbnail for: %src', [ '%src' => $this->request['src'] ]), 404);
		}

		$request = $this->request;
		$response = $this->response;

		$server_location = \ICanBoogie\DOCUMENT_ROOT . $path;
		$stat = stat($server_location);
		$etag = md5($path);

		#
		# The expiration date is set to seven days.
		#

		session_cache_limiter('public');

		$response->cache_control->cacheable = 'public';
		$response->etag = $etag;
		$response->expires = '+1 week';
		$response->headers['X-Generated-By'] = 'Icybee/Thumbnailer';

		if ($request->cache_control->cacheable != 'no-cache')
		{
			$if_none_match = $request->headers['If-None-Match'];
			$if_modified_since = $request->headers['If-Modified-Since'];

			if ($if_modified_since && $if_modified_since->timestamp >= $stat['mtime']
			&& $if_none_match && trim($if_none_match) == $etag)
			{
				$response->status = 304;

				#
				# WARNING: do *not* send any data after that
				#

				return true;
			}
		}

		$pos = strrpos($path, '.');
		$type = substr($path, $pos + 1);

		$response->last_modified = $stat['mtime'];
		$response->content_type = "image/$type";
		$response->content_length = $stat['size'];

		return function() use ($server_location)
		{
			$fh = fopen($server_location, 'rb');

			fpassthru($fh);

			fclose($fh);
		};
	}

	static private function decode_background($background)
	{
		$parts = explode(',', $background);

		$parts[0] = Image::decode_color($parts[0]);

		if (count($parts) == 1)
		{
			return array($parts[0], null, 0);
		}

		$parts[1] = Image::decode_color($parts[1]);

		return $parts;
	}

	static public function fill_callback($image, $w, $h)
	{
		#
		# We create Image::draw_grid() arguments from the dimensions of the image
		# and the values passed using the 'background' parameter.
		#

		$args = (array) self::$background;

		array_unshift($args, $image, 0, 0, (int) $w - 1, (int) $h - 1);

		call_user_func_array('ICanBoogie\Image::draw_grid', $args);
	}

	/**
	 * Under some strange circumstances, IE6 uses URL with encoded entities. This function tries
	 * to rescue the bullied URIs.
	 *
	 * The decoded parameters are set in the operation's params property.
	 */
	private function rescue_uri()
	{
		$query = $this->request->query_string;

		if (strpos($query, '&amp;') === false)
		{
			return;
		}

		$query = html_entity_decode($query);

		$rc = parse_str($query, $this->request->params);
	}
}