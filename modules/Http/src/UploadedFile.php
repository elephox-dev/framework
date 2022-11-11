<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Stream\Contract\Stream;
use Elephox\Mimey\MimeTypeInterface;

class UploadedFile implements Contract\UploadedFile
{
	/**
	 * @param string $clientName
	 * @param string $clientPath
	 * @param FileContract $tmpFile
	 * @param null|MimeTypeInterface $clientMimeType
	 * @param null|int<0, max> $size
	 * @param UploadError $error
	 */
	public function __construct(
		private readonly string             $clientName,
		private readonly string             $clientPath,
		private readonly FileContract       $tmpFile,
		private readonly ?MimeTypeInterface $clientMimeType = null,
		private readonly ?int               $size = null,
		private readonly UploadError        $error = UploadError::Ok,
	) {
	}

	public function getClientFilename(): string
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

	public function getSize(): ?int
	{
		return $this->size ?? $this->tmpFile->size();
	}

	public function getClientMimeType(): ?MimeTypeInterface
	{
		return $this->clientMimeType;
	}

	public function getStream(): Stream
	{
		return $this->tmpFile->stream();
	}

	/**
	 * @param string $targetPath
	 */
	public function moveTo($targetPath): void
	{
		assert(is_string($targetPath));

		$targetFile = new File($targetPath);

		$this->tmpFile->moveTo($targetFile);
	}

	public function getClientMediaType(): ?string
	{
		return $this->getClientMimeType()?->getValue();
	}
}
