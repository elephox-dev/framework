<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use IteratorAggregate;
use JetBrains\PhpStorm\Pure;

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
	#[Pure] public function get(mixed $key): mixed;

	/**
	 * @param TKey $key
	 */
	#[Pure] public function has(mixed $key): bool;

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return GenericMap<TKey, TValue>
	 */
	#[Pure] public function where(callable $filter): GenericMap;


	/**
	 * @param callable(TKey, TValue): bool $filter
	 * @return GenericMap<TKey, TValue>
	 */
	#[Pure] public function whereKey(callable $filter): GenericMap;

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return GenericMap<TKey, TOut>
	 */
	#[Pure] public function map(callable $callback): GenericMap;

	/**
	 * @template TKeyOut
	 *
	 * @param callable(TKey, TValue): TKeyOut $callback
	 * @return GenericMap<TKeyOut, TValue>
	 */
	#[Pure] public function mapKeys(callable $callback): GenericMap;

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return GenericList<TOut>
	 */
	#[Pure] public function reduce(callable $callback): GenericList;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return TValue|null
	 */
	#[Pure] public function first(?callable $filter = null): mixed;

	/**
	 * @param null|callable(TKey, TValue): bool $filter
	 * @return TKey|null
	 */
	#[Pure] public function firstKey(?callable $filter = null): mixed;

	/**
	 * @param null|callable(TValue, TKey): bool $filter
	 * @return bool
	 */
	#[Pure] public function any(?callable $filter = null): bool;

	/**
	 * @param null|callable(TKey, TValue): bool $filter
	 * @return bool
	 */
	#[Pure] public function anyKey(?callable $filter = null): bool;

	/**
	 * @param TValue $value
	 * @return bool
	 */
	#[Pure] public function contains(mixed $value): bool;

	/**
	 * @return GenericList<TValue>
	 */
	#[Pure] public function values(): GenericList;

	/**
	 * @return GenericList<TKey>
	 */
	#[Pure] public function keys(): GenericList;
}
