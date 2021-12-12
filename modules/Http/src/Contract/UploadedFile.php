<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Files\Contract\File;
use Elephox\Http\UploadError;
use Elephox\Support\Contract\MimeType;

interface UploadedFile
{
	public function getClientName(): string;

	public function getClientPath(): string;

	public function getError(): UploadError;

	/**
	 * @return positive-int|0
	 */
	public function getSize(): int;

	public function getMimeType(): MimeType;

	public function getFile(): File;
}
