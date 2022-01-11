<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Iterator;

/**
 * @template TKey
 * @template TValue
 * @template TResult
 *
 * @implements Iterator<TKey, TResult>
 */
class SelectIterator implements Iterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): TResult $elementSelector
	 */
	public function __construct(
		private Iterator $iterator,
		private Closure $elementSelector
	) {
	}

	public function current(): mixed
	{
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
}
