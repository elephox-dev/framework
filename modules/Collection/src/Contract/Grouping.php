<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TGroupKey
 * @template TIteratorKey
 * @template TSource
 *
 * @extends GenericKeyedEnumerable<TIteratorKey, TSource>
 */
interface Grouping extends GenericKeyedEnumerable
{
	/**
	 * @return TGroupKey
	 */
	public function groupKey(): mixed;
}
