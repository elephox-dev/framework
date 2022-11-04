<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 *
 * @implements OuterIterator<TValue, TKey>
 */
class FlipIterator implements OuterIterator
{
	public function __construct(
		private readonly Iterator $iterator,
	) {
	}

	public function current(): mixed
	{
		return $this->iterator->key();
	}

	public function next(): void
	{
		$this->iterator->next();
	}

	public function key(): mixed
	{
		return $this->iterator->current();
	}

	public function valid(): bool
	{
		return $this->iterator->valid();
	}

	public function rewind(): void
	{
		$this->iterator->rewind();
	}

	public function getInnerIterator(): Iterator {
		return $this->iterator;
	}
}
