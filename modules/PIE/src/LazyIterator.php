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
 * @implements IteratorAggregate<TKey, TValue>
 */
class LazyIterator implements Iterator, IteratorAggregate
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
	public function getIterator(): Iterator
	{
		if ($this->inner === null)
		{
			$this->inner = ($this->callback)();
		}

		return $this->inner;
	}

	public function current(): mixed
	{
		return $this->getIterator()->current();
	}

	public function next(): void
	{
		$this->getIterator()->next();
	}

	public function key(): mixed
	{
		return $this->getIterator()->key();
	}

	public function valid(): bool
	{
		return $this->getIterator()->valid();
	}

	public function rewind(): void
	{
		$this->getIterator()->rewind();
	}
}
