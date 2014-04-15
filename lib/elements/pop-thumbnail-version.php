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

	public function offsetSet($offset, $value)
	{
		if (($offset == 'value' || $offset == self::DEFAULT_VALUE) && is_array($value))
		{
			$value = json_encode($value);
		}

		parent::offsetSet($offset, $value);
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
		$decoded_value = json_decode($value, true);

		$input = new Element
		(
			'input', array
			(
				'name' => $this['name'],
				'type' => 'hidden',
				'value' => $value
			)
		);

		$content = '';

		if ($decoded_value)
		{
			$version = new Version($decoded_value);

			$w = $version->width ?: '<em>auto</em>';
			$h = $version->height ?: '<em>auto</em>';
			$method = $version->method;
			$format = '.' . ($version->format ?: '<em>auto</em>');

			$content = "{$w}×{$h} {$method} $format";
		}

		$placeholder = 'Version non définie';

		return <<<EOT
$input <span class="spinner-content">$content</span> <em class="spinner-placeholder">$placeholder</em> $html
EOT;
	}
}

namespace Brickrouge\Widget;

class PopThumbnailVersion extends \ICanBoogie\Modules\Thumbnailer\PopThumbnailVersion
{

}