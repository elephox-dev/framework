<?php
declare(strict_types=1);

namespace Elephox\Clock\Contract;

use StellaMaris\Clock\ClockInterface;

interface Clock extends ClockInterface
{
	public function diff(ClockInterface $clock): Duration;

	public function add(Duration $duration): Clock;

	public function sub(Duration $duration): Clock;

	public function equals(ClockInterface $clock): bool;

	public function compare(ClockInterface $clock): int;
}
