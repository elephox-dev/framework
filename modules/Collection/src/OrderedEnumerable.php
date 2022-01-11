<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericOrderedEnumerable;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 *
 * @extends KeyedEnumerable<NonNegativeInteger, TSource>
 * @implements GenericOrderedEnumerable<TSource>
 */
class OrderedEnumerable extends KeyedEnumerable implements GenericOrderedEnumerable
{
	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		/** @psalm-suppress MixedArgumentTypeCoercion */
		return $this->orderBy($keySelector, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		/** @psalm-suppress MixedArgumentTypeCoercion */
		return $this->orderByDescending($keySelector);
	}
}
