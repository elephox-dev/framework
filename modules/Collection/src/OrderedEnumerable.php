<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericOrderedEnumerable;
use Elephox\Collection\Iterator\OrderedIterator;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
 *
 * @template TSource
 * @template TCompareKey
 *
 * @internal
 *
 * @extends KeyedEnumerable<NonNegativeInteger, TSource>
 *
 * @implements GenericOrderedEnumerable<TSource>
 */
final class OrderedEnumerable extends KeyedEnumerable implements GenericOrderedEnumerable
{
	/**
	 * @param OrderedIterator<NonNegativeInteger, TSource, TCompareKey> $orderedIterator
	 */
	public function __construct(OrderedIterator $orderedIterator)
	{
		parent::__construct($orderedIterator);
	}

	/**
	 * @template TNextCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TNextCompareKey $keySelector
	 * @param null|callable(TNextCompareKey, TNextCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		/**
		 * @var OrderedIterator<NonNegativeInteger, TSource, TNextCompareKey> $orderedIterator
		 * @var callable(TSource, NonNegativeInteger): mixed $keySelector
		 * @var callable(TNextCompareKey, TNextCompareKey): int $comparer
		 */
		$orderedIterator = new OrderedIterator(
			$this->getIterator(),
			$keySelector(...),
			$comparer(...),
		);

		return new self($orderedIterator);
	}

	/**
	 * @template TNextCompareKey
	 *
	 * @param callable(TSource, NonNegativeInteger): TNextCompareKey $keySelector
	 * @param null|callable(TNextCompareKey, TNextCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);
		$comparer = DefaultEqualityComparer::invert($comparer);

		/**
		 * @var OrderedIterator<NonNegativeInteger, TSource, TNextCompareKey> $orderedIterator
		 * @var callable(TSource, NonNegativeInteger): mixed $keySelector
		 * @var callable(TNextCompareKey, TNextCompareKey): int $comparer
		 */
		$orderedIterator = new OrderedIterator(
			$this->getIterator(),
			$keySelector(...),
			$comparer(...),
		);

		return new self($orderedIterator);
	}
}
