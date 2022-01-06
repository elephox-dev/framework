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
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;
}
