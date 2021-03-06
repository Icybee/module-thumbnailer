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

use Brickrouge\InputGroup;
use ICanBoogie\Image;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

class AdjustThumbnailOptions extends Group
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(DIR . 'public/module.css');
		$document->js->add(DIR . 'public/module.js');
	}

	protected $elements = [];

	public function __construct(array $attributes=[])
	{
		parent::__construct($attributes + [

			Element::IS => 'AdjustThumbnailOptions',
			Element::CHILDREN => [

				new InputGroup([

					Element::CHILDREN => [

						"Size",

						'width' => $this->elements['width'] = new Text([

							'class' => 'form-control measure',
							'title' => "Thumbnail width in pixel",
							'size' => 5

						]),

						"&times",

						'height' => $this->elements['height'] = new Text([

							'class' => 'form-control measure',
							'title' => "Thumbnail height in pixel",
							'size' => 5

						]),

						"px",
					]

				]),

				new InputGroup([

					Element::CHILDREN => [

						"Method",

						'method' => $this->elements['method'] = new Element('select', [

							Element::OPTIONS => [

								Image::RESIZE_FILL => 'Remplir',
								Image::RESIZE_FIT => 'Ajuster',
								Image::RESIZE_SURFACE => 'Surface',
								Image::RESIZE_FIXED_HEIGHT => 'Hauteur fixe, largeur ajustée',
								Image::RESIZE_FIXED_HEIGHT_CROPPED => 'Hauteur fixe, largeur respectée',
								Image::RESIZE_FIXED_WIDTH => 'Largeur fixe, hauteur ajustée',
								Image::RESIZE_FIXED_WIDTH_CROPPED => 'Largeur fixe, hauteur respectée',
								Image::RESIZE_CONSTRAINED => 'Contraindre'

							],

							'class' => 'form-control',
							'title' => "Resizing method"

						])
					]
				]),

				new InputGroup([

					Element::CHILDREN => [

						"Format",

						'format' => $this->elements['format'] = new Element('select', [

							Element::OPTIONS => [

								null => 'auto',
								'jpeg' => '.jpeg',
								'png' => '.png',
								'gif' => '.gif'

							],

							'class' => 'form-control'

						]),

						"Quality",

						'quality' => $this->elements['quality'] = new Text([

							Element::DEFAULT_VALUE => Version::$defaults['quality'],

							'class' => 'form-control measure',
							'size' => 3

						])
					],

				]),

				'background' => $this->elements['background'] = new Text([

					Group::LABEL => 'Background'


				]),

				'lightbox' => $this->elements['lightbox'] = new Element(Element::TYPE_CHECKBOX, [

					Element::LABEL => "Display the original in a lightbox"

				])
			],

			'class' => 'widget-adjust-thumbnail-options'

		]);

		$this->tag_name = 'div';
	}

	public function offsetSet($attribute, $value)
	{
		if ($attribute === 'value' || $attribute === self::DEFAULT_VALUE)
		{
			$this->dispatch_value($value, $attribute);
		}
		else if ($attribute === 'name')
		{
			foreach ($this->elements as $identifier => $element)
			{
				$element[$attribute] = $value . '[' . $identifier . ']';
			}
		}

		parent::offsetSet($attribute, $value);
	}

	private function dispatch_value($value, $attribute)
	{
		$version = new Version($value);
		$options = $version->to_array();

		if (!empty($value['lightbox']))
		{
			$options['lightbox'] = true;
		}

		foreach ($options as $name => $v)
		{
			if (empty($this->elements[$name]))
			{
				continue;
			}

			$element = $this->elements[$name];

			if ($element['type'] == 'checkbox' && $attribute == 'value')
			{
				$element['checked'] = $v;
			}
			else
			{
				$element[$attribute] = $v;
			}
		}
	}
}
