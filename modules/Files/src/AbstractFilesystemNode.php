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

	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new FilesystemNodeNotFoundException($this->getPath());
		}

		$timestamp = filemtime($this->getPath());
		if ($timestamp === false) {
			throw new RuntimeException("Failed to get modified time of filesystem node ({$this->getPath()})");
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
