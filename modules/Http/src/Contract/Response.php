<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\ResponseCode;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\ResponseInterface;
use Throwable;

interface Response extends Message, ResponseInterface
{
	#[Pure]
	public static function build(): ResponseBuilder;

	#[Pure]
	public function with(): ResponseBuilder;

	#[Pure]
	public function getResponseCode(): ResponseCode;

	#[Pure]
	public function getException(): ?Throwable;
}
