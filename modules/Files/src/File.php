<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Support\Contract\MimeType;
use Exception;
use JetBrains\PhpStorm\Pure;

class File implements Contract\File
{
	#[Pure] public function __construct(
		private string    $path,
		private ?MimeType $mimeType = null,
	) {
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

	public function getModifiedTime(): DateTime
	{
		try {
			return new DateTime('@' . filemtime($this->path));
		} catch (Exception $e) {
			throw new UnreadableModifiedTimeException($this->path, previous: $e);
		}
	}

	#[Pure] public function getHash(): string|int
	{
		return md5_file($this->path);
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		if ($levels < 1) {
			throw new InvalidParentLevelException($levels);
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

	public function moveTo(string $path): bool
	{
		if (is_uploaded_file($this->path)) {
			return move_uploaded_file($this->path, $path);
		}

		return rename($this->path, $path);
	}

	public function exists(): bool
	{
		return file_exists($this->path);
	}
}
