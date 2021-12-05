<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends ReadonlyMap<TKey, TValue>
 */
interface GenericMap extends ReadonlyMap
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): void;

	/**
	 * @param TKey $key
	 */
	public function remove(mixed $key): void;

	/**
	 * @return ReadonlyMap<TKey, TValue>
	 */
	public function asReadonly(): ReadonlyMap;
}
