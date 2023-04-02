<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use Elephox\Clock\Contract\Clock;
use Elephox\Clock\Contract\Duration as DurationContract;
use JetBrains\PhpStorm\Pure;
use Psr\Clock\ClockInterface;

abstract class AbstractClock implements Clock
{
	#[Pure]
	public function diff(ClockInterface $clock): DurationContract
	{
		/** @psalm-suppress ImpureMethodCall */
		$diff = $this->now()->diff($clock->now());

		return Duration::from(
			$diff->invert === 1,
			$diff->f,
			$diff->s,
			$diff->i,
			$diff->h,
			$diff->d,
			$diff->m,
			$diff->y,
		);
	}

	#[Pure]
	public function equals(ClockInterface $clock): bool
	{
		return $this->compare($clock) === 0;
	}

	#[Pure]
	public function compare(ClockInterface $clock): int
	{
		$diff = $this->diff($clock);

		return Duration::zero()->compare($diff);
	}

	#[Pure]
	public function add(DurationContract $duration): LazyClock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->add($duration->toDateInterval()));
	}

	#[Pure]
	public function sub(DurationContract $duration): LazyClock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->sub($duration->toDateInterval()));
	}
}
