<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Mimey\MimeTypeInterface;

/**
 * @psalm-consistent-constructor
 */
interface ResponseBuilder extends MessageBuilder
{
	public function responseCode(ResponseCode $responseCode): static;

	public function getResponseCode(): ?ResponseCode;

	public function contentType(MimeTypeInterface $mimeType): static;

	public function getContentType(): ?MimeTypeInterface;

	public function get(): Response;
}
