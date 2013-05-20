<?php

/*
 * This file is part of the Icybee package.
*
* (c) Olivier Laviale <olivier.laviale@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Brickrouge\Widget;

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

		$document->css->add('adjust-thumbnail-options.css');
		$document->js->add('adjust-thumbnail-options.js');
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
								'w' => $this->elements['w'] = new Text
								(
									array
									(
										Text::ADDON => 'px',

										'class' => 'measure',
										'size' => 5
									)
								),

								'&nbsp;&times;&nbsp;',

								'h' => $this->elements['h'] = new Text
								(
									array
									(
										Text::ADDON => 'px',

										'class' => 'measure',
										'size' => 5
									)
								)
							)
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
							Form::LABEL => 'Format',

							Element::CHILDREN => array
							(
								'format' => $this->elements['format'] = new Element
								(
									'select', array
									(
										self::OPTIONS => array
										(
											'jpeg' => 'JPEG',
											'png' => 'PNG',
											'gif' => 'GIF'
										),

										self::DEFAULT_VALUE => 'jpeg',

										'style' => 'width: auto; vertical-align: middle'
									)
								),

								'&nbsp;',

								'quality' => $this->elements['quality'] = new Text
								(
									array
									(
										Text::ADDON => 'Qualité',
										Text::ADDON_POSITION => 'before',
										self::DEFAULT_VALUE => 80,

										'class' => 'measure',
										'size' => 3
									)
								)
							)
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
				'data-widget-constructor' => 'AdjustThumbnailOptions'
			)
		);
	}

	public function offsetSet($offset, $value)
	{
		if ($offset === self::DEFAULT_VALUE)
		{
			foreach ($this->elements as $identifier => $element)
			{
				if (!array_key_exists($identifier, $value))
				{
					continue;
				}

				$element[$offset] = $value[$identifier];
			}
		}
		else if ($offset === 'name')
		{
			foreach ($this->elements as $identifier => $element)
			{
				$element[$offset] = $value . '[' . $identifier . ']';
			}
		}

		parent::offsetSet($offset, $value);
	}
}