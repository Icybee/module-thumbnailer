<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer\Routing;

use ICanBoogie\Binding\Routing\ForwardUndefinedPropertiesToApplication;
use ICanBoogie\HTTP\FileResponse;
use ICanBoogie\HTTP\NotFound;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Image;
use ICanBoogie\Modules\Thumbnailer\Binding\ApplicationBindings;
use ICanBoogie\Modules\Thumbnailer\CreateThumbnail;
use ICanBoogie\Modules\Thumbnailer\ThumbnailCacheManager;
use ICanBoogie\Modules\Thumbnailer\Version;
use ICanBoogie\Binding\PrototypedBindings;
use ICanBoogie\Routing\Controller;

class ThumbnailController extends Controller
{
	use PrototypedBindings, ApplicationBindings, ForwardUndefinedPropertiesToApplication;

	/**
	 * @inheritdoc
	 */
	protected function action(Request $request)
	{
		$version = $this->with_device_pixel_ratio(
			$this->resolve_version($request),
			$request
		);

		$source = $this->resolve_source($version);
		$pathname = $this->resolve_thumbnail($source, $version);

		if (!$pathname)
		{
			return null;
		}

		return new FileResponse($pathname, $this->request, [

			FileResponse::OPTION_ETAG => basename($pathname),
			FileResponse::OPTION_EXPIRES => '+3 month'

		]);
	}

	/**
	 * Creates a unique key for a thumbnail, using the source information and version options.
	 *
	 * @param string $source
	 * @param Version $version
	 *
	 * @return string
	 */
	protected function make_key($source, Version $version)
	{
		return rtrim(strtr(base64_encode(hash_file('sha384', $source, true) . hash('sha1', $version, true)), [

			'+' => '-',
			'/' => '_'

		]), '=');
	}

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
			$version = $this->thumbnailer_versions[$version_name];
		}
		else
		{
			$version = Version::from_uri($request->uri);

			if (!$version)
			{
				$version = new Version($request->params);
			}
		}

		Image::assert_sizes($version->method, $version->width, $version->height);

		return $version;
	}

	private function with_device_pixel_ratio(Version $version, Request $request)
	{
		$dpr = (float) ($request['device-pixel-ratio'] ?: $request['dpr'] ?: 1);

		if ($dpr === 1)
		{
			return $version;
		}

		$version = clone $version;
		$version->width = (int) round($version->width * $dpr);
		$version->height = (int) round($version->height * $dpr);

		return $version;
	}

	/**
	 * Resolves source file from version.
	 *
	 * @param Version $version
	 *
	 * @return string
	 * @throws NotFound
	 */
	protected function resolve_source(Version $version)
	{
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
		$source = realpath(\ICanBoogie\DOCUMENT_ROOT . $src);

		if ($source)
		{
			return $source;
		}

		#
		# use the provided default file is defined
		#

		$default = $version->default;

		if (!$default)
		{
			throw new NotFound(\ICanBoogie\format('Thumbnail source not found: %src', [ '%src' => $src ]));
		}

		$src = $path . $default;
		$source = realpath(\ICanBoogie\DOCUMENT_ROOT . $src);

		if (!$source)
		{
			throw new NotFound(\ICanBoogie\format('Thumbnail source (default) not found: %src', [ '%src' => $src ]));
		}

		return $source;
	}

	/**
	 * Returns the location of the thumbnail on the server, relative to the document root.
	 *
	 * The thumbnail is created using the parameters supplied, if it is not already available in
	 * the cache.
	 *
	 * @param string $location
	 * @param Version $version
	 *
	 * @return string Absolute pathname to the thumbnail
	 */
	protected function resolve_thumbnail($location, Version $version)
	{
		$version = clone $version;

		if (!$version->format)
		{
			$info = getimagesize($location);
			$version->format = substr($info['mime'], 6);
		}

		if ($version->format == 'jpeg' && !$version->background)
		{
			$version->background = 'white';
		}

		$key = $this->make_key($location, $version);

		return (new ThumbnailCacheManager)
			->retrieve($key, function ($destination) use ($location, $version) {

				$create = new CreateThumbnail;
				$create($location, $destination, $version);

			});
	}
}
