<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;

/**
 * @template TKey
 * @template TValue
 * @template TCompareKey
 *
 * @implements Iterator<TKey, TValue>
 */
class UniqueByIterator implements Iterator
{
	/**
	 * @var list<TCompareKey>
	 */
	private array $iteratedKeys = [];

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue): TCompareKey $keySelector,
	 * @param Closure(TCompareKey, TCompareKey): bool $comparer
	 */
	public function __construct(
		private Iterator $iterator,
		private Closure $keySelector,
		private Closure $comparer
	) {
	}

	public function current(): mixed
	{
		$current = $this->iterator->current();

		$this->iteratedKeys[] = ($this->keySelector)($current);

		return $current;
	}

	public function next(): void
	{
		while ($this->iterator->valid() && $this->wasIterated($this->iterator->current())) {
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
	 * @return bool
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
}
