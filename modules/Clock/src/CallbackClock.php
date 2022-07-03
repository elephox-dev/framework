<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Closure;
use DateTimeImmutable;

class CallbackClock extends AbstractClock
{
	/**
	 * @param Closure(): DateTimeImmutable $dateTimeProvider
	 */
	public function __construct(
		private readonly Closure $dateTimeProvider,
	) {
	}

	public function now(): DateTimeImmutable
	{
		return ($this->dateTimeProvider)();
	}
}
