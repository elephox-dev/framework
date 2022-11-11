<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use JetBrains\PhpStorm\Pure;

/**
 * @extends ArrayMap<string, int|string|list<int|string>>
 */
class QueryMap extends ArrayMap implements Contract\QueryMap
{
	#[Pure]
	public static function fromString(string $queryString): Contract\QueryMap
	{
		/** @psalm-suppress ImpureFunctionCall */
		parse_str($queryString, $queryArray);
		/** @var array<string, int|list<int|string>|string> $queryArray */

		return new self($queryArray);
	}

	#[Pure]
	public function __toString(): string
	{
		/** @psalm-suppress ImpureMethodCall */
		return http_build_query($this->toArray());
	}
}
