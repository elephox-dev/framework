<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\FilesystemNode;
use JetBrains\PhpStorm\Pure;
use Throwable;

class Directory extends AbstractFilesystemNode implements Contract\Directory
{
	public static function from(string $path): self
	{
		return new self($path);
	}

	public function files(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\File> */
		return $this->children()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\File);
	}

	public function recurseFiles(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\File> */
		return $this->recurseChildren()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\File);
	}

	public function directories(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\Directory> */
		return $this->children()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\Directory);
	}

	public function recurseDirectories(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\Directory> */
		return $this->recurseChildren()->where(static fn (Contract\FilesystemNode $node) => $node instanceof Contract\Directory);
	}

	public function children(): GenericKeyedEnumerable
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this, $this->path());
		}

		$nodes = @scandir($this->path());
		if ($nodes === false) {
			throw new DirectoryCouldNotBeScannedException($this->path());
		}

		/** @var GenericKeyedEnumerable<int, FilesystemNode> */
		return ArrayList::from($nodes)
			->where(static fn (mixed $name) => $name !== '.' && $name !== '..')
			->select(function (mixed $name): Contract\FilesystemNode {
				$path = Path::join($this->path(), $name);
				if (is_dir($path)) {
					return new self($path);
				}

				return new File($path);
			})
		;
	}

	public function recurseChildren(bool $ignoreExceptions = false): GenericKeyedEnumerable
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this, $this->path());
		}

		/** @var GenericKeyedEnumerable<int, Contract\FilesystemNode> */
		return $this->children()->selectMany(
			static function (Contract\FilesystemNode $node) use ($ignoreExceptions): iterable {
				if ($node instanceof Contract\Directory) {
					try {
						return [$node, ...$node->recurseChildren()];
					} catch (Throwable $t) {
						if ($ignoreExceptions) {
							return [$node];
						}

						throw $t;
					}
				}

				return [$node];
			},
		);
	}

	#[Pure]
	public function isRoot(): bool
	{
		return Path::isRoot($this->path());
	}

	public function isEmpty(): bool
	{
		return $this->children()->count() === 0;
	}

	public function file(string $filename): File
	{
		$path = Path::join($this->path(), $filename);

		return new File($path);
	}

	public function directory(string $dirname): self
	{
		$path = Path::join($this->path(), $dirname);

		return new self($path);
	}

	public function child(string $name, bool $throwForNotFound = false): FilesystemNode
	{
		$path = Path::join($this->path(), $name);

		if (is_dir($path)) {
			return new self($path);
		}

		if (file_exists($path)) {
			return new File($path);
		}

		if (is_link($path)) {
			return new Link($path);
		}

		if ($throwForNotFound) {
			throw new FilesystemNodeNotFoundException($path);
		}

		return new UnknownFilesystemNode($path);
	}

	#[Pure]
	public function isReadonly(): bool
	{
		return !is_writable($this->path());
	}

	public function exists(): bool
	{
		$path = $this->path();

		return file_exists($path) && is_dir($path);
	}

	public function delete(bool $recursive = true): void
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this, $this->path());
		}

		$children = $this->children();
		if ($children->isEmpty()) {
			rmdir($this->path());

			return;
		}

		if (!$recursive) {
			throw new DirectoryNotEmptyException($this->path());
		}

		foreach ($children as $node) {
			$node->delete();
		}

		rmdir($this->path());
	}

	public function ensureExists(bool $recursive = true, int $permissions = 0o0777): void
	{
		if ($this->exists()) {
			return;
		}

		if (!mkdir($this->path(), $permissions, $recursive) && !is_dir($this->path())) {
			throw new DirectoryNotCreatedException($this->path());
		}
	}

	public function moveTo(Contract\Directory $directory, bool $overwrite = true): void
	{
		if ($directory->path() === $this->path()) {
			return;
		}

		$directory->ensureExists();

		foreach ($this->children() as $node) {
			if ($node instanceof Contract\File || $node instanceof Contract\Directory) {
				$node->moveTo($directory, $overwrite);
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot move filesystem node for unimplemented type ' . get_debug_type($node));
			}
		}
	}

	public function copyTo(Contract\Directory $directory, bool $overwrite = true): void
	{
		if ($directory->path() === $this->path()) {
			return;
		}

		$directory->ensureExists();

		foreach ($this->children() as $node) {
			if ($node instanceof Contract\File || $node instanceof Contract\Directory) {
				$node->copyTo($directory, $overwrite);
			} else {
				throw new FilesystemNodeNotImplementedException($node, 'Cannot copy filesystem node for unimplemented type ' . get_debug_type($node));
			}
		}
	}
}
