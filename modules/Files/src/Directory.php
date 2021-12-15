<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Collection\ArrayList;
use Elephox\Files\Contract\FilesystemNode;
use Exception;
use JetBrains\PhpStorm\Pure;

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
		/** @var list<string> $nodes */
		$nodes = scandir($this->path);

		/** @var ArrayList<Contract\FilesystemNode> */
		return ArrayList::fromArray($nodes)
			->where(fn(string $name) => $name !== '.' && $name !== '..')
			->map(function (string $name): Contract\FilesystemNode {
				$path = Path::join($this->path, $name);
				if (is_dir($path)) {
					return new Directory($path);
				}

				return new File($path);
			});
	}

	public function isRoot(): bool
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

	public function getParent(int $levels = 1): Contract\Directory
	{
		if ($levels < 1) {
			throw new InvalidParentLevelException($levels);
		}

		return new Directory(dirname($this->path, $levels));
	}

	public function getModifiedTime(): DateTime
	{
		try {
			return new DateTime('@' . filemtime($this->path));
		} catch (Exception $e) {
			throw new UnreadableModifiedTimeException($this->path, previous: $e);
		}
	}

	public function getFile(string $filename): ?File
	{
		$path = Path::join($this->path, $filename);

		if (!file_exists($path)) {
			return null;
		}

		return new File($path);
	}

	public function getDirectory(string $dirname): ?Directory
	{
		$path = Path::join($this->path, $dirname);

		if (!is_dir($path)) {
			return null;
		}

		return new Directory($path);
	}

	public function getChild(string $name): ?FilesystemNode
	{
		$path = Path::join($this->path, $name);

		if (is_dir($path)) {
			return new Directory($path);
		}

		if (file_exists($path)) {
			return new File($path);
		}

		return null;
	}

	public function isReadonly(): bool
	{
		return !is_writable($this->path);
	}
}
