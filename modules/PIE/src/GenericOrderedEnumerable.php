<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TKey of NonNegativeInteger
 * @template TSource
 *
 * @extends GenericEnumerable<TKey, TSource>
 */
interface GenericOrderedEnumerable extends GenericEnumerable
{
	/**
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TKey, TKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TKey, TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TKey, TKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TKey, TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;
}
