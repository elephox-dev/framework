<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template TValue
 * @template TKey of array-key
 *
 * @extends GenericMap<TKey, TValue>
 * @extends ArrayAccess<TKey, TValue>
 */
interface GenericArrayMap extends GenericMap, ArrayAccess
{
	/**
	 * @return TValue|false
	 */
	public function current(): mixed;

	/**
	 * @return TValue|false
	 */
	public function next(): mixed;

	/**
	 * @return TValue|false
	 */
	public function prev(): mixed;

	/**
	 * @return TKey|null
	 */
	public function key(): int|string|null;

	/**
	 * @return TValue|false
	 */
	public function reset(): mixed;
}
