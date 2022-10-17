<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Files\DirectoryNotCreatedException;
use Elephox\Files\DirectoryNotEmptyException;
use Elephox\Files\DirectoryNotFoundException;
use Elephox\Files\FileAlreadyExistsException;
use Elephox\Files\FileCopyException;
use Elephox\Files\FileMoveException;
use Elephox\Files\FilesystemNodeNotFoundException;
use Elephox\Files\FilesystemNodeNotImplementedException;

interface Directory extends FilesystemNode
{
	/**
	 * @return GenericKeyedEnumerable<int, File>
	 */
	public function files(): GenericKeyedEnumerable;

	public function file(string $filename): File;

	/**
	 * @return GenericKeyedEnumerable<int, Directory>
	 */
	public function directories(): GenericKeyedEnumerable;

	public function directory(string $dirname): Directory;

	/**
	 * @return GenericKeyedEnumerable<int, FilesystemNode>
	 */
	public function children(): GenericKeyedEnumerable;

	/**
	 * @throws FilesystemNodeNotFoundException
	 *
	 * @param string $name
	 */
	public function child(string $name): FilesystemNode;

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

	/**
	 * @param Directory $directory
	 * @param bool $overwrite
	 *
	 * @throws FileMoveException
	 * @throws FileAlreadyExistsException
	 * @throws FilesystemNodeNotImplementedException
	 */
	public function moveTo(Directory $directory, bool $overwrite = true): void;

	/**
	 * @param Directory $directory
	 * @param bool $overwrite
	 *
	 * @throws FileCopyException
	 * @throws FileAlreadyExistsException
	 * @throws FilesystemNodeNotImplementedException
	 */
	public function copyTo(Directory $directory, bool $overwrite = true): void;
}
