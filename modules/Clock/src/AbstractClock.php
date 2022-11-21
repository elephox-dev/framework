<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use Elephox\Clock\Contract\Clock;
use Elephox\Clock\Contract\Duration as DurationContract;
use StellaMaris\Clock\ClockInterface;

abstract class AbstractClock implements Clock
{
	public function diff(ClockInterface $clock): DurationContract
	{
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

	public function equals(ClockInterface $clock): bool
	{
		return $this->now()->getTimestamp() === $clock->now()->getTimestamp();
	}

	public function compare(ClockInterface $clock): int
	{
		$diff = $this->diff($clock);

		return Duration::from()->compare($diff);
	}

	public function add(DurationContract $duration): Clock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->add($duration->toDateInterval()));
	}

	public function sub(DurationContract $duration): Clock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->sub($duration->toDateInterval()));
	}
}
