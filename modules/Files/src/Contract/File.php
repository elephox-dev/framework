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

	public function extension(): string;

	public function size(): int;

	public function mimeType(): ?MimeTypeInterface;

	/**
	 * @throws FileMoveException
	 * @throws FileAlreadyExistsException
	 *
	 * @param FilesystemNode $node
	 * @param bool $overwrite
	 */
	public function moveTo(FilesystemNode $node, bool $overwrite = true): File;

	/**
	 * @throws FileCopyException
	 * @throws FileAlreadyExistsException
	 *
	 * @param FilesystemNode $node
	 * @param bool $overwrite
	 */
	public function copyTo(FilesystemNode $node, bool $overwrite = true): File;

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

	public function writeStream(Stream $stream, int $chunkSize = self::DEFAULT_STREAM_CHUNK_SIZE): void;

	public function writeContents(string $contents): void;

	public function contents(): string;
}
