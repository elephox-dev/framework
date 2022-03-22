<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeTypeInterface;
use Throwable;

/**
 * @psalm-consistent-constructor
 */
interface ResponseBuilder extends MessageBuilder
{
	public function responseCode(ResponseCode $responseCode): static;

	public function getResponseCode(): ?ResponseCode;

	public function contentType(?MimeTypeInterface $mimeType): static;

	public function getContentType(): ?MimeTypeInterface;

	public function exception(?Throwable $exception): static;

	public function getException(): ?Throwable;

	public function get(): Response;
}
