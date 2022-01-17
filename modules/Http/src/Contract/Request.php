<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;
use Elephox\Http\Url;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface Request extends Message
{
	#[Pure]
	public function with(): RequestBuilder;

	#[Pure]
	public function getMethod(): RequestMethod;

	#[Pure]
	public function getUrl(): Url;
}
