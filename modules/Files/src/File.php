<?php

namespace Elephox\Files;

use DateTime;
use Elephox\Http\Contract\MimeType;
use JetBrains\PhpStorm\Pure;

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

	/**
	 * @throws \Exception
	 */
	public function getCreatedTime(): DateTime
	{
		return new DateTime('@' . filectime($this->path));
	}

	public function getContents(): string
	{
		return file_get_contents($this->path);
	}

	#[Pure] public function getHash(): string|int
	{
		return md5_file($this->path);
	}
}
