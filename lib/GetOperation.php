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
use ICanBoogie\HTTP\NotFound;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Image;
use ICanBoogie\Operation;
use Icybee\Binding\PrototypeBindings;

/**
 * @property Module $module
 * @property string $repository Path to the thumbnails repository.
 * @property FileCache $cache Thumbnails cache manager.
 */
class GetOperation extends Operation
{
	use PrototypeBindings;

	const VERSION = '2.1';

	static public $background;

	/**
	 * Tries to create a {@link Version} instance from the request.
	 *
	 * @param Request $request
	 *
	 * @return Version
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
	 * @return string
	 *
	 * @throws NotFound
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
				throw new NotFound($this->format('Thumbnail source not found: %src', [ '%src' => $src ]));
			}

			$src = $path . $default;
			$location = \ICanBoogie\DOCUMENT_ROOT . DIRECTORY_SEPARATOR . $src;

			if (!is_file($location))
			{
				throw new NotFound($this->format('Thumbnail source (default) not found: %src', [ '%src' => $src ]));
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

		return (new ThumbnailCacheManager)
			->retrieve($key, function(FileCache $cache, $destination) use ($location, $version) {

				$create = new CreateThumbnail;

				return $create($location, $destination, $version);

			});
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
	 * @throws NotFound when the method fails to obtain the location of the image version.
	 */
	protected function process()
	{
		$this->rescue_uri();

		$path = $this->get();

		if (!$path)
		{
			throw new NotFound($this->format('Unable to create thumbnail for: %src', [ '%src' => $this->request['src'] ]));
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

		parse_str($query, $this->request->params);
	}
}
