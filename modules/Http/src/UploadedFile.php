<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File;
use Elephox\Support\Contract\MimeType;

class UploadedFile implements Contract\UploadedFile
{
	/**
	 * @param string $clientName
	 * @param string $clientPath
	 * @param UploadError $error
	 * @param positive-int|0 $size
	 * @param MimeType $type
	 * @param File $file
	 */
	public function __construct(
		private string $clientName,
		private string $clientPath,
		private UploadError $error,
		private int $size,
		private MimeType $type,
		private File $file
	) {
	}

	public function getClientName(): string
	{
		return $this->clientName;
	}

	public function getClientPath(): string
	{
		return $this->clientPath;
	}

	public function getError(): UploadError
	{
		return $this->error;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getMimeType(): MimeType
	{
		return $this->type;
	}

	public function getFile(): File
	{
		return $this->file;
	}
}
