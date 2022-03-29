<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\Iterator\GroupingIterator;

/**
 * @template TKey
 * @template TElement
 *
 * @extends Enumerable<Grouping<mixed, TKey, TElement>>
 * @implements Contract\GenericGroupedEnumerable<TKey, TElement>
 */
class GroupedEnumerable extends Enumerable implements Contract\GenericGroupedEnumerable
{
	/**
	 * @param GroupingIterator<mixed, TKey, TElement> $iterator
	 */
	public function __construct(GroupingIterator $iterator)
	{
		parent::__construct($iterator);
	}
}
