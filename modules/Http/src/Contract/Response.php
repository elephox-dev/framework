<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\MimeType as MimeTypeContract;

interface Response extends Message
{
	public function getResponseCode(): ResponseCode;

	public function getContentType(): ?MimeTypeContract;

	public function with(): ResponseBuilder;
}
