<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UploadError;
use Elephox\Stream\Contract\Stream;
use Mimey\MimeTypeInterface;

interface UploadedFile
{
	public function getClientFilename(): string;

	public function getClientPath(): string;

	public function getClientMimeType(): ?MimeTypeInterface;

	public function getError(): UploadError;

	/**
	 * @return null|positive-int|0
	 */
	public function getSize(): ?int;

	public function getStream(): Stream;
}
