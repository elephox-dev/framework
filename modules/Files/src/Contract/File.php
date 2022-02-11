<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Support\Contract\HasHash;
use Mimey\MimeTypeInterface;

interface File extends FilesystemNode, HasHash
{
	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeTypeInterface;

	/**
	 * @throws \Elephox\Files\FileMoveException
	 * @throws \Elephox\Files\FileAlreadyExistsException
	 */
	public function moveTo(FilesystemNode $node, bool $overwrite = true): void;

	/**
	 * @throws \Elephox\Files\FileCopyException
	 * @throws \Elephox\Files\FileAlreadyExistsException
	 */
	public function copyTo(FilesystemNode $node, bool $overwrite = true): void;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isExecutable(): bool;

	/**
	 * @throws \Elephox\Files\FileDeleteException
	 */
	public function delete(): void;
}
