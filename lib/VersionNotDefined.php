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

use ICanBoogie\Accessor\AccessorTrait;

/**
 * Exception thrown when a thumbnail version is not defined.
 *
 * @property-read string $version The thumbnail version identifier.
 */
class VersionNotDefined extends \InvalidArgumentException
{
	use AccessorTrait;

	private $version;

	protected function get_version()
	{
		return $this->version;
	}

	public function __construct($version, $code=500, \Exception $previous=null)
	{
		$this->version = $version;

		parent::__construct("Version not defined: $version.", $code, $previous);
	}
}
