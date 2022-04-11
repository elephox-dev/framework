<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\DirectoryNotCreatedException;
use Elephox\Files\DirectoryNotEmptyException;
use Elephox\Files\DirectoryNotFoundException;
use Elephox\Files\FilesystemNodeNotFoundException;

interface Directory extends FilesystemNode
{
	/**
	 * @return GenericKeyedEnumerable<int, File>
	 */
	public function getFiles(): GenericKeyedEnumerable;

	public function getFile(string $filename): File;

	/**
	 * @return GenericKeyedEnumerable<int, Directory>
	 */
	public function getDirectories(): GenericKeyedEnumerable;

	public function getDirectory(string $dirname): Directory;

	/**
	 * @return GenericKeyedEnumerable<int, FilesystemNode>
	 */
	public function getChildren(): GenericKeyedEnumerable;

	/**
	 * @throws FilesystemNodeNotFoundException
	 *
	 * @param string $name
	 */
	public function getChild(string $name): FilesystemNode;

	public function isRoot(): bool;

	public function isEmpty(): bool;

	public function isReadonly(): bool;

	/**
	 * @throws DirectoryNotFoundException
	 * @throws DirectoryNotEmptyException
	 *
	 * @param bool $recursive
	 */
	public function delete(bool $recursive = true): void;

	/**
	 * @throws DirectoryNotCreatedException
	 *
	 * @param bool $recursive
	 * @param int $permissions
	 */
	public function ensureExists(bool $recursive = true, int $permissions = 0o0777): void;
}
