<?php
declare(strict_types=1);

namespace Elephox\Files\Contract;

use Elephox\Files\FileAlreadyExistsException;
use Elephox\Files\FileCopyException;
use Elephox\Files\FileMoveException;
use Elephox\Files\FileNotCreatedException;
use Elephox\Stream\Contract\Stream;
use Elephox\Mimey\MimeTypeInterface;

interface File extends FilesystemNode
{
	public const DEFAULT_STREAM_CHUNK_SIZE = 4096;

	public function extension(): string;

	/**
	 * @return int<0, max>
	 */
	public function size(): int;

	public function mimeType(): ?MimeTypeInterface;

	/**
	 * @throws FileMoveException
	 * @throws FileAlreadyExistsException
	 *
	 * @param FilesystemNode $node
	 * @param bool $overwrite
	 */
	public function moveTo(FilesystemNode $node, bool $overwrite = true): self;

	/**
	 * @throws FileCopyException
	 * @throws FileAlreadyExistsException
	 *
	 * @param FilesystemNode $node
	 * @param bool $overwrite
	 */
	public function copyTo(FilesystemNode $node, bool $overwrite = true): self;

	public function isReadable(): bool;

	public function isWritable(): bool;

	public function isExecutable(): bool;

	/**
	 * @throws FileNotCreatedException
	 */
	public function touch(): void;

	public function stream(): Stream;

	public function writeStream(Stream $input, int $chunkSize = self::DEFAULT_STREAM_CHUNK_SIZE): void;

	public function writeContents(string $contents): void;

	public function contents(): string;

	public function getHash(): string;
}
