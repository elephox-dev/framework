<?php
declare(strict_types=1);

namespace Elephox\Files;

use ValueError;

class UnknownFilesystemNode extends AbstractFilesystemNode
{
	public function getParent(int $levels = 1): Contract\Directory
	{
		try {
			return new Directory(dirname($this->path, $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}

	public function exists(): bool
	{
		return file_exists($this->path) || is_dir($this->path);
	}

	public function asDirectory(): Contract\Directory
	{
		if (!is_dir($this->path)) {
			throw new DirectoryNotFoundException($this->path);
		}

		return new Directory($this->path);
	}

	public function asFile(): Contract\File
	{
		if (!is_file($this->path)) {
			throw new FileNotFoundException($this->path);
		}

		return new File($this->path);
	}
}
