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
interface GenericMap extends GenericKeyedEnumerable
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): bool;

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

	/**
	 * @param TKey $key
	 */
	public function remove(mixed $key): bool;

	public function clear(): void;
}
