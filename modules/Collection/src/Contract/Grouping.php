<?php

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TSource
 *
 * @extends GenericEnumerable<TSource>
 */
interface Grouping extends GenericEnumerable
{
	/**
	 * @return TKey
	 */
	public function groupKey();
}
