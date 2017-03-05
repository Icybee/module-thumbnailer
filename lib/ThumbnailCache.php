<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Thumbnailer;

use function ICanBoogie\format;

class ThumbnailCache
{
	const DEFAULT_SIZE = 32;
	const DEFAULT_DELETE_RATIO = .25;

	/**
	 * Absolute path to the cache directory.
	 *
	 * @var string
	 */
	private $directory;

	/**
	 * Cache size in bytes.
	 *
	 * @var int
	 */
	private $repository_size;

	/**
	 * @var float
	 */
	private $repository_delete_ratio;

	/**
	 * @param string $directory
	 * @param int $size Cache size in mega bytes.
	 * @param float $delete_ratio
	 */
	public function __construct($directory, $size = self::DEFAULT_SIZE, $delete_ratio = self::DEFAULT_DELETE_RATIO)
	{
		$this->directory = $directory;
		$this->repository_size = $size * 1024 * 1024;
		$this->repository_delete_ratio = $delete_ratio;
	}

	private function assert_root()
	{
		if (!is_dir($this->directory))
		{
			throw new \Exception(format('The repository %repository does not exists.', [
				'%repository' => $this->directory
			]), 404);
		}
	}

	/**
	 * Check if a file exists in the repository.
	 *
	 * If the file does not exists, it's created using the provided constructor.
	 *
	 * @param string $filename The name of the file in the repository.
	 *
	 * @param callable $constructor The constructor for the file.
	 * The constructor is invoked with the absolute filename and this instance.
	 *
	 * @return string The absolute pathname.
	 *
	 * @throws \Exception when the repository does not exists.
	 */
	public function get($filename, callable $constructor)
	{
		$this->assert_root();

		$pathname = $this->directory . DIRECTORY_SEPARATOR . $filename;

		if (!is_file($pathname))
		{
			$constructor($pathname, $this);
		}

		if (!is_file($pathname))
		{
			throw new \RuntimeException("Constructor failed to create: $pathname");
		}

		return $pathname;
	}

	/**
	 * @param string $filename
	 *
	 * @return bool
	 */
	public function exists($filename)
	{
		return file_exists($this->directory . DIRECTORY_SEPARATOR . $filename);
	}

	/**
	 * @param string $filename
	 *
	 * @return int
	 */
	public function delete($filename)
	{
		return $this->unlink([ $filename => true ]);
	}

	/**
	 * Read to repository and return an array of files.
	 *
	 * Each entry in the array is made up using the _ctime_ and _size_ of the file. The
	 * key of the entry is the file name.
	 *
	 * @return mixed
	 *
	 * @throws \Exception when the directory cannot be opened.
	 */
	private function read()
	{
		$root = $this->directory;

		if (!is_dir($root))
		{
			return false;
		}

		try
		{
			$dir = new \DirectoryIterator($root);
		}
		catch (\UnexpectedValueException $e)
		{
			throw new \Exception(format('Unable to open directory %root', [ '%root' => $root ]));
		}

		#
		# create file list, with the filename as key and ctime and size as value.
		# we set the ctime first to be able to sort the file by ctime when necessary.
		#

		$files = [];

		foreach ($dir as $file)
		{
			if (!$file->isDot())
			{
				$files[$file->getFilename()] = [ $file->getCTime(), $file->getSize() ];
			}
		}

		return $files;
	}

	/**
	 * Unlink files.
	 *
	 * @param array $files
	 *
	 * @return int The number of files unlinked.
	 */
	private function unlink($files)
	{
		if (!$files)
		{
			return 0;
		}

		#
		# obtain exclusive lock to delete files
		#

		$lh = fopen("$this->directory/.lock", 'w+');

		if (!$lh)
		{
			trigger_error(format('Unable to lock %dir', [ '%dir' => $this->directory ]), E_USER_ERROR);

			return 0;
		}

		#
		# We will try $n time to obtain the exclusive lock
		#

		$n = 10;

		while (!flock($lh, LOCK_EX | LOCK_NB))
		{
			#
			# If the lock is not obtained we sleep for 0 to 100 milliseconds.
			# We sleep to avoid CPU load, and we sleep for a random time
			# to avoid collision.
			#

			usleep(round(rand(0, 100) * 1000));

			if (!--$n)
			{
				#
				# We were unable to obtain the lock in time.
				# We exit silently.
				#

				return 0;
			}
		}

		#
		# The lock was obtained, we can now delete the files
		#

		$n = 0;

		foreach (array_keys($files) as $file)
		{
			$pathname = $this->directory . DIRECTORY_SEPARATOR . $file;

			#
			# Because of concurrent access, the file might have already been deleted.
			# We have to check if the file still exists before calling unlink()
			#

			if (!is_file($pathname))
			{
				continue;
			}

			unlink($pathname);

			$n++;
		}

		#
		# and release the lock.
		#

		fclose($lh);

		return $n;
	}

	/**
	 * Clear all the files in the repository.
	 *
	 * @return int The number of files unlinked.
	 */
	public function clear()
	{
		$files = $this->read();

		if (!$files)
		{
			return 0;
		}

		return $this->unlink($files);
	}

	/**
	 * Clean the repository according to the size and time rules.
	 */
	public function clean()
	{
		$files = $this->read();

		if (!$files)
		{
			return;
		}

		$totalsize = 0;

		foreach ($files as $stat)
		{
			$totalsize += $stat[1];
		}

		$repository_size = $this->repository_size;

		if ($totalsize < $repository_size)
		{
			#
			# There is enough space in the repository. We don't need to delete any file.
			#

			return;
		}

		#
		# The repository is completely full, we need to make some space.
		# We create an array with the files to delete. Files are added until
		# the delete ratio is reached.
		#

		asort($files);

		$deletesize = $repository_size * $this->repository_delete_ratio;

		$i = 0;

		foreach ($files as $file => $stat)
		{
			$i++;

			$deletesize -= $stat[1];

			if ($deletesize < 0)
			{
				break;
			}
		}

		$files = array_slice($files, 0, $i);

		$this->unlink($files);
	}
}
