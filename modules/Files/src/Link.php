<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\Directory as DirectoryContract;
use Elephox\Files\Contract\FilesystemNode;

class Link extends AbstractFilesystemNode implements Contract\Link
{
	public function exists(): bool
	{
		return is_link($this->path());
	}

	public function target(): FilesystemNode
	{
		$target = readlink($this->path());

		if ($target === false) {
			throw new LinkNotFoundException($this, $this->path());
		}

		if (is_dir($target)) {
			return new Directory($target);
		}

		if (is_file($target)) {
			return new File($target);
		}

		if (is_link($target)) {
			return new self($target);
		}

		return new UnknownFilesystemNode($target);
	}

	public function delete(): void
	{
		if (!$this->exists()) {
			throw new LinkNotFoundException($this, $this->path());
		}

		// From the docs: On Windows, to delete a symlink to a directory, rmdir() has to be used instead.
		if (DIRECTORY_SEPARATOR === '\\' && $this->target() instanceof DirectoryContract) {
			if (!rmdir($this->path())) {
				throw new LinkDeleteException($this->path());
			}
		} elseif (!unlink($this->path())) {
			throw new LinkDeleteException($this->path());
		}
	}
}
