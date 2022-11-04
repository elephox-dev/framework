<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Countable;
use Iterator;
use Traversable;

/**
 * @template TKey
 * @template TValue
 *
 * @implements Iterator<TKey, TValue>
 */
class ReverseIterator implements Iterator, Countable
{
	private array $elementStack = [];
	private array $keyQueue = [];

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 */
	public function __construct(
		private readonly Traversable $iterator,
		private readonly bool $preserveKeys,
	) {
	}

	public function current(): mixed
	{
		return current($this->elementStack);
	}

	public function next(): void
	{
		prev($this->elementStack);

		if ($this->preserveKeys) {
			prev($this->keyQueue);
		} else {
			next($this->keyQueue);
		}
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
		if ($this->preserveKeys) {
			end($this->keyQueue);
		}
	}

	public function count(): int
	{
		return count($this->elementStack);
	}
}
