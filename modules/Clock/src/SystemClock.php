<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;

class SystemClock extends AbstractClock
{
	public function now(): DateTimeImmutable
	{
		return new DateTimeImmutable();
	}
}
