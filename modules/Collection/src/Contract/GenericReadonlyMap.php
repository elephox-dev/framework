<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Collection\OffsetNotFoundException;

/**
 * @template TKey
 * @template TValue
 *
 * @extends GenericKeyedEnumerable<TKey, TValue>
 */
interface GenericReadonlyMap extends GenericKeyedEnumerable
{
	/**
	 * @param TKey $key
	 *
	 * @return TValue
	 *
	 * @throws OffsetNotFoundException
	 */
	public function get(mixed $key): mixed;

	/**
	 * @param TKey $key
	 */
	public function has(mixed $key): bool;
}
