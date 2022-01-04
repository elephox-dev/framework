<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Closure;
use Iterator;

/**
 * @template TKey
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
class WhereIterator implements Iterator
{
	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): bool $predicate
	 */
	public function __construct(
		private Iterator $iterator,
		private Closure $predicate
	) {
	}

	public function current(): mixed
	{
		return $this->iterator->current();
	}

	public function next(): void
	{
		if (($this->predicate)($this->iterator->current(), $this->iterator->key())) {
			$this->iterator->next();
		}

		$this->moveToNextMatch();
	}

	private function moveToNextMatch(): void
	{
		while ($this->iterator->valid()) {
			if (($this->predicate)($this->iterator->current(), $this->iterator->key())) {
				return;
			}

			$this->iterator->next();
		}
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

		$this->moveToNextMatch();
	}
}
