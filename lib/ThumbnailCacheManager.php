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

use ICanBoogie\AppConfig;
use ICanBoogie\Prototyped;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

use Icybee\Modules\Cache\CacheManager;
use Icybee\Modules\Cache\Module as CacheModule;

/**
 * Manages cache for thumbnails.
 *
 * The cache is always active.
 *
 * @property-read ThumbnailCache $handler
 */
class ThumbnailCacheManager extends Prototyped implements CacheManager
{
	public $title = "Thumbnails";
	public $description = "Thumbnails created on the fly by the <q>Thumbnailer</q> module.";
	public $group = 'resources';
	public $state = null;

	/**
	 * Configuration for the module.
	 *
	 * - cleanup_interval: The interval between cleanups, in minutes.
	 *
	 * - repository_size: The size of the repository, in Mo.
	 */
	static public $config = [

		'cleanup_interval' => 15,
		'repository_size' => 8

	];

	protected function get_config_preview()
	{
		$app = $this->app;
		$registry = $app->registry;

		$rc = $app->translate("The cache size does not exceed :cache_sizeMb.", [ 'cache_size' => $registry['thumbnailer.cache_size'] ?: 8 ]);
		$rc .= ' ' . $app->translate("The cache is cleaned every :cleanup_interval minutes.", [ 'cleanup_interval' => $registry['thumbnailer.cleanup_interval'] ?: 15 ]);

		return $rc;
	}

	protected function get_editor()
	{
		$registry = $this->app->registry;

		return new Form([

			Form::RENDERER => Form\GroupRenderer::class,
			Element::CHILDREN => [

				'cache_size' => new Text([

					Group::LABEL => "Maximum cache size",
					Text::ADDON => 'Mb',

					'size' => 5,
					'class' => 'measure',
					'value' => $registry['thumbnailer.cache_size'] ?: 8

				]),

				'cleanup_interval' => new Text([

					Group::LABEL => "Interval between cleaning",
					Text::ADDON => 'minutes',

					'size' => 5,
					'class' => 'measure',
					'value' => $registry['thumbnailer.cleanup_interval'] ?: 15

				]),
			],

			'class' => 'stacked'

		]);
	}

	/**
	 * @var string
	 */
	private $path;

	/**
	 * Path to the cache's directory.
	 *
	 * @return string
	 */
	protected function get_path()
	{
		return $this->path;
	}

	/**
	 * Handler for the cache entries.
	 *
	 * @return ThumbnailCache
	 */
	protected function get_handler()
	{
		return new ThumbnailCache($this->path, self::$config['repository_size']);
	}

	/**
	 * Initialize the `path` property.
	 */
	public function __construct()
	{
		$this->path = $this->app->config[AppConfig::REPOSITORY] . 'thumbnailer' . DIRECTORY_SEPARATOR;
	}

	public function enable()
	{

	}

	public function disable()
	{

	}

	public function stat()
	{
		return CacheModule::get_files_stat($this->path);
	}

	public function clear()
	{
		$files = glob("$this->path/*");

		foreach ($files as $file)
		{
			unlink($file);
		}

		return count($files);
	}


	/**
	 * Periodically clears the cache.
	 */
	public function clean()
	{
		$marker = "$this->path/.cleanup";

		$time = file_exists($marker) ? filemtime($marker) : 0;
		$interval = self::$config['cleanup_interval'] * 60;
		$now = time();

		if ($time + $interval > $now)
		{
			return;
		}

		$this->handler->clean();

		touch($marker);
	}

	public function config($params)
	{
		$registry = $this->app->registry;

		if (!empty($params['cache_size']))
		{
			$registry['thumbnailer.cache_size'] = (int) $params['cache_size'];
		}

		if (!empty($params['cleanup_interval']))
		{
			$registry['thumbnailer.cleanup_interval'] = (int) $params['cleanup_interval'];
		}
	}

	/**
	 * @param $key
	 * @param callable $callback
	 * @param array|null $userdata
	 *
	 * @return string
	 */
	public function retrieve($key, callable $callback, array $userdata = null)
	{
		$this->clean();

		return $this->handler->get(...func_get_args());
	}
}
