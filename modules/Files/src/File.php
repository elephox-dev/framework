<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\ResourceStream;
use Elephox\Support\Contract\MimeType;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use OutOfRangeException;

class File implements Contract\File
{
	#[Pure] public function __construct(
		private string    $path,
		private ?MimeType $mimeType = null,
	)
	{
	}

	#[Pure] public function getPath(): string
	{
		return $this->path;
	}

	#[Pure] public function getName(): string
	{
		return basename($this->path);
	}

	#[Pure] public function getExtension(): string
	{
		return pathinfo($this->path, PATHINFO_EXTENSION);
	}

	#[Pure] public function getSize(): int
	{
		return filesize($this->path);
	}

	#[Pure] public function getMimeType(): ?MimeType
	{
		return $this->mimeType;
	}

	/**
	 * @throws \Exception
	 */
	public function getModifiedTime(): DateTime
	{
		return new DateTime('@' . filemtime($this->path));
	}

	public function getStream(bool $readable = true, bool $writeable = false, bool $create = false, bool $append = false, bool $truncate = false): Stream
	{
		if ($readable && !$this->isReadable()) {
			throw new UnreadableFileException($this->path);
		}

		if (($writeable || $append) && !$this->isWritable()) {
			throw new UnwritableFileException($this->path);
		}

		if ($create && $this->getParent()->isReadonly()) {
			throw new ReadonlyParentException($this->path);
		}

		$flags = match (true) {
			 $readable &&  $writeable &&  $append &&  $create => 'a+',
			!$readable &&  $writeable &&  $append &&  $create => 'a',
			 $readable &&  $writeable && !$append &&  $create && $truncate => 'wb+',
			!$readable &&  $writeable && !$append &&  $create && $truncate => 'wb',
			 $readable &&  $writeable && !$append &&  $create => 'c+',
			!$readable &&  $writeable && !$append &&  $create => 'c',
			 $readable &&  $writeable && !$append && !$create => 'rb+',
			 $readable && !$writeable && !$append && !$create => 'rb',
			default => throw new InvalidArgumentException('Invalid combination of flags'),
		};

		return new ResourceStream(fopen($this->path, $flags), $readable, $writeable, $readable);
	}

	#[Pure] public function getHash(): string|int
	{
		return md5_file($this->path);
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		if ($levels < 1) {
			throw new OutOfRangeException('Levels must be greater than 0');
		}

		return new Directory(dirname($this->path, $levels));
	}

	 public function isReadable(): bool
	{
		return is_readable($this->path);
	}

	#[Pure] public function isWritable(): bool
	{
		return is_writable($this->path);
	}

	#[Pure] public function isExecutable(): bool
	{
		return is_executable($this->path);
	}
}
