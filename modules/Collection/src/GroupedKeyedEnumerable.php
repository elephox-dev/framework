<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\Iterator\GroupingIterator;

/**
 * @template TGroupKey
 * @template TIteratorKey
 * @template TSource
 *
 * @extends KeyedEnumerable<TGroupKey, Grouping<TGroupKey, TIteratorKey, TSource>>
 *
 * @implements Contract\GenericGroupedKeyedEnumerable<TGroupKey, TIteratorKey, TSource>
 */
class GroupedKeyedEnumerable extends KeyedEnumerable implements Contract\GenericGroupedKeyedEnumerable
{
	/**
	 * @param GroupingIterator<TGroupKey, TIteratorKey, TSource> $iterator
	 */
	public function __construct(GroupingIterator $iterator)
	{
		parent::__construct($iterator);
	}
}
