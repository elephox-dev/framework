<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Countable;
use SeekableIterator;

/**
 * @implements SeekableIterator<int, int>
 */
class RangeIterator implements SeekableIterator, Countable
{
	private int $offset = 0;

	public function __construct(
		private int $start,
		private int $end,
		private int $step = 1
	) {
	}

	public function current(): int
	{
		return $this->start + $this->offset * $this->step;
	}

	public function next(): void
	{
		$this->offset++;
	}

	public function key(): int
	{
		return $this->offset;
	}

	public function valid(): bool
	{
		return $this->offset < ($this->end - $this->start + $this->step) / $this->step;
	}

	public function rewind(): void
	{
		$this->offset = 0;
	}

	public function seek(int $offset): void
	{
		$this->offset = $offset;
	}

	public function count(): int
	{
		return (int)ceil(($this->end - $this->start + $this->step) / $this->step);
	}
}
