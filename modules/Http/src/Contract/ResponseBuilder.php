<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\MimeType;

/**
 * @psalm-consistent-constructor
 */
interface ResponseBuilder extends MessageBuilder
{
	public function responseCode(ResponseCode $responseCode): static;

	public function contentType(MimeType $mimeType): static;

	public function get(): Response;
}
