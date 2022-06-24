<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use Elephox\Mimey\MimeTypeInterface;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class File extends AbstractFilesystemNode implements Contract\File
{
	public static function openStream(
		string|Contract\File $file,
		bool $readable = true,
		bool $writeable = false,
		bool $create = false,
		bool $append = false,
		bool $truncate = false,
	): ResourceStream {
		if (is_string($file)) {
			$file = new self($file);
		}

		if ($readable && !$file->isReadable()) {
			throw new UnreadableFileException($file->getPath());
		}

		if (($writeable || $append) && $file->exists() && !$file->isWritable()) {
			throw new ReadOnlyFileException($file->getPath());
		}

		if ($create && $file->getParent()->isReadonly()) {
			throw new ReadonlyParentException($file->getPath());
		}

		$flags = match (true) {
			$readable && $writeable && $create && $append && !$truncate => 'ab+',
			!$readable && $writeable && $create && $append && !$truncate => 'ab',
			$readable && $writeable && $create && !$append && $truncate => 'wb+',
			!$readable && $writeable && $create && !$append && $truncate => 'wb',
			$readable && $writeable && $create && !$append && !$truncate => 'cb+',
			!$readable && $writeable && $create && !$append && !$truncate => 'cb',
			$readable && $writeable && !$create && !$append && !$truncate => 'rb+',
			$readable && !$writeable && !$create && !$append && !$truncate => 'rb',
			default => throw new InvalidArgumentException('Invalid combination of flags: readable=' . ($readable ?: '0') . ', writeable=' . ($writeable ?: '0') . ', create=' . ($create ?: '0') . ', append=' . ($append ?: '0') . ', truncate=' . ($truncate ?: '0')),
		};

		$exception = null;

		// handle any warnings emitted by fopen()
		set_error_handler(static function (int $errorCode, string $errorMessage, string $filename = '<unknown>', int $line = 0) use (&$exception): bool {
			$exception = new RuntimeException(sprintf('[%d] %s in %s:%d', $errorCode, $errorMessage, $filename, $line));

			return true;
		});

		$resource = fopen($file->getPath(), $flags);

		restore_error_handler();
		if ($exception !== null) {
			if (is_resource($resource)) {
				fclose($resource);
			}

			throw $exception;
		}

		if ($resource === false) {
			$exception = new RuntimeException('Unable to open file stream: ' . $file->getPath());
		}

		if ($exception !== null) {
			if (is_resource($resource)) {
				fclose($resource);
			}

			throw $exception;
		}

		/** @var resource $resource */
		return new ResourceStream($resource, $readable, $writeable, $readable);
	}

	#[Pure]
	public function __construct(
		string $path,
		protected readonly ?MimeTypeInterface $mimeType = null,
	) {
		parent::__construct($path);
	}

	#[Pure]
	public function getExtension(): string
	{
		return pathinfo($this->getPath(), PATHINFO_EXTENSION);
	}

	public function getSize(): int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		$size = filesize($this->getPath());
		if ($size === false) {
			throw new RuntimeException("Unable to get the size of file ({$this->getPath()})");
		}

		return $size;
	}

	#[Pure]
	public function getMimeType(): ?MimeTypeInterface
	{
		return $this->mimeType;
	}

	public function getHash(): string
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		$hash = md5_file($this->getPath());
		if ($hash === false) {
			throw new RuntimeException('Could not hash file');
		}

		return $hash;
	}

	#[Pure]
	public function isReadable(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
		return is_readable($this->getPath());
	}

	public function isWritable(): bool
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		return is_writable($this->getPath());
	}

	#[Pure]
	public function isExecutable(): bool
	{
		return is_executable($this->getPath());
	}

	public function exists(): bool
	{
		return file_exists($this->getPath());
	}

	public function copyTo(FilesystemNode $node, bool $overwrite = true): Contract\File
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		$destination = $this->getDestination($node, $overwrite);

		$success = copy($this->getPath(), $destination->getPath());
		if (!$success) {
			throw new FileCopyException($this->getPath(), $destination->getPath());
		}

		return new self($destination->getPath());
	}

	public function delete(): void
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		if (!unlink($this->getPath())) {
			throw new FileDeleteException($this->getPath());
		}
	}

	public function moveTo(FilesystemNode $node, bool $overwrite = true): Contract\File
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->getPath());
		}

		$destination = $this->getDestination($node, $overwrite);

		if (is_uploaded_file($this->getPath())) {
			$success = move_uploaded_file($this->getPath(), $destination->getPath());
		} else {
			$success = rename($this->getPath(), $destination->getPath());
		}

		if (!$success) {
			throw new FileMoveException($this->getPath(), $destination->getPath());
		}

		return new self($destination->getPath());
	}

	private function getDestination(FilesystemNode $node, bool $overwrite): Contract\File
	{
		if ($node instanceof Contract\Directory) {
			$destination = new self(Path::join($node->getPath(), $this->getName()));
		} elseif ($node instanceof Contract\File) {
			$destination = $node;
		} else {
			throw new FilesystemNodeNotImplementedException($node, 'Given filesystem node is not a file or directory');
		}

		if (!$overwrite && $destination->exists()) {
			throw new FileAlreadyExistsException($destination->getPath());
		}

		return $destination;
	}

	public function touch(): void
	{
		if ($this->exists()) {
			return;
		}

		try {
			self::openStream($this, false, true, true)->close();
		} catch (RuntimeException $e) {
			throw new FileNotCreatedException($this->path, previous: $e);
		}
	}

	public function stream(bool $writeable = false): Stream
	{
		return self::openStream($this, true, $writeable, $writeable, $writeable);
	}

	public function writeStream(Stream $contents, int $chunkSize = Contract\File::DEFAULT_STREAM_CHUNK_SIZE): void
	{
		$stream = self::openStream($this, false, true, true, false, true);

		while (!$contents->eof()) {
			$stream->write($contents->read($chunkSize));
		}
	}

	public function putContents(string $contents): void
	{
		$this->writeStream(new StringStream($contents));
	}

	public function getContents(): string
	{
		return $this->stream()->getContents();
	}
}
