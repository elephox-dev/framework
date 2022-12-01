<?php
declare(strict_types=1);

namespace Elephox\Clock\Contract;

use JetBrains\PhpStorm\Pure;
use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
	/**
	 * Calculates the exact difference between this clock and the given one.
	 *
	 * @param ClockInterface $clock The clock to compare this clock to.
	 * @return Duration The difference as a duration between the two clocks.
	 */
	#[Pure]
	public function diff(ClockInterface $clock): Duration;

	/**
	 * Adds the given duration to this clock.
	 *
	 * @param Duration $duration The duration to add to this clock.
	 * @return $this Fluent setter.
	 */
	#[Pure]
	public function add(Duration $duration): self;

	/**
	 * Subtracts the given duration from this clock.
	 *
	 * @param Duration $duration The duration to remove from this clock.
	 * @return $this Fluent setter.
	 */
	#[Pure]
	public function sub(Duration $duration): self;

	/**
	 * Compares this clock and the given one and checks if they are equal. <code>true</code> if they are, <code>false</code> if not.
	 *
	 * @param ClockInterface $clock
	 * @return bool
	 */
	#[Pure]
	public function equals(ClockInterface $clock): bool;

	/**
	 * Compares the given clock to this clock and returns an integer indicating if one is greater, equal, or smaller than the other.
	 *
	 * @param ClockInterface $clock The clock to compare this clock to.
	 * @return int Returns a positive integer if this clock is considered greater, a negative integer if smaller, and 0 if they are the same.
	 */
	#[Pure]
	public function compare(ClockInterface $clock): int;
}
