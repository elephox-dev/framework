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
	private int $offset = 0;

	public function __construct(
		private Iterator $inner,
	) {
	}

	private function warmCacheUntil(int $offset): void
	{
		$innerOffset = count($this->innerKeys) - 1;
		while ($innerOffset < $offset) {
			$this->innerKeys[] = $this->inner->key();
			$this->innerValues[] = $this->inner->current();
			$this->inner->next();
			$innerOffset++;
		}
	}

	public function current(): mixed
	{
		$this->warmCacheUntil($this->offset);

		return $this->innerValues[$this->offset];
	}

	public function next(): void
	{
		$this->offset++;
	}

	public function key(): mixed
	{
		$this->warmCacheUntil($this->offset);

		return $this->innerKeys[$this->offset];
	}

	public function valid(): bool
	{
		return $this->offset < count($this->innerKeys) || $this->inner->valid();
	}

	public function rewind(): void
	{
		$this->offset = 0;
	}

	public function seek(int $offset): void
	{
		$this->offset = $offset;
	}
}
