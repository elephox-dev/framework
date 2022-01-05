<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 *
 * @extends GenericEnumerable<NonNegativeInteger, TSource>
 */
interface GenericOrderedEnumerable extends GenericEnumerable
{
	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TKey, TKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TKey
	 *
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TKey, TKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;
}
