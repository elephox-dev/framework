<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Collection\ArrayList;

interface Directory extends FilesystemNode
{
	/**
	 * @return ArrayList<File>
	 */
	public function getFiles(): ArrayList;

	public function getFile(string $filename): ?File;

	/**
	 * @return ArrayList<Directory>
	 */
	public function getDirectories(): ArrayList;

	public function getDirectory(string $dirname): ?Directory;

	/**
	 * @return ArrayList<FilesystemNode>
	 */
	public function getChildren(): ArrayList;

	public function getChild(string $name): ?FilesystemNode;

	public function isRoot(): bool;

	public function isEmpty(): bool;
}
