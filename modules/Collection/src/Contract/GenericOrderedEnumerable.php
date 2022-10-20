<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
 *
 * @template TSource
 *
 * @extends GenericKeyedEnumerable<NonNegativeInteger, TSource>
 */
interface GenericOrderedEnumerable extends GenericKeyedEnumerable
{
	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable;
}
