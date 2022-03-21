<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\FilesystemNode;
use Exception;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use ValueError;

class Directory implements Contract\Directory
{
	#[Pure]
	public function __construct(
		private string $path,
	) {
	}

	public function getFiles(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\File> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\File;
		});
	}

	public function getDirectories(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\Directory> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\Directory;
		});
	}

	public function getChildren(): GenericKeyedEnumerable
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		/** @var list<string> $nodes */
		$nodes = scandir($this->path);

		/** @var GenericKeyedEnumerable<int, FilesystemNode> */
		return ArrayList::from($nodes)
			->where(static fn(mixed $name) => $name !== '.' && $name !== '..')
			->select(function (mixed $name): Contract\FilesystemNode {
				/** @var string $name */
				$path = Path::join($this->path, $name);
				if (is_dir($path)) {
					return new Directory($path);
				}

				return new File($path);
			});
	}

	#[Pure]
	public function isRoot(): bool
	{
		return Path::isRoot($this->path);
	}

	public function isEmpty(): bool
	{
		return $this->getChildren()->count() === 0;
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

	public function getParent(int $levels = 1): Directory
	{
		try {
			return new Directory(dirname($this->path, $levels));
		} catch (ValueError $error) {
			throw new InvalidParentLevelException($levels, previous: $error);
		}
	}

	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		$timestamp = filemtime($this->path);
		if ($timestamp === false) {
			throw new RuntimeException("Failed to get modified time of directory ($this->path)");
		}

		try {
			return new DateTime('@' . $timestamp);
		} catch (Exception $e) {
			throw new RuntimeException("Could not parse timestamp", previous: $e);
		}
	}

	public function getFile(string $filename): File
	{
		$path = Path::join($this->path, $filename);

		return new File($path);
	}

	public function getDirectory(string $dirname): Directory
	{
		$path = Path::join($this->path, $dirname);

		return new Directory($path);
	}

	public function getChild(string $name, bool $throwForNotFound = false): FilesystemNode
	{
		$path = Path::join($this->path, $name);

		if (is_dir($path)) {
			return new Directory($path);
		}

		if (file_exists($path)) {
			return new File($path);
		}

		if ($throwForNotFound) {
			throw new FilesystemNodeNotFoundException($path);
		}

		return new UnknownFilesystemNode($path);
	}

	#[Pure]
	public function isReadonly(): bool
	{
		return !is_writable($this->path);
	}

	public function exists(): bool
	{
		return is_dir($this->path);
	}

	public function delete(bool $recursive = true): void
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		$children = $this->getChildren();
		if ($children->isEmpty()) {
			rmdir($this->path);

			return;
		}

		if (!$recursive) {
			throw new DirectoryNotEmptyException($this->path);
		}

		foreach ($children as $child) {
			if ($child instanceof Contract\Directory) {
				$child->delete(true);
			} else if ($child instanceof Contract\File) {
				$child->delete();
			}
		}

		rmdir($this->path);
	}

	public function ensureExists(bool $recursive = true, int $permissions = 0o0777): void
	{
		if ($this->exists()) {
			return;
		}

		if (!mkdir($this->path, $permissions, $recursive) && !is_dir($this->path)) {
			throw new DirectoryNotCreatedException($this->path);
		}
	}
}
