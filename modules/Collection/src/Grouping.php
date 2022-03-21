<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Grouping as GroupingContract;
use Elephox\Support\DeepCloneable;
use Iterator;

/**
 * @template TGroupKey
 * @template TKey
 * @template TValue
 *
 * @implements GroupingContract<TGroupKey, TKey, TValue>
 */
class Grouping implements GroupingContract
{
	/**
	 * @use IsKeyedEnumerable<TKey, TValue>
	 */
	use IsKeyedEnumerable, DeepCloneable;

	/**
	 * @param TGroupKey $groupKey
	 * @param Iterator<TKey, TValue> $iterator
	 */
	public function __construct(
		private readonly mixed $groupKey,
		private readonly Iterator $iterator
	)
	{
	}

	/**
	 * @return TGroupKey
	 */
	public function groupKey(): mixed
	{
		return $this->groupKey;
	}

	/**
	 * @return Iterator<TKey, TValue>
	 */
	public function getIterator(): Iterator
	{
		return $this->iterator;
	}
}
