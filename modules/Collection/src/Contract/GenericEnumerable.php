<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use IteratorAggregate;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
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
	 */
	public function all(callable $predicate): bool;

	/**
	 * @param null|callable(TSource): bool $predicate
	 */
	public function any(?callable $predicate = null): bool;

	/**
	 * @param TSource $value
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function append(mixed $value): self;

	/**
	 * @param iterable<TSource> $values
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function appendAll(iterable $values): self;

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
	public function concat(self ...$other): self;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(?callable $predicate = null): int;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function distinct(?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function except(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function exceptBy(self $other, callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TSource
	 */
	public function first(?callable $predicate = null): mixed;

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultValue
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return TDefault|TSource
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed;

	/**
	 * @template TGroupKey
	 *
	 * @param callable(TSource): TGroupKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericGroupedKeyedEnumerable<TGroupKey, mixed, TSource>
	 */
	public function groupBy(callable $keySelector, ?callable $comparer = null): GenericGroupedKeyedEnumerable;

	/**
	 * @param string $separator
	 * @param null|callable(TSource): string $toString
	 *
	 * @return string
	 */
	public function implode(string $separator = ', ', ?callable $toString = null): string;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersect(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersectBy(self $other, callable $keySelector, ?callable $comparer = null): self;

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
	public function join(self $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource): bool $predicate
	 *
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
	public function prepend(mixed $value): self;

	/**
	 * @param iterable<TSource> $values
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function prependAll(iterable $values): self;

	/**
	 * @return GenericEnumerable<TSource>
	 */
	public function reverse(bool $preserveKeys = true): self;

	/**
	 * @template TResult
	 *
	 * @param callable(TSource): TResult $selector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function select(callable $selector): self;

	/**
	 * @template TCollection
	 * @template TIntermediateKey
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource): GenericKeyedEnumerable<TIntermediateKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIntermediateKey): TResult $resultSelector
	 * @param null|callable(TSource, TCollection, TIntermediateKey): TCollectionKey $keySelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectManyKeyed(callable $collectionSelector, ?callable $resultSelector = null, ?callable $keySelector = null): GenericKeyedEnumerable;

	/**
	 * @template TCollection
	 * @template TResult
	 *
	 * @param callable(TSource): GenericEnumerable<TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): self;

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 */
	public function sequenceEqual(self $other, ?callable $comparer = null): bool;

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
	public function skip(int $count): self;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function skipLast(int $count): self;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function skipWhile(callable $predicate): self;

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
	public function take(int $count): self;

	/**
	 * @param NonNegativeInteger $count
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function takeLast(int $count): self;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function takeWhile(callable $predicate): self;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(NonNegativeInteger, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array;

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(NonNegativeInteger, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, list<TSource>>
	 */
	public function toNestedArray(?callable $keySelector = null): array;

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
	public function union(self $other, ?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unionBy(self $other, callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unique(?callable $comparer = null): self;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function uniqueBy(callable $keySelector, ?callable $comparer = null): self;

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function where(callable $predicate): self;

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
	public function zip(self $other, ?callable $resultSelector = null): self;
}
