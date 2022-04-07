<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Mimey\MimeTypeInterface;

class UploadedFile implements Contract\UploadedFile
{
	/**
	 * @param string $clientName
	 * @param string $clientPath
	 * @param Stream $stream
	 * @param null|MimeTypeInterface $clientMimeType
	 * @param null|positive-int|0 $size
	 * @param UploadError $error
	 */
	public function __construct(
		private string $clientName,
		private string $clientPath,
		private Stream $stream,
		private ?MimeTypeInterface $clientMimeType = null,
		private ?int $size = null,
		private UploadError $error = UploadError::Ok,
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
		return $this->size;
	}

	public function getClientMimeType(): ?MimeTypeInterface
	{
		return $this->clientMimeType;
	}

	public function getStream(): Stream
	{
		return $this->stream;
	}
}
