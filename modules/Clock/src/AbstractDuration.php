<?php
declare(strict_types=1);

namespace Elephox\Clock;

use Elephox\Clock\Contract\Duration as DurationContract;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
abstract class AbstractDuration implements DurationContract
{
	#[Pure]
	public function getTotalMicroseconds(): float
	{
		return
			$this->getMicroseconds() +
			$this->getSeconds() * self::MICROSECONDS_PER_SECOND +
			$this->getMinutes() * self::MICROSECONDS_PER_MINUTE +
			$this->getHours() * self::MICROSECONDS_PER_HOUR +
			$this->getDays() * self::MICROSECONDS_PER_DAY +
			$this->getMonths() * self::MICROSECONDS_PER_MONTH +
			$this->getYears() * self::MICROSECONDS_PER_YEAR;
	}

	#[Pure]
	public function getTotalSeconds(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_SECOND +
			$this->getSeconds() +
			$this->getMinutes() * self::SECONDS_PER_MINUTE +
			$this->getHours() * self::SECONDS_PER_HOUR +
			$this->getDays() * self::SECONDS_PER_DAY +
			$this->getMonths() * self::SECONDS_PER_MONTH +
			$this->getYears() * self::SECONDS_PER_YEAR;
	}

	#[Pure]
	public function getTotalMinutes(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_MINUTE +
			$this->getSeconds() / self::SECONDS_PER_MINUTE +
			$this->getMinutes() +
			$this->getHours() * self::MINUTES_PER_HOUR +
			$this->getDays() * self::MINUTES_PER_DAY +
			$this->getMonths() * self::MINUTES_PER_MONTH +
			$this->getYears() * self::MINUTES_PER_YEAR;
	}

	#[Pure]
	public function getTotalHours(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_HOUR +
			$this->getSeconds() / self::SECONDS_PER_HOUR +
			$this->getMinutes() / self::MINUTES_PER_HOUR +
			$this->getHours() +
			$this->getDays() * self::HOURS_PER_DAY +
			$this->getMonths() * self::HOURS_PER_MONTH +
			$this->getYears() * self::HOURS_PER_YEAR;
	}

	#[Pure]
	public function getTotalDays(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_DAY +
			$this->getSeconds() / self::SECONDS_PER_DAY +
			$this->getMinutes() / self::MINUTES_PER_DAY +
			$this->getHours() / self::HOURS_PER_DAY +
			$this->getDays() +
			$this->getMonths() * self::DAYS_PER_MONTH +
			$this->getYears() * self::DAYS_PER_YEAR;
	}

	#[Pure]
	public function getTotalMonths(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_MONTH +
			$this->getSeconds() / self::SECONDS_PER_MONTH +
			$this->getMinutes() / self::MINUTES_PER_MONTH +
			$this->getHours() / self::HOURS_PER_MONTH +
			$this->getDays() / self::DAYS_PER_MONTH +
			$this->getMonths() +
			$this->getYears() * self::MONTHS_PER_YEAR;
	}

	#[Pure]
	public function getTotalYears(): float
	{
		return
			$this->getMicroseconds() / self::MICROSECONDS_PER_YEAR +
			$this->getSeconds() / self::SECONDS_PER_YEAR +
			$this->getMinutes() / self::MINUTES_PER_YEAR +
			$this->getHours() / self::HOURS_PER_YEAR +
			$this->getDays() / self::DAYS_PER_YEAR +
			$this->getMonths() / self::MONTHS_PER_YEAR +
			$this->getYears();
	}

	#[Pure]
	public function add(DurationContract $duration): DurationContract
	{
		return new Duration(
			$this->getTotalYears() + $duration->getTotalYears() < 0,
			$this->getMicroseconds() + $duration->getMicroseconds(),
			$this->getSeconds() + $duration->getSeconds(),
			$this->getMinutes() + $duration->getMinutes(),
			$this->getHours() + $duration->getHours(),
			$this->getDays() + $duration->getDays(),
			$this->getMonths() + $duration->getMonths(),
			$this->getYears() + $duration->getYears(),
		);
	}

	#[Pure]
	public function subtract(DurationContract $duration): DurationContract
	{
		return new Duration(
			$this->getTotalMicroseconds() - $duration->getTotalMicroseconds() < 0,
			abs($this->getMicroseconds() - $duration->getMicroseconds()),
			abs($this->getSeconds() - $duration->getSeconds()),
			abs($this->getMinutes() - $duration->getMinutes()),
			abs($this->getHours() - $duration->getHours()),
			abs($this->getDays() - $duration->getDays()),
			abs($this->getMonths() - $duration->getMonths()),
			abs($this->getYears() - $duration->getYears()),
		);
	}

	#[Pure]
	public function equals(DurationContract $duration): bool
	{
		return
			$this->isNegative() === $duration->isNegative() &&
			$this->getMicroseconds() === $duration->getMicroseconds() &&
			$this->getSeconds() === $duration->getSeconds() &&
			$this->getMinutes() === $duration->getMinutes() &&
			$this->getHours() === $duration->getHours() &&
			$this->getDays() === $duration->getDays() &&
			$this->getMonths() === $duration->getMonths() &&
			$this->getYears() === $duration->getYears();
	}

	#[Pure]
	public function compare(DurationContract $duration): int
	{
		if ($this->isNegative() !== $duration->isNegative()) {
			return $this->isNegative() ? -1 : 1;
		}

		$diff = $this->getTotalMicroseconds() - $duration->getTotalMicroseconds();
		if ($diff !== 0.0) {
			return $diff > 0 ? 1 : -1;
		}

		return 0;
	}
}
