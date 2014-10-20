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

use Brickrouge\Element;

class PopThumbnailVersion extends \Brickrouge\Widget
{
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add(DIR . 'public/module.js');
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'a', $attributes + array
			(
				'class' => 'spinner'
			)
		);
	}

	protected function render_class(array $class_names)
	{
		if (!$this['value'])
		{
			$class_names['placeholder'] = true;
		}

		return parent::render_class($class_names);
	}

	protected function render_inner_html()
	{
		$html = parent::render_inner_html();

		$value = $this['value'] ?: $this[self::DEFAULT_VALUE];

		if ($value)
		{
			$value = (string) new Version($value);
		}

		$input = new Element
		(
			'input', array
			(
				'name' => $this['name'],
				'type' => 'hidden',
				'value' => $value
			)
		);

		$placeholder = 'Version non définie';

		return <<<EOT
$input <span class="spinner-content">$value</span> <em class="spinner-placeholder">$placeholder</em> $html
EOT;
	}
}

namespace Brickrouge\Widget;

class PopThumbnailVersion extends \ICanBoogie\Modules\Thumbnailer\PopThumbnailVersion
{

}