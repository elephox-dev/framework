<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template TSource
 * @template TIteratorKey
 *
 * @extends Enumerable<TSource, TIteratorKey>
 * @implements GenericOrderedEnumerable<TSource, TIteratorKey>
 */
class OrderedEnumerable extends Enumerable implements GenericOrderedEnumerable
{
	#[Pure] public function __construct(GenericIterator $iterator)
	{
		parent::__construct($iterator);
	}

	public function thenBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
	}

	public function thenByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		// TODO: Implement thenByDescending() method.
	}
}
