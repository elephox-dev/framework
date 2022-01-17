<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\MimeType;

interface ResponseBuilder extends MessageBuilder
{
	public function responseCode(ResponseCode $responseCode): ResponseBuilder;

	public function contentType(MimeType $mimeType): ResponseBuilder;

	public function get(): Response;
}
