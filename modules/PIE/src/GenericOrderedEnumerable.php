<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @template TSource
 * @template TIteratorKey
 *
 * @extends GenericEnumerable<TSource, TIteratorKey>
 */
interface GenericOrderedEnumerable extends GenericEnumerable
{
	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;
}
