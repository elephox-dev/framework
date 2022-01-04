<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Closure;
use Iterator;
use IteratorAggregate;

/**
 * @template TKey
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
class LazyIterator implements Iterator
{
	/**
	 * @var Iterator<TKey, TValue>|null $inner
	 */
	private ?Iterator $inner = null;

	/**
	 * @param Closure(): Iterator<TKey, TValue> $callback
	 */
	public function __construct(
		private Closure $callback,
	) {
	}

	/**
	 * @return Iterator<TKey, TValue>
	 */
	private function getInnerIterator(): Iterator
	{
		if ($this->inner === null)
		{
			$this->inner = ($this->callback)();
		}

		return $this->inner;
	}

	public function current(): mixed
	{
		return $this->getInnerIterator()->current();
	}

	public function next(): void
	{
		$this->getInnerIterator()->next();
	}

	public function key(): mixed
	{
		return $this->getInnerIterator()->key();
	}

	public function valid(): bool
	{
		return $this->getInnerIterator()->valid();
	}

	public function rewind(): void
	{
		$this->getInnerIterator()->rewind();
	}
}
