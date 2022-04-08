<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use JetBrains\PhpStorm\Pure;
use ValueError;

abstract class AbstractFilesystemNode implements FilesystemNode
{
	#[Pure]
	public function __construct(protected readonly string $path)
	{
	}

	#[Pure]
	public function getPath(): string
	{
		return $this->path;
	}

	#[Pure]
	public function getName(): string
	{
		return basename($this->path);
	}

	#[Pure]
	public function __toString(): string
	{
		return $this->path;
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		try {
			return new Directory(dirname($this->path, $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}
}
