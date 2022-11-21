<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Elephox\Clock\Contract\Clock;
use Elephox\Clock\Contract\Duration as DurationContract;
use Iterator;
use JetBrains\PhpStorm\Pure;

/**
 * @implements Iterator<int, Clock>
 */
class PeriodIterator implements Iterator
{
	private int $periodCount = 0;
	private Duration $offset;

	#[Pure]
	public function __construct(
		private readonly Clock $start,
		private readonly DurationContract $period,
		private readonly ?Clock $end = null,
	) {
		$this->offset = new Duration();
	}

	#[Pure]
	public function getStart(): Clock
	{
		return $this->start;
	}

	#[Pure]
	public function getEnd(): ?Clock
	{
		return $this->end;
	}

	public function getTotalDuration(): ?DurationContract
	{
		if ($this->end === null) {
			return null;
		}

		return $this->start->diff($this->end);
	}

	public function current(): Clock
	{
		return $this->start->add($this->offset);
	}

	#[Pure]
	public function currentOffset(): DurationContract
	{
		return $this->offset;
	}

	public function next(): void
	{
		$this->offset = $this->offset->add($this->period);
		$this->periodCount++;
	}

	#[Pure]
	public function key(): int
	{
		return $this->periodCount;
	}

	public function valid(): bool
	{
		if ($this->end === null) {
			return true;
		}

		return $this->current()->compare($this->end) <= 0;
	}

	public function rewind(): void
	{
		$this->offset = new Duration();
		$this->periodCount = 0;
	}
}
