<?php
declare(strict_types=1);

namespace Elephox\PIE;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 *
 * @extends Enumerable<NonNegativeInteger, TSource>
 * @implements GenericOrderedEnumerable<TSource>
 */
class OrderedEnumerable extends Enumerable implements GenericOrderedEnumerable
{
	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		return $this->orderBy($keySelector, $comparer);
	}

	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		return $this->orderByDescending($keySelector, $comparer);
	}
}
