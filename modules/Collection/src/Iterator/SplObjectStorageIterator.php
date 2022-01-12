<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use BadMethodCallException;
use Iterator;
use SplObjectStorage;

/**
 * @template T of object
 *
 * @implements Iterator<never, T>
 */
class SplObjectStorageIterator implements Iterator
{
	public function __construct(
		private SplObjectStorage $storage
	) {
	}

	public function current(): object
	{
		return $this->storage->current();
	}

	public function next(): void
	{
		$this->storage->next();
	}

	public function key(): never
	{
		throw new BadMethodCallException("Cannot access key of " . $this::class);
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
