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
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function files(): GenericKeyedEnumerable;

	public function file(string $filename): File;

	/**
	 * @return GenericKeyedEnumerable<int, File>
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function recurseFiles(): GenericKeyedEnumerable;

	/**
	 * @return GenericKeyedEnumerable<int, Directory>
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function directories(): GenericKeyedEnumerable;

	public function directory(string $dirname): self;

	/**
	 * @return GenericKeyedEnumerable<int, Directory>
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function recurseDirectories(): GenericKeyedEnumerable;

	/**
	 * @return GenericKeyedEnumerable<int, FilesystemNode>
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function children(): GenericKeyedEnumerable;

	/**
	 * @return GenericKeyedEnumerable<int, FilesystemNode>
	 *
	 * @throws DirectoryNotFoundException
	 */
	public function recurseChildren(bool $ignoreExceptions = false): GenericKeyedEnumerable;

	/**
	 * @throws FilesystemNodeNotFoundException
	 */
	public function child(string $name): FilesystemNode;

	public function isRoot(): bool;

	public function isEmpty(): bool;

	public function isReadonly(): bool;

	/**
	 * @throws DirectoryNotFoundException
	 * @throws DirectoryNotEmptyException
	 */
	public function delete(bool $recursive = true): void;

	/**
	 * @throws DirectoryNotCreatedException
	 */
	public function ensureExists(bool $recursive = true, int $permissions = 0o0777): void;

	/**
	 * @throws FileMoveException
	 * @throws FileAlreadyExistsException
	 * @throws FilesystemNodeNotImplementedException
	 */
	public function moveTo(self $directory, bool $overwrite = true): void;

	/**
	 * @throws FileCopyException
	 * @throws FileAlreadyExistsException
	 * @throws FilesystemNodeNotImplementedException
	 */
	public function copyTo(self $directory, bool $overwrite = true): void;
}
