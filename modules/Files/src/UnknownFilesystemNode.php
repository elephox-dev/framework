<?php
declare(strict_types=1);

namespace Elephox\Files;

class UnknownFilesystemNode extends AbstractFilesystemNode
{
	public function exists(): bool
	{
		return file_exists($this->path()) || is_dir($this->path());
	}

	public function isDirectory(): bool {
		return is_dir($this->path());
	}

	public function isFile(): bool {
		return is_file($this->path());
	}

	public function isLink(): bool {
		return is_link($this->path());
	}

	public function asDirectory(): Contract\Directory
	{
		if (!$this->isDirectory()) {
			throw new DirectoryNotFoundException($this->path());
		}

		return new Directory($this->path());
	}

	public function asFile(): Contract\File
	{
		if (!$this->isFile()) {
			throw new FileNotFoundException($this->path());
		}

		return new File($this->path());
	}

	public function asLink(): Contract\Link
	{
		if (!$this->isLink()) {
			throw new LinkNotFoundException($this->path());
		}

		return new Link($this->path());
	}

	public function delete(): void {
		if ($this->isDirectory()) {
			$this->asDirectory()->delete();
		} else if ($this->isFile()) {
			$this->asFile()->delete();
		} else if ($this->isLink()) {
			$this->asLink()->delete();
		} else {
			throw new FilesystemNodeNotImplementedException($this, "Unable to delete unknown filesystem node.");
		}
	}
}
