<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericOrderedEnumerable;
use Elephox\Collection\Iterator\OrderedIterator;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 *
 * @internal
 *
 * @extends KeyedEnumerable<NonNegativeInteger, TSource>
 * @implements GenericOrderedEnumerable<TSource>
 */
class OrderedEnumerable extends KeyedEnumerable implements GenericOrderedEnumerable
{
	/**
	 * @param OrderedIterator<NonNegativeInteger, TSource> $orderedIterator
	 */
	public function __construct(OrderedIterator $orderedIterator)
	{
		parent::__construct($orderedIterator);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TCompareKey>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		$orderedIterator = new OrderedIterator(
			$this->getIterator(),
			$keySelector,
			$comparer
		);

		return new OrderedEnumerable($orderedIterator);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TCompareKey>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);
		$comparer = DefaultEqualityComparer::invert($comparer);

		$orderedIterator = new OrderedIterator(
			$this->getIterator(),
			$keySelector,
			$comparer
		);

		return new OrderedEnumerable($orderedIterator);
	}
}
