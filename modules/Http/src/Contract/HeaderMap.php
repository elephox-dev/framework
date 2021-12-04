<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;
use Elephox\Collection\Contract\ReadonlyList;

/**
 * @extends GenericMap<non-empty-string|HeaderName, ReadonlyList<string>>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
	/**
	 * @param string|HeaderName $key
	 * @param iterable<string>|string $value
	 */
	public function put(mixed $key, mixed $value): void;
}
