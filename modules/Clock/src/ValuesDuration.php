<?php
declare(strict_types=1);

namespace Elephox\Clock;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class ValuesDuration extends AbstractDuration
{
	#[Pure]
	public static function from(bool $negative = false, float $microseconds = 0, int $seconds = 0, int $minutes = 0, int $hours = 0, int $days = 0, int $months = 0, int $years = 0): Contract\Duration
	{
		return new self($negative, $microseconds, $seconds, $minutes, $hours, $days, $months, $years);
	}

	#[Pure]
	public function __construct(
		private readonly bool $negative = false,
		private readonly float $microseconds = 0,
		private readonly int $seconds = 0,
		private readonly int $minutes = 0,
		private readonly int $hours = 0,
		private readonly int $days = 0,
		private readonly int $months = 0,
		private readonly int $years = 0,
	) {
		assert($this->microseconds >= 0, 'Microseconds must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->seconds >= 0, 'Seconds must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->minutes >= 0, 'Minutes must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->hours >= 0, 'Hours must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->days >= 0, 'Days must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->months >= 0, 'Months must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
		assert($this->years >= 0, 'Years must be greater than or equal to 0. To represent negative durations, pass "negative: true"');
	}

	#[Pure]
	public function isNegative(): bool
	{
		return $this->negative;
	}

	#[Pure]
	public function getMicroseconds(): float
	{
		return $this->microseconds;
	}

	#[Pure]
	public function getSeconds(): int
	{
		return $this->seconds;
	}

	#[Pure]
	public function getMinutes(): int
	{
		return $this->minutes;
	}

	#[Pure]
	public function getHours(): int
	{
		return $this->hours;
	}

	#[Pure]
	public function getDays(): int
	{
		return $this->days;
	}

	#[Pure]
	public function getMonths(): int
	{
		return $this->months;
	}

	#[Pure]
	public function getYears(): int
	{
		return $this->years;
	}
}
