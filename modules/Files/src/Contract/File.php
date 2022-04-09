<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Files\FileAlreadyExistsException;
use Elephox\Files\FileCopyException;
use Elephox\Files\FileDeleteException;
use Elephox\Files\FileMoveException;
use Elephox\Files\FileNotCreatedException;
use Elephox\Stream\Contract\Stream;
use Elephox\Support\Contract\HasHash;
use Elephox\Mimey\MimeTypeInterface;

interface File extends FilesystemNode, HasHash
{
	public const DEFAULT_STREAM_CHUNK_SIZE = 4096;

	public function getExtension(): string;

	public function getSize(): int;

	public function getMimeType(): ?MimeTypeInterface;

	/**
	 * @throws FileMoveException
	 * @throws FileAlreadyExistsException
	 */
	public function moveTo(FilesystemNode $node, bool $overwrite = true): void;

	/**
	 * @throws FileCopyException
	 * @throws FileAlreadyExistsException
	 */
	public function copyTo(FilesystemNode $node, bool $overwrite = true): void;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isExecutable(): bool;

	/**
	 * @throws FileDeleteException
	 */
	public function delete(): void;

	/**
	 * @throws FileNotCreatedException
	 */
	public function touch(): void;

	public function stream(): Stream;

	public function writeStream(Stream $contents, int $chunkSize = self::DEFAULT_STREAM_CHUNK_SIZE): void;

	public function putContents(string $contents): void;
}
