<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\Iterator\GroupingIterator;
use Elephox\Collection\Iterator\LookupIterator;

/**
 * @template TSource
 * @template TKey
 * @template TElement
 *
 * @extends Enumerable<Grouping<TKey, TElement>>
 * @implements Contract\GenericGroupedEnumerable<TSource, TKey, TElement>
 */
class GroupedEnumerable extends Enumerable implements Contract\GenericGroupedEnumerable
{
	/**
	 * @param LookupIterator<TSource, TKey, TElement> $iterator
	 */
	public function __construct(LookupIterator $iterator)
	{
		parent::__construct($iterator);
	}
}
