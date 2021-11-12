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

	/**
	 * @return ArrayList<Directory>
	 */
	public function getDirectories(): ArrayList;

	/**
	 * @return ArrayList<FilesystemNode>
	 */
	public function getChildren(): ArrayList;

	public function isRoot(): bool;

	public function isEmpty(): bool;
}
