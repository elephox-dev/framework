<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

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
 * @extends GenericCollection<TSource>
 */
interface GenericKeyedEnumerable extends GenericCollection, IteratorAggregate, Countable
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
	 * @param callable(TSource, TIteratorKey): bool $predicate
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
	 * @param TIteratorKey $key
	 * @param TSource $value
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function append(mixed $key, mixed $value): GenericKeyedEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function average(callable $selector): int|float|string;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> ...$other
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function concat(GenericKeyedEnumerable ...$other): GenericKeyedEnumerable;

	/**
	 * @param null|callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(callable $predicate = null): int;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinct(?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function except(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function exceptBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function first(?callable $predicate = null): mixed;

	/**
	 * @param TSource $defaultValue
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed;

	/**
	 * @return GenericKeyedEnumerable<TSource, TIteratorKey>
	 */
	public function flip(): GenericKeyedEnumerable;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersectBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @template TInner
	 * @template TInnerIteratorKey
	 * @template TCompareKey
	 * @template TResult
	 *
	 * @param GenericKeyedEnumerable<TInner, TInnerIteratorKey> $inner
	 * @param callable(TSource, TIteratorKey): TCompareKey $outerKeySelector
	 * @param callable(TInner, TInnerIteratorKey): TCompareKey $innerKeySelector
	 * @param callable(TSource, TInner, TIteratorKey, TInnerIteratorKey): TResult $resultSelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function join(GenericKeyedEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 * @return TSource
	 */
	public function last(?callable $predicate = null): mixed;

	/**
	 * @param TSource $default
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function lastOrDefault(mixed $default, ?callable $predicate = null): mixed;

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function max(callable $selector): int|float|string;

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
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
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @param TIteratorKey $key
	 * @param TSource $value
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function prepend(mixed $key, mixed $value): GenericKeyedEnumerable;

	/**
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function reverse(): GenericKeyedEnumerable;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TResult>
	 */
	public function select(callable $selector): GenericKeyedEnumerable;

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): GenericKeyedEnumerable<TCollection, TCollectionKey> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericKeyedEnumerable;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer
	 *
	 * @return bool
	 */
	public function sequenceEqual(GenericKeyedEnumerable $other, ?callable $comparer = null): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function single(?callable $predicate = null): mixed;

	/**
	 * @param TSource $default
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function singleOrDefault(mixed $default, ?callable $predicate = null): mixed;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skip(int $count): GenericKeyedEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skipLast(int $count): GenericKeyedEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skipWhile(callable $predicate): GenericKeyedEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function take(int $count): GenericKeyedEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function takeLast(int $count): GenericKeyedEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function takeWhile(callable $predicate): GenericKeyedEnumerable;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(TSource, TIteratorKey): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array;

	/**
	 * @return GenericEnumerable<TIteratorKey>
	 */
	public function keys(): GenericEnumerable;

	/**
	 * @return GenericEnumerable<TSource>
	 */
	public function values(): GenericEnumerable;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function union(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function unionBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable;

	/**
	 * @param callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function where(callable $predicate): GenericKeyedEnumerable;

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 * @template TResultKey
	 *
	 * @param GenericKeyedEnumerable<TOtherIteratorKey, TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 * @param null|callable(TIteratorKey, TOtherIteratorKey): TResultKey $keySelector
	 *
	 * @return GenericKeyedEnumerable<TResultKey, TResult>
	 */
	public function zip(GenericKeyedEnumerable $other, ?callable $resultSelector = null, ?callable $keySelector = null): GenericKeyedEnumerable;
}
