<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use Elephox\Support\Contract\MimeType;
use JetBrains\PhpStorm\Pure;

interface Response extends Message
{
	#[Pure]
	public function with(): ResponseBuilder;

	#[Pure]
	public function getResponseCode(): ResponseCode;

	#[Pure]
	public function getMimeType(): ?MimeType;
}
