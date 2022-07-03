<?php
declare(strict_types=1);

namespace Elephox\Clock\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface Duration
{
	public const MICROSECONDS_PER_SECOND = 1_000_000;
	public const MICROSECONDS_PER_MINUTE = self::MICROSECONDS_PER_SECOND * self::SECONDS_PER_MINUTE;
	public const MICROSECONDS_PER_HOUR = self::MICROSECONDS_PER_MINUTE * self::MINUTES_PER_HOUR;
	public const MICROSECONDS_PER_DAY = self::MICROSECONDS_PER_HOUR * self::HOURS_PER_DAY;
	public const MICROSECONDS_PER_MONTH = self::MICROSECONDS_PER_DAY * self::DAYS_PER_MONTH;
	public const MICROSECONDS_PER_YEAR = self::MICROSECONDS_PER_DAY * self::DAYS_PER_YEAR;

	public const SECONDS_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_SECOND;
	public const SECONDS_PER_MINUTE = 60;
	public const SECONDS_PER_HOUR = self::SECONDS_PER_MINUTE * self::MINUTES_PER_HOUR;
	public const SECONDS_PER_DAY = self::SECONDS_PER_HOUR * self::HOURS_PER_DAY;
	public const SECONDS_PER_MONTH = self::SECONDS_PER_DAY * self::DAYS_PER_MONTH;
	public const SECONDS_PER_YEAR = self::SECONDS_PER_DAY * self::DAYS_PER_YEAR;

	public const MINUTES_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_MINUTE;
	public const MINUTES_PER_SECOND = 1 / self::SECONDS_PER_MINUTE;
	public const MINUTES_PER_HOUR = 60;
	public const MINUTES_PER_DAY = self::MINUTES_PER_HOUR * self::HOURS_PER_DAY;
	public const MINUTES_PER_MONTH = self::MINUTES_PER_DAY * self::DAYS_PER_MONTH;
	public const MINUTES_PER_YEAR = self::MINUTES_PER_DAY * self::DAYS_PER_YEAR;

	public const HOURS_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_HOUR;
	public const HOURS_PER_SECOND = 1 / self::SECONDS_PER_HOUR;
	public const HOURS_PER_MINUTE = 1 / self::MINUTES_PER_HOUR;
	public const HOURS_PER_DAY = 24;
	public const HOURS_PER_MONTH = self::HOURS_PER_DAY * self::DAYS_PER_MONTH;
	public const HOURS_PER_YEAR = self::HOURS_PER_DAY * self::DAYS_PER_YEAR;

	public const DAYS_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_DAY;
	public const DAYS_PER_SECOND = 1 / self::SECONDS_PER_DAY;
	public const DAYS_PER_MINUTE = 1 / self::MINUTES_PER_DAY;
	public const DAYS_PER_HOUR = 1 / self::HOURS_PER_DAY;
	public const DAYS_PER_MONTH = self::DAYS_PER_YEAR / self::MONTHS_PER_YEAR;
	public const DAYS_PER_YEAR = 365.2425; // average length of a Gregorian year over a complete leap cycle of 400 years

	public const MONTHS_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_MONTH;
	public const MONTHS_PER_SECOND = 1 / self::SECONDS_PER_MONTH;
	public const MONTHS_PER_MINUTE = 1 / self::MINUTES_PER_MONTH;
	public const MONTHS_PER_HOUR = 1 / self::HOURS_PER_MONTH;
	public const MONTHS_PER_DAY = 1 / self::DAYS_PER_MONTH;
	public const MONTHS_PER_YEAR = 12;

	public const YEARS_PER_MICROSECOND = 1 / self::MICROSECONDS_PER_YEAR;
	public const YEARS_PER_SECOND = 1 / self::SECONDS_PER_YEAR;
	public const YEARS_PER_MINUTE = 1 / self::MINUTES_PER_YEAR;
	public const YEARS_PER_HOUR = 1 / self::HOURS_PER_YEAR;
	public const YEARS_PER_DAY = 1 / self::DAYS_PER_YEAR;
	public const YEARS_PER_MONTH = 1 / self::MONTHS_PER_YEAR;

	public static function from(
		bool $negative = false,
		float $microseconds = 0,
		int $seconds = 0,
		int $minutes = 0,
		int $hours = 0,
		int $days = 0,
		int $months = 0,
		int $years = 0,
	): self;

	#[Pure]
	public function isNegative(): bool;

	#[Pure]
	public function getMicroseconds(): float;

	#[Pure]
	public function getSeconds(): int;

	#[Pure]
	public function getMinutes(): int;

	#[Pure]
	public function getHours(): int;

	#[Pure]
	public function getDays(): int;

	#[Pure]
	public function getMonths(): int;

	#[Pure]
	public function getYears(): int;

	#[Pure]
	public function getTotalMicroseconds(): float;

	#[Pure]
	public function getTotalSeconds(): float;

	#[Pure]
	public function getTotalMinutes(): float;

	#[Pure]
	public function getTotalHours(): float;

	#[Pure]
	public function getTotalDays(): float;

	#[Pure]
	public function getTotalMonths(): float;

	#[Pure]
	public function getTotalYears(): float;

	#[Pure]
	public function add(Duration $duration): Duration;

	#[Pure]
	public function subtract(Duration $duration): Duration;

	#[Pure]
	public function equals(Duration $duration): bool;

	#[Pure]
	public function compare(Duration $duration): int;
}
