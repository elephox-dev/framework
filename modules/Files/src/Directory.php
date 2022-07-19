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
			throw new DirectoryNotFoundException($this->getPath());
		}

		/** @var list<string> $nodes */
		$nodes = scandir($this->getPath());

		/** @var GenericKeyedEnumerable<int, FilesystemNode> */
		return ArrayList::from($nodes)
			->where(static fn (mixed $name) => $name !== '.' && $name !== '..')
			->select(function (mixed $name): Contract\FilesystemNode {
				$path = Path::join($this->getPath(), $name);
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
		return Path::isRoot($this->getPath());
	}

	public function isEmpty(): bool
	{
		return $this->getChildren()->count() === 0;
	}

	public function getFile(string $filename): File
	{
		$path = Path::join($this->getPath(), $filename);

		return new File($path);
	}

	public function getDirectory(string $dirname): Directory
	{
		$path = Path::join($this->getPath(), $dirname);

		return new Directory($path);
	}

	public function getChild(string $name, bool $throwForNotFound = false): FilesystemNode
	{
		$path = Path::join($this->getPath(), $name);

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
		return !is_writable($this->getPath());
	}

	public function exists(): bool
	{
		$path = $this->getPath();
		return file_exists($path) && is_dir($path);
	}

	public function delete(bool $recursive = true): void
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->getPath());
		}

		$children = $this->getChildren();
		if ($children->isEmpty()) {
			rmdir($this->getPath());

			return;
		}

		if (!$recursive) {
			throw new DirectoryNotEmptyException($this->getPath());
		}

		foreach ($children as $node) {
			if ($node instanceof Contract\Directory || $node instanceof Contract\File) {
				$node->delete();
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot delete filesystem node for unimplemented type ' . get_debug_type($node));
			}
		}

		rmdir($this->getPath());
	}

	public function ensureExists(bool $recursive = true, int $permissions = 0o0777): void
	{
		if ($this->exists()) {
			return;
		}

		if (!mkdir($this->getPath(), $permissions, $recursive) && !is_dir($this->getPath())) {
			throw new DirectoryNotCreatedException($this->getPath());
		}
	}

	public function moveTo(Contract\Directory $directory, bool $overwrite = true): void
	{
		if ($directory->getPath() === $this->getPath()) {
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
		if ($directory->getPath() === $this->getPath()) {
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
