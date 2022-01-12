<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Iterator;
use Closure;

/**
 * @template TKey
 * @template TValue
 * @template TCompareKey
 *
 * @implements Iterator<TKey, TValue>
 */
class OrderedIterator implements Iterator
{
	public ?OrderedIterator $next = null;

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): TCompareKey $keySelector
	 * @param Closure(TCompareKey, TCompareKey): int $comparer
	 */
	public function __construct(
		protected Iterator $iterator,
		protected Closure $keySelector,
		protected Closure $comparer
	) {
	}

	public function current(): mixed
	{
		// TODO: Implement current() method.
	}

	public function next(): void
	{
		// TODO: Implement next() method.
	}

	public function key(): mixed
	{
		// TODO: Implement key() method.
	}

	public function valid(): bool
	{
		// TODO: Implement valid() method.
	}

	public function rewind(): void
	{
		$this->iterator->rewind();
	}
}
