<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Files\Contract\File;
use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeType;
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

	public function exception(?Throwable $exception, ?ResponseCode $responseCode = ResponseCode::InternalServerError): static;

	public function jsonBody(array $data, ?MimeTypeInterface $mimeType = MimeType::ApplicationJson): static;

	public function htmlBody(string $content, ?MimeTypeInterface $mimeType = MimeType::TextHtml): static;

	public function fileBody(string|File $path, ?MimeTypeInterface $mimeType = MimeType::ApplicationOctetStream): static;

	public function getException(): ?Throwable;

	public function get(): Response;
}
