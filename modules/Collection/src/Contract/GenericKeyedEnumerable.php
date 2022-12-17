<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
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
	 * @return Traversable<TIteratorKey, TSource>
	 */
	public function getIterator(): Traversable;

	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate, TSource, TIteratorKey): TAccumulate $accumulator
	 * @param TAccumulate $seed
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null): mixed;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 */
	public function all(callable $predicate): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 */
	public function any(?callable $predicate = null): bool;

	/**
	 * @param TIteratorKey $key
	 * @param TSource $value
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function append(mixed $key, mixed $value): self;

	/**
	 * @param iterable<TIteratorKey, TSource> $values
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function appendAll(iterable $values): self;

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
	public function concat(self ...$other): self;

	/**
	 * @param TIteratorKey $key
	 * @param null|callable(TIteratorKey, TIteratorKey): bool $comparer
	 */
	public function containsKey(mixed $key, ?callable $comparer = null): bool;

	/**
	 * @param null|callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(?callable $predicate = null): int;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinct(?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function except(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function exceptBy(self $other, callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function first(?callable $predicate = null): mixed;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TIteratorKey
	 */
	public function firstKey(?callable $predicate = null): mixed;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyValuePair<TIteratorKey, TSource>
	 */
	public function firstPair(?callable $predicate = null): GenericKeyValuePair;

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultValue
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TDefault|TSource
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed;

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultKey
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TDefault|TIteratorKey
	 */
	public function firstKeyOrDefault(mixed $defaultKey, ?callable $predicate = null): mixed;

	/**
	 * @param null|GenericKeyValuePair<TIteratorKey, TSource> $defaultPair
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return null|GenericKeyValuePair<TIteratorKey, TSource>
	 */
	public function firstPairOrDefault(?GenericKeyValuePair $defaultPair, ?callable $predicate = null): ?GenericKeyValuePair;

	/**
	 * @return GenericKeyedEnumerable<TSource, TIteratorKey>
	 */
	public function flip(): self;

	/**
	 * @template TGroupKey
	 *
	 * @param callable(TSource): TGroupKey $keySelector
	 * @param null|callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer
	 *
	 * @return GenericGroupedKeyedEnumerable<TGroupKey, TIteratorKey, TSource>
	 */
	public function groupBy(callable $keySelector, ?callable $comparer = null): GenericGroupedKeyedEnumerable;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersectBy(self $other, callable $keySelector, ?callable $comparer = null): self;

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
	public function join(self $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
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
	public function prepend(mixed $key, mixed $value): self;

	/**
	 * @param iterable<TIteratorKey, TSource> $values
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function prependAll(iterable $values): self;

	/**
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function reverse(bool $preserveKeys = true): self;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TResult>
	 */
	public function select(callable $selector): self;

	/**
	 * @template TResult
	 *
	 * @param callable(TIteratorKey, TSource): TResult $keySelector
	 *
	 * @return GenericKeyedEnumerable<TResult, TSource>
	 */
	public function selectKeys(callable $keySelector): self;

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): iterable<TCollection, TCollectionKey> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): self;

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer
	 */
	public function sequenceEqual(self $other, ?callable $comparer = null): bool;

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
	public function skip(int $count): self;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skipLast(int $count): self;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skipWhile(callable $predicate): self;

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
	public function take(int $count): self;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function takeLast(int $count): self;

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function takeWhile(callable $predicate): self;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(TIteratorKey, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(TIteratorKey, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, list<TSource>>
	 */
	public function groupByKey(?callable $keySelector = null): array;

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
	public function union(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function unionBy(self $other, callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function unique(?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function uniqueBy(callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function where(callable $predicate): self;

	/**
	 * @param callable(TIteratorKey, TSource, Iterator<TSource, TIteratorKey>): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function whereKey(callable $predicate): self;

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
	public function zip(self $other, ?callable $resultSelector = null, ?callable $keySelector = null): self;
}
