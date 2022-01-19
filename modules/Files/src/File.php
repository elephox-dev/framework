<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Files\Contract\FilesystemNode;
use Elephox\Support\Contract\MimeType;
use Exception;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Safe\Exceptions\FilesystemException;

class File implements Contract\File
{
	#[Pure]
	public function __construct(
		private string    $path,
		private ?MimeType $mimeType = null,
	) {
	}

	#[Pure]
	public function getPath(): string
	{
		return $this->path;
	}

	#[Pure]
	public function getName(): string
	{
		return basename($this->path);
	}

	#[Pure]
	public function getExtension(): string
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	/**
	 * @throws \Safe\Exceptions\FilesystemException
	 * @throws \Elephox\Files\FileNotFoundException
	 */
	public function getSize(): int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		return \Safe\filesize($this->path);
	}

	#[Pure]
	public function getMimeType(): ?MimeType
	{
		return $this->mimeType;
	}

	/**
	 * @throws \Safe\Exceptions\FilesystemException
	 * @throws \Exception
	 */
	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		return new DateTime('@' . \Safe\filemtime($this->path));
	}

	/**
	 * @throws \Safe\Exceptions\StringsException
	 */
	public function getHash(): string|int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		return \Safe\md5_file($this->path);
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		if ($levels < 1) {
			throw new InvalidParentLevelException($levels);
		}

		return new Directory(dirname($this->path, $levels));
	}

	#[Pure]
	public function isReadable(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
		return is_readable($this->path);
	}

	#[Pure]
	public function isWritable(): bool
	{
		return is_writable($this->path);
	}

	#[Pure]
	public function isExecutable(): bool
	{
		return is_executable($this->path);
	}

	public function exists(): bool
	{
		return file_exists($this->path);
	}

	/**
	 * @throws \Elephox\Files\FileCopyException
	 */
	public function copyTo(FilesystemNode $node, bool $overwrite = true): void
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		$destination = $this->getDestination($node, $overwrite);

		try {
			\Safe\copy($this->path, $destination->getPath());
		} catch (FilesystemException $e) {
			throw new FileCopyException($this->path, $destination->getPath(), previous: $e);
		}
	}

	/**
	 * @throws \Elephox\Files\FileDeleteException
	 */
	public function delete(): void
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		try {
			\Safe\unlink($this->path);
		} catch (FilesystemException $e) {
			throw new FileDeleteException($this->path, previous: $e);
		}
	}

	/**
	 * @throws \Elephox\Files\FileMoveException
	 */
	public function moveTo(FilesystemNode $node, bool $overwrite = true): void
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		$destination = $this->getDestination($node, $overwrite);

		if (is_uploaded_file($this->path)) {
			if (!move_uploaded_file($this->path, $destination->getPath())) {
				throw new FileMoveException($this->path, $destination->getPath());
			}
		} else {
			try {
				\Safe\rename($this->path, $destination->getPath());
			} catch (FilesystemException $e) {
				throw new FileMoveException($this->path, $destination->getPath(), previous: $e);
			}
		}
	}

	/**
	 * @throws \Elephox\Files\FileAlreadyExistsException
	 */
	private function getDestination(FilesystemNode $node, bool $overwrite): Contract\File
	{
		if ($node instanceof Contract\Directory) {
			$destination = new self(Path::join($node->getPath(), $this->getName()));
		} else if ($node instanceof Contract\File) {
			$destination = $node;
		} else {
			throw new InvalidArgumentException("Given filesystem node is not a file or directory");
		}

		if (!$overwrite && $destination->exists()) {
			throw new FileAlreadyExistsException($destination->getPath());
		}

		return $destination;
	}
}
