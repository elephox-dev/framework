<?php
declare(strict_types=1);

namespace Elephox\Files;

use ValueError;

class UnknownFilesystemNode extends AbstractFilesystemNode
{
	public function getParent(int $levels = 1): Contract\Directory
	{
		try {
			return new Directory(dirname($this->getPath(), $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}

	public function exists(): bool
	{
		return file_exists($this->getPath()) || is_dir($this->getPath());
	}

	public function asDirectory(): Contract\Directory
	{
		if (!is_dir($this->getPath())) {
			throw new DirectoryNotFoundException($this->getPath());
		}

		return new Directory($this->getPath());
	}

	public function asFile(): Contract\File
	{
		if (!is_file($this->getPath())) {
			throw new FileNotFoundException($this->getPath());
		}

		return new File($this->getPath());
	}
}
