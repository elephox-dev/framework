<?php
declare(strict_types=1);

namespace Elephox\Clock\Contract;

use Iterator;
use JetBrains\PhpStorm\Pure;

/**
 * @extends Iterator<int, Clock>
 */
interface PeriodIterator extends Iterator
{
	#[Pure]
	public function getStart(): Clock;

	#[Pure]
	public function getEnd(): ?Clock;

	#[Pure]
	public function getPeriod(): Duration;

	#[Pure]
	public function getOffset(): Duration;

	#[Pure]
	public function getTotalDuration(): ?Duration;
}
