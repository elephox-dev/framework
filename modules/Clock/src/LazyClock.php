<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Closure;
use DateTimeImmutable;

class LazyClock extends AbstractClock
{
	private ?DateTimeImmutable $value = null;

	/**
	 * @param Closure(): DateTimeImmutable $dateTimeProvider
	 */
	public function __construct(
		private readonly Closure $dateTimeProvider,
	) {
	}

	public function now(): DateTimeImmutable
	{
		if ($this->value === null) {
			$this->value = ($this->dateTimeProvider)();
		}

		return $this->value;
	}
}
