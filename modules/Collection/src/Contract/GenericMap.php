<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends GenericReadonlyMap<TKey, TValue>
 */
interface GenericMap extends GenericReadonlyMap
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): bool;

	/**
	 * @param TKey $key
	 */
	public function remove(mixed $key): bool;

	public function clear(): void;
}
