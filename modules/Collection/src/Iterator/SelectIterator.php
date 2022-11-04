<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 * @template TResult
 *
 * @implements OuterIterator<TKey, TResult>
 */
class SelectIterator implements OuterIterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): TResult $elementSelector
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly Closure $elementSelector,
	) {
	}

	public function current(): mixed
	{
		/** @psalm-suppress PossiblyNullArgument since the usage may allow null as a parameter */
		return ($this->elementSelector)($this->iterator->current(), $this->iterator->key());
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
		return $this->iterator->valid();
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
