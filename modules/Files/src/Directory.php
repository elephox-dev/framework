<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\Contract\FilesystemNode;
use JetBrains\PhpStorm\Pure;

class Directory implements Contract\Directory
{
	#[Pure]
	public function __construct(
		private string $path,
	) {
	}

	/**
	 * @throws \Safe\Exceptions\DirException
	 */
	public function getFiles(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\File> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\File;
		});
	}

	/**
	 * @throws \Safe\Exceptions\DirException
	 */
	public function getDirectories(): GenericKeyedEnumerable
	{
		/** @var GenericKeyedEnumerable<int, Contract\Directory> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\Directory;
		});
	}

	/**
	 * @throws \Safe\Exceptions\DirException
	 */
	public function getChildren(): GenericKeyedEnumerable
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		/** @var list<string> $nodes */
		$nodes = \Safe\scandir($this->path);

		/** @var GenericKeyedEnumerable<int, FilesystemNode> */
		return ArrayList::from($nodes)
			->where(fn(string $name) => $name !== '.' && $name !== '..')
			->select(function (string $name): Contract\FilesystemNode {
				$path = Path::join($this->path, $name);
				if (is_dir($path)) {
					return new Directory($path);
				}

				return new File($path);
			});
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function isRoot(): bool
	{
		return Path::isRoot($this->path);
	}

	/**
	 * @throws \Safe\Exceptions\DirException
	 */
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
		if ($levels < 1) {
			throw new InvalidParentLevelException($levels);
		}

		return new Directory(dirname($this->path, $levels));
	}

	/**
	 * @throws \Safe\Exceptions\FilesystemException
	 * @throws \Exception
	 */
	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		return new DateTime('@' . \Safe\filemtime($this->path));
	}

	/**
	 * @throws \Elephox\Files\FileNotFoundException
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function getFile(string $filename): File
	{
		$path = Path::join($this->path, $filename);

		if (!file_exists($path)) {
			throw new FileNotFoundException($path);
		}

		return new File($path);
	}

	/**
	 * @throws \Elephox\Files\DirectoryNotFoundException
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function getDirectory(string $dirname): Directory
	{
		$path = Path::join($this->path, $dirname);

		if (!is_dir($path)) {
			throw new DirectoryNotFoundException($path);
		}

		return new Directory($path);
	}

	/**
	 * @throws \Elephox\Files\FileNotFoundException
	 * @throws \Safe\Exceptions\PcreException
	 */
	public function getChild(string $name): FilesystemNode
	{
		$path = Path::join($this->path, $name);

		if (is_dir($path)) {
			return new Directory($path);
		}

		if (file_exists($path)) {
			return new File($path);
		}

		throw new FileNotFoundException($path);
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

	/**
	 * @throws \Safe\Exceptions\FilesystemException
	 * @throws \Elephox\Files\DirectoryNotFoundException
	 * @throws \Elephox\Files\DirectoryNotEmptyException
	 * @throws \Elephox\Files\FileDeleteException
	 * @throws \Safe\Exceptions\DirException
	 */
	public function delete(bool $recursive = true): void
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		$children = $this->getChildren();
		if ($children->isEmpty()) {
			\Safe\rmdir($this->path);

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
	}
}
