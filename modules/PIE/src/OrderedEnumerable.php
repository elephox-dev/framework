<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TKey of NonNegativeInteger
 * @template TSource
 *
 * @extends Enumerable<TKey, TSource>
 * @implements GenericOrderedEnumerable<TKey, TSource>
 */
class OrderedEnumerable extends Enumerable implements GenericOrderedEnumerable
{
	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		/** @psalm-suppress MixedArgumentTypeCoercion Psalm doesn't understand that $this has a TKey of NonNegativeInteger */
		return $this->orderBy($keySelector, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		/** @psalm-suppress MixedArgumentTypeCoercion Psalm doesn't understand that $this has a TKey of NonNegativeInteger */
		return $this->orderByDescending($keySelector);
	}
}
