<?php

namespace Elephox\Collection\Contract;

/**
 * @template TGroupKey
 * @template TKey
 * @template TSource
 *
 * @extends GenericKeyedEnumerable<TKey, TSource>
 */
interface Grouping extends GenericKeyedEnumerable
{
	/**
	 * @return TGroupKey
	 */
	public function groupKey(): mixed;
}
