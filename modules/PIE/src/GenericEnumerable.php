<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Countable;
use IteratorAggregate;
use JetBrains\PhpStorm\Pure;

/**
 * @template TSource
 * @template TIteratorKey
 *
 * @extends IteratorAggregate<TIteratorKey, TSource>
 */
interface GenericEnumerable extends IteratorAggregate, Countable
{
	/**
	 * @return GenericIterator<TSource, TIteratorKey>
	 */
	public function getIterator(): GenericIterator;

	/**
	 * @template TAccumulate
	 * @template TResult
	 *
	 * @param callable(TAccumulate, TSource, TIteratorKey): TAccumulate $accumulator
	 * @param TAccumulate|null $seed
	 * @param null|callable(TAccumulate): TResult $resultSelector
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null, callable $resultSelector = null): mixed;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return bool
	 */
	public function all(callable $predicate): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return bool
	 */
	public function any(callable $predicate = null): bool;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function append(mixed $value): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): int|float $selector
	 *
	 * @return int|float
	 */
	public function average(callable $selector): int|float;

	/**
	 * @param int $size
	 *
	 * @return GenericEnumerable<non-empty-list<TSource>, TIteratorKey>
	 */
	#[Pure]
	public function chunk(int $size): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> ...$enumerables
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function concat(GenericEnumerable ...$enumerables): GenericEnumerable;

	/**
	 * @param TSource $value
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return bool
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return int
	 */
	public function count(callable $predicate = null): int;

	/**
	 * @param TSource|null $defaultValue
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function defaultIfEmpty(mixed $defaultValue = null): GenericEnumerable;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function distinct(?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param int $index
	 *
	 * @return TSource
	 */
	public function elementAt(int $index): mixed;

	/**
	 * @param int $index
	 * @param TSource $defaultValue
	 *
	 * @return TSource
	 */
	public function elementAtOrDefault(int $index, mixed $defaultValue): mixed;

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function exceptBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function first(?callable $predicate = null): mixed;

	/**
	 * @param TSource $defaultValue
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed;

//	/**
//	 * @template TKey
//	 * @template TElement
//	 * @template TResult
//	 *
//	 * @param callable(TSource, TIteratorKey): TKey $keySelector
//	 * @param null|callable(TSource, TIteratorKey): TElement $elementSelector
//	 * @param null|callable(TKey, GenericEnumerable<TElement, TIteratorKey>): TResult $resultSelector
//	 * @param null|callable(TKey, TKey): bool $comparer
//	 *
//	 * @return GenericEnumerable<GenericGrouping<TKey, TElement>, TIteratorKey>
//	 */
//	#[Pure]
//	public function groupBy(callable $keySelector, ?callable $elementSelector = null, ?callable $resultSelector = null, ?callable $comparer = null): GenericEnumerable;
//
//	/**
//	 * @template TInner
//	 * @template TInnerIteratorKey
//	 * @template TKey
//	 * @template TResult
//	 *
//	 * @param GenericEnumerable<TInner, TInnerIteratorKey> $inner
//	 * @param callable(TSource): TKey $outerKeySelector
//	 * @param callable(TInner): TKey $innerKeySelector
//	 * @param callable(TSource, GenericEnumerable<TInner, TInnerIteratorKey>): TResult $resultSelector
//	 * @param null|callable(TSource, TSource): bool $comparer
//	 *
//	 * @return GenericEnumerable<TSource, TIteratorKey>
//	 */
//	#[Pure]
//	public function groupJoin(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TKey, TIteratorKey> $other
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TInner
	 * @template TInnerIteratorKey
	 * @template TKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TInner, TInnerIteratorKey> $inner
	 * @param callable(TSource): TKey $outerKeySelector
	 * @param callable(TInner): TKey $innerKeySelector
	 * @param callable(TSource, TInner): TResult $resultSelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function join(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param null|callable(TSource): bool $predicate
	 * @return TSource
	 */
	public function last(?callable $predicate = null): mixed;

	/**
	 * @param TSource $default
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function lastOrDefault(mixed $default, ?callable $predicate = null): mixed;

	/**
	 * @param callable(TSource): int|float $selector
	 *
	 * @return int|float
	 */
	public function max(callable $selector): int|float;

	/**
	 * @param callable(TSource): int|float $selector
	 *
	 * @return int|float
	 */
	public function min(callable $selector): int|float;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function prepend(mixed $value): GenericEnumerable;

	/**
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function reverse(): GenericEnumerable;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericEnumerable<TResult, TIteratorKey>
	 */
	public function select(callable $selector): GenericEnumerable;

	/**
	 * @template TCollection
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): GenericEnumerable<TCollection, TIteratorKey> $collectionSelector
	 * @param callable(TSource, TCollection): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult, TIteratorKey>
	 */
	public function selectMany(callable $collectionSelector, callable $resultSelector): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return bool
	 */
	public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function single(?callable $predicate = null): mixed;

	/**
	 * @param TSource $default
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function singleOrDefault(mixed $default, ?callable $predicate = null): mixed;

	/**
	 * @param int $count
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function skip(int $count): GenericEnumerable;

	/**
	 * @param int $count
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function skipLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function skipWhile(callable $predicate): GenericEnumerable;

	/**
	 * @param callable(TSource): int|float $selector
	 *
	 * @return int|float
	 */
	public function sum(callable $selector): int|float;

	/**
	 * @param int $count
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function take(int $count): GenericEnumerable;

	/**
	 * @param int $count
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	#[Pure]
	public function takeLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function takeWhile(callable $predicate): GenericEnumerable;

	/**
	 * @return list<TSource>
	 */
	public function toList(): array;

	/**
	 * @return array<TIteratorKey, TSource>
	 */
	public function toArray(): array;

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): int $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param callable(TSource, int): bool $predicate
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function where(callable $predicate): GenericEnumerable;

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TOther, TOtherIteratorKey> $other
	 * @param null|callable(TSource, TOther, TIteratorKey, TOtherIteratorKey): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult, TIteratorKey>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null): GenericEnumerable;
}
