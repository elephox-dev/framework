<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 * @template TResultKey
 *
 * @implements OuterIterator<TResultKey, TValue>
 */
class KeySelectIterator implements OuterIterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TKey, TValue): TResultKey $keySelector
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly Closure $keySelector,
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
		/** @psalm-suppress PossiblyNullArgument since the usage may allow null as a parameter */
		return ($this->keySelector)($this->iterator->key(), $this->iterator->current());
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
