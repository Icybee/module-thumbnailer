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

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class AdjustThumbnailVersion extends \Brickrouge\Group
{
	private $elements = [];

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(DIR . 'public/module.css');
		$document->js->add(DIR . 'public/module.js');
	}

	public function __construct(array $attributes=[])
	{
		parent::__construct(\ICanBoogie\array_merge_recursive([

			Element::IS => 'AdjustThumbnailVersion',

			Element::CHILDREN => [

				new Element('div', [

					Form::LABEL => 'Dimensions',
					Element::CHILDREN => [

						'width' => $this->elements['width'] = new Text([

							Text::ADDON => 'px',

							'class' => 'measure',
							'size' => 5

						]),

						' × ',

						'height' => $this->elements['height'] = new Text([

							Text::ADDON => 'px',

							'class' => 'measure',
							'size' => 5

						])
					]
				]),

				'method' => $this->elements['method'] = new Element('select', [

					Form::LABEL => 'Méthode',
					Element::OPTIONS => [

						Image::RESIZE_FILL => 'Remplir',
						Image::RESIZE_FIT => 'Ajuster',
						Image::RESIZE_SURFACE => 'Surface',
						Image::RESIZE_FIXED_HEIGHT => 'Hauteur fixe, largeur ajustée',
						Image::RESIZE_FIXED_HEIGHT_CROPPED => 'Hauteur fixe, largeur respectée',
						Image::RESIZE_FIXED_WIDTH => 'Largeur fixe, hauteur ajustée',
						Image::RESIZE_FIXED_WIDTH_CROPPED => 'Largeur fixe, hauteur respectée',
						Image::RESIZE_CONSTRAINED => 'Contraindre'

					]
				]),

				'no-upscale' => $this->elements['no-upscale'] = new Element(Element::TYPE_CHECKBOX, [

					Element::LABEL => 'Redimensionner mais ne pas agrandir'

				]),

				new Element('div', [

					Form::LABEL => 'Format de la miniature',

					self::CHILDREN => [

						'format' => $this->elements['format'] = new Element('select', [

							self::OPTIONS => [

								null => 'auto',
								'jpeg' => '.jpeg',
								'png' => '.png',
								'gif' => '.gif'

							],

							'style' => 'vertical-align: middle; width: auto'

						]),

						'&nbsp;',

						'quality' => $this->elements['quality'] = new Text([

							Text::ADDON => 'Qualité',
							Text::ADDON_POSITION => 'before',
							self::DEFAULT_VALUE => Version::$defaults['quality'],

							'class' => 'measure',
							'size' => 3

						])
					]
				]),

				'background' => $this->elements['background'] = new Text([

					Form::LABEL => 'Remplissage'

				]),

				'filter' => $this->elements['filter'] = new Text([

					Form::LABEL => 'Filtre'


				])
			],

			'class' => 'adjust widget-adjust-thumbnail-version'

		], $attributes));
	}

	public function offsetSet($offset, $value)
	{
		switch ($offset)
		{
			case self::DEFAULT_VALUE:
			{
				if (!$value) break;

				$version = new Version($value);
				$options = $version->to_array();

				foreach ($options as $identifier => $v)
				{
					if (empty($this->elements[$identifier]))
					{
						continue;
					}

					$element[$offset] = $v;
				}
			}
			break;

			case 'name':
			{
				foreach ($this->elements as $identifier => $element)
				{
					$element[$offset] = $value . '[' . $identifier . ']';
				}
			}
			break;

			case 'value':
			{
				if (!$value) break;

				$version = new Version($value);
				$options = $version->to_array();

				foreach ($options as $identifier => $v)
				{
					if (empty($this->elements[$identifier]))
					{
						continue;
					}

					// FIXME-20110518: use handle_value() ?

					$this->elements[$identifier][($identifier == 'interlace' || $identifier == 'no-upscale') ? 'checked' : 'value'] = $v;
				}
			}
			break;
		}

		parent::offsetSet($offset, $value);
	}
}

namespace Brickrouge\Widget;

class AdjustThumbnailVersion extends \ICanBoogie\Modules\Thumbnailer\AdjustThumbnailVersion
{

}