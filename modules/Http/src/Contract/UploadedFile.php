<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UploadError;
use Elephox\Stream\Contract\Stream;
use Elephox\Mimey\MimeTypeInterface;
use Psr\Http\Message\UploadedFileInterface;

interface UploadedFile extends UploadedFileInterface
{
	public function getClientFilename(): string;

	public function getClientPath(): string;

	public function getClientMimeType(): ?MimeTypeInterface;

	public function getUploadError(): UploadError;

	/**
	 * @return null|int<0, max>
	 */
	public function getSize(): ?int;

	public function getStream(): Stream;
}
