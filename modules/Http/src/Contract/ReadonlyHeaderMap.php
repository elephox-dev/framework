<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\ReadonlyMap;
use Elephox\Collection\Contract\ReadonlyList;

/**
 * @extends ReadonlyMap<HeaderName, ReadonlyList<string>>
 * @extends ArrayConvertible<string, list<string>>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
	/**
	 * @param string|HeaderName $key
	 *
	 * @return bool
	 */
	public function has(mixed $key): bool;

	/**
	 * @param string|HeaderName $key
	 *
	 * @return ReadonlyList<string>
	 */
	public function get(mixed $key): ReadonlyList;
}
