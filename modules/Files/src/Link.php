<?php
declare(strict_types=1);

namespace Elephox\Files;

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
			throw new LinkNotFoundException($this->path());
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
}
