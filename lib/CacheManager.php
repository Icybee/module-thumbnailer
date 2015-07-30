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
use ICanBoogie\I18n;
use ICanBoogie\Object;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Cache\CacheManagerInterface;
use Icybee\Modules\Cache\Module as CacheModule;

/**
 * Manages cache for thumbnails.
 *
 * The cache is always active.
 */
class CacheManager extends Object implements CacheManagerInterface
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
		$registry = $this->app->registry;

		$rc = I18n\t("The cache size does not exceed :cache_sizeMb.", [ 'cache_size' => $registry['thumbnailer.cache_size'] ?: 8 ]);
		$rc .= ' ' . I18n\t("The cache is cleaned every :cleanup_interval minutes.", [ 'cleanup_interval' => $registry['thumbnailer.cleanup_interval'] ?: 15 ]);

		return $rc;
	}

	protected function get_editor()
	{
		$registry = $this->app->registry;

		return new Form([

			Form::RENDERER => 'Simple',
			Element::CHILDREN => [

				'cache_size' => new Text([

					Form::LABEL => "Maximum cache size",
					Text::ADDON => 'Mb',

					'size' => 5,
					'class' => 'measure',
					'value' => $registry['thumbnailer.cache_size'] ?: 8

				]),

				'cleanup_interval' => new Text([

					Form::LABEL => "Interval between cleaning",
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
	 * Path to the cache's directory.
	 *
	 * @return string
	 */
	protected function get_path()
	{
		return \ICanBoogie\REPOSITORY . 'thumbnailer' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Handler for the cache entries.
	 *
	 * @return FileCache
	 */
	protected function get_handler()
	{
		return new FileCache([

			FileCache::T_REPOSITORY => $this->path,
			FileCache::T_REPOSITORY_SIZE => self::$config['repository_size'] * 1024

		]);
	}

	public function enable()
	{

	}

	public function disable()
	{

	}

	public function stat()
	{
		return CacheModule::get_files_stat(\ICanBoogie\REPOSITORY . 'thumbnailer');
	}

	public function clear()
	{
		$files = glob(\ICanBoogie\REPOSITORY . 'thumbnailer/*');

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
		$marker = \ICanBoogie\REPOSITORY . 'thumbnailer/.cleanup';

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

	public function retrieve($key, array $callback, array $userdata)
	{
		$this->clean();

		return call_user_func_array([ $this->handler, 'get' ], func_get_args());
	}
}
