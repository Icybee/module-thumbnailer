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

	protected $elements = array();

	public function __construct(array $attributes=array())
	{
		$versions = array(null => '<personnalisé>');

		parent::__construct
		(
			$attributes + array
			(
				self::CHILDREN => array
				(
					/*
					'v' => $this->elements['v'] = new Element
					(
						'select', array
						(
							Element::OPTIONS => $versions
						)
					),
					*/

					new Element
					(
						'div', array
						(
							Element::CHILDREN => array
							(
								'<span class="add-on">Size</span>',

								'width' => $this->elements['width'] = new Text
								(
									array
									(
										'class' => 'measure',
										'size' => 5
									)
								),

								'<span class="add-on">&times</span>',

								'height' => $this->elements['height'] = new Text
								(
									array
									(
										'class' => 'measure',
										'size' => 5
									)
								),

								'<span class="add-on">px</span>',
							),

							'class' => 'input-prepend input-append'
						)
					),

					'method' => $this->elements['method'] = new Element
					(
						'select', array
						(
							Form::LABEL => 'Méthode',

							Element::OPTIONS => array
							(
								Image::RESIZE_FILL => 'Remplir',
								Image::RESIZE_FIT => 'Ajuster',
								Image::RESIZE_SURFACE => 'Surface',
								Image::RESIZE_FIXED_HEIGHT => 'Hauteur fixe, largeur ajustée',
								Image::RESIZE_FIXED_HEIGHT_CROPPED => 'Hauteur fixe, largeur respectée',
								Image::RESIZE_FIXED_WIDTH => 'Largeur fixe, hauteur ajustée',
								Image::RESIZE_FIXED_WIDTH_CROPPED => 'Largeur fixe, hauteur respectée',
								Image::RESIZE_CONSTRAINED => 'Contraindre'
							)
						)
					),

					new Element
					(
						'div', array
						(
							Group::LABEL => 'Format',

							Element::CHILDREN => array
							(
								'format' => $this->elements['format'] = new Element
								(
									'select', array
									(
										self::OPTIONS => array
										(
											null => 'Auto.',
											'jpeg' => 'JPEG',
											'png' => 'PNG',
											'gif' => 'GIF'
										),

										'style' => 'width: auto;'
									)
								),

								'&nbsp;',

								'quality' => $this->elements['quality'] = new Text
								(
									array
									(
										Text::ADDON => 'Qualité',
										Text::ADDON_POSITION => 'before',
										self::DEFAULT_VALUE => 90,

										'class' => 'measure',
										'size' => 3
									)
								)
							),

							'class' => 'format-combo'
						)
					),

					'background' => $this->elements['background'] = new Text
					(
						array
						(
							Group::LABEL => 'Remplissage'
						)
					),

					'lightbox' => $this->elements['lightbox'] = new Element
					(
						Element::TYPE_CHECKBOX, array
						(
							Element::LABEL => "Afficher l'original en lightbox"
						)
					)
				),

				'class' => 'widget-adjust-thumbnail-options',
				Element::WIDGET_CONSTRUCTOR => 'AdjustThumbnailOptions'
			)
		);

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

		if ($options['background'] == 'transparent')
		{
			$options['background'] = '';
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