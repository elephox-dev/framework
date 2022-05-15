<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 *
 * @implements OuterIterator<TKey, TValue>
 */
class WhileIterator implements OuterIterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): bool $predicate
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly Closure $predicate,
	) {
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

	public function valid(): bool
	{
		/** @psalm-suppress PossiblyNullArgument since the usage may allow null as a parameter */
		return $this->iterator->valid() && ($this->predicate)($this->iterator->current(), $this->iterator->key());
	}

	public function rewind(): void
	{
		$this->iterator->rewind();
	}

	public function getInnerIterator(): Iterator
	{
		return $this->iterator;
	}
}
