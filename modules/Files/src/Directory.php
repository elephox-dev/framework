<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Collection\ArrayList;
use Elephox\Files\Contract\FilesystemNode;
use Exception;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class Directory implements Contract\Directory
{
	#[Pure] public function __construct(
		private string $path,
	)
	{
	}

	public function getFiles(): ArrayList
	{
		/** @var ArrayList<Contract\File> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\File;
		});
	}

	public function getDirectories(): ArrayList
	{
		/** @var ArrayList<Contract\Directory> */
		return $this->getChildren()->where(function (Contract\FilesystemNode $node) {
			return $node instanceof Contract\Directory;
		});
	}

	public function getChildren(): ArrayList
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		/** @var list<string> $nodes */
		$nodes = scandir($this->path);

		/** @var ArrayList<Contract\FilesystemNode> */
		return ArrayList::from($nodes)
			->where(fn(string $name) => $name !== '.' && $name !== '..')
			->map(function (string $name): Contract\FilesystemNode {
				$path = Path::join($this->path, $name);
				if (is_dir($path)) {
					return new Directory($path);
				}

				return new File($path);
			});
	}

	#[Pure] public function isRoot(): bool
	{
		return $this->path === '\\' ||
			$this->path === '/' ||
			$this->path === dirname($this->path) ||
			preg_match("/^\w:\\\\$/", $this->path) === 1;
	}

	public function isEmpty(): bool
	{
		return $this->getChildren()->isEmpty();
	}

	#[Pure] public function getPath(): string
	{
		return $this->path;
	}

	#[Pure] public function getName(): string
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

	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new DirectoryNotFoundException($this->path);
		}

		try {
			return new DateTime('@' . filemtime($this->path));
		} catch (Exception $e) {
			throw new RuntimeException("Could not parse timestamp", previous: $e);
		}
	}

	public function getFile(string $filename): File
	{
		$path = Path::join($this->path, $filename);

		/** @psalm-suppress ImpureFunctionCall */
		if (!file_exists($path)) {
			throw new FileNotFoundException($path);
		}

		return new File($path);
	}

	public function getDirectory(string $dirname): Directory
	{
		$path = Path::join($this->path, $dirname);

		/** @psalm-suppress ImpureFunctionCall */
		if (!is_dir($path)) {
			throw new DirectoryNotFoundException($path);
		}

		return new Directory($path);
	}

	public function getChild(string $name): FilesystemNode
	{
		$path = Path::join($this->path, $name);

		/** @psalm-suppress ImpureFunctionCall */
		if (is_dir($path)) {
			return new Directory($path);
		}

		/** @psalm-suppress ImpureFunctionCall */
		if (file_exists($path)) {
			return new File($path);
		}

		throw new FileNotFoundException($path);
	}

	#[Pure] public function isReadonly(): bool
	{
		return !is_writable($this->path);
	}

	#[Pure] public function exists(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
		return is_dir($this->path);
	}
}
