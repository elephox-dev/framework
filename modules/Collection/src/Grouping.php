<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\Grouping as GroupingContract;
use Traversable;

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
	use IsKeyedEnumerable;

	/**
	 * @param TGroupKey $groupKey
	 * @param Traversable<TKey, TValue> $iterator
	 */
	public function __construct(
		private readonly mixed $groupKey,
		private readonly Traversable $iterator,
	) {
	}

	/**
	 * @return TGroupKey
	 */
	public function groupKey(): mixed
	{
		return $this->groupKey;
	}

	/**
	 * @return Traversable<TKey, TValue>
	 */
	public function getIterator(): Traversable
	{
		return $this->iterator;
	}
}
