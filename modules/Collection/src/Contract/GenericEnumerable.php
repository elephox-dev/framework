<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use IteratorAggregate;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 *
 * @extends IteratorAggregate<mixed, TSource>
 * @extends GenericCollection<TSource>
 */
interface GenericEnumerable extends GenericCollection, IteratorAggregate, Countable
{
	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate|null, TSource): TAccumulate $accumulator
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
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return bool
	 */
	public function any(callable $predicate = null): bool;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function append(mixed $value): GenericEnumerable;

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function average(callable $selector): int|float|string;

	/**
	 * @param GenericEnumerable<TSource> ...$other
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function concat(GenericEnumerable ...$other): GenericEnumerable;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(callable $predicate = null): int;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function distinct(?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
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
	 * @template TGroupKey
	 *
	 * @param callable(TSource): TGroupKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<Grouping<TGroupKey, TSource>>
	 */
	public function groupBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TInner
	 * @template TCompareKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TInner> $inner
	 * @param callable(TSource): TCompareKey $outerKeySelector
	 * @param callable(TInner): TCompareKey $innerKeySelector
	 * @param callable(TSource, TInner): TResult $resultSelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
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
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function prepend(mixed $value): GenericEnumerable;

	/**
	 * @return GenericEnumerable<TSource>
	 */
	public function reverse(): GenericEnumerable;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource): TResult $selector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function select(callable $selector): GenericEnumerable;

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource): GenericKeyedEnumerable<TCollectionKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectManyKeyed(callable $collectionSelector, ?callable $resultSelector = null): GenericKeyedEnumerable;

	/**
	 * @template TCollection
	 * @template TResult
	 *
	 * @param callable(TSource): GenericEnumerable<TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericEnumerable;

	/**
	 * @param GenericEnumerable<TSource> $other
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
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function skip(int $count): GenericEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function skipLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
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
	 * @return GenericEnumerable<TSource>
	 */
	public function take(int $count): GenericEnumerable;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function takeLast(int $count): GenericEnumerable;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function takeWhile(callable $predicate): GenericEnumerable;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(NonNegativeInteger, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array;

	/**
	 * @template TIteratorKey
	 *
	 * @param callable(TSource): TIteratorKey $keySelector
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function toKeyed(callable $keySelector): GenericKeyedEnumerable;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function where(callable $predicate): GenericEnumerable;

	/**
	 * @template TOther
	 * @template TResult
	 * @template TResultKey
	 *
	 * @param GenericEnumerable<TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null): GenericEnumerable;
}
