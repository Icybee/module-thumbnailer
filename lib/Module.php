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

use ICanBoogie\Errors;

/**
 * @property-read string $repository Path to the thumbnails repository.
 */
class Module extends \ICanBoogie\Module
{
	/**
	 * Getter for the $repository magic property.
	 */
	protected function get_repository()
	{
		return \ICanBoogie\REPOSITORY . 'thumbnailer' . DIRECTORY_SEPARATOR;
	}

	/**
	 * Creates the repository folder where generated thumbnails are saved.
	 *
	 * @inheritdoc
	 */
	public function install(Errors $errors)
	{
		$path = $this->repository;

		if (!file_exists($path))
		{
			$parent = dirname($path);

			if (is_writable($parent))
			{
				mkdir($path, 0705, true);
			}
			else
			{
				$errors->add($this->id, "Unable to create %directory directory, its parent is not writable", [

					'%directory' => \ICanBoogie\strip_root($path)

				]);
			}
		}

		return !count($errors);
	}

	/**
	 * Check if the repository folder has been created.
	 *
	 * @inheritdoc
	 */
	public function is_installed(Errors $errors)
	{
		$path = $this->repository;

		if (!file_exists($path))
		{
			$errors->add($this->id, "The %directory directory is missing.", [

				'%directory' => \ICanBoogie\strip_root($path)

			]);
		}

		return !count($errors);
	}
}
