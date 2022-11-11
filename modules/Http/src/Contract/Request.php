<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;
use Elephox\Http\Url;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\RequestInterface;

#[Immutable]
interface Request extends Message, RequestInterface
{
	#[Pure]
	public static function build(): RequestBuilder;

	#[Pure]
	public function with(): RequestBuilder;

	#[Pure]
	public function getRequestMethod(): RequestMethod;

	#[Pure]
	public function getUrl(): Url;
}
