<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<HeaderName, GenericList<string>>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
	/**
	 * @param string|HeaderName $key
	 * @param iterable<string>|string $value
	 */
	public function put(mixed $key, mixed $value): void;
}
