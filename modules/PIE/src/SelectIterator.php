<?php
declare(strict_types=1);

namespace Elephox\PIE;

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
	 * @param null|Closure(TKey, TValue): TResult $keySelector
	 */
	public function __construct(
		private Iterator $iterator,
		private Closure $elementSelector,
		private ?Closure $keySelector = null
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
		if ($this->keySelector === null) {
			return $this->iterator->key();
		}

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
}
