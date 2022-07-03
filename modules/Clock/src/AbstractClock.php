<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use Elephox\Clock\Contract\Clock;
use Elephox\Clock\Contract\Duration;
use StellaMaris\Clock\ClockInterface;

abstract class AbstractClock implements Clock
{
	public function diff(ClockInterface $clock): Duration
	{
		$diff = $this->now()->diff($clock->now());

		return ValuesDuration::from(
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
		$diff = $this->now()->diff($clock->now());

		if ($diff->invert === 1) {
			return 1;
		}

		return $diff->f === 0.0 && $diff->s === 0 && $diff->i === 0 && $diff->h === 0 && $diff->d === 0 && $diff->m === 0 && $diff->y === 0
			? 0
			: -1;
	}

	public function add(Duration $duration): Clock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->add(AbstractDuration::toInterval($duration)));
	}

	public function sub(Duration $duration): Clock
	{
		return new LazyClock(fn (): DateTimeImmutable => $this->now()->sub(AbstractDuration::toInterval($duration)));
	}
}
