<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Iterator;
use OutOfRangeException;
use SeekableIterator;

/**
 * @template TKey
 * @template TValue
 *
 * @implements SeekableIterator<TKey, TValue>
 */
class CachingIterator implements SeekableIterator
{
	private array $innerValues = [];
	private array $innerKeys = [];
	private int $position = 0;

	public function __construct(
		private Iterator $inner,
	) {
	}

	public function current(): mixed
	{
		while (count($this->innerValues) < $this->position && $this->inner->valid()) {
			$this->innerValues[] = $this->inner->current();
			$this->innerKeys[] = $this->inner->key();
			$this->inner->next();
		}

		if (count($this->innerValues) < $this->position) {
			throw new OutOfRangeException("The requested position $this->position is out of range");
		}

		return $this->innerValues[$this->position];
	}

	public function next(): void
	{
		$this->position++;
	}

	public function key(): mixed
	{
		while (count($this->innerKeys) < $this->position && $this->inner->valid()) {
			$this->innerValues[] = $this->inner->current();
			$this->innerKeys[] = $this->inner->key();
			$this->inner->next();
		}

		if (count($this->innerKeys) < $this->position) {
			throw new OutOfRangeException("The requested position $this->position is out of range");
		}

		return $this->innerKeys[$this->position];
	}

	public function valid(): bool
	{
		return count($this->innerKeys) < $this->position || $this->inner->valid();
	}

	public function rewind(): void
	{
		$this->position = 0;
	}

	public function seek(int $offset): void
	{
		$this->position = $offset;
	}
}
