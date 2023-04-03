<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use Elephox\Mimey\MimeTypeInterface;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

class File extends AbstractFilesystemNode implements Contract\File
{
	public static function from(string $path): self
	{
		return new self($path);
	}

	public static function temp(null|Contract\Directory|string $parent = null, ?string $prefix = null): Contract\File
	{
		$parent ??= sys_get_temp_dir();
		if (is_string($parent)) {
			$parent = new Directory($parent);
		}

		$parent->ensureExists();

		$filename = uniqid($prefix ?? 'php', true);

		return $parent->file($filename);
	}

	public static function openStream(
		string|Contract\File $file,
		bool $readable = true,
		bool $writable = false,
		bool $create = false,
		bool $append = false,
		bool $truncate = false,
	): ResourceStream {
		if (is_string($file)) {
			$file = new self($file);
		}

		if ($readable && !$file->isReadable()) {
			throw new UnreadableFileException($file->path());
		}

		if (($writable || $append) && $file->exists() && !$file->isWritable()) {
			throw new ReadOnlyFileException($file->path());
		}

		if ($create) {
			$parent = $file->parent();
			$parent->ensureExists();
			if ($parent->isReadonly()) {
				throw new ReadonlyParentException($file->path());
			}
		}

		return ResourceStream::open($file->path(), $readable, $writable, $create, $append, $truncate);
	}

	#[Pure]
	public function __construct(
		string $path,
		protected readonly ?MimeTypeInterface $mimeType = null,
	) {
		parent::__construct($path);
	}

	#[Pure]
	public function getNameWithoutExtension(): string
	{
		return pathinfo($this->path(), PATHINFO_FILENAME);
	}

	#[Pure]
	public function extension(): string
	{
		return pathinfo($this->path(), PATHINFO_EXTENSION);
	}

	public function size(): int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		/** @var false|int<0, max> $size */
		$size = filesize($this->path());
		if ($size === false) {
			throw new RuntimeException("Unable to get the size of file ({$this->path()})");
		}

		return $size;
	}

	#[Pure]
	public function mimeType(): ?MimeTypeInterface
	{
		return $this->mimeType;
	}

	public function getHash(): string
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		$hash = md5_file($this->path());
		if ($hash === false) {
			throw new RuntimeException('Could not hash file');
		}

		return $hash;
	}

	#[Pure]
	public function isReadable(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
		return is_readable($this->path());
	}

	public function isWritable(): bool
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		return is_writable($this->path());
	}

	#[Pure]
	public function isExecutable(): bool
	{
		return is_executable($this->path());
	}

	public function exists(): bool
	{
		$path = $this->path();

		return file_exists($path) && is_file($path);
	}

	public function copyTo(FilesystemNode $node, bool $overwrite = true): Contract\File
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		$destination = $this->getDestination($node, $overwrite);

		$success = copy($this->path(), $destination->path());
		if (!$success) {
			throw new FileCopyException($this->path(), $destination->path());
		}

		return new self($destination->path());
	}

	public function delete(): void
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		if (!unlink($this->path())) {
			throw new FileDeleteException($this->path());
		}
	}

	public function moveTo(FilesystemNode $node, bool $overwrite = true): Contract\File
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path());
		}

		$destination = $this->getDestination($node, $overwrite);

		if (is_uploaded_file($this->path())) {
			$success = move_uploaded_file($this->path(), $destination->path());
		} else {
			$success = rename($this->path(), $destination->path());
		}

		if (!$success) {
			throw new FileMoveException($this->path(), $destination->path());
		}

		return new self($destination->path());
	}

	private function getDestination(FilesystemNode $node, bool $overwrite): Contract\File
	{
		if ($node instanceof Contract\Directory) {
			$destination = new self(Path::join($node->path(), $this->name()));
		} elseif ($node instanceof Contract\File) {
			$destination = $node;
		} elseif ($node instanceof Contract\Link) {
			$target = $node->target();
			if ($target instanceof Contract\Link) {
				$nextTarget = $target->target();

				if ($nextTarget->path() === $this->path()) {
					throw new LinkLoopDetectedException($node->path(), $target->path());
				}

				// MIND: it's still possible for $nextTarget to point to another link and so on
			}

			$destination = $this->getDestination($target, $overwrite);
		} else {
			throw new FilesystemNodeNotImplementedException($node, 'Given filesystem node is not a file or directory');
		}

		if (!$overwrite && $destination->exists()) {
			throw new FileAlreadyExistsException($destination->path());
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
			throw new FileNotCreatedException($this->path(), previous: $e);
		}
	}

	public function stream(bool $writeable = false): Stream
	{
		return self::openStream($this, true, $writeable, $writeable, $writeable);
	}

	public function writeStream(Stream $input, int $chunkSize = Contract\File::DEFAULT_STREAM_CHUNK_SIZE): void
	{
		$output = self::openStream($this, false, true, true, false, true);

		while (!$input->eof()) {
			$output->write($input->read($chunkSize));
		}
	}

	public function writeContents(string $contents): void
	{
		$this->writeStream(new StringStream($contents));
	}

	public function contents(): string
	{
		return $this->stream()->getContents();
	}
}
