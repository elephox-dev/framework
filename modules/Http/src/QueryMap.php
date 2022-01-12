<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<string, int|string|list<int|string>>
 */
class QueryMap extends ArrayMap implements Contract\QueryMap
{
	public static function fromString(string $queryString): Contract\QueryMap
	{
		parse_str($queryString, $queryArray);

		return new self($queryArray);
	}

	public function __toString(): string
	{
		return http_build_query($this->toArray());
	}
}
