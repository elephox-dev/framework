<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\UrlScheme;
use Elephox\Support\Contract\ArrayConvertible;
use Psr\Http\Message\UriInterface;
use Stringable;

interface Url extends Stringable, ArrayConvertible, UriInterface
{
	public function getUrlScheme(): ?UrlScheme;
}
