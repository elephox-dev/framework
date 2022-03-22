<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use JetBrains\PhpStorm\Pure;
use Elephox\Mimey\MimeTypeInterface;
use Throwable;

interface Response extends Message
{
	#[Pure]
	public static function build(): ResponseBuilder;

	#[Pure]
	public function with(): ResponseBuilder;

	#[Pure]
	public function getResponseCode(): ResponseCode;

	#[Pure]
	public function getMimeType(): ?MimeTypeInterface;

	#[Pure]
	public function getException(): ?Throwable;
}
