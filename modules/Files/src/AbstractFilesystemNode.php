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

	public function getPathRelative(FilesystemNode $node): string
	{
		return Path::relativeTo($this->getPath(), $node->getPath());
	}

	#[Pure]
	public function getName(): string
	{
		return basename($this->getPath());
	}

	#[Pure]
	public function __toString(): string
	{
		return $this->getPath();
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		try {
			return new Directory(dirname($this->getPath(), $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}
}
