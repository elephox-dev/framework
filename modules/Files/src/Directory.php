<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\FilesystemNode;
use JetBrains\PhpStorm\Pure;

class Directory extends AbstractFilesystemNode implements Contract\Directory
{
	public function getFiles(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\File> */
		return $this->getChildren()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\File);
	}

	public function getDirectories(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\Directory> */
		return $this->getChildren()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\Directory);
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
			->where(static fn (mixed $name) => $name !== '.' && $name !== '..')
			->select(function (mixed $name): Contract\FilesystemNode {
				$path = Path::join($this->path, $name);
				if (is_dir($path)) {
					return new Directory($path);
				}

				return new File($path);
			})
		;
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

		foreach ($children as $node) {
			if ($node instanceof Contract\Directory || $node instanceof Contract\File) {
				$node->delete();
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot delete filesystem node for unimplemented type ' . get_debug_type($node));
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

	public function moveTo(Contract\Directory $directory, bool $overwrite = true): void
	{
		if ($directory->getPath() === $this->path) {
			return;
		}

		$directory->ensureExists();

		foreach ($this->getChildren() as $node) {
			if ($node instanceof Contract\File || $node instanceof Contract\Directory) {
				$node->moveTo($directory, $overwrite);
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot move filesystem node for unimplemented type ' . get_debug_type($node));
			}
		}
	}

	public function copyTo(Contract\Directory $directory, bool $overwrite = true): void
	{
		if ($directory->getPath() === $this->path) {
			return;
		}

		$directory->ensureExists();

		foreach ($this->getChildren() as $node) {
			if ($node instanceof Contract\File || $node instanceof Contract\Directory) {
				$node->copyTo($directory, $overwrite);
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot copy filesystem node for unimplemented type ' . get_debug_type($node));
			}
		}
	}
}
