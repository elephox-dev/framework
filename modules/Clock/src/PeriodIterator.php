<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Elephox\Clock\Contract\Clock;
use Elephox\Clock\Contract\Duration as DurationContract;
use Elephox\Clock\Contract\PeriodIterator as PeriodIteratorContract;
use JetBrains\PhpStorm\Pure;

class PeriodIterator implements PeriodIteratorContract
{
	private int $periodCount = 0;
	private DurationContract $offset;

	#[Pure]
	public function __construct(
		private readonly Clock $start,
		private readonly DurationContract $period,
		private readonly ?Clock $end = null,
	) {
		$this->offset = Duration::zero();
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

	#[Pure]
	public function getPeriod(): DurationContract
	{
		return $this->period;
	}

	#[Pure]
	public function getOffset(): DurationContract
	{
		return $this->offset;
	}

	#[Pure]
	public function getTotalDuration(): ?DurationContract
	{
		$end = $this->getEnd();
		if ($end === null) {
			return null;
		}

		return $this->getStart()->diff($end);
	}

	#[Pure]
	public function current(): Clock
	{
		return $this->getStart()->add($this->getOffset());
	}

	public function next(): void
	{
		$this->offset = $this->getOffset()->add($this->getPeriod());
		$this->periodCount++;
	}

	#[Pure]
	public function key(): int
	{
		return $this->periodCount;
	}

	#[Pure]
	public function valid(): bool
	{
		$end = $this->getEnd();
		if ($end === null) {
			return true;
		}

		return $this->current()->compare($end) <= 0;
	}

	public function rewind(): void
	{
		$this->offset = Duration::zero();
		$this->periodCount = 0;
	}
}
