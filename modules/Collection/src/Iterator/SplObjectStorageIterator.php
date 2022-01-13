<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use BadMethodCallException;
use Iterator;
use SplObjectStorage;

/**
 * @template TKey of object
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
class SplObjectStorageIterator implements Iterator
{
	public function __construct(
		private SplObjectStorage $storage
	) {
	}

	public function current(): mixed
	{
		return $this->storage->offsetGet($this->storage->current());
	}

	public function next(): void
	{
		$this->storage->next();
	}

	public function key(): object
	{
		return $this->storage->current();
	}

	public function valid(): bool
	{
		return $this->storage->valid();
	}

	public function rewind(): void
	{
		$this->storage->rewind();
	}
}
