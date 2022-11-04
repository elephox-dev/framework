<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;
use OuterIterator;

/**
 * @template TKey
 * @template TValue
 * @template TCompareKey
 *
 * @implements OuterIterator<TKey, TValue>
 */
class UniqueByIterator implements OuterIterator
{
	/**
	 * @var list<TCompareKey>
	 */
	private array $iteratedKeys = [];

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue): TCompareKey $keySelector
	 * @param Closure(TCompareKey, TCompareKey): bool $comparer
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly Closure $keySelector,
		private readonly Closure $comparer,
	) {
	}

	public function current(): mixed
	{
		$current = $this->iterator->current();

		assert($current !== null, 'Current iterator was null when claiming to be valid');

		$this->iteratedKeys[] = ($this->keySelector)($current);

		return $current;
	}

	public function next(): void
	{
		while ($this->iterator->valid()) {
			$current = $this->iterator->current();

			assert($current !== null, 'Current iterator was null when claiming to be valid');

			if (!$this->wasIterated($current)) {
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
		$this->iteratedKeys = [];

		$this->iterator->rewind();
	}

	/**
	 * @param TValue $value
	 */
	public function wasIterated(mixed $value): bool
	{
		$key = ($this->keySelector)($value);

		foreach ($this->iteratedKeys as $iteratedKey) {
			if (($this->comparer)($key, $iteratedKey)) {
				return true;
			}
		}

		return false;
	}

	public function getInnerIterator(): Iterator
	{
		return $this->iterator;
	}
}
