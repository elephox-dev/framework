<?php
declare(strict_types=1);

namespace Elephox\Clock;

use DateInterval;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class Duration extends AbstractDuration
{
	#[Pure]
	public static function zero(): self
	{
		/**
		 * @var ?self $zero
		 *
		 * @psalm-suppress ImpureStaticVariable
		 */
		static $zero;
		$zero ??= new self();

		return $zero;
	}

	#[Pure]
	public static function fromDateInterval(DateInterval $interval): self
	{
		return new self(
			$interval->invert === 1,
			$interval->f,
			$interval->s,
			$interval->i,
			$interval->h,
			$interval->d,
			$interval->m,
			$interval->y,
		);
	}

	#[Pure]
	public static function from(
		bool $negative = false,
		float $microseconds = 0,
		int $seconds = 0,
		int $minutes = 0,
		int $hours = 0,
		int $days = 0,
		int $months = 0,
		int $years = 0,
	): Contract\Duration {
		return new self(
			$negative,
			$microseconds,
			$seconds,
			$minutes,
			$hours,
			$days,
			$months,
			$years,
		);
	}

	public static function fromTotalMicroseconds(float $total): Contract\Duration
	{
		$negative = $total < 0;

		$secondsPart = (int)($total / self::MICROSECONDS_PER_SECOND);
		$minutesPart = (int)($secondsPart / self::SECONDS_PER_MINUTE);
		$hoursPart = (int)($minutesPart / self::MINUTES_PER_HOUR);
		$daysPart = (int)($hoursPart / self::HOURS_PER_DAY);
		$monthsPart = (int)($daysPart / self::DAYS_PER_MONTH);
		$years = (int)($monthsPart / self::MONTHS_PER_YEAR);

		$microseconds = fmod($total, self::MICROSECONDS_PER_SECOND);
		$seconds = (int)fmod($secondsPart, self::SECONDS_PER_MINUTE);
		$minutes = (int)fmod($minutesPart, self::MINUTES_PER_HOUR);
		$hours = (int)fmod($hoursPart, self::HOURS_PER_DAY);
		$days = (int)fmod($monthsPart, self::DAYS_PER_MONTH);
		$months = (int)fmod($years, self::MONTHS_PER_YEAR);

		return new self(
			$negative,
			$microseconds,
			$seconds,
			$minutes,
			$hours,
			$days,
			$months,
			$years,
		);
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
		assert(
			$this->microseconds >= 0,
			'Microseconds must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->seconds >= 0,
			'Seconds must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->minutes >= 0,
			'Minutes must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->hours >= 0,
			'Hours must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->days >= 0,
			'Days must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->months >= 0,
			'Months must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
		assert(
			$this->years >= 0,
			'Years must be greater than or equal to 0. To represent negative durations, pass "negative: true"',
		);
	}

	#[Pure]
	public function toDateInterval(): DateInterval
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		$d = new DateInterval(sprintf(
			'P%dY%dM%dDT%dH%dM%dS',
			$this->years,
			$this->months,
			$this->days,
			$this->hours,
			$this->minutes,
			$this->seconds,
		));

		/** @psalm-suppress ImpurePropertyAssignment */
		$d->invert = $this->negative ? 1 : 0;

		// f = Number of microseconds, as a fraction of a second.
		/** @psalm-suppress ImpurePropertyAssignment */
		$d->f = $this->microseconds / self::MICROSECONDS_PER_SECOND;

		return $d;
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
