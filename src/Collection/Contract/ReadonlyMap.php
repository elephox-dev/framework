<?php

namespace Philly\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Philly\Collection\Contract\GenericCollection<TValue>
 */
interface ReadonlyMap extends GenericCollection
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
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return TValue|null
	 */
	public function first(?callable $filter = null): mixed;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return bool
	 */
	public function any(?callable $filter = null): bool;
}
