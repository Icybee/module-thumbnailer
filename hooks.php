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

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Operation;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Widget;

use Icybee\Modules\Cache\Collection as CacheCollection;
use Icybee\Modules\Images\Image as ImageActiveRecord;

class Hooks
{
	/**
	 * Callback for the `thumbnail()` method added to the active records of the "images" module.
	 *
	 * @param Icybee\Modules\Images\Image $ar An active record of the "images" module.
	 * @param string $version The version used to create the thumbnail, or a number of options
	 * defined as CSS properties. e.g. 'w:300;h=200'.
	 * @return string The URL of the thumbnail.
	 */
	static public function method_thumbnail(ImageActiveRecord $ar, $version, $additionnal_options=null)
	{
		return new Thumbnail($ar, $version, $additionnal_options);
	}

	/**
	 * Callback for the `thumbnail` getter added to the active records of the "images" module.
	 *
	 * The thumbnail is created using options of the 'primary' version.
	 *
	 * @param Icybee\Modules\Images\Image $ar An active record of the "images" module.
	 * @return string The URL of the thumbnail.
	 */
	static public function method_get_thumbnail(ImageActiveRecord $ar)
	{
		return self::method_thumbnail($ar, 'primary');
	}

	/**
	 * Callback for the {@link Icybee\ConfigBlock::alter_children} event, adding
	 * {@link PopThumbnailVersion} elements to the config block if image versions are defined for
	 * the module.
	 *
	 * @param Event $ev
	 */
	static public function on_configblock_alter_children(Event $event, \Icybee\ConfigBlock $block)
	{
		global $core;

		$module_id = (string) $event->module->id;

		$c = $core->configs->synthesize('thumbnailer', 'merge');

		$configs = array();

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

		$core->document->css->add('assets/admin.css');

		$children = array();

		foreach ($configs as $version_name => $config)
		{
			list($defaults) = $config;

			$config += array
			(
				'description' => null
			);

			$children['global[thumbnailer.versions][' . $version_name . ']'] = new Widget\PopThumbnailVersion
			(
				array
				(
					Form::LABEL => new Element('span', array(Element::INNER_HTML => $config['title'] . ' <small>(' . $version_name . ')</small>')),
					Element::DEFAULT_VALUE => $defaults,
					Element::GROUP => 'thumbnailer',
					Element::DESCRIPTION => $config['description'],

					'value' => $core->registry["thumbnailer.verison.$version_name"]
				)
			);
		}

		$event->attributes[Element::GROUPS]['thumbnailer'] = array
		(
			'title' => 'Miniatures',
			'description' => "Ce groupe permet de configurer les différentes
			versions de miniatures qu'il est possible d'utiliser pour
			les entrées de ce module."
		);

		$event->children = array_merge($event->children, $children);
	}

	/**
	 * Callback for the `properties:before` event, pre-parsing thumbnailer versions if they are
	 * defined.
	 *
	 * @param \Icybee\ConfigOperation\BeforePropertiesEvent $ev
	 */
	static public function before_configoperation_properties(\Icybee\ConfigOperation\BeforePropertiesEvent $event)
	{
		global $core;

		$params = &$event->request->params;

		if (empty($params['global']['thumbnailer.versions']))
		{
			return;
		}

		$config = $core->configs->synthesize('thumbnailer', 'merge');

		foreach ($params['global']['thumbnailer.versions'] as $name => &$options)
		{
			if (is_string($options))
			{
				$options = json_decode($options, true);
			}

			$options = (array) $options;

			$options += (isset($config[$name][0]) ? $config[$name][0] : array()) + array
			(
				'no-upscale' => false,
				'interlace' => false
			);

			$options['no-upscale'] = filter_var($options['no-upscale'], FILTER_VALIDATE_BOOLEAN);
			$options['interlace'] = filter_var($options['interlace'], FILTER_VALIDATE_BOOLEAN);

			$options = (empty($options['w']) && empty($options['h'])) ? null : json_encode($options);
		}

		unset($core->vars['cached_thumbnailer_versions']);
	}

	/**
	 * Adds our cache manager to the cache collection.
	 *
	 * @param CacheCollection\CollectEvent $event
	 * @param CacheCollection $collection
	 */
	static public function on_cache_collection_collect(CacheCollection\CollectEvent $event, CacheCollection $collection)
	{
		global $core;

		$event->collection['thumbnails'] = new CacheManager();
	}
}