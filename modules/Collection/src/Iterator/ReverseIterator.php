<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Countable;
use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 *
 * @implements OuterIterator<TKey, TValue>
 */
class ReverseIterator implements OuterIterator, Countable
{
	private array $elementStack = [];
	private array $keyQueue = [];

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 */
	public function __construct(
		private Iterator $iterator,
	) {
	}

	public function current(): mixed
	{
		return current($this->elementStack);
	}

	public function next(): void
	{
		prev($this->elementStack);

		// MAYBE: only reverse key order if key is numeric
		next($this->keyQueue);
	}

	public function key(): mixed
	{
		return current($this->keyQueue);
	}

	public function valid(): bool
	{
		return key($this->elementStack) !== null;
	}

	public function rewind(): void
	{
		$this->elementStack = [];
		$this->keyQueue = [];
		foreach ($this->iterator as $key => $value) {
			$this->elementStack[] = $value;
			$this->keyQueue[] = $key;
		}
		end($this->elementStack);
	}

	public function getInnerIterator(): Iterator
	{
		return $this->iterator;
	}

	public function count(): int
	{
		return count($this->elementStack);
	}
}
