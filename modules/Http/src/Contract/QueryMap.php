<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;
use Stringable;

/**
 * @extends GenericMap<string, int|string|list<int|string>>
 */
interface QueryMap extends GenericMap, Stringable
{
	public static function fromString(string $queryString): self;
}
