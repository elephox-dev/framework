<?php
declare(strict_types=1);

namespace Elephox\Files;

use DateTime;
use Elephox\Support\Contract\MimeType;
use Exception;
use JetBrains\PhpStorm\Pure;
use RuntimeException;

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

	public function getSize(): int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		return filesize($this->path);
	}

	#[Pure] public function getMimeType(): ?MimeType
	{
		return $this->mimeType;
	}

	public function getModifiedTime(): DateTime
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		try {
			return new DateTime('@' . filemtime($this->path));
		} catch (Exception $e) {
			throw new RuntimeException("Could not parse timestamp", previous: $e);
		}
	}

	public function getHash(): string|int
	{
		if (!$this->exists()) {
			throw new FileNotFoundException($this->path);
		}

		return md5_file($this->path);
	}

	public function getParent(int $levels = 1): Contract\Directory
	{
		if ($levels < 1) {
			throw new InvalidParentLevelException($levels);
		}

		return new Directory(dirname($this->path, $levels));
	}

	#[Pure] public function isReadable(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
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

	#[Pure] public function exists(): bool
	{
		/** @psalm-suppress ImpureFunctionCall */
		return file_exists($this->path);
	}
}
