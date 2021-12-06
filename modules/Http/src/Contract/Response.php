<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\MimeType as MimeTypeContract;
use Psr\Http\Message\ResponseInterface;

interface Response extends HttpMessage, ResponseInterface
{
	public function getHeaderMap(): ResponseHeaderMap;

	public function getResponseCode(): ResponseCode;

	public function withResponseCode(ResponseCode $code): self;

	public function getMimeType(): ?MimeTypeContract;

	public function withMimeType(?MimeTypeContract $mimeType): self;

	public function withStatus($code, $reasonPhrase = ''): self;

	public function getStatusCode(): int;

	public function getReasonPhrase(): string;

	public function send(): void;
}
