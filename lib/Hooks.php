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

use function ICanBoogie\app;
use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

use Icybee\Modules\Cache\CacheCollection as CacheCollection;
use Icybee\Modules\Images\Image as ImageActiveRecord;

class Hooks
{
	/**
	 * Callback for the {@link Icybee\Block\ConfigBlock::alter_children} event, adding
	 * {@link PopThumbnailVersion} elements to the config block if image versions are defined for
	 * the module.
	 *
	 * @param \Icybee\Block\FormBlock\AlterChildrenEvent $event
	 * @param \Icybee\Block\ConfigBlock $block
	 *
	 * @internal param Event $ev
	 */
	static public function on_configblock_alter_children(\Icybee\Block\FormBlock\AlterChildrenEvent $event, \Icybee\Block\ConfigBlock $block)
	{
		$app = app();

		$module_id = (string) $event->module->id;

		$c = $app->configs->synthesize('thumbnailer', 'merge');

		if (!$c)
		{
			return;
		}

		$configs = [];

		foreach ($c as $version_name => $config)
		{
			if (empty($config['module']) || $config['module'] != $module_id)
			{
				continue;
			}

			$configs[$version_name] = $config;
		}

		if (!$configs)
		{
			return;
		}

		$app->document->css->add(DIR . 'public/admin.css');

		$children = [];

		foreach ($configs as $version_name => $config)
		{
			list($defaults) = $config;

			$config += [

				'description' => null

			];

			$children['global[thumbnailer.versions][' . $version_name . ']'] = new Widget\PopThumbnailVersion([

				Form::LABEL => new Element('span', [ Element::INNER_HTML => $config['title'] . '<br /><small>' . $version_name . '</small>' ]),
				Element::DEFAULT_VALUE => $defaults,
				Element::GROUP => 'thumbnailer',
				Element::DESCRIPTION => $config['description'],

				'value' => $app->registry["thumbnailer.verison.$version_name"]

			]);
		}

		$event->attributes[Element::GROUPS]['thumbnailer'] = [

			'title' => 'Miniatures',
			'description' => "Ce groupe permet de configurer les différentes
			versions de miniatures qu'il est possible d'utiliser pour
			les entrées de ce module."

		];

		$event->children = array_merge($event->children, $children);
	}

	/**
	 * Clears the versions cache.
	 *
	 * @param \Icybee\Operation\Module\ConfigOperation\BeforePropertiesEvent $event
	 */
	static public function before_configoperation_properties(\Icybee\Operation\Module\ConfigOperation\BeforePropertiesEvent $event)
	{
		if (empty($event->request->params['global']['thumbnailer.versions']))
		{
			return;
		}

		unset(app()->vars['cached_thumbnailer_versions']);
	}

	/**
	 * Adds our cache manager to the cache collection.
	 *
	 * @param CacheCollection\CollectEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
	{
		$event->collection['thumbnails'] = new ThumbnailCacheManager;
	}
}
