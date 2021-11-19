<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use IteratorAggregate;

/**
 * @template TKey
 * @template TValue
 *
 * @extends GenericCollection<TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface ReadonlyMap extends GenericCollection, IteratorAggregate
{
	/**
	 * @param TKey $key
	 * @return TValue
	 */
	public function get(mixed $key): mixed;

	/**
	 * @param TKey $key
	 */
	public function has(mixed $key): bool;

	/**
	 * @param callable(TValue): bool $filter
	 * @return GenericMap<TKey, TValue>
	 */
	public function where(callable $filter): GenericMap;

	/**
	 * @template TOut
	 *
	 * @param callable(TValue): TOut $callback
	 * @return GenericMap<TKey, TOut>
	 */
	public function map(callable $callback): GenericMap;

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return GenericList<TOut>
	 */
	public function reduce(callable $callback): GenericList;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return TValue|null
	 */
	public function first(?callable $filter = null): mixed;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return TKey|null
	 */
	public function firstKey(?callable $filter = null): mixed;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return bool
	 */
	public function any(?callable $filter = null): bool;

	/**
	 * @param TValue $value
	 * @return bool
	 */
	public function contains(mixed $value): bool;

	/**
	 * @return GenericList<TValue>
	 */
	public function values(): GenericList;

	/**
	 * @return GenericList<TKey>
	 */
	public function keys(): GenericList;
}
