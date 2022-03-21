<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Iterator;

/**
 * @template TKey
 * @template TGroupKey
 * @template TValue
 *
 * @implements Iterator<TKey, \Elephox\Collection\Contract\Grouping<TGroupKey, TValue>>
 */
class GroupingIterator implements Iterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param TGroupKey $groupKey
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly mixed $groupKey
	)
	{
	}

	public function current(): mixed
	{
		return $this->iterator->current();
	}

	public function next(): void
	{
		$this->iterator->next();
	}

	public function key(): mixed
	{
		return $this->iterator->key();
	}

	public function groupKey(): mixed
	{
		return $this->groupKey;
	}

	public function valid(): bool
	{
		return $this->iterator->valid();
	}

	public function rewind(): void
	{
		$this->iterator->rewind();
	}
}
