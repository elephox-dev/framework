<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Countable;
use Iterator;
use IteratorAggregate;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TIteratorKey
 * @template TSource
 *
 * @extends IteratorAggregate<TIteratorKey, TSource>
 */
interface GenericEnumerable extends IteratorAggregate, Countable
{
	/**
	 * @return Iterator<TIteratorKey, TSource>
	 */
	public function getIterator(): Iterator;

	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate|null, TSource, TIteratorKey): TAccumulate $accumulator
	 * @param TAccumulate|null $seed
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null): mixed;

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
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function append(mixed $value): GenericEnumerable;

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function average(callable $selector): int|float|string;

	/**
	 * @param NonNegativeInteger $size
	 *
	 * @return GenericEnumerable<NonNegativeInteger, list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TIteratorKey, TSource> ...$other
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function concat(GenericEnumerable ...$other): GenericEnumerable;

	/**
	 * @param TSource $value
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return bool
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(callable $predicate = null): int;

	/**
	 * @param TSource|null $defaultValue
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function defaultIfEmpty(mixed $defaultValue = null): GenericEnumerable;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function distinct(?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param int $index
	 *
	 * @return TSource
	 */
	public function elementAt(int $index): mixed;

	/**
	 * @param NonNegativeInteger $index
	 * @param TSource $defaultValue
	 *
	 * @return TSource
	 */
	public function elementAtOrDefault(int $index, mixed $defaultValue): mixed;

	/**
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
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

	/**
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function flip(): GenericEnumerable;

//	/**
//	 * @template TKey
//	 * @template TElement
//	 * @template TResult
//	 *
//	 * @param callable(TSource, TIteratorKey): TKey $keySelector
//	 * @param null|callable(TSource, TIteratorKey): TElement $elementSelector
//	 * @param null|callable(TKey, GenericEnumerable<TIteratorKey, TElement>): TResult $resultSelector
//	 * @param null|callable(TKey, TKey): bool $comparer
//	 *
//	 * @return GenericEnumerable<TIteratorKey, GenericGrouping<TKey, TElement>>
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
//	 * @param GenericEnumerable<TInnerIteratorKey, TInner> $inner
//	 * @param callable(TSource): TKey $outerKeySelector
//	 * @param callable(TInner): TKey $innerKeySelector
//	 * @param callable(TSource, GenericEnumerable<TInnerIteratorKey, TInner>): TResult $resultSelector
//	 * @param null|callable(TSource, TSource): bool $comparer
//	 *
//	 * @return GenericEnumerable<TIteratorKey, TSource>
//	 */
//	#[Pure]
//	public function groupJoin(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TInner
	 * @template TInnerIteratorKey
	 * @template TKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TInner, TInnerIteratorKey> $inner
	 * @param callable(TSource, TIteratorKey): TKey $outerKeySelector
	 * @param callable(TInner, TInnerIteratorKey): TKey $innerKeySelector
	 * @param callable(TSource, TInner, TIteratorKey, TInnerIteratorKey): TResult $resultSelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
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
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function max(callable $selector): int|float|string;

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function min(callable $selector): int|float|string;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function prepend(mixed $value): GenericEnumerable;

	/**
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function reverse(): GenericEnumerable;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericEnumerable<TIteratorKey, TResult>
	 */
	public function select(callable $selector): GenericEnumerable;

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): GenericEnumerable<TCollection, TCollectionKey> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer
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
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function skip(int $count): GenericEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function skipLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function skipWhile(callable $predicate): GenericEnumerable;

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function take(int $count): GenericEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function takeLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function takeWhile(callable $predicate): GenericEnumerable;

	/**
	 * @return list<TSource>
	 */
	public function toList(): array;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(): array;

	/**
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function where(callable $predicate): GenericEnumerable;

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 * @template TResultKey
	 *
	 * @param GenericEnumerable<TOtherIteratorKey, TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 * @param null|callable(TIteratorKey, TOtherIteratorKey): TResultKey $keySelector
	 *
	 * @return GenericEnumerable<TResultKey, TResult>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null, ?callable $keySelector = null): GenericEnumerable;
}
