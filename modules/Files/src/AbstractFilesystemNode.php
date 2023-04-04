<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Files\Contract\FilesystemNode;
use Exception;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use ValueError;

abstract class AbstractFilesystemNode implements FilesystemNode
{
	#[Pure]
	public function __construct(protected readonly string $path)
	{
	}

	#[Pure]
	public function path(): string
	{
		return $this->path;
	}

	#[Pure]
	public function relativePathTo(FilesystemNode $node): string
	{
		return Path::relativeTo($this->path(), $node->path());
	}

	#[Pure]
	public function name(): string
	{
		return basename($this->path());
	}

	public function modifiedAt(): DateTime
	{
		if (!$this->exists()) {
			throw new FilesystemNodeNotFoundException($this->path());
		}

		$timestamp = filemtime($this->path());
		if ($timestamp === false) {
			throw new RuntimeException("Failed to get modified time of filesystem node ({$this->path()})");
		}

		try {
			return new DateTime('@' . $timestamp);
		} catch (Exception $e) {
			throw new RuntimeException('Could not parse timestamp', previous: $e);
		}
	}

	#[Pure]
	public function __toString(): string
	{
		return $this->path();
	}

	public function parent(int $levels = 1): Contract\Directory
	{
		try {
			return new Directory(dirname($this->path(), $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}
}
