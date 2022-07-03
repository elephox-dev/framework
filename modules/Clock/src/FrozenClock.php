<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateTimeImmutable;
use JetBrains\PhpStorm\Pure;

class FrozenClock extends AbstractClock
{
	#[Pure]
	public function __construct(
		private readonly DateTimeImmutable $source,
	) {
	}

	#[Pure]
	public function now(): DateTimeImmutable
	{
		return $this->source;
	}
}
