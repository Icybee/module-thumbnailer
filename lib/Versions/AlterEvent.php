<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer\Versions;

use ICanBoogie\Modules\Thumbnailer\Versions;

/**
 * Event class for the `ICanBoogie\Modules\Thumbnailer\Versions::alert` event.
 */
class AlterEvent extends \ICanBoogie\Event
{
	/**
	 * The event is constructed with the type `alter`.
	 *
	 * @param Versions $target
	 */
	public function __construct(Versions $target)
	{
		parent::__construct($target, 'alter');
	}
}