<?php
declare(strict_types=1);

namespace Elephox\Files;

class UnknownFilesystemNode extends AbstractFilesystemNode
{
	public function exists(): bool
	{
		return file_exists($this->path()) || is_dir($this->path());
	}

	public function asDirectory(): Contract\Directory
	{
		if (!is_dir($this->path())) {
			throw new DirectoryNotFoundException($this->path());
		}

		return new Directory($this->path());
	}

	public function asFile(): Contract\File
	{
		if (!is_file($this->path())) {
			throw new FileNotFoundException($this->path());
		}

		return new File($this->path());
	}

	public function asLink(): Contract\Link
	{
		if (!is_link($this->path())) {
			throw new LinkNotFoundException($this->path());
		}

		return new Link($this->path());
	}
}
